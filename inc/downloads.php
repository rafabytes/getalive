<?php
/**
 * Arquivos dos templates: pasta protegida, upload pelo painel e entrega só para logados.
 */

defined( 'ABSPATH' ) || exit;

function ga_private_dir(): string {
	$uploads = wp_upload_dir( null, false );
	return $uploads['basedir'] . '/getalive-private';
}

/**
 * Cria a pasta protegida com bloqueio de acesso direto (Apache e IIS).
 */
function ga_ensure_private_dir(): string {
	$dir = ga_private_dir();
	if ( ! is_dir( $dir ) ) {
		wp_mkdir_p( $dir );
	}
	$guards = [
		'.htaccess'  => "Require all denied\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n",
		'index.php'  => "<?php // Silence is golden.\n",
		'web.config' => "<?xml version=\"1.0\"?><configuration><system.webServer><authorization><deny users=\"*\" /></authorization></system.webServer></configuration>\n",
	];
	foreach ( $guards as $name => $content ) {
		if ( ! file_exists( "$dir/$name" ) ) {
			file_put_contents( "$dir/$name", $content );
		}
	}
	return $dir;
}

/**
 * Guarda o ZIP enviado no painel (ou copiado por script) e atualiza os metadados.
 *
 * @param array|string $source Item de $_FILES ou caminho de um arquivo local.
 */
function ga_store_template_file( int $post_id, $source ) {
	$dir = ga_ensure_private_dir();

	if ( is_array( $source ) ) {
		if ( UPLOAD_ERR_OK !== ( $source['error'] ?? UPLOAD_ERR_NO_FILE ) ) {
			return new WP_Error( 'upload', 'O envio falhou. Verifique o limite de upload do servidor (upload_max_filesize).' );
		}
		$tmp  = $source['tmp_name'];
		$name = sanitize_file_name( $source['name'] );
	} else {
		$tmp  = $source;
		$name = sanitize_file_name( basename( $source ) );
	}

	if ( 'zip' !== strtolower( pathinfo( $name, PATHINFO_EXTENSION ) ) ) {
		return new WP_Error( 'type', 'Envie um arquivo .zip.' );
	}
	$handle = fopen( $tmp, 'rb' );
	$magic  = $handle ? fread( $handle, 4 ) : '';
	if ( $handle ) {
		fclose( $handle );
	}
	if ( "PK\x03\x04" !== $magic ) {
		return new WP_Error( 'type', 'O arquivo não é um ZIP válido.' );
	}

	// Nome no disco imprevisível; o nome original é usado só no download.
	$stored = $post_id . '-' . wp_generate_password( 12, false ) . '.zip';
	$target = "$dir/$stored";
	$moved  = is_array( $source ) ? move_uploaded_file( $tmp, $target ) : copy( $tmp, $target );
	if ( ! $moved ) {
		return new WP_Error( 'move', 'Não foi possível gravar o arquivo na pasta protegida.' );
	}

	$old = (string) get_post_meta( $post_id, '_ga_file', true );
	if ( $old && $old !== $stored && file_exists( "$dir/$old" ) ) {
		wp_delete_file( "$dir/$old" );
	}

	update_post_meta( $post_id, '_ga_file', $stored );
	update_post_meta( $post_id, '_ga_file_name', $name );
	update_post_meta( $post_id, '_ga_file_size', filesize( $target ) );
	return $stored;
}

add_action( 'before_delete_post', function ( $post_id ) {
	if ( 'ga_template' !== get_post_type( $post_id ) ) {
		return;
	}
	$file = (string) get_post_meta( $post_id, '_ga_file', true );
	if ( $file && file_exists( ga_private_dir() . '/' . $file ) ) {
		wp_delete_file( ga_private_dir() . '/' . $file );
	}
} );

/**
 * Pode baixar? Gratuito: qualquer logado. Pago: quem comprou o produto vinculado.
 */
function ga_user_can_download( int $post_id, int $user_id = 0 ): bool {
	$user_id = $user_id ?: get_current_user_id();
	if ( ! $user_id || 'publish' !== get_post_status( $post_id ) && ! user_can( $user_id, 'edit_post', $post_id ) ) {
		return false;
	}
	if ( get_post_meta( $post_id, '_ga_premium', true ) ) {
		return ga_user_owns_template( $post_id, $user_id );
	}
	return true;
}

function ga_download_url( int $post_id ): string {
	return add_query_arg( [
		'ga_download' => $post_id,
		'_wpnonce'    => wp_create_nonce( 'ga_download_' . $post_id ),
	], home_url( '/' ) );
}

add_action( 'template_redirect', function () {
	if ( empty( $_GET['ga_download'] ) ) {
		return;
	}
	$post_id = absint( $_GET['ga_download'] );

	if ( ! is_user_logged_in() ) {
		wp_safe_redirect( ga_login_url( get_permalink( $post_id ) ?: home_url( '/' ) ) );
		exit;
	}
	if ( ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ?? '' ), 'ga_download_' . $post_id ) ) {
		wp_safe_redirect( get_permalink( $post_id ) ?: home_url( '/' ) );
		exit;
	}
	if ( 'ga_template' !== get_post_type( $post_id ) || ! ga_user_can_download( $post_id ) ) {
		wp_die( esc_html__( 'Você não tem acesso a este download.', 'getalive' ), esc_html__( 'Download indisponível', 'getalive' ), [ 'response' => 403, 'back_link' => true ] );
	}

	$file = (string) get_post_meta( $post_id, '_ga_file', true );
	$path = ga_private_dir() . '/' . basename( $file );
	if ( ! $file || ! is_file( $path ) ) {
		wp_die( esc_html__( 'O arquivo deste template não está disponível no momento.', 'getalive' ), esc_html__( 'Arquivo não encontrado', 'getalive' ), [ 'response' => 404, 'back_link' => true ] );
	}

	ga_log_download( $post_id, get_current_user_id() );

	$name = (string) get_post_meta( $post_id, '_ga_file_name', true ) ?: basename( $path );
	nocache_headers();
	header( 'Content-Type: application/zip' );
	header( 'Content-Disposition: attachment; filename="' . str_replace( '"', '', $name ) . '"; filename*=UTF-8\'\'' . rawurlencode( $name ) );
	header( 'Content-Length: ' . filesize( $path ) );
	header( 'X-Content-Type-Options: nosniff' );
	while ( ob_get_level() ) {
		ob_end_clean();
	}
	readfile( $path );
	exit;
} );

/**
 * Contador público por template e histórico por usuário (data do último download).
 */
function ga_log_download( int $post_id, int $user_id ): void {
	update_post_meta( $post_id, '_ga_downloads', (int) get_post_meta( $post_id, '_ga_downloads', true ) + 1 );

	$history             = (array) get_user_meta( $user_id, 'ga_downloads', true );
	$history[ $post_id ] = time();
	arsort( $history );
	update_user_meta( $user_id, 'ga_downloads', $history );

	ga_log_activity( $user_id, 'download', [
		'object_id' => $post_id,
		'ip'        => true,
		'detail'    => [ 'title' => get_the_title( $post_id ), 'version' => (string) get_post_meta( $post_id, '_ga_version', true ) ],
	] );
}

function ga_user_downloads( int $user_id = 0 ): array {
	$user_id = $user_id ?: get_current_user_id();
	return array_filter( (array) get_user_meta( $user_id, 'ga_downloads', true ) );
}

/**
 * Quem baixou um template, do download mais recente ao mais antigo: [ 'user' => WP_User, 'time' => int ].
 * O histórico é um array serializado: o LIKE só pré-filtra, a confirmação é feita no PHP.
 */
function ga_template_downloaders( int $post_id ): array {
	$users = get_users( [
		'meta_key'     => 'ga_downloads',
		'meta_value'   => 'i:' . $post_id . ';',
		'meta_compare' => 'LIKE',
	] );
	$list = [];
	foreach ( $users as $user ) {
		$time = (int) ( ga_user_downloads( $user->ID )[ $post_id ] ?? 0 );
		if ( $time ) {
			$list[] = [ 'user' => $user, 'time' => $time ];
		}
	}
	usort( $list, fn( $a, $b ) => $b['time'] <=> $a['time'] );
	return $list;
}

function ga_downloaders_admin_url( int $post_id ): string {
	return admin_url( 'edit.php?post_type=ga_template&page=ga-downloaders&template=' . $post_id );
}

/**
 * Página "Quem baixou", aberta pelo número na coluna Downloads (fica fora do menu).
 */
add_action( 'admin_menu', function () {
	$hook = add_submenu_page( 'edit.php?post_type=ga_template', 'Quem baixou', 'Quem baixou', 'list_users', 'ga-downloaders', 'ga_downloaders_page' );
	add_action( "load-$hook", function () {
		add_action( 'admin_enqueue_scripts', fn() => wp_enqueue_style( 'ga-admin', GA_URI . '/assets/admin.css', [], filemtime( GA_DIR . '/assets/admin.css' ) ) );
	} );
} );

add_action( 'admin_head', function () {
	remove_submenu_page( 'edit.php?post_type=ga_template', 'ga-downloaders' );
} );

function ga_downloaders_page(): void {
	$post_id = absint( $_GET['template'] ?? 0 );
	if ( 'ga_template' !== get_post_type( $post_id ) ) {
		wp_die( 'Template não encontrado.' );
	}
	$rows  = ga_template_downloaders( $post_id );
	$total = (int) get_post_meta( $post_id, '_ga_downloads', true );
	?>
	<div class="wrap">
		<h1 class="wp-heading-inline">Quem baixou o <?php echo esc_html( get_the_title( $post_id ) ); ?></h1>
		<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=ga_template' ) ); ?>" class="page-title-action">Voltar aos templates</a>
		<hr class="wp-header-end">
		<p>
			<?php echo esc_html( ga_plural( $total, 'download', 'downloads' ) . ' de ' . ga_plural( count( $rows ), 'usuário', 'usuários' ) ); ?>.
			O contador soma cada download; aqui cada usuário aparece uma vez, com a data do último.
		</p>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr><th>Usuário</th><th>E-mail</th><th>Último download</th><th>Conta criada em</th></tr>
			</thead>
			<tbody>
				<?php if ( ! $rows ) : ?>
					<tr><td colspan="4">Ninguém baixou este template ainda.</td></tr>
				<?php endif; ?>
				<?php foreach ( $rows as $row ) : ?>
					<?php $user = $row['user']; ?>
					<tr>
						<td class="ga-downloader">
							<?php echo get_avatar( $user, 32 ); ?>
							<a href="<?php echo esc_url( ga_user_activity_url( $user->ID ) ); ?>" title="Ver atividade"><strong><?php echo esc_html( $user->display_name ); ?></strong></a>
						</td>
						<td><a href="mailto:<?php echo esc_attr( $user->user_email ); ?>"><?php echo esc_html( $user->user_email ); ?></a></td>
						<td><?php echo esc_html( wp_date( 'j \d\e F \d\e Y, H:i', $row['time'] ) ); ?></td>
						<td><?php echo esc_html( wp_date( 'j \d\e F \d\e Y', strtotime( $user->user_registered . ' UTC' ) ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
}

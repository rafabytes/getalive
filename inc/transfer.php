<?php
/**
 * Templates → Exportar / Importar: leva templates de um site para outro (ex.: do local
 * para a produção) num pacote .zip, sem migrar o banco inteiro.
 *
 * O pacote tem package.json (textos, metadados, estilos e cenas), as imagens em media/
 * e o ZIP de download em files/. Na importação só os templates do pacote são criados
 * ou atualizados, casando pelo slug. Nada mais é tocado: usuários, páginas, menus,
 * configurações e os outros templates ficam como estão. Num template que já existe
 * são mantidos a ordem, a data de publicação, o contador de downloads e o produto do
 * WooCommerce. Imagens iguais (mesmo conteúdo) são reaproveitadas, não duplicadas.
 *
 * Também roda pela linha de comando: tools/export-templates.php e tools/import-package.php.
 */

defined( 'ABSPATH' ) || exit;

const GA_TRANSFER_FORMAT = 'getalive-templates';

/** Metadados copiados. Ficam de fora os que são do site de destino (_ga_downloads, _ga_product_id, arquivo). */
const GA_TRANSFER_META = [
	'_ga_tagline', '_ga_version', '_ga_resolution', '_ga_obs_version', '_ga_accent',
	'_ga_features', '_ga_requirements', '_ga_install', '_ga_platforms', '_ga_featured', '_ga_premium', '_ga_price',
];

/** Pasta protegida onde ficam os pacotes enviados (ou colocados por FTP/Gerenciador de Arquivos) até a importação. */
function ga_transfer_dir(): string {
	$dir = ga_ensure_private_dir() . '/import';
	if ( ! is_dir( $dir ) ) {
		wp_mkdir_p( $dir );
		file_put_contents( "$dir/index.php", "<?php // Silence is golden.\n" );
	}
	return $dir;
}

function ga_transfer_prepare(): void {
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	if ( function_exists( 'set_time_limit' ) ) {
		@set_time_limit( 600 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	}
}

/* -------------------------------------------------------------------------- */
/* Exportar                                                                   */
/* -------------------------------------------------------------------------- */

/**
 * Monta o pacote com os templates indicados e devolve o caminho do .zip temporário.
 *
 * @param int[] $ids IDs de posts ga_template.
 * @return string|WP_Error
 */
function ga_transfer_export( array $ids ) {
	if ( ! class_exists( 'ZipArchive' ) ) {
		return new WP_Error( 'zip', 'O PHP deste servidor não tem a extensão zip.' );
	}
	$posts = array_filter( array_map( 'get_post', array_map( 'intval', $ids ) ), fn( $p ) => $p && 'ga_template' === $p->post_type );
	if ( ! $posts ) {
		return new WP_Error( 'empty', 'Escolha pelo menos um template.' );
	}

	$path = wp_tempnam( 'getalive-templates.zip' );
	$zip  = new ZipArchive();
	if ( true !== $zip->open( $path, ZipArchive::OVERWRITE ) ) {
		return new WP_Error( 'zip', 'Não foi possível criar o pacote.' );
	}

	$media = [];
	// Uma imagem entra uma vez no pacote, mesmo usada em mais de um lugar.
	$add_media = function ( int $id ) use ( $zip, &$media ) {
		if ( ! $id || 'attachment' !== get_post_type( $id ) ) {
			return null;
		}
		if ( isset( $media[ $id ] ) ) {
			return $media[ $id ];
		}
		$file = function_exists( 'wp_get_original_image_path' ) ? wp_get_original_image_path( $id ) : '';
		$file = $file && is_file( $file ) ? $file : get_attached_file( $id );
		if ( ! $file || ! is_file( $file ) ) {
			return null;
		}
		$entry = "media/{$id}-" . basename( $file );
		$zip->addFile( $file, $entry );
		return $media[ $id ] = [
			'path'  => $entry,
			'hash'  => (string) get_post_meta( $id, '_ga_source_hash', true ) ?: md5_file( $file ),
			'title' => get_the_title( $id ),
			'alt'   => (string) get_post_meta( $id, '_wp_attachment_image_alt', true ),
		];
	};

	$templates = [];
	foreach ( $posts as $post ) {
		$meta = [];
		foreach ( GA_TRANSFER_META as $key ) {
			if ( metadata_exists( 'post', $post->ID, $key ) ) {
				$meta[ $key ] = get_post_meta( $post->ID, $key, true );
			}
		}

		$styles = [];
		foreach ( wp_get_object_terms( $post->ID, 'ga_style' ) as $term ) {
			$parent   = $term->parent ? get_term( $term->parent, 'ga_style' ) : null;
			$styles[] = [ 'name' => $term->name, 'slug' => $term->slug, 'description' => $term->description, 'parent' => $parent && ! is_wp_error( $parent ) ? $parent->slug : '' ];
		}

		$scenes = [];
		foreach ( (array) get_post_meta( $post->ID, '_ga_scenes', true ) as $scene ) {
			if ( ! is_array( $scene ) ) {
				continue;
			}
			$scenes[] = [ 'title' => $scene['title'] ?? '', 'desc' => $scene['desc'] ?? '', 'image' => $add_media( (int) ( $scene['image'] ?? 0 ) ) ];
		}

		$file   = null;
		$stored = (string) get_post_meta( $post->ID, '_ga_file', true );
		if ( $stored && is_file( ga_private_dir() . "/$stored" ) ) {
			$name  = (string) get_post_meta( $post->ID, '_ga_file_name', true ) ?: $stored;
			$entry = "files/{$post->post_name}/" . sanitize_file_name( $name );
			$zip->addFile( ga_private_dir() . "/$stored", $entry );
			$file = [ 'path' => $entry, 'name' => sanitize_file_name( $name ), 'md5' => md5_file( ga_private_dir() . "/$stored" ) ];
		}

		$templates[] = [
			'slug'         => $post->post_name,
			'title'        => $post->post_title,
			'status'       => $post->post_status,
			'content'      => $post->post_content,
			'excerpt'      => $post->post_excerpt,
			'modified_gmt' => $post->post_modified_gmt,
			'meta'         => $meta,
			'styles'       => $styles,
			'thumbnail'    => $add_media( (int) get_post_thumbnail_id( $post ) ),
			'scenes'       => $scenes,
			'file'         => $file,
		];
	}

	$zip->addFromString( 'package.json', wp_json_encode( [
		'format'    => GA_TRANSFER_FORMAT,
		'version'   => 1,
		'created'   => gmdate( 'Y-m-d H:i:s' ),
		'source'    => home_url(),
		'templates' => $templates,
	], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
	$zip->close();
	return $path;
}

/** Nome sugerido para o pacote: um slug quando é um template só, senão a quantidade. */
function ga_transfer_filename( array $ids ): string {
	$label = 1 === count( $ids ) ? get_post_field( 'post_name', (int) $ids[0] ) : count( $ids ) . '-templates';
	return 'getalive-' . sanitize_file_name( $label ) . '-' . wp_date( 'Ymd-Hi' ) . '.zip';
}

/* -------------------------------------------------------------------------- */
/* Importar                                                                   */
/* -------------------------------------------------------------------------- */

/**
 * Abre um pacote e devolve [ZipArchive, dados do package.json].
 *
 * @return array|WP_Error
 */
function ga_transfer_open( string $path ) {
	if ( ! class_exists( 'ZipArchive' ) ) {
		return new WP_Error( 'zip', 'O PHP deste servidor não tem a extensão zip.' );
	}
	$zip = new ZipArchive();
	if ( true !== $zip->open( $path ) ) {
		return new WP_Error( 'zip', 'O arquivo não é um ZIP válido.' );
	}
	$data = json_decode( (string) $zip->getFromName( 'package.json' ), true );
	if ( ! is_array( $data ) || GA_TRANSFER_FORMAT !== ( $data['format'] ?? '' ) || empty( $data['templates'] ) ) {
		$zip->close();
		return new WP_Error( 'format', 'Este ZIP não é um pacote exportado em Templates → Exportar / Importar.' );
	}
	return [ $zip, $data ];
}

function ga_transfer_find_media( string $hash ): int {
	if ( ! $hash ) {
		return 0;
	}
	$found = get_posts( [
		'post_type'   => 'attachment',
		'post_status' => 'inherit',
		'meta_key'    => '_ga_source_hash',
		'meta_value'  => $hash,
		'fields'      => 'ids',
		'numberposts' => 1,
	] );
	return (int) ( $found[0] ?? 0 );
}

/**
 * O que a importação faria com cada template do pacote, para revisar antes de aplicar.
 */
function ga_transfer_preview( array $data ): array {
	$rows = [];
	foreach ( $data['templates'] as $t ) {
		$slug     = sanitize_title( $t['slug'] ?? '' );
		$existing = $slug ? get_page_by_path( $slug, OBJECT, 'ga_template' ) : null;
		$refs     = array_filter( array_merge( [ $t['thumbnail'] ?? null ], array_column( $t['scenes'] ?? [], 'image' ) ) );
		$new_imgs = count( array_unique( array_filter( array_map( fn( $r ) => ga_transfer_find_media( $r['hash'] ?? '' ) ? null : ( $r['hash'] ?? $r['path'] ), $refs ) ) ) );

		$zip_change = 'sem arquivo';
		if ( ! empty( $t['file'] ) ) {
			$current    = $existing ? (string) get_post_meta( $existing->ID, '_ga_file', true ) : '';
			$same       = $current && is_file( ga_private_dir() . "/$current" ) && md5_file( ga_private_dir() . "/$current" ) === ( $t['file']['md5'] ?? '' );
			$zip_change = $same ? 'ZIP igual' : 'ZIP novo';
		}

		$rows[] = [
			'slug'     => $slug,
			'title'    => $t['title'] ?? $slug,
			'status'   => $t['status'] ?? 'publish',
			'exists'   => (bool) $existing,
			// A cópia de destino foi editada depois da exportação? Vale avisar antes de sobrescrever.
			'newer'    => $existing && ! empty( $t['modified_gmt'] ) && $existing->post_modified_gmt > $t['modified_gmt'],
			'summary'  => sprintf( '%s · %s', $new_imgs ? $new_imgs . ( 1 === $new_imgs ? ' imagem nova' : ' imagens novas' ) : 'imagens já existem', $zip_change ),
			'url'      => $existing ? get_permalink( $existing ) : '',
		];
	}
	return $rows;
}

/** Extrai uma entrada do pacote para um arquivo temporário com o nome indicado. */
function ga_transfer_extract( ZipArchive $zip, string $entry, string $name ) {
	$stream = $zip->getStream( $entry );
	if ( ! $stream ) {
		return new WP_Error( 'missing', "Arquivo ausente no pacote: $entry" );
	}
	$dir = trailingslashit( get_temp_dir() ) . 'ga-transfer-' . wp_generate_password( 8, false );
	wp_mkdir_p( $dir );
	$path = "$dir/" . sanitize_file_name( $name );
	$out  = fopen( $path, 'wb' );
	stream_copy_to_stream( $stream, $out );
	fclose( $out );
	fclose( $stream );
	return $path;
}

function ga_transfer_cleanup( string $path ): void {
	if ( file_exists( $path ) ) {
		wp_delete_file( $path );
	}
	@rmdir( dirname( $path ) ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
}

/** Importa (ou reaproveita) uma imagem do pacote. */
function ga_transfer_media( ZipArchive $zip, ?array $ref, int $parent, array &$log ): int {
	if ( ! $ref || empty( $ref['path'] ) ) {
		return 0;
	}
	$id = ga_transfer_find_media( $ref['hash'] ?? '' );
	if ( $id ) {
		return $id;
	}
	// O prefixo "<id>-" da exportação sai do nome do arquivo na biblioteca.
	$name = preg_replace( '/^\d+-/', '', basename( $ref['path'] ) );
	$tmp  = ga_transfer_extract( $zip, $ref['path'], $name );
	if ( is_wp_error( $tmp ) ) {
		$log[] = '  ERRO ' . $tmp->get_error_message();
		return 0;
	}
	$hash = md5_file( $tmp );
	$id   = media_handle_sideload( [ 'name' => $name, 'tmp_name' => $tmp ], $parent, $ref['title'] ?? '' );
	ga_transfer_cleanup( $tmp );
	if ( is_wp_error( $id ) ) {
		$log[] = "  ERRO na imagem $name: " . $id->get_error_message();
		return 0;
	}
	update_post_meta( $id, '_ga_source_hash', $ref['hash'] ?? $hash );
	update_post_meta( $id, '_wp_attachment_image_alt', $ref['alt'] ?? '' );
	$log[] = "  imagem: $name";
	return (int) $id;
}

/**
 * Cria ou atualiza um template a partir do pacote. Devolve as linhas do relatório.
 */
function ga_transfer_import_template( ZipArchive $zip, array $t ): array {
	$log  = [];
	$slug = sanitize_title( $t['slug'] ?? '' );
	if ( ! $slug ) {
		return [ 'ERRO: template sem slug no pacote.' ];
	}
	$existing = get_page_by_path( $slug, OBJECT, 'ga_template' );
	$status   = in_array( $t['status'] ?? '', [ 'publish', 'draft', 'pending', 'private', 'future' ], true ) ? $t['status'] : 'draft';

	$postarr = [
		'post_type'    => 'ga_template',
		'post_status'  => $status,
		'post_title'   => $t['title'] ?? $slug,
		'post_name'    => $slug,
		'post_content' => $t['content'] ?? '',
		'post_excerpt' => $t['excerpt'] ?? '',
	];
	if ( $existing ) {
		// Mantém posição na lista, data e autor da cópia de destino (sem a data, o WordPress usaria a de agora).
		$postarr['ID']            = $existing->ID;
		$postarr['menu_order']    = $existing->menu_order;
		$postarr['post_author']   = $existing->post_author;
		$postarr['post_date']     = $existing->post_date;
		$postarr['post_date_gmt'] = $existing->post_date_gmt;
	}
	$post_id = wp_insert_post( wp_slash( $postarr ), true );
	if ( is_wp_error( $post_id ) ) {
		return [ "ERRO em {$slug}: " . $post_id->get_error_message() ];
	}
	$log[] = ( $existing ? 'Atualizado' : 'Criado' ) . ": {$postarr['post_title']} (#{$post_id})";

	foreach ( GA_TRANSFER_META as $key ) {
		if ( array_key_exists( $key, $t['meta'] ?? [] ) ) {
			update_post_meta( $post_id, $key, wp_slash( $t['meta'][ $key ] ) );
		}
	}

	$terms = [];
	foreach ( $t['styles'] ?? [] as $style ) {
		$term = get_term_by( 'slug', $style['slug'] ?? '', 'ga_style' ) ?: get_term_by( 'name', $style['name'] ?? '', 'ga_style' );
		if ( ! $term ) {
			$parent = ! empty( $style['parent'] ) ? get_term_by( 'slug', $style['parent'], 'ga_style' ) : null;
			$new    = wp_insert_term( $style['name'], 'ga_style', [ 'slug' => $style['slug'] ?? '', 'description' => $style['description'] ?? '', 'parent' => $parent ? $parent->term_id : 0 ] );
			if ( is_wp_error( $new ) ) {
				$log[] = "  ERRO no estilo {$style['name']}: " . $new->get_error_message();
				continue;
			}
			$log[]   = "  estilo novo: {$style['name']}";
			$terms[] = (int) $new['term_id'];
			continue;
		}
		$terms[] = (int) $term->term_id;
	}
	wp_set_object_terms( $post_id, $terms, 'ga_style' );

	$scenes = [];
	foreach ( $t['scenes'] ?? [] as $scene ) {
		$scenes[] = [ 'title' => $scene['title'] ?? '', 'desc' => $scene['desc'] ?? '', 'image' => ga_transfer_media( $zip, $scene['image'] ?? null, $post_id, $log ) ];
	}
	update_post_meta( $post_id, '_ga_scenes', wp_slash( $scenes ) );

	$thumb = ga_transfer_media( $zip, $t['thumbnail'] ?? null, $post_id, $log );
	if ( $thumb ) {
		set_post_thumbnail( $post_id, $thumb );
	}

	if ( ! empty( $t['file']['path'] ) ) {
		$current = (string) get_post_meta( $post_id, '_ga_file', true );
		$same    = $current && is_file( ga_private_dir() . "/$current" ) && md5_file( ga_private_dir() . "/$current" ) === ( $t['file']['md5'] ?? '' );
		if ( $same ) {
			$log[] = '  zip: sem mudanças';
		} else {
			$tmp = ga_transfer_extract( $zip, $t['file']['path'], $t['file']['name'] ?? basename( $t['file']['path'] ) );
			if ( is_wp_error( $tmp ) ) {
				$log[] = '  ERRO ' . $tmp->get_error_message();
			} else {
				$stored = ga_store_template_file( $post_id, $tmp );
				$log[]  = is_wp_error( $stored ) ? '  zip: ERRO ' . $stored->get_error_message() : '  zip: ' . basename( $tmp ) . ' (' . size_format( filesize( ga_private_dir() . "/$stored" ), 1 ) . ')';
				ga_transfer_cleanup( $tmp );
			}
		}
	}
	return $log;
}

/**
 * Importa os templates escolhidos de um pacote (todos se $slugs for null).
 *
 * @return string[]|WP_Error Linhas do relatório.
 */
function ga_transfer_import( string $path, ?array $slugs = null ) {
	ga_transfer_prepare();
	$opened = ga_transfer_open( $path );
	if ( is_wp_error( $opened ) ) {
		return $opened;
	}
	[ $zip, $data ] = $opened;
	$log = [];
	foreach ( $data['templates'] as $t ) {
		if ( null !== $slugs && ! in_array( sanitize_title( $t['slug'] ?? '' ), $slugs, true ) ) {
			continue;
		}
		$log = array_merge( $log, ga_transfer_import_template( $zip, $t ) );
	}
	$zip->close();
	// Cache de página (LiteSpeed), se houver: a lista de templates mudou.
	do_action( 'litespeed_purge_all' );
	return $log;
}

/* -------------------------------------------------------------------------- */
/* Painel                                                                     */
/* -------------------------------------------------------------------------- */

add_action( 'admin_menu', function () {
	add_submenu_page( 'edit.php?post_type=ga_template', 'Exportar / Importar templates', 'Exportar / Importar', 'manage_options', 'ga-transfer', 'ga_transfer_page' );
} );

function ga_transfer_url( array $args = [] ): string {
	return add_query_arg( $args, admin_url( 'edit.php?post_type=ga_template&page=ga-transfer' ) );
}

/** Caminho de um pacote na pasta de importação, só se o nome for seguro e o arquivo existir. */
function ga_transfer_package_path( string $name ): string {
	$name = basename( $name );
	if ( ! preg_match( '/^[A-Za-z0-9._-]+\.zip$/', $name ) ) {
		return '';
	}
	$path = ga_transfer_dir() . "/$name";
	return is_file( $path ) ? $path : '';
}

add_action( 'admin_post_ga_transfer_export', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Sem permissão.' );
	}
	check_admin_referer( 'ga_transfer_export' );
	$ids  = array_map( 'intval', (array) ( $_POST['ids'] ?? [] ) );
	$path = ga_transfer_export( $ids );
	if ( is_wp_error( $path ) ) {
		wp_safe_redirect( ga_transfer_url( [ 'ga_error' => rawurlencode( $path->get_error_message() ) ] ) );
		exit;
	}
	nocache_headers();
	header( 'Content-Type: application/zip' );
	header( 'Content-Disposition: attachment; filename="' . ga_transfer_filename( $ids ) . '"' );
	header( 'Content-Length: ' . filesize( $path ) );
	readfile( $path );
	wp_delete_file( $path );
	exit;
} );

add_action( 'admin_post_ga_transfer_upload', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Sem permissão.' );
	}
	check_admin_referer( 'ga_transfer_upload' );
	$picked = ga_transfer_package_path( sanitize_text_field( wp_unslash( $_POST['existing'] ?? '' ) ) );
	if ( $picked ) {
		wp_safe_redirect( ga_transfer_url( [ 'pkg' => basename( $picked ) ] ) );
		exit;
	}
	$file = $_FILES['package'] ?? null; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	if ( ! $file || UPLOAD_ERR_OK !== ( $file['error'] ?? UPLOAD_ERR_NO_FILE ) ) {
		$msg = $file && in_array( $file['error'] ?? 0, [ UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE ], true )
			? 'O pacote passa do limite de envio do servidor. Envie o arquivo por FTP ou pelo Gerenciador de Arquivos para a pasta indicada abaixo e escolha-o na lista.'
			: 'Escolha um pacote .zip para enviar.';
		wp_safe_redirect( ga_transfer_url( [ 'ga_error' => rawurlencode( $msg ) ] ) );
		exit;
	}
	$name = 'upload-' . gmdate( 'Ymd-His' ) . '-' . wp_generate_password( 6, false ) . '.zip';
	$path = ga_transfer_dir() . "/$name";
	if ( ! move_uploaded_file( $file['tmp_name'], $path ) ) {
		wp_safe_redirect( ga_transfer_url( [ 'ga_error' => rawurlencode( 'Não foi possível gravar o pacote no servidor.' ) ] ) );
		exit;
	}
	$opened = ga_transfer_open( $path );
	if ( is_wp_error( $opened ) ) {
		wp_delete_file( $path );
		wp_safe_redirect( ga_transfer_url( [ 'ga_error' => rawurlencode( $opened->get_error_message() ) ] ) );
		exit;
	}
	$opened[0]->close();
	wp_safe_redirect( ga_transfer_url( [ 'pkg' => $name ] ) );
	exit;
} );

add_action( 'admin_post_ga_transfer_import', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Sem permissão.' );
	}
	check_admin_referer( 'ga_transfer_import' );
	$path = ga_transfer_package_path( sanitize_text_field( wp_unslash( $_POST['pkg'] ?? '' ) ) );
	if ( ! $path ) {
		wp_safe_redirect( ga_transfer_url( [ 'ga_error' => rawurlencode( 'Pacote não encontrado. Envie de novo.' ) ] ) );
		exit;
	}
	if ( isset( $_POST['cancel'] ) ) {
		wp_delete_file( $path );
		wp_safe_redirect( ga_transfer_url() );
		exit;
	}
	$slugs = array_map( 'sanitize_title', (array) wp_unslash( $_POST['slugs'] ?? [] ) );
	if ( ! $slugs ) {
		wp_safe_redirect( ga_transfer_url( [ 'pkg' => basename( $path ), 'ga_error' => rawurlencode( 'Marque pelo menos um template.' ) ] ) );
		exit;
	}
	$log = ga_transfer_import( $path, $slugs );
	if ( is_wp_error( $log ) ) {
		wp_safe_redirect( ga_transfer_url( [ 'ga_error' => rawurlencode( $log->get_error_message() ) ] ) );
		exit;
	}
	wp_delete_file( $path );
	set_transient( 'ga_transfer_log_' . get_current_user_id(), $log, HOUR_IN_SECONDS );
	wp_safe_redirect( ga_transfer_url( [ 'done' => 1 ] ) );
	exit;
} );

function ga_transfer_page(): void {
	$error = isset( $_GET['ga_error'] ) ? sanitize_text_field( wp_unslash( $_GET['ga_error'] ) ) : '';
	$pkg   = isset( $_GET['pkg'] ) ? ga_transfer_package_path( sanitize_text_field( wp_unslash( $_GET['pkg'] ) ) ) : '';
	?>
	<div class="wrap">
		<h1>Exportar / Importar templates</h1>
		<p>Leva templates de um site para outro (por exemplo, do local para a produção) sem migrar o banco inteiro. Só os templates do pacote são criados ou atualizados; o resto do site não é tocado.</p>
		<?php if ( $error ) : ?>
			<div class="notice notice-error"><p><?php echo esc_html( $error ); ?></p></div>
		<?php endif; ?>
		<?php
		if ( ! empty( $_GET['done'] ) ) {
			$log = (array) get_transient( 'ga_transfer_log_' . get_current_user_id() );
			delete_transient( 'ga_transfer_log_' . get_current_user_id() );
			$failed = (bool) preg_grep( '/ERRO/', $log );
			printf( '<div class="notice notice-%s"><p><strong>%s</strong></p><pre style="white-space:pre-wrap">%s</pre></div>', $failed ? 'warning' : 'success', $failed ? 'Importação concluída com avisos.' : 'Importação concluída.', esc_html( implode( "\n", $log ) ) );
			echo '<p>Se a produção usa o cache NGINX do cPanel, limpe-o para os visitantes verem a mudança.</p>';
		}

		if ( $pkg ) {
			ga_transfer_review( $pkg );
		} else {
			ga_transfer_forms();
		}
		?>
	</div>
	<?php
}

function ga_transfer_forms(): void {
	$templates = get_posts( [ 'post_type' => 'ga_template', 'post_status' => [ 'publish', 'draft', 'pending', 'private', 'future' ], 'numberposts' => -1, 'orderby' => 'modified', 'order' => 'DESC' ] );
	$waiting   = glob( ga_transfer_dir() . '/*.zip' ) ?: [];
	$statuses  = get_post_statuses();
	?>
	<h2>Exportar</h2>
	<p>Marque os templates e baixe o pacote. Ele leva textos, detalhes, estilos, cenas, imagens e o ZIP de download. A lista está do mais recente para o mais antigo (última alteração).</p>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'ga_transfer_export' ); ?>
		<input type="hidden" name="action" value="ga_transfer_export">
		<table class="widefat striped" style="max-width:900px">
			<thead><tr><td class="check-column"><input type="checkbox" onclick="this.closest('table').querySelectorAll('tbody input').forEach(c=>c.checked=this.checked)"></td><th>Template</th><th>Status</th><th>Última alteração</th><th>ZIP</th></tr></thead>
			<tbody>
			<?php foreach ( $templates as $t ) : ?>
				<?php $file = (string) get_post_meta( $t->ID, '_ga_file', true ); ?>
				<tr>
					<th class="check-column"><input type="checkbox" name="ids[]" value="<?php echo (int) $t->ID; ?>"></th>
					<td><strong><?php echo esc_html( $t->post_title ); ?></strong> <code><?php echo esc_html( $t->post_name ); ?></code></td>
					<td><?php echo esc_html( $statuses[ $t->post_status ] ?? $t->post_status ); ?></td>
					<td><?php echo esc_html( get_post_modified_time( 'd/m/Y H:i', false, $t ) ); ?></td>
					<td><?php echo $file && is_file( ga_private_dir() . "/$file" ) ? esc_html( size_format( filesize( ga_private_dir() . "/$file" ), 1 ) ) : '—'; ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php submit_button( 'Baixar pacote' ); ?>
	</form>

	<h2>Importar</h2>
	<p>Envie um pacote exportado aqui ou em outro site. Antes de aplicar, você vê o que cada template vai virar e escolhe quais importar.</p>
	<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'ga_transfer_upload' ); ?>
		<input type="hidden" name="action" value="ga_transfer_upload">
		<p><input type="file" name="package" accept=".zip,application/zip"> <small>Limite de envio deste servidor: <?php echo esc_html( size_format( wp_max_upload_size() ) ); ?></small></p>
		<?php if ( $waiting ) : ?>
			<p>
				<label for="ga-existing">Ou use um pacote que já está no servidor:</label>
				<select name="existing" id="ga-existing">
					<option value="">—</option>
					<?php foreach ( $waiting as $w ) : ?>
						<option value="<?php echo esc_attr( basename( $w ) ); ?>"><?php echo esc_html( basename( $w ) . ' (' . size_format( filesize( $w ), 1 ) . ')' ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>
		<?php endif; ?>
		<p class="description">Pacote grande demais para o limite? Envie por FTP ou pelo Gerenciador de Arquivos do cPanel para <code><?php echo esc_html( str_replace( ABSPATH, '', ga_transfer_dir() ) ); ?>/</code> e ele aparece aqui para escolher.</p>
		<?php submit_button( 'Revisar pacote', 'secondary' ); ?>
	</form>
	<?php
}

function ga_transfer_review( string $path ): void {
	$opened = ga_transfer_open( $path );
	if ( is_wp_error( $opened ) ) {
		echo '<div class="notice notice-error"><p>' . esc_html( $opened->get_error_message() ) . '</p></div>';
		return;
	}
	[ $zip, $data ] = $opened;
	$zip->close();
	$rows     = ga_transfer_preview( $data );
	$statuses = get_post_statuses();
	?>
	<h2>Revisar pacote</h2>
	<p>Pacote <code><?php echo esc_html( basename( $path ) ); ?></code>, exportado de <code><?php echo esc_html( $data['source'] ?? '?' ); ?></code> em <?php echo esc_html( get_date_from_gmt( $data['created'] ?? '', 'd/m/Y H:i' ) ); ?>.</p>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'ga_transfer_import' ); ?>
		<input type="hidden" name="action" value="ga_transfer_import">
		<input type="hidden" name="pkg" value="<?php echo esc_attr( basename( $path ) ); ?>">
		<table class="widefat striped" style="max-width:1000px">
			<thead><tr><td class="check-column"></td><th>Template</th><th>O que acontece</th><th>Arquivos</th></tr></thead>
			<tbody>
			<?php foreach ( $rows as $r ) : ?>
				<tr>
					<th class="check-column"><input type="checkbox" name="slugs[]" value="<?php echo esc_attr( $r['slug'] ); ?>" checked></th>
					<td><strong><?php echo esc_html( $r['title'] ); ?></strong> <code><?php echo esc_html( $r['slug'] ); ?></code><br><small><?php echo esc_html( $statuses[ $r['status'] ] ?? $r['status'] ); ?></small></td>
					<td>
						<?php if ( $r['exists'] ) : ?>
							Atualiza o template <a href="<?php echo esc_url( $r['url'] ); ?>" target="_blank">que já existe</a> (mantém ordem, data, downloads e venda)
							<?php if ( $r['newer'] ) : ?><br><strong style="color:#b32d2e">Atenção: a cópia deste site foi alterada depois da exportação e será sobrescrita.</strong><?php endif; ?>
						<?php else : ?>
							<strong>Novo</strong>: cria o template
						<?php endif; ?>
					</td>
					<td><?php echo esc_html( $r['summary'] ); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<p>Nada além dos templates marcados é alterado. Imagens iguais às que já estão na biblioteca são reaproveitadas.</p>
		<p>
			<?php submit_button( 'Importar selecionados', 'primary', 'submit', false ); ?>
			<?php submit_button( 'Cancelar e apagar o pacote', 'secondary', 'cancel', false ); ?>
		</p>
	</form>
	<?php
}

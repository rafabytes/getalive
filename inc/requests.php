<?php
/**
 * Solicitações de novos templates feitas pelos usuários.
 */

defined( 'ABSPATH' ) || exit;

function ga_request_statuses(): array {
	return [
		'received'   => 'Recebida',
		'reviewing'  => 'Em análise',
		'production' => 'Em produção',
		'published'  => 'Publicada',
		'declined'   => 'Não vamos fazer',
	];
}

function ga_request_options(): array {
	return [
		'vibes'     => [ 'Retrô / pixel art', 'Minimalista', 'Neon / synthwave', 'Anime / kawaii', 'Aconchegante / lo-fi', 'Gamer / esports', 'Terror', 'Natureza', 'Corporativo / podcast' ],
		'scenes'    => [ 'Começando', 'Jogo', 'Conversa', 'React', 'Já volto', 'Encerrando', 'Alertas', 'Chat na tela', 'Painéis da Twitch' ],
		'platforms' => [ 'Twitch', 'YouTube', 'Kick', 'TikTok' ],
	];
}

/**
 * Cria a solicitação a partir de dados já validados.
 */
function ga_create_request( int $user_id, array $data ) {
	$id = wp_insert_post( [
		'post_type'    => 'ga_request',
		'post_status'  => 'publish',
		'post_author'  => $user_id,
		'post_title'   => $data['title'],
		'post_content' => $data['description'],
	], true );

	if ( is_wp_error( $id ) ) {
		return $id;
	}

	update_post_meta( $id, '_ga_status', 'received' );
	update_post_meta( $id, '_ga_vibes', $data['vibes'] );
	update_post_meta( $id, '_ga_scenes', $data['scenes'] );
	update_post_meta( $id, '_ga_platforms', $data['platforms'] );
	update_post_meta( $id, '_ga_references', $data['references'] );

	ga_notify_request( $id );
	return $id;
}

function ga_notify_request( int $id ): void {
	$post = get_post( $id );
	$user = get_userdata( $post->post_author );
	$to   = ga_option( 'request_email', ga_contact_email() );
	$list = fn( $key ) => implode( ', ', (array) get_post_meta( $id, $key, true ) ) ?: '—';

	$body  = "Nova solicitação de template\n\n";
	$body .= "Ideia: {$post->post_title}\n";
	$body .= "De: {$user->display_name} <{$user->user_email}>\n";
	$body .= 'Estilo: ' . $list( '_ga_vibes' ) . "\n";
	$body .= 'Cenas: ' . $list( '_ga_scenes' ) . "\n";
	$body .= 'Plataformas: ' . $list( '_ga_platforms' ) . "\n\n";
	$body .= wp_strip_all_tags( $post->post_content ) . "\n\n";
	$refs  = (array) get_post_meta( $id, '_ga_references', true );
	if ( $refs ) {
		$body .= "Referências:\n" . implode( "\n", $refs ) . "\n\n";
	}
	$body .= 'Ver no painel: ' . admin_url( "post.php?post={$id}&action=edit" );

	wp_mail( $to, '[get a live] Nova solicitação: ' . $post->post_title, $body, [ 'Reply-To: ' . $user->display_name . ' <' . $user->user_email . '>' ] );
}

function ga_user_requests( int $user_id = 0 ): array {
	return get_posts( [
		'post_type'      => 'ga_request',
		'author'         => $user_id ?: get_current_user_id(),
		'posts_per_page' => 50,
		'post_status'    => 'publish',
	] );
}

/* ---------------------------------------------------------------------------
 * Painel
 * ------------------------------------------------------------------------- */

add_action( 'add_meta_boxes_ga_request', function () {
	add_meta_box( 'ga_request_info', 'Detalhes', 'ga_metabox_request', 'ga_request', 'side', 'high' );
} );

function ga_metabox_request( WP_Post $post ): void {
	wp_nonce_field( 'ga_save_request', 'ga_request_nonce' );
	$status = get_post_meta( $post->ID, '_ga_status', true ) ?: 'received';
	$user   = get_userdata( $post->post_author );
	$list   = fn( $key ) => implode( ', ', (array) get_post_meta( $post->ID, $key, true ) ) ?: '—';
	?>
	<p><label for="ga_status"><strong>Status</strong></label><br>
		<select id="ga_status" name="ga_status" style="width:100%">
			<?php foreach ( ga_request_statuses() as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $status, $key ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<p><strong>Quem pediu</strong><br><?php echo $user ? esc_html( $user->display_name ) . '<br><a href="mailto:' . esc_attr( $user->user_email ) . '">' . esc_html( $user->user_email ) . '</a>' : '—'; ?></p>
	<p><strong>Estilo</strong><br><?php echo esc_html( $list( '_ga_vibes' ) ); ?></p>
	<p><strong>Cenas</strong><br><?php echo esc_html( $list( '_ga_scenes' ) ); ?></p>
	<p><strong>Plataformas</strong><br><?php echo esc_html( $list( '_ga_platforms' ) ); ?></p>
	<?php
	$refs = (array) get_post_meta( $post->ID, '_ga_references', true );
	if ( $refs ) {
		echo '<p><strong>Referências</strong><br>';
		foreach ( $refs as $ref ) {
			echo '<a href="' . esc_url( $ref ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $ref ) . '</a><br>';
		}
		echo '</p>';
	}
}

add_action( 'save_post_ga_request', function ( $post_id ) {
	if ( ! isset( $_POST['ga_request_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['ga_request_nonce'] ), 'ga_save_request' ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$status = sanitize_key( $_POST['ga_status'] ?? '' );
	if ( isset( ga_request_statuses()[ $status ] ) ) {
		update_post_meta( $post_id, '_ga_status', $status );
	}
} );

add_filter( 'manage_ga_request_posts_columns', function ( $cols ) {
	return [
		'cb'        => $cols['cb'],
		'title'     => 'Ideia',
		'ga_author' => 'Quem pediu',
		'ga_status' => 'Status',
		'date'      => 'Data',
	];
} );

add_action( 'manage_ga_request_posts_custom_column', function ( $col, $post_id ) {
	if ( 'ga_author' === $col ) {
		$user = get_userdata( (int) get_post_field( 'post_author', $post_id ) );
		echo $user ? esc_html( $user->display_name ) . '<br><small>' . esc_html( $user->user_email ) . '</small>' : '—';
	} elseif ( 'ga_status' === $col ) {
		$status = get_post_meta( $post_id, '_ga_status', true ) ?: 'received';
		echo esc_html( ga_request_statuses()[ $status ] ?? $status );
	}
}, 10, 2 );

/**
 * Contador de solicitações novas no menu.
 */
add_action( 'admin_menu', function () {
	global $submenu;
	$count = count( get_posts( [
		'post_type'      => 'ga_request',
		'posts_per_page' => 99,
		'fields'         => 'ids',
		'meta_key'       => '_ga_status',
		'meta_value'     => 'received',
	] ) );
	if ( ! $count || empty( $submenu['edit.php?post_type=ga_template'] ) ) {
		return;
	}
	foreach ( $submenu['edit.php?post_type=ga_template'] as &$item ) {
		if ( 'edit.php?post_type=ga_request' === $item[2] ) {
			$item[0] .= ' <span class="awaiting-mod">' . $count . '</span>';
		}
	}
}, 99 );

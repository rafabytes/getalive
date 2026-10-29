<?php
/**
 * Campos do template no painel: detalhes, cenas, arquivo e venda.
 */

defined( 'ABSPATH' ) || exit;

// Editor clássico para templates: os campos e o upload do ZIP ficam numa tela só.
add_filter( 'use_block_editor_for_post_type', fn( $use, $type ) => 'ga_template' === $type ? false : $use, 10, 2 );

add_action( 'post_edit_form_tag', function ( $post ) {
	if ( 'ga_template' === $post->post_type ) {
		echo ' enctype="multipart/form-data"';
	}
} );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	$screen = get_current_screen();
	if ( ! $screen || 'ga_template' !== $screen->post_type || ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'ga-admin', GA_URI . '/assets/admin.js', [ 'jquery', 'jquery-ui-sortable', 'wp-color-picker' ], GA_VERSION, true );
	wp_enqueue_style( 'ga-admin', GA_URI . '/assets/admin.css', [], GA_VERSION );
} );

add_action( 'add_meta_boxes_ga_template', function () {
	add_meta_box( 'ga_details', 'Detalhes do template', 'ga_metabox_details', 'ga_template', 'normal', 'high' );
	add_meta_box( 'ga_scenes', 'Cenas', 'ga_metabox_scenes', 'ga_template', 'normal', 'high' );
	add_meta_box( 'ga_file', 'Arquivo para download', 'ga_metabox_file', 'ga_template', 'side', 'high' );
	add_meta_box( 'ga_sale', 'Venda (premium)', 'ga_metabox_sale', 'ga_template', 'side', 'default' );
} );

function ga_metabox_details( WP_Post $post ): void {
	wp_nonce_field( 'ga_save_template', 'ga_template_nonce' );
	$m         = fn( $k ) => get_post_meta( $post->ID, $k, true );
	$platforms = (array) $m( '_ga_platforms' );
	?>
	<div class="ga-fields">
		<p class="ga-field ga-field--wide">
			<label for="ga_tagline">Chamada curta</label>
			<input type="text" id="ga_tagline" name="ga_tagline" value="<?php echo esc_attr( $m( '_ga_tagline' ) ); ?>" placeholder="Synthwave em pixel art com cidade neon animada">
		</p>
		<p class="ga-field">
			<label for="ga_version">Versão</label>
			<input type="text" id="ga_version" name="ga_version" value="<?php echo esc_attr( $m( '_ga_version' ) ); ?>" placeholder="1.0">
		</p>
		<p class="ga-field">
			<label for="ga_resolution">Resolução</label>
			<input type="text" id="ga_resolution" name="ga_resolution" value="<?php echo esc_attr( $m( '_ga_resolution' ) ); ?>" placeholder="1920×1080">
		</p>
		<p class="ga-field">
			<label for="ga_obs_version">OBS mínimo</label>
			<input type="text" id="ga_obs_version" name="ga_obs_version" value="<?php echo esc_attr( $m( '_ga_obs_version' ) ); ?>" placeholder="30">
		</p>
		<p class="ga-field">
			<label for="ga_accent">Cor de destaque</label>
			<input type="text" id="ga_accent" name="ga_accent" class="ga-color" value="<?php echo esc_attr( $m( '_ga_accent' ) ?: '#7c3aff' ); ?>">
		</p>
		<fieldset class="ga-field ga-field--wide">
			<legend>Plataformas</legend>
			<?php foreach ( [ 'twitch', 'youtube', 'kick', 'tiktok' ] as $p ) : ?>
				<label class="ga-check"><input type="checkbox" name="ga_platforms[]" value="<?php echo esc_attr( $p ); ?>" <?php checked( in_array( $p, $platforms, true ) ); ?>> <?php echo esc_html( ga_platform_label( $p ) ); ?></label>
			<?php endforeach; ?>
		</fieldset>
		<p class="ga-field ga-field--wide">
			<label class="ga-check"><input type="checkbox" name="ga_featured" value="1" <?php checked( $m( '_ga_featured' ) ); ?>> Destacar na página inicial</label>
		</p>
		<p class="ga-field ga-field--wide">
			<label for="ga_features">Recursos <small>(um por linha)</small></label>
			<textarea id="ga_features" name="ga_features" rows="6"><?php echo esc_textarea( $m( '_ga_features' ) ); ?></textarea>
		</p>
		<p class="ga-field">
			<label for="ga_requirements">Requisitos <small>(um por linha)</small></label>
			<textarea id="ga_requirements" name="ga_requirements" rows="5"><?php echo esc_textarea( $m( '_ga_requirements' ) ); ?></textarea>
		</p>
		<p class="ga-field">
			<label for="ga_install">Passos de instalação <small>(um por linha, vazio usa o padrão)</small></label>
			<textarea id="ga_install" name="ga_install" rows="5"><?php echo esc_textarea( $m( '_ga_install' ) ); ?></textarea>
		</p>
	</div>
	<?php
}

function ga_metabox_scenes( WP_Post $post ): void {
	$scenes = array_values( (array) get_post_meta( $post->ID, '_ga_scenes', true ) );
	?>
	<p class="description">Cada cena aparece na galeria e na lista de cenas da página do template. Arraste para reordenar.</p>
	<ol class="ga-scenes" data-next="<?php echo esc_attr( count( $scenes ) ); ?>">
		<?php foreach ( $scenes as $i => $scene ) : ?>
			<?php ga_scene_row( $i, $scene ); ?>
		<?php endforeach; ?>
	</ol>
	<script type="text/html" id="tmpl-ga-scene"><?php ga_scene_row( '__i__', [] ); ?></script>
	<p><button type="button" class="button ga-scene-add">+ Adicionar cena</button></p>
	<?php
}

function ga_scene_row( $i, array $scene ): void {
	$image = (int) ( $scene['image'] ?? 0 );
	$thumb = $image ? wp_get_attachment_image_url( $image, 'thumbnail' ) : '';
	?>
	<li class="ga-scene">
		<span class="ga-scene__handle dashicons dashicons-move" aria-hidden="true"></span>
		<button type="button" class="ga-scene__image" title="Escolher imagem">
			<?php if ( $thumb ) : ?><img src="<?php echo esc_url( $thumb ); ?>" alt=""><?php else : ?><span>Imagem</span><?php endif; ?>
		</button>
		<input type="hidden" name="ga_scenes[<?php echo esc_attr( $i ); ?>][image]" value="<?php echo esc_attr( $image ?: '' ); ?>" class="ga-scene__image-id">
		<div class="ga-scene__text">
			<input type="text" name="ga_scenes[<?php echo esc_attr( $i ); ?>][title]" value="<?php echo esc_attr( $scene['title'] ?? '' ); ?>" placeholder="Nome da cena (ex.: Começando)">
			<textarea name="ga_scenes[<?php echo esc_attr( $i ); ?>][desc]" rows="2" placeholder="Para que serve esta cena"><?php echo esc_textarea( $scene['desc'] ?? '' ); ?></textarea>
		</div>
		<button type="button" class="ga-scene__remove button-link" aria-label="Remover cena"><span class="dashicons dashicons-trash"></span></button>
	</li>
	<?php
}

function ga_metabox_file( WP_Post $post ): void {
	$file = (string) get_post_meta( $post->ID, '_ga_file', true );
	$name = (string) get_post_meta( $post->ID, '_ga_file_name', true );
	$size = (int) get_post_meta( $post->ID, '_ga_file_size', true );
	$ok   = $file && file_exists( ga_private_dir() . '/' . $file );
	?>
	<?php if ( $ok ) : ?>
		<p class="ga-file-current"><span class="dashicons dashicons-media-archive"></span> <strong><?php echo esc_html( $name ); ?></strong><br><small><?php echo esc_html( size_format( $size, 1 ) ); ?> · <?php echo esc_html( (int) get_post_meta( $post->ID, '_ga_downloads', true ) ); ?> downloads</small></p>
	<?php elseif ( $file ) : ?>
		<p class="ga-file-missing">O arquivo <code><?php echo esc_html( $file ); ?></code> não foi encontrado na pasta protegida.</p>
	<?php else : ?>
		<p>Nenhum arquivo enviado.</p>
	<?php endif; ?>
	<p>
		<label for="ga_file_upload"><strong><?php echo $ok ? 'Substituir ZIP' : 'Enviar ZIP'; ?></strong></label><br>
		<input type="file" id="ga_file_upload" name="ga_file_upload" accept=".zip,application/zip">
	</p>
	<p class="description">O arquivo fica numa pasta protegida e só é entregue para usuários logados.</p>
	<?php
}

function ga_metabox_sale( WP_Post $post ): void {
	$premium = (bool) get_post_meta( $post->ID, '_ga_premium', true );
	$price   = (string) get_post_meta( $post->ID, '_ga_price', true );
	$product = (int) get_post_meta( $post->ID, '_ga_product_id', true );
	$wc      = class_exists( 'WooCommerce' );
	?>
	<p><label class="ga-check"><input type="checkbox" name="ga_premium" value="1" <?php checked( $premium ); ?>> Template pago</label></p>
	<p class="ga-field">
		<label for="ga_price">Preço (R$)</label>
		<input type="text" id="ga_price" name="ga_price" value="<?php echo esc_attr( $price ); ?>" placeholder="29,90" inputmode="decimal">
	</p>
	<?php if ( $product ) : ?>
		<p>Produto vinculado: <a href="<?php echo esc_url( get_edit_post_link( $product ) ?: '#' ); ?>">#<?php echo esc_html( $product ); ?></a></p>
	<?php endif; ?>
	<p class="description">
		<?php if ( ! $wc ) : ?>
			Templates pagos ficam <strong>ocultos no site</strong> até o WooCommerce estar ativo e a venda ligada em Configurações → get a live.
		<?php elseif ( ! ga_premium_enabled() ) : ?>
			WooCommerce ativo. Ligue a venda em Configurações → get a live para publicar templates pagos.
		<?php else : ?>
			Ao salvar, um produto virtual é criado ou atualizado no WooCommerce com este preço.
		<?php endif; ?>
	</p>
	<?php
}

add_action( 'save_post_ga_template', function ( int $post_id ) {
	if ( ! isset( $_POST['ga_template_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['ga_template_nonce'] ), 'ga_save_template' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// update_post_meta remove barras; wp_slash preserva caminhos como "Documentos\Temas OBS".
	$text = [ 'tagline', 'version', 'resolution', 'obs_version', 'price' ];
	foreach ( $text as $key ) {
		update_post_meta( $post_id, "_ga_{$key}", wp_slash( sanitize_text_field( wp_unslash( $_POST[ "ga_{$key}" ] ?? '' ) ) ) );
	}
	foreach ( [ 'features', 'requirements', 'install' ] as $key ) {
		update_post_meta( $post_id, "_ga_{$key}", wp_slash( sanitize_textarea_field( wp_unslash( $_POST[ "ga_{$key}" ] ?? '' ) ) ) );
	}
	update_post_meta( $post_id, '_ga_accent', sanitize_hex_color( wp_unslash( $_POST['ga_accent'] ?? '' ) ) ?: '#7c3aff' );
	update_post_meta( $post_id, '_ga_featured', empty( $_POST['ga_featured'] ) ? '' : '1' );
	update_post_meta( $post_id, '_ga_premium', empty( $_POST['ga_premium'] ) ? '' : '1' );

	$allowed   = [ 'twitch', 'youtube', 'kick', 'tiktok' ];
	$platforms = array_values( array_intersect( $allowed, array_map( 'sanitize_key', (array) wp_unslash( $_POST['ga_platforms'] ?? [] ) ) ) );
	update_post_meta( $post_id, '_ga_platforms', $platforms );

	$scenes = [];
	foreach ( (array) wp_unslash( $_POST['ga_scenes'] ?? [] ) as $scene ) {
		$title = sanitize_text_field( $scene['title'] ?? '' );
		if ( '' === $title ) {
			continue;
		}
		$scenes[] = [
			'title' => $title,
			'desc'  => sanitize_textarea_field( $scene['desc'] ?? '' ),
			'image' => absint( $scene['image'] ?? 0 ),
		];
	}
	update_post_meta( $post_id, '_ga_scenes', wp_slash( $scenes ) );

	if ( ! empty( $_FILES['ga_file_upload']['name'] ) ) {
		$result = ga_store_template_file( $post_id, $_FILES['ga_file_upload'] );
		if ( is_wp_error( $result ) ) {
			set_transient( 'ga_file_error_' . get_current_user_id(), $result->get_error_message(), 60 );
		}
	}

	do_action( 'ga_template_saved', $post_id );
} );

add_action( 'admin_notices', function () {
	$key   = 'ga_file_error_' . get_current_user_id();
	$error = get_transient( $key );
	if ( $error ) {
		delete_transient( $key );
		printf( '<div class="notice notice-error"><p><strong>Arquivo do template:</strong> %s</p></div>', esc_html( $error ) );
	}
} );

/**
 * Colunas da listagem de templates.
 */
add_filter( 'manage_ga_template_posts_columns', function ( $cols ) {
	$new = [];
	foreach ( $cols as $key => $label ) {
		if ( 'title' === $key ) {
			$new['ga_thumb'] = '';
		}
		$new[ $key ] = $label;
		if ( 'title' === $key ) {
			$new['ga_downloads'] = 'Downloads';
			$new['ga_file']      = 'Arquivo';
		}
	}
	return $new;
} );

add_action( 'manage_ga_template_posts_custom_column', function ( $col, $post_id ) {
	if ( 'ga_thumb' === $col ) {
		echo get_the_post_thumbnail( $post_id, [ 96, 54 ], [ 'style' => 'width:96px;height:54px;object-fit:cover;border-radius:4px' ] );
	} elseif ( 'ga_downloads' === $col ) {
		$count = (int) get_post_meta( $post_id, '_ga_downloads', true );
		echo $count && current_user_can( 'list_users' )
			? '<a href="' . esc_url( ga_downloaders_admin_url( $post_id ) ) . '" title="Ver quem baixou">' . esc_html( number_format_i18n( $count ) ) . '</a>'
			: esc_html( number_format_i18n( $count ) );
		if ( get_post_meta( $post_id, '_ga_premium', true ) ) {
			echo ' <span class="ga-badge">pago</span>';
		}
	} elseif ( 'ga_file' === $col ) {
		$file = (string) get_post_meta( $post_id, '_ga_file', true );
		echo $file && file_exists( ga_private_dir() . '/' . $file )
			? esc_html( size_format( (int) get_post_meta( $post_id, '_ga_file_size', true ), 1 ) )
			: '<span style="color:#b32d2e">sem arquivo</span>';
	}
}, 10, 2 );

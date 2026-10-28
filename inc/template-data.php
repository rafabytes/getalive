<?php
/**
 * Leitura dos dados de um template (metadados, cenas, imagens) num formato só.
 */

defined( 'ABSPATH' ) || exit;

function ga_lines( $text ): array {
	return array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) $text ) ) ) );
}

function ga_template( $post = null ): array {
	$post = get_post( $post );
	if ( ! $post ) {
		return [];
	}
	$id = $post->ID;

	$scenes = array_values( array_filter( (array) get_post_meta( $id, '_ga_scenes', true ), fn( $s ) => ! empty( $s['title'] ) ) );
	foreach ( $scenes as &$scene ) {
		$image          = (int) ( $scene['image'] ?? 0 );
		$scene['image'] = $image;
		$scene['src']   = $image ? wp_get_attachment_image_url( $image, 'ga-stage' ) : '';
		$scene['thumb'] = $image ? wp_get_attachment_image_url( $image, 'ga-card' ) : '';
		$scene['desc']  = $scene['desc'] ?? '';
	}
	unset( $scene );

	$file = (string) get_post_meta( $id, '_ga_file', true );

	return [
		'id'           => $id,
		'title'        => get_the_title( $post ),
		'url'          => get_permalink( $post ),
		'tagline'      => (string) get_post_meta( $id, '_ga_tagline', true ),
		'version'      => (string) get_post_meta( $id, '_ga_version', true ),
		'resolution'   => (string) get_post_meta( $id, '_ga_resolution', true ) ?: '1920×1080',
		'obs_version'  => (string) get_post_meta( $id, '_ga_obs_version', true ) ?: '30',
		'platforms'    => array_values( (array) get_post_meta( $id, '_ga_platforms', true ) ),
		'features'     => ga_lines( get_post_meta( $id, '_ga_features', true ) ),
		'requirements' => ga_lines( get_post_meta( $id, '_ga_requirements', true ) ),
		'install'      => ga_lines( get_post_meta( $id, '_ga_install', true ) ),
		'scenes'       => $scenes,
		'accent'       => sanitize_hex_color( get_post_meta( $id, '_ga_accent', true ) ) ?: '#7c3aff',
		'featured'     => (bool) get_post_meta( $id, '_ga_featured', true ),
		'downloads'    => (int) get_post_meta( $id, '_ga_downloads', true ),
		'has_file'     => $file && file_exists( ga_private_dir() . '/' . $file ),
		'file_name'    => (string) get_post_meta( $id, '_ga_file_name', true ),
		'file_size'    => (int) get_post_meta( $id, '_ga_file_size', true ),
		'premium'      => (bool) get_post_meta( $id, '_ga_premium', true ),
		'product_id'   => (int) get_post_meta( $id, '_ga_product_id', true ),
		'styles'       => array_values( array_map(
			fn( $t ) => [ 'slug' => $t->slug, 'name' => $t->name ],
			get_the_terms( $id, 'ga_style' ) ?: []
		) ),
		'thumbnail'    => get_the_post_thumbnail_url( $post, 'ga-card' ) ?: ( $scenes[0]['thumb'] ?? '' ),
		'updated'      => get_the_modified_date( 'U', $post ),
	];
}

/**
 * Imagens para o hover dos cards: capa + cenas, sem repetição.
 */
function ga_template_images( array $t, string $key = 'thumb' ): array {
	$images = array_filter( array_merge( [ $t['thumbnail'] ], array_column( $t['scenes'], $key ) ) );
	return array_values( array_unique( $images ) );
}

/**
 * Dados enxutos para os componentes React (listagem e favoritos).
 */
function ga_template_card_data( $post ): array {
	$t = ga_template( $post );
	return [
		'id'        => $t['id'],
		'title'     => $t['title'],
		'url'       => $t['url'],
		'tagline'   => $t['tagline'],
		'images'    => ga_template_images( $t ),
		'styles'    => $t['styles'],
		'scenes'    => count( $t['scenes'] ),
		'platforms' => $t['platforms'],
		'downloads' => $t['downloads'],
		'accent'    => $t['accent'],
		'date'      => (int) get_post_time( 'U', true, $post ),
		'favorite'  => ga_is_favorite( $t['id'] ),
		'price'     => ga_premium_enabled() && $t['premium'] ? ga_premium_price_html( $t['id'] ) : '',
	];
}

/**
 * Templates premium só aparecem quando a venda estiver ligada (WooCommerce + opção).
 */
function ga_hidden_template_meta_query(): array {
	return [
		'relation' => 'OR',
		[ 'key' => '_ga_premium', 'compare' => 'NOT EXISTS' ],
		[ 'key' => '_ga_premium', 'value' => '1', 'compare' => '!=' ],
	];
}

add_action( 'pre_get_posts', function ( WP_Query $q ) {
	if ( is_admin() ) {
		return;
	}
	$types = (array) $q->get( 'post_type' );
	$is_tpl_query = in_array( 'ga_template', $types, true ) || $q->is_tax( 'ga_style' );

	if ( $is_tpl_query && ! ga_premium_enabled() ) {
		$meta   = (array) $q->get( 'meta_query' );
		$meta[] = ga_hidden_template_meta_query();
		$q->set( 'meta_query', $meta );
	}

	if ( $q->is_main_query() && ( $q->is_post_type_archive( 'ga_template' ) || $q->is_tax( 'ga_style' ) ) ) {
		// A listagem filtra no navegador; carrega o catálogo inteiro.
		$q->set( 'posts_per_page', 200 );
	}
} );

add_filter( 'rest_ga_template_query', function ( $args ) {
	if ( ! ga_premium_enabled() ) {
		$args['meta_query']   = (array) ( $args['meta_query'] ?? [] );
		$args['meta_query'][] = ga_hidden_template_meta_query();
	}
	return $args;
} );

/**
 * Página individual de um template premium com a venda desligada: 404.
 */
add_action( 'template_redirect', function () {
	if ( is_singular( 'ga_template' ) && ! ga_premium_enabled() && get_post_meta( get_queried_object_id(), '_ga_premium', true ) ) {
		if ( current_user_can( 'edit_post', get_queried_object_id() ) ) {
			return;
		}
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
	}
} );

function ga_query_templates( array $args = [] ): array {
	return get_posts( array_merge( [
		'post_type'        => 'ga_template',
		'posts_per_page'   => -1,
		'post_status'      => 'publish',
		'orderby'          => GA_TEMPLATE_ORDER,
		'suppress_filters' => false,
	], $args ) );
}

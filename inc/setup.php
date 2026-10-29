<?php
/**
 * Suportes do tema, menus, tamanhos de imagem e ajustes gerais.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', function () {
	load_theme_textdomain( 'getalive', GA_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', [ 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ] );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'editor-styles' );
	// Preparado para a loja (templates pagos) quando o WooCommerce for ativado.
	add_theme_support( 'woocommerce' );

	register_nav_menus( [
		'primary' => 'Menu principal',
		'footer'  => 'Menu do rodapé',
	] );

	// Degraus 16:9 do srcset: do card no celular até o palco em tela retina.
	add_image_size( 'ga-thumb', 640, 360, true );
	add_image_size( 'ga-card', 960, 540, true );
	add_image_size( 'ga-hd', 1280, 720, true );
	add_image_size( 'ga-stage', 1920, 1080, true );
} );

/**
 * Assinantes não veem a barra de administração nem o painel.
 */
add_filter( 'show_admin_bar', function ( $show ) {
	return current_user_can( 'edit_posts' ) ? $show : false;
} );

add_action( 'admin_init', function () {
	if ( wp_doing_ajax() || current_user_can( 'edit_posts' ) || ! is_user_logged_in() ) {
		return;
	}
	wp_safe_redirect( ga_page_url( 'account' ) );
	exit;
} );

add_filter( 'body_class', function ( $classes ) {
	$classes[] = is_user_logged_in() ? 'is-logged-in' : 'is-logged-out';
	if ( ga_is_page( 'login' ) || ga_is_page( 'register' ) ) {
		$classes[] = 'is-auth';
	}
	return $classes;
} );

/**
 * Remove o lixo padrão do <head>.
 */
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );

add_filter( 'excerpt_length', fn() => 24 );
add_filter( 'excerpt_more', fn() => '…' );

/**
 * Meta tags de compartilhamento básicas.
 */
add_action( 'wp_head', function () {
	$title = wp_get_document_title();
	$desc  = get_bloginfo( 'description' ) ?: 'Layouts de live animados para o OBS Studio. Baixe, instale em dois cliques e entre no ar.';
	$image = '';

	if ( is_singular() ) {
		$post = get_queried_object();
		if ( has_excerpt( $post ) || 'ga_template' === $post->post_type ) {
			$desc = wp_strip_all_tags( get_the_excerpt( $post ) ) ?: $desc;
		}
		$image = get_the_post_thumbnail_url( $post, 'ga-stage' ) ?: '';
	}

	printf( '<meta name="description" content="%s">' . "\n", esc_attr( $desc ) );
	printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );
	printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $desc ) );
	printf( '<meta property="og:type" content="%s">' . "\n", is_singular( 'ga_template' ) ? 'product' : 'website' );
	printf( '<meta property="og:url" content="%s">' . "\n", esc_url( ga_current_url() ) );
	printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( get_bloginfo( 'name' ) ) );
	if ( $image ) {
		printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $image ) );
		echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
	}
	echo '<meta name="theme-color" content="#08080a">' . "\n";
}, 2 );

/**
 * Favicon em SVG gerado a partir da marca, quando o site não define um ícone.
 */
add_action( 'wp_head', function () {
	if ( has_site_icon() ) {
		return;
	}
	echo '<link rel="icon" href="' . esc_url( GA_URI . '/assets/favicon.svg' ) . '" type="image/svg+xml">' . "\n";
}, 3 );

/**
 * Links de menu: itens padrão quando nenhum menu foi atribuído.
 */
function ga_default_menu_items( string $location ): array {
	if ( 'footer' === $location ) {
		return [
			[ 'label' => 'Sobre', 'url' => ga_page_url( 'about' ) ],
			[ 'label' => 'Privacidade', 'url' => ga_page_url( 'privacy' ) ],
			[ 'label' => 'Solicitar template', 'url' => ga_page_url( 'request' ) ],
		];
	}
	return [
		[ 'label' => 'Templates', 'url' => ga_page_url( 'templates' ) ],
		[ 'label' => 'Como instalar', 'url' => ga_page_url( 'install' ) ],
		[ 'label' => 'Solicitar', 'url' => ga_page_url( 'request' ) ],
		[ 'label' => 'Sobre', 'url' => ga_page_url( 'about' ) ],
	];
}

/**
 * Itens de um local de menu como lista simples (label/url), para marcação própria.
 */
function ga_menu_items( string $location ): array {
	$locations = get_nav_menu_locations();
	if ( empty( $locations[ $location ] ) ) {
		return ga_default_menu_items( $location );
	}
	$items = wp_get_nav_menu_items( $locations[ $location ] ) ?: [];
	return array_map(
		fn( $item ) => [ 'label' => $item->title, 'url' => $item->url ],
		array_filter( $items, fn( $item ) => ! $item->menu_item_parent )
	);
}

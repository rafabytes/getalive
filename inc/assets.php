<?php
/**
 * Enfileira os bundles do Parcel (dist/) e expõe a configuração para o JS.
 */

defined( 'ABSPATH' ) || exit;

function ga_asset_version( string $file ): string {
	$path = GA_DIR . '/dist/' . $file;
	return file_exists( $path ) ? (string) filemtime( $path ) : GA_VERSION;
}

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'getalive', GA_URI . '/dist/js/main.css', [], ga_asset_version( 'js/main.css' ) );
	wp_enqueue_script( 'getalive', GA_URI . '/dist/js/main.js', [], ga_asset_version( 'js/main.js' ), [
		'in_footer' => true,
		'strategy'  => 'defer',
	] );

	wp_add_inline_script( 'getalive', 'window.GA = ' . wp_json_encode( ga_js_config() ) . ';', 'before' );

	// O tema não usa blocos no front; remove o CSS global do editor.
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'global-styles' );
	wp_dequeue_style( 'classic-theme-styles' );
}, 20 );

/**
 * LiteSpeed Cache não pode juntar nem atrasar o bundle: o Parcel carrega os chunks
 * (React, AuthApp...) por caminho relativo ao main.js, que quebra se ele for movido
 * para wp-content/litespeed/js/. O $tag inclui a config window.GA, que vai junto.
 */
add_filter( 'script_loader_tag', function ( $tag, $handle ) {
	if ( 'getalive' !== $handle ) {
		return $tag;
	}
	return str_replace( '<script ', '<script data-no-optimize="1" data-no-defer="1" ', $tag );
}, 10, 2 );

function ga_js_config(): array {
	$user = wp_get_current_user();
	return [
		'rest'      => esc_url_raw( rest_url( 'getalive/v1/' ) ),
		'nonce'     => wp_create_nonce( 'wp_rest' ),
		'home'      => home_url( '/' ),
		'loggedIn'  => $user->exists(),
		'user'      => $user->exists() ? [ 'name' => ga_user_first_name( $user ) ] : null,
		'pages'     => [
			'login'     => ga_page_url( 'login' ),
			'register'  => ga_page_url( 'register' ),
			'account'   => ga_page_url( 'account' ),
			'privacy'   => ga_page_url( 'privacy' ),
			'request'   => ga_page_url( 'request' ),
			'templates' => ga_page_url( 'templates' ),
		],
		'googleClientId' => ga_option( 'google_client_id' ),
	];
}

/**
 * Tela padrão wp-login.php (redefinição de senha, logout) com a cara do tema.
 */
add_action( 'login_enqueue_scripts', function () {
	wp_enqueue_style( 'getalive-login', GA_URI . '/dist/css/login.css', [], ga_asset_version( 'css/login.css' ) );
} );

add_filter( 'login_headerurl', fn() => home_url( '/' ) );
add_filter( 'login_headertext', fn() => get_bloginfo( 'name' ) );

<?php
/**
 * Enfileira os bundles do Parcel (dist/) e expõe a configuração para o JS.
 */

defined( 'ABSPATH' ) || exit;

function ga_asset_version( string $file ): string {
	$path = GA_DIR . '/dist/' . $file;
	return file_exists( $path ) ? (string) filemtime( $path ) : GA_VERSION;
}

/**
 * URL de um arquivo do dist/. O _litespeed_rm_qs=0 impede o LiteSpeed de tirar o
 * ?ver=: main.js e main.css não têm hash no nome e o servidor manda guardar por um
 * ano, então sem o ver o navegador seguiria com a versão antiga depois de um deploy.
 */
function ga_asset_url( string $file ): string {
	return add_query_arg( '_litespeed_rm_qs', '0', GA_URI . '/dist/' . $file );
}

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'getalive', ga_asset_url( 'js/main.css' ), [], ga_asset_version( 'js/main.css' ) );
	wp_enqueue_script( 'getalive', ga_asset_url( 'js/main.js' ), [], ga_asset_version( 'js/main.js' ), [
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

/**
 * O CSS do tema também fica de fora: o Parcel já entrega um arquivo único e
 * minificado. Otimizado pelo LiteSpeed, ele passava a depender do CSS crítico e do
 * UCSS gerados por página, que ficam velhos a cada deploy (primeira visita sem
 * estilo) e não enxergam as classes usadas só pelo JS e pelo React.
 */
add_filter( 'style_loader_tag', function ( $tag, $handle ) {
	if ( 'getalive' !== $handle ) {
		return $tag;
	}
	return str_replace( '<link ', '<link data-no-optimize="1" data-no-async="1" ', $tag );
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
		'lang'           => ga_lang(),
		'locale'         => GA_LANGS[ ga_lang() ]['intl'],
		'i18n'           => (object) ga_js_i18n(),
		'langHomes'      => array_combine( array_keys( GA_LANGS ), array_map( fn( $code ) => ga_lang_url( $code, '' ), array_keys( GA_LANGS ) ) ),
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

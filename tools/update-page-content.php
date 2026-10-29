<?php
/**
 * Atualiza o texto de uma página do tema (Sobre, Política de Privacidade) a partir
 * do arquivo em inc/content/. O texto anterior fica nas revisões da página.
 *
 * Uso:  php tools/update-page-content.php privacy
 */

if ( PHP_SAPI !== 'cli' ) {
	exit( "Rode pela linha de comando.\n" );
}

// Carrega o WordPress (localmente o tema fica em wp-content/themes/<tema>/tools).
$_SERVER['HTTP_HOST']   = parse_url( getenv( 'WP_HOME' ) ?: 'http://getalive.local', PHP_URL_HOST );
$_SERVER['REQUEST_URI'] = '/';
// No servidor os scripts rodam da cópia do repositório (fora do WordPress): WP_PATH aponta para ele.
require ( getenv( 'WP_PATH' ) ?: dirname( __DIR__, 4 ) ) . '/wp-load.php';

$pages = array_filter( ga_install_pages(), fn( $page ) => '' !== $page[3] );
$key   = $argv[1] ?? '';
if ( ! isset( $pages[ $key ] ) ) {
	fwrite( STDERR, 'Uso: php tools/update-page-content.php <' . implode( '|', array_keys( $pages ) ) . ">\n" );
	exit( 1 );
}

$id = ga_page_id( $key );
if ( ! $id || ! get_post( $id ) ) {
	fwrite( STDERR, "A página '$key' não existe. Ative o tema para criá-la.\n" );
	exit( 1 );
}

$content = (string) file_get_contents( GA_DIR . '/inc/content/' . $pages[ $key ][3] );
if ( get_post_field( 'post_content', $id ) === $content ) {
	echo "Nada a fazer: a página já está com o texto do arquivo.\n";
	exit;
}

$admins = get_users( [ 'role' => 'administrator', 'number' => 1 ] );
wp_set_current_user( $admins[0]->ID ?? 1 );

$result = wp_update_post( [ 'ID' => $id, 'post_content' => $content ], true );
if ( is_wp_error( $result ) ) {
	fwrite( STDERR, $result->get_error_message() . "\n" );
	exit( 1 );
}
echo 'Atualizada: ' . get_the_title( $id ) . ' (' . get_permalink( $id ) . ")\n";

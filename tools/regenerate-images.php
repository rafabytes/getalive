<?php
/**
 * Gera os tamanhos de imagem que faltam nos anexos já enviados (ex.: depois de
 * adicionar um add_image_size no tema). Arquivos que já existem são mantidos.
 *
 * Uso:  php tools/regenerate-images.php
 */

if ( PHP_SAPI !== 'cli' ) {
	exit( "Rode pela linha de comando.\n" );
}

// Carrega o WordPress (localmente o tema fica em wp-content/themes/<tema>/tools).
$_SERVER['HTTP_HOST']   = parse_url( getenv( 'WP_HOME' ) ?: 'http://getalive.local', PHP_URL_HOST );
$_SERVER['REQUEST_URI'] = '/';
// No servidor os scripts rodam da cópia do repositório (fora do WordPress): WP_PATH aponta para ele.
require ( getenv( 'WP_PATH' ) ?: dirname( __DIR__, 4 ) ) . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$ids = get_posts( [
	'post_type'      => 'attachment',
	'post_mime_type' => 'image',
	'post_status'    => 'inherit',
	'posts_per_page' => -1,
	'fields'         => 'ids',
] );

$done = 0;
foreach ( $ids as $id ) {
	// Já ignora tamanhos maiores que o original (ex.: ga-stage numa imagem de 1920).
	if ( ! wp_get_missing_image_subsizes( $id ) ) {
		continue;
	}
	$result = wp_update_image_subsizes( $id );
	if ( is_wp_error( $result ) ) {
		fwrite( STDERR, "#$id " . get_the_title( $id ) . ': ' . $result->get_error_message() . "\n" );
		continue;
	}
	$done++;
	echo "#$id " . basename( get_attached_file( $id ) ) . "\n";
}

echo "\n$done de " . count( $ids ) . " imagens atualizadas.\n";

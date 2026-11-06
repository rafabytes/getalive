<?php
/**
 * Exporta templates para um pacote .zip (o mesmo de Templates → Exportar / Importar).
 *
 * Uso:  php tools/export-templates.php cafe ratimbum      (pelos slugs)
 *       php tools/export-templates.php --since=2026-11-01 (alterados desde a data)
 *       php tools/export-templates.php --all
 *       ... [--out=pasta]                                  (padrão: pasta atual)
 */

if ( PHP_SAPI !== 'cli' ) {
	exit( "Rode pela linha de comando.\n" );
}

// Carrega o WordPress (localmente o tema fica em wp-content/themes/<tema>/tools).
$_SERVER['HTTP_HOST']   = parse_url( getenv( 'WP_HOME' ) ?: 'http://getalive.local', PHP_URL_HOST );
$_SERVER['REQUEST_URI'] = '/';
// No servidor os scripts rodam da cópia do repositório (fora do WordPress): WP_PATH aponta para ele.
require ( getenv( 'WP_PATH' ) ?: dirname( __DIR__, 4 ) ) . '/wp-load.php';

$args  = array_slice( $argv, 1 );
$opt   = fn( $name ) => ( preg_grep( "/^--{$name}=/", $args ) ? explode( '=', current( preg_grep( "/^--{$name}=/", $args ) ), 2 )[1] : null );
$slugs = array_values( array_filter( $args, fn( $a ) => '--' !== substr( $a, 0, 2 ) ) );
$query = [ 'post_type' => 'ga_template', 'post_status' => [ 'publish', 'draft', 'pending', 'private', 'future' ], 'numberposts' => -1, 'fields' => 'ids' ];

if ( $slugs ) {
	$query['post_name__in'] = $slugs;
} elseif ( $opt( 'since' ) ) {
	$query['date_query'] = [ [ 'column' => 'post_modified', 'after' => $opt( 'since' ), 'inclusive' => true ] ];
} elseif ( ! in_array( '--all', $args, true ) ) {
	fwrite( STDERR, "Uso: php tools/export-templates.php <slug...> | --since=AAAA-MM-DD | --all [--out=pasta]\n" );
	exit( 1 );
}

$ids = get_posts( $query );
if ( $slugs && count( $ids ) < count( $slugs ) ) {
	$found = array_map( fn( $id ) => get_post_field( 'post_name', $id ), $ids );
	fwrite( STDERR, 'Não encontrado: ' . implode( ', ', array_diff( $slugs, $found ) ) . "\n" );
	exit( 1 );
}
$path = ga_transfer_export( $ids );
if ( is_wp_error( $path ) ) {
	fwrite( STDERR, $path->get_error_message() . "\n" );
	exit( 1 );
}

$dir = rtrim( $opt( 'out' ) ?: getcwd(), '/\\' );
$out = $dir . '/' . ga_transfer_filename( $ids );
rename( $path, $out );
foreach ( $ids as $id ) {
	echo '  ' . get_the_title( $id ) . ' (' . get_post_field( 'post_name', $id ) . ")\n";
}
echo 'Pacote: ' . $out . ' (' . size_format( filesize( $out ), 1 ) . ")\n";

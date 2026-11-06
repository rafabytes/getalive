<?php
/**
 * Importa um pacote exportado em Templates → Exportar / Importar (ou por tools/export-templates.php).
 * Só os templates do pacote são criados ou atualizados (pelo slug); o resto do site fica como está.
 *
 * Uso:  php tools/import-package.php pacote.zip            (mostra o que vai acontecer e para)
 *       php tools/import-package.php pacote.zip --yes      (importa todos)
 *       php tools/import-package.php pacote.zip cafe --yes (só os slugs indicados)
 */

if ( PHP_SAPI !== 'cli' ) {
	exit( "Rode pela linha de comando.\n" );
}

// Carrega o WordPress (localmente o tema fica em wp-content/themes/<tema>/tools).
$_SERVER['HTTP_HOST']   = parse_url( getenv( 'WP_HOME' ) ?: 'http://getalive.local', PHP_URL_HOST );
$_SERVER['REQUEST_URI'] = '/';
// No servidor os scripts rodam da cópia do repositório (fora do WordPress): WP_PATH aponta para ele.
require ( getenv( 'WP_PATH' ) ?: dirname( __DIR__, 4 ) ) . '/wp-load.php';

$args = array_slice( $argv, 1 );
$file = $args[0] ?? '';
if ( ! $file || ! is_file( $file ) ) {
	fwrite( STDERR, "Uso: php tools/import-package.php <pacote.zip> [slug...] [--yes]\n" );
	exit( 1 );
}
$slugs = array_values( array_filter( array_slice( $args, 1 ), fn( $a ) => '--' !== substr( $a, 0, 2 ) ) );

$admins = get_users( [ 'role' => 'administrator', 'number' => 1 ] );
wp_set_current_user( $admins[0]->ID ?? 1 );

$opened = ga_transfer_open( $file );
if ( is_wp_error( $opened ) ) {
	fwrite( STDERR, $opened->get_error_message() . "\n" );
	exit( 1 );
}
[ $zip, $data ] = $opened;
$zip->close();

echo "Pacote de {$data['source']} ({$data['created']} UTC)\n";
foreach ( ga_transfer_preview( $data ) as $r ) {
	if ( $slugs && ! in_array( $r['slug'], $slugs, true ) ) {
		continue;
	}
	echo sprintf( "  %-9s %s (%s) · %s%s\n", $r['exists'] ? 'atualiza' : 'NOVO', $r['title'], $r['slug'], $r['summary'], $r['newer'] ? ' · ATENÇÃO: a cópia daqui foi alterada depois da exportação' : '' );
}

if ( ! in_array( '--yes', $args, true ) ) {
	echo "Nada foi alterado. Rode de novo com --yes para importar.\n";
	exit( 0 );
}

$log = ga_transfer_import( $file, $slugs ?: null );
if ( is_wp_error( $log ) ) {
	fwrite( STDERR, $log->get_error_message() . "\n" );
	exit( 1 );
}
echo implode( "\n", $log ) . "\n";

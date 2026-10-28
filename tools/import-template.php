<?php
/**
 * Importa (ou atualiza) um template a partir de um manifesto JSON.
 *
 * Uso:  php tools/import-template.php tools/templates/vapo.json [--draft]
 *
 * O manifesto aponta para a pasta do template (source), as imagens das cenas e o
 * ZIP de distribuição. Rodar de novo atualiza o mesmo post (pelo slug) e só
 * reimporta imagens/ZIP que mudaram.
 */

if ( PHP_SAPI !== 'cli' ) {
	exit( "Rode pela linha de comando.\n" );
}

$manifest_path = $argv[1] ?? '';
if ( ! $manifest_path || ! is_file( $manifest_path ) ) {
	fwrite( STDERR, "Uso: php tools/import-template.php <manifesto.json> [--draft]\n" );
	exit( 1 );
}
$m = json_decode( file_get_contents( $manifest_path ), true );
if ( ! $m ) {
	fwrite( STDERR, "Manifesto inválido: " . json_last_error_msg() . "\n" );
	exit( 1 );
}

// Carrega o WordPress (o tema fica em wp-content/themes/<tema>/tools).
$_SERVER['HTTP_HOST']   = parse_url( getenv( 'WP_HOME' ) ?: 'http://getalive.local', PHP_URL_HOST );
$_SERVER['REQUEST_URI'] = '/';
require dirname( __DIR__, 4 ) . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

$admins = get_users( [ 'role' => 'administrator', 'number' => 1 ] );
wp_set_current_user( $admins[0]->ID ?? 1 );

if ( get_stylesheet() !== basename( dirname( __DIR__ ) ) ) {
	switch_theme( basename( dirname( __DIR__ ) ) );
	// As funções do tema só carregam numa nova execução do WordPress.
	exit( "Tema ativado. Rode o comando de novo para importar.\n" );
}
ga_install();

$src = rtrim( str_replace( '\\', '/', $m['source'] ), '/' );
if ( ! is_dir( $src ) ) {
	fwrite( STDERR, "Pasta do template não encontrada: {$src}\n" );
	exit( 1 );
}

/**
 * Importa uma imagem para a biblioteca, reaproveitando se o arquivo não mudou.
 */
function ga_import_image( string $path, int $parent, string $title ): int {
	$hash     = md5_file( $path );
	$existing = get_posts( [
		'post_type'   => 'attachment',
		'post_status' => 'inherit',
		'meta_key'    => '_ga_source_hash',
		'meta_value'  => $hash,
		'fields'      => 'ids',
		'numberposts' => 1,
	] );
	if ( $existing ) {
		return (int) $existing[0];
	}
	$tmp = wp_tempnam( basename( $path ) );
	copy( $path, $tmp );
	$id = media_handle_sideload( [ 'name' => basename( $path ), 'tmp_name' => $tmp ], $parent, $title );
	if ( is_wp_error( $id ) ) {
		fwrite( STDERR, "Falha ao importar {$path}: " . $id->get_error_message() . "\n" );
		return 0;
	}
	update_post_meta( $id, '_ga_source_hash', $hash );
	update_post_meta( $id, '_wp_attachment_image_alt', $title );
	echo "  imagem: " . basename( $path ) . "\n";
	return (int) $id;
}

// Post
$existing = get_page_by_path( $m['slug'], OBJECT, 'ga_template' );
$post_id  = wp_insert_post( [
	'ID'           => $existing->ID ?? 0,
	'post_type'    => 'ga_template',
	'post_status'  => in_array( '--draft', $argv, true ) ? 'draft' : 'publish',
	'post_title'   => $m['title'],
	'post_name'    => $m['slug'],
	'post_content' => $m['content'] ?? '',
	'post_excerpt' => $m['excerpt'] ?? '',
	// Mantém a posição definida arrastando na lista do painel.
	'menu_order'   => $existing->menu_order ?? 0,
], true );
if ( is_wp_error( $post_id ) ) {
	fwrite( STDERR, $post_id->get_error_message() . "\n" );
	exit( 1 );
}
echo ( $existing ? 'Atualizado' : 'Criado' ) . ": {$m['title']} (#{$post_id})\n";

// Metadados simples
foreach ( [ 'tagline', 'version', 'resolution', 'obs_version', 'accent' ] as $key ) {
	update_post_meta( $post_id, "_ga_{$key}", $m[ $key ] ?? '' );
}
foreach ( [ 'features', 'requirements', 'install' ] as $key ) {
	// wp_slash: update_post_meta remove barras (e os caminhos do Windows usam "\").
	update_post_meta( $post_id, "_ga_{$key}", wp_slash( implode( "\n", $m[ $key ] ?? [] ) ) );
}
update_post_meta( $post_id, '_ga_platforms', $m['platforms'] ?? [] );
update_post_meta( $post_id, '_ga_featured', empty( $m['featured'] ) ? '' : '1' );
update_post_meta( $post_id, '_ga_premium', empty( $m['premium'] ) ? '' : '1' );
if ( ! empty( $m['price'] ) ) {
	update_post_meta( $post_id, '_ga_price', $m['price'] );
}

// Estilos
$terms = [];
foreach ( $m['styles'] ?? [] as $name ) {
	$term    = term_exists( $name, 'ga_style' ) ?: wp_insert_term( $name, 'ga_style' );
	$terms[] = (int) ( is_array( $term ) ? $term['term_id'] : $term );
}
wp_set_object_terms( $post_id, $terms, 'ga_style' );

// Cenas
$scenes = [];
foreach ( $m['scenes'] ?? [] as $scene ) {
	$image    = ! empty( $scene['image'] ) && is_file( "$src/{$scene['image']}" )
		? ga_import_image( "$src/{$scene['image']}", $post_id, "{$m['title']}: {$scene['title']}" )
		: 0;
	$scenes[] = [ 'title' => $scene['title'], 'desc' => $scene['desc'] ?? '', 'image' => $image ];
}
update_post_meta( $post_id, '_ga_scenes', $scenes );

// Capa
if ( ! empty( $m['thumbnail'] ) && is_file( "$src/{$m['thumbnail']}" ) ) {
	set_post_thumbnail( $post_id, ga_import_image( "$src/{$m['thumbnail']}", $post_id, $m['title'] ) );
}

// ZIP: o mais recente que casar com o padrão
$zips = glob( "$src/" . ( $m['zip'] ?? 'dist/*.zip' ) );
usort( $zips, fn( $a, $b ) => filemtime( $b ) <=> filemtime( $a ) );
if ( $zips ) {
	$zip     = $zips[0];
	$current = (string) get_post_meta( $post_id, '_ga_file', true );
	$same    = $current && is_file( ga_private_dir() . "/$current" ) && md5_file( ga_private_dir() . "/$current" ) === md5_file( $zip );
	if ( $same ) {
		echo "  zip: sem mudanças\n";
	} else {
		$stored = ga_store_template_file( $post_id, $zip );
		echo is_wp_error( $stored ) ? '  zip: ERRO ' . $stored->get_error_message() . "\n" : '  zip: ' . basename( $zip ) . ' (' . size_format( filesize( $zip ), 1 ) . ")\n";
	}
} else {
	echo "  zip: nenhum arquivo encontrado em {$m['zip']}\n";
}

flush_rewrite_rules();
echo 'Pronto: ' . get_permalink( $post_id ) . "\n";

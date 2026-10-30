<?php
/**
 * Confere as traduções do tema: lista os textos em português sem tradução em
 * languages/<locale>.l10n.php e os templates sem languages/templates/<slug>.json.
 *
 * Uso:  php tools/i18n-check.php            (resumo do que falta)
 *       php tools/i18n-check.php --missing  (só os textos que faltam, um por linha, para traduzir)
 *
 * Além dos textos do código (__(), esc_html_e()...), confere os que vêm do banco e
 * passam por ga_t(): itens de menu, estilos, títulos das páginas do tema, descrição
 * do site, textos da página inicial no Personalizar e opções do formulário de pedido.
 */

if ( PHP_SAPI !== 'cli' ) {
	exit( "Rode pela linha de comando.\n" );
}

// Carrega o WordPress (localmente o tema fica em wp-content/themes/<tema>/tools).
$_SERVER['HTTP_HOST']   = parse_url( getenv( 'WP_HOME' ) ?: 'http://getalive.local', PHP_URL_HOST );
$_SERVER['REQUEST_URI'] = '/';
// No servidor os scripts rodam da cópia do repositório (fora do WordPress): WP_PATH aponta para ele.
require ( getenv( 'WP_PATH' ) ?: dirname( __DIR__, 4 ) ) . '/wp-load.php';

$theme   = dirname( __DIR__ );

/** Texto de uma string entre aspas simples: só ' e \ são escapes (caminhos do Windows mantêm a barra). */
function ga_i18n_unquote( string $text ): string {
	return strtr( $text, [ "\\'" => "'", '\\\\' => '\\' ] );
}
$strings = [];
$add     = function ( string $text, string $from ) use ( &$strings ) {
	$text = trim( $text );
	if ( '' !== $text && ! is_numeric( $text ) ) {
		$strings[ $text ] ??= $from;
	}
};

// Código PHP do tema.
$files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $theme, FilesystemIterator::SKIP_DOTS ) );
foreach ( $files as $file ) {
	$path = str_replace( '\\', '/', substr( $file->getPathname(), strlen( $theme ) + 1 ) );
	if ( 'php' !== $file->getExtension() || preg_match( '#^(tools|node_modules|languages|dist)/#', $path ) ) {
		continue;
	}
	preg_match_all( "/\b(?:__|_e|esc_html__|esc_html_e|esc_attr__|esc_attr_e)\(\s*'((?:[^'\\\\]|\\\\.)*)'\s*,\s*'getalive'/", file_get_contents( $file->getPathname() ), $m );
	foreach ( $m[1] as $text ) {
		$add( ga_i18n_unquote( $text ), $path );
	}
}

// JS: todo __('...') precisa estar em ga_js_strings().
$js_list = ga_js_strings();
foreach ( $js_list as $text ) {
	$add( $text, 'ga_js_strings()' );
}
$js_missing = [];
foreach ( new RecursiveIteratorIterator( new RecursiveDirectoryIterator( "$theme/src/js", FilesystemIterator::SKIP_DOTS ) ) as $file ) {
	preg_match_all( "/\b_[_n]\(\s*'((?:[^'\\\\]|\\\\.)*)'(?:\s*,\s*'((?:[^'\\\\]|\\\\.)*)')?/", file_get_contents( $file->getPathname() ), $m, PREG_SET_ORDER );
	foreach ( $m as $match ) {
		$texts = [ $match[1] ];
		if ( str_contains( $match[0], '_n(' ) && isset( $match[2] ) ) {
			$texts[] = $match[2];
		}
		foreach ( $texts as $text ) {
			if ( ! in_array( ga_i18n_unquote( $text ), $js_list, true ) ) {
				$js_missing[ ga_i18n_unquote( $text ) ] = $file->getFilename();
			}
		}
	}
}

// Textos do banco que passam por ga_t().
foreach ( get_nav_menu_locations() as $menu ) {
	foreach ( wp_get_nav_menu_items( $menu ) ?: [] as $item ) {
		$add( $item->title, 'menu' );
	}
}
foreach ( get_terms( [ 'taxonomy' => 'ga_style', 'hide_empty' => false ] ) as $term ) {
	$add( $term->name, 'estilo' );
}
foreach ( (array) get_option( 'ga_pages', [] ) as $id ) {
	$add( get_post_field( 'post_title', $id ), 'título de página' );
}
$add( get_option( 'blogdescription' ), 'descrição do site' );
foreach ( ga_home_fields() as $key => [ , $type, $default ] ) {
	if ( 'checkbox' === $type ) {
		continue;
	}
	foreach ( array_unique( [ $default, (string) get_theme_mod( "ga_home_{$key}", '' ) ] ) as $value ) {
		if ( 'faq_items' === $key ) {
			foreach ( preg_split( "/\n\s*\n/", str_replace( "\r", '', $value ) ) as $block ) {
				$lines = array_values( array_filter( array_map( 'trim', explode( "\n", $block ) ) ) );
				if ( $lines ) {
					$add( array_shift( $lines ), 'home: perguntas' );
					$add( implode( ' ', $lines ), 'home: perguntas' );
				}
			}
		} elseif ( 'package_items' === $key ) {
			foreach ( explode( "\n", $value ) as $line ) {
				$add( $line, 'home: no pacote' );
			}
		} else {
			$add( $value, "home: {$key}" );
		}
	}
}
foreach ( [ 'vibes', 'scenes' ] as $key ) {
	foreach ( ga_request_options()[ $key ] as $option ) {
		$add( $option, 'pedido' );
	}
}
foreach ( [ 'cena', 'cenas', 'template', 'templates', 'download', 'downloads' ] as $word ) {
	$add( $word, 'ga_plural()' );
}

// Comparação com os dicionários.
$only_missing = in_array( '--missing', $argv, true );
$problems     = 0;
foreach ( GA_LANGS as $code => $lang ) {
	if ( GA_LANG_DEFAULT === $code ) {
		continue;
	}
	$file     = "$theme/languages/{$lang['locale']}.l10n.php";
	$messages = is_file( $file ) ? ( include $file )['messages'] ?? [] : [];
	$missing  = array_diff_key( $strings, $messages );
	$unused   = array_diff_key( $messages, $strings );
	$problems += count( $missing );

	if ( $only_missing ) {
		foreach ( $missing as $text => $from ) {
			echo "[{$code}] {$text}\n";
		}
		continue;
	}
	printf( "%s (%s): %d textos, %d sem tradução, %d sobrando no dicionário\n", $lang['name'], $lang['locale'], count( $strings ), count( $missing ), count( $unused ) );
	foreach ( $missing as $text => $from ) {
		echo "  falta: {$text}  ({$from})\n";
	}
	foreach ( $unused as $text => $translation ) {
		echo "  sobrando: {$text}\n";
	}

	foreach ( ga_query_templates( [ 'post_status' => [ 'publish', 'draft' ] ] ) as $post ) {
		$json = "$theme/languages/templates/{$post->post_name}.json";
		$data = is_file( $json ) ? json_decode( file_get_contents( $json ), true ) : null;
		if ( empty( $data[ $code ] ) ) {
			++$problems;
			echo "  template sem tradução: {$post->post_name}\n";
		}
	}
}

foreach ( $js_missing as $text => $file ) {
	++$problems;
	echo "JS: \"{$text}\" ({$file}) não está em ga_js_strings() no inc/i18n.php\n";
}
exit( $problems ? 1 : 0 );

<?php
/**
 * Idiomas do site: português (padrão, sem prefixo), inglês (/en/) e espanhol (/es/).
 *
 * O idioma vem do começo da URL. home_url() ganha o prefixo, então o WordPress já
 * descarta o /en/ ao ler o endereço e todo link interno (permalinks, REST, menus)
 * sai no idioma da página. As páginas do tema têm slug traduzido (/en/about/): ele
 * é trocado pelo slug em português só enquanto o WordPress lê a URL.
 *
 * Textos do tema: escritos em português no código (__(), esc_html_e()...) e traduzidos em
 * languages/<locale>.l10n.php. Conteúdo dos templates: languages/templates/<slug>.json.
 */

defined( 'ABSPATH' ) || exit;

const GA_LANGS = [
	'pt' => [ 'locale' => 'pt_BR', 'name' => 'Português', 'hreflang' => 'pt-BR', 'intl' => 'pt-BR' ],
	'en' => [ 'locale' => 'en_US', 'name' => 'English', 'hreflang' => 'en', 'intl' => 'en-US' ],
	'es' => [ 'locale' => 'es_ES', 'name' => 'Español', 'hreflang' => 'es', 'intl' => 'es-ES' ],
];

const GA_LANG_DEFAULT = 'pt';

/**
 * Primeiro trecho da URL traduzido: páginas do tema e a base dos estilos.
 * Chave = slug em português (o que está no banco).
 */
const GA_LANG_SLUGS = [
	'entrar'                  => [ 'en' => 'login', 'es' => 'iniciar-sesion' ],
	'criar-conta'             => [ 'en' => 'sign-up', 'es' => 'crear-cuenta' ],
	'conta'                   => [ 'en' => 'account', 'es' => 'cuenta' ],
	'solicitar'               => [ 'en' => 'request', 'es' => 'solicitar' ],
	'como-instalar'           => [ 'en' => 'how-to-install', 'es' => 'como-instalar' ],
	'sobre'                   => [ 'en' => 'about', 'es' => 'acerca-de' ],
	'politica-de-privacidade' => [ 'en' => 'privacy-policy', 'es' => 'politica-de-privacidad' ],
	'estilo'                  => [ 'en' => 'style', 'es' => 'estilo' ],
];

function ga_lang(): string {
	return $GLOBALS['ga_lang'] ?? GA_LANG_DEFAULT;
}

function ga_lang_is_default(): bool {
	return GA_LANG_DEFAULT === ga_lang();
}

/** Caminho da home sem o prefixo de idioma: "/" ou "/getalive/". */
function ga_lang_base_path(): string {
	return trailingslashit( (string) wp_parse_url( get_option( 'home' ), PHP_URL_PATH ) );
}

/**
 * Troca o primeiro trecho de um caminho relativo à home ("sobre/?x=1") entre idiomas.
 */
function ga_lang_map_path( string $path, string $from, string $to ): string {
	if ( $from === $to || ! preg_match( '#^([^/?\#]+)(.*)$#s', $path, $m ) ) {
		return $path;
	}
	$segment = rawurldecode( $m[1] );
	foreach ( GA_LANG_SLUGS as $pt => $slugs ) {
		$slugs[ GA_LANG_DEFAULT ] = $pt;
		if ( ( $slugs[ $from ] ?? '' ) === $segment ) {
			return $slugs[ $to ] . $m[2];
		}
	}
	return $path;
}

/**
 * Lê o idioma da URL. Roda ao carregar o tema, antes de o WordPress interpretar o endereço.
 */
function ga_lang_detect(): void {
	$GLOBALS['ga_lang']      = GA_LANG_DEFAULT;
	$GLOBALS['ga_lang_path'] = '';
	if ( is_admin() || wp_doing_cron() || ( defined( 'WP_CLI' ) && WP_CLI ) || PHP_SAPI === 'cli' ) {
		return;
	}

	$uri  = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
	$base = ga_lang_base_path();
	if ( ! str_starts_with( $uri, $base ) ) {
		return;
	}
	$rest = substr( $uri, strlen( $base ) );
	$lang = GA_LANG_DEFAULT;
	if ( preg_match( '#^([a-z]{2})(?=/|\?|$)/?#', $rest, $m ) && isset( GA_LANGS[ $m[1] ] ) && GA_LANG_DEFAULT !== $m[1] ) {
		$lang = $m[1];
		$rest = substr( $rest, strlen( $m[0] ) );
	}

	// Caminho "interno" (slugs em português, sem prefixo): base dos links do seletor.
	$internal                = ga_lang_map_path( $rest, $lang, GA_LANG_DEFAULT );
	$GLOBALS['ga_lang']      = $lang;
	$GLOBALS['ga_lang_path'] = $internal;

	if ( $internal !== $rest ) {
		// O WordPress procura a página pelo slug em português; o endereço original volta logo depois.
		$GLOBALS['ga_lang_request_uri'] = $_SERVER['REQUEST_URI'];
		$_SERVER['REQUEST_URI']         = $base . ( GA_LANG_DEFAULT === $lang ? '' : $lang . '/' ) . $internal;
		add_action( 'parse_request', function () {
			$_SERVER['REQUEST_URI'] = $GLOBALS['ga_lang_request_uri'];
		}, 0 );
	}
}
ga_lang_detect();

/**
 * Endereço da página atual (ou de um caminho interno) em outro idioma.
 */
function ga_lang_url( string $lang, ?string $internal_path = null ): string {
	$path = ga_lang_map_path( $internal_path ?? $GLOBALS['ga_lang_path'], GA_LANG_DEFAULT, $lang );
	$root = untrailingslashit( set_url_scheme( get_option( 'home' ) ) ) . '/';
	return $root . ( GA_LANG_DEFAULT === $lang ? '' : $lang . '/' ) . $path;
}

/* ---------------------------------------------------------------------------
 * Links no idioma da página
 * ------------------------------------------------------------------------- */

add_filter( 'home_url', function ( $url, $path ) {
	if ( ga_lang_is_default() || ( is_admin() && ! wp_doing_ajax() ) ) {
		return $url;
	}
	if ( is_string( $path ) && preg_match( '#^/?(wp-login\.php|wp-admin|wp-content|wp-includes|xmlrpc\.php|robots\.txt|favicon\.ico)#', $path ) ) {
		return $url;
	}
	$home = untrailingslashit( set_url_scheme( get_option( 'home' ), wp_parse_url( $url, PHP_URL_SCHEME ) ) );
	if ( ! str_starts_with( $url, $home ) ) {
		return $url;
	}
	$rest = substr( $url, strlen( $home ) );
	return $home . '/' . ga_lang() . ( '' === $rest ? '' : '/' . ltrim( $rest, '/' ) );
}, 10, 2 );

/** Slug traduzido nas páginas do tema e na base dos estilos. */
function ga_lang_localize_link( string $url ): string {
	if ( ga_lang_is_default() ) {
		return $url;
	}
	$home = home_url( '/' );
	if ( ! str_starts_with( $url, $home ) ) {
		return $url;
	}
	return $home . ga_lang_map_path( substr( $url, strlen( $home ) ), GA_LANG_DEFAULT, ga_lang() );
}
add_filter( 'page_link', 'ga_lang_localize_link', 20 );
add_filter( 'term_link', 'ga_lang_localize_link', 20 );

/** Link digitado à mão (ex.: item personalizado de menu) para o próprio site, no idioma da página. */
function ga_lang_localize_custom_url( string $url ): string {
	$root = untrailingslashit( set_url_scheme( get_option( 'home' ) ) ) . '/';
	$url  = set_url_scheme( $url );
	if ( ga_lang_is_default() || ! str_starts_with( $url, $root ) ) {
		return $url;
	}
	return ga_lang_url( ga_lang(), substr( $url, strlen( $root ) ) );
}

/* ---------------------------------------------------------------------------
 * Locale: textos do WordPress, do tema e dos plugins
 * ------------------------------------------------------------------------- */

add_filter( 'locale', fn( $locale ) => ga_lang_is_default() ? $locale : GA_LANGS[ ga_lang() ]['locale'] );

if ( ! ga_lang_is_default() ) {
	// O núcleo já carregou as traduções em português antes do tema: recarrega no idioma da URL.
	unload_textdomain( 'default', true );
	load_default_textdomain();
	$GLOBALS['wp_locale'] = new WP_Locale();
}

/**
 * Traduções do WordPress (nomes dos meses, mensagens do núcleo) para os idiomas do site.
 * O inglês já vem no WordPress; os outros são baixados quando um administrador abre o painel.
 */
add_action( 'admin_init', function () {
	if ( ! current_user_can( 'install_languages' ) || get_transient( 'ga_lang_packs_checked' ) ) {
		return;
	}
	set_transient( 'ga_lang_packs_checked', 1, DAY_IN_SECONDS );
	require_once ABSPATH . 'wp-admin/includes/translation-install.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	foreach ( GA_LANGS as $lang ) {
		if ( 'en_US' !== $lang['locale'] && ! in_array( $lang['locale'], get_available_languages(), true ) ) {
			wp_download_language_pack( $lang['locale'] );
		}
	}
} );

add_action( 'after_setup_theme', function () {
	$locale = determine_locale();
	if ( 'pt_BR' !== $locale ) {
		// O português é o texto do código; inglês e espanhol vêm de languages/<locale>.l10n.php.
		load_textdomain( 'getalive', GA_DIR . "/languages/{$locale}.mo", $locale );
	}
}, 1 );

/** Tradução de um texto vindo do banco (menus, estilos, Personalizar). */
function ga_t( string $text ): string {
	// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText -- textos do banco, traduzidos no dicionário.
	return '' === $text || ga_lang_is_default() ? $text : __( $text, 'getalive' );
}

/** Formato de data por idioma. */
function ga_date_format( string $kind = 'long' ): string {
	$formats = [
		'long'      => [ 'pt' => 'j \d\e F \d\e Y', 'en' => 'F j, Y', 'es' => 'j \d\e F \d\e Y' ],
		'long_time' => [ 'pt' => 'j \d\e F \d\e Y, H:i', 'en' => 'F j, Y, g:i a', 'es' => 'j \d\e F \d\e Y, H:i' ],
		'short'     => [ 'pt' => 'j M Y', 'en' => 'M j, Y', 'es' => 'j M Y' ],
	];
	return $formats[ $kind ][ ga_lang() ] ?? $formats[ $kind ][ GA_LANG_DEFAULT ];
}

/* ---------------------------------------------------------------------------
 * Conteúdo do banco no idioma da página
 * ------------------------------------------------------------------------- */

/** Nome do site e descrição. */
add_filter( 'option_blogdescription', 'ga_t' );

/** Títulos das páginas do tema (Entrar, Sobre, Política de Privacidade...). */
function ga_lang_page_title( $title, $id = 0 ) {
	if ( ga_lang_is_default() || ! $id || ! in_array( (int) $id, array_map( 'intval', (array) get_option( 'ga_pages', [] ) ), true ) ) {
		return $title;
	}
	return ga_t( (string) $title );
}
add_filter( 'the_title', 'ga_lang_page_title', 10, 2 );
add_filter( 'single_post_title', fn( $title, $post ) => ga_lang_page_title( $title, $post->ID ?? 0 ), 10, 2 );

/** Nomes dos estilos. */
add_filter( 'get_term', function ( $term ) {
	if ( ga_lang_is_default() || ! $term instanceof WP_Term || 'ga_style' !== $term->taxonomy || ( is_admin() && ! wp_doing_ajax() ) ) {
		return $term;
	}
	$term       = clone $term;
	$term->name = ga_t( $term->name );
	return $term;
} );

/**
 * Texto das páginas Sobre e Política de Privacidade no idioma da página:
 * inc/content/<arquivo>.<idioma>.html. Vazio quando não há tradução.
 */
function ga_lang_page_content( int $post_id ): string {
	if ( ga_lang_is_default() ) {
		return '';
	}
	foreach ( ga_install_pages() as $key => $page ) {
		if ( $page[3] && $post_id === ga_page_id( $key ) ) {
			$file = GA_DIR . '/inc/content/' . str_replace( '.html', '.' . ga_lang() . '.html', $page[3] );
			return is_file( $file ) ? (string) file_get_contents( $file ) : '';
		}
	}
	return '';
}

add_filter( 'the_content', function ( $content ) {
	return in_the_loop() ? ( ga_lang_page_content( (int) get_the_ID() ) ?: $content ) : $content;
}, 1 );

/**
 * Tradução dos textos de um template (languages/templates/<slug>.json, por idioma).
 * Campos: tagline, excerpt, content, features, requirements, install, scenes [{title, desc}].
 */
function ga_template_i18n( $post ): array {
	static $cache = [];
	$post = get_post( $post );
	if ( ga_lang_is_default() || ! $post ) {
		return [];
	}
	if ( ! isset( $cache[ $post->post_name ] ) ) {
		$file = GA_DIR . '/languages/templates/' . sanitize_file_name( $post->post_name ) . '.json';
		$data = is_file( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : [];

		$cache[ $post->post_name ] = is_array( $data ) ? $data : [];
	}
	return $cache[ $post->post_name ][ ga_lang() ] ?? [];
}

add_filter( 'get_the_excerpt', function ( $excerpt, $post ) {
	return ga_template_i18n( $post )['excerpt'] ?? $excerpt;
}, 5, 2 );

add_filter( 'the_content', function ( $content ) {
	if ( ! in_the_loop() || 'ga_template' !== get_post_type() ) {
		return $content;
	}
	return ga_template_i18n( get_post() )['content'] ?? $content;
}, 1 );

/**
 * Rank Math monta título e descrição com o texto do banco: troca pelo do idioma da página.
 */
add_filter( 'rank_math/frontend/title', function ( $title ) {
	if ( ga_lang_is_default() || ! is_singular() ) {
		return $title;
	}
	$post       = get_queried_object();
	$translated = ga_lang_page_title( $post->post_title, $post->ID );
	return $translated === $post->post_title ? $title : str_replace( $post->post_title, $translated, $title );
} );

add_filter( 'rank_math/frontend/description', function ( $description ) {
	if ( ga_lang_is_default() || ! is_singular() ) {
		return $description;
	}
	$post = get_queried_object();
	if ( 'ga_template' === $post->post_type ) {
		return ga_template_i18n( $post )['excerpt'] ?? $description;
	}
	// Páginas com texto traduzido: o primeiro parágrafo, no tamanho que o Rank Math usa.
	if ( preg_match( '#<p[^>]*>(.*?)</p>#s', ga_lang_page_content( $post->ID ), $m ) ) {
		$text = trim( wp_strip_all_tags( do_shortcode( $m[1] ) ) );
		return mb_strlen( $text ) > 160 ? rtrim( mb_substr( $text, 0, mb_strrpos( mb_substr( $text, 0, 160 ), ' ' ) ), ' ,.;:' ) : $text;
	}
	return $description;
} );

/* ---------------------------------------------------------------------------
 * <head>: versões em outros idiomas
 * ------------------------------------------------------------------------- */

add_action( 'wp_head', function () {
	if ( is_404() || is_search() || is_author() ) {
		return;
	}
	foreach ( GA_LANGS as $code => $lang ) {
		printf( '<link rel="alternate" hreflang="%s" href="%s">' . "\n", esc_attr( $lang['hreflang'] ), esc_url( ga_lang_url( $code ) ) );
	}
	printf( '<link rel="alternate" hreflang="x-default" href="%s">' . "\n", esc_url( ga_lang_url( GA_LANG_DEFAULT ) ) );
}, 1 );

/**
 * Seletor de idioma. $variant: 'menu' (cabeçalho, com lista suspensa) ou 'list' (links lado a lado).
 * Os links trocam de página inteira (data-no-swup): o cabeçalho e o rodapé também mudam de idioma.
 * O JS mantém os endereços em dia na navegação pelo Swup, a partir do <link hreflang> do <head>.
 */
function ga_lang_switcher( string $variant = 'menu', string $class = '' ): string {
	$current = ga_lang();
	$items   = '';
	foreach ( GA_LANGS as $code => $lang ) {
		$items .= sprintf(
			'<a href="%1$s" hreflang="%2$s" lang="%2$s" data-lang-link="%3$s" data-no-swup %4$s>%5$s</a>',
			esc_url( ga_lang_url( $code ) ),
			esc_attr( $lang['hreflang'] ),
			esc_attr( $code ),
			$code === $current ? 'aria-current="true"' : '',
			'menu' === $variant ? '<span class="lang-menu__code">' . esc_html( strtoupper( $code ) ) . '</span>' . esc_html( $lang['name'] ) : esc_html( strtoupper( $code ) )
		);
	}

	if ( 'list' === $variant ) {
		return '<nav class="lang-list ' . esc_attr( $class ) . '" aria-label="' . esc_attr__( 'Idioma', 'getalive' ) . '">' . $items . '</nav>';
	}
	return '<div class="lang-menu ' . esc_attr( $class ) . '" data-lang-menu>'
		. '<button type="button" class="lang-menu__toggle" aria-expanded="false" aria-haspopup="true" aria-label="' . esc_attr( sprintf( /* translators: %s: idioma atual */ __( 'Idioma: %s', 'getalive' ), GA_LANGS[ $current ]['name'] ) ) . '" data-lang-menu-toggle>'
		. ga_icon( 'globe', 'size-4' ) . '<span>' . esc_html( strtoupper( $current ) ) . '</span>' . ga_icon( 'chevron-down', 'size-3.5 text-ink-400' )
		. '</button><div class="lang-menu__panel">' . $items . '</div></div>';
}

/**
 * Textos usados no JS e nos componentes React (src/js/lib/i18n.js).
 * Em português vai vazio: o próprio texto do código é o português.
 */
function ga_js_i18n(): array {
	if ( ga_lang_is_default() ) {
		return [];
	}
	$out = [];
	foreach ( ga_js_strings() as $text ) {
		$out[ $text ] = ga_t( $text );
	}
	return $out;
}

/** Textos em português usados pelo JS (o tools/i18n-check.php confere se a lista está completa). */
function ga_js_strings(): array {
	return [
		// api.js, main.js, módulos
		'Sem conexão. Confira sua internet e tente de novo.',
		'Algo deu errado. Tente de novo em instantes.',
		'Seu download está começando…',
		'Conta excluída. Até logo!',
		'Remover dos favoritos',
		'Favoritar',
		'Favoritado',
		'Salvo nos favoritos',
		'Removido dos favoritos',
		'Abrir menu',
		'Fechar menu',
		'Ver o template %s',
		'Imagem ampliada',
		'Fechar',
		// AuthApp
		'Bem-vindo',
		'de volta.',
		'Entre para baixar, favoritar e pedir templates.',
		'Entrar',
		'Crie sua',
		'conta grátis.',
		'Leva menos de um minuto. Só pedimos nome e e-mail.',
		'Criar conta',
		'Esqueceu',
		'a senha?',
		'Sem problema. Mandamos um link para você criar outra.',
		'Enviar link',
		'Não foi possível carregar o login do Google.',
		'Login com Google cancelado.',
		'Não foi possível abrir o login do Google.',
		'Senha fraca',
		'Senha razoável',
		'Senha boa',
		'Senha forte',
		'Esconder senha',
		'Mostrar senha',
		'faltam %d caracteres',
		'falta 1 caractere',
		'Conta criada com o Google.',
		'Login feito com o Google.',
		'Conta pra gente como você se chama.',
		'Informe um e-mail válido.',
		'Informe sua senha.',
		'Use pelo menos 8 caracteres.',
		'Para criar a conta, aceite a Política de Privacidade.',
		'Login feito.',
		'Conta criada.',
		'Oi, %s!',
		'Tudo certo!',
		'%s Levando você de volta…',
		'Entrar ou criar conta',
		'Voltar para o login',
		'Criar conta com o Google',
		'Continuar com o Google',
		'ou com e-mail',
		'Seu nome',
		'E-mail',
		'Senha',
		'Li e aceito a %s. Meus dados são usados só para a conta funcionar.',
		'Política de Privacidade',
		'Lembrar de mim',
		'Esqueci a senha',
		'Ainda não tem conta?',
		'Crie grátis',
		'Já tem conta?',
		// RequestForm
		'Dê um nome para a sua ideia.',
		'Conte um pouco mais (pelo menos %d caracteres).',
		'Pedido enviado, %s!',
		'Pedido enviado!',
		'Recebemos “%s”. Você acompanha o status em Minha conta e fica sabendo quando virar template.',
		'Ver minhas solicitações',
		'Mandar outra ideia',
		'A ideia',
		'Dê um nome (ex.: “Café lo-fi chuvoso”)',
		'Estilo',
		'Escolha quantos quiser.',
		'Cenas que você usa',
		'Assim o template já vem com tudo que a sua live precisa.',
		'Onde você faz live',
		'Descreva',
		'Cores, clima, elementos, o tipo de conteúdo da live…',
		'Referências',
		'Opcional. Links de lives, imagens ou vídeos que mostram o que você imagina (um por linha).',
		'Você recebe atualizações do pedido em Minha conta.',
		'Enviar pedido',
		// TemplateExplorer e TemplateCard
		'Destaques',
		'Populares',
		'Buscar templates',
		'Buscar por nome, estilo ou plataforma',
		'Limpar busca',
		'Ordenar',
		'Favoritos',
		'Estilos',
		'Todos',
		'template',
		'templates',
		'limpar filtros',
		'Nenhum favorito ainda',
		'Nada encontrado',
		'Não achamos templates para “%s”. Que tal pedir um com essa cara?',
		'Tente outro filtro ou peça um template novo.',
		'Limpar filtros',
		'Solicitar template',
		'Próximo template',
		'Pode ser o seu',
		'Novos templates saem a partir dos pedidos. Conte a sua ideia.',
		'cena',
		'cenas',
	];
}

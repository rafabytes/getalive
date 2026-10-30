<?php
/**
 * Textos da página inicial, editáveis em Aparência → Personalizar → Página inicial.
 *
 * Em inglês e espanhol vale o texto de "Página inicial (English/Español)" quando
 * preenchido; em branco, a tradução do texto em português pelo dicionário do tema.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Seções e campos: chave => [ rótulo, tipo, padrão ].
 * Nos títulos, o trecho entre *asteriscos* ganha o destaque de cor.
 */
function ga_home_sections(): array {
	return [
		'hero'    => [ 'Topo', [
			'hero_eyebrow' => [ 'Linha acima do título', 'text', 'Overlays grátis para OBS e Twitch' ],
			'hero_title_1' => [ 'Título: linha 1', 'text', 'entre' ],
			'hero_title_2' => [ 'Título: linha 2', 'text', '*ao vivo*' ],
			'hero_title_3' => [ 'Título: linha 3 (contorno)', 'text', 'agora' ],
			'hero_text'    => [ 'Texto', 'textarea', 'Overlays animados para live com todas as cenas prontas: tela de começando, jogo, conversa, já volto e encerrando, com chat e alertas. Grátis, e instala no OBS Studio em dois cliques.' ],
			'hero_cta'     => [ 'Botão principal', 'text', 'Explorar templates' ],
			'hero_stats'   => [ 'Mostrar números (templates, cenas e preço)', 'checkbox', '' ],
			'hero_price'   => [ 'Preço', 'text', 'R$0' ],
		] ],
		'catalog' => [ 'Catálogo', [
			'catalog_title_1' => [ 'Título: linha 1', 'text', 'Escolha o overlay' ],
			'catalog_title_2' => [ 'Título: linha 2', 'text', '*da sua live.*' ],
			'catalog_cta'     => [ 'Botão', 'text', 'Ver todos os overlays' ],
		] ],
		'how'     => [ 'Como funciona', [
			'how_title_1' => [ 'Título: linha 1', 'text', 'Do download' ],
			'how_title_2' => [ 'Título: linha 2', 'text', 'ao *ao vivo*.' ],
			'how_text'    => [ 'Texto', 'textarea', 'Sem arrastar PNG, sem montar cena por cena. Cada template é um pacote de overlay completo, com a coleção de cenas do OBS já configurada.' ],
			'how_cta'     => [ 'Botão', 'text', 'Como colocar o overlay no OBS' ],
			'step_1_title' => [ 'Passo 1: título', 'text', 'Baixe o template' ],
			'step_1_text'  => [ 'Passo 1: texto', 'textarea', 'Escolha o seu, entre na sua conta e baixe o ZIP. Extraia numa pasta fixa, como Documentos\\Temas OBS.' ],
			'step_2_title' => [ 'Passo 2: título', 'text', 'Dê dois cliques no instalador' ],
			'step_2_text'  => [ 'Passo 2: texto', 'textarea', 'Com o OBS fechado, abra o INSTALAR.bat. Ele cria a coleção de cenas com todos os caminhos certos.' ],
			'step_3_title' => [ 'Passo 3: título', 'text', 'Coloque seu nome e entre no ar' ],
			'step_3_text'  => [ 'Passo 3: texto', 'textarea', 'Edite o config.js no Bloco de Notas com seu nome, suas redes e sua agenda. Abra o OBS e escolha a coleção.' ],
		] ],
		'package' => [ 'No pacote', [
			'package_title'  => [ 'Título', 'text', 'Tudo que uma live precisa, já animado.' ],
			'package_items'  => [ 'Itens (um por linha)', 'textarea', "Telas de começando, jogo, conversa, react, já volto e encerrando\nContagem regressiva e cronômetro de pausa\nChat da Twitch na tela, sem login\nAlertas de seguidor, sub, doação e raid com som\nMoldura de webcam, meta de seguidores e barra de redes\nOverlays leves: animações sem pesar o PC" ],
			'card_1_title'   => [ 'Card roxo: título', 'text', 'Chat ao vivo' ],
			'card_1_text'    => [ 'Card roxo: texto', 'textarea', 'Conecta direto no canal da Twitch. Esconde bots e comandos.' ],
			'card_2_title'   => [ 'Card branco: título', 'text', 'Painéis para Twitch' ],
			'card_2_text'    => [ 'Card branco: texto', 'textarea', 'Sobre mim, agenda, regras, Discord e banner offline combinando.' ],
		] ],
		'request' => [ 'Pedidos', [
			'request_title_1' => [ 'Título: linha 1', 'text', 'Não achou o seu estilo?' ],
			'request_title_2' => [ 'Título: linha 2', 'text', '*Peça um template.*' ],
			'request_text'    => [ 'Texto', 'textarea', 'Conte a vibe, as cenas que você usa e mande referências. As ideias mais pedidas viram os próximos templates.' ],
			'request_cta'     => [ 'Botão', 'text', 'Solicitar um template' ],
		] ],
		'faq'     => [ 'Perguntas frequentes', [
			'faq_items' => [ 'Perguntas', 'textarea', "Os overlays são grátis mesmo?\nSim. É só criar uma conta (nome e e-mail, ou com o Google) para baixar os overlays. A conta também guarda seus favoritos e seus downloads.\n\nComo colocar o overlay no OBS?\nBaixe o template, extraia o ZIP numa pasta fixa e, com o OBS fechado, dê dois cliques no INSTALAR.bat. Ele cria a coleção de cenas com overlay, chat e alertas; depois é só escolher em Coleção de cenas. O passo a passo com imagens está na página Como instalar.\n\nFunciona no meu OBS?\nOs templates são feitos para o OBS Studio 30 ou mais novo no Windows 10 e 11. Cada página de template mostra os requisitos específicos.\n\nPreciso saber programar para personalizar?\nNão. Seu nome, redes sociais, textos e agenda ficam num único arquivo (config.js) que você edita no Bloco de Notas. O passo a passo vem no README de cada template.\n\nFunciona com Twitch, YouTube e Kick?\nAs cenas funcionam em qualquer plataforma. O chat na tela conecta na Twitch; os alertas funcionam com StreamElements e com a URL de alertas de outros serviços.\n\nVai pesar meu PC?\nOs overlays são páginas leves rodando dentro do próprio OBS. Se precisar, dá para baixar os quadros por segundo ou usar o fundo parado no config.js.\n\nPosso pedir um template específico?\nPode e deve! Use a página de solicitações. Você acompanha o status do seu pedido em Minha conta." ],
		] ],
	];
}

function ga_home_fields(): array {
	return array_merge( ...array_values( array_map( fn( $s ) => $s[1], ga_home_sections() ) ) );
}

/** Quantas telas o slider do topo aceita. */
const GA_HOME_SLIDES = 10;

/** Ordem padrão do slider (slugs), até alguém mudar no Personalizar. */
const GA_HOME_SLIDES_DEFAULT = [ 'ratimbum', 'rococo', 'vaporwave', 'liquid', 'vice', 'terminal' ];

/**
 * Templates do slider do topo, na ordem escolhida em Personalizar → Página inicial → Slider do topo.
 * Guardamos o slug (vale igual no local e em produção). Com todas as telas em branco, entram todos na ordem do catálogo.
 *
 * @return WP_Post[]
 */
function ga_home_slides(): array {
	$slugs = [];
	for ( $n = 1; $n <= GA_HOME_SLIDES; $n++ ) {
		$slug = (string) get_theme_mod( "ga_home_slide_{$n}", GA_HOME_SLIDES_DEFAULT[ $n - 1 ] ?? '' );
		if ( '' !== $slug && ! in_array( $slug, $slugs, true ) ) {
			$slugs[] = $slug;
		}
	}
	if ( ! $slugs ) {
		return ga_query_templates();
	}

	$by_slug = [];
	foreach ( ga_query_templates( [ 'post_name__in' => $slugs ] ) as $p ) {
		$by_slug[ $p->post_name ] = $p;
	}
	return array_values( array_filter( array_map( fn( $s ) => $by_slug[ $s ] ?? null, $slugs ) ) );
}

add_action( 'customize_register', function ( WP_Customize_Manager $wp_customize ) {
	$wp_customize->add_panel( 'ga_home', [
		'title'    => 'Página inicial',
		'priority' => 30,
	] );

	$wp_customize->add_section( 'ga_home_slider', [
		'title'       => 'Slider do topo',
		'description' => 'Cada tela mostra um template, nesta ordem. Deixe todas em branco para mostrar todos os templates na ordem do catálogo.',
		'panel'       => 'ga_home',
		'priority'    => 5,
	] );
	$choices = [ '' => '—' ];
	foreach ( ga_query_templates() as $p ) {
		$choices[ $p->post_name ] = get_the_title( $p );
	}
	for ( $n = 1; $n <= GA_HOME_SLIDES; $n++ ) {
		$wp_customize->add_setting( "ga_home_slide_{$n}", [
			'default'           => GA_HOME_SLIDES_DEFAULT[ $n - 1 ] ?? '',
			'sanitize_callback' => 'sanitize_title',
		] );
		$wp_customize->add_control( "ga_home_slide_{$n}", [
			'label'   => "Tela {$n}",
			'section' => 'ga_home_slider',
			'type'    => 'select',
			'choices' => $choices,
		] );
	}

	// Português no painel "Página inicial"; os outros idiomas num painel cada, com os mesmos campos.
	foreach ( GA_LANGS as $lang => $info ) {
		$default_lang = GA_LANG_DEFAULT === $lang;
		$panel        = $default_lang ? 'ga_home' : "ga_home_{$lang}";
		$suffix       = $default_lang ? '' : "_{$lang}";
		if ( ! $default_lang ) {
			$wp_customize->add_panel( $panel, [
				'title'       => "Página inicial ({$info['name']})",
				'description' => 'Em branco, cada campo usa a tradução do texto em português. Para ver a prévia, navegue até a versão ' . $info['name'] . ' do site (/' . $lang . '/).',
				'priority'    => 31,
			] );
		}

		foreach ( ga_home_sections() as $section => [ $title, $fields ] ) {
			$wp_customize->add_section( "ga_home_{$section}{$suffix}", [
				'title' => $title,
				'panel' => $panel,
			] );

			foreach ( $fields as $key => [ $label, $type, $default ] ) {
				if ( ! $default_lang && 'checkbox' === $type ) {
					continue;
				}
				$sanitize = [
					'textarea' => 'sanitize_textarea_field',
					'checkbox' => fn( $v ) => $v ? '1' : '',
				];
				$wp_customize->add_setting( "ga_home_{$key}{$suffix}", [
					'default'           => $default_lang ? $default : '',
					'sanitize_callback' => $sanitize[ $type ] ?? 'sanitize_text_field',
				] );

				$description = '';
				if ( str_contains( $key, '_title_' ) && ! str_starts_with( $key, 'step_' ) ) {
					$description = 'Coloque entre *asteriscos* o trecho que ganha cor de destaque.';
				} elseif ( 'faq_items' === $key ) {
					$description = 'Primeira linha é a pergunta, as seguintes a resposta. Separe as perguntas com uma linha em branco.';
				}

				$wp_customize->add_control( "ga_home_{$key}{$suffix}", [
					'label'       => $label,
					'description' => $description,
					'section'     => "ga_home_{$section}{$suffix}",
					'type'        => $type,
				] );
			}
		}
	}
} );

/**
 * Valor de um campo da home e se ele ainda precisa ser traduzido: o texto do idioma
 * da página, quando preenchido no Personalizar, ou o em português (vazio volta ao padrão).
 *
 * @return array{0:string,1:bool}
 */
function ga_home_value( string $key ): array {
	if ( ! ga_lang_is_default() ) {
		$own = (string) get_theme_mod( "ga_home_{$key}_" . ga_lang(), '' );
		if ( '' !== trim( $own ) ) {
			return [ $own, false ];
		}
	}
	$default = ga_home_fields()[ $key ][2] ?? '';
	$value   = (string) get_theme_mod( "ga_home_{$key}", $default );
	return [ '' === trim( $value ) ? $default : $value, ! ga_lang_is_default() ];
}

/**
 * Texto puro de um campo da home, no idioma da página.
 */
function ga_home( string $key ): string {
	[ $value, $translate ] = ga_home_value( $key );
	return $translate ? ga_t( $value ) : $value;
}

/**
 * Linha de título com o trecho entre *asteriscos* envolvido em <em $attrs>.
 */
function ga_home_line( string $key, string $attrs = '' ): string {
	return preg_replace( '/\*(.+?)\*/', '<em ' . $attrs . '>$1</em>', esc_html( ga_home( $key ) ) );
}

/**
 * Campo de várias linhas como lista, ignorando linhas vazias. Traduz item a item.
 */
function ga_home_list( string $key ): array {
	[ $value, $translate ] = ga_home_value( $key );
	$items = array_values( array_filter( array_map( 'trim', explode( "\n", $value ) ) ) );
	return $translate ? array_map( 'ga_t', $items ) : $items;
}

/**
 * Perguntas frequentes: blocos separados por linha em branco, a primeira linha é a pergunta.
 */
function ga_home_faq(): array {
	[ $value, $translate ] = ga_home_value( 'faq_items' );
	$faq = [];
	foreach ( preg_split( "/\n\s*\n/", str_replace( "\r", '', $value ) ) as $block ) {
		$lines = array_values( array_filter( array_map( 'trim', explode( "\n", $block ) ) ) );
		if ( $lines ) {
			$question = array_shift( $lines );
			$answer   = implode( ' ', $lines );
			$faq[]    = $translate ? [ ga_t( $question ), ga_t( $answer ) ] : [ $question, $answer ];
		}
	}
	return $faq;
}

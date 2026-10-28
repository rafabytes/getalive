<?php
/**
 * Textos da página inicial, editáveis em Aparência → Personalizar → Página inicial.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Seções e campos: chave => [ rótulo, tipo, padrão ].
 * Nos títulos, o trecho entre *asteriscos* ganha o destaque de cor.
 */
function ga_home_sections(): array {
	return [
		'hero'    => [ 'Topo', [
			'hero_eyebrow' => [ 'Linha acima do título', 'text', 'Ao vivo · Templates para OBS Studio' ],
			'hero_title_1' => [ 'Título: linha 1', 'text', 'Sua live' ],
			'hero_title_2' => [ 'Título: linha 2', 'text', 'no ar em *dois*' ],
			'hero_title_3' => [ 'Título: linha 3 (contorno)', 'text', 'cliques.' ],
			'hero_text'    => [ 'Texto', 'textarea', 'Coleções de cenas completas e animadas: começando, jogo, conversa, react, já volto e encerrando. Com chat, alertas e painéis. Baixe, rode o instalador e o OBS abre pronto.' ],
			'hero_cta'     => [ 'Botão principal', 'text', 'Explorar templates' ],
			'hero_stats'   => [ 'Mostrar números (templates, cenas e preço)', 'checkbox', '' ],
			'hero_price'   => [ 'Preço', 'text', 'R$0' ],
		] ],
		'catalog' => [ 'Catálogo', [
			'catalog_title_1' => [ 'Título: linha 1', 'text', 'Escolha o clima' ],
			'catalog_title_2' => [ 'Título: linha 2', 'text', '*da sua live.*' ],
			'catalog_cta'     => [ 'Botão', 'text', 'Ver todos os templates' ],
		] ],
		'how'     => [ 'Como funciona', [
			'how_title_1' => [ 'Título: linha 1', 'text', 'Do download' ],
			'how_title_2' => [ 'Título: linha 2', 'text', 'ao *ao vivo*.' ],
			'how_text'    => [ 'Texto', 'textarea', 'Sem arrastar PNG, sem montar cena por cena. Cada template vem com a coleção de cenas inteira configurada.' ],
			'how_cta'     => [ 'Botão', 'text', 'Ver o passo a passo com imagens' ],
			'step_1_title' => [ 'Passo 1: título', 'text', 'Baixe o template' ],
			'step_1_text'  => [ 'Passo 1: texto', 'textarea', 'Escolha o seu, entre na sua conta e baixe o ZIP. Extraia numa pasta fixa, como Documentos\\Temas OBS.' ],
			'step_2_title' => [ 'Passo 2: título', 'text', 'Dê dois cliques no instalador' ],
			'step_2_text'  => [ 'Passo 2: texto', 'textarea', 'Com o OBS fechado, abra o INSTALAR.bat. Ele cria a coleção de cenas com todos os caminhos certos.' ],
			'step_3_title' => [ 'Passo 3: título', 'text', 'Coloque seu nome e entre no ar' ],
			'step_3_text'  => [ 'Passo 3: texto', 'textarea', 'Edite o config.js no Bloco de Notas com seu nome, suas redes e sua agenda. Abra o OBS e escolha a coleção.' ],
		] ],
		'package' => [ 'No pacote', [
			'package_title'  => [ 'Título', 'text', 'Tudo que uma live precisa, já animado.' ],
			'package_items'  => [ 'Itens (um por linha)', 'textarea', "Cenas de começando, jogo, conversa, react, já volto e encerrando\nContagem regressiva e cronômetro de pausa\nChat da Twitch na tela, sem login\nAlertas de seguidor, sub, doação e raid com som\nMeta de seguidores e barra de redes\nFontes leves: animações sem pesar o PC" ],
			'card_1_title'   => [ 'Card roxo: título', 'text', 'Chat ao vivo' ],
			'card_1_text'    => [ 'Card roxo: texto', 'textarea', 'Conecta direto no canal da Twitch. Esconde bots e comandos.' ],
			'card_2_title'   => [ 'Card branco: título', 'text', 'Painéis e banner' ],
			'card_2_text'    => [ 'Card branco: texto', 'textarea', 'Sobre mim, agenda, regras, Discord e banner offline combinando.' ],
		] ],
		'request' => [ 'Pedidos', [
			'request_title_1' => [ 'Título: linha 1', 'text', 'Não achou o seu estilo?' ],
			'request_title_2' => [ 'Título: linha 2', 'text', '*Peça um template.*' ],
			'request_text'    => [ 'Texto', 'textarea', 'Conte a vibe, as cenas que você usa e mande referências. As ideias mais pedidas viram os próximos templates.' ],
			'request_cta'     => [ 'Botão', 'text', 'Solicitar um template' ],
		] ],
		'faq'     => [ 'Perguntas frequentes', [
			'faq_items' => [ 'Perguntas', 'textarea', "Os templates são grátis mesmo?\nSim. É só criar uma conta (nome e e-mail, ou com o Google) para baixar. A conta também guarda seus favoritos e seus downloads.\n\nFunciona no meu OBS?\nOs templates são feitos para o OBS Studio 30 ou mais novo no Windows 10 e 11. Cada página de template mostra os requisitos específicos.\n\nPreciso saber programar para personalizar?\nNão. Seu nome, redes sociais, textos e agenda ficam num único arquivo (config.js) que você edita no Bloco de Notas. O passo a passo vem no README de cada template.\n\nFunciona com Twitch, YouTube e Kick?\nAs cenas funcionam em qualquer plataforma. O chat na tela conecta na Twitch; os alertas funcionam com StreamElements e com a URL de alertas de outros serviços.\n\nVai pesar meu PC?\nOs overlays são páginas leves rodando dentro do próprio OBS. Se precisar, dá para baixar os quadros por segundo ou usar o fundo parado no config.js.\n\nPosso pedir um template específico?\nPode e deve! Use a página de solicitações. Você acompanha o status do seu pedido em Minha conta." ],
		] ],
	];
}

function ga_home_fields(): array {
	return array_merge( ...array_values( array_map( fn( $s ) => $s[1], ga_home_sections() ) ) );
}

add_action( 'customize_register', function ( WP_Customize_Manager $wp_customize ) {
	$wp_customize->add_panel( 'ga_home', [
		'title'    => 'Página inicial',
		'priority' => 30,
	] );

	foreach ( ga_home_sections() as $section => [ $title, $fields ] ) {
		$wp_customize->add_section( "ga_home_{$section}", [
			'title' => $title,
			'panel' => 'ga_home',
		] );

		foreach ( $fields as $key => [ $label, $type, $default ] ) {
			$sanitize = [
				'textarea' => 'sanitize_textarea_field',
				'checkbox' => fn( $v ) => $v ? '1' : '',
			];
			$wp_customize->add_setting( "ga_home_{$key}", [
				'default'           => $default,
				'sanitize_callback' => $sanitize[ $type ] ?? 'sanitize_text_field',
			] );

			$description = '';
			if ( str_contains( $key, '_title_' ) && ! str_starts_with( $key, 'step_' ) ) {
				$description = 'Coloque entre *asteriscos* o trecho que ganha cor de destaque.';
			} elseif ( 'faq_items' === $key ) {
				$description = 'Primeira linha é a pergunta, as seguintes a resposta. Separe as perguntas com uma linha em branco.';
			}

			$wp_customize->add_control( "ga_home_{$key}", [
				'label'       => $label,
				'description' => $description,
				'section'     => "ga_home_{$section}",
				'type'        => $type,
			] );
		}
	}
} );

/**
 * Texto puro de um campo da home; vazio volta ao padrão.
 */
function ga_home( string $key ): string {
	$default = ga_home_fields()[ $key ][2] ?? '';
	$value   = get_theme_mod( "ga_home_{$key}", $default );
	return '' === trim( (string) $value ) ? $default : (string) $value;
}

/**
 * Linha de título com o trecho entre *asteriscos* envolvido em <em $attrs>.
 */
function ga_home_line( string $key, string $attrs = '' ): string {
	return preg_replace( '/\*(.+?)\*/', '<em ' . $attrs . '>$1</em>', esc_html( ga_home( $key ) ) );
}

/**
 * Campo de várias linhas como lista, ignorando linhas vazias.
 */
function ga_home_list( string $key ): array {
	return array_values( array_filter( array_map( 'trim', explode( "\n", ga_home( $key ) ) ) ) );
}

/**
 * Perguntas frequentes: blocos separados por linha em branco, a primeira linha é a pergunta.
 */
function ga_home_faq(): array {
	$faq = [];
	foreach ( preg_split( "/\n\s*\n/", str_replace( "\r", '', ga_home( 'faq_items' ) ) ) as $block ) {
		$lines = array_values( array_filter( array_map( 'trim', explode( "\n", $block ) ) ) );
		if ( $lines ) {
			$faq[] = [ array_shift( $lines ), implode( ' ', $lines ) ];
		}
	}
	return $faq;
}

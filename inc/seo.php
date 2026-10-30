<?php
/**
 * Título e descrição para buscadores, nos três idiomas, com os termos que as pessoas
 * usam para procurar overlays de OBS ("overlay grátis para OBS", "overlay Twitch",
 * "tela de começando"...). Vale para o Rank Math e para o <title> padrão do WordPress.
 */

defined( 'ABSPATH' ) || exit;

/**
 * SEO da página atual: [ 'title' => sem o nome do site, 'description' => ... ], ou vazio.
 */
function ga_seo(): array {
	if ( is_front_page() ) {
		return [
			'title'       => __( 'Overlays e templates grátis para OBS e Twitch', 'getalive' ),
			'description' => __( 'Baixe overlays animados grátis para OBS Studio: telas de começando, jogo, já volto e encerrando, com chat e alertas para Twitch, YouTube e Kick. Instala em dois cliques.', 'getalive' ),
		];
	}
	if ( is_post_type_archive( 'ga_template' ) ) {
		return [
			'title'       => __( 'Overlays grátis para OBS: todos os templates', 'getalive' ),
			'description' => __( 'Todos os overlays grátis para OBS Studio: pacotes animados com telas de começando, jogo, já volto e encerrando, chat e alertas para Twitch, YouTube e Kick.', 'getalive' ),
		];
	}
	if ( is_tax( 'ga_style' ) ) {
		$name = ga_t( single_term_title( '', false ) );
		return [
			/* translators: %s: estilo (ex.: Retrô) */
			'title'       => sprintf( __( 'Overlays %s para OBS', 'getalive' ), $name ),
			/* translators: %s: estilo (ex.: Retrô) */
			'description' => sprintf( __( 'Overlays grátis para OBS no estilo %s, com cenas animadas de começando, jogo, já volto e encerrando, chat e alertas para Twitch, YouTube e Kick.', 'getalive' ), $name ),
		];
	}
	if ( is_singular( 'ga_template' ) ) {
		/* translators: %s: nome do template */
		return [ 'title' => sprintf( __( '%s: overlay animado grátis para OBS e Twitch', 'getalive' ), get_the_title( get_queried_object() ) ) ];
	}
	if ( ga_is_page( 'install' ) ) {
		return [
			'title'       => __( 'Como colocar overlay no OBS: passo a passo com imagens', 'getalive' ),
			'description' => __( 'Como colocar um overlay no OBS Studio em dois cliques: extraia o ZIP, rode o instalador e escolha a coleção de cenas. Passo a passo com capturas reais do OBS.', 'getalive' ),
		];
	}
	if ( ga_is_page( 'request' ) ) {
		return [
			'title'       => __( 'Peça um overlay para OBS', 'getalive' ),
			'description' => __( 'Não achou o overlay que queria? Conte a vibe e as cenas que você usa. As ideias mais pedidas viram os próximos templates grátis para OBS.', 'getalive' ),
		];
	}
	return [];
}

/** Título completo: na home o nome do site vem antes, nas outras páginas depois. */
function ga_seo_title( string $fallback ): string {
	$seo = ga_seo();
	if ( empty( $seo['title'] ) ) {
		return $fallback;
	}
	$name = get_bloginfo( 'name' );
	return is_front_page() ? "{$name} - {$seo['title']}" : "{$seo['title']} - {$name}";
}

add_filter( 'rank_math/frontend/title', 'ga_seo_title', 20 );
add_filter( 'rank_math/frontend/description', fn( $description ) => ga_seo()['description'] ?? $description, 20 );

// Sem o Rank Math, o <title> do WordPress.
add_filter( 'pre_get_document_title', function ( $title ) {
	return defined( 'RANK_MATH_VERSION' ) ? $title : ga_seo_title( $title );
}, 20 );

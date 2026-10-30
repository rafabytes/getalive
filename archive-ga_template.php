<?php
/**
 * Listagem de templates. O HTML do servidor serve de base (SEO, sem JS);
 * o componente React TemplateExplorer assume com busca, filtros e ordenação.
 */

defined( 'ABSPATH' ) || exit;
get_header();

// No estilo, o HTML lista só os dele, mas o explorador recebe o catálogo inteiro já filtrado.
$ga_posts  = $GLOBALS['wp_query']->posts;
$ga_all    = is_tax( 'ga_style' ) ? ga_query_templates() : $ga_posts;
$ga_styles = get_terms( [ 'taxonomy' => 'ga_style', 'hide_empty' => true ] );
$ga_active = is_tax( 'ga_style' ) ? get_queried_object()->slug : '';
$ga_data   = [
	'items'    => array_map( 'ga_template_card_data', $ga_all ),
	// array_values: get_terms() deixa buracos nos índices ao esconder estilos vazios, e aí o JSON vira objeto.
	'styles'   => array_values( array_map( fn( $t ) => [ 'slug' => $t->slug, 'name' => $t->name ], is_wp_error( $ga_styles ) ? [] : $ga_styles ) ),
	'style'    => $ga_active,
	'archive'  => ga_page_url( 'templates' ),
	'request'  => ga_page_url( 'request' ),
	'loggedIn' => is_user_logged_in(),
	'loginUrl' => ga_login_url(),
];
?>

<section class="relative overflow-hidden pb-12 pt-[calc(var(--header-h)+3rem)] md:pt-[calc(var(--header-h)+5rem)]">
	<div class="pointer-events-none absolute -right-60 -top-60 size-[40rem] rounded-full bg-signal/25 blur-[140px]" aria-hidden="true"></div>
	<div class="wrap relative">
		<div class="flex flex-col justify-between gap-8 md:flex-row md:items-end">
			<div>
				<p class="eyebrow eyebrow--dot mb-6" data-reveal><?php esc_html_e( 'Overlays grátis para OBS', 'getalive' ); ?> · <?php echo esc_html( ga_plural( count( $ga_posts ), 'template', 'templates' ) ); ?></p>
				<h1 class="text-display-xl font-semibold text-white" style="font-variation-settings:'wdth' 80" data-split>
					<span class="split-line"><span><?php echo is_tax( 'ga_style' ) ? esc_html( ga_t( single_term_title( '', false ) ) ) : esc_html__( 'Templates', 'getalive' ); ?></span></span>
				</h1>
			</div>
			<p class="max-w-sm text-lg text-ink-300" data-reveal style="--delay:.2s">
				<?php esc_html_e( 'Overlays para OBS Studio com todas as cenas da live: começando, jogo, conversa, já volto e encerrando. Passe o mouse para ver as cenas de cada template.', 'getalive' ); ?>
			</p>
		</div>
	</div>
</section>

<section class="pb-28 md:pb-40">
	<div class="wrap">
		<div data-react="TemplateExplorer" data-props="<?php echo esc_attr( wp_json_encode( $ga_data ) ); ?>">
			<div class="grid gap-x-8 gap-y-14 md:grid-cols-2 xl:grid-cols-3">
				<?php foreach ( $ga_posts as $i => $p ) : ?>
					<?php get_template_part( 'template-parts/card', null, [ 'post' => $p, 'index' => $i ] ); ?>
				<?php endforeach; ?>
				<?php get_template_part( 'template-parts/card-soon' ); ?>
			</div>
		</div>
	</div>
</section>

<?php
get_footer();

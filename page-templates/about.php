<?php
/**
 * Template Name: Sobre
 */

defined( 'ABSPATH' ) || exit;
get_header();

$ga_count = count( ga_query_templates( [ 'fields' => 'ids' ] ) );

while ( have_posts() ) :
	the_post();
	?>
	<section class="relative overflow-hidden pb-20 pt-[calc(var(--header-h)+3rem)] md:pt-[calc(var(--header-h)+5rem)]">
		<div class="pointer-events-none absolute -right-40 top-0 size-[44rem] rounded-full bg-signal/25 blur-[150px]" aria-hidden="true"></div>
		<div class="wrap relative">
			<p class="eyebrow eyebrow--dot mb-8" data-reveal>Sobre o <?php echo esc_html( get_bloginfo( 'name' ) ); ?></p>
			<h1 class="max-w-6xl text-display-xl font-semibold text-white" style="font-variation-settings:'wdth' 78" data-split>
				<span class="split-line"><span>Feito para</span></span>
				<span class="split-line"><span>quem quer</span></span>
				<span class="split-line"><span><span class="text-outline">só apertar</span> <span class="text-lime">play.</span></span></span>
			</h1>
		</div>
	</section>

	<section class="pb-24 md:pb-36">
		<div class="wrap grid gap-16 lg:grid-cols-12">
			<aside class="lg:col-span-4">
				<div class="grid grid-cols-2 gap-4 lg:sticky lg:top-[calc(var(--header-h)+var(--admin-bar,0px)+2rem)] lg:grid-cols-1" data-reveal>
					<div class="rounded-3xl bg-lime p-6 text-ink-950">
						<p class="font-display text-6xl font-semibold tracking-tighter">2</p>
						<p class="mt-1 font-medium">cliques para instalar</p>
					</div>
					<div class="rounded-3xl bg-signal p-6 text-white">
						<p class="font-display text-6xl font-semibold tracking-tighter">1</p>
						<p class="mt-1 font-medium">arquivo para personalizar</p>
					</div>
					<div class="col-span-2 rounded-3xl border border-white/10 p-6 lg:col-span-1">
						<p class="font-display text-6xl font-semibold tracking-tighter text-white"><?php echo esc_html( $ga_count ); ?></p>
						<p class="mt-1 font-medium text-ink-300"><?php echo 1 === $ga_count ? 'template publicado' : 'templates publicados'; ?>, e contando</p>
					</div>
				</div>
			</aside>
			<div class="prose-ga lg:col-span-7 lg:col-start-6" data-reveal>
				<?php the_content(); ?>
			</div>
		</div>
	</section>

	<section class="rounded-t-[2.5rem] bg-paper py-24 text-ink-950 md:rounded-t-[4rem] md:py-32">
		<div class="wrap">
			<p class="eyebrow mb-5 !text-ink-500" data-reveal>Princípios</p>
			<div class="grid gap-4 md:grid-cols-3">
				<?php
				foreach ( [
					[ 'Instalou, funcionou', 'Nada de montar cena por cena. O instalador cria a coleção inteira com os caminhos certos.', 'bolt' ],
					[ 'Leve de verdade', 'Overlays em HTML e CSS, sem programas extras. Seu PC fica para o jogo e para o encoder.', 'monitor' ],
					[ 'Seu, do seu jeito', 'Nome, redes, agenda e textos num único arquivo. Ninguém precisa saber programar.', 'wand' ],
				] as $i => [ $title, $text, $icon ] ) :
					?>
					<div class="rounded-3xl bg-white/70 p-8" data-reveal style="--delay:<?php echo esc_attr( $i * 0.1 ); ?>s">
						<span class="inline-flex size-12 items-center justify-center rounded-full bg-ink-950 text-lime"><?php echo ga_icon( $icon, 'size-5' ); ?></span>
						<h2 class="mt-8 text-3xl tracking-tight"><?php echo esc_html( $title ); ?></h2>
						<p class="mt-3 text-ink-600"><?php echo esc_html( $text ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
			<div class="mt-16 flex flex-col items-start justify-between gap-8 border-t border-ink-950/10 pt-12 md:flex-row md:items-center" data-reveal>
				<p class="max-w-xl font-display text-3xl font-medium leading-tight tracking-tight">Tem uma ideia ou quer falar com a gente? Escreva para <?php echo do_shortcode( '[ga_contact_email]' ); ?>.</p>
				<a href="<?php echo esc_url( ga_page_url( 'templates' ) ); ?>" class="btn btn--light btn--lg"><span>Ver templates</span><?php echo ga_icon( 'arrow-right', 'btn__arrow' ); ?></a>
			</div>
		</div>
	</section>
	<?php
endwhile;
get_footer();

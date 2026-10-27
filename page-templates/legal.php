<?php
/**
 * Template Name: Texto legal (com índice)
 */

defined( 'ABSPATH' ) || exit;
get_header();

while ( have_posts() ) :
	the_post();
	$ga_content = apply_filters( 'the_content', get_the_content() );
	preg_match_all( '/<h2[^>]*id="([^"]+)"[^>]*>(.*?)<\/h2>/s', $ga_content, $ga_toc, PREG_SET_ORDER );
	?>
	<section class="relative overflow-hidden pb-14 pt-[calc(var(--header-h)+3rem)] md:pt-[calc(var(--header-h)+5rem)]">
		<div class="pointer-events-none absolute -right-60 -top-60 size-[40rem] rounded-full bg-signal/20 blur-[140px]" aria-hidden="true"></div>
		<div class="wrap relative">
			<p class="eyebrow eyebrow--dot mb-6" data-reveal>Atualizada em <?php echo esc_html( get_the_modified_date( 'j \d\e F \d\e Y' ) ); ?></p>
			<h1 class="max-w-5xl text-display-lg font-semibold text-white" style="font-variation-settings:'wdth' 80" data-split>
				<span class="split-line"><span><?php the_title(); ?></span></span>
			</h1>
		</div>
	</section>

	<section class="pb-28 md:pb-40">
		<div class="wrap grid gap-16 lg:grid-cols-12">
			<?php if ( $ga_toc ) : ?>
				<nav class="hidden lg:col-span-3 lg:block" aria-label="Nesta página">
					<div class="sticky top-[calc(var(--header-h)+var(--admin-bar,0px)+2rem)]">
						<p class="eyebrow mb-5">Nesta página</p>
						<ol class="space-y-2.5 border-l border-white/10 text-sm" data-toc>
							<?php foreach ( $ga_toc as [ , $id, $label ] ) : ?>
								<li><a href="#<?php echo esc_attr( $id ); ?>" class="-ml-px block border-l border-transparent pl-4 text-ink-400 transition hover:text-white" data-toc-link><?php echo esc_html( wp_strip_all_tags( $label ) ); ?></a></li>
							<?php endforeach; ?>
						</ol>
						<div class="mt-8"><?php echo do_shortcode( '[ga_cookie_preferences]' ); ?></div>
					</div>
				</nav>
			<?php endif; ?>
			<div class="prose-ga lg:col-span-8 lg:col-start-5">
				<?php echo $ga_content; // phpcs:ignore WordPress.Security.EscapeOutput -- conteúdo do editor já filtrado. ?>
			</div>
		</div>
	</section>
	<?php
endwhile;
get_footer();

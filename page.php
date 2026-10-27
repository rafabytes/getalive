<?php
/**
 * Página padrão.
 */

defined( 'ABSPATH' ) || exit;
get_header();

while ( have_posts() ) :
	the_post();
	?>
	<section class="relative overflow-hidden pb-14 pt-[calc(var(--header-h)+3rem)] md:pt-[calc(var(--header-h)+5rem)]">
		<div class="pointer-events-none absolute -right-60 -top-60 size-[40rem] rounded-full bg-signal/20 blur-[140px]" aria-hidden="true"></div>
		<div class="wrap relative">
			<h1 class="max-w-5xl text-display-lg font-semibold text-white" style="font-variation-settings:'wdth' 80" data-split>
				<span class="split-line"><span><?php the_title(); ?></span></span>
			</h1>
		</div>
	</section>
	<section class="pb-28 md:pb-40">
		<div class="wrap">
			<div class="prose-ga mx-auto">
				<?php the_content(); ?>
			</div>
		</div>
	</section>
	<?php
endwhile;
get_footer();

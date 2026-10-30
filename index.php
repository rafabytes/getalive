<?php
/**
 * Fallback: blog, busca e arquivos genéricos.
 */

defined( 'ABSPATH' ) || exit;
get_header();
?>
<section class="pb-28 pt-[calc(var(--header-h)+3rem)] md:pb-40 md:pt-[calc(var(--header-h)+5rem)]">
	<div class="wrap">
		<h1 class="mb-16 text-display-lg font-semibold text-white" style="font-variation-settings:'wdth' 80">
			<?php
			if ( is_search() ) {
				/* translators: %s: termo buscado */
				printf( esc_html__( 'Busca: %s', 'getalive' ), esc_html( get_search_query() ) );
			} elseif ( is_archive() ) {
				echo esc_html( wp_strip_all_tags( get_the_archive_title() ) );
			} else {
				echo esc_html( get_the_title( (int) get_option( 'page_for_posts' ) ) ?: __( 'Novidades', 'getalive' ) );
			}
			?>
		</h1>

		<?php if ( have_posts() ) : ?>
			<div class="grid gap-x-8 gap-y-14 md:grid-cols-2 xl:grid-cols-3">
				<?php
				while ( have_posts() ) :
					the_post();
					if ( 'ga_template' === get_post_type() ) {
						get_template_part( 'template-parts/card', null, [ 'post' => get_post() ] );
						continue;
					}
					?>
					<article class="group relative rounded-3xl border border-white/10 p-6 transition hover:border-white/25">
						<p class="eyebrow mb-4"><?php echo esc_html( get_the_date() ); ?></p>
						<h2 class="font-display text-2xl font-semibold tracking-tight text-white"><a href="<?php the_permalink(); ?>" class="after:absolute after:inset-0"><?php the_title(); ?></a></h2>
						<p class="mt-3 text-ink-400"><?php echo esc_html( get_the_excerpt() ); ?></p>
					</article>
				<?php endwhile; ?>
			</div>
			<div class="mt-16 flex justify-center gap-3">
				<?php
				echo wp_kses_post( paginate_links( [
					'prev_text' => '←',
					'next_text' => '→',
				] ) ?? '' );
				?>
			</div>
		<?php else : ?>
			<?php get_template_part( 'template-parts/empty', null, [
				'icon'  => 'search',
				'title' => __( 'Nada por aqui', 'getalive' ),
				'text'  => __( 'Não encontramos conteúdo. Que tal ver os templates?', 'getalive' ),
				'cta'   => [ __( 'Ver templates', 'getalive' ), ga_page_url( 'templates' ) ],
			] ); ?>
		<?php endif; ?>
	</div>
</section>
<?php
get_footer();

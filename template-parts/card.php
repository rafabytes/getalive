<?php
/**
 * Card de template. Recebe $args['post'] e, opcionalmente, $args['index'] e $args['size'] ('lg').
 */

defined( 'ABSPATH' ) || exit;

$t      = ga_template( $args['post'] );
$images = array_slice( ga_template_images( $t ), 0, 6 );
$index  = (int) ( $args['index'] ?? 0 );
?>
<article class="tcard group" style="--card-accent: <?php echo esc_attr( $t['accent'] ); ?>; --delay: <?php echo esc_attr( ( $index % 3 ) * 0.08 ); ?>s" data-reveal>
	<span class="tcard__glow" aria-hidden="true"></span>
	<div class="relative">
		<a href="<?php echo esc_url( $t['url'] ); ?>" class="tcard__media block" data-card-cycle tabindex="-1" aria-hidden="true">
			<?php foreach ( $images as $i => $src ) : ?>
				<img src="<?php echo esc_url( $src ); ?>" alt="" loading="<?php echo $i ? 'lazy' : 'eager'; ?>" decoding="async" class="<?php echo 0 === $i ? 'is-active' : ''; ?>">
			<?php endforeach; ?>
			<?php if ( count( $images ) > 1 ) : ?>
				<span class="tcard__progress"><?php echo str_repeat( '<span></span>', count( $images ) ); ?></span>
			<?php endif; ?>
		</a>
		<div class="absolute left-3 top-3 flex gap-1.5">
			<span class="tag tag--live"><span class="size-1.5 rounded-full bg-white"></span>Live</span>
			<?php if ( ga_premium_enabled() && $t['premium'] ) : ?>
				<span class="tag bg-lime text-ink-950"><?php echo esc_html( ga_premium_price_html( $t['id'] ) ); ?></span>
			<?php endif; ?>
		</div>
		<div class="absolute right-3 top-3 z-10">
			<?php echo ga_favorite_button( $t['id'] ); ?>
		</div>
	</div>
	<div class="mt-5 flex items-start justify-between gap-4">
		<div class="min-w-0">
			<h3 class="tcard__title"><a href="<?php echo esc_url( $t['url'] ); ?>" class="after:absolute after:inset-0 after:content-['']"><?php echo esc_html( $t['title'] ); ?></a></h3>
			<?php if ( $t['tagline'] ) : ?>
				<p class="mt-1.5 line-clamp-2 text-ink-400"><?php echo esc_html( $t['tagline'] ); ?></p>
			<?php endif; ?>
		</div>
		<span class="mt-1 inline-flex size-10 shrink-0 items-center justify-center rounded-full border border-white/10 transition duration-500 ease-expo group-hover:rotate-45 group-hover:border-lime group-hover:bg-lime group-hover:text-ink-950" aria-hidden="true">
			<?php echo ga_icon( 'arrow-up-right', 'size-4' ); ?>
		</span>
	</div>
	<div class="mt-4 flex flex-wrap gap-1.5">
		<span class="tag"><?php echo esc_html( ga_plural( count( $t['scenes'] ), 'cena', 'cenas' ) ); ?></span>
		<?php foreach ( $t['styles'] as $style ) : ?>
			<span class="tag"><?php echo esc_html( $style['name'] ); ?></span>
		<?php endforeach; ?>
	</div>
</article>

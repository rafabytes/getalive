<?php
/**
 * Estado vazio. $args: icon, title, text, cta [label, url].
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="flex flex-col items-center rounded-[2rem] border border-dashed border-white/15 px-6 py-20 text-center">
	<span class="inline-flex size-16 items-center justify-center rounded-full bg-white/[0.05] text-ink-300"><?php echo ga_icon( $args['icon'], 'size-7' ); ?></span>
	<h2 class="mt-6 font-display text-3xl font-semibold tracking-tight text-white"><?php echo esc_html( $args['title'] ); ?></h2>
	<p class="mt-2 max-w-sm text-ink-400"><?php echo esc_html( $args['text'] ); ?></p>
	<?php if ( ! empty( $args['cta'] ) ) : ?>
		<a href="<?php echo esc_url( $args['cta'][1] ); ?>" class="btn btn--primary mt-8"><span><?php echo esc_html( $args['cta'][0] ); ?></span><?php echo ga_icon( 'arrow-right', 'btn__arrow' ); ?></a>
	<?php endif; ?>
</div>

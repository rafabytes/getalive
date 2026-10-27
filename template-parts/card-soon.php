<?php
/**
 * Card "em breve" para completar a grade quando há poucos templates.
 */

defined( 'ABSPATH' ) || exit;
?>
<a href="<?php echo esc_url( ga_page_url( 'request' ) ); ?>" class="tcard group" data-reveal style="--delay:.16s">
	<span class="tcard__media flex flex-col items-center justify-center gap-4 border border-dashed border-white/15 !bg-transparent text-center transition duration-500 group-hover:border-lime/60">
		<span class="inline-flex size-14 items-center justify-center rounded-full border border-white/15 transition duration-500 ease-expo group-hover:rotate-90 group-hover:border-lime group-hover:bg-lime group-hover:text-ink-950">
			<?php echo ga_icon( 'plus', 'size-6' ); ?>
		</span>
		<span class="eyebrow">Próximo template</span>
	</span>
	<span class="mt-5 block">
		<span class="tcard__title block">Pode ser o seu</span>
		<span class="mt-1.5 block text-ink-400">Novos templates saem a partir dos pedidos. Conte a sua ideia.</span>
	</span>
</a>

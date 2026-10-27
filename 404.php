<?php
/**
 * Página não encontrada.
 */

defined( 'ABSPATH' ) || exit;
get_header();
?>
<section class="relative flex min-h-[90svh] items-center overflow-hidden pt-[var(--header-h)]">
	<div class="pointer-events-none absolute left-1/2 top-1/2 size-[44rem] -translate-x-1/2 -translate-y-1/2 rounded-full bg-signal/25 blur-[150px]" aria-hidden="true"></div>
	<div class="wrap relative text-center">
		<p class="eyebrow mb-6 justify-center"><span class="live-dot"></span> Sinal perdido</p>
		<h1 class="font-display font-semibold leading-[0.8] tracking-[-0.06em] text-white" style="font-size:clamp(8rem,30vw,26rem);font-variation-settings:'wdth' 75" data-glitch>404</h1>
		<p class="mx-auto mt-6 max-w-md text-lg text-ink-300">Essa cena não existe na coleção. Volte para o início ou escolha um template.</p>
		<div class="mt-10 flex flex-wrap justify-center gap-3">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn--outline"><span>Página inicial</span></a>
			<a href="<?php echo esc_url( ga_page_url( 'templates' ) ); ?>" class="btn btn--primary"><span>Ver templates</span><?php echo ga_icon( 'arrow-right', 'btn__arrow' ); ?></a>
		</div>
	</div>
</section>
<?php
get_footer();

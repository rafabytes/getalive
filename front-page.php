<?php
/**
 * Página inicial.
 */

defined( 'ABSPATH' ) || exit;
get_header();

$ga_all      = ga_query_templates();
$ga_featured = array_values( array_filter( $ga_all, fn( $p ) => get_post_meta( $p->ID, '_ga_featured', true ) ) );
$ga_hero     = $ga_featured[0] ?? ( $ga_all[0] ?? null );
$ga_hero_t   = $ga_hero ? ga_template( $ga_hero ) : null;
$ga_grid     = array_slice( $ga_featured ?: $ga_all, 0, 6 );

$ga_scene_count = array_sum( array_map( fn( $p ) => count( (array) get_post_meta( $p->ID, '_ga_scenes', true ) ), $ga_all ) );
$ga_downloads   = array_sum( array_map( fn( $p ) => (int) get_post_meta( $p->ID, '_ga_downloads', true ), $ga_all ) );
?>

<!-- Hero ------------------------------------------------------------- -->
<section class="relative overflow-hidden pb-20 pt-[calc(var(--header-h)+3rem)] md:pb-28 md:pt-[calc(var(--header-h)+5rem)] lg:flex lg:min-h-[100svh] lg:items-center lg:pb-16 lg:pt-[calc(var(--header-h)+2rem)]">
	<div class="pointer-events-none absolute -right-40 -top-40 size-[44rem] rounded-full bg-signal/30 blur-[140px]" aria-hidden="true"></div>
	<div class="pointer-events-none absolute -left-60 top-1/2 size-[30rem] rounded-full bg-live/10 blur-[120px]" aria-hidden="true"></div>

	<!-- Empilhado até lg; a partir dele, texto à esquerda e monitor à direita, cabendo na altura da tela. -->
	<div class="wrap relative grid gap-12 lg:grid-cols-12 lg:items-center lg:gap-10">
		<div class="lg:col-span-5">
			<p class="eyebrow mb-8 lg:mb-6" data-reveal><span class="live-dot"></span> <?php echo esc_html( ga_home( 'hero_eyebrow' ) ); ?></p>

			<h1 class="text-display-xl font-semibold text-white lg:text-[clamp(3.5rem,min(6.5vw,14svh),6.75rem)]" style="font-variation-settings:'wdth' 80" data-split>
				<span class="split-line"><span><?php echo ga_home_line( 'hero_title_1', 'class="not-italic text-lime"' ); ?></span></span>
				<span class="split-line"><span><?php echo ga_home_line( 'hero_title_2', 'class="not-italic text-lime"' ); ?></span></span>
				<span class="split-line"><span class="text-outline text-white"><?php echo ga_home_line( 'hero_title_3', 'class="not-italic text-lime"' ); ?></span></span>
			</h1>

			<div class="mt-12 lg:mt-8" data-reveal style="--delay:.3s">
				<p class="max-w-md text-lg leading-relaxed text-ink-300">
					<?php echo esc_html( ga_home( 'hero_text' ) ); ?>
				</p>
				<div class="mt-8 flex flex-wrap gap-3">
					<a href="<?php echo esc_url( ga_page_url( 'templates' ) ); ?>" class="btn btn--primary btn--lg" data-magnetic><span><?php echo esc_html( ga_home( 'hero_cta' ) ); ?></span><?php echo ga_icon( 'arrow-right', 'btn__arrow' ); ?></a>
					<?php if ( ! is_user_logged_in() ) : ?>
						<a href="<?php echo esc_url( ga_login_url( '', 'register' ) ); ?>" class="btn btn--outline btn--lg" data-login-link><span>Criar conta grátis</span></a>
					<?php endif; ?>
				</div>

				<?php if ( ga_home( 'hero_stats' ) ) : ?>
				<dl class="mt-12 grid grid-cols-3 gap-6 border-t border-white/10 pt-6">
					<div>
						<dt class="eyebrow">Templates</dt>
						<dd class="mt-1 font-display text-4xl font-semibold tracking-tight text-white" data-count="<?php echo esc_attr( count( $ga_all ) ); ?>"><?php echo esc_html( count( $ga_all ) ); ?></dd>
					</div>
					<div>
						<dt class="eyebrow">Cenas</dt>
						<dd class="mt-1 font-display text-4xl font-semibold tracking-tight text-white" data-count="<?php echo esc_attr( $ga_scene_count ); ?>"><?php echo esc_html( $ga_scene_count ); ?></dd>
					</div>
					<?php if ( ga_premium_enabled() ) : ?>
						<div>
							<dt class="eyebrow">Downloads</dt>
							<dd class="mt-1 font-display text-4xl font-semibold tracking-tight text-lime" data-count="<?php echo esc_attr( $ga_downloads ); ?>"><?php echo esc_html( number_format_i18n( $ga_downloads ) ); ?></dd>
						</div>
					<?php else : ?>
						<div>
							<dt class="eyebrow">Preço</dt>
							<dd class="mt-1 font-display text-4xl font-semibold tracking-tight text-lime"><?php echo esc_html( ga_home( 'hero_price' ) ); ?></dd>
						</div>
					<?php endif; ?>
				</dl>
				<?php endif; ?>
			</div>
		</div>

		<div class="lg:col-span-7" data-reveal style="--delay:.15s">
				<?php if ( $ga_hero_t && $ga_hero_t['scenes'] ) : ?>
					<div class="monitor" data-stage>
						<div class="monitor__bar">
							<span class="flex items-center gap-2"><span class="live-dot"></span> REC <span class="text-white" data-timecode>00:00:00</span></span>
							<span class="hidden sm:block"><?php echo esc_html( $ga_hero_t['title'] ); ?> · <?php echo esc_html( $ga_hero_t['resolution'] ); ?></span>
							<span class="meters" aria-hidden="true"><span></span><span></span><span></span><span></span><span></span></span>
						</div>
						<a href="<?php echo esc_url( $ga_hero_t['url'] ); ?>" class="monitor__screen block" aria-label="Ver o template <?php echo esc_attr( $ga_hero_t['title'] ); ?>">
							<?php foreach ( $ga_hero_t['scenes'] as $i => $scene ) : ?>
								<?php
								echo ga_image( $scene['image'], 'ga-stage', 'hero', [
									'alt'   => 'Cena ' . $scene['title'] . ' do template ' . $ga_hero_t['title'],
									'class' => 0 === $i ? 'is-active' : '',
								] + ( $i ? [ 'loading' => 'lazy' ] : [ 'loading' => false, 'fetchpriority' => 'high' ] ) );
								?>
							<?php endforeach; ?>
						</a>
						<div class="scene-dock" role="tablist" aria-label="Cenas">
							<?php foreach ( $ga_hero_t['scenes'] as $i => $scene ) : ?>
								<button type="button" role="tab" aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>" data-scene="<?php echo (int) $i; ?>"><?php echo esc_html( $scene['title'] ); ?></button>
							<?php endforeach; ?>
						</div>
					</div>
				<?php else : ?>
					<div class="monitor">
						<div class="monitor__bar"><span class="flex items-center gap-2"><span class="live-dot"></span> REC</span><span class="meters"><span></span><span></span><span></span><span></span><span></span></span></div>
						<div class="monitor__screen flex items-center justify-center bg-gradient-to-br from-signal-700 via-ink-900 to-ink-950">
							<span class="font-display text-display-md text-white/80">em breve</span>
						</div>
					</div>
				<?php endif; ?>
			</div>
	</div>
</section>

<!-- Templates -------------------------------------------------------- -->
<?php if ( $ga_grid ) : ?>
<section class="py-24 md:py-36">
	<div class="wrap">
		<div class="mb-14">
			<p class="eyebrow eyebrow--dot mb-5" data-reveal>[01] Catálogo</p>
			<h2 class="text-display-lg text-white" data-split>
				<span class="split-line"><span><?php echo ga_home_line( 'catalog_title_1', 'class="not-italic text-ink-500"' ); ?></span></span>
				<span class="split-line"><span><?php echo ga_home_line( 'catalog_title_2', 'class="not-italic text-ink-500"' ); ?></span></span>
			</h2>
		</div>

		<div class="grid gap-x-8 gap-y-14 md:grid-cols-2 xl:grid-cols-3">
			<?php foreach ( $ga_grid as $i => $p ) : ?>
				<?php get_template_part( 'template-parts/card', null, [ 'post' => $p, 'index' => $i ] ); ?>
			<?php endforeach; ?>
			<?php if ( count( $ga_grid ) < 3 ) : ?>
				<?php get_template_part( 'template-parts/card-soon' ); ?>
			<?php endif; ?>
		</div>

		<div class="mt-16 flex justify-center md:mt-20">
			<a href="<?php echo esc_url( ga_page_url( 'templates' ) ); ?>" class="btn btn--outline" data-reveal><span><?php echo esc_html( ga_home( 'catalog_cta' ) ); ?></span><?php echo ga_icon( 'arrow-right', 'btn__arrow' ); ?></a>
		</div>
	</div>
</section>
<?php endif; ?>

<!-- Como funciona ---------------------------------------------------- -->
<section class="relative overflow-hidden rounded-t-[2.5rem] bg-paper py-24 text-ink-950 md:rounded-t-[4rem] md:py-36">
	<div class="wrap">
		<div class="grid gap-12 lg:grid-cols-12">
			<div class="lg:col-span-5">
				<p class="eyebrow mb-5 !text-ink-500" data-reveal>[02] Como funciona</p>
				<h2 class="text-display-lg" data-split>
					<?php $ga_signal = 'class="italic text-signal" style="font-variation-settings:\'wdth\' 100"'; ?>
					<span class="split-line"><span><?php echo ga_home_line( 'how_title_1', $ga_signal ); ?></span></span>
					<span class="split-line"><span><?php echo ga_home_line( 'how_title_2', $ga_signal ); ?></span></span>
				</h2>
				<p class="mt-8 max-w-md text-lg text-ink-600" data-reveal><?php echo esc_html( ga_home( 'how_text' ) ); ?></p>
				<a href="<?php echo esc_url( ga_page_url( 'install' ) ); ?>" class="btn btn--light mt-8" data-reveal><span><?php echo esc_html( ga_home( 'how_cta' ) ); ?></span><?php echo ga_icon( 'arrow-right', 'btn__arrow' ); ?></a>
			</div>

			<ol class="space-y-4 lg:col-span-7">
				<?php
				foreach ( [ 1, 2, 3 ] as $i => $n ) :
					$title = ga_home( "step_{$n}_title" );
					$text  = ga_home( "step_{$n}_text" );
					?>
					<li class="group grid grid-cols-[auto_1fr] gap-6 rounded-3xl border border-ink-950/10 bg-white/60 p-6 transition duration-500 ease-expo hover:bg-white md:gap-10 md:p-8" data-reveal style="--delay:<?php echo esc_attr( $i * 0.1 ); ?>s">
						<span class="font-display text-6xl font-semibold leading-none tracking-tighter text-ink-950/15 transition group-hover:text-signal md:text-7xl">0<?php echo (int) $i + 1; ?></span>
						<div>
							<h3 class="flex items-center gap-3 text-2xl tracking-tight md:text-3xl"><?php echo esc_html( $title ); ?></h3>
							<p class="mt-2 text-ink-600"><?php echo esc_html( $text ); ?></p>
						</div>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>

		<!-- Bento: o que vem no pacote -->
		<div class="mt-24 grid auto-rows-[minmax(14rem,auto)] gap-4 md:mt-36 md:grid-cols-6">
			<div class="relative overflow-hidden rounded-3xl bg-ink-950 p-8 text-white md:col-span-4 md:row-span-2" data-reveal>
				<p class="eyebrow mb-4">[03] No pacote</p>
				<h3 class="max-w-lg text-display-md"><?php echo esc_html( ga_home( 'package_title' ) ); ?></h3>
				<ul class="mt-10 grid gap-3 sm:grid-cols-2">
					<?php foreach ( ga_home_list( 'package_items' ) as $item ) : ?>
						<li class="flex gap-3 text-ink-300"><span class="mt-1 inline-flex size-5 shrink-0 items-center justify-center rounded-full bg-lime text-ink-950"><?php echo ga_icon( 'check', 'size-3' ); ?></span><?php echo esc_html( $item ); ?></li>
					<?php endforeach; ?>
				</ul>
				<div class="pointer-events-none absolute -bottom-24 -right-24 size-80 rounded-full bg-signal/40 blur-3xl" aria-hidden="true"></div>
			</div>
			<div class="flex flex-col justify-between rounded-3xl bg-signal p-8 text-white md:col-span-2" data-reveal style="--delay:.1s">
				<?php echo ga_icon( 'message', 'size-8' ); ?>
				<div>
					<h3 class="text-2xl tracking-tight"><?php echo esc_html( ga_home( 'card_1_title' ) ); ?></h3>
					<p class="mt-1 text-white/75"><?php echo esc_html( ga_home( 'card_1_text' ) ); ?></p>
				</div>
			</div>
			<div class="flex flex-col justify-between rounded-3xl border border-ink-950/10 bg-white p-8 md:col-span-2" data-reveal style="--delay:.2s">
				<?php echo ga_icon( 'twitch', 'size-8 text-signal' ); ?>
				<div>
					<h3 class="text-2xl tracking-tight"><?php echo esc_html( ga_home( 'card_2_title' ) ); ?></h3>
					<p class="mt-1 text-ink-600"><?php echo esc_html( ga_home( 'card_2_text' ) ); ?></p>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- Solicitar -------------------------------------------------------- -->
<section class="relative overflow-hidden bg-ink-950 py-28 md:py-44">
	<div class="pointer-events-none absolute left-1/2 top-1/2 size-[50rem] -translate-x-1/2 -translate-y-1/2 rounded-full bg-signal/25 blur-[160px]" aria-hidden="true"></div>
	<div class="wrap relative text-center">
		<p class="eyebrow eyebrow--dot mb-6 justify-center" data-reveal>[04] Pedidos abertos</p>
		<h2 class="mx-auto max-w-5xl text-display-lg text-white" data-split>
			<span class="split-line"><span><?php echo ga_home_line( 'request_title_1', 'class="not-italic text-lime"' ); ?></span></span>
			<span class="split-line"><span><?php echo ga_home_line( 'request_title_2', 'class="not-italic text-lime"' ); ?></span></span>
		</h2>
		<p class="mx-auto mt-8 max-w-xl text-lg text-ink-300" data-reveal><?php echo esc_html( ga_home( 'request_text' ) ); ?></p>
		<div class="mt-10 flex justify-center" data-reveal>
			<a href="<?php echo esc_url( ga_page_url( 'request' ) ); ?>" class="btn btn--primary btn--lg" data-magnetic><span><?php echo esc_html( ga_home( 'request_cta' ) ); ?></span><?php echo ga_icon( 'wand', 'btn__arrow' ); ?></a>
		</div>
	</div>
</section>

<!-- FAQ -------------------------------------------------------------- -->
<section class="border-t border-white/[0.06] py-24 md:py-32">
	<div class="wrap grid gap-12 lg:grid-cols-12">
		<div class="lg:col-span-4">
			<p class="eyebrow eyebrow--dot mb-5" data-reveal>[05] Dúvidas</p>
			<h2 class="text-display-md text-white" data-split><span class="split-line"><span>Perguntas</span></span><span class="split-line"><span>frequentes</span></span></h2>
		</div>
		<div class="faq lg:col-span-8" data-reveal>
			<?php foreach ( ga_home_faq() as $i => [ $q, $a ] ) : ?>
				<details <?php echo 0 === $i ? 'open' : ''; ?>>
					<summary><?php echo esc_html( $q ); ?><span class="faq__icon"><?php echo ga_icon( 'plus', 'size-4' ); ?></span></summary>
					<div><p><?php echo esc_html( $a ); ?></p></div>
				</details>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php
get_footer();

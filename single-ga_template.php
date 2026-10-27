<?php
/**
 * Página de um template.
 */

defined( 'ABSPATH' ) || exit;
get_header();

while ( have_posts() ) :
	the_post();
	$t        = ga_template();
	$scenes   = $t['scenes'];
	$stage    = $scenes ?: ( $t['thumbnail'] ? [ [ 'title' => $t['title'], 'desc' => '', 'src' => get_the_post_thumbnail_url( null, 'ga-stage' ), 'thumb' => $t['thumbnail'] ] ] : [] );
	$logged   = is_user_logged_in();
	$premium  = ga_premium_enabled() && $t['premium'];
	$can      = $logged && ga_user_can_download( $t['id'] );
	$install  = $t['install'] ?: [
		'Extraia o ZIP numa pasta fixa (ex.: Documentos\\Temas OBS\\' . $t['title'] . ').',
		'Feche o OBS e dê dois cliques em INSTALAR.bat.',
		'Abra o OBS e escolha Coleção de cenas → ' . $t['title'] . '.',
		'Edite overlays\\config.js com seu nome e suas redes.',
	];
	$related = ga_query_templates( [ 'post__not_in' => [ $t['id'] ], 'posts_per_page' => 3, 'orderby' => 'rand' ] );
	?>

	<article style="--accent: <?php echo esc_attr( $t['accent'] ); ?>">
		<!-- Topo -->
		<header class="relative overflow-hidden pb-10 pt-[calc(var(--header-h)+2.5rem)] md:pt-[calc(var(--header-h)+4rem)]">
			<div class="pointer-events-none absolute -top-60 left-1/3 size-[46rem] rounded-full opacity-30 blur-[150px]" style="background: var(--accent)" aria-hidden="true"></div>
			<div class="wrap relative">
				<nav class="eyebrow mb-8 flex-wrap" aria-label="Você está em" data-reveal>
					<a class="ulink hover:text-white" href="<?php echo esc_url( ga_page_url( 'templates' ) ); ?>">Templates</a>
					<span aria-hidden="true">/</span>
					<?php foreach ( $t['styles'] as $style ) : ?>
						<a class="ulink hover:text-white" href="<?php echo esc_url( get_term_link( $style['slug'], 'ga_style' ) ); ?>"><?php echo esc_html( $style['name'] ); ?></a>
						<span aria-hidden="true">/</span>
					<?php endforeach; ?>
					<span class="text-white" aria-current="page"><?php echo esc_html( $t['title'] ); ?></span>
				</nav>

				<div class="grid items-end gap-8 lg:grid-cols-12">
					<h1 class="text-display-xl font-semibold text-white lg:col-span-7" style="font-variation-settings:'wdth' 78" data-split>
						<span class="split-line"><span><?php echo esc_html( $t['title'] ); ?></span></span>
					</h1>
					<?php if ( $t['tagline'] ) : ?>
						<p class="font-display text-2xl leading-snug tracking-tight text-ink-200 lg:col-span-5 lg:pb-4" style="font-variation-settings:'wdth' 90; --delay:.15s" data-reveal><?php echo esc_html( $t['tagline'] ); ?></p>
					<?php endif; ?>
				</div>

				<ul class="mt-10 flex flex-wrap gap-2" data-reveal style="--delay:.25s">
					<li class="tag !text-[11px]"><?php echo ga_icon( 'layers', 'size-3.5' ); ?> <?php echo esc_html( ga_plural( count( $scenes ), 'cena', 'cenas' ) ); ?></li>
					<li class="tag !text-[11px]"><?php echo ga_icon( 'monitor', 'size-3.5' ); ?> <?php echo esc_html( $t['resolution'] ); ?></li>
					<li class="tag !text-[11px]"><?php echo ga_icon( 'obs', 'size-3.5' ); ?> OBS <?php echo esc_html( $t['obs_version'] ); ?>+</li>
					<?php foreach ( $t['platforms'] as $platform ) : ?>
						<li class="tag !text-[11px]"><?php echo ga_icon( $platform, 'size-3.5' ); ?> <?php echo esc_html( ga_platform_label( $platform ) ); ?></li>
					<?php endforeach; ?>
					<?php if ( $t['version'] ) : ?>
						<li class="tag !text-[11px]">v<?php echo esc_html( $t['version'] ); ?></li>
					<?php endif; ?>
				</ul>
			</div>
		</header>

		<!-- Palco + painel de download -->
		<section class="pb-24">
			<div class="wrap grid gap-8 lg:grid-cols-12">
				<div class="lg:col-span-8" data-reveal>
					<?php if ( $stage ) : ?>
						<div class="monitor" data-stage data-stage-autoplay="false">
							<div class="monitor__bar">
								<span class="flex items-center gap-2"><span class="live-dot"></span> Prévia <span class="text-white" data-stage-label><?php echo esc_html( $stage[0]['title'] ); ?></span></span>
								<span class="meters" aria-hidden="true"><span></span><span></span><span></span><span></span><span></span></span>
							</div>
							<button type="button" class="monitor__screen group block w-full cursor-zoom-in" data-lightbox aria-label="Ampliar cena">
								<?php foreach ( $stage as $i => $scene ) : ?>
									<img src="<?php echo esc_url( $scene['src'] ); ?>" alt="Cena <?php echo esc_attr( $scene['title'] ); ?>" class="<?php echo 0 === $i ? 'is-active' : ''; ?>" <?php echo $i ? 'loading="lazy"' : 'fetchpriority="high"'; ?> data-title="<?php echo esc_attr( $scene['title'] ); ?>">
								<?php endforeach; ?>
								<span class="absolute bottom-4 right-4 inline-flex size-11 items-center justify-center rounded-full bg-ink-950/70 text-white opacity-0 backdrop-blur transition group-hover:opacity-100"><?php echo ga_icon( 'expand', 'size-4' ); ?></span>
							</button>
							<?php if ( count( $stage ) > 1 ) : ?>
								<div class="scene-dock" role="tablist" aria-label="Cenas do template">
									<?php foreach ( $stage as $i => $scene ) : ?>
										<button type="button" role="tab" aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>" data-scene="<?php echo (int) $i; ?>"><?php echo esc_html( $scene['title'] ); ?></button>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>

				<aside class="lg:col-span-4">
					<div class="sticky top-[calc(var(--header-h)+var(--admin-bar,0px)+1rem)] rounded-3xl border border-white/10 bg-ink-900/80 p-6 backdrop-blur md:p-8" data-reveal style="--delay:.1s">
						<div class="flex items-baseline justify-between gap-4">
							<p class="font-display text-4xl font-semibold tracking-tight text-white">
								<?php echo $premium ? esc_html( ga_premium_price_html( $t['id'] ) ) : 'Grátis'; ?>
							</p>
							<p class="text-sm text-ink-400"><?php echo esc_html( number_format_i18n( $t['downloads'] ) ); ?> downloads</p>
						</div>

						<div class="mt-6 space-y-3">
							<?php if ( ! $t['has_file'] ) : ?>
								<span class="btn btn--outline btn--lg w-full" aria-disabled="true"><span>Disponível em breve</span></span>
							<?php elseif ( ! $logged ) : ?>
								<a href="<?php echo esc_url( ga_login_url( $t['url'] ) ); ?>" class="btn btn--primary btn--lg w-full" data-login-link data-magnetic>
									<?php echo ga_icon( 'lock', 'size-4' ); ?><span>Entrar para baixar</span>
								</a>
								<p class="text-center text-sm text-ink-400">É grátis. Só precisa de uma conta. <a class="text-white underline underline-offset-2" href="<?php echo esc_url( ga_login_url( $t['url'], 'register' ) ); ?>" data-login-link>Criar agora</a></p>
							<?php elseif ( $can ) : ?>
								<a href="<?php echo esc_url( ga_download_url( $t['id'] ) ); ?>" class="btn btn--primary btn--lg w-full" data-no-swup data-download data-magnetic>
									<?php echo ga_icon( 'download', 'size-5' ); ?><span>Baixar <?php echo $premium ? '' : 'grátis'; ?></span>
									<span class="font-mono text-xs opacity-60"><?php echo esc_html( size_format( $t['file_size'], 1 ) ); ?></span>
								</a>
							<?php elseif ( $premium && ga_premium_buy_url( $t['id'] ) ) : ?>
								<a href="<?php echo esc_url( ga_premium_buy_url( $t['id'] ) ); ?>" class="btn btn--primary btn--lg w-full" data-no-swup>
									<span>Comprar por <?php echo esc_html( ga_premium_price_html( $t['id'] ) ); ?></span><?php echo ga_icon( 'arrow-right', 'btn__arrow' ); ?>
								</a>
							<?php endif; ?>

							<?php if ( $logged ) : ?>
								<?php echo ga_favorite_button( $t['id'], 'wide' ); ?>
							<?php else : ?>
								<a href="<?php echo esc_url( ga_login_url( $t['url'] ) ); ?>" class="fav-btn fav-btn--wide" data-login-link><?php echo ga_icon( 'heart', 'fav-btn__icon' ); ?><span>Favoritar</span></a>
							<?php endif; ?>
						</div>

						<dl class="mt-8 divide-y divide-white/[0.07] border-t border-white/[0.07] text-sm">
							<?php
							$ga_meta_rows = array_filter( [
								'Versão'      => $t['version'] ? 'v' . $t['version'] : '',
								'Atualizado'  => wp_date( 'j M Y', $t['updated'] ),
								'Arquivo'     => $t['file_size'] ? 'ZIP · ' . size_format( $t['file_size'], 1 ) : '',
								'Resolução'   => $t['resolution'],
								'OBS Studio'  => $t['obs_version'] . ' ou mais novo',
							] );
							foreach ( $ga_meta_rows as $label => $value ) :
								?>
								<div class="flex justify-between gap-4 py-3">
									<dt class="text-ink-400"><?php echo esc_html( $label ); ?></dt>
									<dd class="text-right text-ink-100"><?php echo esc_html( $value ); ?></dd>
								</div>
							<?php endforeach; ?>
						</dl>
					</div>
				</aside>
			</div>
		</section>

		<!-- Conteúdo -->
		<section class="border-t border-white/[0.06] py-24 md:py-32">
			<div class="wrap grid gap-16 lg:grid-cols-12">
				<div class="lg:col-span-4">
					<p class="eyebrow eyebrow--dot mb-5" data-reveal>Sobre o template</p>
					<h2 class="text-display-md text-white" data-split><span class="split-line"><span>O que tem</span></span><span class="split-line"><span class="text-ink-500">dentro.</span></span></h2>
				</div>
				<div class="prose-ga lg:col-span-8" data-reveal>
					<?php the_content(); ?>
				</div>
			</div>
		</section>

		<?php if ( count( $scenes ) > 0 ) : ?>
			<section class="py-16 md:py-24">
				<div class="wrap">
					<div class="mb-12 flex items-end justify-between gap-6">
						<h2 class="text-display-md text-white" data-split><span class="split-line"><span><?php echo esc_html( ga_plural( count( $scenes ), 'cena', 'cenas' ) ); ?></span></span></h2>
						<p class="eyebrow hidden md:inline-flex">Clique para ampliar</p>
					</div>
					<div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
						<?php foreach ( $scenes as $i => $scene ) : ?>
							<figure class="group" data-reveal style="--delay:<?php echo esc_attr( ( $i % 3 ) * 0.08 ); ?>s">
								<button type="button" class="relative block aspect-video w-full cursor-zoom-in overflow-hidden rounded-2xl bg-ink-800 ring-1 ring-inset ring-white/10" data-lightbox-src="<?php echo esc_url( $scene['src'] ); ?>" aria-label="Ampliar cena <?php echo esc_attr( $scene['title'] ); ?>">
									<img src="<?php echo esc_url( $scene['thumb'] ); ?>" alt="" loading="lazy" class="size-full object-cover transition duration-700 ease-expo group-hover:scale-105">
									<span class="absolute left-3 top-3 font-mono text-[11px] text-white/80">0<?php echo (int) $i + 1; ?></span>
								</button>
								<figcaption class="mt-4">
									<p class="font-display text-xl font-semibold tracking-tight text-white"><?php echo esc_html( $scene['title'] ); ?></p>
									<?php if ( $scene['desc'] ) : ?><p class="mt-1 text-sm text-ink-400"><?php echo esc_html( $scene['desc'] ); ?></p><?php endif; ?>
								</figcaption>
							</figure>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<section class="py-16 md:py-24">
			<div class="wrap grid gap-6 lg:grid-cols-3">
				<?php if ( $t['features'] ) : ?>
					<div class="rounded-3xl border border-white/10 p-8 lg:col-span-2" data-reveal>
						<p class="eyebrow mb-6">Recursos</p>
						<ul class="grid gap-x-8 gap-y-4 sm:grid-cols-2">
							<?php foreach ( $t['features'] as $feature ) : ?>
								<li class="flex gap-3 text-ink-200"><span class="mt-1 inline-flex size-5 shrink-0 items-center justify-center rounded-full bg-lime text-ink-950"><?php echo ga_icon( 'check', 'size-3' ); ?></span><?php echo esc_html( $feature ); ?></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>
				<div class="space-y-6">
					<?php if ( $t['requirements'] ) : ?>
						<div class="rounded-3xl border border-white/10 p-8" data-reveal style="--delay:.1s">
							<p class="eyebrow mb-6">Requisitos</p>
							<ul class="space-y-3 text-ink-200">
								<?php foreach ( $t['requirements'] as $req ) : ?>
									<li class="flex gap-3"><?php echo ga_icon( 'monitor', 'mt-0.5 size-4 shrink-0 text-ink-400' ); ?><?php echo esc_html( $req ); ?></li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</section>

		<section class="py-16 md:py-24">
			<div class="wrap">
				<div class="relative overflow-hidden rounded-[2rem] bg-paper p-8 text-ink-950 md:p-14" data-reveal>
					<div class="grid gap-10 lg:grid-cols-12">
						<div class="lg:col-span-4">
							<p class="eyebrow mb-4 !text-ink-500">Instalação</p>
							<h2 class="text-display-md">Pronto em <span class="text-signal">minutos</span>.</h2>
						</div>
						<ol class="grid gap-4 sm:grid-cols-2 lg:col-span-8">
							<?php foreach ( $install as $i => $step ) : ?>
								<li class="rounded-2xl bg-white/70 p-5">
									<span class="font-mono text-xs text-ink-500">Passo 0<?php echo (int) $i + 1; ?></span>
									<p class="mt-2 font-medium leading-snug"><?php echo esc_html( $step ); ?></p>
								</li>
							<?php endforeach; ?>
						</ol>
					</div>
				</div>
			</div>
		</section>

		<?php if ( $related ) : ?>
			<section class="border-t border-white/[0.06] py-24 md:py-32">
				<div class="wrap">
					<h2 class="mb-12 text-display-md text-white" data-split><span class="split-line"><span>Veja também</span></span></h2>
					<div class="grid gap-x-8 gap-y-14 md:grid-cols-2 xl:grid-cols-3">
						<?php foreach ( $related as $i => $p ) : ?>
							<?php get_template_part( 'template-parts/card', null, [ 'post' => $p, 'index' => $i ] ); ?>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
		<?php else : ?>
			<section class="border-t border-white/[0.06] py-24">
				<div class="wrap flex flex-col items-start justify-between gap-8 md:flex-row md:items-center">
					<h2 class="text-display-md text-white">Quer outro estilo?</h2>
					<a href="<?php echo esc_url( ga_page_url( 'request' ) ); ?>" class="btn btn--primary btn--lg"><span>Solicitar um template</span><?php echo ga_icon( 'arrow-right', 'btn__arrow' ); ?></a>
				</div>
			</section>
		<?php endif; ?>
	</article>

	<?php
endwhile;
get_footer();

<?php
/**
 * Página inicial.
 */

defined( 'ABSPATH' ) || exit;
get_header();

$ga_all      = ga_query_templates( [ 'orderby' => 'date', 'order' => 'DESC' ] );
$ga_featured = array_values( array_filter( $ga_all, fn( $p ) => get_post_meta( $p->ID, '_ga_featured', true ) ) );
$ga_hero     = $ga_featured[0] ?? ( $ga_all[0] ?? null );
$ga_hero_t   = $ga_hero ? ga_template( $ga_hero ) : null;
$ga_grid     = array_slice( $ga_featured ?: $ga_all, 0, 6 );

$ga_scene_count = array_sum( array_map( fn( $p ) => count( (array) get_post_meta( $p->ID, '_ga_scenes', true ) ), $ga_all ) );
$ga_downloads   = array_sum( array_map( fn( $p ) => (int) get_post_meta( $p->ID, '_ga_downloads', true ), $ga_all ) );
?>

<!-- Hero ------------------------------------------------------------- -->
<section class="relative overflow-hidden pb-20 pt-[calc(var(--header-h)+3rem)] md:pb-28 md:pt-[calc(var(--header-h)+5rem)]">
	<div class="pointer-events-none absolute -right-40 -top-40 size-[44rem] rounded-full bg-signal/30 blur-[140px]" aria-hidden="true"></div>
	<div class="pointer-events-none absolute -left-60 top-1/2 size-[30rem] rounded-full bg-live/10 blur-[120px]" aria-hidden="true"></div>

	<div class="wrap relative">
		<p class="eyebrow mb-8" data-reveal><span class="live-dot"></span> Ao vivo · Templates para OBS Studio</p>

		<h1 class="text-display-xl font-semibold text-white" style="font-variation-settings:'wdth' 80" data-split>
			<span class="split-line"><span>Sua live</span></span>
			<span class="split-line"><span>no ar em <em class="not-italic text-lime">dois</em></span></span>
			<span class="split-line"><span class="text-outline text-white">cliques.</span></span>
		</h1>

		<div class="mt-12 grid items-end gap-12 lg:mt-16 lg:grid-cols-12">
			<div class="lg:col-span-4" data-reveal style="--delay:.3s">
				<p class="max-w-md text-lg leading-relaxed text-ink-300">
					Coleções de cenas completas e animadas: começando, jogo, conversa, react, já volto e encerrando. Com chat, alertas e painéis. Baixe, rode o instalador e o OBS abre pronto.
				</p>
				<div class="mt-8 flex flex-wrap gap-3">
					<a href="<?php echo esc_url( ga_page_url( 'templates' ) ); ?>" class="btn btn--primary btn--lg" data-magnetic><span>Explorar templates</span><?php echo ga_icon( 'arrow-right', 'btn__arrow' ); ?></a>
					<?php if ( ! is_user_logged_in() ) : ?>
						<a href="<?php echo esc_url( ga_login_url( '', 'register' ) ); ?>" class="btn btn--outline btn--lg" data-login-link><span>Criar conta grátis</span></a>
					<?php endif; ?>
				</div>

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
							<dd class="mt-1 font-display text-4xl font-semibold tracking-tight text-lime">R$0</dd>
						</div>
					<?php endif; ?>
				</dl>
			</div>

			<div class="lg:col-span-8 lg:-mt-40" data-reveal style="--delay:.15s">
				<?php if ( $ga_hero_t && $ga_hero_t['scenes'] ) : ?>
					<div class="monitor" data-stage>
						<div class="monitor__bar">
							<span class="flex items-center gap-2"><span class="live-dot"></span> REC <span class="text-white" data-timecode>00:00:00</span></span>
							<span class="hidden sm:block"><?php echo esc_html( $ga_hero_t['title'] ); ?> · <?php echo esc_html( $ga_hero_t['resolution'] ); ?></span>
							<span class="meters" aria-hidden="true"><span></span><span></span><span></span><span></span><span></span></span>
						</div>
						<a href="<?php echo esc_url( $ga_hero_t['url'] ); ?>" class="monitor__screen block" aria-label="Ver o template <?php echo esc_attr( $ga_hero_t['title'] ); ?>">
							<?php foreach ( $ga_hero_t['scenes'] as $i => $scene ) : ?>
								<img src="<?php echo esc_url( $scene['src'] ); ?>" alt="Cena <?php echo esc_attr( $scene['title'] ); ?> do template <?php echo esc_attr( $ga_hero_t['title'] ); ?>" class="<?php echo 0 === $i ? 'is-active' : ''; ?>" <?php echo $i ? 'loading="lazy"' : 'fetchpriority="high"'; ?>>
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
	</div>
</section>

<!-- Faixa ------------------------------------------------------------ -->
<div class="relative -rotate-1 overflow-hidden border-y border-ink-950 bg-lime py-5 text-ink-950" aria-hidden="true">
	<div class="marquee">
		<?php for ( $r = 0; $r < 2; $r++ ) : ?>
			<?php foreach ( [ 'Overlays animados', 'Chat na tela', 'Alertas com som', 'Painéis para Twitch', 'Instalador em 2 cliques', 'Banner offline', 'Personalização em um arquivo' ] as $word ) : ?>
				<span class="flex items-center gap-8 whitespace-nowrap pr-8 font-display text-3xl font-semibold tracking-tight md:text-4xl" style="font-variation-settings:'wdth' 80">
					<?php echo esc_html( $word ); ?> <?php echo ga_icon( 'sparkle', 'size-7' ); ?>
				</span>
			<?php endforeach; ?>
		<?php endfor; ?>
	</div>
</div>

<!-- Templates -------------------------------------------------------- -->
<?php if ( $ga_grid ) : ?>
<section class="py-24 md:py-36">
	<div class="wrap">
		<div class="mb-14 flex flex-col justify-between gap-8 md:flex-row md:items-end">
			<div>
				<p class="eyebrow eyebrow--dot mb-5" data-reveal>[01] Catálogo</p>
				<h2 class="text-display-lg text-white" data-split>
					<span class="split-line"><span>Escolha o clima</span></span>
					<span class="split-line"><span class="text-ink-500">da sua live.</span></span>
				</h2>
			</div>
			<a href="<?php echo esc_url( ga_page_url( 'templates' ) ); ?>" class="btn btn--outline" data-reveal><span>Ver todos os templates</span><?php echo ga_icon( 'arrow-right', 'btn__arrow' ); ?></a>
		</div>

		<div class="grid gap-x-8 gap-y-14 md:grid-cols-2 xl:grid-cols-3">
			<?php foreach ( $ga_grid as $i => $p ) : ?>
				<?php get_template_part( 'template-parts/card', null, [ 'post' => $p, 'index' => $i ] ); ?>
			<?php endforeach; ?>
			<?php if ( count( $ga_grid ) < 3 ) : ?>
				<?php get_template_part( 'template-parts/card-soon' ); ?>
			<?php endif; ?>
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
					<span class="split-line"><span>Do download</span></span>
					<span class="split-line"><span>ao <span class="italic text-signal" style="font-variation-settings:'wdth' 100">ao vivo</span>.</span></span>
				</h2>
				<p class="mt-8 max-w-md text-lg text-ink-600" data-reveal>Sem arrastar PNG, sem montar cena por cena. Cada template vem com a coleção de cenas inteira configurada.</p>
			</div>

			<ol class="space-y-4 lg:col-span-7">
				<?php
				$ga_steps = [
					[ 'Baixe o template', 'Escolha o seu, entre na sua conta e baixe o ZIP. Extraia numa pasta fixa, como Documentos\\Temas OBS.', 'download' ],
					[ 'Dê dois cliques no instalador', 'Com o OBS fechado, abra o INSTALAR.bat. Ele cria a coleção de cenas com todos os caminhos certos.', 'bolt' ],
					[ 'Coloque seu nome e entre no ar', 'Edite o config.js no Bloco de Notas com seu nome, suas redes e sua agenda. Abra o OBS e escolha a coleção.', 'play' ],
				];
				foreach ( $ga_steps as $i => [ $title, $text, $icon ] ) :
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
				<h3 class="max-w-lg text-display-md">Tudo que uma live precisa, já animado.</h3>
				<ul class="mt-10 grid gap-3 sm:grid-cols-2">
					<?php foreach ( [ 'Cenas de começando, jogo, conversa, react, já volto e encerrando', 'Contagem regressiva e cronômetro de pausa', 'Chat da Twitch na tela, sem login', 'Alertas de seguidor, sub, doação e raid com som', 'Meta de seguidores e barra de redes', 'Fontes leves: animações sem pesar o PC' ] as $item ) : ?>
						<li class="flex gap-3 text-ink-300"><span class="mt-1 inline-flex size-5 shrink-0 items-center justify-center rounded-full bg-lime text-ink-950"><?php echo ga_icon( 'check', 'size-3' ); ?></span><?php echo esc_html( $item ); ?></li>
					<?php endforeach; ?>
				</ul>
				<div class="pointer-events-none absolute -bottom-24 -right-24 size-80 rounded-full bg-signal/40 blur-3xl" aria-hidden="true"></div>
			</div>
			<div class="flex flex-col justify-between rounded-3xl bg-signal p-8 text-white md:col-span-2" data-reveal style="--delay:.1s">
				<?php echo ga_icon( 'message', 'size-8' ); ?>
				<div>
					<h3 class="text-2xl tracking-tight">Chat ao vivo</h3>
					<p class="mt-1 text-white/75">Conecta direto no canal da Twitch. Esconde bots e comandos.</p>
				</div>
			</div>
			<div class="flex flex-col justify-between rounded-3xl border border-ink-950/10 bg-white p-8 md:col-span-2" data-reveal style="--delay:.2s">
				<?php echo ga_icon( 'twitch', 'size-8 text-signal' ); ?>
				<div>
					<h3 class="text-2xl tracking-tight">Painéis e banner</h3>
					<p class="mt-1 text-ink-600">Sobre mim, agenda, regras, Discord e banner offline combinando.</p>
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
			<span class="split-line"><span>Não achou o seu estilo?</span></span>
			<span class="split-line"><span class="text-lime">Peça um template.</span></span>
		</h2>
		<p class="mx-auto mt-8 max-w-xl text-lg text-ink-300" data-reveal>Conte a vibe, as cenas que você usa e mande referências. As ideias mais pedidas viram os próximos templates.</p>
		<div class="mt-10 flex justify-center" data-reveal>
			<a href="<?php echo esc_url( ga_page_url( 'request' ) ); ?>" class="btn btn--primary btn--lg" data-magnetic><span>Solicitar um template</span><?php echo ga_icon( 'wand', 'btn__arrow' ); ?></a>
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
			<?php
			$ga_faq = [
				[ 'Os templates são grátis mesmo?', 'Sim. É só criar uma conta (nome e e-mail, ou com o Google) para baixar. A conta também guarda seus favoritos e seus downloads.' ],
				[ 'Funciona no meu OBS?', 'Os templates são feitos para o OBS Studio 30 ou mais novo no Windows 10 e 11. Cada página de template mostra os requisitos específicos.' ],
				[ 'Preciso saber programar para personalizar?', 'Não. Seu nome, redes sociais, textos e agenda ficam num único arquivo (config.js) que você edita no Bloco de Notas. O passo a passo vem no README de cada template.' ],
				[ 'Funciona com Twitch, YouTube e Kick?', 'As cenas funcionam em qualquer plataforma. O chat na tela conecta na Twitch; os alertas funcionam com StreamElements e com a URL de alertas de outros serviços.' ],
				[ 'Vai pesar meu PC?', 'Os overlays são páginas leves rodando dentro do próprio OBS. Se precisar, dá para baixar os quadros por segundo ou usar o fundo parado no config.js.' ],
				[ 'Posso pedir um template específico?', 'Pode e deve! Use a página de solicitações. Você acompanha o status do seu pedido em Minha conta.' ],
			];
			foreach ( $ga_faq as $i => [ $q, $a ] ) :
				?>
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

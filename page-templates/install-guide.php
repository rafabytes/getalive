<?php
/**
 * Template Name: Como instalar (guia com capturas do OBS)
 */

defined( 'ABSPATH' ) || exit;
get_header();

$ga_img = GA_URI . '/assets/img/guia/';

// Saída real do tools/instalar.ps1 (cores do Write-Host), com o nome e as redes de exemplo.
$ga_term = [
	[ "  Instalando o tema 'Vapo' no OBS Studio", 'term--cyan' ],
	[ '  -----------------------------------------', 'term--cyan' ],
	[ '', '' ],
	[ '  Quer preencher seu nome e suas redes sociais agora? (S/n): S', '' ],
	[ '', '' ],
	[ '  Digite só o nome de usuário (sem @). Deixe em branco e aperte Enter para pular.', 'term--dim' ],
	[ '', '' ],
	[ '  Seu nome na live: Rafa', '' ],
	[ '  Twitch: rafabytes', '' ],
	[ '  YouTube: rafabytes', '' ],
	[ '  Instagram: rafabytes', '' ],
	[ '  TikTok:', '' ],
	[ '  X (Twitter):', '' ],
	[ '  Kick:', '' ],
	[ '  Discord (código do convite):', '' ],
	[ '', '' ],
	[ '  Salvo em overlays\config.js (dá para mudar depois pelo Bloco de Notas).', 'term--dim' ],
	[ '', '' ],
	[ '  Pronto!', 'term--green' ],
	[ '', '' ],
	[ '  Agora:', 'term--white' ],
	[ '    1. Abra o OBS Studio', '' ],
	[ '    2. No menu de cima, clique em  Coleção de cenas  >  Vapo', '' ],
	[ '', '' ],
	[ 'Pressione qualquer tecla para continuar. . .', '' ],
];

$ga_files = [
	[ 'assets', 'Pasta de arquivos' ],
	[ 'obs', 'Pasta de arquivos' ],
	[ 'overlays', 'Pasta de arquivos' ],
	[ 'panels', 'Pasta de arquivos' ],
	[ 'previews', 'Pasta de arquivos' ],
	[ 'tools', 'Pasta de arquivos' ],
	[ 'INSTALAR.bat', 'Arquivo em lotes do Windows' ],
	[ 'preview.html', 'Documento HTML' ],
	[ 'README.md', 'Arquivo MD' ],
];

$ga_trouble = [
	[ 'O Windows mostrou "O Windows protegeu o computador"', 'É o aviso padrão para arquivos baixados da internet. Clique em <strong>Mais informações</strong> e depois em <strong>Executar assim mesmo</strong>. O instalador só copia a coleção de cenas para a pasta do OBS.' ],
	[ 'Apareceu "Extraia o arquivo ZIP para uma pasta antes de instalar"', 'Você abriu o INSTALAR.bat de dentro do ZIP. Clique com o botão direito no ZIP, escolha <strong>Extrair tudo…</strong> e rode o INSTALAR.bat da pasta extraída.' ],
	[ 'O instalador diz que o OBS está aberto', 'Feche o OBS (inclusive pela bandeja, perto do relógio) e aperte <strong>Enter</strong> na janela do instalador. O OBS precisa estar fechado porque ele regrava a coleção ativa ao sair.' ],
	[ 'O template não aparece no menu Coleção de cenas', 'Abra o OBS pelo menos uma vez antes de instalar, para ele criar a pasta de configurações. Se o seu OBS é portátil, o instalador gera o arquivo em <code>obs\Vapo.json</code>: no OBS, use <strong>Coleção de cenas → Importar</strong> e escolha esse arquivo.' ],
	[ 'Mudei a pasta do template de lugar e as cenas ficaram pretas', 'O OBS lê os overlays direto da pasta onde você extraiu. Depois de mover a pasta, dê dois cliques no INSTALAR.bat de novo para ele apontar os caminhos para o lugar novo.' ],
];

while ( have_posts() ) :
	the_post();
	?>
	<section class="relative overflow-hidden pb-16 pt-[calc(var(--header-h)+3rem)] md:pb-24 md:pt-[calc(var(--header-h)+5rem)]">
		<div class="pointer-events-none absolute -right-40 -top-40 size-[44rem] rounded-full bg-signal/30 blur-[140px]" aria-hidden="true"></div>
		<div class="wrap relative">
			<p class="eyebrow eyebrow--dot mb-8" data-reveal>Guia de instalação · OBS Studio</p>
			<h1 class="text-display-xl font-semibold text-white" style="font-variation-settings:'wdth' 78" data-split>
				<span class="split-line"><span>Instalar é</span></span>
				<span class="split-line"><span>dar <em class="not-italic text-lime">dois</em></span></span>
				<span class="split-line"><span class="text-outline text-white">cliques.</span></span>
			</h1>

			<div class="mt-12 grid items-end gap-10 lg:grid-cols-12">
				<p class="max-w-xl text-lg leading-relaxed text-ink-300 lg:col-span-6" data-reveal style="--delay:.2s">
					Sem montar cena por cena, sem arrastar PNG, sem copiar caminho de arquivo. Você extrai o ZIP, dá dois cliques no instalador e escolhe o template no menu do OBS. As imagens abaixo são do OBS de verdade, com o template Vapo.
				</p>
				<div class="grid grid-cols-3 gap-4 lg:col-span-5 lg:col-start-8" data-reveal style="--delay:.3s">
					<div class="rounded-3xl bg-lime p-5 text-ink-950">
						<p class="font-display text-5xl font-semibold tracking-tighter">2</p>
						<p class="mt-1 text-sm font-medium">cliques para instalar</p>
					</div>
					<div class="rounded-3xl bg-signal p-5 text-white">
						<p class="font-display text-5xl font-semibold tracking-tighter">2<span class="text-2xl">min</span></p>
						<p class="mt-1 text-sm font-medium">do ZIP ao ar</p>
					</div>
					<div class="rounded-3xl border border-white/10 p-5">
						<p class="font-display text-5xl font-semibold tracking-tighter text-white">0</p>
						<p class="mt-1 text-sm font-medium text-ink-300">cenas para montar</p>
					</div>
				</div>
			</div>

			<ul class="mt-12 flex flex-wrap gap-2" data-reveal style="--delay:.35s">
				<li class="tag !text-[11px]"><?php echo ga_icon( 'monitor', 'size-3.5' ); ?> Windows 10 ou 11</li>
				<li class="tag !text-[11px]"><?php echo ga_icon( 'obs', 'size-3.5' ); ?> OBS Studio 30+, já aberto uma vez</li>
				<li class="tag !text-[11px]"><?php echo ga_icon( 'download', 'size-3.5' ); ?> O ZIP do template baixado</li>
			</ul>
		</div>
	</section>

	<section class="pb-24 md:pb-36">
		<div class="wrap space-y-24 md:space-y-36">

			<!-- 01 · Extrair -->
			<div class="guide-step">
				<div class="guide-step__text" data-reveal>
					<span class="guide-step__num">01</span>
					<h2 class="guide-step__title">Extraia o ZIP numa pasta fixa</h2>
					<p>Clique com o botão direito no arquivo baixado, escolha <strong>Extrair tudo…</strong> e depois <strong>Extrair</strong>.</p>
					<p>Deixe a pasta num lugar que não vai mudar, como <code>Documentos\Temas OBS\Vapo</code>. O OBS lê os overlays direto dela.</p>
				</div>
				<div class="guide-step__visual" data-reveal style="--delay:.1s">
					<div class="win" role="img" aria-label="Pasta Vapo extraída no Explorador de Arquivos, com o INSTALAR.bat em destaque">
						<div class="win__bar">
							<span class="flex items-center gap-2"><?php echo ga_icon( 'file', 'size-3.5 text-[#f5c542]' ); ?> Vapo</span>
							<span class="win__controls" aria-hidden="true"><span>—</span><span>☐</span><span>✕</span></span>
						</div>
						<div class="win__path">Documentos <span>›</span> Temas OBS <span>›</span> <b>Vapo</b></div>
						<div class="win__head"><span>Nome</span><span>Tipo</span></div>
						<ul class="win__list">
							<?php foreach ( $ga_files as [ $name, $type ] ) : ?>
								<li class="<?php echo 'INSTALAR.bat' === $name ? 'is-hit' : ''; ?>">
									<span class="flex items-center gap-2.5">
										<span class="win__icon <?php echo str_contains( $type, 'Pasta' ) ? 'win__icon--folder' : 'win__icon--file'; ?>" aria-hidden="true"></span>
										<?php echo esc_html( $name ); ?>
									</span>
									<span class="text-ink-400"><?php echo esc_html( $type ); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			</div>

			<!-- 02 · Instalador -->
			<div class="guide-step guide-step--flip">
				<div class="guide-step__text" data-reveal>
					<span class="guide-step__num">02</span>
					<h2 class="guide-step__title">Feche o OBS e dê <span class="text-lime">dois cliques</span> no INSTALAR.bat</h2>
					<p>Esses são os dois cliques. O instalador pergunta seu nome e seu usuário em cada rede: digite ou cole o link do perfil, ou só aperte <strong>Enter</strong> para pular.</p>
					<p>Quando aparecer <strong class="text-lime">Pronto!</strong>, a coleção de cenas inteira já está dentro do OBS, com todos os caminhos certos. Pode fechar a janela.</p>
					<p class="guide-note"><?php echo ga_icon( 'shield', 'mt-1 size-4 shrink-0' ); ?><span>Se o Windows mostrar um aviso azul, clique em <strong>Mais informações → Executar assim mesmo</strong>.</span></p>
				</div>
				<div class="guide-step__visual" data-reveal style="--delay:.1s">
					<div class="term" role="img" aria-label="Janela do instalador mostrando as perguntas de nome e redes e a mensagem Pronto!">
						<div class="term__bar">
							<span class="term__tab">Instalador do tema para OBS</span>
							<span class="win__controls" aria-hidden="true"><span>—</span><span>☐</span><span>✕</span></span>
						</div>
<pre class="term__body"><?php
foreach ( $ga_term as [ $line, $tone ] ) {
	echo '<span' . ( $tone ? ' class="' . esc_attr( $tone ) . '"' : '' ) . '>' . esc_html( $line ) . "</span>\n";
}
?></pre>
					</div>
				</div>
			</div>

			<!-- 03 · Menu do OBS -->
			<div class="guide-step">
				<div class="guide-step__text" data-reveal>
					<span class="guide-step__num">03</span>
					<h2 class="guide-step__title">Abra o OBS e escolha o template no menu</h2>
					<p>No menu de cima, clique em <strong>Coleção de cenas</strong> e depois no nome do template, aqui o <strong>Vapo</strong>.</p>
					<p>Suas outras coleções continuam lá. Para voltar para elas, é o mesmo menu.</p>
				</div>
				<div class="guide-step__visual" data-reveal style="--delay:.1s">
					<figure class="shot shot--menu">
						<img src="<?php echo esc_url( $ga_img . 'obs-menu-colecao.jpg' ); ?>" alt="Barra de menus do OBS Studio em português, com o menu Coleção de cenas" width="1500" height="840" loading="lazy">
						<div class="obs-menu" aria-hidden="true">
							<span>Novo</span>
							<span>Duplicar</span>
							<span>Renomear</span>
							<span>Remover</span>
							<i></i>
							<span>Importar</span>
							<span>Exportar</span>
							<i></i>
							<span>Aero</span>
							<span class="is-checked">Rococo</span>
							<span class="is-hover">Vapo</span>
						</div>
						<span class="shot__pin" style="left:56.5%;top:52.5%" aria-hidden="true">clique</span>
					</figure>
					<p class="mt-3 font-mono text-[11px] uppercase tracking-[0.14em] text-ink-500">OBS Studio 33 · o menu aberto foi redesenhado por cima da captura</p>
				</div>
			</div>

			<!-- 04 · Pronto -->
			<div>
				<div class="mb-10 grid gap-6 lg:grid-cols-12 lg:items-end">
					<div class="lg:col-span-7" data-reveal>
						<span class="guide-step__num">04</span>
						<h2 class="text-display-md text-white">Pronto. <span class="text-lime">Já dá para entrar no ar.</span></h2>
					</div>
					<p class="text-lg text-ink-300 lg:col-span-5" data-reveal style="--delay:.1s">O OBS abre com as seis cenas montadas, o seu nome e as suas redes na tela, contagem regressiva rodando e o chat ligado no seu canal.</p>
				</div>

				<div class="monitor" data-reveal>
					<div class="monitor__bar">
						<span class="flex items-center gap-2"><span class="live-dot"></span> Captura real · OBS Studio 33</span>
						<span class="hidden sm:block">Coleção de cenas: Vapo</span>
						<span class="meters" aria-hidden="true"><span></span><span></span><span></span><span></span><span></span></span>
					</div>
					<button type="button" class="group relative block w-full cursor-zoom-in bg-black" data-lightbox-src="<?php echo esc_url( $ga_img . 'obs-vapo-pronto.jpg' ); ?>" aria-label="Ampliar: OBS com a coleção Vapo instalada">
						<img src="<?php echo esc_url( $ga_img . 'obs-vapo-pronto.jpg' ); ?>" alt="OBS Studio com a coleção Vapo instalada: cena Começando com contagem regressiva, nome e redes do streamer" width="1920" height="1014" loading="lazy" class="block w-full">
						<span class="absolute bottom-4 right-4 inline-flex size-11 items-center justify-center rounded-full bg-ink-950/70 text-white opacity-0 backdrop-blur transition group-hover:opacity-100"><?php echo ga_icon( 'expand', 'size-4' ); ?></span>
					</button>
				</div>

				<div class="mt-6 grid gap-6 lg:grid-cols-12">
					<figure class="shot lg:col-span-7" data-reveal>
						<button type="button" class="block w-full cursor-zoom-in" data-lightbox-src="<?php echo esc_url( $ga_img . 'obs-vapo-cenas.jpg' ); ?>" aria-label="Ampliar: lista de cenas do Vapo no OBS">
							<img src="<?php echo esc_url( $ga_img . 'obs-vapo-cenas.jpg' ); ?>" alt="Painel Cenas do OBS com Começando, Jogo, Conversa, React, Já Volto e Encerrando" width="1160" height="390" loading="lazy" class="block w-full">
						</button>
					</figure>
					<div class="rounded-3xl border border-white/10 p-7 lg:col-span-5" data-reveal style="--delay:.1s">
						<p class="eyebrow mb-4">Último detalhe</p>
						<p class="font-display text-2xl font-medium leading-snug tracking-tight text-white">Na cena <span class="text-lime">Jogo</span>, dê dois cliques na fonte <span class="text-lime">Câmera</span>, escolha sua webcam e clique em OK.</p>
						<p class="mt-3 text-ink-400">As outras cenas usam a mesma câmera, então é uma vez só.</p>
					</div>
				</div>
			</div>
		</div>
	</section>

	<section class="rounded-t-[2.5rem] bg-paper py-24 text-ink-950 md:rounded-t-[4rem] md:py-32">
		<div class="wrap grid gap-12 lg:grid-cols-12">
			<div class="lg:col-span-5">
				<p class="eyebrow mb-5 !text-ink-500" data-reveal>Depois</p>
				<h2 class="text-display-md" data-split>
					<span class="split-line"><span>Quer mudar</span></span>
					<span class="split-line"><span>algum <span class="text-signal">texto</span>?</span></span>
				</h2>
			</div>
			<ol class="space-y-4 lg:col-span-7">
				<?php
				foreach ( [
					[ 'Abra overlays\config.js', 'Na pasta do template, clique com o botão direito no config.js → Abrir com → Bloco de Notas.' ],
					[ 'Troque o que está entre aspas', 'Seu nome, redes, agenda, meta de seguidores, textos de cada tela e os minutos da contagem.' ],
					[ 'Salve e reabra o OBS', 'Ctrl+S, feche e abra o OBS. Quer conferir antes? Dê dois cliques no preview.html.' ],
				] as $i => [ $title, $text ] ) :
					?>
					<li class="grid grid-cols-[auto_1fr] gap-6 rounded-3xl border border-ink-950/10 bg-white/60 p-6 md:gap-10 md:p-8" data-reveal style="--delay:<?php echo esc_attr( $i * 0.1 ); ?>s">
						<span class="font-display text-5xl font-semibold leading-none tracking-tighter text-ink-950/15">0<?php echo (int) $i + 1; ?></span>
						<div>
							<h3 class="text-2xl tracking-tight"><?php echo esc_html( $title ); ?></h3>
							<p class="mt-2 text-ink-600"><?php echo esc_html( $text ); ?></p>
						</div>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	</section>

	<section class="bg-paper pb-24 text-ink-950 md:pb-32">
		<div class="wrap">
			<div class="rounded-[2rem] bg-ink-950 p-8 text-white md:p-14">
				<div class="grid gap-12 lg:grid-cols-12">
					<div class="lg:col-span-4">
						<p class="eyebrow eyebrow--dot mb-5">Se algo travar</p>
						<h2 class="text-display-md">Problemas comuns</h2>
					</div>
					<div class="faq lg:col-span-8">
						<?php foreach ( $ga_trouble as [ $q, $a ] ) : ?>
							<details>
								<summary><?php echo esc_html( $q ); ?><span class="faq__icon"><?php echo ga_icon( 'plus', 'size-4' ); ?></span></summary>
								<div><p><?php echo wp_kses( $a, [ 'strong' => [], 'code' => [] ] ); ?></p></div>
							</details>
						<?php endforeach; ?>
					</div>
				</div>
			</div>

			<div class="mt-16 flex flex-col items-start justify-between gap-8 border-t border-ink-950/10 pt-12 md:flex-row md:items-center" data-reveal>
				<p class="max-w-xl font-display text-3xl font-medium leading-tight tracking-tight">Agora é escolher o clima da sua live.</p>
				<a href="<?php echo esc_url( ga_page_url( 'templates' ) ); ?>" class="btn btn--light btn--lg"><span>Ver templates</span><?php echo ga_icon( 'arrow-right', 'btn__arrow' ); ?></a>
			</div>
		</div>
	</section>
	<?php
endwhile;
get_footer();

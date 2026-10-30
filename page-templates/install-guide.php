<?php
/**
 * Template Name: Como instalar (guia com capturas do OBS)
 */

defined( 'ABSPATH' ) || exit;
get_header();

$ga_img = GA_URI . '/assets/img/guia/';

// Saída real do tools/instalar.ps1 (cores do Write-Host), com o nome e as redes de exemplo.
// Fica em português em todos os idiomas: é o que o instalador mostra de verdade.
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

$ga_folder = __( 'Pasta de arquivos', 'getalive' );
$ga_files  = [
	[ 'assets', $ga_folder, true ],
	[ 'obs', $ga_folder, true ],
	[ 'overlays', $ga_folder, true ],
	[ 'panels', $ga_folder, true ],
	[ 'previews', $ga_folder, true ],
	[ 'tools', $ga_folder, true ],
	[ 'INSTALAR.bat', __( 'Arquivo em lotes do Windows', 'getalive' ), false ],
	[ 'preview.html', __( 'Documento HTML', 'getalive' ), false ],
	[ 'README.md', __( 'Arquivo MD', 'getalive' ), false ],
];

$ga_trouble = [
	[ __( 'O Windows mostrou "O Windows protegeu o computador"', 'getalive' ), __( 'É o aviso padrão para arquivos baixados da internet. Clique em <strong>Mais informações</strong> e depois em <strong>Executar assim mesmo</strong>. O instalador só copia a coleção de cenas para a pasta do OBS.', 'getalive' ) ],
	[ __( 'Apareceu "Extraia o arquivo ZIP para uma pasta antes de instalar"', 'getalive' ), __( 'Você abriu o INSTALAR.bat de dentro do ZIP. Clique com o botão direito no ZIP, escolha <strong>Extrair tudo…</strong> e rode o INSTALAR.bat da pasta extraída.', 'getalive' ) ],
	[ __( 'O instalador diz que o OBS está aberto', 'getalive' ), __( 'Feche o OBS (inclusive pela bandeja, perto do relógio) e aperte <strong>Enter</strong> na janela do instalador. O OBS precisa estar fechado porque ele regrava a coleção ativa ao sair.', 'getalive' ) ],
	[ __( 'O template não aparece no menu Coleção de cenas', 'getalive' ), __( 'Abra o OBS pelo menos uma vez antes de instalar, para ele criar a pasta de configurações. Se o seu OBS é portátil, o instalador gera o arquivo em <code>obs\Vapo.json</code>: no OBS, use <strong>Coleção de cenas → Importar</strong> e escolha esse arquivo.', 'getalive' ) ],
	[ __( 'Mudei a pasta do template de lugar e as cenas ficaram pretas', 'getalive' ), __( 'O OBS lê os overlays direto da pasta onde você extraiu. Depois de mover a pasta, dê dois cliques no INSTALAR.bat de novo para ele apontar os caminhos para o lugar novo.', 'getalive' ) ],
];

// Trechos com <strong>, <code> e destaques de cor.
$ga_html = fn( string $text ) => wp_kses( $text, [ 'strong' => [ 'class' => [] ], 'code' => [], 'span' => [ 'class' => [] ], 'em' => [ 'class' => [] ] ] );

while ( have_posts() ) :
	the_post();
	?>
	<section class="relative overflow-hidden pb-16 pt-[calc(var(--header-h)+3rem)] md:pb-24 md:pt-[calc(var(--header-h)+5rem)]">
		<div class="pointer-events-none absolute -right-40 -top-40 size-[44rem] rounded-full bg-signal/30 blur-[140px]" aria-hidden="true"></div>
		<div class="wrap relative">
			<p class="eyebrow eyebrow--dot mb-8" data-reveal><?php esc_html_e( 'Como colocar overlay no OBS Studio', 'getalive' ); ?></p>
			<h1 class="text-display-xl font-semibold text-white" style="font-variation-settings:'wdth' 78" data-split>
				<span class="split-line"><span><?php esc_html_e( 'Instalar é', 'getalive' ); ?></span></span>
				<span class="split-line"><span><?php echo $ga_html( __( 'dar <em class="not-italic text-lime">dois</em>', 'getalive' ) ); ?></span></span>
				<span class="split-line"><span class="text-outline text-white"><?php esc_html_e( 'cliques.', 'getalive' ); ?></span></span>
			</h1>

			<div class="mt-12 grid items-end gap-10 lg:grid-cols-12">
				<p class="max-w-xl text-lg leading-relaxed text-ink-300 lg:col-span-6" data-reveal style="--delay:.2s">
					<?php esc_html_e( 'Colocar overlay no OBS não precisa levar a tarde inteira: sem montar cena por cena, sem arrastar PNG, sem copiar caminho de arquivo. Você extrai o ZIP, dá dois cliques no instalador e escolhe o template no menu do OBS. As imagens abaixo são do OBS de verdade, com o template Vapo.', 'getalive' ); ?>
				</p>
				<div class="grid grid-cols-3 gap-4 lg:col-span-5 lg:col-start-8" data-reveal style="--delay:.3s">
					<div class="rounded-3xl bg-lime p-5 text-ink-950">
						<p class="font-display text-5xl font-semibold tracking-tighter">2</p>
						<p class="mt-1 text-sm font-medium"><?php esc_html_e( 'cliques para instalar', 'getalive' ); ?></p>
					</div>
					<div class="rounded-3xl bg-signal p-5 text-white">
						<p class="font-display text-5xl font-semibold tracking-tighter">2<span class="text-2xl">min</span></p>
						<p class="mt-1 text-sm font-medium"><?php esc_html_e( 'do ZIP ao ar', 'getalive' ); ?></p>
					</div>
					<div class="rounded-3xl border border-white/10 p-5">
						<p class="font-display text-5xl font-semibold tracking-tighter text-white">0</p>
						<p class="mt-1 text-sm font-medium text-ink-300"><?php esc_html_e( 'cenas para montar', 'getalive' ); ?></p>
					</div>
				</div>
			</div>

			<ul class="mt-12 flex flex-wrap gap-2" data-reveal style="--delay:.35s">
				<li class="tag !text-[11px]"><?php echo ga_icon( 'monitor', 'size-3.5' ); ?> <?php esc_html_e( 'Windows 10 ou 11', 'getalive' ); ?></li>
				<li class="tag !text-[11px]"><?php echo ga_icon( 'obs', 'size-3.5' ); ?> <?php esc_html_e( 'OBS Studio 30+, já aberto uma vez', 'getalive' ); ?></li>
				<li class="tag !text-[11px]"><?php echo ga_icon( 'download', 'size-3.5' ); ?> <?php esc_html_e( 'O ZIP do template baixado', 'getalive' ); ?></li>
			</ul>
		</div>
	</section>

	<section class="pb-24 md:pb-36">
		<div class="wrap space-y-24 md:space-y-36">

			<!-- 01 · Extrair -->
			<div class="guide-step">
				<div class="guide-step__text" data-reveal>
					<span class="guide-step__num">01</span>
					<h2 class="guide-step__title"><?php esc_html_e( 'Extraia o ZIP numa pasta fixa', 'getalive' ); ?></h2>
					<p><?php echo $ga_html( __( 'Clique com o botão direito no arquivo baixado, escolha <strong>Extrair tudo…</strong> e depois <strong>Extrair</strong>.', 'getalive' ) ); ?></p>
					<p><?php echo $ga_html( __( 'Deixe a pasta num lugar que não vai mudar, como <code>Documentos\Temas OBS\Vapo</code>. O OBS lê os overlays direto dela.', 'getalive' ) ); ?></p>
				</div>
				<div class="guide-step__visual" data-reveal style="--delay:.1s">
					<div class="win" role="img" aria-label="<?php esc_attr_e( 'Pasta Vapo extraída no Explorador de Arquivos, com o INSTALAR.bat em destaque', 'getalive' ); ?>">
						<div class="win__bar">
							<span class="flex items-center gap-2"><?php echo ga_icon( 'file', 'size-3.5 text-[#f5c542]' ); ?> Vapo</span>
							<span class="win__controls" aria-hidden="true"><span>—</span><span>☐</span><span>✕</span></span>
						</div>
						<div class="win__path"><?php esc_html_e( 'Documentos', 'getalive' ); ?> <span>›</span> <?php esc_html_e( 'Temas OBS', 'getalive' ); ?> <span>›</span> <b>Vapo</b></div>
						<div class="win__head"><span><?php esc_html_e( 'Nome', 'getalive' ); ?></span><span><?php esc_html_e( 'Tipo', 'getalive' ); ?></span></div>
						<ul class="win__list">
							<?php foreach ( $ga_files as [ $name, $type, $is_folder ] ) : ?>
								<li class="<?php echo 'INSTALAR.bat' === $name ? 'is-hit' : ''; ?>">
									<span class="flex items-center gap-2.5">
										<span class="win__icon <?php echo $is_folder ? 'win__icon--folder' : 'win__icon--file'; ?>" aria-hidden="true"></span>
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
					<h2 class="guide-step__title"><?php echo $ga_html( __( 'Feche o OBS e dê <span class="text-lime">dois cliques</span> no INSTALAR.bat', 'getalive' ) ); ?></h2>
					<p><?php echo $ga_html( __( 'Esses são os dois cliques. O instalador pergunta seu nome e seu usuário em cada rede: digite ou cole o link do perfil, ou só aperte <strong>Enter</strong> para pular.', 'getalive' ) ); ?></p>
					<p><?php echo $ga_html( __( 'Quando aparecer <strong class="text-lime">Pronto!</strong>, a coleção de cenas inteira já está dentro do OBS, com todos os caminhos certos. Pode fechar a janela.', 'getalive' ) ); ?></p>
					<p class="guide-note"><?php echo ga_icon( 'shield', 'mt-1 size-4 shrink-0' ); ?><span><?php echo $ga_html( __( 'Se o Windows mostrar um aviso azul, clique em <strong>Mais informações → Executar assim mesmo</strong>.', 'getalive' ) ); ?></span></p>
				</div>
				<div class="guide-step__visual" data-reveal style="--delay:.1s">
					<div class="term" role="img" aria-label="<?php esc_attr_e( 'Janela do instalador mostrando as perguntas de nome e redes e a mensagem Pronto!', 'getalive' ); ?>">
						<div class="term__bar">
							<span class="term__tab"><?php esc_html_e( 'Instalador do tema para OBS', 'getalive' ); ?></span>
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
					<h2 class="guide-step__title"><?php esc_html_e( 'Abra o OBS e escolha o template no menu', 'getalive' ); ?></h2>
					<p><?php echo $ga_html( __( 'No menu de cima, clique em <strong>Coleção de cenas</strong> e depois no nome do template, aqui o <strong>Vapo</strong>.', 'getalive' ) ); ?></p>
					<p><?php esc_html_e( 'Suas outras coleções continuam lá. Para voltar para elas, é o mesmo menu.', 'getalive' ); ?></p>
				</div>
				<div class="guide-step__visual" data-reveal style="--delay:.1s">
					<figure class="shot shot--menu">
						<img src="<?php echo esc_url( $ga_img . 'obs-menu-colecao.jpg' ); ?>" alt="<?php esc_attr_e( 'Barra de menus do OBS Studio em português, com o menu Coleção de cenas', 'getalive' ); ?>" width="1500" height="840" loading="lazy">
						<div class="obs-menu" aria-hidden="true">
							<span><?php esc_html_e( 'Novo', 'getalive' ); ?></span>
							<span><?php esc_html_e( 'Duplicar', 'getalive' ); ?></span>
							<span><?php esc_html_e( 'Renomear', 'getalive' ); ?></span>
							<span><?php esc_html_e( 'Remover', 'getalive' ); ?></span>
							<i></i>
							<span><?php esc_html_e( 'Importar', 'getalive' ); ?></span>
							<span><?php esc_html_e( 'Exportar', 'getalive' ); ?></span>
							<i></i>
							<span>Aero</span>
							<span class="is-checked">Rococo</span>
							<span class="is-hover">Vapo</span>
						</div>
						<span class="shot__pin" style="left:56.5%;top:52.5%" aria-hidden="true"><?php esc_html_e( 'clique', 'getalive' ); ?></span>
					</figure>
					<p class="mt-3 font-mono text-[11px] uppercase tracking-[0.14em] text-ink-500"><?php esc_html_e( 'OBS Studio 33 · o menu aberto foi redesenhado por cima da captura', 'getalive' ); ?></p>
				</div>
			</div>

			<!-- 04 · Pronto -->
			<div>
				<div class="mb-10 grid gap-6 lg:grid-cols-12 lg:items-end">
					<div class="lg:col-span-7" data-reveal>
						<span class="guide-step__num">04</span>
						<h2 class="text-display-md text-white"><?php echo $ga_html( __( 'Pronto. <span class="text-lime">Já dá para entrar no ar.</span>', 'getalive' ) ); ?></h2>
					</div>
					<p class="text-lg text-ink-300 lg:col-span-5" data-reveal style="--delay:.1s"><?php esc_html_e( 'O OBS abre com as seis cenas montadas, o seu nome e as suas redes na tela, contagem regressiva rodando e o chat ligado no seu canal.', 'getalive' ); ?></p>
				</div>

				<div class="monitor" data-reveal>
					<div class="monitor__bar">
						<span class="flex items-center gap-2"><span class="live-dot"></span> <?php esc_html_e( 'Captura real · OBS Studio 33', 'getalive' ); ?></span>
						<span class="hidden sm:block"><?php esc_html_e( 'Coleção de cenas: Vapo', 'getalive' ); ?></span>
						<span class="meters" aria-hidden="true"><span></span><span></span><span></span><span></span><span></span></span>
					</div>
					<button type="button" class="group relative block w-full cursor-zoom-in bg-black" data-lightbox-src="<?php echo esc_url( $ga_img . 'obs-vapo-pronto.jpg' ); ?>" aria-label="<?php esc_attr_e( 'Ampliar: OBS com a coleção Vapo instalada', 'getalive' ); ?>">
						<img src="<?php echo esc_url( $ga_img . 'obs-vapo-pronto.jpg' ); ?>" alt="<?php esc_attr_e( 'OBS Studio com a coleção Vapo instalada: cena Começando com contagem regressiva, nome e redes do streamer', 'getalive' ); ?>" width="1920" height="1014" loading="lazy" class="block w-full">
						<span class="absolute bottom-4 right-4 inline-flex size-11 items-center justify-center rounded-full bg-ink-950/70 text-white opacity-0 backdrop-blur transition group-hover:opacity-100"><?php echo ga_icon( 'expand', 'size-4' ); ?></span>
					</button>
				</div>

				<div class="mt-6 grid gap-6 lg:grid-cols-12">
					<figure class="shot lg:col-span-7" data-reveal>
						<button type="button" class="block w-full cursor-zoom-in" data-lightbox-src="<?php echo esc_url( $ga_img . 'obs-vapo-cenas.jpg' ); ?>" aria-label="<?php esc_attr_e( 'Ampliar: lista de cenas do Vapo no OBS', 'getalive' ); ?>">
							<img src="<?php echo esc_url( $ga_img . 'obs-vapo-cenas.jpg' ); ?>" alt="<?php esc_attr_e( 'Painel Cenas do OBS com Começando, Jogo, Conversa, React, Já Volto e Encerrando', 'getalive' ); ?>" width="1160" height="390" loading="lazy" class="block w-full">
						</button>
					</figure>
					<div class="rounded-3xl border border-white/10 p-7 lg:col-span-5" data-reveal style="--delay:.1s">
						<p class="eyebrow mb-4"><?php esc_html_e( 'Último detalhe', 'getalive' ); ?></p>
						<p class="font-display text-2xl font-medium leading-snug tracking-tight text-white"><?php echo $ga_html( __( 'Na cena <span class="text-lime">Jogo</span>, dê dois cliques na fonte <span class="text-lime">Câmera</span>, escolha sua webcam e clique em OK.', 'getalive' ) ); ?></p>
						<p class="mt-3 text-ink-400"><?php esc_html_e( 'As outras cenas usam a mesma câmera, então é uma vez só.', 'getalive' ); ?></p>
					</div>
				</div>
			</div>
		</div>
	</section>

	<section class="rounded-t-[2.5rem] bg-paper py-24 text-ink-950 md:rounded-t-[4rem] md:py-32">
		<div class="wrap grid gap-12 lg:grid-cols-12">
			<div class="lg:col-span-5">
				<p class="eyebrow mb-5 !text-ink-500" data-reveal><?php esc_html_e( 'Depois', 'getalive' ); ?></p>
				<h2 class="text-display-md" data-split>
					<span class="split-line"><span><?php esc_html_e( 'Quer mudar', 'getalive' ); ?></span></span>
					<span class="split-line"><span><?php echo $ga_html( __( 'algum <span class="text-signal">texto</span>?', 'getalive' ) ); ?></span></span>
				</h2>
			</div>
			<ol class="space-y-4 lg:col-span-7">
				<?php
				foreach ( [
					[ __( 'Abra overlays\config.js', 'getalive' ), __( 'Na pasta do template, clique com o botão direito no config.js → Abrir com → Bloco de Notas.', 'getalive' ) ],
					[ __( 'Troque o que está entre aspas', 'getalive' ), __( 'Seu nome, redes, agenda, meta de seguidores, textos de cada tela e os minutos da contagem.', 'getalive' ) ],
					[ __( 'Salve e reabra o OBS', 'getalive' ), __( 'Ctrl+S, feche e abra o OBS. Quer conferir antes? Dê dois cliques no preview.html.', 'getalive' ) ],
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
						<p class="eyebrow eyebrow--dot mb-5"><?php esc_html_e( 'Se algo travar', 'getalive' ); ?></p>
						<h2 class="text-display-md"><?php esc_html_e( 'Problemas comuns', 'getalive' ); ?></h2>
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
				<p class="max-w-xl font-display text-3xl font-medium leading-tight tracking-tight"><?php esc_html_e( 'Agora é escolher o clima da sua live.', 'getalive' ); ?></p>
				<a href="<?php echo esc_url( ga_page_url( 'templates' ) ); ?>" class="btn btn--light btn--lg"><span><?php esc_html_e( 'Ver templates', 'getalive' ); ?></span><?php echo ga_icon( 'arrow-right', 'btn__arrow' ); ?></a>
			</div>
		</div>
	</section>
	<?php
endwhile;
get_footer();

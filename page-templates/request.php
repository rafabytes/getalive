<?php
/**
 * Template Name: Solicitar template
 */

defined( 'ABSPATH' ) || exit;
get_header();

$ga_props = [
	'options'    => ga_request_options(),
	'accountUrl' => ga_account_tab_url( 'solicitacoes' ),
	'name'       => ga_user_first_name(),
];
?>

<section class="relative overflow-hidden pb-28 pt-[calc(var(--header-h)+3rem)] md:pb-40 md:pt-[calc(var(--header-h)+5rem)]">
	<div class="pointer-events-none absolute -left-60 top-20 size-[40rem] rounded-full bg-signal/25 blur-[140px]" aria-hidden="true"></div>
	<div class="wrap relative grid gap-16 lg:grid-cols-12">
		<div class="lg:col-span-5">
			<div class="lg:sticky lg:top-[calc(var(--header-h)+var(--admin-bar,0px)+2rem)]">
				<p class="eyebrow eyebrow--dot mb-6" data-reveal>Pedidos abertos</p>
				<h1 class="text-display-lg font-semibold text-white" style="font-variation-settings:'wdth' 80" data-split>
					<span class="split-line"><span>Qual live</span></span>
					<span class="split-line"><span>você quer</span></span>
					<span class="split-line"><span class="text-lime">ver no ar?</span></span>
				</h1>
				<p class="mt-8 max-w-md text-lg text-ink-300" data-reveal>Conte a vibe, as cenas que você usa e mande referências. As ideias mais pedidas viram os próximos templates.</p>

				<ol class="mt-12 space-y-5 border-t border-white/10 pt-8" data-reveal>
					<?php foreach ( [ [ 'Você manda a ideia', 'Leva uns 2 minutos.' ], [ 'A gente analisa', 'O status aparece em Minha conta.' ], [ 'Vira template', 'Você recebe aviso quando publicar.' ] ] as $i => [ $title, $text ] ) : ?>
						<li class="flex gap-4">
							<span class="font-mono text-xs text-ink-500">0<?php echo (int) $i + 1; ?></span>
							<div><p class="font-medium text-white"><?php echo esc_html( $title ); ?></p><p class="text-sm text-ink-400"><?php echo esc_html( $text ); ?></p></div>
						</li>
					<?php endforeach; ?>
				</ol>
			</div>
		</div>

		<div class="lg:col-span-7" data-reveal style="--delay:.15s">
			<?php if ( is_user_logged_in() ) : ?>
				<div class="rounded-[2rem] border border-white/10 bg-ink-900/70 p-6 backdrop-blur md:p-10">
					<div data-react="RequestForm" data-props="<?php echo esc_attr( wp_json_encode( $ga_props ) ); ?>"></div>
				</div>
			<?php else : ?>
				<div class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-ink-900/70 p-8 md:p-12">
					<div class="pointer-events-none select-none space-y-4 opacity-30 blur-[2px]" aria-hidden="true">
						<div class="h-14 rounded-2xl border border-white/10"></div>
						<div class="flex flex-wrap gap-2"><?php echo str_repeat( '<span class="h-9 w-28 rounded-full border border-white/10"></span>', 6 ); ?></div>
						<div class="h-40 rounded-2xl border border-white/10"></div>
						<div class="h-14 rounded-2xl border border-white/10"></div>
					</div>
					<div class="absolute inset-0 flex flex-col items-center justify-center bg-gradient-to-t from-ink-950 via-ink-950/80 to-ink-950/40 p-8 text-center">
						<span class="inline-flex size-14 items-center justify-center rounded-full bg-lime text-ink-950"><?php echo ga_icon( 'lock', 'size-6' ); ?></span>
						<h2 class="mt-6 font-display text-3xl font-semibold tracking-tight text-white">Entre para fazer seu pedido</h2>
						<p class="mt-2 max-w-sm text-ink-300">Com a conta você acompanha o status da solicitação e recebe aviso quando o template sair.</p>
						<div class="mt-8 flex flex-wrap justify-center gap-3">
							<a href="<?php echo esc_url( ga_login_url( '', 'register' ) ); ?>" class="btn btn--primary" data-login-link><span>Criar conta grátis</span></a>
							<a href="<?php echo esc_url( ga_login_url() ); ?>" class="btn btn--outline" data-login-link><span>Já tenho conta</span></a>
						</div>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>

<?php
get_footer();

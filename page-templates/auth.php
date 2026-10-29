<?php
/**
 * Template Name: Entrar / Criar conta
 *
 * Tela dividida: à esquerda um "estúdio" animado com as cenas dos templates,
 * à direita o formulário (componente React AuthApp).
 */

defined( 'ABSPATH' ) || exit;
get_header();

$ga_mode = ga_is_page( 'register' ) ? 'register' : 'login';
if ( 'recuperar' === sanitize_key( $_GET['modo'] ?? '' ) ) {
	$ga_mode = 'forgot';
}
$ga_redirect = ga_safe_redirect_target( wp_unslash( $_GET['redirect_to'] ?? '' ) );

// Cenas para o palco: as do template em destaque ou do mais recente.
$ga_scenes = [];
foreach ( ga_query_templates( [ 'posts_per_page' => 4 ] ) as $p ) {
	foreach ( ga_template( $p )['scenes'] as $scene ) {
		if ( $scene['image'] ) {
			$ga_scenes[] = [ 'image' => $scene['image'], 'title' => $scene['title'], 'template' => get_the_title( $p ) ];
		}
	}
}
$ga_scenes = array_slice( $ga_scenes, 0, 8 );

$ga_props = [
	'mode'             => $ga_mode,
	'redirectTo'       => $ga_redirect,
	'googleClientId'   => ga_option( 'google_client_id' ),
	'registrationOpen' => ga_registration_open(),
	'privacyUrl'       => ga_page_url( 'privacy' ),
	'urls'             => [
		'login'    => ga_page_url( 'login' ),
		'register' => ga_page_url( 'register' ),
	],
];
?>

<div class="auth">
	<!-- Estúdio -->
	<div class="auth__visual" aria-hidden="true" data-auth-visual>
		<div class="auth__orb -left-40 -top-40 size-[36rem] bg-[#a374ff]"></div>
		<div class="auth__orb -bottom-60 -right-20 size-[40rem] bg-[#3b0fa8]"></div>
		<div class="auth__orb bottom-10 left-1/4 size-64 bg-live/40"></div>

		<span class="auth-word -left-6 top-[8%]">Get a</span>
		<span class="auth-word right-4 bottom-[4%]">live</span>

		<div class="auth-stage" data-auth-stage>
			<div class="auth-stage__tilt" data-auth-tilt>
				<div class="monitor !border-white/20 !shadow-[0_60px_140px_-30px_rgba(10,0,40,0.9)]" data-stage data-stage-interval="2600">
					<div class="monitor__bar">
						<span class="flex items-center gap-2"><span class="live-dot"></span> Ao vivo <span class="text-white" data-timecode>00:00:00</span></span>
						<span class="text-white/80" data-stage-label><?php echo esc_html( $ga_scenes[0]['title'] ?? 'Começando' ); ?></span>
						<span class="meters"><span></span><span></span><span></span><span></span><span></span></span>
					</div>
					<div class="monitor__screen">
						<?php if ( $ga_scenes ) : ?>
							<?php foreach ( $ga_scenes as $i => $scene ) : ?>
								<?php echo ga_image( $scene['image'], 'ga-hd', 'auth', [ 'class' => 0 === $i ? 'is-active' : '', 'data-title' => $scene['title'], 'loading' => $i ? 'lazy' : false ] ); ?>
							<?php endforeach; ?>
						<?php else : ?>
							<div class="absolute inset-0 bg-gradient-to-br from-signal-700 to-ink-950"></div>
						<?php endif; ?>
					</div>
				</div>

				<div class="auth-chip -left-10 top-[18%] animate-float" style="transform: translateZ(60px)">
					<span class="inline-flex size-8 items-center justify-center rounded-full bg-live text-white"><?php echo ga_icon( 'heart', 'size-4' ); ?></span>
					<span><strong class="block text-[13px]">Favoritos salvos</strong><span class="text-xs text-ink-400">em qualquer aparelho</span></span>
				</div>
				<div class="auth-chip -right-8 bottom-[20%] animate-float [animation-delay:-3s]" style="transform: translateZ(90px)">
					<span class="inline-flex size-8 items-center justify-center rounded-full bg-lime text-ink-950"><?php echo ga_icon( 'download', 'size-4' ); ?></span>
					<span><strong class="block text-[13px]">Download liberado</strong><span class="text-xs text-ink-400">template.zip · grátis</span></span>
				</div>
				<div class="auth-chip -bottom-6 left-[12%] animate-float [animation-delay:-5s]" style="transform: translateZ(40px)">
					<span class="inline-flex size-8 items-center justify-center rounded-full bg-signal text-white"><?php echo ga_icon( 'wand', 'size-4' ); ?></span>
					<span><strong class="block text-[13px]">Peça templates</strong><span class="text-xs text-ink-400">e acompanhe o status</span></span>
				</div>
			</div>
		</div>

		<div class="absolute inset-x-0 bottom-0 flex items-end justify-between p-10">
			<p class="max-w-xs font-display text-2xl font-medium leading-tight tracking-tight text-white" style="font-variation-settings:'wdth' 88">
				Uma conta. Todos os templates, favoritos e pedidos num lugar só.
			</p>
			<p class="font-mono text-[11px] uppercase tracking-[0.18em] text-white/60">OBS Studio · 1080p</p>
		</div>
	</div>

	<!-- Formulário -->
	<div class="auth__panel">
		<div class="pointer-events-none absolute -right-40 -top-40 size-[30rem] rounded-full bg-signal/20 blur-[120px] lg:hidden" aria-hidden="true"></div>

		<div class="relative flex items-center justify-between">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="logo">
				<span class="logo__mark" aria-hidden="true"></span>
				<span><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
			</a>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn--ghost btn--sm"><?php echo ga_icon( 'arrow-left', 'size-4' ); ?><span>Voltar ao site</span></a>
		</div>

		<div class="relative mx-auto flex w-full max-w-[26rem] flex-1 flex-col justify-center py-12">
			<div data-react="AuthApp" data-props="<?php echo esc_attr( wp_json_encode( $ga_props ) ); ?>">
				<noscript><p class="notice notice--error">Ative o JavaScript para entrar ou criar sua conta.</p></noscript>
			</div>
		</div>

		<p class="relative text-center text-xs leading-relaxed text-ink-500">
			Protegido por limite de tentativas. Ao continuar, você concorda com a
			<a class="text-ink-300 underline underline-offset-2 hover:text-white" href="<?php echo esc_url( ga_page_url( 'privacy' ) ); ?>">Política de Privacidade</a>.
		</p>
	</div>
</div>

<?php
get_footer();

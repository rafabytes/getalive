<?php
/**
 * Cabeçalho. Tudo fora de #swup permanece entre navegações.
 */

defined( 'ABSPATH' ) || exit;
$ga_user  = wp_get_current_user();
$ga_items = ga_menu_items( 'primary' );
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="grain" aria-hidden="true"></div>
<a class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100] focus:rounded-full focus:bg-lime focus:px-4 focus:py-2 focus:text-ink-950" href="#conteudo"><?php esc_html_e( 'Pular para o conteúdo', 'getalive' ); ?></a>

<header class="site-header" data-header>
	<div class="site-header__bar">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="logo" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: nome do site */ __( '%s, página inicial', 'getalive' ), get_bloginfo( 'name' ) ) ); ?>">
			<span class="logo__mark" aria-hidden="true"></span>
			<span><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
		</a>

		<nav class="hidden items-center gap-9 md:flex" aria-label="<?php esc_attr_e( 'Principal', 'getalive' ); ?>">
			<?php foreach ( $ga_items as $item ) : ?>
				<a class="nav-link" href="<?php echo esc_url( $item['url'] ); ?>" data-nav><?php echo esc_html( $item['label'] ); ?></a>
			<?php endforeach; ?>
		</nav>

		<div class="flex items-center gap-2">
			<?php echo ga_lang_switcher( 'menu', 'hidden md:block' ); ?>
			<?php if ( $ga_user->exists() ) : ?>
				<div class="user-menu" data-user-menu>
					<button type="button" class="flex items-center gap-2.5 rounded-full border border-white/10 py-1 pl-1 pr-3 transition hover:border-white/25" aria-expanded="false" aria-haspopup="true" data-user-menu-toggle>
						<?php echo ga_avatar_html( 'size-8' ); ?>
						<span class="hidden max-w-[9rem] truncate text-sm font-medium sm:block"><?php echo esc_html( ga_user_first_name( $ga_user ) ); ?></span>
						<?php echo ga_icon( 'chevron-down', 'size-4 text-ink-400' ); ?>
					</button>
					<div class="user-menu__panel" role="menu">
						<div class="mb-1 border-b border-white/[0.07] px-3 pb-3 pt-2">
							<p class="truncate text-sm font-medium text-white"><?php echo esc_html( $ga_user->display_name ); ?></p>
							<p class="truncate text-xs text-ink-400"><?php echo esc_html( $ga_user->user_email ); ?></p>
						</div>
						<a role="menuitem" href="<?php echo esc_url( ga_account_tab_url( 'favoritos' ) ); ?>"><?php echo ga_icon( 'heart', 'size-4' ); ?> <?php esc_html_e( 'Favoritos', 'getalive' ); ?></a>
						<a role="menuitem" href="<?php echo esc_url( ga_account_tab_url( 'downloads' ) ); ?>"><?php echo ga_icon( 'download', 'size-4' ); ?> <?php esc_html_e( 'Downloads', 'getalive' ); ?></a>
						<a role="menuitem" href="<?php echo esc_url( ga_account_tab_url( 'solicitacoes' ) ); ?>"><?php echo ga_icon( 'wand', 'size-4' ); ?> <?php esc_html_e( 'Minhas solicitações', 'getalive' ); ?></a>
						<a role="menuitem" href="<?php echo esc_url( ga_account_tab_url( 'perfil' ) ); ?>"><?php echo ga_icon( 'user', 'size-4' ); ?> <?php esc_html_e( 'Perfil', 'getalive' ); ?></a>
						<?php if ( current_user_can( 'edit_posts' ) ) : ?>
							<a role="menuitem" href="<?php echo esc_url( admin_url() ); ?>" data-no-swup><?php echo ga_icon( 'layers', 'size-4' ); ?> <?php esc_html_e( 'Painel', 'getalive' ); ?></a>
						<?php endif; ?>
						<a role="menuitem" href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" data-no-swup class="!text-ink-400 hover:!text-live"><?php echo ga_icon( 'logout', 'size-4' ); ?> <?php esc_html_e( 'Sair', 'getalive' ); ?></a>
					</div>
				</div>
			<?php else : ?>
				<a href="<?php echo esc_url( ga_login_url() ); ?>" class="btn btn--ghost btn--sm hidden sm:inline-flex" data-login-link><span><?php esc_html_e( 'Entrar', 'getalive' ); ?></span></a>
				<a href="<?php echo esc_url( ga_login_url( '', 'register' ) ); ?>" class="btn btn--primary btn--sm" data-login-link><span><?php esc_html_e( 'Criar conta', 'getalive' ); ?></span></a>
			<?php endif; ?>
			<button type="button" class="burger" aria-label="<?php esc_attr_e( 'Abrir menu', 'getalive' ); ?>" aria-expanded="false" aria-controls="mobile-nav" data-burger><span></span><span></span></button>
		</div>
	</div>
</header>

<nav id="mobile-nav" class="mobile-nav md:hidden" aria-label="<?php esc_attr_e( 'Menu', 'getalive' ); ?>" data-mobile-nav>
	<?php foreach ( $ga_items as $i => $item ) : ?>
		<a class="mobile-nav__link" href="<?php echo esc_url( $item['url'] ); ?>"><span class="mr-3 align-top font-mono text-xs text-ink-500">0<?php echo (int) $i + 1; ?></span><?php echo esc_html( $item['label'] ); ?></a>
	<?php endforeach; ?>
	<div class="mt-auto flex flex-col gap-3">
		<?php echo ga_lang_switcher( 'list', 'mb-3' ); ?>
		<?php if ( $ga_user->exists() ) : ?>
			<a href="<?php echo esc_url( ga_page_url( 'account' ) ); ?>" class="btn btn--outline"><span><?php esc_html_e( 'Minha conta', 'getalive' ); ?></span></a>
		<?php else : ?>
			<a href="<?php echo esc_url( ga_login_url() ); ?>" class="btn btn--outline" data-login-link><span><?php esc_html_e( 'Entrar', 'getalive' ); ?></span></a>
			<a href="<?php echo esc_url( ga_login_url( '', 'register' ) ); ?>" class="btn btn--primary" data-login-link><span><?php esc_html_e( 'Criar conta grátis', 'getalive' ); ?></span></a>
		<?php endif; ?>
	</div>
</nav>

<main id="swup" class="transition-main">
	<div id="conteudo" tabindex="-1" class="outline-none">

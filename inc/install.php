<?php
/**
 * Ativação do tema: cria as páginas, menus e opções de que o tema precisa.
 * Idempotente: pode rodar de novo sem duplicar nada.
 */

defined( 'ABSPATH' ) || exit;

function ga_install_pages(): array {
	return [
		'login'    => [ 'Entrar', 'entrar', 'page-templates/auth.php', '' ],
		'register' => [ 'Criar conta', 'criar-conta', 'page-templates/auth.php', '' ],
		'account'  => [ 'Minha conta', 'conta', 'page-templates/account.php', '' ],
		'request'  => [ 'Solicitar template', 'solicitar', 'page-templates/request.php', '' ],
		'install'  => [ 'Como instalar no OBS', 'como-instalar', 'page-templates/install-guide.php', '' ],
		'about'    => [ 'Sobre', 'sobre', 'page-templates/about.php', 'about.html' ],
		'privacy'  => [ 'Política de Privacidade', 'politica-de-privacidade', 'page-templates/legal.php', 'privacy.html' ],
	];
}

function ga_install(): void {
	$pages = get_option( 'ga_pages', [] );
	$new   = [];

	foreach ( ga_install_pages() as $key => [ $title, $slug, $template, $content_file ] ) {
		$id = (int) ( $pages[ $key ] ?? 0 );
		if ( $id && get_post( $id ) && 'trash' !== get_post_status( $id ) ) {
			continue;
		}
		$existing = get_page_by_path( $slug );
		$content  = $content_file ? (string) file_get_contents( GA_DIR . '/inc/content/' . $content_file ) : '';

		if ( $existing ) {
			$id = $existing->ID;
			$update = [ 'ID' => $id, 'post_status' => 'publish' ];
			// A página de privacidade padrão do WP vem em rascunho e genérica: troca pelo texto do tema.
			if ( 'privacy' === $key && 'publish' !== $existing->post_status ) {
				$update['post_content'] = $content;
				$update['post_title']   = $title;
			}
			wp_update_post( $update );
		} else {
			$id = wp_insert_post( [
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => $title,
				'post_name'    => $slug,
				'post_content' => $content,
			] );
		}
		update_post_meta( $id, '_wp_page_template', $template );
		$pages[ $key ] = $id;
		$new[]         = $key;
	}

	update_option( 'ga_pages', $pages );
	update_option( 'wp_page_for_privacy_policy', $pages['privacy'] );
	update_option( 'users_can_register', 1 );
	update_option( 'default_role', 'subscriber' );

	if ( ! get_option( 'permalink_structure' ) || str_contains( get_option( 'permalink_structure' ), 'index.php' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
	}

	ga_install_menus( $pages, $new );
	ga_ensure_private_dir();

	if ( ! term_exists( 'retro', 'ga_style' ) ) {
		wp_insert_term( 'Retrô', 'ga_style', [ 'slug' => 'retro' ] );
	}

	flush_rewrite_rules();
}

/**
 * $new: páginas criadas agora. Num menu que já existe, só elas são acrescentadas (ex.: uma página nova numa atualização do tema).
 */
function ga_install_menus( array $pages, array $new = [] ): void {
	$locations = get_theme_mod( 'nav_menu_locations', [] );
	$menus     = [
		'primary' => [ 'Principal', [ 'templates', 'install', 'request', 'about' ] ],
		'footer'  => [ 'Rodapé', [ 'about', 'privacy', 'request' ] ],
	];

	foreach ( $menus as $location => [ $name, $items ] ) {
		if ( ! empty( $locations[ $location ] ) && wp_get_nav_menu_object( $locations[ $location ] ) ) {
			foreach ( array_intersect( $items, $new ) as $item ) {
				ga_install_menu_item( $locations[ $location ], $item, $pages );
			}
			continue;
		}
		$menu    = wp_get_nav_menu_object( $name );
		$menu_id = $menu ? $menu->term_id : wp_create_nav_menu( $name );
		if ( is_wp_error( $menu_id ) ) {
			continue;
		}
		if ( ! $menu ) {
			foreach ( $items as $item ) {
				ga_install_menu_item( $menu_id, $item, $pages );
			}
		}
		$locations[ $location ] = $menu_id;
	}
	set_theme_mod( 'nav_menu_locations', $locations );
}

function ga_install_menu_item( int $menu_id, string $item, array $pages ): void {
	if ( 'templates' === $item ) {
		wp_update_nav_menu_item( $menu_id, 0, [
			'menu-item-title'  => 'Templates',
			'menu-item-type'   => 'post_type_archive',
			'menu-item-object' => 'ga_template',
			'menu-item-status' => 'publish',
		] );
		return;
	}
	$labels = [ 'request' => 'Solicitar', 'install' => 'Como instalar' ];
	wp_update_nav_menu_item( $menu_id, 0, [
		'menu-item-title'     => $labels[ $item ] ?? get_the_title( $pages[ $item ] ),
		'menu-item-object'    => 'page',
		'menu-item-object-id' => $pages[ $item ],
		'menu-item-type'      => 'post_type',
		'menu-item-status'    => 'publish',
	] );
}

add_action( 'after_switch_theme', 'ga_install' );

/**
 * Se o tema foi ativado sem passar pelo after_switch_theme (ex.: por script), ou se uma
 * atualização trouxe uma página nova, instala no admin.
 */
add_action( 'admin_init', function () {
	$missing = array_diff_key( ga_install_pages(), (array) get_option( 'ga_pages', [] ) );
	if ( $missing && current_user_can( 'manage_options' ) ) {
		ga_install();
	}
} );

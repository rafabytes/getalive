<?php
/**
 * Ordem dos templates no site, definida arrastando as linhas em Templates no painel.
 *
 * A ordem fica no menu_order do post. Templates novos entram com 0 e, no empate,
 * o mais recente vem antes: aparecem no topo até alguém arrastá-los.
 */

defined( 'ABSPATH' ) || exit;

const GA_TEMPLATE_ORDER = [ 'menu_order' => 'ASC', 'date' => 'DESC' ];

add_action( 'pre_get_posts', function ( WP_Query $q ) {
	if ( $q->get( 'orderby' ) ) {
		return;
	}
	if ( is_admin() ) {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $q->is_main_query() && $screen && 'edit-ga_template' === $screen->id ) {
			$q->set( 'orderby', GA_TEMPLATE_ORDER );
		}
		return;
	}
	if ( $q->is_main_query() && ( $q->is_post_type_archive( 'ga_template' ) || $q->is_tax( 'ga_style' ) ) ) {
		$q->set( 'orderby', GA_TEMPLATE_ORDER );
	}
} );

/**
 * Arrastar só faz sentido na lista completa, sem busca, filtro ou outra ordenação.
 */
function ga_template_order_enabled(): bool {
	foreach ( [ 'orderby', 's', 'm', 'ga_style', 'post_status', 'author' ] as $arg ) {
		if ( ! empty( $_GET[ $arg ] ) && ! ( 'post_status' === $arg && 'all' === $_GET[ $arg ] ) ) {
			return false;
		}
	}
	return current_user_can( 'edit_others_posts' );
}

add_filter( 'manage_ga_template_posts_columns', function ( $cols ) {
	return ga_template_order_enabled() ? [ 'cb' => $cols['cb'], 'ga_order' => '' ] + $cols : $cols;
}, 20 );

add_action( 'manage_ga_template_posts_custom_column', function ( $col ) {
	if ( 'ga_order' === $col ) {
		echo '<span class="dashicons dashicons-menu ga-order__handle" title="Arraste para mudar a ordem no site"></span>';
	}
} );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	$screen = get_current_screen();
	if ( 'edit.php' !== $hook || ! $screen || 'ga_template' !== $screen->post_type || ! ga_template_order_enabled() ) {
		return;
	}
	$per_page = (int) get_user_option( 'edit_ga_template_per_page' ) ?: 20;
	wp_enqueue_script( 'ga-admin-order', GA_URI . '/assets/admin-order.js', [ 'jquery', 'jquery-ui-sortable' ], filemtime( GA_DIR . '/assets/admin-order.js' ), true );
	wp_localize_script( 'ga-admin-order', 'gaOrder', [
		'ajax'   => admin_url( 'admin-ajax.php' ),
		'nonce'  => wp_create_nonce( 'ga_template_order' ),
		'offset' => ( max( 1, (int) ( $_GET['paged'] ?? 1 ) ) - 1 ) * $per_page,
	] );
	wp_enqueue_style( 'ga-admin', GA_URI . '/assets/admin.css', [], filemtime( GA_DIR . '/assets/admin.css' ) );
} );

add_action( 'admin_notices', function () {
	$screen = get_current_screen();
	if ( $screen && 'edit-ga_template' === $screen->id && ga_template_order_enabled() ) {
		echo '<div class="notice notice-info ga-order-notice"><p>Arraste as linhas pelo <span class="dashicons dashicons-menu"></span> para mudar a ordem dos templates no site. O primeiro em destaque aparece no topo da página inicial.</p></div>';
	}
} );

add_action( 'wp_ajax_ga_template_order', function () {
	check_ajax_referer( 'ga_template_order' );
	if ( ! current_user_can( 'edit_others_posts' ) ) {
		wp_send_json_error( null, 403 );
	}
	global $wpdb;
	$offset = max( 0, (int) ( $_POST['offset'] ?? 0 ) );
	$ids    = array_map( 'intval', (array) ( $_POST['ids'] ?? [] ) );
	foreach ( $ids as $i => $id ) {
		if ( 'ga_template' !== get_post_type( $id ) ) {
			continue;
		}
		// Direto no banco: mudar a ordem não cria revisão nem muda a data de modificação.
		$wpdb->update( $wpdb->posts, [ 'menu_order' => $offset + $i + 1 ], [ 'ID' => $id ] );
		clean_post_cache( $id );
	}
	wp_send_json_success();
} );

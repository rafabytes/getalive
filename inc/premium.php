<?php
/**
 * Templates pagos (venda por template via WooCommerce).
 *
 * Fica tudo desligado até existirem as duas coisas: WooCommerce ativo e a opção
 * "Venda de templates" marcada. Até lá, templates pagos não aparecem no site.
 * Cada template pago tem um produto virtual vinculado (_ga_product_id); a página
 * do template é a vitrine, e o download continua saindo pelo handler do tema.
 */

defined( 'ABSPATH' ) || exit;

function ga_premium_enabled(): bool {
	return class_exists( 'WooCommerce' ) && '1' === ga_option( 'premium_enabled' );
}

function ga_template_product( int $post_id ) {
	if ( ! function_exists( 'wc_get_product' ) ) {
		return null;
	}
	$product_id = (int) get_post_meta( $post_id, '_ga_product_id', true );
	return $product_id ? wc_get_product( $product_id ) : null;
}

/**
 * O usuário comprou este template? A equipe sempre tem acesso.
 */
function ga_user_owns_template( int $post_id, int $user_id = 0 ): bool {
	$user_id = $user_id ?: get_current_user_id();
	if ( ! $user_id ) {
		return false;
	}
	if ( user_can( $user_id, 'edit_post', $post_id ) ) {
		return true;
	}
	$product = ga_template_product( $post_id );
	if ( ! $product || ! function_exists( 'wc_customer_bought_product' ) ) {
		return false;
	}
	$user = get_userdata( $user_id );
	return wc_customer_bought_product( $user->user_email, $user_id, $product->get_id() );
}

function ga_premium_price_html( int $post_id ): string {
	$product = ga_template_product( $post_id );
	if ( $product ) {
		return wp_strip_all_tags( wc_price( $product->get_price() ) );
	}
	$price = (string) get_post_meta( $post_id, '_ga_price', true );
	return $price ? 'R$ ' . $price : '';
}

function ga_premium_buy_url( int $post_id ): string {
	$product = ga_template_product( $post_id );
	if ( ! $product ) {
		return '';
	}
	return add_query_arg( 'add-to-cart', $product->get_id(), wc_get_checkout_url() );
}

/**
 * Ao salvar um template pago com preço, cria ou atualiza o produto virtual.
 */
add_action( 'ga_template_saved', function ( int $post_id ) {
	if ( ! class_exists( 'WC_Product_Simple' ) || ! get_post_meta( $post_id, '_ga_premium', true ) ) {
		return;
	}
	$price = str_replace( [ '.', ',' ], [ '', '.' ], (string) get_post_meta( $post_id, '_ga_price', true ) );
	if ( ! is_numeric( $price ) || (float) $price <= 0 ) {
		return;
	}

	$product = ga_template_product( $post_id ) ?: new WC_Product_Simple();
	$product->set_name( get_the_title( $post_id ) );
	$product->set_status( 'publish' === get_post_status( $post_id ) ? 'publish' : 'draft' );
	$product->set_regular_price( wc_format_decimal( $price ) );
	$product->set_virtual( true );
	$product->set_sold_individually( true );
	$product->set_catalog_visibility( 'hidden' );
	$product->set_short_description( (string) get_post_meta( $post_id, '_ga_tagline', true ) );
	if ( has_post_thumbnail( $post_id ) ) {
		$product->set_image_id( get_post_thumbnail_id( $post_id ) );
	}
	$product->update_meta_data( '_ga_template_id', $post_id );
	$product_id = $product->save();

	update_post_meta( $post_id, '_ga_product_id', $product_id );
} );

/**
 * A página do produto redireciona para a página do template (a vitrine é o tema).
 */
add_action( 'template_redirect', function () {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}
	$template = (int) get_post_meta( get_queried_object_id(), '_ga_template_id', true );
	if ( $template ) {
		wp_safe_redirect( get_permalink( $template ), 301 );
		exit;
	}
} );

/**
 * Pedido concluído: os templates comprados aparecem em "Minha conta" na aba downloads.
 */
add_action( 'woocommerce_order_status_completed', function ( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order || ! $order->get_user_id() ) {
		return;
	}
	$owned = (array) get_user_meta( $order->get_user_id(), 'ga_purchases', true );
	foreach ( $order->get_items() as $item ) {
		$template = (int) get_post_meta( $item->get_product_id(), '_ga_template_id', true );
		if ( $template ) {
			$owned[ $template ] = time();
		}
	}
	update_user_meta( $order->get_user_id(), 'ga_purchases', $owned );
} );

/**
 * Pedidos de templates são 100% digitais: concluem sozinhos após o pagamento.
 */
add_filter( 'woocommerce_payment_complete_order_status', function ( $status, $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return $status;
	}
	foreach ( $order->get_items() as $item ) {
		if ( ! get_post_meta( $item->get_product_id(), '_ga_template_id', true ) ) {
			return $status;
		}
	}
	return 'completed';
}, 10, 2 );

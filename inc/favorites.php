<?php
/**
 * Favoritos: lista de IDs de templates no meta do usuário.
 */

defined( 'ABSPATH' ) || exit;

function ga_user_favorites( int $user_id = 0 ): array {
	$user_id = $user_id ?: get_current_user_id();
	if ( ! $user_id ) {
		return [];
	}
	return array_values( array_map( 'intval', array_filter( (array) get_user_meta( $user_id, 'ga_favorites', true ) ) ) );
}

function ga_is_favorite( int $post_id, int $user_id = 0 ): bool {
	return in_array( $post_id, ga_user_favorites( $user_id ), true );
}

/**
 * Liga/desliga um favorito. Retorna o estado final.
 */
function ga_set_favorite( int $post_id, bool $on, int $user_id = 0 ): bool {
	$user_id = $user_id ?: get_current_user_id();
	$was     = ga_is_favorite( $post_id, $user_id );
	$favs    = array_diff( ga_user_favorites( $user_id ), [ $post_id ] );
	if ( $on ) {
		array_unshift( $favs, $post_id );
	}
	update_user_meta( $user_id, 'ga_favorites', array_values( $favs ) );
	if ( $was !== $on ) {
		ga_log_activity( $user_id, $on ? 'favorite_add' : 'favorite_remove', [ 'object_id' => $post_id, 'detail' => [ 'title' => get_the_title( $post_id ) ] ] );
	}
	return $on;
}

/**
 * Botão de favorito renderizado no servidor; o JS assume a interação.
 */
function ga_favorite_button( int $post_id, string $variant = 'icon' ): string {
	$on    = ga_is_favorite( $post_id );
	$label = $on ? __( 'Remover dos favoritos', 'getalive' ) : __( 'Favoritar', 'getalive' );
	return sprintf(
		'<button type="button" class="fav-btn fav-btn--%1$s%2$s" data-favorite="%3$d" aria-pressed="%4$s" aria-label="%5$s" data-login="%6$s">%7$s<span class="fav-btn__label">%8$s</span></button>',
		esc_attr( $variant ),
		$on ? ' is-on' : '',
		$post_id,
		$on ? 'true' : 'false',
		esc_attr( $label ),
		esc_url( ga_login_url( get_permalink( $post_id ) ) ),
		ga_icon( 'heart', 'fav-btn__icon' ),
		esc_html( $on ? __( 'Favoritado', 'getalive' ) : __( 'Favoritar', 'getalive' ) )
	);
}

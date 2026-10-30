<?php
/**
 * Funções utilitárias usadas em todo o tema.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Lê uma configuração do tema (Configurações → get a live).
 */
function ga_option( string $key, $default = '' ) {
	$settings = get_option( 'ga_settings', [] );
	return isset( $settings[ $key ] ) && '' !== $settings[ $key ] ? $settings[ $key ] : $default;
}

/**
 * Páginas criadas pelo tema na ativação, guardadas em ga_pages.
 * Chaves: login, register, account, request, install, about, privacy, templates.
 */
function ga_page_id( string $key ): int {
	$pages = get_option( 'ga_pages', [] );
	return isset( $pages[ $key ] ) ? (int) $pages[ $key ] : 0;
}

function ga_page_url( string $key ): string {
	if ( 'templates' === $key ) {
		return get_post_type_archive_link( 'ga_template' ) ?: home_url( '/templates/' );
	}
	$id = ga_page_id( $key );
	return $id ? get_permalink( $id ) : home_url( '/' );
}

function ga_is_page( string $key ): bool {
	$id = ga_page_id( $key );
	return $id && is_page( $id );
}

function ga_current_url(): string {
	$uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
	// REQUEST_URI já inclui a subpasta do WordPress (ex.: /getalive/...); home_url() a repetiria.
	$home = wp_parse_url( home_url() );
	$base = $home['scheme'] . '://' . $home['host'] . ( isset( $home['port'] ) ? ':' . $home['port'] : '' );
	return esc_url_raw( $base . $uri );
}

/**
 * URL da tela de login com retorno para a página atual (ou outra informada).
 */
function ga_login_url( string $redirect = '', string $page = 'login' ): string {
	$url      = ga_page_url( $page );
	$redirect = $redirect ?: ga_current_url();
	if ( untrailingslashit( $redirect ) === untrailingslashit( $url ) ) {
		return $url;
	}
	return add_query_arg( 'redirect_to', rawurlencode( $redirect ), $url );
}

/**
 * Destino seguro após login: só aceita URLs do próprio site.
 */
function ga_safe_redirect_target( $target, string $fallback = '' ): string {
	$fallback = $fallback ?: ga_page_url( 'account' );
	if ( ! is_string( $target ) || '' === $target ) {
		return $fallback;
	}
	return wp_validate_redirect( esc_url_raw( $target ), $fallback );
}

function ga_contact_email(): string {
	return ga_option( 'contact_email', get_option( 'admin_email' ) );
}

/**
 * IP do visitante para limites de tentativas (não é armazenado em banco).
 */
function ga_client_ip(): string {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0.0.0.0';
	return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '0.0.0.0';
}

/**
 * Limite simples por IP e ação. Retorna false quando o limite estourou.
 */
function ga_rate_limit( string $action, int $max, int $window ): bool {
	$key   = 'ga_rl_' . md5( $action . '|' . ga_client_ip() );
	$count = (int) get_transient( $key );
	if ( $count >= $max ) {
		return false;
	}
	set_transient( $key, $count + 1, $window );
	return true;
}

function ga_rate_limit_reset( string $action ): void {
	delete_transient( 'ga_rl_' . md5( $action . '|' . ga_client_ip() ) );
}

/**
 * Primeiro nome do usuário para saudações.
 */
function ga_user_first_name( ?WP_User $user = null ): string {
	$user = $user ?: wp_get_current_user();
	if ( ! $user || ! $user->exists() ) {
		return '';
	}
	$name = $user->first_name ?: $user->display_name;
	return trim( strtok( $name, ' ' ) ?: $name );
}

function ga_user_initials( ?WP_User $user = null ): string {
	$user  = $user ?: wp_get_current_user();
	$name  = trim( $user->display_name ?: $user->user_email );
	$parts = preg_split( '/\s+/', $name );
	$first = mb_substr( $parts[0] ?? '', 0, 1 );
	$last  = count( $parts ) > 1 ? mb_substr( end( $parts ), 0, 1 ) : '';
	return mb_strtoupper( $first . $last );
}

/**
 * Foto do perfil Google, quando a conta foi criada/vinculada pelo Google.
 * Sem foto, a interface mostra as iniciais.
 */
function ga_user_avatar_url( ?WP_User $user = null ): string {
	$user = $user ?: wp_get_current_user();
	return esc_url_raw( (string) get_user_meta( $user->ID, 'ga_google_picture', true ) );
}

function ga_avatar_html( string $class = '', ?WP_User $user = null ): string {
	$user = $user ?: wp_get_current_user();
	$url  = ga_user_avatar_url( $user );
	$out  = '<span class="ga-avatar ' . esc_attr( $class ) . '" aria-hidden="true">';
	$out .= $url
		? '<img src="' . esc_url( $url ) . '" alt="" referrerpolicy="no-referrer" loading="lazy">'
		: '<span>' . esc_html( ga_user_initials( $user ) ) . '</span>';
	return $out . '</span>';
}

/**
 * Número com a palavra no singular ou plural ("1 cena", "6 cenas"), no idioma da página.
 * As palavras são passadas em português e traduzidas no dicionário.
 */
function ga_plural( int $n, string $singular, string $plural ): string {
	return sprintf( '%d %s', $n, ga_t( 1 === $n ? $singular : $plural ) );
}

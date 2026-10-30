<?php
/**
 * Contas: cadastro com nome e e-mail obrigatórios, login próprio e redirecionamentos do wp-login.
 */

defined( 'ABSPATH' ) || exit;

function ga_registration_open(): bool {
	return (bool) get_option( 'users_can_register' );
}

/**
 * Usa as telas do tema no lugar do wp-login.php.
 */
add_filter( 'login_url', function ( $url, $redirect ) {
	if ( ! ga_page_id( 'login' ) ) {
		return $url;
	}
	return $redirect ? add_query_arg( 'redirect_to', rawurlencode( $redirect ), ga_page_url( 'login' ) ) : ga_page_url( 'login' );
}, 10, 2 );

add_filter( 'register_url', fn( $url ) => ga_page_id( 'register' ) ? ga_page_url( 'register' ) : $url );

add_filter( 'lostpassword_url', function ( $url ) {
	return ga_page_id( 'login' ) ? add_query_arg( 'modo', 'recuperar', ga_page_url( 'login' ) ) : $url;
} );

add_action( 'login_init', function () {
	$action = sanitize_key( $_REQUEST['action'] ?? 'login' );
	if ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) || isset( $_REQUEST['interim-login'] ) || ! ga_page_id( 'login' ) ) {
		return;
	}
	$redirect = isset( $_REQUEST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_REQUEST['redirect_to'] ) ) : '';

	if ( in_array( $action, [ 'login', 'register', 'lostpassword', 'retrievepassword' ], true ) && ! isset( $_GET['checkemail'] ) ) {
		$target = 'register' === $action ? ga_page_url( 'register' ) : ga_page_url( 'login' );
		if ( in_array( $action, [ 'lostpassword', 'retrievepassword' ], true ) ) {
			$target = add_query_arg( 'modo', 'recuperar', $target );
		}
		if ( isset( $_GET['loggedout'] ) ) {
			$target = home_url( '/' );
		}
		if ( $redirect ) {
			$target = add_query_arg( 'redirect_to', rawurlencode( $redirect ), $target );
		}
		wp_safe_redirect( $target );
		exit;
	}
} );

add_filter( 'logout_redirect', fn( $to, $requested ) => $requested ?: home_url( '/' ), 10, 2 );

/**
 * Depois de redefinir a senha, o link "Acessar" leva para a tela do tema.
 */
add_filter( 'login_message', function ( $message ) {
	if ( isset( $_GET['action'] ) && 'resetpass' === $_GET['action'] ) {
		$message .= '<p class="message"><a href="' . esc_url( ga_page_url( 'login' ) ) . '">Ir para o login →</a></p>';
	}
	return $message;
} );

/**
 * Telas de login/cadastro: quem já está logado vai para a conta.
 * Conta: quem não está logado vai para o login.
 */
add_action( 'template_redirect', function () {
	if ( is_user_logged_in() && ( ga_is_page( 'login' ) || ga_is_page( 'register' ) ) ) {
		wp_safe_redirect( ga_safe_redirect_target( wp_unslash( $_GET['redirect_to'] ?? '' ) ) );
		exit;
	}
	if ( ! is_user_logged_in() && ga_is_page( 'account' ) ) {
		wp_safe_redirect( ga_login_url( ga_page_url( 'account' ) ) );
		exit;
	}
} );

/**
 * Valida nome/e-mail/senha e cria o assinante. Também usado pelo login com Google.
 *
 * @return int|WP_Error
 */
function ga_register_user( string $name, string $email, string $password, array $extra = [] ) {
	$name  = trim( preg_replace( '/\s+/', ' ', $name ) );
	$email = sanitize_email( $email );

	if ( mb_strlen( $name ) < 2 ) {
		return new WP_Error( 'name', __( 'Conta pra gente como você se chama.', 'getalive' ), [ 'field' => 'name' ] );
	}
	if ( ! is_email( $email ) ) {
		return new WP_Error( 'email', __( 'Esse e-mail não parece válido.', 'getalive' ), [ 'field' => 'email' ] );
	}
	if ( email_exists( $email ) ) {
		return new WP_Error( 'email_exists', __( 'Já existe uma conta com esse e-mail. Que tal entrar?', 'getalive' ), [ 'field' => 'email' ] );
	}
	if ( strlen( $password ) < 8 ) {
		return new WP_Error( 'password', __( 'A senha precisa ter pelo menos 8 caracteres.', 'getalive' ), [ 'field' => 'password' ] );
	}

	$parts   = explode( ' ', $name, 2 );
	$user_id = wp_insert_user( [
		'user_login'   => ga_unique_username( $email ),
		'user_email'   => $email,
		'user_pass'    => $password,
		'display_name' => $name,
		'nickname'     => $parts[0],
		'first_name'   => $parts[0],
		'last_name'    => $parts[1] ?? '',
		'role'         => get_option( 'default_role', 'subscriber' ),
		// Quem se cadastra em inglês ou espanhol recebe os e-mails do WordPress nesse idioma.
		'locale'       => ga_lang_is_default() ? '' : GA_LANGS[ ga_lang() ]['locale'],
	] );

	if ( is_wp_error( $user_id ) ) {
		return $user_id;
	}

	ga_record_consent( $user_id, $extra['source'] ?? 'form' );
	wp_send_new_user_notifications( $user_id, 'admin' );
	do_action( 'ga_user_registered', $user_id, $extra );
	return $user_id;
}

function ga_unique_username( string $email ): string {
	$base = sanitize_user( strtolower( strstr( $email, '@', true ) ), true ) ?: 'user';
	$base = substr( preg_replace( '/[^a-z0-9._-]/', '', $base ), 0, 40 ) ?: 'user';
	$name = $base;
	$i    = 1;
	while ( username_exists( $name ) ) {
		$name = $base . ++$i;
	}
	return $name;
}

/**
 * Registro do aceite da política de privacidade (LGPD art. 8º: prova do consentimento).
 */
function ga_record_consent( int $user_id, string $source ): void {
	update_user_meta( $user_id, 'ga_privacy_consent', [
		'at'      => time(),
		'source'  => $source,
		'policy'  => ga_page_id( 'privacy' ) ? get_post_modified_time( 'U', true, ga_page_id( 'privacy' ) ) : 0,
		'ip_hash' => wp_hash( ga_client_ip() ),
	] );
}

/**
 * Faz login de um usuário já autenticado por outro meio (cadastro, Google).
 */
function ga_login_as( int $user_id, bool $remember = true ): void {
	// Sem isso, wp_create_nonce() nesta mesma requisição ainda usaria a sessão antiga.
	$use_new_cookie = function ( $cookie ) {
		$_COOKIE[ LOGGED_IN_COOKIE ] = $cookie;
	};
	add_action( 'set_logged_in_cookie', $use_new_cookie );
	wp_clear_auth_cookie();
	wp_set_current_user( $user_id );
	wp_set_auth_cookie( $user_id, $remember, is_ssl() );
	remove_action( 'set_logged_in_cookie', $use_new_cookie );
	do_action( 'wp_login', get_userdata( $user_id )->user_login, get_userdata( $user_id ) );
}

/**
 * Contas sem nome (criadas por fora do fluxo do tema) precisam completar o perfil.
 */
function ga_user_needs_profile( ?WP_User $user = null ): bool {
	$user = $user ?: wp_get_current_user();
	if ( ! $user->exists() || user_can( $user, 'edit_posts' ) ) {
		return false;
	}
	$name = trim( $user->first_name ?: '' );
	return '' === $name || ! is_email( $user->user_email );
}

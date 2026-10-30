<?php
/**
 * API REST do tema (getalive/v1): autenticação, favoritos, solicitações e conta.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'rest_api_init', function () {
	$ns        = 'getalive/v1';
	$public    = '__return_true';
	$logged_in = fn() => is_user_logged_in();

	register_rest_route( $ns, '/auth/login', [
		'methods'             => 'POST',
		'permission_callback' => $public,
		'callback'            => 'ga_rest_login',
	] );
	register_rest_route( $ns, '/auth/register', [
		'methods'             => 'POST',
		'permission_callback' => $public,
		'callback'            => 'ga_rest_register',
	] );
	register_rest_route( $ns, '/auth/google', [
		'methods'             => 'POST',
		'permission_callback' => $public,
		'callback'            => 'ga_rest_google',
	] );
	register_rest_route( $ns, '/auth/lost-password', [
		'methods'             => 'POST',
		'permission_callback' => $public,
		'callback'            => 'ga_rest_lost_password',
	] );
	register_rest_route( $ns, '/favorites/(?P<id>\d+)', [
		'methods'             => 'POST',
		'permission_callback' => $logged_in,
		'callback'            => 'ga_rest_favorite',
	] );
	register_rest_route( $ns, '/requests', [
		'methods'             => 'POST',
		'permission_callback' => $logged_in,
		'callback'            => 'ga_rest_request',
	] );
	register_rest_route( $ns, '/account/profile', [
		'methods'             => 'POST',
		'permission_callback' => $logged_in,
		'callback'            => 'ga_rest_profile',
	] );
	register_rest_route( $ns, '/account/delete', [
		'methods'             => 'POST',
		'permission_callback' => $logged_in,
		'callback'            => 'ga_rest_delete_account',
	] );
} );

function ga_rest_error( WP_Error $error, int $status = 400 ): WP_REST_Response {
	$data = $error->get_error_data();
	return new WP_REST_Response( [
		'code'    => $error->get_error_code(),
		'message' => wp_strip_all_tags( $error->get_error_message() ),
		'field'   => is_array( $data ) ? ( $data['field'] ?? null ) : null,
	], $status );
}

function ga_rest_fail( string $code, string $message, int $status = 400, ?string $field = null ): WP_REST_Response {
	return ga_rest_error( new WP_Error( $code, $message, [ 'field' => $field ] ), $status );
}

function ga_rest_redirect( WP_REST_Request $req ): string {
	return ga_safe_redirect_target( (string) $req->get_param( 'redirect_to' ) );
}

/* ---------------------------------------------------------------------------
 * Autenticação
 * ------------------------------------------------------------------------- */

function ga_rest_login( WP_REST_Request $req ) {
	if ( ! ga_rate_limit( 'login', 10, 15 * MINUTE_IN_SECONDS ) ) {
		return ga_rest_fail( 'throttled', __( 'Muitas tentativas. Espere alguns minutos e tente de novo.', 'getalive' ), 429 );
	}

	$login    = trim( (string) $req->get_param( 'email' ) );
	$password = (string) $req->get_param( 'password' );
	if ( '' === $login ) {
		return ga_rest_fail( 'email', __( 'Informe seu e-mail.', 'getalive' ), 400, 'email' );
	}
	if ( '' === $password ) {
		return ga_rest_fail( 'password', __( 'Informe sua senha.', 'getalive' ), 400, 'password' );
	}

	$user = wp_signon( [
		'user_login'    => $login,
		'user_password' => $password,
		'remember'      => (bool) $req->get_param( 'remember' ),
	], is_ssl() );

	if ( is_wp_error( $user ) ) {
		$field = in_array( $user->get_error_code(), [ 'invalid_email', 'invalid_username' ], true ) ? 'email' : 'password';
		$msg   = 'email' === $field ? __( 'Não encontramos uma conta com esse e-mail.', 'getalive' ) : __( 'Senha incorreta. Confira e tente de novo.', 'getalive' );
		return ga_rest_fail( 'login', $msg, 401, $field );
	}

	ga_rate_limit_reset( 'login' );
	return [ 'ok' => true, 'redirect' => ga_rest_redirect( $req ), 'name' => ga_user_first_name( $user ) ];
}

function ga_rest_register( WP_REST_Request $req ) {
	if ( ! ga_registration_open() ) {
		return ga_rest_fail( 'closed', __( 'Os cadastros estão fechados no momento.', 'getalive' ), 403 );
	}
	// Honeypot: humanos não veem esse campo.
	if ( '' !== (string) $req->get_param( 'website' ) ) {
		return [ 'ok' => true, 'redirect' => home_url( '/' ) ];
	}
	if ( ! ga_rate_limit( 'register', 5, HOUR_IN_SECONDS ) ) {
		return ga_rest_fail( 'throttled', __( 'Muitos cadastros a partir desta rede. Tente mais tarde.', 'getalive' ), 429 );
	}
	if ( ! $req->get_param( 'consent' ) ) {
		return ga_rest_fail( 'consent', __( 'Para criar a conta, aceite a Política de Privacidade.', 'getalive' ), 400, 'consent' );
	}

	$user_id = ga_register_user(
		(string) $req->get_param( 'name' ),
		(string) $req->get_param( 'email' ),
		(string) $req->get_param( 'password' )
	);
	if ( is_wp_error( $user_id ) ) {
		return ga_rest_error( $user_id );
	}

	ga_login_as( $user_id );
	return [ 'ok' => true, 'redirect' => ga_rest_redirect( $req ), 'name' => ga_user_first_name( get_userdata( $user_id ) ) ];
}

function ga_rest_google( WP_REST_Request $req ) {
	if ( ! ga_rate_limit( 'google', 20, 15 * MINUTE_IN_SECONDS ) ) {
		return ga_rest_fail( 'throttled', __( 'Muitas tentativas. Espere alguns minutos.', 'getalive' ), 429 );
	}
	$token = (string) $req->get_param( 'access_token' );
	if ( '' === $token ) {
		return ga_rest_fail( 'token', __( 'Login com Google cancelado.', 'getalive' ) );
	}
	$result = ga_google_sign_in( $token );
	if ( is_wp_error( $result ) ) {
		return ga_rest_error( $result, 401 );
	}
	return [
		'ok'       => true,
		'created'  => $result['created'],
		'redirect' => ga_rest_redirect( $req ),
		'name'     => ga_user_first_name( get_userdata( $result['user_id'] ) ),
	];
}

function ga_rest_lost_password( WP_REST_Request $req ) {
	if ( ! ga_rate_limit( 'lost', 5, HOUR_IN_SECONDS ) ) {
		return ga_rest_fail( 'throttled', __( 'Muitos pedidos. Tente de novo mais tarde.', 'getalive' ), 429 );
	}
	$email = sanitize_email( (string) $req->get_param( 'email' ) );
	if ( ! is_email( $email ) ) {
		return ga_rest_fail( 'email', __( 'Informe um e-mail válido.', 'getalive' ), 400, 'email' );
	}
	if ( email_exists( $email ) ) {
		retrieve_password( $email );
	}
	// Mesma resposta sempre, para não revelar quais e-mails têm conta.
	return [ 'ok' => true, 'message' => __( 'Se existir uma conta com esse e-mail, você vai receber um link para criar uma nova senha.', 'getalive' ) ];
}

/* ---------------------------------------------------------------------------
 * Favoritos e solicitações
 * ------------------------------------------------------------------------- */

function ga_rest_favorite( WP_REST_Request $req ) {
	$id = (int) $req['id'];
	if ( 'ga_template' !== get_post_type( $id ) || 'publish' !== get_post_status( $id ) ) {
		return ga_rest_fail( 'not_found', __( 'Template não encontrado.', 'getalive' ), 404 );
	}
	$on = null === $req->get_param( 'on' ) ? ! ga_is_favorite( $id ) : rest_sanitize_boolean( $req->get_param( 'on' ) );
	ga_set_favorite( $id, $on );
	return [ 'ok' => true, 'favorite' => $on, 'count' => count( ga_user_favorites() ) ];
}

function ga_rest_request( WP_REST_Request $req ) {
	if ( '' !== (string) $req->get_param( 'website' ) ) {
		return [ 'ok' => true ];
	}
	if ( ! ga_rate_limit( 'request_' . get_current_user_id(), 5, DAY_IN_SECONDS ) ) {
		return ga_rest_fail( 'throttled', __( 'Você já mandou várias ideias hoje. Volte amanhã!', 'getalive' ), 429 );
	}

	$options = ga_request_options();
	$pick    = fn( $key ) => array_values( array_intersect( $options[ $key ], array_map( 'sanitize_text_field', (array) $req->get_param( $key ) ) ) );

	$title = sanitize_text_field( (string) $req->get_param( 'title' ) );
	$desc  = sanitize_textarea_field( (string) $req->get_param( 'description' ) );
	if ( mb_strlen( $title ) < 3 ) {
		return ga_rest_fail( 'title', __( 'Dê um nome para a sua ideia.', 'getalive' ), 400, 'title' );
	}
	if ( mb_strlen( $desc ) < 20 ) {
		return ga_rest_fail( 'description', __( 'Conte um pouco mais (pelo menos 20 caracteres).', 'getalive' ), 400, 'description' );
	}

	$refs = array_slice( array_values( array_filter( array_map(
		fn( $url ) => wp_http_validate_url( esc_url_raw( trim( $url ) ) ) ?: null,
		preg_split( '/[\s,]+/', (string) $req->get_param( 'references' ) )
	) ) ), 0, 8 );

	$id = ga_create_request( get_current_user_id(), [
		'title'       => mb_substr( $title, 0, 120 ),
		'description' => mb_substr( $desc, 0, 4000 ),
		'vibes'       => $pick( 'vibes' ),
		'scenes'      => $pick( 'scenes' ),
		'platforms'   => $pick( 'platforms' ),
		'references'  => $refs,
	] );

	if ( is_wp_error( $id ) ) {
		return ga_rest_error( $id, 500 );
	}
	return [ 'ok' => true, 'id' => $id ];
}

/* ---------------------------------------------------------------------------
 * Conta
 * ------------------------------------------------------------------------- */

function ga_rest_profile( WP_REST_Request $req ) {
	$user = wp_get_current_user();
	$name = trim( preg_replace( '/\s+/', ' ', sanitize_text_field( (string) $req->get_param( 'name' ) ) ) );
	if ( mb_strlen( $name ) < 2 ) {
		return ga_rest_fail( 'name', __( 'Informe seu nome.', 'getalive' ), 400, 'name' );
	}

	$parts  = explode( ' ', $name, 2 );
	$update = [
		'ID'           => $user->ID,
		'display_name' => $name,
		'nickname'     => $parts[0],
		'first_name'   => $parts[0],
		'last_name'    => $parts[1] ?? '',
	];

	$new = (string) $req->get_param( 'new_password' );
	if ( '' !== $new ) {
		if ( strlen( $new ) < 8 ) {
			return ga_rest_fail( 'new_password', __( 'A nova senha precisa ter pelo menos 8 caracteres.', 'getalive' ), 400, 'new_password' );
		}
		// Contas criadas pelo Google podem definir senha sem informar a atual.
		$no_password = (bool) get_user_meta( $user->ID, 'ga_password_unset', true );
		if ( ! $no_password && ! wp_check_password( (string) $req->get_param( 'current_password' ), $user->user_pass, $user->ID ) ) {
			return ga_rest_fail( 'current_password', __( 'A senha atual não confere.', 'getalive' ), 400, 'current_password' );
		}
		$update['user_pass'] = $new;
	}

	$result = wp_update_user( $update );
	if ( is_wp_error( $result ) ) {
		return ga_rest_error( $result );
	}
	$response = [ 'ok' => true, 'message' => __( 'Perfil atualizado.', 'getalive' ), 'name' => $name ];
	if ( isset( $update['user_pass'] ) ) {
		delete_user_meta( $user->ID, 'ga_password_unset' );
		// Trocar a senha derruba a sessão; renova os cookies e entrega o nonce da sessão nova.
		ga_login_as( $user->ID );
		$response['nonce'] = wp_create_nonce( 'wp_rest' );
		// O link "Sair" do cabeçalho (fora do Swup) tem nonce da sessão antiga.
		$response['logout'] = html_entity_decode( wp_logout_url( home_url( '/' ) ) );
	}
	return $response;
}

function ga_rest_delete_account( WP_REST_Request $req ) {
	$user = wp_get_current_user();
	if ( user_can( $user, 'edit_posts' ) ) {
		return ga_rest_fail( 'forbidden', __( 'Contas da equipe não podem ser excluídas por aqui.', 'getalive' ), 403 );
	}
	// Vale a palavra do idioma da página ou a em português.
	if ( ! in_array( mb_strtoupper( trim( (string) $req->get_param( 'confirm' ) ) ), [ 'EXCLUIR', __( 'EXCLUIR', 'getalive' ) ], true ) ) {
		/* translators: %s: palavra de confirmação */
		return ga_rest_fail( 'confirm', sprintf( __( 'Digite %s para confirmar.', 'getalive' ), __( 'EXCLUIR', 'getalive' ) ), 400, 'confirm' );
	}

	require_once ABSPATH . 'wp-admin/includes/user.php';
	// Solicitações ficam anônimas (atribuídas a ninguém) em vez de sumirem.
	foreach ( ga_user_requests( $user->ID ) as $request ) {
		wp_update_post( [ 'ID' => $request->ID, 'post_author' => 0 ] );
	}
	wp_logout();
	wp_delete_user( $user->ID );
	return [ 'ok' => true, 'redirect' => home_url( '/' ) ];
}

<?php
/**
 * Entrar com o Google (Google Identity Services, fluxo de token no navegador).
 *
 * O navegador recebe um access token pelo popup do Google e o envia para a API.
 * Aqui o token é validado (aud = nosso Client ID) e os dados do perfil são lidos
 * direto do Google, então nada que vem do navegador é confiado sem checagem.
 */

defined( 'ABSPATH' ) || exit;

/**
 * @return array{sub:string,email:string,name:string,picture:string}|WP_Error
 */
function ga_google_profile( string $access_token ) {
	$client_id = ga_option( 'google_client_id' );
	if ( ! $client_id ) {
		return new WP_Error( 'google_off', 'O login com Google não está configurado.' );
	}

	$info = wp_remote_get( 'https://oauth2.googleapis.com/tokeninfo?access_token=' . rawurlencode( $access_token ), [ 'timeout' => 10 ] );
	if ( is_wp_error( $info ) || 200 !== wp_remote_retrieve_response_code( $info ) ) {
		return new WP_Error( 'google_token', 'Não conseguimos confirmar seu login com o Google. Tente de novo.' );
	}
	$info = json_decode( wp_remote_retrieve_body( $info ), true );
	if ( ( $info['aud'] ?? '' ) !== $client_id && ( $info['azp'] ?? '' ) !== $client_id ) {
		return new WP_Error( 'google_aud', 'Esse login do Google não pertence a este site.' );
	}

	$res = wp_remote_get( 'https://openidconnect.googleapis.com/v1/userinfo', [
		'timeout' => 10,
		'headers' => [ 'Authorization' => 'Bearer ' . $access_token ],
	] );
	if ( is_wp_error( $res ) || 200 !== wp_remote_retrieve_response_code( $res ) ) {
		return new WP_Error( 'google_profile', 'Não conseguimos ler seu perfil do Google.' );
	}
	$profile = json_decode( wp_remote_retrieve_body( $res ), true );

	if ( empty( $profile['email'] ) || empty( $profile['email_verified'] ) ) {
		return new WP_Error( 'google_email', 'Sua conta Google precisa ter um e-mail verificado.' );
	}

	return [
		'sub'     => (string) $profile['sub'],
		'email'   => sanitize_email( $profile['email'] ),
		'name'    => sanitize_text_field( $profile['name'] ?? trim( ( $profile['given_name'] ?? '' ) . ' ' . ( $profile['family_name'] ?? '' ) ) ),
		'picture' => esc_url_raw( $profile['picture'] ?? '' ),
	];
}

/**
 * Encontra (pelo ID do Google ou e-mail) ou cria o usuário e faz login.
 *
 * @return array{user_id:int,created:bool}|WP_Error
 */
function ga_google_sign_in( string $access_token ) {
	$profile = ga_google_profile( $access_token );
	if ( is_wp_error( $profile ) ) {
		return $profile;
	}

	$users   = get_users( [ 'meta_key' => 'ga_google_sub', 'meta_value' => $profile['sub'], 'number' => 1, 'fields' => 'ID' ] );
	$user_id = $users ? (int) $users[0] : (int) email_exists( $profile['email'] );
	$created = false;

	if ( ! $user_id ) {
		if ( ! ga_registration_open() ) {
			return new WP_Error( 'closed', 'Os cadastros estão fechados no momento.' );
		}
		$name = $profile['name'] ?: ucfirst( strstr( $profile['email'], '@', true ) );
		// Senha aleatória: a conta entra pelo Google, e pode definir uma senha depois.
		$user_id = ga_register_user( $name, $profile['email'], wp_generate_password( 32, true, true ), [ 'source' => 'google' ] );
		if ( is_wp_error( $user_id ) ) {
			return $user_id;
		}
		update_user_meta( $user_id, 'ga_password_unset', 1 );
		$created = true;
	}

	update_user_meta( $user_id, 'ga_google_sub', $profile['sub'] );
	if ( $profile['picture'] ) {
		update_user_meta( $user_id, 'ga_google_picture', $profile['picture'] );
	}

	// Conta antiga sem nome: completa com o nome do Google.
	$user = get_userdata( $user_id );
	if ( ! $user->first_name && $profile['name'] ) {
		$parts = explode( ' ', $profile['name'], 2 );
		wp_update_user( [ 'ID' => $user_id, 'first_name' => $parts[0], 'last_name' => $parts[1] ?? '', 'display_name' => $profile['name'] ] );
	}

	ga_login_as( $user_id );
	return [ 'user_id' => $user_id, 'created' => $created ];
}

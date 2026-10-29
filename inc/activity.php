<?php
/**
 * Histórico de atividade dos usuários, para auditoria: cadastro, acessos, páginas
 * vistas e ações no site. Fica numa tabela própria e só aparece para administradores,
 * na página de perfil do usuário (author.php).
 */

defined( 'ABSPATH' ) || exit;

const GA_ACTIVITY_DB = '1';

/** Registros mais antigos que isso são apagados (LGPD: guardar só o necessário). */
const GA_ACTIVITY_RETENTION_DAYS = 365;

function ga_activity_table(): string {
	global $wpdb;
	return $wpdb->prefix . 'ga_activity';
}

/**
 * Cria a tabela na primeira execução e preenche com o que já existia
 * (cadastro, downloads e solicitações), para os usuários antigos não começarem vazios.
 */
add_action( 'init', function () {
	if ( get_option( 'ga_activity_db' ) === GA_ACTIVITY_DB ) {
		return;
	}
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( 'CREATE TABLE ' . ga_activity_table() . " (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		user_id bigint(20) unsigned NOT NULL,
		action varchar(32) NOT NULL,
		object_id bigint(20) unsigned NOT NULL DEFAULT 0,
		url varchar(255) NOT NULL DEFAULT '',
		detail text NULL,
		ip varchar(45) NOT NULL DEFAULT '',
		created_at datetime NOT NULL,
		PRIMARY KEY  (id),
		KEY user_time (user_id,created_at)
	) {$wpdb->get_charset_collate()};" );

	if ( ! get_option( 'ga_activity_db' ) ) {
		ga_activity_backfill();
	}
	update_option( 'ga_activity_db', GA_ACTIVITY_DB );
} );

function ga_activity_backfill(): void {
	foreach ( get_users( [ 'fields' => [ 'ID', 'user_registered' ] ] ) as $user ) {
		$id      = (int) $user->ID;
		$consent = (array) get_user_meta( $id, 'ga_privacy_consent', true );
		ga_log_activity( $id, 'register', [
			'time'   => strtotime( $user->user_registered . ' UTC' ),
			'detail' => [ 'method' => $consent['source'] ?? '', 'backfill' => 1 ],
		] );
		foreach ( ga_user_downloads( $id ) as $post_id => $time ) {
			ga_log_activity( $id, 'download', [ 'object_id' => $post_id, 'time' => $time, 'detail' => [ 'title' => get_the_title( $post_id ), 'backfill' => 1 ] ] );
		}
	}
	foreach ( get_posts( [ 'post_type' => 'ga_request', 'posts_per_page' => -1, 'post_status' => 'any' ] ) as $request ) {
		ga_log_activity( (int) $request->post_author, 'request', [
			'object_id' => $request->ID,
			'time'      => strtotime( $request->post_date_gmt . ' UTC' ),
			'detail'    => [ 'title' => $request->post_title ],
		] );
	}
}

/**
 * Grava um evento. $args: object_id, url, detail (array), ip (true = IP atual, ou o IP), time.
 */
function ga_log_activity( int $user_id, string $action, array $args = [] ): void {
	global $wpdb;
	if ( ! $user_id ) {
		return;
	}
	$ip = $args['ip'] ?? '';
	$wpdb->insert( ga_activity_table(), [
		'user_id'    => $user_id,
		'action'     => $action,
		'object_id'  => (int) ( $args['object_id'] ?? 0 ),
		'url'        => mb_substr( (string) ( $args['url'] ?? '' ), 0, 255 ),
		'detail'     => empty( $args['detail'] ) ? null : wp_json_encode( $args['detail'] ),
		'ip'         => true === $ip ? ga_client_ip() : (string) $ip,
		'created_at' => gmdate( 'Y-m-d H:i:s', (int) ( $args['time'] ?? time() ) ),
	] );
}

/**
 * Eventos de um usuário, do mais recente ao mais antigo.
 *
 * @param string[] $actions Filtra por tipo; vazio traz todos.
 */
function ga_user_activity( int $user_id, array $actions = [], int $limit = 100, int $offset = 0 ): array {
	global $wpdb;
	$where = $wpdb->prepare( 'user_id = %d', $user_id );
	if ( $actions ) {
		$where .= " AND action IN ('" . implode( "','", array_map( 'esc_sql', $actions ) ) . "')";
	}
	$rows = $wpdb->get_results( $wpdb->prepare(
		'SELECT * FROM ' . ga_activity_table() . " WHERE $where ORDER BY created_at DESC, id DESC LIMIT %d OFFSET %d",
		$limit,
		$offset
	) );
	foreach ( $rows as $row ) {
		$row->detail = $row->detail ? (array) json_decode( $row->detail, true ) : [];
		$row->time   = strtotime( $row->created_at . ' UTC' );
	}
	return $rows;
}

function ga_user_activity_counts( int $user_id ): array {
	global $wpdb;
	$rows = $wpdb->get_results( $wpdb->prepare( 'SELECT action, COUNT(*) AS total, MAX(created_at) AS last FROM ' . ga_activity_table() . ' WHERE user_id = %d GROUP BY action', $user_id ), OBJECT_K );
	return array_map( fn( $r ) => [ 'total' => (int) $r->total, 'last' => strtotime( $r->last . ' UTC' ) ], $rows );
}

/**
 * Grupos do filtro da linha do tempo: chave => [ rótulo, tipos ].
 */
function ga_activity_groups(): array {
	return [
		'tudo'      => [ 'Tudo', [] ],
		'acoes'     => [ 'Ações', [ 'register', 'favorite_add', 'favorite_remove', 'download', 'request', 'request_status', 'profile_update' ] ],
		'acesso'    => [ 'Acesso e segurança', [ 'register', 'login', 'login_failed', 'logout', 'password_reset_request', 'password_reset', 'profile_update' ] ],
		'navegacao' => [ 'Navegação', [ 'view' ] ],
	];
}

/* ---------------------------------------------------------------------------
 * O que é registrado
 * ------------------------------------------------------------------------- */

/** Rota REST da requisição atual, para saber de onde veio um login. */
function ga_current_rest_route(): string {
	return (string) ( $GLOBALS['wp']->query_vars['rest_route'] ?? '' );
}

function ga_user_agent(): string {
	return mb_substr( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ?? '' ) ), 0, 200 );
}

add_action( 'ga_user_registered', function ( $user_id, $extra ) {
	ga_log_activity( $user_id, 'register', [ 'ip' => true, 'detail' => [ 'method' => $extra['source'] ?? 'form' ] ] );
}, 10, 2 );

add_action( 'wp_login', function ( $login, $user ) {
	$route = ga_current_rest_route();
	// Trocar a senha renova a sessão (ga_login_as), mas não é um novo login.
	if ( str_contains( $route, '/account/profile' ) ) {
		return;
	}
	$method = match ( true ) {
		str_contains( $route, '/auth/google' )   => 'google',
		str_contains( $route, '/auth/register' ) => 'register',
		str_contains( $route, '/auth/login' )    => 'password',
		default                                  => 'wp-login',
	};
	ga_log_activity( $user->ID, 'login', [ 'ip' => true, 'detail' => [ 'method' => $method, 'agent' => ga_user_agent() ] ] );
}, 10, 2 );

add_action( 'wp_login_failed', function ( $login ) {
	$user = is_email( $login ) ? get_user_by( 'email', $login ) : get_user_by( 'login', $login );
	if ( $user ) {
		ga_log_activity( $user->ID, 'login_failed', [ 'ip' => true, 'detail' => [ 'agent' => ga_user_agent() ] ] );
	}
} );

add_action( 'wp_logout', function ( $user_id ) {
	ga_log_activity( (int) $user_id, 'logout', [ 'ip' => true ] );
} );

add_action( 'retrieve_password', function ( $login ) {
	$user = get_user_by( 'login', $login );
	if ( $user ) {
		ga_log_activity( $user->ID, 'password_reset_request', [ 'ip' => true ] );
	}
} );

add_action( 'after_password_reset', function ( $user ) {
	ga_log_activity( $user->ID, 'password_reset', [ 'ip' => true ] );
} );

add_action( 'profile_update', function ( $user_id, $old, $data ) {
	$changed = [];
	if ( isset( $data['display_name'] ) && $data['display_name'] !== $old->display_name ) {
		$changed[] = 'nome';
	}
	if ( isset( $data['user_email'] ) && $data['user_email'] !== $old->user_email ) {
		$changed[] = 'e-mail';
	}
	if ( isset( $data['user_pass'] ) && $data['user_pass'] !== $old->user_pass ) {
		$changed[] = 'senha';
	}
	if ( ! $changed ) {
		return;
	}
	$detail = [ 'fields' => $changed ];
	if ( get_current_user_id() && get_current_user_id() !== (int) $user_id ) {
		$detail['by'] = wp_get_current_user()->display_name;
	}
	ga_log_activity( (int) $user_id, 'profile_update', [ 'ip' => true, 'detail' => $detail ] );
}, 10, 3 );

add_action( 'deleted_user', function ( $user_id ) {
	global $wpdb;
	$wpdb->delete( ga_activity_table(), [ 'user_id' => (int) $user_id ] );
} );

/**
 * Páginas vistas: o navegador avisa a cada página exibida (carregamento e navegação
 * pelo Swup). Pelo servidor não dá, porque o Swup pré-carrega links no hover.
 */
add_action( 'rest_api_init', function () {
	register_rest_route( 'getalive/v1', '/activity/view', [
		'methods'             => 'POST',
		'permission_callback' => fn() => is_user_logged_in(),
		'callback'            => 'ga_rest_activity_view',
	] );
} );

function ga_rest_activity_view( WP_REST_Request $req ) {
	global $wpdb;
	$path = (string) $req->get_param( 'path' );
	$home = wp_parse_url( home_url( '/' ), PHP_URL_PATH ) ?: '/';
	if ( strlen( $path ) > 255 || str_starts_with( $path, '//' ) || ! str_starts_with( $path, $home ) ) {
		return [ 'ok' => false ];
	}
	$url     = esc_url_raw( home_url( substr( $path, strlen( $home ) - 1 ) ) );
	$user_id = get_current_user_id();

	// Recarregar a mesma página em seguida não vira outro registro.
	$last = $wpdb->get_row( $wpdb->prepare( 'SELECT url, created_at FROM ' . ga_activity_table() . " WHERE user_id = %d AND action = 'view' ORDER BY id DESC LIMIT 1", $user_id ) );
	if ( $last && $last->url === $url && time() - strtotime( $last->created_at . ' UTC' ) < MINUTE_IN_SECONDS ) {
		return [ 'ok' => true ];
	}

	$title = sanitize_text_field( (string) $req->get_param( 'title' ) );
	$title = preg_replace( '/\s+[–—|-]\s+' . preg_quote( get_bloginfo( 'name' ), '/' ) . '$/u', '', $title );
	ga_log_activity( $user_id, 'view', [
		'url'       => $url,
		'object_id' => url_to_postid( $url ),
		'detail'    => [ 'title' => mb_substr( $title, 0, 160 ) ],
	] );
	return [ 'ok' => true ];
}

/* ---------------------------------------------------------------------------
 * Retenção
 * ------------------------------------------------------------------------- */

add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'ga_activity_prune' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'ga_activity_prune' );
	}
} );

add_action( 'ga_activity_prune', function () {
	global $wpdb;
	$wpdb->query( $wpdb->prepare(
		'DELETE FROM ' . ga_activity_table() . ' WHERE created_at < %s',
		gmdate( 'Y-m-d H:i:s', time() - GA_ACTIVITY_RETENTION_DAYS * DAY_IN_SECONDS )
	) );
} );

/* ---------------------------------------------------------------------------
 * Página de perfil (author.php): só administradores
 * ------------------------------------------------------------------------- */

/**
 * Para os outros é 404. Roda antes do redirect_canonical, que transformaria
 * ?author=1 em /author/<login>/ e revelaria o nome de usuário.
 */
add_action( 'template_redirect', function () {
	if ( is_author() && ! current_user_can( 'list_users' ) ) {
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
	}
}, 1 );

add_filter( 'wp_robots', function ( $robots ) {
	if ( is_author() ) {
		$robots['noindex']  = true;
		$robots['nofollow'] = true;
	}
	return $robots;
} );

function ga_user_activity_url( int $user_id ): string {
	return get_author_posts_url( $user_id );
}

add_filter( 'user_row_actions', function ( $actions, $user ) {
	if ( current_user_can( 'list_users' ) ) {
		$actions['ga_activity'] = '<a href="' . esc_url( ga_user_activity_url( $user->ID ) ) . '">Atividade</a>';
	}
	return $actions;
}, 10, 2 );

/* ---------------------------------------------------------------------------
 * Apresentação
 * ------------------------------------------------------------------------- */

/** "Chrome no Windows" a partir do user agent. */
function ga_agent_label( string $agent ): string {
	$browser = match ( true ) {
		str_contains( $agent, 'Edg/' )    => 'Edge',
		str_contains( $agent, 'OPR/' )    => 'Opera',
		str_contains( $agent, 'Firefox' ) => 'Firefox',
		str_contains( $agent, 'Chrome' )  => 'Chrome',
		str_contains( $agent, 'Safari' )  => 'Safari',
		default                           => '',
	};
	$os = match ( true ) {
		str_contains( $agent, 'Android' )                                   => 'Android',
		str_contains( $agent, 'iPhone' ) || str_contains( $agent, 'iPad' ) => 'iOS',
		str_contains( $agent, 'Windows' )                                   => 'Windows',
		str_contains( $agent, 'Mac OS X' )                                  => 'macOS',
		str_contains( $agent, 'Linux' )                                     => 'Linux',
		default                                                             => '',
	};
	return trim( $browser . ( $browser && $os ? ' no ' : '' ) . $os );
}

/**
 * Ícone, texto (HTML) e tom de um evento. Tom: '' normal, 'muted' navegação, 'alert' atenção.
 */
function ga_activity_describe( object $row ): array {
	$d       = $row->detail;
	$title   = esc_html( $d['title'] ?? get_the_title( $row->object_id ) ?: 'item removido' );
	$link    = fn( string $text, string $url ) => $url ? '<a href="' . esc_url( $url ) . '">' . $text . '</a>' : $text;
	$tpl     = $link( '<strong>' . $title . '</strong>', $row->object_id ? (string) get_permalink( $row->object_id ) : '' );
	$by      = ! empty( $d['by'] ) ? ' <span class="text-ink-500">por ' . esc_html( $d['by'] ) . '</span>' : '';
	$methods = [
		'form'     => 'com e-mail e senha',
		'google'   => 'com o Google',
		'password' => 'com e-mail e senha',
		'register' => 'logo após criar a conta',
		'wp-login' => 'pelo wp-login.php',
	];
	$method  = isset( $methods[ $d['method'] ?? '' ] ) ? ' ' . $methods[ $d['method'] ] : '';

	return match ( $row->action ) {
		'register'               => [ 'sparkle', 'Criou a conta' . $method, '' ],
		'login'                  => [ 'lock', 'Entrou no site' . $method, '' ],
		'login_failed'           => [ 'shield', 'Errou a senha ao tentar entrar', 'alert' ],
		'logout'                 => [ 'logout', 'Saiu da conta', '' ],
		'password_reset_request' => [ 'mail', 'Pediu um link para redefinir a senha', 'alert' ],
		'password_reset'         => [ 'lock', 'Redefiniu a senha pelo link do e-mail', 'alert' ],
		'profile_update'         => [ 'user', 'Alterou ' . esc_html( wp_sprintf( '%l', (array) ( $d['fields'] ?? [] ) ) ) . ' no perfil' . $by, '' ],
		'view'                   => [ 'eye', 'Viu ' . $link( esc_html( $d['title'] ?? '' ) ?: esc_html( wp_parse_url( $row->url, PHP_URL_PATH ) ), $row->url ), 'muted' ],
		'favorite_add'           => [ 'heart', 'Favoritou o template ' . $tpl, '' ],
		'favorite_remove'        => [ 'heart', 'Tirou o template ' . $tpl . ' dos favoritos', '' ],
		'download'               => [ 'download', 'Baixou o template ' . $tpl . ( ! empty( $d['version'] ) ? ' v' . esc_html( $d['version'] ) : '' ), '' ],
		'request'                => [ 'wand', 'Pediu um template: ' . $link( '<strong>' . $title . '</strong>', (string) get_edit_post_link( $row->object_id ) ), '' ],
		'request_status'         => [ 'message', 'O pedido ' . $link( '<strong>' . $title . '</strong>', (string) get_edit_post_link( $row->object_id ) ) . ' mudou para <strong>' . esc_html( $d['status'] ?? '' ) . '</strong>' . $by, '' ],
		default                  => [ 'bolt', esc_html( $row->action ), '' ],
	};
}

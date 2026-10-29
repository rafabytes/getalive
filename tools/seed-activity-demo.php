<?php
/**
 * Cria um usuário fictício com histórico de atividade, para ver a página de
 * perfil (auditoria) preenchida. Rodar de novo recria do zero.
 *
 * Uso:  php tools/seed-activity-demo.php            cria (ou recria)
 *       php tools/seed-activity-demo.php --remove   apaga o usuário e o que ele gerou
 *
 * E-mail em example.com e IPs das faixas de documentação (RFC 5737): nada real.
 */

if ( PHP_SAPI !== 'cli' ) {
	exit( "Rode pela linha de comando.\n" );
}

// Carrega o WordPress (localmente o tema fica em wp-content/themes/<tema>/tools).
$_SERVER['HTTP_HOST']   = parse_url( getenv( 'WP_HOME' ) ?: 'http://getalive.local', PHP_URL_HOST );
$_SERVER['REQUEST_URI'] = '/';
// No servidor os scripts rodam da cópia do repositório (fora do WordPress): WP_PATH aponta para ele.
require ( getenv( 'WP_PATH' ) ?: dirname( __DIR__, 4 ) ) . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/user.php';

const DEMO_EMAIL = 'marina.demo@example.com';

$admins = get_users( [ 'role' => 'administrator', 'number' => 1 ] );
$admin  = $admins[0] ?? null;

/** Apaga o usuário de exemplo, desfazendo o que ele somou nos contadores. */
function ga_demo_remove(): void {
	$user = get_user_by( 'email', DEMO_EMAIL );
	if ( ! $user ) {
		return;
	}
	foreach ( array_keys( ga_user_downloads( $user->ID ) ) as $post_id ) {
		update_post_meta( $post_id, '_ga_downloads', max( 0, (int) get_post_meta( $post_id, '_ga_downloads', true ) - 1 ) );
	}
	foreach ( ga_user_requests( $user->ID ) as $request ) {
		wp_delete_post( $request->ID, true );
	}
	wp_delete_user( $user->ID );
	echo "Usuário de exemplo removido.\n";
}

ga_demo_remove();
if ( in_array( '--remove', $argv, true ) ) {
	exit;
}

$tpl = fn( string $slug ) => get_page_by_path( $slug, OBJECT, 'ga_template' );
foreach ( [ 'ratimbum', 'vaporwave', 'rococo', 'vice' ] as $slug ) {
	if ( ! $tpl( $slug ) ) {
		fwrite( STDERR, "Template '$slug' não encontrado; o exemplo usa Ratimbum, Vaporwave, Rococo e Vice.\n" );
		exit( 1 );
	}
}

/** Horário local: $days dias atrás, às $hm. */
$at = fn( int $days, string $hm ) => ( new DateTimeImmutable( "today -$days days $hm", wp_timezone() ) )->getTimestamp();

$windows = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36';
$iphone  = 'Mozilla/5.0 (iPhone; CPU iPhone OS 19_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/19.0 Mobile/15E148 Safari/604.1';
$home_ip = '203.0.113.45';
$mob_ip  = '198.51.100.23';

$registered = $at( 9, '19:44' );
$user_id    = wp_insert_user( [
	'user_login'      => 'marina.demo',
	'user_email'      => DEMO_EMAIL,
	'user_pass'       => wp_generate_password( 24 ),
	'display_name'    => 'Marina Teixeira',
	'nickname'        => 'Marina',
	'first_name'      => 'Marina',
	'last_name'       => 'Teixeira',
	'role'            => 'subscriber',
	'user_registered' => gmdate( 'Y-m-d H:i:s', $registered ),
] );
if ( is_wp_error( $user_id ) ) {
	fwrite( STDERR, $user_id->get_error_message() . "\n" );
	exit( 1 );
}

$log  = fn( int $time, string $action, array $args = [] ) => ga_log_activity( $user_id, $action, [ 'time' => $time ] + $args );
$view = function ( int $time, string $url, string $title ) use ( $log ) {
	$log( $time, 'view', [ 'url' => $url, 'object_id' => url_to_postid( $url ), 'detail' => [ 'title' => $title ] ] );
};
$favorite = function ( int $time, WP_Post $p, bool $on ) use ( $log, $user_id ) {
	$favs = array_diff( ga_user_favorites( $user_id ), [ $p->ID ] );
	update_user_meta( $user_id, 'ga_favorites', array_values( $on ? array_merge( [ $p->ID ], $favs ) : $favs ) );
	$log( $time, $on ? 'favorite_add' : 'favorite_remove', [ 'object_id' => $p->ID, 'detail' => [ 'title' => $p->post_title ] ] );
};
$download = function ( int $time, WP_Post $p, string $ip ) use ( $log, $user_id ) {
	update_post_meta( $p->ID, '_ga_downloads', (int) get_post_meta( $p->ID, '_ga_downloads', true ) + 1 );
	$history           = (array) get_user_meta( $user_id, 'ga_downloads', true );
	$history[ $p->ID ] = $time;
	arsort( $history );
	update_user_meta( $user_id, 'ga_downloads', array_filter( $history ) );
	$log( $time, 'download', [ 'object_id' => $p->ID, 'ip' => $ip, 'detail' => [ 'title' => $p->post_title, 'version' => (string) get_post_meta( $p->ID, '_ga_version', true ) ] ] );
};

// Dia 1: cadastro pelo computador, explora e baixa o primeiro template.
$log( $registered, 'register', [ 'ip' => $home_ip, 'detail' => [ 'method' => 'form' ] ] );
$log( $registered, 'login', [ 'ip' => $home_ip, 'detail' => [ 'method' => 'register', 'agent' => $windows ] ] );
$view( $at( 9, '19:45' ), ga_page_url( 'templates' ), 'Templates' );
$view( $at( 9, '19:46' ), get_permalink( $tpl( 'vaporwave' ) ), 'Vaporwave' );
$favorite( $at( 9, '19:47' ), $tpl( 'vaporwave' ), true );
$view( $at( 9, '19:49' ), get_permalink( $tpl( 'ratimbum' ) ), 'Ratimbum' );
$download( $at( 9, '19:50' ), $tpl( 'ratimbum' ), $home_ip );
$view( $at( 9, '19:51' ), ga_page_url( 'install' ), 'Como instalar no OBS' );
$log( $at( 9, '20:15' ), 'logout', [ 'ip' => $home_ip ] );

// Dia 2: pelo celular, esquece a senha, redefine e pede um template.
$log( $at( 6, '21:03' ), 'login_failed', [ 'ip' => $mob_ip, 'detail' => [ 'agent' => $iphone ] ] );
$log( $at( 6, '21:04' ), 'password_reset_request', [ 'ip' => $mob_ip ] );
$log( $at( 6, '21:07' ), 'password_reset', [ 'ip' => $mob_ip ] );
$log( $at( 6, '21:08' ), 'login', [ 'ip' => $mob_ip, 'detail' => [ 'method' => 'password', 'agent' => $iphone ] ] );
$view( $at( 6, '21:09' ), ga_page_url( 'templates' ), 'Templates' );
$view( $at( 6, '21:10' ), get_permalink( $tpl( 'rococo' ) ), 'Rococo' );
$favorite( $at( 6, '21:11' ), $tpl( 'rococo' ), true );
$favorite( $at( 6, '21:12' ), $tpl( 'vaporwave' ), false );
$view( $at( 6, '21:15' ), ga_page_url( 'request' ), 'Solicitar um template' );

// Direto no banco, sem ga_create_request(): ela mandaria e-mail para a equipe.
$request_id = wp_insert_post( [
	'post_type'     => 'ga_request',
	'post_status'   => 'publish',
	'post_author'   => $user_id,
	'post_title'    => 'Terror pixelado',
	'post_content'  => 'Uma casa abandonada em pixel art, com luz piscando e chat numa tela de TV velha. Para lives de jogos de terror.',
	'post_date'     => wp_date( 'Y-m-d H:i:s', $at( 6, '21:21' ) ),
	'post_date_gmt' => gmdate( 'Y-m-d H:i:s', $at( 6, '21:21' ) ),
] );
update_post_meta( $request_id, '_ga_vibes', [ 'Retrô / pixel art', 'Terror' ] );
update_post_meta( $request_id, '_ga_scenes', [ 'Começando', 'Jogo', 'Já volto', 'Encerrando' ] );
update_post_meta( $request_id, '_ga_platforms', [ 'Twitch' ] );
update_post_meta( $request_id, '_ga_references', [] );
$log( $at( 6, '21:21' ), 'request', [ 'object_id' => $request_id, 'detail' => [ 'title' => 'Terror pixelado' ] ] );
$view( $at( 6, '21:22' ), add_query_arg( 'aba', 'pedidos', ga_page_url( 'account' ) ), 'Minha conta' );

// Dia 8: a equipe muda o status do pedido.
update_post_meta( $request_id, '_ga_status', 'reviewing' );
$log( $at( 2, '10:30' ), 'request_status', [ 'object_id' => $request_id, 'detail' => [ 'title' => 'Terror pixelado', 'status' => ga_request_statuses()['reviewing'], 'by' => $admin ? $admin->display_name : 'Equipe' ] ] );

// Hoje: volta pelo computador, baixa outro template e ajusta o nome.
$now = time();
$log( $now - 50 * MINUTE_IN_SECONDS, 'login', [ 'ip' => $home_ip, 'detail' => [ 'method' => 'password', 'agent' => $windows ] ] );
$view( $now - 49 * MINUTE_IN_SECONDS, home_url( '/' ), 'Início' );
$view( $now - 47 * MINUTE_IN_SECONDS, get_permalink( $tpl( 'vice' ) ), 'Vice' );
$download( $now - 46 * MINUTE_IN_SECONDS, $tpl( 'vice' ), $home_ip );
$view( $now - 40 * MINUTE_IN_SECONDS, ga_page_url( 'account' ), 'Minha conta' );
$log( $now - 39 * MINUTE_IN_SECONDS, 'profile_update', [ 'ip' => $home_ip, 'detail' => [ 'fields' => [ 'nome' ] ] ] );

echo 'Usuário de exemplo criado: Marina Teixeira (' . DEMO_EMAIL . ")\n";
echo get_author_posts_url( $user_id ) . "\n";

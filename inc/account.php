<?php
/**
 * Minha conta: abas e dados de apoio.
 */

defined( 'ABSPATH' ) || exit;

function ga_account_tabs(): array {
	return [
		'favoritos'    => [ 'Favoritos', 'heart' ],
		'downloads'    => [ 'Downloads', 'download' ],
		'solicitacoes' => [ 'Solicitações', 'wand' ],
		'perfil'       => [ 'Perfil', 'user' ],
		'privacidade'  => [ 'Privacidade', 'shield' ],
	];
}

function ga_account_tab(): string {
	$tab = sanitize_key( $_GET['aba'] ?? '' );
	return isset( ga_account_tabs()[ $tab ] ) ? $tab : 'favoritos';
}

function ga_account_tab_url( string $tab ): string {
	return add_query_arg( 'aba', $tab, ga_page_url( 'account' ) );
}

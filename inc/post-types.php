<?php
/**
 * Tipos de conteúdo: templates de live, estilos e solicitações.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	register_post_type( 'ga_template', [
		'labels'        => [
			'name'               => 'Templates',
			'singular_name'      => 'Template',
			'add_new'            => 'Adicionar template',
			'add_new_item'       => 'Adicionar novo template',
			'edit_item'          => 'Editar template',
			'new_item'           => 'Novo template',
			'view_item'          => 'Ver template',
			'search_items'       => 'Buscar templates',
			'not_found'          => 'Nenhum template encontrado',
			'not_found_in_trash' => 'Nenhum template na lixeira',
			'all_items'          => 'Todos os templates',
			'menu_name'          => 'Templates',
		],
		'public'        => true,
		'has_archive'   => 'templates',
		'rewrite'       => [ 'slug' => 'templates', 'with_front' => false ],
		'menu_icon'     => 'dashicons-video-alt2',
		'menu_position' => 5,
		'supports'      => [ 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ],
		'show_in_rest'  => true,
	] );

	register_taxonomy( 'ga_style', 'ga_template', [
		'labels'            => [
			'name'          => 'Estilos',
			'singular_name' => 'Estilo',
			'add_new_item'  => 'Adicionar estilo',
			'edit_item'     => 'Editar estilo',
			'search_items'  => 'Buscar estilos',
			'all_items'     => 'Todos os estilos',
			'menu_name'     => 'Estilos',
		],
		'hierarchical'      => true,
		'public'            => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => [ 'slug' => 'estilo', 'with_front' => false ],
	] );

	register_post_type( 'ga_request', [
		'labels'          => [
			'name'          => 'Solicitações',
			'singular_name' => 'Solicitação',
			'edit_item'     => 'Ver solicitação',
			'all_items'     => 'Solicitações',
			'menu_name'     => 'Solicitações',
			'not_found'     => 'Nenhuma solicitação ainda',
		],
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => 'edit.php?post_type=ga_template',
		'supports'        => [ 'title', 'editor', 'author' ],
		'capability_type' => 'post',
		'capabilities'    => [ 'create_posts' => 'do_not_allow' ],
		'map_meta_cap'    => true,
	] );
} );

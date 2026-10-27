<?php
/**
 * get a live: bootstrap do tema.
 */

defined( 'ABSPATH' ) || exit;

define( 'GA_VERSION', '1.0.0' );
define( 'GA_DIR', get_template_directory() );
define( 'GA_URI', get_template_directory_uri() );

foreach ( [
	'helpers',
	'icons',
	'setup',
	'assets',
	'post-types',
	'template-data',
	'admin-meta',
	'requests',
	'downloads',
	'favorites',
	'auth',
	'google',
	'rest',
	'account',
	'consent',
	'settings',
	'premium',
	'install',
] as $ga_file ) {
	require GA_DIR . "/inc/{$ga_file}.php";
}

<?php
/**
 * get a live: bootstrap do tema.
 */

defined( 'ABSPATH' ) || exit;

define( 'GA_VERSION', '1.0.0' );
define( 'GA_DIR', get_template_directory() );
define( 'GA_URI', get_template_directory_uri() );

foreach ( [
	'i18n',
	'helpers',
	'icons',
	'setup',
	'assets',
	'post-types',
	'template-data',
	'template-order',
	'admin-meta',
	'requests',
	'downloads',
	'favorites',
	'auth',
	'google',
	'rest',
	'account',
	'activity',
	'consent',
	'settings',
	'premium',
	'install',
	'home',
	'seo',
] as $ga_file ) {
	require GA_DIR . "/inc/{$ga_file}.php";
}

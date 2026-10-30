<?php
/**
 * Ícones SVG inline (traço no estilo Lucide; marcas no estilo Simple Icons).
 */

defined( 'ABSPATH' ) || exit;

function ga_icon_paths(): array {
	return [
		'arrow-right'    => '<path d="M5 12h14"/><path d="m13 6 6 6-6 6"/>',
		'arrow-up-right' => '<path d="M7 17 17 7"/><path d="M8 7h9v9"/>',
		'arrow-left'     => '<path d="M19 12H5"/><path d="m11 18-6-6 6-6"/>',
		'download'       => '<path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M5 21h14"/>',
		'heart'          => '<path d="M19 14c1.5-1.5 3-3.2 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.8 0-3 .5-4.5 2-1.5-1.5-2.7-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4 3 5.5l7 7Z"/>',
		'search'         => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
		'close'          => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
		'check'          => '<path d="M20 6 9 17l-5-5"/>',
		'plus'           => '<path d="M12 5v14"/><path d="M5 12h14"/>',
		'menu'           => '<path d="M4 8h16"/><path d="M4 16h16"/>',
		'user'           => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
		'logout'         => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/>',
		'lock'           => '<rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
		'mail'           => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
		'monitor'        => '<rect x="2" y="4" width="20" height="13" rx="2"/><path d="M8 21h8"/><path d="M12 17v4"/>',
		'layers'         => '<path d="m12 2 10 5-10 5L2 7l10-5Z"/><path d="m2 12 10 5 10-5"/><path d="m2 17 10 5 10-5"/>',
		'bolt'           => '<path d="M13 2 4 14h7l-1 8 9-12h-7l1-8Z"/>',
		'sparkle'        => '<path d="M12 3v4M12 17v4M3 12h4M17 12h4M6 6l2.5 2.5M15.5 15.5 18 18M6 18l2.5-2.5M15.5 8.5 18 6"/>',
		'play'           => '<path d="M7 4v16l13-8L7 4Z"/>',
		'clock'          => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
		'file'           => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8l-5-5Z"/><path d="M14 3v5h5"/>',
		'chevron-down'   => '<path d="m6 9 6 6 6-6"/>',
		'globe'          => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3a14 14 0 0 1 0 18 14 14 0 0 1 0-18Z"/>',
		'expand'         => '<path d="M15 3h6v6"/><path d="M9 21H3v-6"/><path d="M21 3l-7 7"/><path d="M3 21l7-7"/>',
		'message'        => '<path d="M21 12a8 8 0 0 1-11.6 7.1L3 21l1.9-6.4A8 8 0 1 1 21 12Z"/>',
		'wand'           => '<path d="m15 4 5 5L8 21l-5-5L15 4Z"/><path d="m12 7 5 5"/><path d="M5 3v4M3 5h4M19 15v4M17 17h4"/>',
		'shield'         => '<path d="M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6l-8-3Z"/><path d="m9 12 2 2 4-4"/>',
		'cookie'         => '<path d="M21 12a9 9 0 1 1-9-9 3 3 0 0 0 3 3 3 3 0 0 0 3 3 3 3 0 0 0 3 3Z"/><circle cx="8.5" cy="10.5" r="1"/><circle cx="14" cy="15" r="1"/><circle cx="9" cy="16" r="1"/>',
		'trash'          => '<path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M6 6l1 15h10l1-15"/>',
		'eye'            => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>',
		'eye-off'        => '<path d="M10.6 5.1A10 10 0 0 1 12 5c6.5 0 10 7 10 7a17 17 0 0 1-2.2 3.2M6.6 6.6C3.8 8.4 2 12 2 12s3.5 7 10 7c1.9 0 3.6-.6 5-1.5"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/><path d="m2 2 20 20"/>',
	];
}

function ga_brand_paths(): array {
	return [
		'google'    => '<path fill="#4285F4" d="M23.5 12.3c0-.8-.1-1.6-.2-2.3H12v4.5h6.5a5.6 5.6 0 0 1-2.4 3.6v3h3.9c2.2-2.1 3.5-5.1 3.5-8.8Z"/><path fill="#34A853" d="M12 24c3.2 0 6-1.1 8-2.9l-3.9-3c-1.1.7-2.5 1.2-4.1 1.2-3.1 0-5.8-2.1-6.7-5H1.3v3.1A12 12 0 0 0 12 24Z"/><path fill="#FBBC05" d="M5.3 14.3a7.2 7.2 0 0 1 0-4.6V6.6H1.3a12 12 0 0 0 0 10.8l4-3.1Z"/><path fill="#EA4335" d="M12 4.8c1.8 0 3.3.6 4.6 1.8l3.4-3.4A12 12 0 0 0 1.3 6.6l4 3.1c.9-2.9 3.6-4.9 6.7-4.9Z"/>',
		'twitch'    => '<path fill="currentColor" d="M11.6 11.4h1.7V6.2h-1.7v5.2Zm4.7 0H18V6.2h-1.7v5.2ZM7 0 2.7 4.3v15.4h5.1V24l4.3-4.3h3.5L23.4 12V0H7Zm14.6 11.1-3.4 3.4h-3.5l-3 3v-3H7.8V1.7h13.8v9.4Z"/>',
		'youtube'   => '<path fill="currentColor" d="M23.5 6.2a3 3 0 0 0-2.1-2.1C19.5 3.6 12 3.6 12 3.6s-7.5 0-9.4.5A3 3 0 0 0 .5 6.2 31 31 0 0 0 0 12a31 31 0 0 0 .5 5.8 3 3 0 0 0 2.1 2.1c1.9.5 9.4.5 9.4.5s7.5 0 9.4-.5a3 3 0 0 0 2.1-2.1A31 31 0 0 0 24 12a31 31 0 0 0-.5-5.8ZM9.6 15.6V8.4l6.2 3.6-6.2 3.6Z"/>',
		'kick'      => '<path fill="currentColor" d="M1.3 1.5h7.2v4.8h2.4V3.9h2.4V1.5h7.2v7.2h-2.4v2.4h-2.4v1.8h2.4v2.4h2.4v7.2h-7.2v-2.4h-2.4v-2.4H8.5v4.8H1.3V1.5Z"/>',
		'tiktok'    => '<path fill="currentColor" d="M12.5 0h4c.3 2.1 1.6 3.9 3.9 4.3v4c-1.5 0-2.9-.4-4-1.1v8.1a7.3 7.3 0 1 1-6.3-7.2v4.1a3.2 3.2 0 1 0 2.4 3.1V0Z"/>',
		'instagram' => '<path fill="currentColor" d="M12 2.2c3.2 0 3.6 0 4.8.1 3.3.1 4.8 1.7 4.9 4.9.1 1.3.1 1.6.1 4.8s0 3.6-.1 4.8c-.1 3.2-1.7 4.8-4.9 4.9-1.3.1-1.6.1-4.8.1s-3.6 0-4.8-.1c-3.3-.1-4.8-1.7-4.9-4.9C2.2 15.6 2.2 15.2 2.2 12s0-3.6.1-4.8C2.4 3.9 3.9 2.4 7.2 2.3 8.4 2.2 8.8 2.2 12 2.2ZM12 0C8.7 0 8.3 0 7.1.1 2.7.3.3 2.7.1 7.1 0 8.3 0 8.7 0 12s0 3.7.1 4.9c.2 4.4 2.6 6.8 7 7 1.2.1 1.6.1 4.9.1s3.7 0 4.9-.1c4.4-.2 6.8-2.6 7-7 .1-1.2.1-1.6.1-4.9s0-3.7-.1-4.9c-.2-4.4-2.6-6.8-7-7C15.7 0 15.3 0 12 0Zm0 5.8a6.2 6.2 0 1 0 0 12.4 6.2 6.2 0 0 0 0-12.4ZM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8Zm6.4-11.8a1.4 1.4 0 1 0 0 2.9 1.4 1.4 0 0 0 0-2.9Z"/>',
		'x'         => '<path fill="currentColor" d="M18.9 1.2h3.7l-8 9.2L24 22.8h-7.4l-5.8-7.6-6.6 7.6H.5L9 13 0 1.2h7.6l5.2 6.9 6.1-6.9Zm-1.3 19.4h2L6.5 3.2H4.3l13.3 17.4Z"/>',
		'discord'   => '<path fill="currentColor" d="M20.3 4.4A19.8 19.8 0 0 0 15.4 3l-.6 1.3a18.4 18.4 0 0 0-5.5 0L8.6 3a19.7 19.7 0 0 0-4.9 1.5C.6 9.1-.3 13.6.1 18.1a19.9 19.9 0 0 0 6 3l1.3-2a13 13 0 0 1-2-1l.5-.4a14.2 14.2 0 0 0 12.2 0l.5.4-2 1 1.3 2a19.8 19.8 0 0 0 6-3c.5-5.2-.9-9.7-3.6-13.7ZM8 15.4c-1.2 0-2.2-1.1-2.2-2.4S6.8 10.6 8 10.6s2.2 1.1 2.2 2.4-1 2.4-2.2 2.4Zm8 0c-1.2 0-2.2-1.1-2.2-2.4s1-2.4 2.2-2.4 2.2 1.1 2.2 2.4-1 2.4-2.2 2.4Z"/>',
		'obs'       => '<path fill="currentColor" d="M12 0a12 12 0 1 0 0 24 12 12 0 0 0 0-24Zm0 1.6a10.4 10.4 0 0 1 9.6 14.4 5.5 5.5 0 0 0-6.4-7.4 5.5 5.5 0 0 0-8.6-4.9A10.3 10.3 0 0 1 12 1.6Zm-6.6 4a4 4 0 1 1 3.5 6 5.5 5.5 0 0 0-5 6.9A10.4 10.4 0 0 1 5.4 5.6Zm11.2 3.9a4 4 0 1 1-2 7.4 5.5 5.5 0 0 0-1.6-8 4 4 0 0 1 3.6.6ZM8.8 13a4 4 0 1 1-.1 8 4 4 0 0 1 .1-8Z"/>',
	];
}

/**
 * Retorna o SVG de um ícone. Marcas usam preenchimento, os demais usam traço.
 */
function ga_icon( string $name, string $class = 'size-5' ): string {
	$brands = ga_brand_paths();
	if ( isset( $brands[ $name ] ) ) {
		return '<svg class="' . esc_attr( $class ) . '" viewBox="0 0 24 24" aria-hidden="true" focusable="false">' . $brands[ $name ] . '</svg>';
	}
	$icons = ga_icon_paths();
	if ( ! isset( $icons[ $name ] ) ) {
		return '';
	}
	return '<svg class="' . esc_attr( $class ) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $icons[ $name ] . '</svg>';
}

function ga_platform_label( string $slug ): string {
	return [
		'twitch'  => 'Twitch',
		'youtube' => 'YouTube',
		'kick'    => 'Kick',
		'tiktok'  => 'TikTok',
	][ $slug ] ?? ucfirst( $slug );
}

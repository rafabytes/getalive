<?php
/**
 * Configurações → get a live.
 */

defined( 'ABSPATH' ) || exit;

function ga_settings_fields(): array {
	return [
		'google_client_id'  => [ 'Google Client ID', 'text', 'ID do cliente OAuth (tipo "Aplicativo da Web") criado no Google Cloud Console. Em "Origens JavaScript autorizadas", adicione ' . home_url() . '. Vazio = botão do Google escondido.' ],
		'contact_email'     => [ 'E-mail de contato', 'email', 'Aparece na Política de Privacidade como canal do titular de dados.' ],
		'request_email'     => [ 'E-mail das solicitações', 'email', 'Recebe um aviso a cada nova solicitação de template. Vazio = e-mail de contato.' ],
		'social_instagram'  => [ 'Instagram', 'url', '' ],
		'social_x'          => [ 'X (Twitter)', 'url', '' ],
		'social_youtube'    => [ 'YouTube', 'url', '' ],
		'social_twitch'     => [ 'Twitch', 'url', '' ],
		'social_discord'    => [ 'Discord', 'url', '' ],
		'social_tiktok'     => [ 'TikTok', 'url', '' ],
		'scripts_analytics' => [ 'Scripts de estatística', 'code', 'Ex.: Google Analytics. Só rodam depois que o visitante aceitar "Estatísticas".' ],
		'scripts_marketing' => [ 'Scripts de marketing', 'code', 'Ex.: Meta Pixel. Só rodam depois que o visitante aceitar "Marketing".' ],
		'premium_enabled'   => [ 'Venda de templates', 'checkbox', 'Publica os templates marcados como pagos e libera a compra pelo WooCommerce. Exige o WooCommerce ativo.' ],
	];
}

add_action( 'admin_menu', function () {
	add_options_page( 'get a live', 'get a live', 'manage_options', 'getalive', 'ga_settings_page' );
} );

add_action( 'admin_init', function () {
	register_setting( 'getalive', 'ga_settings', [
		'type'              => 'array',
		'sanitize_callback' => 'ga_sanitize_settings',
		'default'           => [],
	] );
} );

function ga_sanitize_settings( $input ): array {
	$out = [];
	foreach ( ga_settings_fields() as $key => [ , $type ] ) {
		$value = $input[ $key ] ?? '';
		switch ( $type ) {
			case 'email':
				$out[ $key ] = sanitize_email( $value );
				break;
			case 'url':
				$out[ $key ] = esc_url_raw( $value );
				break;
			case 'checkbox':
				$out[ $key ] = empty( $value ) ? '' : '1';
				break;
			case 'code':
				// Só administradores com unfiltered_html podem gravar scripts.
				$out[ $key ] = current_user_can( 'unfiltered_html' ) ? trim( (string) $value ) : ga_option( $key );
				break;
			default:
				$out[ $key ] = sanitize_text_field( $value );
		}
	}
	return $out;
}

function ga_settings_page(): void {
	$wc = class_exists( 'WooCommerce' );
	?>
	<div class="wrap">
		<h1>get a live</h1>
		<form method="post" action="options.php">
			<?php settings_fields( 'getalive' ); ?>
			<table class="form-table" role="presentation">
				<?php foreach ( ga_settings_fields() as $key => [ $label, $type, $help ] ) : ?>
					<?php $value = ga_option( $key ); $name = "ga_settings[{$key}]"; ?>
					<tr>
						<th scope="row"><label for="ga-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
						<td>
							<?php if ( 'code' === $type ) : ?>
								<textarea id="ga-<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $name ); ?>" rows="5" class="large-text code"><?php echo esc_textarea( $value ); ?></textarea>
							<?php elseif ( 'checkbox' === $type ) : ?>
								<label><input type="checkbox" id="ga-<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $name ); ?>" value="1" <?php checked( $value, '1' ); ?> <?php disabled( ! $wc && 'premium_enabled' === $key ); ?>> Ativar</label>
								<?php if ( 'premium_enabled' === $key && ! $wc ) : ?>
									<input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>">
									<br><em>WooCommerce não está ativo.</em>
								<?php endif; ?>
							<?php else : ?>
								<input type="<?php echo esc_attr( $type ); ?>" id="ga-<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" class="regular-text">
							<?php endif; ?>
							<?php if ( $help ) : ?><p class="description"><?php echo esc_html( $help ); ?></p><?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

function ga_socials(): array {
	$out = [];
	foreach ( [ 'instagram', 'x', 'youtube', 'twitch', 'discord', 'tiktok' ] as $network ) {
		$url = ga_option( "social_{$network}" );
		if ( $url ) {
			$out[ $network ] = $url;
		}
	}
	return $out;
}

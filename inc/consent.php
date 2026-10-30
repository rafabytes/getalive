<?php
/**
 * Consentimento de cookies (LGPD): banner, preferências e scripts bloqueados até o aceite.
 *
 * Scripts opcionais são impressos com type="text/plain" e data-consent="analytics|marketing";
 * o JS só os ativa depois do aceite da categoria.
 */

defined( 'ABSPATH' ) || exit;

const GA_CONSENT_VERSION = 1;

function ga_consent_categories(): array {
	return [
		'necessary' => [
			'label'    => __( 'Necessários', 'getalive' ),
			'desc'     => __( 'Mantêm você logado, guardam suas escolhas de privacidade e protegem os formulários. Não dá para desligar.', 'getalive' ),
			'required' => true,
		],
		'analytics' => [
			'label'    => __( 'Estatísticas', 'getalive' ),
			'desc'     => __( 'Contam visitas e cliques de forma agregada para sabermos quais templates fazer a seguir.', 'getalive' ),
			'required' => false,
		],
		'marketing' => [
			'label'    => __( 'Marketing', 'getalive' ),
			'desc'     => __( 'Permitem medir campanhas e mostrar novidades do get a live em outros sites.', 'getalive' ),
			'required' => false,
		],
	];
}

/**
 * Scripts de terceiros cadastrados em Configurações, bloqueados até o consentimento.
 */
add_action( 'wp_head', function () {
	foreach ( [ 'analytics', 'marketing' ] as $cat ) {
		$code = (string) ga_option( "scripts_{$cat}" );
		if ( '' === trim( $code ) ) {
			continue;
		}
		echo preg_replace(
			'/<script\b(?![^>]*\btype=)([^>]*)>/i',
			'<script type="text/plain" data-consent="' . $cat . '"$1>',
			$code
		) . "\n";
	}
}, 99 );

function ga_consent_banner(): void {
	$privacy = ga_page_url( 'privacy' );
	?>
	<div class="consent" data-consent-banner data-version="<?php echo esc_attr( GA_CONSENT_VERSION ); ?>" hidden>
		<div class="consent__card" role="dialog" aria-modal="false" aria-labelledby="consent-title" aria-describedby="consent-desc">
			<div class="consent__head">
				<span class="consent__icon"><?php echo ga_icon( 'cookie', 'size-5' ); ?></span>
				<p id="consent-title" class="consent__title"><?php esc_html_e( 'Sua privacidade, suas regras', 'getalive' ); ?></p>
			</div>
			<p id="consent-desc" class="consent__text">
				<?php esc_html_e( 'Usamos cookies necessários para o site funcionar e, com a sua permissão, cookies de estatística e marketing.', 'getalive' ); ?>
				<?php esc_html_e( 'Você pode mudar isso quando quiser.', 'getalive' ); ?> <a href="<?php echo esc_url( $privacy ); ?>#cookies"><?php esc_html_e( 'Saiba mais', 'getalive' ); ?></a><span class="sm:hidden"> <?php esc_html_e( 'ou', 'getalive' ); ?> <button type="button" data-consent-open><?php esc_html_e( 'personalize', 'getalive' ); ?></button></span>.
			</p>

			<form class="consent__prefs" data-consent-prefs hidden>
				<?php foreach ( ga_consent_categories() as $key => $cat ) : ?>
					<label class="consent__row">
						<span>
							<strong><?php echo esc_html( $cat['label'] ); ?></strong>
							<small><?php echo esc_html( $cat['desc'] ); ?></small>
						</span>
						<span class="switch">
							<input type="checkbox" name="<?php echo esc_attr( $key ); ?>" <?php echo $cat['required'] ? 'checked disabled' : ''; ?>>
							<span class="switch__ui" aria-hidden="true"></span>
						</span>
					</label>
				<?php endforeach; ?>
			</form>

			<div class="consent__actions">
				<button type="button" class="btn btn--ghost btn--sm" data-consent-action="customize"><?php esc_html_e( 'Personalizar', 'getalive' ); ?></button>
				<button type="button" class="btn btn--ghost btn--sm" data-consent-action="save" hidden><?php esc_html_e( 'Salvar escolhas', 'getalive' ); ?></button>
				<button type="button" class="btn btn--outline btn--sm" data-consent-action="reject"><?php esc_html_e( 'Só necessários', 'getalive' ); ?></button>
				<button type="button" class="btn btn--primary btn--sm" data-consent-action="accept"><?php esc_html_e( 'Aceitar todos', 'getalive' ); ?></button>
			</div>
		</div>
	</div>
	<?php
}

/* ---------------------------------------------------------------------------
 * Shortcodes usados nas páginas institucionais
 * ------------------------------------------------------------------------- */

add_shortcode( 'ga_site_name', fn() => esc_html( get_bloginfo( 'name' ) ) );
add_shortcode( 'ga_contact_email', function () {
	$email = antispambot( ga_contact_email() );
	return '<a href="mailto:' . $email . '">' . $email . '</a>';
} );
add_shortcode( 'ga_cookie_preferences', function () {
	return '<button type="button" class="btn btn--outline btn--sm" data-consent-open>' . ga_icon( 'cookie', 'size-4' ) . '<span>' . esc_html__( 'Gerenciar cookies', 'getalive' ) . '</span></button>';
} );
add_shortcode( 'ga_policy_updated', fn() => esc_html( get_the_modified_date( ga_date_format() ) ) );
add_shortcode( 'ga_cookie_table', function () {
	$rows = [
		[ 'wordpress_logged_in_*, wordpress_sec_*', __( 'Necessário', 'getalive' ), __( 'Mantém sua sessão de login', 'getalive' ), __( 'Até 14 dias', 'getalive' ) ],
		[ 'wordpress_test_cookie', __( 'Necessário', 'getalive' ), __( 'Verifica se o navegador aceita cookies', 'getalive' ), __( 'Sessão', 'getalive' ) ],
		[ 'ga_consent', __( 'Necessário', 'getalive' ), __( 'Guarda suas escolhas de cookies', 'getalive' ), __( '12 meses', 'getalive' ) ],
		[ __( 'Cookies do Google (accounts.google.com)', 'getalive' ), __( 'Necessário, só se você usar "Entrar com Google"', 'getalive' ), __( 'Autenticação pela sua conta Google', 'getalive' ), __( 'Definido pelo Google', 'getalive' ) ],
		[ __( 'Ferramentas de estatística', 'getalive' ), __( 'Estatísticas', 'getalive' ), __( 'Medição de audiência agregada, só após o seu aceite', 'getalive' ), __( 'Até 13 meses', 'getalive' ) ],
		[ __( 'Ferramentas de marketing', 'getalive' ), __( 'Marketing', 'getalive' ), __( 'Medição de campanhas, só após o seu aceite', 'getalive' ), __( 'Até 13 meses', 'getalive' ) ],
	];
	$html = '<div class="table-wrap"><table><thead><tr><th>' . esc_html__( 'Cookie', 'getalive' ) . '</th><th>' . esc_html__( 'Categoria', 'getalive' ) . '</th><th>' . esc_html__( 'Para quê', 'getalive' ) . '</th><th>' . esc_html__( 'Duração', 'getalive' ) . '</th></tr></thead><tbody>';
	foreach ( $rows as $row ) {
		$html .= '<tr><td><code>' . esc_html( $row[0] ) . '</code></td><td>' . esc_html( $row[1] ) . '</td><td>' . esc_html( $row[2] ) . '</td><td>' . esc_html( $row[3] ) . '</td></tr>';
	}
	return $html . '</tbody></table></div>';
} );

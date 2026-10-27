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
			'label'    => 'Necessários',
			'desc'     => 'Mantêm você logado, guardam suas escolhas de privacidade e protegem os formulários. Não dá para desligar.',
			'required' => true,
		],
		'analytics' => [
			'label'    => 'Estatísticas',
			'desc'     => 'Contam visitas e cliques de forma agregada para sabermos quais templates fazer a seguir.',
			'required' => false,
		],
		'marketing' => [
			'label'    => 'Marketing',
			'desc'     => 'Permitem medir campanhas e mostrar novidades do get a live em outros sites.',
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
				<p id="consent-title" class="consent__title">Sua privacidade, suas regras</p>
			</div>
			<p id="consent-desc" class="consent__text">
				Usamos cookies necessários para o site funcionar e, com a sua permissão, cookies de estatística e marketing.
				Você pode mudar isso quando quiser. <a href="<?php echo esc_url( $privacy ); ?>#cookies">Saiba mais</a>.
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
				<button type="button" class="btn btn--ghost btn--sm" data-consent-action="customize">Personalizar</button>
				<button type="button" class="btn btn--ghost btn--sm" data-consent-action="save" hidden>Salvar escolhas</button>
				<button type="button" class="btn btn--outline btn--sm" data-consent-action="reject">Só necessários</button>
				<button type="button" class="btn btn--primary btn--sm" data-consent-action="accept">Aceitar todos</button>
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
	return '<button type="button" class="btn btn--outline btn--sm" data-consent-open>' . ga_icon( 'cookie', 'size-4' ) . '<span>Gerenciar cookies</span></button>';
} );
add_shortcode( 'ga_policy_updated', fn() => esc_html( get_the_modified_date( 'j \d\e F \d\e Y' ) ) );
add_shortcode( 'ga_cookie_table', function () {
	$rows = [
		[ 'wordpress_logged_in_*, wordpress_sec_*', 'Necessário', 'Mantém sua sessão de login', 'Até 14 dias' ],
		[ 'wordpress_test_cookie', 'Necessário', 'Verifica se o navegador aceita cookies', 'Sessão' ],
		[ 'ga_consent', 'Necessário', 'Guarda suas escolhas de cookies', '12 meses' ],
		[ 'Cookies do Google (accounts.google.com)', 'Necessário, só se você usar "Entrar com Google"', 'Autenticação pela sua conta Google', 'Definido pelo Google' ],
		[ 'Ferramentas de estatística', 'Estatísticas', 'Medição de audiência agregada, só após o seu aceite', 'Até 13 meses' ],
		[ 'Ferramentas de marketing', 'Marketing', 'Medição de campanhas, só após o seu aceite', 'Até 13 meses' ],
	];
	$html = '<div class="table-wrap"><table><thead><tr><th>Cookie</th><th>Categoria</th><th>Para quê</th><th>Duração</th></tr></thead><tbody>';
	foreach ( $rows as $row ) {
		$html .= '<tr><td><code>' . esc_html( $row[0] ) . '</code></td><td>' . esc_html( $row[1] ) . '</td><td>' . esc_html( $row[2] ) . '</td><td>' . esc_html( $row[3] ) . '</td></tr>';
	}
	return $html . '</tbody></table></div>';
} );

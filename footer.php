<?php
/**
 * Rodapé, aviso de cookies e cortina das transições.
 */

defined( 'ABSPATH' ) || exit;
$ga_socials = ga_socials();
?>
	</div>
</main>

<footer class="site-footer">
	<div class="wrap">
		<div class="grid gap-12 pb-16 md:grid-cols-12">
			<div class="md:col-span-5">
				<p class="eyebrow eyebrow--dot mb-5">No ar desde <?php echo esc_html( gmdate( 'Y' ) ); ?></p>
				<p class="max-w-md font-display text-3xl font-medium leading-tight tracking-tight text-white md:text-4xl" style="font-variation-settings:'wdth' 90">
					Layouts de live para o OBS que instalam em dois cliques.
				</p>
				<a href="<?php echo esc_url( ga_page_url( 'templates' ) ); ?>" class="btn btn--primary mt-8"><span>Ver templates</span><?php echo ga_icon( 'arrow-right', 'btn__arrow' ); ?></a>
			</div>

			<div class="grid grid-cols-2 gap-8 md:col-span-6 md:col-start-7 md:grid-cols-3">
				<div>
					<p class="eyebrow mb-4">Navegue</p>
					<ul class="space-y-2.5">
						<?php foreach ( ga_menu_items( 'primary' ) as $item ) : ?>
							<li><a class="ulink text-ink-200 hover:text-white" href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</div>
				<div>
					<p class="eyebrow mb-4">Institucional</p>
					<ul class="space-y-2.5">
						<?php foreach ( ga_menu_items( 'footer' ) as $item ) : ?>
							<li><a class="ulink text-ink-200 hover:text-white" href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a></li>
						<?php endforeach; ?>
						<li><button type="button" class="ulink text-left text-ink-200 hover:text-white" data-consent-open>Preferências de cookies</button></li>
					</ul>
				</div>
				<div>
					<p class="eyebrow mb-4">Conta</p>
					<ul class="space-y-2.5">
						<?php if ( is_user_logged_in() ) : ?>
							<li><a class="ulink text-ink-200 hover:text-white" href="<?php echo esc_url( ga_account_tab_url( 'favoritos' ) ); ?>">Favoritos</a></li>
							<li><a class="ulink text-ink-200 hover:text-white" href="<?php echo esc_url( ga_account_tab_url( 'perfil' ) ); ?>">Perfil</a></li>
						<?php else : ?>
							<li><a class="ulink text-ink-200 hover:text-white" href="<?php echo esc_url( ga_login_url() ); ?>" data-login-link>Entrar</a></li>
							<li><a class="ulink text-ink-200 hover:text-white" href="<?php echo esc_url( ga_login_url( '', 'register' ) ); ?>" data-login-link>Criar conta</a></li>
						<?php endif; ?>
					</ul>
				</div>
			</div>
		</div>

		<div class="flex flex-col gap-6 border-t border-white/[0.06] py-8 text-sm text-ink-500 md:flex-row md:items-center md:justify-between">
			<p>© <?php echo esc_html( gmdate( 'Y' ) . ' ' . get_bloginfo( 'name' ) ); ?>. OBS Studio é uma marca do OBS Project; não temos vínculo com o projeto.</p>
			<?php if ( $ga_socials ) : ?>
				<ul class="flex gap-2">
					<?php foreach ( $ga_socials as $network => $url ) : ?>
						<li><a class="icon-btn size-10" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( ucfirst( $network ) ); ?>"><?php echo ga_icon( $network, 'size-4' ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</div>

	<div class="footer-word -mb-[0.12em] text-center" aria-hidden="true"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></div>
</footer>

<?php ga_consent_banner(); ?>

<div class="curtain" aria-hidden="true">
	<div class="curtain__logo"><span class="logo__mark"></span><?php echo esc_html( get_bloginfo( 'name' ) ); ?></div>
</div>

<?php wp_footer(); ?>
</body>
</html>

<?php
/**
 * Template Name: Minha conta
 */

defined( 'ABSPATH' ) || exit;
get_header();

$ga_user = wp_get_current_user();
$ga_tab  = ga_account_tab();
$ga_favs = ga_user_favorites();
$ga_dls  = ga_user_downloads();
$ga_reqs = ga_user_requests();
?>

<section class="relative overflow-hidden pb-10 pt-[calc(var(--header-h)+3rem)] md:pt-[calc(var(--header-h)+4.5rem)]">
	<div class="pointer-events-none absolute -right-40 -top-60 size-[40rem] rounded-full bg-signal/25 blur-[140px]" aria-hidden="true"></div>
	<div class="wrap relative">
		<div class="flex flex-col gap-8 md:flex-row md:items-end md:justify-between">
			<div class="flex items-center gap-6">
				<?php echo ga_avatar_html( 'ga-avatar--lg' ); ?>
				<div>
					<p class="eyebrow eyebrow--dot mb-2">Minha conta</p>
					<h1 class="text-display-md text-white">Oi, <?php echo esc_html( ga_user_first_name( $ga_user ) ); ?>.</h1>
				</div>
			</div>
			<dl class="flex gap-10">
				<div><dt class="eyebrow">Favoritos</dt><dd class="font-display text-3xl font-semibold text-white" data-fav-count><?php echo count( $ga_favs ); ?></dd></div>
				<div><dt class="eyebrow">Downloads</dt><dd class="font-display text-3xl font-semibold text-white"><?php echo count( $ga_dls ); ?></dd></div>
				<div><dt class="eyebrow">Pedidos</dt><dd class="font-display text-3xl font-semibold text-white"><?php echo count( $ga_reqs ); ?></dd></div>
			</dl>
		</div>

		<nav class="tabs mt-12 w-fit max-w-full" aria-label="Seções da conta">
			<?php foreach ( ga_account_tabs() as $key => [ $label, $icon ] ) : ?>
				<a href="<?php echo esc_url( ga_account_tab_url( $key ) ); ?>" <?php echo $key === $ga_tab ? 'aria-current="page"' : ''; ?> data-swup-preload>
					<?php echo ga_icon( $icon, 'size-4' ); ?> <?php echo esc_html( $label ); ?>
				</a>
			<?php endforeach; ?>
		</nav>
	</div>
</section>

<section class="pb-28 pt-6 md:pb-40">
	<div class="wrap">
		<?php if ( 'favoritos' === $ga_tab ) : ?>
			<?php $ga_fav_posts = $ga_favs ? ga_query_templates( [ 'post__in' => $ga_favs, 'orderby' => 'post__in' ] ) : []; ?>
			<?php if ( $ga_fav_posts ) : ?>
				<div class="grid gap-x-8 gap-y-14 md:grid-cols-2 xl:grid-cols-3" data-fav-grid>
					<?php foreach ( $ga_fav_posts as $i => $p ) : ?>
						<?php get_template_part( 'template-parts/card', null, [ 'post' => $p, 'index' => $i ] ); ?>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<?php get_template_part( 'template-parts/empty', null, [
					'icon'  => 'heart',
					'title' => 'Nenhum favorito ainda',
					'text'  => 'Toque no coração de um template para guardar aqui e achar rápido depois.',
					'cta'   => [ 'Explorar templates', ga_page_url( 'templates' ) ],
				] ); ?>
			<?php endif; ?>

		<?php elseif ( 'downloads' === $ga_tab ) : ?>
			<?php if ( $ga_dls ) : ?>
				<ul class="divide-y divide-white/[0.07] rounded-3xl border border-white/10">
					<?php foreach ( $ga_dls as $post_id => $time ) : ?>
						<?php
						if ( 'publish' !== get_post_status( $post_id ) ) {
							continue;
						}
						$t = ga_template( $post_id );
						?>
						<li class="flex flex-col gap-4 p-4 sm:flex-row sm:items-center md:p-5">
							<a href="<?php echo esc_url( $t['url'] ); ?>" class="block aspect-video w-full shrink-0 overflow-hidden rounded-xl bg-ink-800 sm:w-40">
								<?php echo ga_image( $t['thumbnail_id'], 'ga-thumb', 'row', [ 'class' => 'size-full object-cover', 'loading' => 'lazy' ] ); ?>
							</a>
							<div class="min-w-0 flex-1">
								<a href="<?php echo esc_url( $t['url'] ); ?>" class="font-display text-xl font-semibold tracking-tight text-white hover:text-lime"><?php echo esc_html( $t['title'] ); ?></a>
								<p class="text-sm text-ink-400">Baixado em <?php echo esc_html( wp_date( 'j \d\e F \d\e Y, H:i', $time ) ); ?><?php echo $t['version'] ? ' · versão atual v' . esc_html( $t['version'] ) : ''; ?></p>
								<?php if ( $t['updated'] > $time ) : ?>
									<p class="mt-1 inline-flex items-center gap-1.5 text-sm text-lime"><?php echo ga_icon( 'sparkle', 'size-3.5' ); ?> Atualizado desde o seu download</p>
								<?php endif; ?>
							</div>
							<?php if ( $t['has_file'] && ga_user_can_download( $t['id'] ) ) : ?>
								<a href="<?php echo esc_url( ga_download_url( $t['id'] ) ); ?>" class="btn btn--outline btn--sm" data-no-swup><?php echo ga_icon( 'download', 'size-4' ); ?><span>Baixar de novo</span></a>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<?php get_template_part( 'template-parts/empty', null, [
					'icon'  => 'download',
					'title' => 'Nada baixado por enquanto',
					'text'  => 'Os templates que você baixar aparecem aqui, com aviso quando ganharem atualização.',
					'cta'   => [ 'Escolher um template', ga_page_url( 'templates' ) ],
				] ); ?>
			<?php endif; ?>

		<?php elseif ( 'solicitacoes' === $ga_tab ) : ?>
			<div class="mb-8 flex items-center justify-between gap-4">
				<p class="text-ink-300">Acompanhe o andamento das suas ideias.</p>
				<a href="<?php echo esc_url( ga_page_url( 'request' ) ); ?>" class="btn btn--primary btn--sm"><?php echo ga_icon( 'plus', 'size-4' ); ?><span>Nova solicitação</span></a>
			</div>
			<?php if ( $ga_reqs ) : ?>
				<ul class="grid gap-4 md:grid-cols-2">
					<?php foreach ( $ga_reqs as $req ) : ?>
						<?php $status = get_post_meta( $req->ID, '_ga_status', true ) ?: 'received'; ?>
						<li class="rounded-3xl border border-white/10 p-6">
							<div class="flex items-start justify-between gap-4">
								<h3 class="font-display text-xl font-semibold tracking-tight text-white"><?php echo esc_html( $req->post_title ); ?></h3>
								<span class="status status--<?php echo esc_attr( $status ); ?> shrink-0"><?php echo esc_html( ga_request_statuses()[ $status ] ?? $status ); ?></span>
							</div>
							<p class="mt-2 line-clamp-3 text-sm text-ink-400"><?php echo esc_html( wp_strip_all_tags( $req->post_content ) ); ?></p>
							<p class="mt-4 font-mono text-[11px] uppercase tracking-[0.14em] text-ink-500">Enviada em <?php echo esc_html( get_the_date( 'j M Y', $req ) ); ?></p>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<?php get_template_part( 'template-parts/empty', null, [
					'icon'  => 'wand',
					'title' => 'Nenhum pedido ainda',
					'text'  => 'Tem uma ideia de template? Conte pra gente e acompanhe por aqui.',
					'cta'   => [ 'Solicitar template', ga_page_url( 'request' ) ],
				] ); ?>
			<?php endif; ?>

		<?php elseif ( 'perfil' === $ga_tab ) : ?>
			<?php $ga_no_password = (bool) get_user_meta( $ga_user->ID, 'ga_password_unset', true ); ?>
			<form class="grid max-w-3xl gap-10" data-account-form="profile" novalidate>
				<fieldset class="grid gap-4">
					<legend class="mb-4 font-display text-2xl font-semibold tracking-tight text-white">Seus dados</legend>
					<label class="field">
						<input class="field__input" type="text" name="name" value="<?php echo esc_attr( $ga_user->display_name ); ?>" placeholder=" " required autocomplete="name">
						<span class="field__label">Nome</span>
					</label>
					<label class="field">
						<input class="field__input cursor-not-allowed opacity-60" type="email" value="<?php echo esc_attr( $ga_user->user_email ); ?>" placeholder=" " disabled>
						<span class="field__label">E-mail</span>
					</label>
					<p class="text-sm text-ink-500">Para trocar o e-mail, fale com <?php echo do_shortcode( '[ga_contact_email]' ); ?>.</p>
				</fieldset>

				<fieldset class="grid gap-4">
					<legend class="mb-2 font-display text-2xl font-semibold tracking-tight text-white"><?php echo $ga_no_password ? 'Criar uma senha' : 'Trocar senha'; ?></legend>
					<p class="mb-2 text-sm text-ink-400">
						<?php echo $ga_no_password ? 'Sua conta entra pelo Google. Se quiser, crie uma senha para entrar também com e-mail.' : 'Deixe em branco para manter a senha atual.'; ?>
					</p>
					<?php if ( ! $ga_no_password ) : ?>
						<label class="field">
							<input class="field__input" type="password" name="current_password" placeholder=" " autocomplete="current-password">
							<span class="field__label">Senha atual</span>
						</label>
					<?php endif; ?>
					<label class="field">
						<input class="field__input" type="password" name="new_password" placeholder=" " autocomplete="new-password" minlength="8">
						<span class="field__label">Nova senha (mín. 8 caracteres)</span>
					</label>
				</fieldset>

				<div class="flex items-center gap-4">
					<button type="submit" class="btn btn--primary"><span>Salvar alterações</span></button>
					<p class="text-sm" data-form-status role="status"></p>
				</div>
			</form>

		<?php else : ?>
			<?php $ga_consent = get_user_meta( $ga_user->ID, 'ga_privacy_consent', true ); ?>
			<div class="grid max-w-4xl gap-6 md:grid-cols-2">
				<div class="rounded-3xl border border-white/10 p-6 md:p-8">
					<?php echo ga_icon( 'shield', 'size-7 text-lime' ); ?>
					<h2 class="mt-5 font-display text-2xl font-semibold tracking-tight text-white">Seu consentimento</h2>
					<p class="mt-2 text-sm text-ink-400">
						<?php if ( $ga_consent ) : ?>
							Você aceitou a Política de Privacidade em <?php echo esc_html( wp_date( 'j \d\e F \d\e Y', $ga_consent['at'] ) ); ?> (<?php echo 'google' === ( $ga_consent['source'] ?? '' ) ? 'pelo Google' : 'no cadastro'; ?>).
						<?php else : ?>
							Sua conta foi criada antes do registro de consentimento.
						<?php endif; ?>
					</p>
					<div class="mt-6 flex flex-wrap gap-2">
						<a href="<?php echo esc_url( ga_page_url( 'privacy' ) ); ?>" class="btn btn--outline btn--sm"><span>Ler a política</span></a>
						<?php echo do_shortcode( '[ga_cookie_preferences]' ); ?>
					</div>
				</div>

				<div class="rounded-3xl border border-live/25 bg-live/[0.04] p-6 md:p-8">
					<?php echo ga_icon( 'trash', 'size-7 text-live' ); ?>
					<h2 class="mt-5 font-display text-2xl font-semibold tracking-tight text-white">Excluir conta</h2>
					<p class="mt-2 text-sm text-ink-400">Apaga sua conta, favoritos e histórico de downloads. Suas solicitações ficam anônimas. Não dá para desfazer.</p>
					<?php if ( current_user_can( 'edit_posts' ) ) : ?>
						<p class="mt-6 text-sm text-ink-500">Contas da equipe são removidas pelo painel.</p>
					<?php else : ?>
						<form class="mt-6 grid gap-3" data-account-form="delete" novalidate>
							<label class="field">
								<input class="field__input" type="text" name="confirm" placeholder=" " autocomplete="off" required>
								<span class="field__label">Digite EXCLUIR para confirmar</span>
							</label>
							<button type="submit" class="btn btn--outline btn--sm !border-live/50 !text-[#ff8f9a]"><span>Excluir minha conta</span></button>
							<p class="text-sm" data-form-status role="status"></p>
						</form>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();

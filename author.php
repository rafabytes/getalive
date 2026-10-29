<?php
/**
 * Perfil de um usuário com a linha do tempo de atividade, para auditoria.
 * Só administradores chegam aqui; para os outros a página é 404 (inc/activity.php).
 */

defined( 'ABSPATH' ) || exit;
get_header();

$ga_user   = get_queried_object();
$ga_groups = ga_activity_groups();
$ga_group  = sanitize_key( $_GET['tipo'] ?? '' );
$ga_group  = isset( $ga_groups[ $ga_group ] ) ? $ga_group : 'tudo';
$ga_page   = max( 1, absint( $_GET['pagina'] ?? 1 ) );
$ga_per    = 100;
$ga_rows   = ga_user_activity( $ga_user->ID, $ga_groups[ $ga_group ][1], $ga_per + 1, ( $ga_page - 1 ) * $ga_per );
$ga_more   = count( $ga_rows ) > $ga_per;
$ga_rows   = array_slice( $ga_rows, 0, $ga_per );
$ga_counts = ga_user_activity_counts( $ga_user->ID );
$ga_last   = max( $ga_counts['login']['last'] ?? 0, $ga_counts['view']['last'] ?? 0 );
$ga_roles  = array_map( fn( $role ) => translate_user_role( wp_roles()->roles[ $role ]['name'] ?? $role ), $ga_user->roles );
$ga_url    = fn( array $args ) => add_query_arg( $args, ga_user_activity_url( $ga_user->ID ) );
$ga_date   = 'j \d\e F \d\e Y';
?>

<section class="relative overflow-hidden pb-10 pt-[calc(var(--header-h)+3rem)] md:pt-[calc(var(--header-h)+4.5rem)]">
	<div class="pointer-events-none absolute -right-40 -top-60 size-[40rem] rounded-full bg-signal/25 blur-[140px]" aria-hidden="true"></div>
	<div class="wrap relative">
		<div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
			<div class="flex items-center gap-6">
				<?php echo ga_avatar_html( 'ga-avatar--lg', $ga_user ); ?>
				<div class="min-w-0">
					<p class="eyebrow mb-2"><?php echo ga_icon( 'shield', 'size-3.5' ); ?> Auditoria · visível só para administradores</p>
					<h1 class="text-display-md text-white"><?php echo esc_html( $ga_user->display_name ); ?></h1>
					<p class="mt-2 break-all text-ink-400">
						<a href="mailto:<?php echo esc_attr( $ga_user->user_email ); ?>" class="hover:text-white"><?php echo esc_html( $ga_user->user_email ); ?></a>
						· <?php echo esc_html( implode( ', ', $ga_roles ) ); ?>
						· <a href="<?php echo esc_url( get_edit_user_link( $ga_user->ID ) ); ?>" class="underline decoration-white/20 underline-offset-4 hover:text-white" data-no-swup>Editar no painel</a>
					</p>
				</div>
			</div>
			<dl class="grid grid-cols-2 gap-x-10 gap-y-4 sm:flex">
				<div><dt class="eyebrow">Conta criada</dt><dd class="mt-1 text-white"><?php echo esc_html( wp_date( 'd/m/Y', strtotime( $ga_user->user_registered . ' UTC' ) ) ); ?></dd></div>
				<div><dt class="eyebrow">Último acesso</dt><dd class="mt-1 text-white"><?php echo $ga_last ? esc_html( wp_date( 'd/m/Y H:i', $ga_last ) ) : '—'; ?></dd></div>
				<div><dt class="eyebrow">Downloads</dt><dd class="mt-1 text-white"><?php echo (int) ( $ga_counts['download']['total'] ?? 0 ); ?></dd></div>
				<div><dt class="eyebrow">Favoritos</dt><dd class="mt-1 text-white"><?php echo count( ga_user_favorites( $ga_user->ID ) ); ?></dd></div>
				<div><dt class="eyebrow">Pedidos</dt><dd class="mt-1 text-white"><?php echo (int) ( $ga_counts['request']['total'] ?? 0 ); ?></dd></div>
				<div><dt class="eyebrow">Páginas vistas</dt><dd class="mt-1 text-white"><?php echo (int) ( $ga_counts['view']['total'] ?? 0 ); ?></dd></div>
			</dl>
		</div>

		<nav class="tabs mt-12 w-fit max-w-full" aria-label="Filtrar atividade">
			<?php foreach ( $ga_groups as $key => [ $label ] ) : ?>
				<a href="<?php echo esc_url( 'tudo' === $key ? ga_user_activity_url( $ga_user->ID ) : $ga_url( [ 'tipo' => $key ] ) ); ?>" <?php echo $key === $ga_group ? 'aria-current="page"' : ''; ?>><?php echo esc_html( $label ); ?></a>
			<?php endforeach; ?>
		</nav>
	</div>
</section>

<section class="pb-28 pt-6 md:pb-40">
	<div class="wrap"><div class="max-w-4xl">
		<?php if ( ! $ga_rows ) : ?>
			<?php get_template_part( 'template-parts/empty', null, [
				'icon'  => 'clock',
				'title' => 'Sem atividade',
				'text'  => 'Nada registrado para este filtro ainda.',
			] ); ?>
		<?php else : ?>
			<?php
			$ga_day = '';
			foreach ( $ga_rows as $ga_row ) :
				[ $icon, $text, $tone ] = ga_activity_describe( $ga_row );
				$day = wp_date( 'Y-m-d', $ga_row->time );
				if ( $day !== $ga_day ) :
					if ( $ga_day ) {
						echo '</ol>';
					}
					$ga_day = $day;
					$label  = match ( $day ) {
						wp_date( 'Y-m-d' )                                 => 'Hoje',
						wp_date( 'Y-m-d', time() - DAY_IN_SECONDS )        => 'Ontem',
						default                                            => wp_date( $ga_date, $ga_row->time ),
					};
					?>
					<h2 class="eyebrow mb-4 mt-12 first:mt-0"><?php echo esc_html( $label ); ?></h2>
					<ol class="activity">
				<?php endif; ?>
				<li class="activity__item<?php echo $tone ? ' activity__item--' . esc_attr( $tone ) : ''; ?>">
					<time class="activity__time" datetime="<?php echo esc_attr( gmdate( 'c', $ga_row->time ) ); ?>" title="<?php echo esc_attr( wp_date( $ga_date . ', H:i:s', $ga_row->time ) ); ?>"><?php echo esc_html( wp_date( 'H:i', $ga_row->time ) ); ?></time>
					<span class="activity__icon"><?php echo ga_icon( $icon, 'size-4' ); ?></span>
					<div class="min-w-0">
						<p class="activity__text"><?php echo wp_kses_post( $text ); ?></p>
						<?php
						$meta = array_filter( [
							$ga_row->ip ? 'IP ' . $ga_row->ip : '',
							ga_agent_label( $ga_row->detail['agent'] ?? '' ),
							! empty( $ga_row->detail['backfill'] ) ? 'registro anterior ao histórico' : '',
						] );
						if ( $meta ) :
							?>
							<p class="activity__meta"><?php echo esc_html( implode( ' · ', $meta ) ); ?></p>
						<?php endif; ?>
					</div>
				</li>
			<?php endforeach; ?>
			</ol>

			<?php if ( $ga_more || $ga_page > 1 ) : ?>
				<div class="mt-12 flex flex-wrap gap-3">
					<?php if ( $ga_page > 1 ) : ?>
						<a href="<?php echo esc_url( $ga_url( [ 'tipo' => 'tudo' === $ga_group ? false : $ga_group, 'pagina' => $ga_page > 2 ? $ga_page - 1 : false ] ) ); ?>" class="btn btn--ghost"><?php echo ga_icon( 'arrow-left', 'size-4' ); ?><span>Mais recentes</span></a>
					<?php endif; ?>
					<?php if ( $ga_more ) : ?>
						<a href="<?php echo esc_url( $ga_url( [ 'tipo' => 'tudo' === $ga_group ? false : $ga_group, 'pagina' => $ga_page + 1 ] ) ); ?>" class="btn btn--ghost"><span>Mais antigos</span><?php echo ga_icon( 'arrow-right', 'btn__arrow' ); ?></a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		<?php endif; ?>
	</div></div>
</section>

<?php
get_footer();

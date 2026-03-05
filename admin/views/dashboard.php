<?php
/**
 * Dashboard view template.
 *
 * Ultra-modern dashboard with SVG gauge, dimension charts, and module cards.
 *
 * @package WP_Plugin_Health_Monitor
 *
 * @var array $score Health score data from WPHM_Health_Scorer::get_score().
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wphm_scorer      = new WPHM_Health_Scorer();
$wphm_score_label = $wphm_scorer->get_score_label( $score['total'] );
$wphm_score_class = $wphm_scorer->get_score_css_class( $score['total'] );
$wphm_total       = absint( $score['total'] );

// Dimension data for JS.
$wphm_dimensions = array(
	array(
		'key'   => 'plugins',
		'label' => __( 'Plugins', 'health-radar' ),
		'icon'  => 'admin-plugins',
		'val'   => absint( $score['plugins'] ),
		'max'   => WPHM_Health_Scorer::PLUGIN_MAX,
		'raw'   => absint( $score['raw']['plugin_count'] ),
		'unit'  => __( 'active', 'health-radar' ),
		'color' => '#3858e9',
	),
	array(
		'key'   => 'assets',
		'label' => __( 'Assets', 'health-radar' ),
		'icon'  => 'media-code',
		'val'   => absint( $score['assets'] ),
		'max'   => WPHM_Health_Scorer::ASSET_MAX,
		'raw'   => absint( $score['raw']['asset_count'] ),
		'unit'  => __( 'enqueued', 'health-radar' ),
		'color' => '#00a76f',
	),
	array(
		'key'      => 'db_queries',
		'label'    => __( 'DB Queries', 'health-radar' ),
		'icon'     => 'database',
		'val'      => absint( $score['db_queries'] ),
		'max'      => WPHM_Health_Scorer::DB_QUERY_MAX,
		'raw'      => absint( $score['raw']['db_query_count'] ),
		'unit'     => __( 'queries', 'health-radar' ),
		'color'    => '#8e33ff',
		'no_query' => ! defined( 'SAVEQUERIES' ) || ! SAVEQUERIES,
	),
	array(
		'key'   => 'autoload',
		'label' => __( 'Autoload', 'health-radar' ),
		'icon'  => 'editor-table',
		'val'   => absint( $score['autoload'] ),
		'max'   => WPHM_Health_Scorer::AUTOLOAD_MAX,
		'raw'   => absint( $score['raw']['autoload_size'] ),
		'unit'  => __( 'loaded', 'health-radar' ),
		'color' => '#ff5630',
	),
);
?>
<div class="wrap wphm-wrap wphm-page wphm-page--dashboard">
	<div class="wphm-dash">

		<!-- ─── Hero Section: Score Ring + Dimensions ─── -->
		<div class="wphm-dash__hero">
			<div class="wphm-dash__hero-left">
				<div class="wphm-dash__ring-wrap" id="wphm-ring-wrap"
					data-score="<?php echo absint( $wphm_total ); ?>"
					data-class="<?php echo esc_attr( $wphm_score_class ); ?>">
					<svg viewBox="0 0 200 200" class="wphm-dash__ring-svg">
						<defs>
							<linearGradient id="wphm-ring-grad-excellent" x1="0%" y1="0%" x2="100%" y2="100%">
								<stop offset="0%" stop-color="#00e676" />
								<stop offset="100%" stop-color="#00a32a" />
							</linearGradient>
							<linearGradient id="wphm-ring-grad-good" x1="0%" y1="0%" x2="100%" y2="100%">
								<stop offset="0%" stop-color="#64b5f6" />
								<stop offset="100%" stop-color="#2271b1" />
							</linearGradient>
							<linearGradient id="wphm-ring-grad-fair" x1="0%" y1="0%" x2="100%" y2="100%">
								<stop offset="0%" stop-color="#ffd54f" />
								<stop offset="100%" stop-color="#dba617" />
							</linearGradient>
							<linearGradient id="wphm-ring-grad-poor" x1="0%" y1="0%" x2="100%" y2="100%">
								<stop offset="0%" stop-color="#ff8a65" />
								<stop offset="100%" stop-color="#d63638" />
							</linearGradient>
							<linearGradient id="wphm-ring-grad-critical" x1="0%" y1="0%" x2="100%" y2="100%">
								<stop offset="0%" stop-color="#d63638" />
								<stop offset="100%" stop-color="#8c1a1c" />
							</linearGradient>
						</defs>
						<circle class="wphm-dash__ring-track" cx="100" cy="100" r="88"
							fill="none" stroke="#e8ecf0" stroke-width="12" />
						<circle class="wphm-dash__ring-progress" cx="100" cy="100" r="88"
							fill="none"
							stroke="url(#wphm-ring-grad-<?php echo esc_attr( $wphm_score_class ); ?>)"
							stroke-width="12"
							stroke-linecap="round"
							stroke-dasharray="0 553"
							transform="rotate(-90 100 100)" />
					</svg>
					<div class="wphm-dash__ring-center">
						<span class="wphm-dash__ring-num" id="wphm-ring-num">0</span>
					<span class="wphm-dash__ring-label wphm-dash__ring-label--<?php echo esc_attr( $wphm_score_class ); ?>"
						id="wphm-ring-label"><?php echo esc_html( $wphm_score_label ); ?></span>
					</div>
				</div>

				<div class="wphm-dash__scan-action">
					<button type="button" id="wphm-run-scan" class="wphm-dash__scan-btn">
						<span class="dashicons dashicons-image-rotate"></span>
						<?php esc_html_e( 'Run Full Scan', 'health-radar' ); ?>
					</button>
					<span id="wphm-scan-status" class="wphm-dash__scan-status"></span>
				</div>
			</div>

			<div class="wphm-dash__hero-right">
				<h1 class="wphm-dash__heading"><?php esc_html_e( 'Health Radar', 'health-radar' ); ?></h1>
				<p class="wphm-dash__subheading"><?php esc_html_e( 'Real-time overview of your WordPress plugin ecosystem health.', 'health-radar' ); ?></p>

				<!-- Dimension Bars -->
				<div class="wphm-dash__dims" id="wphm-dims">
				<?php foreach ( $wphm_dimensions as $wphm_dim ) :
					$wphm_pct    = $wphm_dim['max'] > 0 ? round( ( $wphm_dim['val'] / $wphm_dim['max'] ) * 100 ) : 0;
					$wphm_nodata = ! empty( $wphm_dim['no_query'] );
					$wphm_raw    = $wphm_dim['key'] === 'autoload'
						? size_format( $wphm_dim['raw'], 1 ) . ' ' . $wphm_dim['unit']
						: absint( $wphm_dim['raw'] ) . ' ' . $wphm_dim['unit'];
					if ( $wphm_nodata && 0 === $wphm_dim['raw'] ) {
						$wphm_raw = __( 'SAVEQUERIES not enabled', 'health-radar' );
						}
					?>
				<div class="wphm-dash__dim" data-key="<?php echo esc_attr( $wphm_dim['key'] ); ?>">
					<div class="wphm-dash__dim-top">
						<span class="wphm-dash__dim-icon" style="color:<?php echo esc_attr( $wphm_dim['color'] ); ?>">
							<span class="dashicons dashicons-<?php echo esc_attr( $wphm_dim['icon'] ); ?>"></span>
						</span>
						<span class="wphm-dash__dim-name"><?php echo esc_html( $wphm_dim['label'] ); ?></span>
						<span class="wphm-dash__dim-val">
							<strong><?php echo absint( $wphm_dim['val'] ); ?></strong><span class="wphm-dash__dim-max">/<?php echo absint( $wphm_dim['max'] ); ?></span>
						</span>
					</div>
					<div class="wphm-dash__dim-bar">
						<div class="wphm-dash__dim-fill" style="width:<?php echo absint( $wphm_pct ); ?>%;background:<?php echo esc_attr( $wphm_dim['color'] ); ?>"></div>
					</div>
					<span class="wphm-dash__dim-detail"><?php echo esc_html( $wphm_raw ); ?></span>
					</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>

		<!-- ─── Module Cards Grid ─── -->
		<div class="wphm-dash__cards">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wphm-conflicts' ) ); ?>" class="wphm-dash__card">
				<div class="wphm-dash__card-icon wphm-dash__card-icon--orange">
					<span class="dashicons dashicons-warning"></span>
				</div>
				<div class="wphm-dash__card-body">
					<h3 class="wphm-dash__card-title"><?php esc_html_e( 'Plugin Conflicts', 'health-radar' ); ?></h3>
					<p class="wphm-dash__card-desc"><?php esc_html_e( 'Detect hook collisions & duplicate registrations', 'health-radar' ); ?></p>
				</div>
				<span class="wphm-dash__card-arrow dashicons dashicons-arrow-right-alt2"></span>
			</a>

			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wphm-performance' ) ); ?>" class="wphm-dash__card">
				<div class="wphm-dash__card-icon wphm-dash__card-icon--purple">
					<span class="dashicons dashicons-performance"></span>
				</div>
				<div class="wphm-dash__card-body">
					<h3 class="wphm-dash__card-title"><?php esc_html_e( 'Performance', 'health-radar' ); ?></h3>
					<p class="wphm-dash__card-desc"><?php esc_html_e( 'DB queries, autoload size & asset weight', 'health-radar' ); ?></p>
				</div>
				<span class="wphm-dash__card-arrow dashicons dashicons-arrow-right-alt2"></span>
			</a>

			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wphm-php-compat' ) ); ?>" class="wphm-dash__card">
				<div class="wphm-dash__card-icon wphm-dash__card-icon--blue">
					<span class="dashicons dashicons-editor-code"></span>
				</div>
				<div class="wphm-dash__card-body">
					<h3 class="wphm-dash__card-title"><?php esc_html_e( 'PHP Compatibility', 'health-radar' ); ?></h3>
					<p class="wphm-dash__card-desc"><?php esc_html_e( 'Version requirements & deprecated functions', 'health-radar' ); ?></p>
				</div>
				<span class="wphm-dash__card-arrow dashicons dashicons-arrow-right-alt2"></span>
			</a>

			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wphm-debug-log' ) ); ?>" class="wphm-dash__card">
				<div class="wphm-dash__card-icon wphm-dash__card-icon--teal">
					<span class="dashicons dashicons-clipboard"></span>
				</div>
				<div class="wphm-dash__card-body">
					<h3 class="wphm-dash__card-title"><?php esc_html_e( 'Debug Log', 'health-radar' ); ?></h3>
					<p class="wphm-dash__card-desc"><?php esc_html_e( 'Error analysis & top-offending plugins', 'health-radar' ); ?></p>
				</div>
				<span class="wphm-dash__card-arrow dashicons dashicons-arrow-right-alt2"></span>
			</a>

			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wphm-report' ) ); ?>" class="wphm-dash__card wphm-dash__card--highlight">
				<div class="wphm-dash__card-icon wphm-dash__card-icon--green">
					<span class="dashicons dashicons-chart-area"></span>
				</div>
				<div class="wphm-dash__card-body">
					<h3 class="wphm-dash__card-title"><?php esc_html_e( 'Full Report', 'health-radar' ); ?></h3>
					<p class="wphm-dash__card-desc"><?php esc_html_e( 'Generate & download comprehensive health report', 'health-radar' ); ?></p>
				</div>
				<span class="wphm-dash__card-arrow dashicons dashicons-arrow-right-alt2"></span>
			</a>
		</div>

	</div>
</div>

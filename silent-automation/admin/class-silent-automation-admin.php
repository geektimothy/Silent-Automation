<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Silent_Automation_Admin {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_ajax_silent_toggle_automation', array( $this, 'toggle_automation' ) );
	}

	public function add_menu() {
		add_menu_page(
			'Silent Automation',
			'Silent Automation',
			'manage_options',
			'silent-automation',
			array( $this, 'render_dashboard' ),
			'dashicons-chart-line',
			30
		);
	}

	public function enqueue_assets( $hook ) {
		if ( 'toplevel_page_silent-automation' !== $hook ) {
			return;
		}

		wp_enqueue_style( 'silent-admin-css', SILENT_AUTOMATION_URL . 'assets/css/admin.css', array(), SILENT_AUTOMATION_VERSION );
		wp_enqueue_script( 'silent-admin-js', SILENT_AUTOMATION_URL . 'assets/js/admin.js', array( 'jquery' ), SILENT_AUTOMATION_VERSION, true );
		
		wp_localize_script( 'silent-admin-js', 'silentAdmin', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'silent_admin_nonce' )
		) );
	}

	public function render_dashboard() {
		$tracker = Silent_Automation_Tracker::get_instance();
		$total_events = $tracker->get_total_events();
		$patterns = $tracker->get_detected_patterns();
		$active_rules = get_option( 'silent_automation_active_rules', array() );

		?>
		<div class="wrap silent-automation-wrap">
			<h1>Silent Automation Dashboard</h1>
			
			<div class="silent-stats-grid">
				<div class="silent-stat-card">
					<h3>Total Tracked Events</h3>
					<p class="stat-value"><?php echo esc_html( $total_events ); ?></p>
				</div>
				<div class="silent-stat-card">
					<h3>Active Automations</h3>
					<p class="stat-value"><?php echo count( $active_rules ); ?></p>
				</div>
			</div>

			<h2>Detected Patterns & Suggestions</h2>
			<div class="silent-suggestions">
				<?php if ( empty( $patterns ) ) : ?>
					<p>No patterns detected yet. Keep tracking visitor behavior!</p>
				<?php else : ?>
					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th>Pattern</th>
								<th>Condition</th>
								<th>Suggestion</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $patterns as $pattern ) : 
								$rule_id = md5( $pattern['type'] . $pattern['page_url'] );
								$is_active = isset( $active_rules[ $rule_id ] );
								?>
								<tr>
									<td><strong><?php echo esc_html( ucfirst( str_replace('_', ' ', $pattern['type']) ) ); ?></strong></td>
									<td><?php echo esc_html( $pattern['condition'] ); ?></td>
									<td><?php echo esc_html( $pattern['message'] ); ?></td>
									<td>
										<button 
											class="button <?php echo $is_active ? 'button-secondary' : 'button-primary'; ?> silent-toggle-btn"
											data-rule-id="<?php echo esc_attr( $rule_id ); ?>"
											data-page-url="<?php echo esc_attr( $pattern['page_url'] ); ?>"
											data-type="<?php echo esc_attr( $pattern['type'] ); ?>"
										>
											<?php echo $is_active ? 'Deactivate' : 'Activate Automation'; ?>
										</button>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	public function toggle_automation() {
		check_ajax_referer( 'silent_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized' );
		}

		$rule_id = sanitize_text_field( $_POST['rule_id'] );
		$page_url = esc_url_raw( $_POST['page_url'] );
		$type = sanitize_text_field( $_POST['type'] );
		
		$active_rules = get_option( 'silent_automation_active_rules', array() );

		if ( isset( $active_rules[ $rule_id ] ) ) {
			unset( $active_rules[ $rule_id ] );
			$status = 'deactivated';
		} else {
			$active_rules[ $rule_id ] = array(
				'page_url' => $page_url,
				'type'     => $type,
				'message'  => 'Get 10% discount today!'
			);
			$status = 'activated';
		}

		update_option( 'silent_automation_active_rules', $active_rules );

		wp_send_json_success( array( 'status' => $status ) );
	}
}

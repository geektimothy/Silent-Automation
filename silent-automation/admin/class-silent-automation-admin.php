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
		add_action( 'wp_ajax_silent_save_automation', array( $this, 'save_automation' ) );
		add_action( 'wp_ajax_silent_delete_automation', array( $this, 'delete_automation' ) );
		add_action( 'wp_ajax_silent_save_settings', array( $this, 'save_settings' ) );
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
		$tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'overview';
		?>
		<div class="silent-admin-container">
			<!-- Sidebar Navigation -->
			<aside class="silent-sidebar">
				<div class="silent-brand">
					<span class="dashicons dashicons-chart-line"></span>
					<h2>Silent Auto</h2>
				</div>
				<nav class="silent-nav">
					<a href="?page=silent-automation&tab=overview" class="silent-nav-item <?php echo $tab === 'overview' ? 'is-active' : ''; ?>">
						<span class="dashicons dashicons-dashboard"></span> Overview
					</a>
					<a href="?page=silent-automation&tab=automations" class="silent-nav-item <?php echo $tab === 'automations' ? 'is-active' : ''; ?>">
						<span class="dashicons dashicons-zap"></span> Automations
					</a>
					<a href="?page=silent-automation&tab=analytics" class="silent-nav-item <?php echo $tab === 'analytics' ? 'is-active' : ''; ?>">
						<span class="dashicons dashicons-performance"></span> Analytics
					</a>
					<a href="?page=silent-automation&tab=settings" class="silent-nav-item <?php echo $tab === 'settings' ? 'is-active' : ''; ?>">
						<span class="dashicons dashicons-admin-settings"></span> Settings
					</a>
				</nav>
				<div class="silent-sidebar-footer">
					<p>Version <?php echo SILENT_AUTOMATION_VERSION; ?></p>
				</div>
			</aside>

			<!-- Main Content Area -->
			<main class="silent-main">
				<header class="silent-header">
					<div class="silent-header-title">
						<h1><?php echo esc_html( ucfirst( $tab ) ); ?></h1>
						<p class="silent-subtitle">Manage your website's silent behavior automations.</p>
					</div>
					<div class="silent-header-actions">
						<?php if ( 'automations' === $tab ) : ?>
							<button class="silent-btn silent-btn-primary" id="silent-open-builder">
								<span class="dashicons dashicons-plus"></span> New Automation
							</button>
						<?php endif; ?>
					</div>
				</header>

				<div class="silent-content">
					<?php
					switch ( $tab ) {
						case 'automations':
							$this->render_automations_tab();
							break;
						case 'settings':
							$this->render_settings_tab();
							break;
						case 'analytics':
							$this->render_analytics_tab();
							break;
						default:
							$this->render_overview_tab();
							break;
					}
					?>
				</div>
			</main>
		</div>
		<?php
	}

	private function render_overview_tab() {
		$tracker = Silent_Automation_Tracker::get_instance();
		$total_events = $tracker->get_total_events();
		$patterns = $tracker->get_detected_patterns();
		$active_rules = get_option( 'silent_automation_active_rules', array() );

		?>
		<div class="silent-grid">
			<div class="silent-card silent-stat-card">
				<div class="silent-card-icon"><span class="dashicons dashicons-visibility"></span></div>
				<div class="silent-card-data">
					<span class="silent-label">Total Events</span>
					<h3 class="silent-value"><?php echo number_format( $total_events ); ?></h3>
				</div>
			</div>
			<div class="silent-card silent-stat-card">
				<div class="silent-card-icon"><span class="dashicons dashicons-yes-alt"></span></div>
				<div class="silent-card-data">
					<span class="silent-label">Quick Rules</span>
					<h3 class="silent-value"><?php echo count( $active_rules ); ?></h3>
				</div>
			</div>
			<div class="silent-card silent-stat-card">
				<div class="silent-card-icon"><span class="dashicons dashicons-groups"></span></div>
				<div class="silent-card-data">
					<span class="silent-label">Insights</span>
					<h3 class="silent-value"><?php echo count( $patterns ); ?></h3>
				</div>
			</div>
		</div>

		<div class="silent-section">
			<div class="silent-section-header">
				<h2>Smart Suggestions</h2>
				<p>Patterns detected from recent visitor behavior.</p>
			</div>
			
			<div class="silent-card no-padding">
				<?php if ( empty( $patterns ) ) : ?>
					<div class="silent-empty-state">
						<span class="dashicons dashicons-search"></span>
						<p>No patterns detected yet. Tracking is active and collecting data.</p>
					</div>
				<?php else : ?>
					<table class="silent-table">
						<thead>
							<tr>
								<th>Pattern Type</th>
								<th>Condition</th>
								<th>Suggestion</th>
								<th class="text-right">Action</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $patterns as $pattern ) : 
								$rule_id = md5( $pattern['type'] . $pattern['page_url'] );
								$is_active = isset( $active_rules[ $rule_id ] );
								?>
								<tr>
									<td>
										<span class="silent-badge badge-info">
											<?php echo esc_html( ucfirst( str_replace('_', ' ', $pattern['type']) ) ); ?>
										</span>
									</td>
									<td><code><?php echo esc_html( $pattern['condition'] ); ?></code></td>
									<td><?php echo esc_html( $pattern['message'] ); ?></td>
									<td class="text-right">
										<button 
											class="silent-btn <?php echo $is_active ? 'silent-btn-secondary' : 'silent-btn-primary'; ?> silent-toggle-btn"
											data-rule-id="<?php echo esc_attr( $rule_id ); ?>"
											data-page-url="<?php echo esc_attr( $pattern['page_url'] ); ?>"
											data-type="<?php echo esc_attr( $pattern['type'] ); ?>"
										>
											<?php echo $is_active ? 'Deactivate' : 'Activate'; ?>
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

	private function render_automations_tab() {
		$automation_manager = Silent_Automation_Automation::get_instance();
		$automations = $automation_manager->get_automations('all');
		?>
		<div class="silent-automation-container">
			<!-- Builder (Hidden by default, toggled by JS) -->
			<div id="silent-builder-overlay" class="silent-modal" style="display:none;">
				<div class="silent-modal-content">
					<div class="silent-modal-header">
						<h2>Create Automation</h2>
						<button class="silent-modal-close">&times;</button>
					</div>
					<form id="silent-new-automation-form" class="silent-builder-form">
						<div class="silent-form-group">
							<label>Automation Name</label>
							<input type="text" name="name" required placeholder="e.g. Abandoned Cart Recovery">
						</div>
						
						<div class="silent-visual-flow">
							<div class="flow-step">
								<label>When this happens...</label>
								<select name="condition_type">
									<option value="high_intent">High Intent (Revisit)</option>
									<option value="engaged">Engaged (Time Spent)</option>
									<option value="cart_abandonment">Cart Abandonment</option>
								</select>
							</div>
							<div class="flow-arrow"><span class="dashicons dashicons-arrow-right-alt2"></span></div>
							<div class="flow-step">
								<label>Do this action...</label>
								<select name="action_type">
									<option value="popup">Show Popup</option>
									<option value="whatsapp">WhatsApp Trigger</option>
									<option value="email">Email Log</option>
								</select>
							</div>
						</div>

						<div class="silent-form-group">
							<label>Message / Content</label>
							<textarea name="message" required rows="4" placeholder="Enter the message users will see..."></textarea>
						</div>
						
						<div class="silent-modal-footer">
							<button type="button" class="silent-btn silent-btn-secondary silent-modal-close">Cancel</button>
							<button type="submit" class="silent-btn silent-btn-primary">Create Automation</button>
						</div>
					</form>
				</div>
			</div>

			<div class="silent-section">
				<div class="silent-section-header">
					<h2>Active Automations</h2>
					<p>Custom rules currently running on your site.</p>
				</div>

				<div class="silent-card no-padding">
					<?php if ( empty( $automations ) ) : ?>
						<div class="silent-empty-state">
							<span class="dashicons dashicons-plus-alt"></span>
							<p>No custom automations yet. Create your first one to boost conversions!</p>
							<button class="silent-btn silent-btn-primary" onclick="jQuery('#silent-open-builder').click()">Create Now</button>
						</div>
					<?php else : ?>
						<table class="silent-table">
							<thead>
								<tr>
									<th>Name</th>
									<th>Flow</th>
									<th>Status</th>
									<th class="text-right">Actions</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $automations as $auto ) : ?>
									<tr>
										<td><strong><?php echo esc_html( $auto->name ); ?></strong></td>
										<td>
											<div class="silent-flow-badge">
												<span><?php echo esc_html( $auto->condition_type ); ?></span>
												<span class="dashicons dashicons-arrow-right-alt2"></span>
												<span><?php echo esc_html( $auto->action_type ); ?></span>
											</div>
										</td>
										<td>
											<span class="silent-badge <?php echo $auto->status === 'active' ? 'badge-success' : 'badge-default'; ?>">
												<?php echo esc_html( ucfirst( $auto->status ) ); ?>
											</span>
										</td>
										<td class="text-right">
											<button class="silent-btn silent-btn-danger silent-delete-auto" data-id="<?php echo $auto->id; ?>">
												<span class="dashicons dashicons-trash"></span>
											</button>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}

	private function render_analytics_tab() {
		?>
		<div class="silent-empty-state">
			<span class="dashicons dashicons-chart-area"></span>
			<h2>Analytics Coming Soon</h2>
			<p>We are building deep insights to show you exactly how much revenue Silent Auto is generating.</p>
		</div>
		<?php
	}

	private function render_settings_tab() {
		$settings = get_option( 'silent_automation_settings', array() );
		?>
		<div class="silent-section max-w-md">
			<div class="silent-section-header">
				<h2>Global Settings</h2>
				<p>Configure your integration preferences.</p>
			</div>
			
			<div class="silent-card">
				<form id="silent-settings-form" class="silent-builder-form">
					<div class="silent-form-group">
						<label>WhatsApp Number</label>
						<input type="text" name="whatsapp_number" value="<?php echo esc_attr( $settings['whatsapp_number'] ); ?>" placeholder="e.g. 15551234567">
						<p class="description">Include country code without + or spaces.</p>
					</div>
					<div class="silent-form-actions">
						<button type="submit" class="silent-btn silent-btn-primary">Save Settings</button>
					</div>
				</form>
			</div>
		</div>
		<?php
	}

	public function save_automation() {
		check_ajax_referer( 'silent_admin_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

		$result = Silent_Automation_Automation::get_instance()->add_automation( $_POST );
		if ( $result ) wp_send_json_success();
		else wp_send_json_error();
	}

	public function delete_automation() {
		check_ajax_referer( 'silent_admin_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

		$result = Silent_Automation_Automation::get_instance()->delete_automation( $_POST['id'] );
		if ( $result ) wp_send_json_success();
		else wp_send_json_error();
	}

	public function save_settings() {
		check_ajax_referer( 'silent_admin_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

		$settings = array(
			'whatsapp_number' => sanitize_text_field( $_POST['whatsapp_number'] )
		);
		update_option( 'silent_automation_settings', $settings );
		wp_send_json_success();
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

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
		
		// Enqueue Chart.js
		wp_enqueue_script( 'chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '4.4.1', true );
		
		wp_enqueue_script( 'silent-admin-js', SILENT_AUTOMATION_URL . 'assets/js/admin.js', array( 'jquery', 'chart-js' ), SILENT_AUTOMATION_VERSION, true );
		
		$tracker = Silent_Automation_Tracker::get_instance();
		$chart_data = $tracker->get_events_by_day();
		
		wp_localize_script( 'silent-admin-js', 'silentAdmin', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'silent_admin_nonce' ),
			'chartData' => $chart_data
		) );
	}

	public function render_dashboard() {
		$tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'overview';
		?>
		<div class="sa-container">
			<!-- Header Section -->
			<header class="sa-header">
				<div class="sa-header-content">
					<h1>Silent Automation <span style="font-size: 12px; vertical-align: middle; background: var(--sa-accent-soft); color: var(--sa-accent); padding: 4px 8px; border-radius: 6px; margin-left: 8px;">v2.1 PRO</span></h1>
					<p>Intelligent behavior tracking & conversion engine.</p>
				</div>
				<div class="sa-actions">
					<button class="sa-btn sa-btn-secondary" id="silent-simulate-visit">
						<span class="dashicons dashicons-visibility"></span> Simulate Visit
					</button>
					<button class="sa-btn sa-btn-primary" id="silent-open-builder">
						<span class="dashicons dashicons-plus"></span> New Automation
					</button>
				</div>
			</header>

			<!-- Navigation Tabs -->
			<nav class="sa-nav">
				<a href="<?php echo admin_url('admin.php?page=silent-automation'); ?>" class="sa-nav-item <?php echo $tab === 'overview' ? 'active' : ''; ?>">Dashboard</a>
				<a href="<?php echo admin_url('admin.php?page=silent-automation&tab=automations'); ?>" class="sa-nav-item <?php echo $tab === 'automations' ? 'active' : ''; ?>">Automations</a>
				<a href="<?php echo admin_url('admin.php?page=silent-automation&tab=settings'); ?>" class="sa-nav-item <?php echo $tab === 'settings' ? 'active' : ''; ?>">Settings</a>
			</nav>

			<!-- Main Content Area -->
			<div class="sa-content">
				<?php
				switch ( $tab ) {
					case 'automations':
						$this->render_automations_tab();
						break;
					case 'settings':
						$this->render_settings_tab();
						break;
					default:
						$this->render_overview_tab();
						break;
				}
				?>
			</div>
		</div>

		<!-- Automation Builder Modal (Keep existing structure but style via CSS) -->
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
						<button type="button" class="sa-btn sa-btn-secondary silent-modal-close">Cancel</button>
						<button type="submit" class="sa-btn sa-btn-primary">Create Automation</button>
					</div>
				</form>
			</div>
		</div>
		<?php
	}

	private function render_overview_tab() {
		$tracker = Silent_Automation_Tracker::get_instance();
		$patterns = $tracker->get_detected_patterns();
		$active_rules = get_option( 'silent_automation_active_rules', array() );
		$automations = Silent_Automation_Automation::get_instance()->get_automations('all');
		$latest_events = $tracker->get_latest_events(8);
		
		$total_events = $tracker->get_total_events();
		$active_rules_count = count( $active_rules );
		$unique_visitors = $tracker->get_unique_visitors_count();

		?>
		<div class="sa-stats-grid">
			<div class="sa-stat-card">
				<span class="sa-stat-label">Total Events</span>
				<div class="sa-stat-value"><?php echo number_format( $total_events ); ?></div>
			</div>
			<div class="sa-stat-card">
				<span class="sa-stat-label">Active Automations</span>
				<div class="sa-stat-value"><?php echo number_format( $active_rules_count ); ?></div>
			</div>
			<div class="sa-stat-card">
				<span class="sa-stat-label">Unique Visitors</span>
				<div class="sa-stat-value"><?php echo number_format( $unique_visitors ); ?></div>
			</div>
		</div>

		<div class="sa-overview-grid">
			<!-- Main Analytics -->
			<div class="sa-chart-box">
				<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
					<h2 class="sa-section-title" style="margin: 0;">Activity Overview</h2>
					<span style="font-size: 12px; color: var(--sa-text-muted);">Last 7 Days</span>
				</div>
				<div class="sa-chart-wrapper">
					<canvas id="sa-activity-chart" height="300"></canvas>
				</div>
			</div>

			<!-- Live Feed -->
			<div class="sa-feed-box">
				<h2 class="sa-section-title">Live Stream</h2>
				<div class="sa-live-feed">
					<?php if ( empty( $latest_events ) ) : ?>
						<p style="color: #94a3b8; text-align: center; padding: 20px;">Waiting for activity...</p>
					<?php else : ?>
						<?php foreach ( $latest_events as $event ) : ?>
							<div class="sa-feed-item">
								<div class="sa-feed-icon">
									<span class="dashicons dashicons-<?php 
										echo $event->event_type === 'add_to_cart' ? 'cart' : 
											($event->event_type === 'checkout_completed' ? 'yes' : 'visibility'); 
									?>"></span>
								</div>
								<div class="sa-feed-content">
									<strong><?php echo esc_html( ucfirst( str_replace('_', ' ', $event->event_type) ) ); ?></strong>
									<div class="sa-feed-time"><?php echo human_time_diff( strtotime( $event->created_at ), current_time( 'timestamp' ) ); ?> ago</div>
								</div>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</div>

			<!-- Opportunities -->
			<div class="sa-opportunities-box">
				<h2 class="sa-section-title">Proactive Insights</h2>
				<div class="sa-opportunities-grid">
					<?php if ( empty( $patterns ) ) : ?>
						<div class="sa-stat-card" style="grid-column: 1 / -1; text-align: center; padding: 40px; background: transparent; border-style: dashed;">
							<p style="color: var(--sa-text-muted);">Analyzing visitor behavior... Insights will appear here.</p>
						</div>
					<?php else : ?>
						<?php foreach ( $patterns as $pattern ) : 
							$rule_id = md5( $pattern['type'] . $pattern['page_url'] );
							$is_active = isset( $active_rules[ $rule_id ] );
							$badge_class = strpos($pattern['type'], 'intent') !== false ? 'sa-badge-intent' : 'sa-badge-engaged';
							?>
							<div class="sa-suggestion-card">
								<span class="sa-badge <?php echo $badge_class; ?>">
									<?php echo esc_html( ucfirst( str_replace('_', ' ', $pattern['type']) ) ); ?>
								</span>
								<h3><?php echo esc_html( $pattern['condition'] ); ?></h3>
								<p><?php echo esc_html( $pattern['message'] ); ?></p>
								<button 
									class="sa-btn <?php echo $is_active ? 'sa-btn-secondary' : 'sa-btn-primary'; ?> silent-toggle-btn"
									data-rule-id="<?php echo esc_attr( $rule_id ); ?>"
									data-page-url="<?php echo esc_attr( $pattern['page_url'] ); ?>"
									data-type="<?php echo esc_attr( $pattern['type'] ); ?>"
									style="width: 100%;"
								>
									<?php echo $is_active ? 'Deactivate' : 'Activate Automation'; ?>
								</button>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}

	private function render_automations_tab() {
		$automations = Silent_Automation_Automation::get_instance()->get_automations('all');
		?>
		<div class="sa-chart-box" style="width: 100%; box-sizing: border-box;">
			<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
				<div>
					<h2 class="sa-section-title" style="margin: 0;">Active Automations</h2>
					<p style="color: var(--sa-text-muted); margin-top: 4px;">Manage your custom behavior-based triggers and actions.</p>
				</div>
				<button class="sa-btn sa-btn-primary" onclick="document.getElementById('silent-open-builder').click()">
					<span class="dashicons dashicons-plus"></span> New Automation
				</button>
			</div>
			
			<div class="sa-automations-list">
				<?php if ( empty( $automations ) ) : ?>
					<div style="text-align: center; padding: 60px; border: 1px dashed var(--sa-border); border-radius: var(--sa-radius-xl);">
						<span class="dashicons dashicons-plus-alt" style="font-size: 48px; width: 48px; height: 48px; color: var(--sa-text-muted); margin-bottom: 16px;"></span>
						<h3>No automations yet</h3>
						<p style="color: var(--sa-text-muted); margin-bottom: 24px;">Create your first automation to start converting visitors.</p>
					</div>
				<?php else : ?>
					<table class="sa-table">
						<thead>
							<tr>
								<th>Automation Name</th>
								<th>Condition</th>
								<th>Action</th>
								<th>Status</th>
								<th style="text-align: right;">Actions</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $automations as $auto ) : ?>
								<tr>
									<td>
										<div style="font-weight: 600; color: var(--sa-text-primary);"><?php echo esc_html( $auto->name ); ?></div>
										<div style="font-size: 11px; color: var(--sa-text-muted);">ID: #<?php echo $auto->id; ?></div>
									</td>
									<td>
										<span class="sa-badge sa-badge-engaged">
											<?php echo esc_html( ucfirst( str_replace('_', ' ', $auto->condition_type) ) ); ?>
										</span>
									</td>
									<td>
										<span class="sa-badge sa-badge-intent">
											<?php echo esc_html( ucfirst( $auto->action_type ) ); ?>
										</span>
									</td>
									<td>
										<span class="sa-status-badge <?php echo $auto->status === 'active' ? 'sa-status-active' : 'sa-status-paused'; ?>">
											<span class="sa-status-dot"></span>
											<?php echo esc_html( ucfirst( $auto->status ) ); ?>
										</span>
									</td>
									<td style="text-align: right;">
										<button class="sa-btn sa-btn-secondary silent-delete-auto" data-id="<?php echo $auto->id; ?>" style="padding: 8px;">
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
		<?php
	}

	private function render_analytics_tab() {
		?>
		<div class="sa-stat-card" style="text-align: center; padding: 60px;">
			<span class="dashicons dashicons-chart-area" style="font-size: 48px; width: 48px; height: 48px; color: var(--sa-text-muted); margin-bottom: 16px;"></span>
			<h2 class="sa-section-title">Analytics Coming Soon</h2>
			<p style="color: var(--sa-text-muted);">We are building deep insights to show you exactly how much revenue Silent Auto is generating.</p>
		</div>
		<?php
	}

	private function render_settings_tab() {
		$settings = get_option( 'silent_automation_settings', array() );
		?>
		<div class="sa-chart-box" style="max-width: 600px;">
			<h2 class="sa-section-title" style="margin-bottom: 24px;">Global Configuration</h2>
			
			<form id="silent-settings-form" class="silent-builder-form">
				<div class="silent-form-group">
					<label style="color: var(--sa-text-primary); font-weight: 600; display: block; margin-bottom: 8px;">WhatsApp Business Number</label>
					<div style="position: relative;">
						<span class="dashicons dashicons-whatsapp" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--sa-text-muted);"></span>
						<input type="text" name="whatsapp_number" value="<?php echo esc_attr( $settings['whatsapp_number'] ); ?>" placeholder="e.g. 15551234567" style="padding-left: 40px; width: 100%; box-sizing: border-box;">
					</div>
					<p class="description" style="margin-top: 8px; font-size: 12px; color: var(--sa-text-muted);">Include country code without + or spaces. This number will receive automation triggers.</p>
				</div>

				<div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid var(--sa-border);">
					<button type="submit" class="sa-btn sa-btn-primary" style="width: 100%; justify-content: center;">
						Update Settings
					</button>
				</div>
			</form>
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

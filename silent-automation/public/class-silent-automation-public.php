<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Silent_Automation_Public {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_footer', array( $this, 'render_popup_container' ) );
	}

	public function enqueue_assets() {
		wp_enqueue_style( 'silent-public-css', SILENT_AUTOMATION_URL . 'assets/css/public.css', array(), SILENT_AUTOMATION_VERSION );
		wp_enqueue_script( 'silent-tracker-js', SILENT_AUTOMATION_URL . 'assets/js/tracker.js', array(), SILENT_AUTOMATION_VERSION, true );
		
		$active_rules = get_option( 'silent_automation_active_rules', array() );
		
		wp_localize_script( 'silent-tracker-js', 'silentData', array(
			'apiUrl'      => esc_url_raw( rest_url( 'silent-automation/v1/track' ) ),
			'nonce'       => wp_create_nonce( 'wp_rest' ),
			'pageUrl'     => home_url( add_query_arg( array(), $GLOBALS['wp']->request ) ),
			'activeRules' => array_values( $active_rules )
		) );
	}

	public function render_popup_container() {
		echo '<div id="silent-automation-popup" class="silent-popup" style="display:none;">';
		echo '<div class="silent-popup-content">';
		echo '<span class="silent-close">&times;</span>';
		echo '<div class="silent-message"></div>';
		echo '</div>';
		echo '</div>';
	}
}

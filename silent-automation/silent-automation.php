<?php
/**
 * Plugin Name: Silent Automation
 * Plugin URI: https://example.com/silent-automation
 * Description: Tracks user behavior and suggests simple automations in the WordPress dashboard.
 * Version: 1.0.0
 * Author: Senior WordPress Engineer
 * License: GPL2
 * Text Domain: silent-automation
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define constants
define( 'SILENT_AUTOMATION_VERSION', '1.0.0' );
define( 'SILENT_AUTOMATION_PATH', plugin_dir_path( __FILE__ ) );
define( 'SILENT_AUTOMATION_URL', plugin_dir_url( __FILE__ ) );

/**
 * Main Plugin Class
 */
class Silent_Automation {

	/**
	 * Instance of this class.
	 * @var Silent_Automation
	 */
	private static $instance = null;

	/**
	 * Get instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Include required files
	 */
	private function includes() {
		require_once SILENT_AUTOMATION_PATH . 'includes/class-silent-automation-db.php';
		require_once SILENT_AUTOMATION_PATH . 'includes/class-silent-automation-api.php';
		require_once SILENT_AUTOMATION_PATH . 'includes/class-silent-automation-tracker.php';
		
		if ( is_admin() ) {
			require_once SILENT_AUTOMATION_PATH . 'admin/class-silent-automation-admin.php';
		} else {
			require_once SILENT_AUTOMATION_PATH . 'public/class-silent-automation-public.php';
		}
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		register_activation_hook( __FILE__, array( 'Silent_Automation_DB', 'create_tables' ) );
		
		add_action( 'plugins_loaded', array( $this, 'init_classes' ) );
	}

	/**
	 * Initialize classes
	 */
	public function init_classes() {
		Silent_Automation_API::get_instance();
		Silent_Automation_Tracker::get_instance();
		
		if ( is_admin() ) {
			Silent_Automation_Admin::get_instance();
		} else {
			Silent_Automation_Public::get_instance();
		}
	}
}

// Start the plugin
Silent_Automation::get_instance();

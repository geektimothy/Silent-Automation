<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Silent_Automation_API {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes() {
		register_rest_route( 'silent-automation/v1', '/track', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'handle_tracking' ),
			'permission_callback' => '__return_true', // Public tracking
		) );
	}

	public function handle_tracking( $request ) {
		$params = $request->get_params();

		if ( empty( $params['session_id'] ) || empty( $params['event_type'] ) ) {
			return new WP_Error( 'missing_params', 'Missing required parameters', array( 'status' => 400 ) );
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'silent_events';

		$data = array(
			'session_id' => sanitize_text_field( $params['session_id'] ),
			'page_url'   => esc_url_raw( $params['page_url'] ),
			'event_type' => sanitize_text_field( $params['event_type'] ),
			'value'      => intval( $params['value'] ),
			'created_at' => current_time( 'mysql' ),
		);

		$wpdb->insert( $table_name, $data );

		return rest_ensure_response( array( 'success' => true ) );
	}
}

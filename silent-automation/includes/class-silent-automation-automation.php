<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Silent_Automation_Automation {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	public function get_automations( $status = 'active' ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'silent_automations';
		
		if ( $status === 'all' ) {
			return $wpdb->get_results( "SELECT * FROM $table_name ORDER BY created_at DESC" );
		}

		return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE status = %s ORDER BY created_at DESC", $status ) );
	}

	public function add_automation( $data ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'silent_automations';

		return $wpdb->insert( $table_name, array(
			'name'           => sanitize_text_field( $data['name'] ),
			'condition_type' => sanitize_text_field( $data['condition_type'] ),
			'action_type'    => sanitize_text_field( $data['action_type'] ),
			'message'        => sanitize_textarea_field( $data['message'] ),
			'status'         => 'active',
			'created_at'     => current_time( 'mysql' ),
		) );
	}

	public function delete_automation( $id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'silent_automations';
		return $wpdb->delete( $table_name, array( 'id' => intval( $id ) ) );
	}
}

<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Silent_Automation_DB {

	/**
	 * Create custom tables on activation
	 */
	public static function create_tables() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		
		// Events Table
		$table_events = $wpdb->prefix . 'silent_events';
		$sql_events = "CREATE TABLE $table_events (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			session_id varchar(100) NOT NULL,
			page_url text NOT NULL,
			event_type varchar(50) NOT NULL,
			value int(11) DEFAULT 0,
			created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id),
			KEY session_id (session_id)
		) $charset_collate;";

		// Automations Table
		$table_automations = $wpdb->prefix . 'silent_automations';
		$sql_automations = "CREATE TABLE $table_automations (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			condition_type varchar(100) NOT NULL,
			action_type varchar(100) NOT NULL,
			message text NOT NULL,
			status varchar(20) DEFAULT 'active' NOT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql_events );
		dbDelta( $sql_automations );
		
		// Initialize options
		if ( false === get_option( 'silent_automation_settings' ) ) {
			add_option( 'silent_automation_settings', array(
				'whatsapp_number' => ''
			) );
		}
	}
}

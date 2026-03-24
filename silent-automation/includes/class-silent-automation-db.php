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
		$table_name = $wpdb->prefix . 'silent_events';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			session_id varchar(100) NOT NULL,
			page_url text NOT NULL,
			event_type varchar(50) NOT NULL,
			value int(11) DEFAULT 0,
			created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id),
			KEY session_id (session_id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
		
		// Initialize options for active automations
		if ( false === get_option( 'silent_automation_active_rules' ) ) {
			add_option( 'silent_automation_active_rules', array() );
		}
	}
}

<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Silent_Automation_Tracker {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	/**
	 * Get patterns detected from events
	 */
	public function get_detected_patterns() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'silent_events';
		
		$patterns = array();

		// Rule 1: High Intent (Visited same page 2+ times)
		$high_intent = $wpdb->get_results( "
			SELECT page_url, COUNT(*) as visit_count 
			FROM $table_name 
			WHERE event_type = 'visit' 
			GROUP BY page_url 
			HAVING visit_count >= 2
		" );

		foreach ( $high_intent as $row ) {
			$patterns[] = array(
				'type'      => 'high_intent',
				'page_url'  => $row->page_url,
				'condition' => 'Visited ' . $row->visit_count . ' times',
				'message'   => 'Users are revisiting your ' . $this->get_page_name($row->page_url) . ' page. Suggest showing a discount popup.'
			);
		}

		// Rule 2: Engaged (Time spent > 60 seconds)
		$engaged = $wpdb->get_results( "
			SELECT page_url, SUM(value) as total_time 
			FROM $table_name 
			WHERE event_type = 'time_spent' 
			GROUP BY page_url 
			HAVING total_time > 60
		" );

		foreach ( $engaged as $row ) {
			$patterns[] = array(
				'type'      => 'engaged',
				'page_url'  => $row->page_url,
				'condition' => 'Spent ' . $row->total_time . 's',
				'message'   => 'Users are highly engaged with ' . $this->get_page_name($row->page_url) . '. Suggest a newsletter signup.'
			);
		}

		return $patterns;
	}

	private function get_page_name($url) {
		$path = parse_url($url, PHP_URL_PATH);
		return $path ? trim($path, '/') : 'home';
	}
	
	public function get_total_events() {
		global $wpdb;
		return $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}silent_events" );
	}
}

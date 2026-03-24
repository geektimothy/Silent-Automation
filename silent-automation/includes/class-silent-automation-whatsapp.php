<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Silent_Automation_WhatsApp {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	public function get_whatsapp_url( $message ) {
		$settings = get_option( 'silent_automation_settings', array() );
		$number = isset( $settings['whatsapp_number'] ) ? $settings['whatsapp_number'] : '';
		
		if ( empty( $number ) ) {
			return '#';
		}

		return 'https://wa.me/' . preg_replace( '/[^0-9]/', '', $number ) . '?text=' . urlencode( $message );
	}
}

<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Silent_Automation_WooCommerce {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		add_action( 'woocommerce_add_to_cart', array( $this, 'track_add_to_cart' ), 10, 6 );
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'track_checkout' ) );
	}

	public function track_add_to_cart( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
		$this->log_wc_event( 'add_to_cart', $product_id );
	}

	public function track_checkout( $order_id ) {
		$this->log_wc_event( 'checkout_completed', $order_id );
	}

	private function log_wc_event( $type, $value = 0 ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'silent_events';

		$session_id = isset( $_COOKIE['silent_session_id'] ) ? sanitize_text_field( $_COOKIE['silent_session_id'] ) : 'wc_user_' . session_id();

		$wpdb->insert( $table_name, array(
			'session_id' => $session_id,
			'page_url'   => home_url( $_SERVER['REQUEST_URI'] ),
			'event_type' => $type,
			'value'      => intval( $value ),
			'created_at' => current_time( 'mysql' ),
		) );
	}
}

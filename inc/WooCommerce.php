<?php
/**
 * Admin Chat Box Rest Route
 *
 * This class is used to response and all rest route works
 *
 * @package WPNTS\Inc
 */

namespace WPNTS\Inc;

use WPNTS\Inc\nts_fs;
use WPNTS\Inc\Activate;
use WPNTS\Inc\Deactivate;
use WPNTS\Inc\Database\DB;
use WPNTS\Inc\SlackAttachment;

defined('ABSPATH') || die('Hey, what are you doing here? You silly human!');
/**
 * Update used to rest route created
 *
 * @since 1.0.0
 */
class WooCommerce {

	/**
	 * Construct method.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$wpnts_db_instance = new DB();
		$is_active = $wpnts_db_instance->is_pro_active();
		$schedules_int = get_option( 'wpnts_schedules_interval_woocommerce_settings');
		$schedules_interval = json_decode($schedules_int);

		$wpnts_stockoutofstocknotifications = $schedules_interval->stockoutofstocknotifications ?? 'false';
		
		$wpntspro_salesnotifications = $schedules_interval->salesnotifications ?? 'false';
		$wooproducteditornotify = $schedules_interval->wooproducteditornotify ?? 'false';
		$shipingstatus = $schedules_interval->shipingstatus ?? 'false';
		$billingshippingnotify = $schedules_interval->billingshippingnotify ?? 'false';

		// Product quantity status stock, out of stock Notifications to Slack.
		if ( true === $wpnts_stockoutofstocknotifications ) {
			add_action( 'woocommerce_product_set_stock', [ $this, 'wpnts_product_stock_notification' ], 10, 2 );
		}


		if ( true === $wpntspro_salesnotifications ) {
			add_action( 'woocommerce_order_status_changed', [ $this, 'sales_notification' ], 10, 4 );
		}

		if ( true === $wooproducteditornotify ) {
			add_action('save_post', [$this, 'notify_on_product_changes'], 10, 3);
		}

		if ( true === $shipingstatus ) {
			add_action('woocommerce_order_status_changed', [$this, 'notify_on_shipping_status_update'], 10, 4);
		}

		if ( true === $billingshippingnotify ) {
			add_action('woocommerce_saved_order_items', [$this, 'notify_on_address_update'], 10, 2);
		}

	}



	/**
	 * Product quantity status stock, out of stock Notifications to Slack.
	 */
	public function wpnts_product_stock_notification( $product_id ) {
		$product = wc_get_product( $product_id );
		$stock = $product->get_stock_quantity();

		$schedules_int = get_option( 'wpnts_schedules_interval_woocommerce_settings');
		$schedules_interval = json_decode($schedules_int);
		$wpnts_webhook = $schedules_interval->webhook;

		$slack_webhook_url = $wpnts_webhook;

		$message = '';
		if ( $stock <= 0 ) {
			$message = "Product '" . $product->get_name() . "' is out of stock.";
		} else {
			$message = "Product '" . $product->get_name() . "' is now back in stock with " . $stock . ' items available.';
		}

		$payload = json_encode( [ 'text' => $message ] );
		$args = [
			'body'        => $payload,
			'headers'     => [ 'Content-Type' => 'application/json' ],
			'timeout'     => '5',
			'sslverify'   => false,
		];
		$response = wp_remote_post( $slack_webhook_url, $args );

		$severity = "High";
		$event_type = "WooCommerce Stock";
		$time = current_time( 'mysql' );
		$activated_by = wp_get_current_user()->display_name;
		
		$attachmentHandler = new SlackAttachment();
		$ip = $attachmentHandler->get_user_ip();
    	$attachmentHandler->log_activity_to_db( $severity, $time, $ip, $activated_by, $event_type, $message  );

	}


	/**
	 * WordPress sales notification.
	 *
	 * @since 1.0.0
	 */
	public function sales_notification( $order_id, $old_status, $new_status, $order ) {

		$schedules_int = get_option( 'wpnts_schedules_interval_woocommerce_settings');
			$schedules_interval = json_decode($schedules_int);
			$wpntspro_webhook = $schedules_interval->webhook;

			$slack_webhook_url = $wpntspro_webhook;

			$user = $order->get_user();
    		$username = $user ? $user->display_name : 'Guest';

    		$message = 'Order #' . $order_id . ' has been updated from ' . $old_status . ' to ' . $new_status . ' for customer ' . $username . "\n";


			

			foreach ( $order->get_items() as $item_id => $item ) {
				$product = $item->get_product();
				$product_name = $product->get_name();
				$product_price = $product->get_price();
				$product_quantity = $item->get_quantity();
				$product_total = $product_price * $product_quantity;

				$message .= "\nProduct: " . $product_name . "\nQuantity: " . $product_quantity . "\nPrice: $" . $product_price . "\nTotal: $" . $product_total;
			}

			$message .= "\n\nTotal Sales: $" . $order->get_total() . "\nOrder Number: " . $order_id;

			$payload = json_encode( [ 'text' => $message ] );
			$args = [
				'body'        => $payload,
				'headers'     => [ 'Content-Type' => 'application/json' ],
				'timeout'     => '5',
				'sslverify'   => false,
			];

			$response = wp_remote_post( $slack_webhook_url, $args );

			$user = $order->get_user();
    		$username = $user ? $user->display_name : 'Guest';

			$severity = "Medium";
			$event_type = "WooCommerce Stock";
			$time = current_time( 'mysql' );
			
			$attachmentHandler = new SlackAttachment();
			$ip = $attachmentHandler->get_user_ip();
			$attachmentHandler->log_activity_to_db( $severity, $time, $ip, $username, $event_type, $message  );


	}



	/**
	 * WooCommerce Product editing
	 */
	
	 public function notify_on_product_changes($product_id, $product, $update) {
	
		if (is_a($product, 'WP_Post') && $product->post_type === 'product') {
			// Get the product object
			$product_object = wc_get_product($product_id);
	
			// Log message for product changes
			$log_message = $this->get_product_changes_message($product_object);
	
			$schedules_int = get_option('wpnts_schedules_interval_woocommerce_settings');
			$schedules_interval = json_decode($schedules_int);
			$wpntspro_webhook = $schedules_interval->webhook;
	
			// Send the notification to Slack if there are changes
			if (!empty($log_message)) {
				// Build payload for the Slack message
				$payload = json_encode(['text' => $log_message]);
	
				// Set up arguments for the wp_remote_post function
				$args = [
					'body'      => $payload,
					'headers'   => ['Content-Type' => 'application/json'],
					'timeout'   => '5',
					'sslverify' => false,
				];
	
				// Send the notification to Slack
				wp_remote_post($wpntspro_webhook, $args);

				$activated_by = wp_get_current_user()->display_name;
				$severity = "Medium";
				$event_type = "Product Edit";
				$time = current_time( 'mysql' );
				
				$attachmentHandler = new SlackAttachment();
				$ip = $attachmentHandler->get_user_ip();
				$attachmentHandler->log_activity_to_db( $severity, $time, $ip, $activated_by, $event_type, $log_message  );
			}
		}
	}
	
	private function get_product_changes_message($product_data) {
		$changes = [];
	
		// Check for changes in product title
		if ($product_data->get_name()) {
			$changes[] = ['Field' => 'Product title',  'Data' => $product_data->get_name()];
		}
	
		// Check for changes in product short description
		if ($product_data->get_short_description()) {
			$changes[] = ['Field' => 'Short description',  'Data' => $product_data->get_short_description()];
		}
	
		// Check for changes in product description
		if ($product_data->get_description()) {
			$changes[] = ['Field' => 'Description',  'Data' => $product_data->get_description()];
		}
	
		// Check for changes in product SKU
		if ($product_data->get_sku()) {
			$changes[] = ['Field' => 'SKU',  'Data' => $product_data->get_sku()];
		}
	
		// Check for changes in product stock
		if ($product_data->get_stock_quantity()) {
			$changes[] = ['Field' => 'Stock',  'Data' => $product_data->get_stock_quantity()];
		}
	
		// Check for changes in product weight
		if ($product_data->get_weight()) {
			$changes[] = ['Field' => 'Weight',  'Data' => $product_data->get_weight()];
		}
	
		// Check for changes in product regular price
		if ($product_data->get_regular_price()) {
			$changes[] = ['Field' => 'Regular Price',  'Data' => $product_data->get_regular_price()];
		}
	
		// Check for changes in product sale price
		if ($product_data->get_sale_price()) {
			$changes[] = ['Field' => 'Sale Price',  'Data' => $product_data->get_sale_price()];
		}
	
		// Check for changes in product image
		if ($product_data->get_image_id()) {
			$image = wp_get_attachment_image_src($product_data->get_image_id(), 'full');
			$changes[] = ['Field' => 'Product Image', 'Data' => $image[0]];
		}

		// Check for changes in product gallery
		$gallery_image_ids = $product_data->get_gallery_image_ids();
		if (!empty($gallery_image_ids)) {
			$gallery_images = [];
			foreach ($gallery_image_ids as $gallery_image_id) {
				$gallery_image = wp_get_attachment_image_src($gallery_image_id, 'full');
				$gallery_images[] = $gallery_image[0];
			}
			$changes[] = ['Field' => 'Product Gallery', 'Data' => implode(', ', $gallery_images)];
		}

		// Check for changes in product type (Simple or Variable)
		$changes[] = ['Field' => 'Product Type', 'Data' => $product_data->get_type()];

		// Check for changes in stock status
		$changes[] = ['Field' => 'Stock Status', 'Data' => $product_data->get_stock_status()];
	
		// Check for changes in product categories
		$categories = implode(', ', wp_get_post_terms($product_data->get_id(), 'product_cat', ['fields' => 'names']));
		$changes[] = ['Field' => 'Product Categories', 'Data' => $categories];
	
		// Check for changes in product tags
		$tags = implode(', ', wp_get_post_terms($product_data->get_id(), 'product_tag', ['fields' => 'names']));
		$changes[] = ['Field' => 'Product Tags', 'Data' => $tags];
	
		// Check for changes in product gallery (with clickable links)
		$gallery_images = [];
		foreach ($gallery_image_ids as $gallery_image_id) {
			$gallery_image = wp_get_attachment_image_src($gallery_image_id, 'full');
			$gallery_images[] = '<a href="' . esc_url($gallery_image[0]) . '">Click to view</a>';
		}
		$changes[] = ['Field' => 'Product Gallery', 'Data' => implode(', ', $gallery_images)];

		// Format changes into a table
		$table = "Listed product data has been updated\n<br>";
		$table .= "------------------------------------------------------\n<br>";
	
		foreach ($changes as $change) {
			$table .= sprintf("%s => %s \n", $change['Field'], $change['Data']);
		}
	
		return $table;
	}
	
	
	/**
	 * Shiping status change notification
	 */
	public function notify_on_shipping_status_update($order_id, $old_status, $new_status, $order) {
		if ($old_status !== $new_status) {
			// Get the order data
			$order_data = wc_get_order($order_id);
	
			// Log message for shipping status update
			$log_message = $this->get_shipping_status_update_message($order_data);
	
			// Send the notification to Slack if there are changes
			if (!empty($log_message)) {
				$this->send_slack_notification($log_message);

				$activated_by = wp_get_current_user()->display_name;
				$severity = "Medium";
				$event_type = "Shiping";
				$time = current_time( 'mysql' );
				
				$attachmentHandler = new SlackAttachment();
				$ip = $attachmentHandler->get_user_ip();
				$attachmentHandler->log_activity_to_db( $severity, $time, $ip, $activated_by, $event_type, $log_message  );

			}
		}
	}
	
	// Function to get shipping status update message
	private function get_shipping_status_update_message($order_data) {
		// Check for changes in shipping status
		$changes = [];
	
		// Example: Check for changes in order shipping status
		$changes[] = ['Field' => 'Shipping Status', 'Data' => $order_data->get_status()];
	
		// Format changes into a table
		$table = "Shipping status has been updated\n<br>";
		$table .= "------------------------------------------------------\n<br>";
	
		foreach ($changes as $change) {
			$table .= sprintf(" %s => %s \n", $change['Field'], $change['Data']);
		}
	
		return $table;
	}


	/**
	 * Billing and Shipping address change 
	 */

	public function notify_on_address_update($order_id, $args) {
		// Get the order object
		$order = wc_get_order($order_id);
	
		// Check if the billing or shipping address has changed
		$old_billing_address = $this->get_formatted_billing_address($order);
		$new_billing_address = $this->get_formatted_address_from_args($args, 'billing');
	
		$old_shipping_address = $this->get_formatted_shipping_address($order);
		$new_shipping_address = $this->get_formatted_address_from_args($args, 'shipping');
	
		$log_messages = []; // Array to hold log messages
	
		// Check if billing address has changed
		if ($old_billing_address !== $new_billing_address) {
			$log_messages[] = $this->get_address_update_message('Billing Address', $new_billing_address);
		}
	
		// Check if shipping address has changed
		if ($old_shipping_address !== $new_shipping_address) {
			$log_messages[] = $this->get_address_update_message('Shipping Address', $new_shipping_address);
		}
	
		// If any address was changed, log the activity
		if (!empty($log_messages)) {
			foreach ($log_messages as $message) {
				$this->send_slack_notification($message);
			}
	
			// Create a unique log message
			$activated_by = wp_get_current_user()->display_name;
			$severity = "Medium";
			$event_type = "Address Update";
			$time = current_time('mysql');
	
			$attachmentHandler = new SlackAttachment();
			$ip = $attachmentHandler->get_user_ip();
			
			// Combine log messages into a single log entry
			$log_message = "Order #{$order_id} address updated:\n" . implode("\n", $log_messages);
			
			$attachmentHandler->log_activity_to_db($severity, $time, $ip, $activated_by, $event_type, $log_message);
		}
	}
	
	
	// Function to get formatted billing or shipping address from order object
	private function get_formatted_address_from_args($args, $type) {
		$address_key = "_{$type}_";
		return WC()->countries->get_formatted_address([
			'first_name' => $args[$address_key . 'first_name'] ?? '',
			'last_name'  => $args[$address_key . 'last_name'] ?? '',
			'company'    => $args[$address_key . 'company'] ?? '',
			'address_1'  => $args[$address_key . '1'] ?? '',
			'address_2'  => $args[$address_key . '2'] ?? '',
			'city'       => $args[$address_key . 'city'] ?? '',
			'state'      => $args[$address_key . 'state'] ?? '',
			'postcode'   => $args[$address_key . 'postcode'] ?? '',
			'country'    => $args[$address_key . 'country'] ?? '',
			'email'      =>$args[$address_key . 'email'] ?? '',
			'phone'      => $args[$address_key . 'phone'] ?? '',
		]);
	}
	
	// Function to get formatted shipping address from order object
	private function get_formatted_shipping_address($order) {
		return $order->get_formatted_shipping_address();
	}
	
	// Function to get formatted billing address from order object
	private function get_formatted_billing_address($order) {
		return $order->get_formatted_billing_address();
	}
	
	// Function to get address update message

	private function get_address_update_message($address_type, $new_address) {
		// Replace <br/> with newline character
		$new_address = str_replace('<br/>', "\n", $new_address);
		
		// Replace <br> with newline character (in case Slack prefers this format)
		$new_address = str_replace('<br>', "\n", $new_address);
	
		return sprintf("Below %s update has been recorded\n%s\n", $address_type, $new_address);
	}


	private function send_slack_notification($message) {
        $schedules_int = get_option('wpnts_schedules_interval_woocommerce_settings');
        $schedules_interval = json_decode($schedules_int);
        $wpntspro_webhook = $schedules_interval->webhook;

        // Build payload for the Slack message
        $payload = json_encode(['text' => $message]);

        // Set up arguments for the wp_remote_post function
        $args = [
            'body'      => $payload,
            'headers'   => ['Content-Type' => 'application/json'],
            'timeout'   => '5',
            'sslverify' => false,
        ];

        // Send the notification to Slack
        wp_remote_post($wpntspro_webhook, $args);
    }


}

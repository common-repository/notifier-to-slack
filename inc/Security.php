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
 * Security used to rest route created
 *
 * @since 1.0.0
 */
class Security {

	/**
	 * Construct method.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$wpnts_db_instance = new DB();
		$schedules_int = get_option( 'wpnts_webhook_site_settings');
		$schedules_interval = json_decode($schedules_int);
		
		$wpnts_disable_file_editor = $schedules_interval->wpnts_disable_file_editor ?? 'false';
		$suspicious_requests = $schedules_interval->suspicious_requests ?? 'false';
		$rate_limit_ip = $schedules_interval->rate_limit_ip ?? 'false';
		$hide_wordpress_version = $schedules_interval->hide_wordpress_version ?? 'false';
		$x_powered_by = $schedules_interval->x_powered_by ?? 'false';


		//Disable Themes & Plugins Editor page
		if ( true === $wpnts_disable_file_editor ) {
			add_action('init', [$this, 'wpnts_disable_file_editor']);
		}

		// Monitoring Suspicious HTTP Requests
		if ( true === $suspicious_requests ) {
			add_action('init', [$this, 'wpnts_detect_suspicious_requests']);
		}

		// Bot Traffic Detection (Rate-Limiting)
		if ( true === $rate_limit_ip ) {
			add_action('init', [$this, 'wpnts_rate_limit_ip']);
		}

		//Hide WP version
		if ( true === $hide_wordpress_version ) {
			add_action('init', [$this, 'wpnts_hide_wordpress_version']);
        	add_filter('style_loader_src', [$this, 'wpnts_remove_version_from_assets'], 9999);
        	add_filter('script_loader_src', [$this, 'wpnts_remove_version_from_assets'], 9999);
		}
		// Hook into REST API responses to remove X-Powered-By
		if ( true === $x_powered_by ) {
			add_action('wp', [$this, 'wpnts_remove_x_powered_by_header' ]);
        	add_filter('rest_pre_serve_request', [$this, 'wpnts_remove_x_powered_by_for_rest']);
			//https://whatismyipaddress.com/ip/202.96.281.47. 
		}

	
		
	}


	/**
	 * Disable Themes and Plugins Editor
	 * Disables the built-in WordPress theme and plugin editor to prevent unauthorized changes to core files from the admin dashboard.
	 */
	public function wpnts_disable_file_editor( ) {
		if (!defined('DISALLOW_FILE_EDIT')) {
			define('DISALLOW_FILE_EDIT', true);
		}
	}

	/**
	 * Detect suspicious requests (basic example for SQL injection patterns)
	 * 
	 * Add suspicious strings to the URL query parameters that match the patterns in your function:
	 * SQL Injection example: http://localhost/wpxpertise/notifier/?id=union+select+1,2,3
	 * XSS example: http://localhost/wpxpertise/notifier/?q=<script>alert('XSS')</script>
	 * SQL Dropping example: http://localhost/wpxpertise/notifier/?cmd=drop+table+users
	 * Directory traversal example: http://localhost/wpxpertise/notifier/?file=../../etc/passwd
	 * 
	 * Scans incoming URL requests for potential malicious patterns like SQL injection, XSS, and directory traversal. If a suspicious pattern is detected, it logs the activity and sends an alert to Slack.
	 * 
	 */
	public function wpnts_detect_suspicious_requests() {
			
		$request_uri = $_SERVER['REQUEST_URI'];
		$query_string = $_SERVER['QUERY_STRING'];

		// Define patterns for potential attacks (SQL injection, XSS, etc.)
		$patterns = [
			'/union.*select/i',       // SQL Injection
			'/<script.*?>/i',         // XSS Injection
			'/drop.*table/i',         // SQL Dropping
			'/\.\.\/\.\.\//',         // Directory traversal
		];

		foreach ($patterns as $pattern) {
			if (preg_match($pattern, $request_uri) || preg_match($pattern, $query_string)) {
				
				// Log the event
				$severity = "High";
				$event_type = "Suspicious";
				$current_user = wp_get_current_user();
				$author = ($current_user && !empty($current_user->display_name)) ? $current_user->display_name : "Unknown";
				$time = current_time( 'mysql' );

				$attachmentHandler = new SlackAttachment();
				$ip = $attachmentHandler->get_user_ip();


				$log_message = "⚠️Suspicious activity detected from ➡️IP $ip . Possible hacking attempt. <br> Request URI: $request_uri";

				// Send notification to Slack
				$this->wpnts_notify_slack($log_message, ":warning:");

				$attachmentHandler->log_activity_to_db_pro( $severity, $time, $ip, $author, $event_type, $log_message  );

				// Show an alert in the admin area.
				add_action('admin_notices', function() use ($log_message) {
					echo "<div class='notice notice-error'><p>$log_message</p></div>";
				});

				break;
			}
		}
	}


	/**
	 * Bot Detection and Rate Limiting
	 * Detects excessive requests from bots and applies rate-limiting. 
	 * If an IP makes more than 200 requests per minute, it is temporarily blocked for 30 minutes, and a Slack notification is triggered.
	 */

	public function wpnts_rate_limit_ip()  {
		$current_user = wp_get_current_user();
	
		// Bypass rate limiting for logged-in users or admins.
		if (is_user_logged_in()) {
			return;
		}
	
		// Exclude requests for certain file types from rate-limiting.
		$excluded_file_types = array_flip(['js', 'css', 'jpg', 'jpeg', 'png', 'gif', 'svg', 'woff', 'woff2', 'ttf']);
		$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
		$extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
	
		if (isset($excluded_file_types[$extension])) {
			return; 
		}
	
		// Retrieve dynamic rate limit settings
		$schedules_int = get_option('wpnts_webhook_site_settings');
		$schedules_interval = json_decode($schedules_int);
		
		// Use dynamic values for blocked requests and block duration
		$blockedRequests = !empty($schedules_interval->blockedRequests) ? intval($schedules_interval->blockedRequests) : 200; 
		$blockedminutes = !empty($schedules_interval->blockedminutes) ? intval($schedules_interval->blockedminutes) : 30;
	
		$attachmentHandler = new SlackAttachment();
		$ip_address = $attachmentHandler->get_user_ip();
		
		// Check if the IP is currently blocked (for dynamic minutes)
		if (get_transient("wpnts_bots_blocked_ip_$ip_address")) {
			header('HTTP/1.1 429 Too Many Requests');
			exit('You are temporarily blocked due to excessive requests.');
		}
	
		// Count the number of requests within 1 minute
		$request_count = get_transient("wpnts_request_count_$ip_address") ?: 0;
		set_transient("wpnts_request_count_$ip_address", $request_count + 1, 60); // Track requests per minute
	
		// Check if the request count exceeds the dynamic limit
		if ($request_count > $blockedRequests) { 
			$log_message = "Suspicious bot detected with more than $blockedRequests requests per minute from IP $ip_address. Rate-limiting applied and blocked for $blockedminutes minutes.";
			
			// Send a real-time alert to Slack or Email
			$this->wpnts_notify_slack($log_message, ":robot:");
	
			// Log the event
			$current_user = wp_get_current_user();
			$author = ($current_user && !empty($current_user->display_name)) ? $current_user->display_name : "Unknown";
			$severity = "Critical";
			$event_type = "Security";
			
			$attachmentHandler = new SlackAttachment();
			$ip = $attachmentHandler->get_user_ip();
			$attachmentHandler->log_activity_to_db($severity, current_time('mysql'), $ip, $author, $event_type, $log_message);
	
			// Block the IP for the dynamic time (in minutes)
			set_transient("wpnts_bots_blocked_ip_$ip_address", true, 60 * $blockedminutes); // Block for dynamic minutes
	
			// Send the HTTP 429 response
			header('HTTP/1.1 429 Too Many Requests');
			exit("Rate limit exceeded. You are blocked for $blockedminutes minutes. Please try again later.");
		}
	}

	

	 /**
     * Hide WordPress Version from Meta Tags and Feeds
	 * Removes the WordPress version from the HTML meta tags and RSS feeds to reduce exposure to potential vulnerabilities.
     *
     * @since 1.0.0
     */
	public function wpnts_hide_wordpress_version() {
		// Remove WordPress version from meta tag in head
		remove_action('wp_head', 'wp_generator');
		// Remove WordPress version from RSS feeds
		add_filter('the_generator', '__return_null');
	}


	/**
     * Remove WordPress Version from Enqueued Scripts and Styles
     *
     * @param string $src The source URL of the asset.
     * @return string
     * @since 1.0.0
     */
    public function wpnts_remove_version_from_assets($src) {
        if (strpos($src, 'ver=') !== false) {
			$src = remove_query_arg('ver', $src);
		}
		return $src;
    }

    /**
     * Hide PHP version from HTTP headers
     * Removes the X-Powered-By header from HTTP responses to hide the PHP version, reducing potential security risks.
     * @since 1.0.0
     */

    public function wpnts_remove_x_powered_by_header() {
        if (function_exists('header_remove')) {
			header_remove('X-Powered-By'); // For Apache
		}
    }

    /**
     * Remove the X-Powered-By header for REST API responses
     *
     * @param mixed $result The result to send to the client
     * @return mixed
     */
    public function wpnts_remove_x_powered_by_for_rest($result) {
		if (function_exists('header_remove')) {
			// Remove X-Powered-By for REST API responses
			header_remove('X-Powered-By'); 
		}
		return $result;
    }



	/**
	 * Slack notification helper.
	 */
	private function wpnts_notify_slack($message, $emoji = '') {
        $schedules_int = get_option('wpnts_webhook_site_settings');
        $schedules_interval = json_decode($schedules_int);
        $wpnts_webhook = $schedules_interval->webhook;
        $slack_webhook_url = $wpnts_webhook;

        $message = $emoji . ' ' . $message;
        $payload = json_encode(['text' => $message]);
        $args = [
            'body'      => $payload,
            'headers'   => ['Content-Type' => 'application/json'],
            'timeout'   => '5',
            'sslverify' => false,
        ];
        $response = wp_remote_post($slack_webhook_url, $args);
    }
	
}

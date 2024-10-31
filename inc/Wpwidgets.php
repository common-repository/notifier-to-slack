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
 * Wpwidgets used to rest route created
 *
 * @since 1.0.0
 */
class Wpwidgets {

	/**
	 * Construct method.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$wpnts_db_instance = new DB();
		$schedules_int = get_option( 'wpntswebhook_widget_settings');
		$schedules_interval = json_decode($schedules_int);
		
		$save_widget = $schedules_interval->save_widget ?? 'false';
		$delete_widget = $schedules_interval->delete_widget ?? 'false';

		// Track widget save/update
        if (true === $save_widget) {
            add_action('widget_update_callback', [$this, 'wpnts_widget_updated'], 10, 3);
            add_action('wp_ajax_widgets-order', [$this, 'wpnts_widget_added']);
        }

        // Track widget delete
        if (true === $delete_widget) {
            add_action('wp_ajax_delete_widget', [$this, 'wpnts_hook_widget_delete']);
        }

		
		
	}

	
	 /**
     * WordPress widget updated notification.
     *
     * @since 1.0.0
     */
    public function wpnts_widget_updated($instance, $new_instance, $old_instance) {
        $widget_id = sanitize_text_field($instance->id_base);
        $sidebar_id = sanitize_text_field($_POST['sidebar']);

        // Get the current user's details
        $user_id = get_current_user_id();
        $author = get_the_author_meta('display_name', $user_id);

        $this->wpnts_notify_slack("Widget '{$widget_id}' updated by '{$author}'.", ":package:");

        $log_message = "Widget <b>'$widget_id'</b> updated by <b>$author</b>.";
        $this->log_to_db($log_message, 'Widget', 'High', $author);
        
        return $instance;
    }

    /**
     * WordPress widget added notification.
     *
     * @since 1.0.0
     */
    public function wpnts_widget_added() {
        $widget_id = sanitize_text_field($_POST['widget-id']);

        // Get the current user's details
        $user_id = get_current_user_id();
        $author = get_the_author_meta('display_name', $user_id);

        $this->wpnts_notify_slack("Widget '{$widget_id}' added by '{$author}'.", ":package:");

        $log_message = "Widget <b>'$widget_id'</b> added by <b>$author</b>.";
        $this->log_to_db($log_message, 'Widget', 'High', $author);
    }

    /**
     * WordPress widget deleted notification.
     *
     * @since 1.0.0
     */
    public function wpnts_hook_widget_delete() {
        check_ajax_referer('delete-widget', 'nonce');  // Ensure it's a valid request

        $widget_id = sanitize_text_field($_POST['widget-id']);

        // Get the current user's details
        $user_id = get_current_user_id();
        $author = get_the_author_meta('display_name', $user_id);

        $this->wpnts_notify_slack("Widget '{$widget_id}' removed by '{$author}'.", ":package:");

        $log_message = "Widget <b>'$widget_id'</b> removed by <b>$author</b>.";
        $this->log_to_db($log_message, 'Widget', 'High', $author);
    }

    /**
     * Helper function to log activity to the database.
     *
     * @since 1.0.0
     */
    private function log_to_db($message, $event_type, $severity, $author) {
        $time = current_time('mysql');

        // Get IP Address
        $attachmentHandler = new SlackAttachment();
        $ip = $attachmentHandler->get_user_ip();

        // Log activity to the database
        $attachmentHandler->log_activity_to_db($severity, $time, $ip, $author, $event_type, $message);
    }

	
	/**
	 * Slack notification helper.
	 */
	private function wpnts_notify_slack($message, $emoji = '') {
        $schedules_int = get_option('wpntswebhook_media_settings');
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

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
 * Menu used to rest route created
 *
 * @since 1.0.0
 */
class Menu {

	/**
	 * Construct method.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$wpnts_db_instance = new DB();
		$schedules_int = get_option( 'wpntswebhook_menu_settings');
		$schedules_interval = json_decode($schedules_int);
		
		$create_nav_menu = $schedules_interval->create_nav_menu ?? 'false';
		$delete_nav_menu = $schedules_interval->delete_nav_menu ?? 'false';
		$update_nav_menu = $schedules_interval->update_nav_menu ?? 'false';
		

        if ( true === $create_nav_menu ) {
			add_action('wp_create_nav_menu', [$this, 'wpnts_menu_created'], 10, 1);
		}

		if ( true === $delete_nav_menu ) {
			add_action('wp_delete_nav_menu', [$this, 'wpnts_menu_deleted'], 10, 1);
		}
		
		if ( true === $update_nav_menu ) {
			add_action('wp_update_nav_menu', [$this, 'wpnts_menu_updated'], 10, 1);
		}
		
	}

	/**
	 * WordPress media file add/upload notification.
	 *
	 * @since 1.0.0
	 */

	 public function wpnts_menu_created($menu_id) {
        // Get the menu name
		$menu_name = wp_get_nav_menu_object($menu_id)->name;

		// Get the current user's details
		$user_id = get_current_user_id();
		$author = get_the_author_meta('display_name', $user_id);

		$this->wpnts_notify_slack("Menu '{$menu_name}' has been created by {$author}.", ":wastebasket:");

		$log_message = "Menu <b>'$menu_name'</b> has been created by <b>{$author}</b>.";
		$severity = "High";
		$event_type = "Menu";
		$time = current_time( 'mysql' );
		
		$attachmentHandler = new SlackAttachment();
		$ip = $attachmentHandler->get_user_ip();
    	$attachmentHandler->log_activity_to_db( $severity, $time, $ip, $author, $event_type, $log_message  );

    }

    /**
	 * WordPress media file delete notification.
	 *
	 * @since 1.0.0
	 */

	 public function wpnts_menu_updated($menu_id) {
       // Get the menu name
	   $menu_name = wp_get_nav_menu_object($menu_id)->name;

		// Get the current user's details
		$user_id = get_current_user_id();
		$author = get_the_author_meta('display_name', $user_id);

		$this->wpnts_notify_slack("Menu '{$menu_name}' has been updated by {$author}.", ":wastebasket:");

		$log_message = "Menu <b>'$menu_name'</b> has been updated by <b>{$author}</b>.";
		$severity = "High";
		$event_type = "Menu";
		$time = current_time( 'mysql' );
		
		$attachmentHandler = new SlackAttachment();
		$ip = $attachmentHandler->get_user_ip();
    	$attachmentHandler->log_activity_to_db( $severity, $time, $ip, $author, $event_type, $log_message  );

    }



	/**
	 * WordPress media file delete notification.
	 *
	 * @since 1.0.0
	 */

	 public function wpnts_menu_deleted($menu_id) {
		// Attempt to get the menu object
		$menu_object = wp_get_nav_menu_object($menu_id);
	
		// Get the current user's details
		$user_id = get_current_user_id();
		$author = get_the_author_meta('display_name', $user_id);
	
		if ($menu_object) {
			// Menu object exists, get the menu name.
			$menu_name = $menu_object->name;
			$this->wpnts_notify_slack("Menu '{$menu_name}' has been deleted by {$author}.", ":wastebasket:");
			$log_message = "Menu <b>'$menu_name'</b> has been deleted by <b>{$author}</b>.";
		} else {
			// Menu object does not exist, notify using the ID
			$this->wpnts_notify_slack("Menu with ID '{$menu_id}' has been deleted by {$author}.", ":wastebasket:");
			$log_message = "Menu with ID <b>'$menu_id'</b> has been deleted by <b>{$author}</b>.";
		}
	
		// Log the activity
		$severity = "High";
		$event_type = "Menu";
		$time = current_time('mysql');
	
		$attachmentHandler = new SlackAttachment();
		$ip = $attachmentHandler->get_user_ip();
		$attachmentHandler->log_activity_to_db($severity, $time, $ip, $author, $event_type, $log_message);
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

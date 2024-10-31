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

defined('ABSPATH') || die('Hey, what are you doing here? You silly human!');
/**
 * Security used to rest route created
 *
 * @since 1.0.0
 */
class SlackAttachment {
	private $attachments = [];

	/**
	 * For ratting review and support
	 */
	public function addAttachment( $plugin_name, $title, $ticket_description, $link, $date, $color, $emoji, $rating = null ) {

		$fields = [
			[
				'title' => "Name: $plugin_name",
				'short' => false,
			],
			[
				'title' => "Title: $title $emoji",
				'short' => false,
			],
			[
				'value' => "Description: $ticket_description",
				'short' => false,
			],
			[
				'value' => "Link: <{$link}|view>",
				'short' => false,
			],
			[
				'value' => "Date: $date",
				'short' => false,
			],
		];

		if ( $rating !== null ) {
			$fields[] = [
				'value' => str_repeat(':star:', $rating),
				'short' => false,
			];
		}

		$attachment = [
			'fallback' => $title,
			'color' => $color,
			'fields' => $fields,
		];

		$this->attachments[] = $attachment;
	}



	/**
	 * For attatchment call of ratting and review.
	 */
	public function getMessage() {
		return [
			'attachments' => $this->attachments,
		];
	}


	/**
	 * For Plugin Update notification
	 */

	public function addPluginUpdateNotification( $plugin_name, $new_version, $emoji = ':clap:', $color = '#00FF00' ) {
		$message = "$emoji A new version of the $plugin_name plugin is available for update ($new_version).";

		$fields = [
			[
				'title' => $message,
				'short' => false,
			],
		];

		$attachment = [
			'fallback' => $message,
			'color' => $color,
			'fields' => $fields,
		];

		$this->attachments[] = $attachment;
	}


	/**
	 * Add function to send notification via email
	 */
	public function sendEmailNotification( $to, $subject, $notification_message ) {
		$timestamp = current_time('mysql');

		// Customize the email message as needed
		$message = '<html><body style="background-color: #f4f4f4; padding: 20px; font-family: Arial, sans-serif;">';
		$message .= '<div style="background-color: #ffffff; max-width: 600px; margin: 0 auto; padding: 20px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">';

		// Include the date
		$message .= '<h3 style="color: #666; text-align: center;">Site Notification: ' . esc_html($timestamp) . '</h3>';
		$message .= '<p style="color: #666;">Time: ' . esc_html($timestamp) . '</p>';

		// Include the activation details
		$message .= '<h5>' . esc_html($notification_message) . ' </h5><br>';

		$message .= '<p style="color: #666;">Powered by <a href="https://wordpress.org/plugins/notifier-to-slack/" target="_blank">Notifier To Slack</a>.</p>';
		$message .= '</div></body></html>';

		// Email headers
		$headers[] = 'Content-Type: text/html; charset=UTF-8';

		// Send the email
		wp_mail($to, $subject, $message, $headers);
	}



	/**
	 * Send to DB
	 */

	 public function log_activity_to_db( $severity, $time, $ip, $current_user_name, $event_type, $log_message ) {
		global $wpdb;
		$activity_log_table = $wpdb->prefix . 'wpnts_activity_log';

		// Get the Gravatar URL for the current user
		$user = get_user_by( 'login', $current_user_name );
		$thumb = ($user && $user->user_email) ? get_avatar_url( $user->user_email, ['size' => 64] ) : null;

		// Get the primary role of the user
		$user_role = '';
		if ( $user && !empty( $user->roles ) ) {
			$user_role = ucfirst( $user->roles[0] );
		} else {
			$user_role = 'Unknown';
		}

		
		// Prepare the data to be inserted
		$data = [
			'severity'   => $severity,
			'date'       => $time,
			'ip'         => $ip,
			'user'         => $current_user_name,
			'event_type' => $event_type,
			'message'    => $log_message,
			'thumb'      => $thumb,
			'user_role'       => $user_role,
		];
		
		// Insert the data into the database
		$wpdb->insert( $activity_log_table, $data );
	}

	public function get_user_ip() {

		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $user_ip = sanitize_text_field(wp_unslash($_SERVER['HTTP_CLIENT_IP']));
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $user_ip = sanitize_text_field(wp_unslash($_SERVER['HTTP_X_FORWARDED_FOR']));
        } else {
            if (isset($_SERVER['REMOTE_ADDR'])) {
                $user_ip = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']));
            }
        }

        return $user_ip;
    }


}

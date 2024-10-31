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
 * Media used to rest route created
 *
 * @since 1.0.0
 */
class Media {

	/**
	 * Construct method.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$wpnts_db_instance = new DB();
		$schedules_int = get_option( 'wpntswebhook_media_settings');
		$schedules_interval = json_decode($schedules_int);
		
		$newmediaaddnotice = $schedules_interval->newmediaaddnotice ?? 'false';
		$mediadelete = $schedules_interval->mediadelete ?? 'false';
		$trackEmail = $schedules_interval->trackEmail ?? 'false';
		

        if ( true === $newmediaaddnotice ) {
			add_action('add_attachment', [$this, 'wpnts_media_attachment_added']);
		}

		if ( true === $mediadelete ) {
			add_action('delete_attachment', [$this, 'wpnts_media_attachment_deleted']);
		}
		if ( true === $trackEmail ) {
			add_filter('wp_mail', [$this, 'wpnts_log_email_sent']);
			add_action('wp_mail_failed', [$this, 'wpnts_log_email_failed']);
		}
		
	}

	/**
	 * WordPress media file add/upload notification.
	 *
	 * @since 1.0.0
	 */

	 public function wpnts_media_attachment_added($attachment_id) {
        $attachment_title = get_the_title($attachment_id);
        $author = get_user_by('ID', get_post_field('post_author', $attachment_id))->display_name;
        $this->wpnts_notify_slack("New media file named '{$attachment_title}' uploaded by *{$author}*", ":camera:");

		$log_message = "New media file named <b>'$attachment_title'</b> has been uploaded by 👉 <b>$author.</b>";
		$severity = "High";
		$event_type = "Media";
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

    public function wpnts_media_attachment_deleted($attachment_id) {
        $attachment_title = get_the_title($attachment_id);
        $author = get_user_by('ID', get_post_field('post_author', $attachment_id))->display_name;
        $this->wpnts_notify_slack("Media file named '{$attachment_title}' deleted by *{$author}*", ":wastebasket:");

		$log_message = "Media file named <b>'$attachment_title'</b> has been deleted by 👉 <b>$author.</b>";
		$severity = "High";
		$event_type = "Media";
		$time = current_time( 'mysql' );
		
		$attachmentHandler = new SlackAttachment();
		$ip = $attachmentHandler->get_user_ip();
    	$attachmentHandler->log_activity_to_db( $severity, $time, $ip, $author, $event_type, $log_message  );

    }


	/**
	 * Log successful email sends.
	 *
	 * @param array $args Email arguments.
	 * @return array Modified email arguments.
	 */
	public function wpnts_log_email_sent($args) {
		// Extract email details
		$to      = is_array($args['to']) ? implode(', ', $args['to']) : $args['to'];
		$subject = $args['subject'];
		$message = $args['message'];
		$headers = $args['headers'];
		$attachments = $args['attachments'];
	
		// Get current user
		$current_user = wp_get_current_user();
		$sender = $current_user->user_email ? $current_user->user_email : 'System';
	
		// Construct log message
		$log_message = "Email sent from <b>'$sender'</b> to <b>'$to'</b><br>";
		$log_message .= "<b>Subject:</b> $subject<br>";
		$log_message .= "<b>Message:</b> $message<br>";
		if (!empty($attachments)) {
			$log_message .= "<b>Attachments:</b> " . implode(', ', $attachments) . "<br>";
		}
	
		// Log the email event to the database
		$severity = "Medium";
		$event_type = "Email";
		$time = current_time('mysql');
		
		// Get user's IP address
		$attachmentHandler = new SlackAttachment(); // Assuming this is your helper class
		$ip = $attachmentHandler->get_user_ip();
		// Log the activity to the database
		$attachmentHandler->log_activity_to_db($severity, $time, $ip, $sender, $event_type, $log_message);
		
		// Optional: Notify via Slack
		$this->wpnts_notify_slack("Email sent success from *{$sender}* to *{$to}*", ":email:");
		
		// Return the original args to continue the email sending process
		return $args;
	}


	/**
	 * Log failed email attempts.
	 *
	 * @param WP_Error $error Error object with details about the failure.
	 */
	public function wpnts_log_email_failed($error) {
		// Extract email details from the error data
		$mail_data = $error->get_error_data();
		$to = is_array($mail_data['to']) ? implode(', ', $mail_data['to']) : $mail_data['to'];
		$subject = $mail_data['subject'];
		$message = $mail_data['message'];
		$headers = $mail_data['headers'];
		$attachments = isset($mail_data['attachments']) ? implode(', ', $mail_data['attachments']) : 'No Attachments';

		// Get current user
		$current_user = wp_get_current_user();
		$sender = $current_user->user_email ? $current_user->user_email : 'System';

		// Extract error details
		$error_message = $error->get_error_message();
		
		// Construct log message
		$log_message = "Failed to send email from <b>'$sender'</b> to <b>'$to'</b><br>";
		$log_message .= "<b>Subject:</b> $subject<br>";
		$log_message .= "<b>Message:</b> $message<br>";
		$log_message .= "<b>Headers:</b> $headers<br>";
		$log_message .= "<b>Attachments:</b> $attachments<br>";
		$log_message .= "<b>Error:</b> $error_message<br>";

		// Log the email event to the database
		$severity = "High"; // Marking email failures with a higher severity
		$event_type = "Email Failure";
		$time = current_time('mysql');
		
		// Get user's IP address
		$attachmentHandler = new SlackAttachment(); // Assuming this is your helper class
		$ip = $attachmentHandler->get_user_ip();
		
		// Log the activity to the database
		$this->log_activity_to_db($severity, $time, $ip, $sender, $event_type, $log_message);

		// Optional: Notify via Slack about the failure
		$this->wpnts_notify_slack("Failed to send email from *{$sender}* to *{$to}* with subject '{$subject}'. Error: {$error_message}", ":x:");
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

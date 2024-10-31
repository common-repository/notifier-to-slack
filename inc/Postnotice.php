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
 * Postnotice used to rest route created
 *
 * @since 1.0.0
 */
class Postnotice {

	/**
	 * Construct method.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$wpnts_db_instance = new DB();
		$schedules_int = get_option( 'wpntswebhook_pagenpost_settings');
		$schedules_interval = json_decode($schedules_int);

		$newpagecreate = $schedules_interval->newpagecreate ?? 'false';
		$deletepostnpage = $schedules_interval->deletepostnpage ?? 'false';
		
		if ( true === $newpagecreate ) {
			add_action('save_post', [$this, 'wpnts_send_page_notification'], 10, 3);
		}
		
		if ( true === $deletepostnpage ) {
			add_action('before_delete_post', [ $this,'wpnts_send_post_deletion_notification' ]);
		}
		
	}


	/**
	 * Plugin activation notification.
	 *
	 * @since 1.0.0
	 */

	 public function wpnts_send_page_notification($post_ID, $post, $update) {
        if ('page' === $post->post_type) {

            $post_status = get_post_status($post_ID);
            $author = get_user_by('ID', $post->post_author)->display_name;

            /**
             * Plugin activity log
             */
            $severity = "Medium";
            $event_type = "Page";
            $time = current_time( 'mysql' );
            
            switch ($post_status) {
                case 'draft':
                    $this->wpnts_notify_slack("Page named ➡️ '{$post->post_title}' saved as draft by {$author}", ":pencil:");
                    $log_message = "Page named ➡️ '{$post->post_title}' saved as <b>draft</b> by <b>$author</b>";
                    $attachmentHandler = new SlackAttachment();
                    $ip = $attachmentHandler->get_user_ip();
                    $attachmentHandler->log_activity_to_db( $severity, $time, $ip, $author, $event_type, $log_message  );
                    
                    break;
                case 'publish':
                    $post_creation_time = strtotime($post->post_date);
                    $current_time = current_time('timestamp', false);
        
                    // Check if the post was recently updated (within the last minute)
                    $is_recently_updated = ($current_time > $post_creation_time);
        
                    if ($is_recently_updated) {
                        // Post modified
                        $this->wpnts_notify_slack("Page named ➡️ '{$post->post_title}' has been modified by {$author}", ":pencil:");

                        $log_message = "Page named ➡️ '{$post->post_title}' has been <b>modified</b> by <b>$author</b>";
                        $attachmentHandler = new SlackAttachment();
                        $ip = $attachmentHandler->get_user_ip();
                        $attachmentHandler->log_activity_to_db( $severity, $time, $ip, $author, $event_type, $log_message  );
                        
                    } else {
                        // Post published
                        $permalink = get_permalink($post_ID);
                        $this->wpnts_notify_slack("Page named ➡️ '{$post->post_title}' has been published by {$author}. View page: {$permalink}", ":rocket:");

                        $log_message = "Page named ➡️ '{$post->post_title}' has been <b>published</b> by <b>$author</b>";
                        $attachmentHandler = new SlackAttachment();
                        $ip = $attachmentHandler->get_user_ip();
                        $attachmentHandler->log_activity_to_db( $severity, $time, $ip, $author, $event_type, $log_message  );
                        
                    }
                    break;
                case 'private':
                    $this->wpnts_notify_slack("Page named ➡️ '{$post->post_title}' has been marked as private by {$author}", ":lock:");

                    $log_message = "Page named ➡️ '{$post->post_title}' has been marked as <b>private</b> by <b>$author</b>";
                    $attachmentHandler = new SlackAttachment();
                    $ip = $attachmentHandler->get_user_ip();
                    $attachmentHandler->log_activity_to_db( $severity, $time, $ip, $author, $event_type, $log_message  );
                    break;
            }

        }

    }




	/**
	 * WordPres page and post deletion.
	 */
	function wpnts_send_post_deletion_notification($post_ID) {
    
        $post = get_post($post_ID);

        /**
         * Plugin activity log
         */
        $severity = "Medium";
        $time = current_time( 'mysql' );

        if ($post) {
            $post_type = $post->post_type;
            $author = get_user_by('ID', $post->post_author)->display_name;

            if ($post_type === 'page') {
                $this->wpnts_notify_slack("Page named :arrow_right: *{$post->post_title}* deleted by {$author}", ":wastebasket:");
                $event_type = "Page";
                $log_message = "Page named ➡️ '{$post->post_title}' has been deleted by <b>$author</b>";
                $attachmentHandler = new SlackAttachment();
                $ip = $attachmentHandler->get_user_ip();
                $attachmentHandler->log_activity_to_db( $severity, $time, $ip, $author, $event_type, $log_message  );

                return;
            } elseif ($post_type === 'post') {
                $this->wpnts_notify_slack("Post named➡️ *{$post->post_title}* deleted by {$author}", ":wastebasket:");
                $event_type = "Post";
                $log_message = "Page named ➡️ '{$post->post_title}' has been deleted by <b>$author</b>";
                $attachmentHandler = new SlackAttachment();
                $ip = $attachmentHandler->get_user_ip();
                $attachmentHandler->log_activity_to_db( $severity, $time, $ip, $author, $event_type, $log_message  );
                
            }
        }

    }


	private function wpnts_notify_slack($message, $emoji = '') {
        $schedules_int = get_option('wpntswebhook_pagenpost_settings');
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

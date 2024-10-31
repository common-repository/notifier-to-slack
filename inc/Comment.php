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
 * Comment used to rest route created
 *
 * @since 1.0.0
 */
class Comment {

	/**
	 * Construct method.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$wpnts_db_instance = new DB();
		$schedules_int = get_option( 'wpnts_schedules_interval_comment_settings');
		$schedules_interval = json_decode($schedules_int);
		
		$commentactionNotice = $schedules_interval->commentactionNotice ?? 'false';
		$commentmoderationnotifications = $schedules_interval->commentmoderationnotifications ?? 'false';
		$commentDisable = $schedules_interval->commentDisable ?? 'false';
		$commentDisablefromAdmin = $schedules_interval->commentDisablefromAdmin ?? 'false';
		$authorPaneltoMail = $schedules_interval->authorPaneltoMail ?? 'false';
		


		//New action hook for comment approval, deletion, and unapproval.
		if ( true === $commentactionNotice ) {
			add_action('transition_comment_status', [ $this, 'wpnts_comment_status_notification' ], 10, 3);
		}
		//Comment Moderation notifications.
		if ( true === $commentmoderationnotifications ) {
			add_action( 'wp_insert_comment',[ $this, 'wpnts_comment_moderation_notification' ], 10, 2 );
		}
		
		//Comment disable
		if ( true === $commentDisable ) {
			// Filters to remove comments from various aspects of the site
			add_filter('comments_open', [$this, 'custom_comments_open'], 10, 2);
			

		}

		if ( true === $commentDisablefromAdmin ) {
			// Disable comments sitewide
			add_action('init', [$this, 'disable_comments_sitewide'], 99);
			add_action('enqueue_block_editor_assets', [$this, 'unregister_comment_blocks_javascript']);
			add_action('init', [$this, 'unregister_comment_blocks'], 99);
		
		
			// Admin UI and menu modifications
			add_filter('wp_count_comments', [$this, 'filter_wp_count_comments']);
			add_action('add_admin_bar_menus', [$this, 'remove_admin_bar_comments_menu']);
			add_action('admin_menu', [$this, 'remove_comments_menu_page']);
			add_action('load-options-discussion.php', [$this, 'block_comments_admin_screen']);
			add_action('load-edit-comments.php', [$this, 'block_comments_admin_screen']);


			add_filter('pings_open', '__return_false');
			add_filter('get_comments_number', '__return_zero');
			add_filter('comments_pre_query', '__return_empty_array');
			add_filter('feed_links_show_comments_feed', '__return_false');
			add_filter('comments_rewrite_rules', '__return_empty_array');

		}

		//Disable Themes & Plugins Editor page
		if ( true === $authorPaneltoMail ) {
			// add_action('init', [$this, 'commentactionNotice']);
		}
		
	}


	// Comment Moderation notifications.
	public function wpnts_comment_moderation_notification( $comment_ID, $comment_approved ) {

		$schedules_int = get_option( 'wpnts_schedules_interval_woocommerce_settings');
		$schedules_interval = json_decode($schedules_int);
		$wpnts_webhook = $schedules_interval->webhook;

		$slack_webhook_url = $wpnts_webhook;

		$comment = get_comment( $comment_ID );
		$post = get_post( $comment->comment_post_ID );
		$author = $comment->comment_author;
		$post_link = get_permalink( $post->ID );
		$message = "New comment by *$author* on *$post->post_title* ($post_link)\n\n";
		$message .= '> ' . $comment->comment_content;

		$this->wpnts_notify_slack($message, ":memo:");

		$severity = "High";
		$event_type = "Comment";
		$time = current_time( 'mysql' );
		
		$attachmentHandler = new SlackAttachment();
		$ip = $attachmentHandler->get_user_ip();
    	$attachmentHandler->log_activity_to_db( $severity, $time, $ip, $author, $event_type, $message  );

	}

	/**
	 * New comment approved, unapproved, delete, trash, spam
	 */
	public function wpnts_comment_status_notification( $new_status, $old_status, $comment ) {
		$schedules_int = get_option('wpnts_schedules_interval_woocommerce_settings');
		$schedules_interval = json_decode($schedules_int);
		$wpnts_webhook = $schedules_interval->webhook;

		$slack_webhook_url = $wpnts_webhook;

		$post = get_post($comment->comment_post_ID);
		$author = $comment->comment_author;
		$post_link = get_permalink($post->ID);

		$message = "Comment by *$author* on *$post->post_title* ($post_link) has been ";

		if ( 'approved' === $new_status ) {
			$message .= 'approved.';
		} elseif ( 'unapproved' === $new_status ) {
			$message .= 'unapproved.';
		} elseif ( 'spam' === $new_status ) {
			$message .= 'spamed.';
		} elseif ( 'trash' === $new_status ) {
			$message .= 'deleted.';
		}

		$this->wpnts_notify_slack($message, ":memo:");


		$severity = "High";
		$event_type = "Comment";
		$time = current_time( 'mysql' );
		
		$attachmentHandler = new SlackAttachment();
		$ip = $attachmentHandler->get_user_ip();
    	$attachmentHandler->log_activity_to_db( $severity, $time, $ip, $author, $event_type, $message  );
		
	}


	/**
	 * Check if comments are open based on user role
	 */
	public function custom_comments_open($open, $post_id) {
		// Get the current user
		$current_user = wp_get_current_user();
	
		// Array of roles allowed to comment.
		$allowed_roles = [
			'administrator',
			'subscriber',
			'vendor',
			'shop_manager',
			'customer',
			'contributor',
			'author',
			'editor'
		];
	
		// Check if the current user's role is in the allowed list.
		foreach ($allowed_roles as $role) {
			if (user_can($current_user, $role)) {
				return true; // Allow comments for these roles.
			}
		}
	
		return false; // Disable comments for all other roles.
	}
	



    /**
	 * Disable comments sitewide
	 */
	public function disable_comments_sitewide() {
		$post_types = get_post_types_by_support('comments');
		foreach ($post_types as $post_type) {
			remove_post_type_support($post_type, 'comments');
			remove_post_type_support($post_type, 'trackbacks');
		}
	}

	// Enqueue script to remove client-side comment blocks
	public function unregister_comment_blocks_javascript() {
		$asset_data = include_once __DIR__ . '/build/index.asset.php';

		wp_enqueue_script(
			'wpnts',
			plugin_dir_url(__FILE__) . '/build/index.js',
			$asset_data['dependencies'],
			$asset_data['version'],
			true
		);
	}

	// Remove server-side comment blocks
	public function unregister_comment_blocks() {
		$registered_blocks = \WP_Block_Type_Registry::get_instance()->get_all_registered();

		$blocks = [
			'core/comments',
			'core/comments-query-loop',
			'core/comment-author-avatar',
			'core/comment-author-name',
			'core/comment-content',
			'core/comment-date',
			'core/comment-edit-link',
			'core/comment-reply-link',
			'core/comment-template',
			'core/comments-pagination',
			'core/comments-pagination-next',
			'core/comments-pagination-numbers',
			'core/comments-pagination-previous',
			'core/comments-title',
			'core/latest-comments',
			'core/post-comment',
			'core/post-comments-count',
			'core/post-comments-form',
			'core/post-comments-link',
		];

		foreach ($blocks as $block) {
			if (isset($registered_blocks[$block])) {
				unregister_block_type($block);
			}
		}
	}

	// Filter wp_count_comments to always return 0
	public function filter_wp_count_comments() {
		return (object) [
			'approved'       => 0,
			'moderated'      => 0,
			'spam'           => 0,
			'trash'          => 0,
			'post-trashed'   => 0,
			'total_comments' => 0,
			'all'            => 0,
		];
	}

	// Remove comments menu from admin bar
	public function remove_admin_bar_comments_menu() {
		remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
	}

	// Remove comments menu page from admin
	public function remove_comments_menu_page() {
		remove_menu_page('edit-comments.php');
		remove_submenu_page('options-general.php', 'options-discussion.php');
	}

	// Block access to comment-related admin screens
	public function block_comments_admin_screen() {
		wp_die(esc_html__('This comment screen is disabled by Noifier to Slack plugin. To active again try "Disable comment menu from admin bar"', 'wpnts'));
	}

	

	

	/**
	 * Slack notification helper.
	 */
	private function wpnts_notify_slack($message, $emoji = '') {
        $schedules_int = get_option('wpnts_schedules_interval_comment_settings');
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

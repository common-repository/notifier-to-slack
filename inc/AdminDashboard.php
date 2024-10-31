<?php
/**
 * Admin Chat Box Activator
 *
 * This class is used to builds all of the tables when the plugin is activated
 *
 * @package WPNTS\Inc
 */

namespace WPNTS\Inc;

use WPNTS\Inc\nts_fs;

defined('ABSPATH') || die('Hey, what are you doing here? You silly human!');
/**
 * Admin dashboard created
 *
 * @since 1.0.0
 */
class AdminDashboard {

	/**
	 * Construct method.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action('admin_menu', [ $this, 'add_notifier_pages' ]);
		add_action('admin_init', [$this, 'check_and_update_logdurationtime']);
	}
	/**
	 * Admin Menu pages
	 *
	 * @since 1.0.0
	 */
	public function add_notifier_pages() {
		$icon = plugin_dir_url(__FILE__) . '../assets/logo.png';
		add_menu_page(
			__( 'Notifier', 'wpnts' ),
			__( 'Notifier', 'wpnts' ),
			'manage_options',
			'wpnts_notifier',
			[ $this, 'notifier_pages' ],
			$icon,
			'2.5' );

		if ( current_user_can( 'manage_options' ) ) {
			global $submenu;

			$submenu['wpnts_notifier'][] = [ __( '📶 Dashboard', 'wpnts_notifier' ), 'manage_options', 'admin.php?page=wpnts_notifier#/' ]; // phpcs:ignore
			
			$submenu['wpnts_notifier'][] = [ __( '📑 Activity Log', 'wpnts_notifier' ), 'manage_options', 'admin.php?page=wpnts_notifier#/log' ]; // phpcs:ignore

			$submenu['wpnts_notifier'][] = [ __( '🧑🏻‍💻 Author', 'wpnts_notifier' ), 'manage_options', 'admin.php?page=wpnts_notifier#/author' ]; // phpcs:ignore

			$submenu['wpnts_notifier'][] = [ __( '⚙️ Configuration', 'wpnts_notifier' ), 'manage_options', 'admin.php?page=wpnts_notifier#/settings' ]; // phpcs:ignore

			$submenu['wpnts_notifier'][] = [ __( '▶️ Get started', 'wpnts_notifier' ), 'manage_options', 'admin.php?page=wpnts_notifier#/doc' ]; // phpcs:ignore

		}
	}

	/**
	 * Dashboard page
	 *
	 * @since 1.0.0
	 */
	public function notifier_pages() {
		echo '<div id="wpcts_dashboard"></div>';
		echo '<div id="wpcts_portal"></div>';
	}


	/**
	 *  Get the option from the database and update time.
	 *
	 * @since 1.0.0
	 */
	public function check_and_update_logdurationtime() {
		// Check if the log update has already been done
		$log_update_done = get_option('wpnts_log_update_done', false);

		if (!$log_update_done) {
			// Get the wpnts_global_api_settings option from the database
			$settings = get_option('wpnts_global_api_settings', false);
	
			if ($settings) {
				// Decode JSON settings
				$settings_array = json_decode($settings, true);
	
				// Check if 'logdurationtime' is empty
				if (empty($settings_array['logdurationtime'])) {
					// Update 'logdurationtime' with the current date
					$settings_array['logdurationtime'] = date("Y-m-d");
	
					// Save the updated settings back to the option
					update_option('wpnts_global_api_settings', json_encode($settings_array));
	
					// Mark that the update has been done
					update_option('wpnts_log_update_done', true);
				}
			}
		}

		if ( ! wp_next_scheduled('wpnts_corn_hook') ) {
			wp_schedule_event(time(), 'added_schedules_interval', 'wpnts_corn_hook');
		}

		// Check and add thumb column to wpnts_activity_log table if needed
		$this->check_and_add_columns();

	}


	/**
	 * Check if the wpnts_activity_log table has the thumb column; add it if missing.
	 *
	 * @since 1.0.0
	 */
	private function check_and_add_columns() {
		global $wpdb;
		$activity_log_table = $wpdb->prefix . 'wpnts_activity_log';
	
		// Check if the 'thumb' column exists
		$thumb_column_exists = $wpdb->get_results(
			$wpdb->prepare(
				"SHOW COLUMNS FROM `$activity_log_table` LIKE %s",
				'thumb'
			)
		);
	
		// Check if the 'user_role' column exists
		$role_column_exists = $wpdb->get_results(
			$wpdb->prepare(
				"SHOW COLUMNS FROM `$activity_log_table` LIKE %s",
				'user_role'
			)
		);
	
		// If 'thumb' column doesn't exist, add it
		if (empty($thumb_column_exists)) {
			$wpdb->query("ALTER TABLE $activity_log_table ADD thumb VARCHAR(255)");
		}
	
		// If 'role' column doesn't exist, add it
		if (empty($role_column_exists)) {
			$wpdb->query("ALTER TABLE $activity_log_table ADD user_role VARCHAR(50)");
		}
	}
	

}

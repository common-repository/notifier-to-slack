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
use WPNTS\Inc\SlackAttachment;

defined('ABSPATH') || die('Hey, what are you doing here? You silly human!');
/**
 * Route used to rest route created
 *
 * @since 1.0.0
 */
class Route {

	/**
	 * Construct method.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'wpnts_create_rest_route' ] );
	}
	/**
	 * Admin Menu pages
	 *
	 * @since 1.0.0
	 */
	public function wpnts_create_rest_route() {
		/**
		 * Credentials: Save
		 */
		register_rest_route( 'wpnts/v1', '/org_plugin_name',[
			'methods' => 'POST',
			'callback' => [ $this, 'set_plugin_name' ],
			'permission_callback' => [ $this, 'set_plugin_name_permission' ],
		] );
		register_rest_route( 'wpnts/v1', '/updatewizards',[
			'methods' => 'POST',
			'callback' => [ $this, 'set_plugin_wizards' ],
			'permission_callback' => [ $this, 'set_plugin_wizards_permission' ],
		] );
		register_rest_route( 'wpnts/v1', '/slack_webhook_sending_test',[
			'methods' => 'POST',
			'callback' => [ $this, 'set_slack_webhook_sending_test' ],
			'permission_callback' => [ $this, 'set_slack_webhook_sending_test_permission' ],
		] );

		register_rest_route( 'wpnts/v1', '/slack_webhook_interval',[
			'methods' => 'POST',
			'callback' => [ $this, 'set_slack_webhook' ],
			'permission_callback' => [ $this, 'set_slack_webhook_permission' ],
		] );
		register_rest_route( 'wpnts/v1', '/slack_webhook_api_global_settings',[
			'methods' => 'POST',
			'callback' => [ $this, 'set_slack_webhookapi_global_settings' ],
			'permission_callback' => [ $this, 'set_slack_webhook_api_global_settings_permission' ],
		] );

		register_rest_route( 'wpnts/v1', '/slack_webhook_interval_site_settings',[
			'methods' => 'POST',
			'callback' => [ $this, 'set_slack_webhook_site_settings' ],
			'permission_callback' => [ $this, 'set_slack_webhook_permission_site_settings' ],
		] );

		register_rest_route( 'wpnts/v1', '/slack_webhook_interval_woocommerce_settings',[
			'methods' => 'POST',
			'callback' => [ $this, 'set_slack_webhook_woocommerce_settings' ],
			'permission_callback' => [ $this, 'set_slack_webhook_permission_woocommerce_settings' ],
		] );

		register_rest_route( 'wpnts/v1', '/slack_webhook_comment_settings',[
			'methods' => 'POST',
			'callback' => [ $this, 'set_slack_webhook_comment_settings' ],
			'permission_callback' => [ $this, 'set_slack_webhook_permission_comment_settings' ],
		] );

		register_rest_route( 'wpnts/v1', '/slack_webhook_wpntswebhook_emengency_shutdown',[
			'methods' => 'POST',
			'callback' => [ $this, 'set_emengency_shutdown' ],
			'permission_callback' => [ $this, 'set_permission_emengency_shutdown' ],
		] );

		register_rest_route( 'wpnts/v1', '/slack_website_downtime_alerts',[
			'methods' => 'POST',
			'callback' => [ $this, 'set_website_downtime_alerts' ],
			'permission_callback' => [ $this, 'set_permission_website_downtime_alerts' ],
		] );

		register_rest_route( 'wpnts/v1', '/slack_webhook_wpntswebhook_debug_log',[
			'methods' => 'POST',
			'callback' => [ $this, 'set_slack_webhook_debuglog_settings' ],
			'permission_callback' => [ $this, 'set_slack_webhook_permission_debuglog_settings' ],
		] );

		register_rest_route( 'wpnts/v1', '/slack_webhook_wpntswebhook_debug_log_clean',[
			'methods' => 'POST',
			'callback' => [ $this, 'set_slack_webhook_debuglog_clean' ],
			'permission_callback' => [ $this, 'set_slack_webhook_permission_debuglog_clean' ],
		] );

		register_rest_route( 'wpnts/v1', '/slack_webhook_wpntsmaintenannotice_log',[
			'methods' => 'POST',
			'callback' => [ $this, 'set_slack_webhook_maintenannotice_settings' ],
			'permission_callback' => [ $this, 'set_slack_webhook_permission_maintenannotice_settings' ],
		] );

		register_rest_route( 'wpnts/v1', '/slack_webhook_site_security_settings',[
			'methods' => 'POST',
			'callback' => [ $this, 'set_slack__webhook_site_security_settings' ],
			'permission_callback' => [ $this, 'set_slack_webhook_permission_site_security_settings' ],
		] );

		register_rest_route( 'wpnts/v1', '/slack_webhook_wpnts_captcha_settings',[
			'methods' => 'POST',
			'callback' => [ $this, 'set_slack_captcha_settings' ],
			'permission_callback' => [ $this, 'set_slack_captcha_settings_permission' ],
		] );
		register_rest_route( 'wpnts/v1', '/slack_webhook_wpnts_captcha_verify',[
			'methods' => 'POST',
			'callback' => [ $this, 'set_slack_captcha_verify' ],
			'permission_callback' => [ $this, 'set_slack_captcha_verify_permission' ],
		] );

		register_rest_route( 'wpnts/v1', '/slack_webhook_pagenpost_settings',[
			'methods' => 'POST',
			'callback' => [ $this, 'set_slack_webhook_pagenpost_settings' ],
			'permission_callback' => [ $this, 'set_slack_webhook_permission_pagenpost_settings' ],
		] );
		register_rest_route( 'wpnts/v1', '/slack_webhook_media_settings',[
			'methods' => 'POST',
			'callback' => [ $this, 'set_slack_slack_webhook_media_settings' ],
			'permission_callback' => [ $this, 'set_slack_webhook_permission_media_settings' ],
		] );
		register_rest_route( 'wpnts/v1', '/slack_webhook_widget_settings',[
			'methods' => 'POST',
			'callback' => [ $this, 'set_slack_slack_webhook_widget_settings' ],
			'permission_callback' => [ $this, 'set_slack_webhook_permission_widget_settings' ],
		] );
		register_rest_route( 'wpnts/v1', '/slack_webhook_menu_settings',[
			'methods' => 'POST',
			'callback' => [ $this, 'set_slack_slack_webhook_menu_settings' ],
			'permission_callback' => [ $this, 'set_slack_webhook_permission_menu_settings' ],
		] );
		
		register_rest_route( 'wpnts/v1', '/slack_webhook_theme_settings',[
			'methods' => 'POST',
			'callback' => [ $this, 'set_slack_webhook_theme_settings' ],
			'permission_callback' => [ $this, 'set_slack_webhook_permission_theme_settings' ],
		] );

		register_rest_route( 'wpnts/v1', '/plugin_corn_run',[
			'methods' => 'GET',
			'callback' => [ $this, 'get_plugin_corn_run' ],
			'permission_callback' => [ $this, 'get_plugin_corn_run_permission' ],
		] );

		register_rest_route( 'wpnts/v1', '/slack_webhook_integration_settings',[
			'methods' => 'POST',
			'callback' => [ $this, 'set_slack_slack_webhook_integration_settings' ],
			'permission_callback' => [ $this, 'set_slack_webhook_permission_integration_settings' ],
		] );

		register_rest_route( 'wpnts/v1', '/maintenance_mode',[
			'methods' => 'POST',
			'callback' => [ $this, 'set_slack_maintenance_mode' ],
			'permission_callback' => [ $this, 'set_slack_webhook_permission_maintenance_mode' ],
		] );
	}

	/**
	 * Wizards page
	 *
	 * @param WP_Request_Object $req WordPress request object.
	 * @since 1.0.0
	 */
	
	public function set_plugin_wizards($req) {
		// Decode the incoming JSON directly
		$body = json_decode($req->get_body(), true);
		
		// Sanitize and extract the 'updateWizards' array
		$updateWizards = isset($body['updateWizards']) && is_array($body['updateWizards']) ? wp_unslash($body['updateWizards']) : [];
	
		// Check if there are any wizards to update
		if ($updateWizards) {
			// Convert the values to boolean
			$updateWizards['setupComplete'] = !empty($updateWizards['setupComplete']);
			$globalWebhook = !empty($updateWizards['global_webhook']) ? $updateWizards['global_webhook'] : '';

			if (!empty($updateWizards['recipiantmail'])) {
				// Get the current date in 'Y-m-d' format
				$today = current_time('Y-m-d');
			
				// Add 7 days to the current date
				$logDurationTime = date('Y-m-d', strtotime($today . ' +7 days')); 
			
				// Update global API settings
				update_option('wpnts_global_api_settings', json_encode([
					"global_webhook" => $globalWebhook, 
					"global_interval" => 1800, 
					"api_active" => false, 
					"mailconfig" => false,
					"recipiantmail" => $updateWizards['recipiantmail'], 
					"activitylogduration" => "7", 
					"logdurationtime" => $logDurationTime
				]));
				
			}
		
			// Handle active user activity settings.
			if (!empty($updateWizards['active_user_activity'])) {
				update_option('wpnts_schedules_interval_site_settings', json_encode([
					"webhook" => $globalWebhook,
					"interval_plugin_update" => 1800,
					"interval_sitehelgth_update" => 3600,
					"intervalDays" => -7,
					"pluginactivation" => true,
					"plugindeactivation" => true,
					"pluginupdate" => true,
					"updatenotification" => true,
					"wpnotification" => true,
					"userdeletion" => false,
					"authenticaltionfailed" => false,
					"loginandout" => true,
					"loginalertwithip" => false,
					"sitehelgth" => true,
					"registration" => true,
					"rolechangenotification" => false,
					"lostpassreset" => false,
					"accountpassreset" => false
				]));
			}
	
			// Handle active security settings.
			if (!empty($updateWizards['active_security_settings'])) {
				update_option('wpnts_webhook_site_settings', json_encode([
					"webhook" => $globalWebhook,
					"intervalDays" => -1,
					"sitessecurityissuesInterval" => 1500,
					"wpconfigmodification" => false,
					"htaccessmodification" => false,
					"manualIPbloker" => false,
					"autoIPbloker" => false,
					"listofIP" => "",
					"cidrrangeIP" => "",
					"autoaddedIP" => "",
					"loginattempt" => "3",
					"wpnts_disable_file_editor" => false,
					"hide_wordpress_version" => false,
					"x_powered_by" => true,
					"rate_limit_ip" => true,
					"suspicious_requests" => true,
					"monitor_admin_access" => false,
					"limit_login_attempts" => false,
					"scan_uploaded_files" => false,
					"selectedFileTypes" => [],
					"monitor_file_integrity" => false,
					"blockedminutes" => 30,
					"blockedRequests" => 200
				]));
			}
	
			// Update other settings based on their respective flags.
			if (!empty($updateWizards['active_site_theme_security'])) {
				update_option('wpntswebhook_theme_settings', json_encode([
					"webhook" => $globalWebhook,
					"themeupdate" => true,
					"themeinstalled" => true,
					"themedeletion" => true,
					"themeactivation" => true,
					"generalsettingspage" => true,
					"authorPaneltoMail" => false
				]));
			}
	
			if (!empty($updateWizards['active_site_page_post_security'])) {
				update_option('wpntswebhook_pagenpost_settings', json_encode([
					"webhook" => $globalWebhook,
					"newpagecreate" => true,
					"deletepostnpage" => true,
					"newpostcreate" => false,
					"posttaxonomy" => false,
					"maketrash" => false,
					"makeuntrash" => false
				]));
			}
	

			if (!empty($updateWizards['active_site_media_security'])) {
				update_option('wpntswebhook_media_settings', json_encode([
					"webhook" => $globalWebhook,
					"newmediaaddnotice" => true,
					"mediadelete" => true,
					"trackEmail" => false,
				]));
			}
	
			if (!empty($updateWizards['active_site_menu_security'])) {
				update_option('wpntswebhook_menu_settings', json_encode([
					"webhook" => $globalWebhook,
					"delete_nav_menu" => true,
					"create_nav_menu" => true,
					"update_nav_menu" => true
				]));
			}
	
			if (!empty($updateWizards['active_site_widgets_security'])) {
				update_option('wpntswebhook_widget_settings', json_encode([
					"webhook" => $globalWebhook,
					"delete_widget" => true,
					"save_widget" => true
				]));
			}


			if (!empty($updateWizards['active_site_woocommerce_security'])) {
				update_option('wpnts_schedules_interval_woocommerce_settings', json_encode([
					"webhook" => $globalWebhook,
					"stockorout_interval_notification" => 1800,
					"ordernotmarkedstatus_interval_notification" => 2,
					"intervalDays" => -5,
					"salesnotifications" => true,
					"stockoutofstocknotifications" => true,
					"wooproducteditornotify" => true,
					"shipingstatus" => true,
					"billingshippingnotify" => true,
					"ordernotmarkedstatus" => false,
					"productdownloadnotice" => false,
				]));
			}

			if (!empty($updateWizards['active_site_comment_security'])) {
				update_option('wpnts_schedules_interval_comment_settings', json_encode([
					"webhook" => $globalWebhook,
					"commentactionNotice" => true,
					"commentmoderationnotifications" => true,
					"commentDisable" => false,
					"commentDisablefromAdmin" => false,
					"authorPaneltoMail" => false,
				]));
			}

	
			// Handle setup complete.
			$setupComplete = isset($updateWizards['setupComplete']) ? $updateWizards['setupComplete'] : false;
			update_option('notifier_ran_setup_wizard', $setupComplete); // Store the boolean value.
	
			// Remove `setupComplete` from the array to store the rest of the settings.
			unset($updateWizards['setupComplete']);
			
			// Update the `setup_wizard_data` option with the remaining settings.
			update_option('setup_wizard_data', $updateWizards); // No need to encode here either.
	
			return rest_ensure_response(1);
		} else {
			return rest_ensure_response(0);
		}
	}

	
	/**
	 * Wizards page save permission.
	 *
	 * @since 1.0.0
	 */
	public function set_plugin_wizards_permission() {
		// return true;
		return current_user_can( 'administrator' );
	}


	/**
	 * Plugin name add.
	 *
	 * @param WP_Request_Object $req WordPress request object.
	 * @since 1.0.0
	 */
	public function set_plugin_name( $req ) {

		$pluginList    = isset( $req['updatePluginslist'] ) ? rest_sanitize_array( wp_unslash($req['updatePluginslist']) ) : [];

		if ( $pluginList ) {
			update_option( 'wpnts_plugin_list', json_encode($pluginList) );
			return rest_ensure_response(1);
			wp_die();
		} else {
			return rest_ensure_response(0);
			wp_die();
		}
	}

	/**
	 * Rest route save permission.
	 *
	 * @since 1.0.0
	 */
	public function set_plugin_name_permission() {
		// return true;
		return current_user_can( 'administrator' );
	}


	// Slack test -----------
	public function set_slack_webhook_sending_test( $req ) {
		$global_webhook = isset( $req['global_webhook'] ) ? sanitize_text_field( wp_unslash( $req['global_webhook'] ) ) : '';
		$testMessage    = isset( $req['testMessage'] ) ? sanitize_text_field( wp_unslash( $req['testMessage'] ) ) : '';

		// Ensure $testMessage is a string
		$testMessage = is_array( $testMessage ) ? '' : $testMessage;

		$payload = json_encode( [ 'text' => $testMessage ] );
		$args    = [
			'body'      => $payload,
			'headers'   => [ 'Content-Type' => 'application/json' ],
			'timeout'   => '5',
			'sslverify' => false,
		];

		$response = wp_remote_post( $global_webhook, $args );

		if ( $response ) {
			return rest_ensure_response( 1 );
		} else {
			return rest_ensure_response( 0 );
		}
	}


	/**
	 * Rest route save permission.
	 *
	 * @since 1.0.0
	 */
	public function set_slack_webhook_sending_test_permission() {
		// return true;
		return current_user_can( 'administrator' );
	}


	/**
	 * Set webhook page for author settings.
	 *
	 * @param WP_Request_Object $req WordPress request object.
	 * @since 1.0.0
	 */
	public function set_slack_webhook( $req ) {

		$webhook_interval = isset( $req['wpntswebhook'] ) ? rest_sanitize_object( wp_unslash($req['wpntswebhook']) ) : [];

		if ( $webhook_interval ) {
			update_option( 'wpnts_default_interval', json_encode($webhook_interval) );
			return rest_ensure_response(1);
			wp_die();
		} else {
			return rest_ensure_response(0);
			wp_die();
		}
	}
	/**
	 * Rest route save permission.
	 *
	 * @since 1.0.0
	 */
	public function set_slack_webhook_permission() {
		// return true;
		return current_user_can( 'administrator' );
	}

	/**
	 * Set webhook page for author settings.
	 *
	 * @param WP_Request_Object $req WordPress request object.
	 * @since 1.0.0
	 */
	public function set_slack_webhookapi_global_settings( $req ) {

		$webhook_interval    = isset( $req['wpnts_api_settings'] ) ? rest_sanitize_object( wp_unslash($req['wpnts_api_settings']) ) : [];

		if ( $webhook_interval ) {
			update_option( 'wpnts_global_api_settings', json_encode($webhook_interval) );
			return rest_ensure_response(1);
			wp_die();
		} else {
			return rest_ensure_response(0);
			wp_die();
		}
	}
	/**
	 * Rest route save permission.
	 *
	 * @since 1.0.0
	 */
	public function set_slack_webhook_api_global_settings_permission() {
		// return true;
		return current_user_can( 'administrator' );

	}
	// -------------------------------------------------------------------------------------------//
	/**
	 * Set webhook page for site settings.
	 *
	 * @param WP_Request_Object $req WordPress request object.
	 * @since 1.0.0
	 */
	public function set_slack_webhook_site_settings( $req ) {

		$webhook_interval    = isset( $req['wpntswebhook_site_settings'] ) ? rest_sanitize_object( wp_unslash($req['wpntswebhook_site_settings']) ) : [];

		if ( $webhook_interval ) {
			update_option( 'wpnts_schedules_interval_site_settings', json_encode($webhook_interval) );
			return rest_ensure_response(1);
			wp_die();
		} else {
			return rest_ensure_response(0);
			wp_die();
		}
	}
	/**
	 * Rest route save permission.
	 *
	 * @since 1.0.0
	 */
	public function set_slack_webhook_permission_site_settings() {
		// return true;
		return current_user_can( 'administrator' );
	}

	/**
	 * Set webhook page for woocommerce settings.
	 *
	 * @param WP_Request_Object $req WordPress request object.
	 * @since 1.0.0
	 */
	public function set_slack_webhook_woocommerce_settings( $req ) {

		$webhook_interval    = isset( $req['wpntswebhook_woocommerce_settings'] ) ? rest_sanitize_object( wp_unslash($req['wpntswebhook_woocommerce_settings']) ) : [];

		if ( $webhook_interval ) {
			update_option( 'wpnts_schedules_interval_woocommerce_settings', json_encode($webhook_interval) );
			return rest_ensure_response(1);
			wp_die();
		} else {
			return rest_ensure_response(0);
			wp_die();
		}
	}
	/**
	 * Rest route save permission.
	 *
	 * @since 1.0.0
	 */
	public function set_slack_webhook_permission_woocommerce_settings() {
		// return true;
		return current_user_can( 'administrator' );
	}


	/**
	 * Set webhook page for comment_settings settings.
	 *
	 * @param WP_Request_Object $req WordPress request object.
	 * @since 1.0.0
	 */
	public function set_slack_webhook_comment_settings( $req ) {

		$webhook_interval    = isset( $req['wpntswebhook_comment_settings'] ) ? rest_sanitize_object( wp_unslash($req['wpntswebhook_comment_settings']) ) : [];

		if ( $webhook_interval ) {
			update_option( 'wpnts_schedules_interval_comment_settings', json_encode($webhook_interval) );
			return rest_ensure_response(1);
			wp_die();
		} else {
			return rest_ensure_response(0);
			wp_die();
		}
	}
	/**
	 * Rest route save permission.
	 *
	 * @since 1.0.0
	 */
	public function set_slack_webhook_permission_comment_settings() {
		// return true;
		return current_user_can( 'administrator' );
	}

	/**
	 * Set webhook page for woocommerce settings.
	 *
	 * @param WP_Request_Object $req WordPress request object.
	 * @since 1.0.0
	 */
	public function set_emengency_shutdown( $req ) {

		$webhook_interval    = isset( $req['wpntswebhook_emengency_shutdown'] ) ? rest_sanitize_object( wp_unslash($req['wpntswebhook_emengency_shutdown']) ) : [];

		if ( $webhook_interval ) {
			update_option( 'wpntswebhook_emengency_shutdown', json_encode($webhook_interval) );
			return rest_ensure_response(1);
			wp_die();
		} else {
			return rest_ensure_response(0);
			wp_die();
		}
	}
	/**
	 * Rest route save permission.
	 *
	 * @since 1.0.0
	 */
	public function set_permission_emengency_shutdown() {
		// return true;
		return current_user_can( 'administrator' );
	}

	/**
	 * Set webhook page for website_downtime_alerts.
	 *
	 * @param WP_Request_Object $req WordPress request object.
	 * @since 1.0.0
	 */
	public function set_website_downtime_alerts( $req ) {

		$webhook_interval    = isset( $req['wpntswebhook_website_downtime_alerts'] ) ? rest_sanitize_object( wp_unslash($req['wpntswebhook_website_downtime_alerts']) ) : [];

		if ( $webhook_interval ) {
			update_option( 'wpntswebhook_website_downtime_alerts', json_encode($webhook_interval) );
			return rest_ensure_response(1);
			wp_die();
		} else {
			return rest_ensure_response(0);
			wp_die();
		}
	}
	/**
	 * Rest route save permission.
	 *
	 * @since 1.0.0
	 */
	public function set_permission_website_downtime_alerts() {
		// return true;
		return current_user_can( 'administrator' );
	}

	/**
	 * Set webhook page for debuglog settings.
	 *
	 * @param WP_Request_Object $req WordPress request object.
	 * @since 1.0.0
	 */
	public function set_slack_webhook_debuglog_settings( $req ) {

		$webhook_interval   = isset( $req['wpntswebhook_debug_log'] ) ? rest_sanitize_object( wp_unslash($req['wpntswebhook_debug_log']) ) : [];

		if ( isset( $webhook_interval['wpdebuglog'] ) ) {
			$wp_debug = ( defined( 'WP_DEBUG' ) && WP_DEBUG === true );
			$config_path = ABSPATH . 'wp-config.php';
			if ( file_exists( $config_path ) ) {
				$config_contents = file_get_contents( $config_path );

				if ( $webhook_interval['wpdebuglog'] === false ) {
					$config_contents = preg_replace( '/define\s*\(\s*\'WP_DEBUG\'\s*,\s*true\s*\)\s*;/', "define('WP_DEBUG', false);", $config_contents );

					$config_contents = preg_replace( '/define\s*\(\s*\'WP_DEBUG_LOG\'\s*,\s*true\s*\)\s*;/', "define('WP_DEBUG_LOG', false);", $config_contents );
				} else {
					$config_contents = preg_replace( '/define\s*\(\s*\'WP_DEBUG\'\s*,\s*false\s*\)\s*;/', "define('WP_DEBUG', true);", $config_contents );

					$config_contents = preg_replace( '/define\s*\(\s*\'WP_DEBUG_LOG\'\s*,\s*false\s*\)\s*;/', "define('WP_DEBUG_LOG', true);", $config_contents );
				}

				file_put_contents( $config_path, $config_contents );
			}
		}

		if ( $webhook_interval ) {
			update_option( 'wpnts_schedules_interval_debuglog_settings', json_encode($webhook_interval) );
			return rest_ensure_response(1);
			wp_die();
		} else {
			return rest_ensure_response(0);
			wp_die();
		}
	}


	/**
	 * Rest route save permission.
	 *
	 * @since 1.0.0
	 */
	public function set_slack_webhook_permission_debuglog_settings() {
		// return true;
		return current_user_can( 'administrator' );
	}

	/**
	 * Clean log
	 */
	public function set_slack_webhook_debuglog_clean( $req ) {

		$clean_debug_log = isset( $req['cleanDebugLog'] ) ? intval( $req['cleanDebugLog'] ) : false;

		if ( $clean_debug_log === 1 ) {

			$debug_log = WP_CONTENT_DIR . '/debug.log';

			if ( file_exists($debug_log) ) {
				file_put_contents($debug_log, '');
			}
			return rest_ensure_response(1);
		} else {
			return rest_ensure_response(0);
		}
	}

	/**
	 * Rest route save permission.
	 *
	 * @since 1.0.0
	 */
	public function set_slack_webhook_permission_debuglog_clean() {
		// return true;
		return current_user_can( 'administrator' );
	}


	/**
	 * Set webhook page for Maintaince settings.
	 *
	 * @param WP_Request_Object $req WordPress request object.
	 * @since 1.0.0
	 */
	public function set_slack_webhook_maintenannotice_settings( $req ) {

		$webhook_interval   = isset( $req['wpntsmaintenannotice_log'] ) ? rest_sanitize_object( wp_unslash($req['wpntsmaintenannotice_log']) ) : [];

		if ( isset( $webhook_interval['maintenance_mode'] ) ) {
			$current_status = get_option('maintenance_mode');
			update_option('maintenance_mode', ! $current_status);
		}

		if ( $webhook_interval ) {
			update_option( 'wpnts_schedules_maintenannotice_settings', json_encode($webhook_interval) );
			return rest_ensure_response(1);
			wp_die();
		} else {
			return rest_ensure_response(0);
			wp_die();
		}
	}


	/**
	 * Rest route save permission.
	 *
	 * @since 1.0.0
	 */
	public function set_slack_webhook_permission_maintenannotice_settings() {
		// return true;
		return current_user_can( 'administrator' );
	}


	/**
	 * Set webhook page for site security settings.
	 *
	 * @param WP_Request_Object $req WordPress request object.
	 * @since 1.0.0
	 */
	
	public function set_slack__webhook_site_security_settings( $req ) {
		$request_data = isset( $req['wpntswebhook_site_settings'] ) ? rest_sanitize_object( wp_unslash( $req['wpntswebhook_site_settings'] ) ) : [];

		// Get existing data
		$existing_data = json_decode( get_option( 'wpnts_webhook_site_settings', '{}' ), true );

		// Merge existing data with new data
		$updated_data = array_merge( $existing_data, $request_data );

		// Update the option with the merged data
		update_option( 'wpnts_webhook_site_settings', json_encode( $updated_data ) );

		return rest_ensure_response( 1 );
		wp_die();
	}



	public function set_slack_webhook_permission_site_security_settings() {
		// return true;
		return current_user_can( 'manage_options' );
	}

	/**
	 * Set webhook page for site Captcha settings.
	 *
	 * @param WP_Request_Object $req WordPress request object.
	 * @since 1.0.0
	 */
	public function set_slack_captcha_settings( $req ) {

		$reuest_data  = isset( $req['wpnts_captcha_settings'] ) ? rest_sanitize_object( wp_unslash($req['wpnts_captcha_settings']) ) : [];

		if ( $reuest_data ) {
			update_option( 'wpnts_captcha_settings', json_encode($reuest_data) );
			return rest_ensure_response(1);
			wp_die();
		} else {
			return rest_ensure_response(0);
			wp_die();
		}
	}

	public function set_slack_captcha_settings_permission() {
		// return true;
		return current_user_can( 'administrator' );
	}


	/**
	 * Verify and connect
	 */
	public function set_slack_captcha_verify( $req ) {

		$sitekeys = isset( $req['sitekeys'] ) ? sanitize_text_field( wp_unslash( $req['sitekeys'] ) ) : '';
		$secretkey    = isset( $req['secretkey'] ) ? sanitize_text_field( wp_unslash( $req['secretkey'] ) ) : '';
		$token    = isset( $req['token'] ) ? sanitize_text_field( wp_unslash( $req['token'] ) ) : '';

		if ( $token !== '' && $sitekeys !== '' ) {

			$verification_url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

				$response = wp_remote_post(
					$verification_url,
					[
						'body' => [
							'secret'   => $secretkey,
							'response' => $token,
						],
					]
				);

				$varification = json_decode( wp_remote_retrieve_body( $response ), true );

				update_option( 'notifier_turnstile_validated', true );

			if ( $varification ) {
				wp_send_json_success([
					'message'   => __( 'Connection verified & saved.', 'simpleform' ),
					'validated_value' => get_option( 'notifier_turnstile_validated' ),
					'validated' => true,
				]);

			} else {
				wp_send_json_error([
					'message'   => __( 'Validation error', 'simpleform' ),
					'validated_value' => get_option( 'notifier_turnstile_validated' ),
					'validated' => false,
				]);

			}
		} else {

			update_option( 'notifier_turnstile_validated', false );

			wp_send_json_error([
				'message'   => __( 'Validation error! Token Is not generated', 'simpleform' ),
				'validated_value' => get_option( 'notifier_turnstile_validated' ),
				'validated' => false,
			]);

			// return rest_ensure_response(0);
		}
	}

	public function set_slack_captcha_verify_permission() {
		// return true;
		return current_user_can( 'administrator' );
	}

	/**
	 * Page N Post
	 */
	public function set_slack_webhook_pagenpost_settings( $req ) {

		$reuest_data    = isset( $req['wpntswebhook_pagenpost_settings'] ) ? rest_sanitize_object( wp_unslash($req['wpntswebhook_pagenpost_settings']) ) : [];

		if ( $reuest_data ) {
			update_option( 'wpntswebhook_pagenpost_settings', json_encode($reuest_data) );
			return rest_ensure_response(1);
			wp_die();
		} else {
			return rest_ensure_response(0);
			wp_die();
		}
	}
	public function set_slack_webhook_permission_pagenpost_settings() {
		// return true;
		return current_user_can( 'administrator' );
	}

	/**
	 * MEDIA Settings
	 */
	public function set_slack_slack_webhook_media_settings( $req ) {

		$reuest_data = isset( $req['wpntswebhook_media_settings'] ) ? rest_sanitize_object( wp_unslash($req['wpntswebhook_media_settings']) ) : [];

		if ( $reuest_data ) {
			update_option( 'wpntswebhook_media_settings', json_encode($reuest_data) );
			return rest_ensure_response(1);
			wp_die();
		} else {
			return rest_ensure_response(0);
			wp_die();
		}
	}
	public function set_slack_webhook_permission_media_settings() {
		// return true;
		return current_user_can( 'administrator' );
	}


	/**
	 * Widget Settings
	 */
	public function set_slack_slack_webhook_widget_settings( $req ) {

		$reuest_data = isset( $req['wpntswebhook_widget_settings'] ) ? rest_sanitize_object( wp_unslash($req['wpntswebhook_widget_settings']) ) : [];

		if ( $reuest_data ) {
			update_option( 'wpntswebhook_widget_settings', json_encode($reuest_data) );
			return rest_ensure_response(1);
			wp_die();
		} else {
			return rest_ensure_response(0);
			wp_die();
		}
	}
	public function set_slack_webhook_permission_widget_settings() {
		// return true;
		return current_user_can( 'administrator' );
	}


	/**
	 * Menu Settings
	 */
	public function set_slack_slack_webhook_menu_settings( $req ) {

		$reuest_data = isset( $req['wpntswebhook_menu_settings'] ) ? rest_sanitize_object( wp_unslash($req['wpntswebhook_menu_settings']) ) : [];

		if ( $reuest_data ) {
			update_option( 'wpntswebhook_menu_settings', json_encode($reuest_data) );
			return rest_ensure_response(1);
			wp_die();
		} else {
			return rest_ensure_response(0);
			wp_die();
		}
	}
	public function set_slack_webhook_permission_menu_settings() {
		// return true;
		return current_user_can( 'administrator' );
	}


	/**
	 * Theme
	 */
	public function set_slack_webhook_theme_settings( $req ) {

		$reuest_data    = isset( $req['wpntswebhook_theme_settings'] ) ? rest_sanitize_object( wp_unslash($req['wpntswebhook_theme_settings']) ) : [];

		if ( $reuest_data ) {
			update_option( 'wpntswebhook_theme_settings', json_encode($reuest_data) );
			return rest_ensure_response(1);
			wp_die();
		} else {
			return rest_ensure_response(0);
			wp_die();
		}
	}
	public function set_slack_webhook_permission_theme_settings() {
		// return true;
		return current_user_can( 'administrator' );
	}



	/**
	 * Corn to active
	 */
	public function get_plugin_corn_run() {

		$status_data = [
			'site_name' => home_url(),
			'status' => 'active',
		];

		// Return the response as JSON.
		wp_send_json( $status_data );
	}

	public function get_plugin_corn_run_permission() {
		return true;
	}


	/**
	 * MEDIA Settings
	 */
	public function set_slack_slack_webhook_integration_settings( $req ) {

		$reuest_data = isset( $req['wpntswebhook_integration_settings'] ) ? rest_sanitize_object( wp_unslash($req['wpntswebhook_integration_settings']) ) : [];

		if ( $reuest_data ) {
			update_option( 'wpntswebhook_integration_settings', json_encode($reuest_data) );
			return rest_ensure_response(1);
			wp_die();
		} else {
			return rest_ensure_response(0);
			wp_die();
		}
	}
	public function set_slack_webhook_permission_integration_settings() {
		// return true;
		return current_user_can( 'administrator' );
	}

	/**
	 * Maintenance mode form 
	 */
	public function set_slack_maintenance_mode( $request ) {

		 // Verify nonce for CSRF protection
		 if ( ! isset( $request['nonce'] ) || ! wp_verify_nonce( $request['nonce'], 'wp_rest' ) ) {
			return rest_ensure_response(array(
				'success' => 0,
				'message' => __('Invalid request.', 'wpnts')
			));
		}

		// Retrieve data from request
		$user_email = isset($request['user_email']) ? sanitize_email($request['user_email']) : '';
		$user_message = isset($request['user_message']) ? sanitize_text_field($request['user_message']) : '';
	
		// Validate email and message
		if ( empty($user_email) || !is_email($user_email) || empty($user_message) ) {
			return rest_ensure_response(array(
				'success' => 0,
				'message' => __('Invalid email or message input', 'wpnts')
			));
		}
	
		// Send slack notice.
		$schedules_int = get_option( 'wpnts_schedules_maintenannotice_settings');
		$schedules_interval = json_decode($schedules_int);
		
		$wpnts_webhook = $schedules_interval->webhook;
		$maintenannotice = $schedules_interval->maintenannotice ?? 'false';

		if ( true === $maintenannotice ) {
			$message = "Maintenance form submitted by ➡️{$user_email} with message ➡️{$user_message}";
			$severity = "Medium";
			$event_type = "Maintenance";
			$time = current_time( 'mysql' );
			$author = 'Unknown';
			$attachmentHandler = new SlackAttachment();
			$ip = $attachmentHandler->get_user_ip();
			$attachmentHandler->log_activity_to_db( $severity, $time, $ip, $author, $event_type, $message  );

			// Send to Slack 
			$payload = json_encode(['text' => $message]);
			$args = [
				'body'      => $payload,
				'headers'   => ['Content-Type' => 'application/json'],
				'timeout'   => '5',
				'sslverify' => false,
			];
			$response = wp_remote_post($wpnts_webhook, $args);
		}


		// Add the new data to options or database
		$existing_queries = get_option('maintenance_mode_queries', array());
		
		// Check for duplicate email
		/* if ( isset($existing_queries[$user_email]) ) {
			return rest_ensure_response(array(
				'success' => 0,
				'message' => __('This email is already registered.', 'wpnts')
			));
		} */
	
		// Add new query to the database
		$existing_queries[$user_email] = array(
			'user_message' => $user_message,
			'ip_address'   => sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ),
			'submitted_at' => current_time('mysql'),
		);
	
		if ( update_option('maintenance_mode_queries', $existing_queries) ) {

			// Finally form submitted 
			return rest_ensure_response(array(
				'success' => 1,
				'message' => __('Form submitted successfully!', 'wpnts')
			));
		} else {
			return rest_ensure_response(array(
				'success' => 0,
				'message' => __('There was a problem saving your query.', 'wpnts')
			));
		}
	}
	
	public function set_slack_webhook_permission_maintenance_mode() {
		return true;
	}
	
}

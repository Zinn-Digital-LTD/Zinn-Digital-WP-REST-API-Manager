<?php
/**
 * Uninstall script for Zinn WP REST API Manager
 *
 * This file is executed when the plugin is deleted via the WordPress admin.
 * It removes all plugin data from the database.
 *
 * @package Zinn_WP_REST_API_Manager
 * @since 1.0.0
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Security check: Ensure this is being called properly
if ( ! current_user_can( 'delete_plugins' ) ) {
	exit;
}

/**
 * Remove plugin options from the database
 */
function zinn_wpapi_remove_plugin_data() {
	
	// Remove main plugin settings
	delete_option( 'zinnwpapi_settings' );
	
	// Remove any transients (even if not currently used, for future-proofing)
	delete_transient( 'zinn_wpapi_cache' );
	delete_transient( 'zinn_wpapi_post_types' );
	delete_transient( 'zinn_wpapi_taxonomies' );
	
	// For multisite installations, remove options from all sites
	if ( is_multisite() ) {
		global $wpdb;
		
		// Get all blog IDs
		$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
		
		foreach ( $blog_ids as $blog_id ) {
			switch_to_blog( $blog_id );
			
			// Remove options for this site
			delete_option( 'zinnwpapi_settings' );
			delete_transient( 'zinn_wpapi_cache' );
			delete_transient( 'zinn_wpapi_post_types' );
			delete_transient( 'zinn_wpapi_taxonomies' );
			
			restore_current_blog();
		}
	}
}

/**
 * Remove any scheduled cron events (if any exist in future versions)
 */
function zinn_wpapi_remove_cron_events() {
	// Clear any scheduled events
	wp_clear_scheduled_hook( 'zinn_wpapi_cleanup' );
	wp_clear_scheduled_hook( 'zinn_wpapi_daily_check' );
}

/**
 * Clean up user meta (if plugin stores any user-specific data in future)
 */
function zinn_wpapi_remove_user_meta() {
	global $wpdb;
	
	// Remove any user meta related to this plugin
	$wpdb->delete(
		$wpdb->usermeta,
		array(
			'meta_key' => 'zinn_wpapi_user_settings'
		)
	);
}

/**
 * Main uninstall function
 * 
 * Orchestrates the complete removal of all plugin data
 */
function zinn_wpapi_uninstall() {
	// Remove plugin options and transients
	zinn_wpapi_remove_plugin_data();
	
	// Remove any cron events
	zinn_wpapi_remove_cron_events();
	
	// Remove user meta
	zinn_wpapi_remove_user_meta();
	
	// Clear any object cache
	if ( function_exists( 'wp_cache_flush' ) ) {
		wp_cache_flush();
	}
}

// Execute the uninstall
zinn_wpapi_uninstall();

// Final security check - ensure we're still in the uninstall context
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
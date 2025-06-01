<?php
/**
 * Uninstall script for Zinn Digital WP REST API Manager
 *
 * @package Zinn_Digital_WP_REST_API_Manager
 * @since 1.0.0
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Security check
if ( ! current_user_can( 'delete_plugins' ) ) {
	exit;
}

// Remove plugin options
$option_types = array( 'post', 'comment', 'user', 'term' );

foreach ( $option_types as $type ) {
	delete_option( 'zinn_zwram_options_' . $type );
}

// Clear any transients
delete_transient( 'zinn_zwram_cache' );

// For multisite
if ( is_multisite() ) {
	$sites = get_sites( array( 'fields' => 'ids' ) );
	
	foreach ( $sites as $blog_id ) {
		switch_to_blog( $blog_id );
		
		foreach ( $option_types as $type ) {
			delete_option( 'zinn_zwram_options_' . $type );
		}
		
		delete_transient( 'zinn_zwram_cache' );
		
		restore_current_blog();
	}
}

<?php
/*
Plugin Name: Zinn Digital™ WP Rest API Manager
Plugin URI: https://zinndigital.com/
Description: Manage and inspect REST API exposure for all post types and taxonomies. Modular, secure, easy & Created by Zinn Digital™ LTD.
Version: 1.0.0
Author: Zinn Digital LTD
Author URI: https://zinnhub.com/about-team/
License: GPL2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: zinn-digital-wp-rest-api-manager
Domain Path: /languages
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Network: false
*/

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
define( 'ZINN_WPAPI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ZINN_WPAPI_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'ZINN_WPAPI_VERSION', '1.0.0' );
define( 'ZINN_WPAPI_TEXT_DOMAIN', 'zinn-digital-wp-rest-api-manager' );

/**
 * Main plugin class
 */
class Zinn_WPAPI_Manager {

    /**
     * Plugin instance.
     *
     * @var Zinn_WPAPI_Manager
     */
    private static $instance = null;

    /**
     * Get plugin instance.
     *
     * @return Zinn_WPAPI_Manager
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        add_action( 'plugins_loaded', array( $this, 'init' ) );
        add_action( 'init', array( $this, 'load_textdomain' ) );
        
        // Activation and deactivation hooks
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
    }

    /**
     * Initialize the plugin.
     */
    public function init() {
        // Only load admin functionality in admin area
        if ( is_admin() ) {
            $this->load_admin();
        }
    }

    /**
     * Load text domain for translations.
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            ZINN_WPAPI_TEXT_DOMAIN,
            false,
            dirname( plugin_basename( __FILE__ ) ) . '/languages'
        );
    }

    /**
     * Load admin functionality.
     */
    private function load_admin() {
        require_once ZINN_WPAPI_PLUGIN_PATH . 'inc/class-zinn-wpapi-admin.php';
        new Zinn_WPAPI_Admin();
    }

    /**
     * Plugin activation callback.
     */
    public function activate() {
        // Flush rewrite rules to ensure REST API endpoints work properly
        flush_rewrite_rules();
        
        // Set default options if they don't exist
        if ( false === get_option( 'zinnwpapi_settings' ) ) {
            add_option( 'zinnwpapi_settings', array() );
        }
    }

    /**
     * Plugin deactivation callback.
     */
    public function deactivate() {
        // Flush rewrite rules on deactivation
        flush_rewrite_rules();
        
        // Clear any cached data
        wp_cache_flush();
    }
}

// Initialize the plugin
Zinn_WPAPI_Manager::get_instance();
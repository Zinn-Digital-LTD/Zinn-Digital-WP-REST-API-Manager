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
*/

if ( ! defined( 'ABSPATH' ) ) exit;

define('ZINN_WPAPI_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ZINN_WPAPI_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('ZINN_WPAPI_VERSION', '1.0.0');

require_once ZINN_WPAPI_PLUGIN_PATH . 'inc/class-zinn-wpapi-admin.php';

class Zinn_WPAPI_Manager {

    public function __construct() {
        add_action('plugins_loaded', [$this, 'init']);
    }

    public function init() {
        if (is_admin()) {
            new Zinn_WPAPI_Admin();
        }
    }
}

new Zinn_WPAPI_Manager();

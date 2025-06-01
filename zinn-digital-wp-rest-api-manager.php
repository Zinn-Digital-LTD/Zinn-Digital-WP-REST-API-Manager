<?php defined( 'ABSPATH' ) or die( 'No direct access allowed' );

/**
 * @package Zinn_Digital_WP_REST_API_Manager
 * @version 1.0.0
 */

/*
Plugin Name: Zinn Digital™ WP REST API Manager
Plugin URI: https://zinndigital.com/
Description: Enhances WordPress REST API v2 with full metas. Created by Zinn Digital™ LTD. Find settings under the settings menu in the WordPress admin panel.
Author: Zinn Digital LTD
Author URI: https://zinnhub.com/about-team/
Version: 1.0.0
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: zinn-digital-wp-rest-api-manager
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
*/

define('ZINN_ZWRAM_FIELD_PREFIX', 'zinn_zwram_field_');
define('ZINN_ZWRAM_MENUITEM_IDENTIFIER', 'zinn_wp_rest_api_manager');
define('ZINN_ZWRAM_VERSION', '1.0.0');

include __DIR__ . '/response/response.php';
include __DIR__ . '/settings/render.php';
include __DIR__ . '/settings/Controller.php';
include __DIR__ . '/settings/MetaObject.php';
$ZinnZWRAMController = new \ZinnZWRAM\Controller();
$ZinnZWRAMController->init();


// Custom CSS, JS
add_action('admin_enqueue_scripts', function ($hook) {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if (!isset($_GET['page']) || $_GET['page'] !== ZINN_ZWRAM_MENUITEM_IDENTIFIER) {
		return;
	}
	wp_enqueue_style('zinn_zwram_css', plugin_dir_url(__FILE__) . '/assets/zinn-zwram.css', array(), ZINN_ZWRAM_VERSION);
	wp_enqueue_script('zinn_zwram_js', plugin_dir_url(__FILE__) . '/assets/zinn-zwram.js', array('jquery'), ZINN_ZWRAM_VERSION, true);

	// Load WP native jQuery libraries
	wp_enqueue_script('jquery-ui-tabs');
});
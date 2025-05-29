<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Zinn_WPAPI_Admin {

    private $option_name = 'zinnwpapi_settings';

    public function __construct() {
        add_action('admin_menu', [$this, 'admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'admin_assets']);
        add_action('wp_ajax_zinn_toggle_rest_status', [$this, 'ajax_toggle_rest_status']);
        add_action('init', [$this, 'apply_rest_toggles'], 99);
    }

    public function admin_menu() {
        add_menu_page(
            __('API Manager', 'zinn-digital-wp-rest-api-manager'),
            __('API Manager', 'zinn-digital-wp-rest-api-manager'),
            'manage_options',
            'zinn-wpapi-manager',
            [$this, 'admin_page'],
            'dashicons-rest-api',
            2
        );
    }

    public function admin_assets($hook) {
        if ($hook !== 'toplevel_page_zinn-wpapi-manager') return;
        wp_enqueue_style('zinn-wpapi-admin', ZINN_WPAPI_PLUGIN_URL . 'assets/zinn-wpapi-admin.css', [], ZINN_WPAPI_VERSION);
        wp_enqueue_script('zinn-wpapi-admin', ZINN_WPAPI_PLUGIN_URL . 'assets/zinn-wpapi-admin.js', ['jquery'], ZINN_WPAPI_VERSION, true);
        wp_localize_script('zinn-wpapi-admin', 'zinnwpapi', [
            'nonce' => wp_create_nonce('zinnwpapi_toggle'),
            'ajaxurl' => admin_url('admin-ajax.php')
        ]);
    }

    public function admin_page() {
        ?>
        <div class="zinn-wpapi-admin">
            <div class="zinn-header">
                <?php
                // Check if logo file exists before displaying
                $logo_path = ZINN_WPAPI_PLUGIN_PATH . 'assets/zinn-logo.png';
                if ( file_exists( $logo_path ) ) {
                    $logo_url = ZINN_WPAPI_PLUGIN_URL . 'assets/zinn-logo.png';
                    echo '<img src="' . esc_url( $logo_url ) . '" alt="' . esc_attr__( 'Zinn Digital', 'zinn-digital-wp-rest-api-manager' ) . '" class="zinn-logo" />';
                }
                ?>
                <h1><?php esc_html_e('Zinn Digital', 'zinn-digital-wp-rest-api-manager'); ?><sup>™</sup> <?php esc_html_e('WP REST API Manager', 'zinn-digital-wp-rest-api-manager'); ?></h1>
            </div>
            <div class="zinn-links">
                <a class="zinn-btn" href="https://zinndigital.com" target="_blank">
                    <?php esc_html_e('⚡ Zinn WebHosting', 'zinn-digital-wp-rest-api-manager'); ?>
                </a>
                <a class="zinn-btn" href="https://zinnhub.com" target="_blank">
                    <?php esc_html_e('💼 Zinn Hub Marketplace', 'zinn-digital-wp-rest-api-manager'); ?>
                </a>
            </div>
            <div class="zinn-notice">
                <strong><?php esc_html_e('⚠️ Important Note:', 'zinn-digital-wp-rest-api-manager'); ?></strong> <?php esc_html_e('Custom fields (meta) can only be detected if at least', 'zinn-digital-wp-rest-api-manager'); ?> <strong><?php esc_html_e('one item', 'zinn-digital-wp-rest-api-manager'); ?></strong> <?php esc_html_e('is published in that post type. If nothing appears, add a post first!', 'zinn-digital-wp-rest-api-manager'); ?>
            </div>
            <?php $this->render_sections(); ?>
        
            <div class="zinn-support-section"><br>
                <strong><?php esc_html_e('Need Support or Help?', 'zinn-digital-wp-rest-api-manager'); ?></strong><br>
                <?php esc_html_e('If you need help with our plugin, please email us at', 'zinn-digital-wp-rest-api-manager'); ?> <a href="mailto:office@zinndigital.com">office@zinndigital.com</a>.<br>
                <ul>
                    <li><?php esc_html_e('Please include full details of any errors or issues you are experiencing.', 'zinn-digital-wp-rest-api-manager'); ?></li>
                    <li><?php esc_html_e('Attach screenshots where possible to help us understand your situation.', 'zinn-digital-wp-rest-api-manager'); ?></li>
                </ul>
                <?php esc_html_e("We're always happy to help and will respond as quickly as possible!", 'zinn-digital-wp-rest-api-manager'); ?>
            </div>
        </div>
        <?php
    }

    public function render_sections() {
        $options = get_option($this->option_name, []);
        $all_post_types = get_post_types([], 'objects');

        // Split Core/Custom Post Types
        $core_pts = [];
        $custom_pts = [];
        foreach ($all_post_types as $pt) {
            if ($pt->_builtin) $core_pts[$pt->name] = $pt;
            else $custom_pts[$pt->name] = $pt;
        }

        echo '<h2>' . esc_html__('Core Post Types', 'zinn-digital-wp-rest-api-manager') . '</h2>';
        $this->render_pt_table($core_pts, 'core_post_types', $o
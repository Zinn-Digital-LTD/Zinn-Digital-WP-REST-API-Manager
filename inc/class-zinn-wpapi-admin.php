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
            __('API Manager', 'zinn-wp-rest-api-manager'),
            __('API Manager', 'zinn-wp-rest-api-manager'),
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
                <img src="<?php echo esc_url(ZINN_WPAPI_PLUGIN_URL . 'assets/zinn-logo.png'); ?>" alt="Zinn Digital" class="zinn-logo" />
                <h1>Zinn Digital<sup>™</sup> WP REST API Manager</h1>
            </div>
            <div class="zinn-links">
    <a class="zinn-btn" href="https://zinndigital.com" target="_blank">
        ⚡ Zinn WebHosting
    </a>
    <a class="zinn-btn" href="https://zinnhub.com" target="_blank">
        💼 Zinn Hub Marketplace
    </a>
</div>
            <div class="zinn-notice">
    <strong>⚠️ Important Note:</strong> Custom fields (meta) can only be detected if at least <strong>one item</strong> is published in that post type. If nothing appears, add a post first!
            </div>
            <?php $this->render_sections(); ?>
        
    <div class="zinn-support-section"><br>
        <strong>Need Support or Help?</strong><br>
        If you need help with our plugin, please email us at <a href="mailto:office@zinndigital.com">office@zinndigital.com</a>.<br>
        <ul>
            <li>Please include full details of any errors or issues you are experiencing.</li>
            <li>Attach screenshots where possible to help us understand your situation.</li>
        </ul>
        We’re always happy to help and will respond as quickly as possible!
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

        echo '<h2>Core Post Types</h2>';
        $this->render_pt_table($core_pts, 'core_post_types', $options);

        echo '<h2>Custom Post Types</h2>';
        $this->render_pt_table($custom_pts, 'custom_post_types', $options);

        // Taxonomies: Core and Custom
        $all_taxes = get_taxonomies([], 'objects');
        $core_taxes = [];
        $custom_taxes = [];
        foreach ($all_taxes as $tax) {
            if (in_array($tax->name, ['category','post_tag','nav_menu','link_category','post_format'])) $core_taxes[$tax->name] = $tax;
            else $custom_taxes[$tax->name] = $tax;
        }

        echo '<h2>Core Taxonomies</h2>';
        $this->render_tax_table($core_taxes, 'core_taxonomies', $options);

        echo '<h2>Custom Taxonomies</h2>';
        $this->render_tax_table($custom_taxes, 'custom_taxonomies', $options);

        // Meta Section
        $this->render_meta_section($options, $custom_pts);
    }

    private function render_pt_table($pts, $section, $options) {
        if(empty($pts)) { echo '<p>None found.</p>'; return; }
        echo '<table class="zinn-table"><thead><tr><th>Name</th><th>REST Base</th><th>REST Exposed?</th><th>Toggle</th></tr></thead><tbody>';
        foreach($pts as $pt) {
            $saved = isset($options[$section][$pt->name]) ? $options[$section][$pt->name] : $pt->show_in_rest;
            $exposed = $saved ? 1 : 0;
            echo '<tr>
                <td>' . esc_html($pt->labels->singular_name) . ' <span>(' . esc_html($pt->name) . ')</span></td>
                <td>' . esc_html($pt->rest_base ?: '-') . '</td>
                <td><span class="zinn-badge '.($exposed ? 'exposed':'not-exposed').'">'.($exposed ? 'Yes':'No').'</span></td>
                <td><button class="zinn-rest-toggle" data-type="pt" data-key="'.esc_attr($pt->name).'" data-section="'.$section.'" data-exposed="'.$exposed.'">'.($exposed ? 'Unexpose':'Expose').'</button></td>
            </tr>';
        }
        echo '</tbody></table>';
    }

    private function render_tax_table($taxes, $section, $options) {
        if(empty($taxes)) { echo '<p>None found.</p>'; return; }
        echo '<table class="zinn-table"><thead><tr><th>Name</th><th>REST Base</th><th>REST Exposed?</th><th>Toggle</th></tr></thead><tbody>';
        foreach($taxes as $tax) {
            $saved = isset($options[$section][$tax->name]) ? $options[$section][$tax->name] : $tax->show_in_rest;
            $exposed = $saved ? 1 : 0;
            echo '<tr>
                <td>' . esc_html($tax->labels->singular_name) . ' <span>(' . esc_html($tax->name) . ')</span></td>
                <td>' . esc_html($tax->rest_base ?: '-') . '</td>
                <td><span class="zinn-badge '.($exposed ? 'exposed':'not-exposed').'">'.($exposed ? 'Yes':'No').'</span></td>
                <td><button class="zinn-rest-toggle" data-type="tax" data-key="'.esc_attr($tax->name).'" data-section="'.$section.'" data-exposed="'.$exposed.'">'.($exposed ? 'Unexpose':'Expose').'</button></td>
            </tr>';
        }
        echo '</tbody></table>';
    }

    public function render_meta_section($options, $custom_pts) {
        global $wpdb;
        echo '<h2>Custom Fields (Meta)</h2>';
        foreach ($custom_pts as $pt_name => $pt) {
            $query = $wpdb->prepare(
                "SELECT DISTINCT meta_key FROM {$wpdb->postmeta} pm INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE p.post_type=%s AND meta_key NOT LIKE '\_%' LIMIT 100",
                $pt_name
            );
            $meta_keys = $wpdb->get_col($query);
            echo '<h3 class="zinn-pt-label">'.esc_html($pt->labels->singular_name).' <span>('.esc_html($pt_name).')</span></h3>';
            if(empty($meta_keys)) {
                echo '<div class="zinn-meta-warning">No meta fields detected. Please add/publish at least one item in this post type to detect its custom fields!</div>';
                continue;
            }
            echo '<table class="zinn-table"><thead><tr><th>Meta Key</th><th>REST Exposed?</th><th>Toggle</th></tr></thead><tbody>';
            foreach($meta_keys as $meta_key) {
                $toggled = !empty($options['meta'][$pt_name]) && in_array($meta_key, $options['meta'][$pt_name]);
                echo '<tr>
                    <td>' . esc_html($meta_key) . '</td>
                    <td><span class="zinn-badge '.($toggled ? 'exposed':'not-exposed').'">'.($toggled ? 'Yes':'No').'</span></td>
                    <td><button class="zinn-rest-toggle" data-type="meta" data-key="'.esc_attr($meta_key).'" data-section="'.esc_attr($pt_name).'" data-exposed="'.($toggled?1:0).'">'.($toggled?'Unexpose':'Expose').'</button></td>
                </tr>';
            }
            echo '</tbody></table>';
        }
    }

    public function ajax_toggle_rest_status() {
        check_ajax_referer('zinnwpapi_toggle');
        if (!current_user_can('manage_options')) wp_send_json_error('No permission');
        $type = sanitize_text_field($_POST['type'] ?? '');
        $key = sanitize_text_field($_POST['key'] ?? '');
        $section = sanitize_text_field($_POST['section'] ?? '');
        $exposed = isset($_POST['exposed']) && $_POST['exposed'] == '1';
        $options = get_option($this->option_name, []);

        // Toggle logic by type
        if($type === 'pt' || $type === 'tax') {
            if (!isset($options[$section])) $options[$section] = [];
            $options[$section][$key] = $exposed ? 0 : 1;
        }
        if($type === 'meta') {
            if (!isset($options['meta'])) $options['meta'] = [];
            if (!isset($options['meta'][$section])) $options['meta'][$section] = [];
            if ($exposed) {
                $options['meta'][$section] = array_diff($options['meta'][$section], [$key]);
            } else {
                $options['meta'][$section][] = $key;
                $options['meta'][$section] = array_unique($options['meta'][$section]);
            }
        }
        update_option($this->option_name, $options);
        wp_send_json_success(['message' => 'Updated!']);
    }

    public function apply_rest_toggles() {
        $options = get_option($this->option_name, []);
        // CPTs
        if (!empty($options['core_post_types'])) {
            add_filter('register_post_type_args', function($args, $post_type) use ($options){
                if (isset($options['core_post_types'][$post_type])) {
                    $args['show_in_rest'] = (bool)$options['core_post_types'][$post_type];
                }
                return $args;
            }, 10, 2);
        }
        if (!empty($options['custom_post_types'])) {
            add_filter('register_post_type_args', function($args, $post_type) use ($options){
                if (isset($options['custom_post_types'][$post_type])) {
                    $args['show_in_rest'] = (bool)$options['custom_post_types'][$post_type];
                }
                return $args;
            }, 10, 2);
        }
        // Taxonomies
        if (!empty($options['core_taxonomies'])) {
            add_filter('register_taxonomy_args', function($args, $taxonomy) use ($options){
                if (isset($options['core_taxonomies'][$taxonomy])) {
                    $args['show_in_rest'] = (bool)$options['core_taxonomies'][$taxonomy];
                }
                return $args;
            }, 10, 2);
        }
        if (!empty($options['custom_taxonomies'])) {
            add_filter('register_taxonomy_args', function($args, $taxonomy) use ($options){
                if (isset($options['custom_taxonomies'][$taxonomy])) {
                    $args['show_in_rest'] = (bool)$options['custom_taxonomies'][$taxonomy];
                }
                return $args;
            }, 10, 2);
        }
        // Meta
        if (!empty($options['meta'])) {
            foreach ($options['meta'] as $pt => $meta_keys) {
                foreach ($meta_keys as $meta_key) {
                    register_post_meta($pt, $meta_key, [
                        'show_in_rest' => true,
                        'type' => 'string',
                        'single' => true,
                        'auth_callback' => function() { return current_user_can('edit_posts'); }
                    ]);
                }
            }
        }
    }
}

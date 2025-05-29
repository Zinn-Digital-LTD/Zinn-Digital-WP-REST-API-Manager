<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Zinn_WPAPI_Admin {

    private $option_name = 'zinnwpapi_settings';

    public function __construct() {
        add_action('admin_menu', [$this, 'admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'admin_assets']);
        add_action('wp_ajax_zinn_toggle_rest_status', [$this, 'ajax_toggle_rest_status']);
        // Hook very early for filters
        add_action('init', [$this, 'apply_rest_toggles'], 0);
        // Also hook to modify already registered post types
        add_action('rest_api_init', [$this, 'modify_registered_post_types'], 0);
        // Check if we need to flush rewrite rules
        add_action('admin_init', [$this, 'maybe_flush_rewrite_rules']);
    }

    /**
     * Check if we need to flush rewrite rules
     */
    public function maybe_flush_rewrite_rules() {
        if (get_transient('zinn_wpapi_flush_rewrite_rules')) {
            flush_rewrite_rules();
            delete_transient('zinn_wpapi_flush_rewrite_rules');
        }
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
                // Logo display - using inline image for plugin bundled assets
                $logo_path = ZINN_WPAPI_PLUGIN_PATH . 'assets/zinn-logo.png';
                if ( file_exists( $logo_path ) ) {
                    $logo_url = ZINN_WPAPI_PLUGIN_URL . 'assets/zinn-logo.png';
                    ?>
                    <img src="<?php echo esc_url( $logo_url ); ?>" 
                         alt="<?php echo esc_attr__( 'Zinn Digital', 'zinn-digital-wp-rest-api-manager' ); ?>" 
                         class="zinn-logo" 
                         width="58" 
                         height="58" />
                    <?php
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
        $this->render_pt_table($core_pts, 'core_post_types', $options);

        echo '<h2>' . esc_html__('Custom Post Types', 'zinn-digital-wp-rest-api-manager') . '</h2>';
        if (!empty($custom_pts)) {
            $this->render_pt_table($custom_pts, 'custom_post_types', $options);
        } else {
            echo '<p>' . esc_html__('No custom post types detected.', 'zinn-digital-wp-rest-api-manager') . '</p>';
        }

        // Taxonomies
        $all_taxonomies = get_taxonomies([], 'objects');
        $core_taxs = [];
        $custom_taxs = [];
        foreach ($all_taxonomies as $tax) {
            if ($tax->_builtin) $core_taxs[$tax->name] = $tax;
            else $custom_taxs[$tax->name] = $tax;
        }

        echo '<h2>' . esc_html__('Core Taxonomies', 'zinn-digital-wp-rest-api-manager') . '</h2>';
        $this->render_tax_table($core_taxs, 'core_taxonomies', $options);

        echo '<h2>' . esc_html__('Custom Taxonomies', 'zinn-digital-wp-rest-api-manager') . '</h2>';
        if (!empty($custom_taxs)) {
            $this->render_tax_table($custom_taxs, 'custom_taxonomies', $options);
        } else {
            echo '<p>' . esc_html__('No custom taxonomies detected.', 'zinn-digital-wp-rest-api-manager') . '</p>';
        }

        // Meta Fields
        echo '<h2>' . esc_html__('Custom Fields (Meta)', 'zinn-digital-wp-rest-api-manager') . '</h2>';
        $this->render_meta_section($all_post_types, $options);
    }

    private function render_pt_table($post_types, $section, $options) {
        ?>
        <table class="zinn-table">
            <thead>
                <tr>
                    <th><?php esc_html_e('Name', 'zinn-digital-wp-rest-api-manager'); ?></th>
                    <th><?php esc_html_e('REST Base', 'zinn-digital-wp-rest-api-manager'); ?></th>
                    <th><?php esc_html_e('REST Exposed?', 'zinn-digital-wp-rest-api-manager'); ?></th>
                    <th><?php esc_html_e('Toggle', 'zinn-digital-wp-rest-api-manager'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($post_types as $pt_name => $pt): 
                    // Check if we have a saved state for this post type
                    if (isset($options[$section][$pt_name])) {
                        // Use saved state
                        $exposed = (bool)$options[$section][$pt_name];
                    } else {
                        // Use current WordPress state
                        $exposed = !empty($pt->show_in_rest);
                    }
                    
                    $rest_base = !empty($pt->rest_base) ? $pt->rest_base : (!empty($pt->show_in_rest) ? $pt_name : '-');
                ?>
                <tr>
                    <td><span class="zinn-pt-label"><?php echo esc_html($pt->label); ?></span> <span>(<?php echo esc_html($pt_name); ?>)</span></td>
                    <td><?php echo esc_html($rest_base); ?></td>
                    <td><span class="zinn-badge <?php echo $exposed ? 'exposed' : 'not-exposed'; ?>"><?php echo $exposed ? esc_html__('Yes', 'zinn-digital-wp-rest-api-manager') : esc_html__('No', 'zinn-digital-wp-rest-api-manager'); ?></span></td>
                    <td>
                        <button class="zinn-rest-toggle" 
                                data-key="<?php echo esc_attr($pt_name); ?>" 
                                data-section="<?php echo esc_attr($section); ?>" 
                                data-type="post_type"
                                data-exposed="<?php echo $exposed ? '1' : '0'; ?>">
                            <?php echo $exposed ? esc_html__('Unexpose', 'zinn-digital-wp-rest-api-manager') : esc_html__('Expose', 'zinn-digital-wp-rest-api-manager'); ?>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }

    private function render_tax_table($taxonomies, $section, $options) {
        ?>
        <table class="zinn-table">
            <thead>
                <tr>
                    <th><?php esc_html_e('Name', 'zinn-digital-wp-rest-api-manager'); ?></th>
                    <th><?php esc_html_e('REST Base', 'zinn-digital-wp-rest-api-manager'); ?></th>
                    <th><?php esc_html_e('REST Exposed?', 'zinn-digital-wp-rest-api-manager'); ?></th>
                    <th><?php esc_html_e('Toggle', 'zinn-digital-wp-rest-api-manager'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($taxonomies as $tax_name => $tax): 
                    // Check if we have a saved state for this taxonomy
                    if (isset($options[$section][$tax_name])) {
                        // Use saved state
                        $exposed = (bool)$options[$section][$tax_name];
                    } else {
                        // Use current WordPress state
                        $exposed = !empty($tax->show_in_rest);
                    }
                    
                    $rest_base = !empty($tax->rest_base) ? $tax->rest_base : (!empty($tax->show_in_rest) ? $tax_name : '-');
                ?>
                <tr>
                    <td><span class="zinn-pt-label"><?php echo esc_html($tax->label); ?></span> <span>(<?php echo esc_html($tax_name); ?>)</span></td>
                    <td><?php echo esc_html($rest_base); ?></td>
                    <td><span class="zinn-badge <?php echo $exposed ? 'exposed' : 'not-exposed'; ?>"><?php echo $exposed ? esc_html__('Yes', 'zinn-digital-wp-rest-api-manager') : esc_html__('No', 'zinn-digital-wp-rest-api-manager'); ?></span></td>
                    <td>
                        <button class="zinn-rest-toggle" 
                                data-key="<?php echo esc_attr($tax_name); ?>" 
                                data-section="<?php echo esc_attr($section); ?>" 
                                data-type="taxonomy"
                                data-exposed="<?php echo $exposed ? '1' : '0'; ?>">
                            <?php echo $exposed ? esc_html__('Unexpose', 'zinn-digital-wp-rest-api-manager') : esc_html__('Expose', 'zinn-digital-wp-rest-api-manager'); ?>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }

    private function render_meta_section($post_types, $options) {
        global $wpdb;
        
        foreach ($post_types as $pt_name => $pt) {
            if (in_array($pt_name, ['revision', 'nav_menu_item', 'custom_css', 'customize_changeset', 'oembed_cache', 'user_request', 'wp_block', 'wp_template'])) {
                continue;
            }

            // Get cached meta keys if available
            $cache_key = 'zinn_wpapi_meta_' . $pt_name;
            $meta_keys = wp_cache_get($cache_key);

            if (false === $meta_keys) {
                // Get meta keys for this post type using prepared statement
                $meta_keys = $wpdb->get_col(
                    $wpdb->prepare(
                        "SELECT DISTINCT pm.meta_key 
                        FROM {$wpdb->postmeta} pm 
                        INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID 
                        WHERE p.post_type = %s 
                        AND pm.meta_key NOT LIKE %s 
                        ORDER BY pm.meta_key",
                        $pt_name,
                        $wpdb->esc_like('_') . '%'
                    )
                );
                
                // Cache the results for 1 hour
                wp_cache_set($cache_key, $meta_keys, '', HOUR_IN_SECONDS);
            }

            if (!empty($meta_keys)) {
                echo '<h3>' . esc_html($pt->label) . ' (' . esc_html($pt_name) . ')</h3>';
                ?>
                <table class="zinn-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Meta Key', 'zinn-digital-wp-rest-api-manager'); ?></th>
                            <th><?php esc_html_e('REST Exposed?', 'zinn-digital-wp-rest-api-manager'); ?></th>
                            <th><?php esc_html_e('Toggle', 'zinn-digital-wp-rest-api-manager'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($meta_keys as $meta_key): 
                            $exposed = isset($options['meta'][$pt_name][$meta_key]) && $options['meta'][$pt_name][$meta_key];
                        ?>
                        <tr>
                            <td><?php echo esc_html($meta_key); ?></td>
                            <td><span class="zinn-badge <?php echo $exposed ? 'exposed' : 'not-exposed'; ?>"><?php echo $exposed ? esc_html__('Yes', 'zinn-digital-wp-rest-api-manager') : esc_html__('No', 'zinn-digital-wp-rest-api-manager'); ?></span></td>
                            <td>
                                <button class="zinn-rest-toggle" 
                                        data-key="<?php echo esc_attr($meta_key); ?>" 
                                        data-section="meta_<?php echo esc_attr($pt_name); ?>" 
                                        data-type="meta"
                                        data-exposed="<?php echo $exposed ? '1' : '0'; ?>">
                                    <?php echo $exposed ? esc_html__('Unexpose', 'zinn-digital-wp-rest-api-manager') : esc_html__('Expose', 'zinn-digital-wp-rest-api-manager'); ?>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php
            } else {
                echo '<h3>' . esc_html($pt->label) . ' (' . esc_html($pt_name) . ')</h3>';
                echo '<p class="zinn-meta-warning">' . esc_html__('No meta fields detected. Please add/publish at least one item in this post type to detect its custom fields!', 'zinn-digital-wp-rest-api-manager') . '</p>';
            }
        }
    }

    public function ajax_toggle_rest_status() {
        check_ajax_referer('zinnwpapi_toggle', '_ajax_nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Permission denied', 'zinn-digital-wp-rest-api-manager'));
        }

        // Validate and sanitize input
        if (!isset($_POST['key']) || !isset($_POST['section']) || !isset($_POST['type']) || !isset($_POST['exposed'])) {
            wp_die(esc_html__('Missing required parameters', 'zinn-digital-wp-rest-api-manager'));
        }

        $key = sanitize_text_field(wp_unslash($_POST['key']));
        $section = sanitize_text_field(wp_unslash($_POST['section']));
        $type = sanitize_text_field(wp_unslash($_POST['type']));
        $exposed = intval($_POST['exposed']);

        $options = get_option($this->option_name, []);

        if ($type === 'meta') {
            // Extract post type from section (meta_post_type)
            $pt_name = str_replace('meta_', '', $section);
            if (!isset($options['meta'])) $options['meta'] = [];
            if (!isset($options['meta'][$pt_name])) $options['meta'][$pt_name] = [];
            $options['meta'][$pt_name][$key] = !$exposed;
        } else {
            if (!isset($options[$section])) $options[$section] = [];
            $options[$section][$key] = !$exposed;
        }

        update_option($this->option_name, $options);
        
        // Clear meta cache when meta fields are toggled
        if ($type === 'meta') {
            $pt_name = str_replace('meta_', '', $section);
            wp_cache_delete('zinn_wpapi_meta_' . $pt_name);
        }
        
        // Set a transient to flush rewrite rules on next page load
        set_transient('zinn_wpapi_flush_rewrite_rules', true, 60);
        
        wp_send_json_success();
    }

    public function apply_rest_toggles() {
        $options = get_option($this->option_name, []);

        // Apply post type toggles - this works for custom post types registered after this hook
        foreach (['core_post_types', 'custom_post_types'] as $section) {
            if (!empty($options[$section])) {
                foreach ($options[$section] as $pt_name => $exposed) {
                    add_filter('register_post_type_args', function($args, $post_type) use ($pt_name, $exposed) {
                        if ($post_type === $pt_name) {
                            $args['show_in_rest'] = (bool)$exposed;
                        }
                        return $args;
                    }, 10, 2);
                }
            }
        }

        // Apply taxonomy toggles
        foreach (['core_taxonomies', 'custom_taxonomies'] as $section) {
            if (!empty($options[$section])) {
                foreach ($options[$section] as $tax_name => $exposed) {
                    add_filter('register_taxonomy_args', function($args, $taxonomy) use ($tax_name, $exposed) {
                        if ($taxonomy === $tax_name) {
                            $args['show_in_rest'] = (bool)$exposed;
                        }
                        return $args;
                    }, 10, 2);
                }
            }
        }

        // Apply meta field toggles
        if (!empty($options['meta'])) {
            foreach ($options['meta'] as $pt_name => $meta_fields) {
                foreach ($meta_fields as $meta_key => $exposed) {
                    if ($exposed) {
                        register_post_meta($pt_name, $meta_key, [
                            'show_in_rest' => true,
                            'single' => true,
                            'type' => 'string'
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Modify already registered post types and taxonomies
     * This is needed for core types that are registered before our filters run
     */
    public function modify_registered_post_types() {
        $options = get_option($this->option_name, []);
        
        // Modify registered post types
        global $wp_post_types;
        
        foreach (['core_post_types', 'custom_post_types'] as $section) {
            if (!empty($options[$section])) {
                foreach ($options[$section] as $pt_name => $exposed) {
                    if (isset($wp_post_types[$pt_name])) {
                        $wp_post_types[$pt_name]->show_in_rest = (bool)$exposed;
                        
                        // Set REST base if not already set and exposing to REST
                        if ($exposed && empty($wp_post_types[$pt_name]->rest_base)) {
                            $wp_post_types[$pt_name]->rest_base = $pt_name;
                        }
                        
                        // Set REST controller if not already set and exposing to REST
                        if ($exposed && empty($wp_post_types[$pt_name]->rest_controller_class)) {
                            $wp_post_types[$pt_name]->rest_controller_class = 'WP_REST_Posts_Controller';
                        }
                    }
                }
            }
        }
        
        // Modify registered taxonomies
        global $wp_taxonomies;
        
        foreach (['core_taxonomies', 'custom_taxonomies'] as $section) {
            if (!empty($options[$section])) {
                foreach ($options[$section] as $tax_name => $exposed) {
                    if (isset($wp_taxonomies[$tax_name])) {
                        $wp_taxonomies[$tax_name]->show_in_rest = (bool)$exposed;
                        
                        // Set REST base if not already set and exposing to REST
                        if ($exposed && empty($wp_taxonomies[$tax_name]->rest_base)) {
                            $wp_taxonomies[$tax_name]->rest_base = $tax_name;
                        }
                        
                        // Set REST controller if not already set and exposing to REST
                        if ($exposed && empty($wp_taxonomies[$tax_name]->rest_controller_class)) {
                            $wp_taxonomies[$tax_name]->rest_controller_class = 'WP_REST_Terms_Controller';
                        }
                    }
                }
            }
        }
    }
}
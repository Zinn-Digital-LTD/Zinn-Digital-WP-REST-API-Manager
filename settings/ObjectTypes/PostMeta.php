<?php

namespace ZinnZWRAM;

Class PostsMeta extends MetaObject {

	private $metaItemKeys = [];

	// If true, meta keys used in multiple post-types will be moved to special section "universal metas"
	private $useUniversalMetas = true;
	private $universalMetas = [];

	public function init()
	{
		global $wpdb;
		$this->metaItemKeys = $this->getPostMetaItems();
		
		// Add sanitization callback
		register_setting(
			'zinn_zwram_post', 
			'zinn_zwram_options_post',
			array(
				'sanitize_callback' => array($this, 'sanitize_options')
			)
		);

		add_settings_section(
			'zinn_zwram_section_posts',
			'', // Empty string is fine here, no need to translate
			function ()  {
				?>
				<p><?php esc_html_e('Select posts metadata to include in REST API response', 'zinn-digital-wp-rest-api-manager'); ?></p>
				<p><a class="uncheck_all" data-status="0"><?php esc_html_e('Un/check all', 'zinn-digital-wp-rest-api-manager'); ?></a></p>
				<?php
			},
			'zinn_zwram_post'
		);

		if ($this->useUniversalMetas) {
			$metas = [];
			foreach ($this->metaItemKeys as $row) {
				$metas[$row->meta_key][] = $row->post_type;
				$metas[$row->meta_key] = array_unique($metas[$row->meta_key]);
			}
			if (!empty($metas)) {
				foreach ($metas as $metaKey => $usls) {
					if (count($usls) > 1) {
						$this->universalMetas[] = $metaKey;
					}
				}
			}
		}

		$lastObjectType = '';
		foreach ($this->metaItemKeys as $row) {

			$objectType = $row->post_type;
			$metaItem   = $row->meta_key;

			// Used just to divide meta items to labeled post-type subsections
			if ($lastObjectType != $objectType) {
				add_settings_field(
					ZINN_ZWRAM_FIELD_PREFIX . '_post_sub_section_' . $objectType,
					"<h3>" . esc_html($objectType) . "</h3>", // Escape post type name
					function ($args) use($metaItem, $objectType, $lastObjectType) {
						echo '';
					},
					'zinn_zwram_post',
					'zinn_zwram_section_posts',
					[
						'label_for' => ZINN_ZWRAM_FIELD_PREFIX . $metaItem . 'usls',
						'class' => 'zinn_zwram_row',
						'zinn_zwram_custom_data' => 'custom'
					]
				);
			}

			if ($this->useUniversalMetas && in_array($metaItem, $this->universalMetas)) {
				continue;
			}

			add_settings_field(
				ZINN_ZWRAM_FIELD_PREFIX . $metaItem,
				esc_html($metaItem), // Don't translate meta field names
				function ($args) use($metaItem, $objectType, $lastObjectType) {
					$options = get_option('zinn_zwram_options_post');
					?>
					<input type="checkbox" name="zinn_zwram_options_post[<?php echo esc_attr($args['label_for']); ?>]" value="1" <?php if (isset($options[$args['label_for']]) && $options[$args['label_for']] == 1) echo 'checked' ?> id="<?php echo esc_attr(ZINN_ZWRAM_FIELD_PREFIX . $metaItem); ?>">
					<?php
				},
				'zinn_zwram_post',
				'zinn_zwram_section_posts',
				[
					'label_for' => ZINN_ZWRAM_FIELD_PREFIX . $metaItem,
					'class' => 'zinn_zwram_row',
					'zinn_zwram_custom_data' => 'custom'
				]
			);
			$lastObjectType = $objectType;
		}

		if ($this->useUniversalMetas && !empty($this->universalMetas)) {

			// Labeled section
			add_settings_field(
				ZINN_ZWRAM_FIELD_PREFIX . '_post_sub_section_universal',
				"<h3>" . esc_html__('Universal metas', 'zinn-digital-wp-rest-api-manager') . "</h3><p class='desc'>" . esc_html__('Meta keys used in multiple post types', 'zinn-digital-wp-rest-api-manager') . "</p>",
				function ($args) {
					echo '';
				},
				'zinn_zwram_post',
				'zinn_zwram_section_posts',
				[
					'label_for' => ZINN_ZWRAM_FIELD_PREFIX . 'universal',
					'class' => 'zinn_zwram_row',
					'zinn_zwram_custom_data' => 'custom'
				]
			);

			foreach ($this->universalMetas as $metaItem) {
				add_settings_field(
					ZINN_ZWRAM_FIELD_PREFIX . $metaItem,
					esc_html($metaItem), // Don't translate meta field names
					function ($args) use($metaItem) {
						$options = get_option('zinn_zwram_options_post');
						?>
						<input type="checkbox" name="zinn_zwram_options_post[<?php echo esc_attr($args['label_for']); ?>]" value="1" <?php if (isset($options[$args['label_for']]) && $options[$args['label_for']] == 1) echo 'checked' ?> id="<?php echo esc_attr(ZINN_ZWRAM_FIELD_PREFIX . $metaItem); ?>">
						<?php
					},
					'zinn_zwram_post',
					'zinn_zwram_section_posts',
					[
						'label_for' => ZINN_ZWRAM_FIELD_PREFIX . $metaItem,
						'class' => 'zinn_zwram_row',
						'zinn_zwram_custom_data' => 'custom'
					]
				);
			}
		}
	}

	/**
	 * Gets all metadata related to post object type
	 * @return array
	 */
	public function getPostMetaItems()
	{
		global $wpdb;
		
		// Try to get from cache first
		$cache_key = 'zinn_zwram_post_meta_items';
		$cached_result = wp_cache_get($cache_key);
		
		if ($cached_result !== false) {
			return $cached_result;
		}
		
		// Use a static query string to avoid interpolation issues
		$sql_query = "
			SELECT DISTINCT
				p.post_type,
				m.meta_key
			FROM wp_postmeta m
			INNER JOIN wp_posts p ON p.ID = m.post_id
			ORDER BY 
				post_type,
				meta_key
		";
		
		// Replace wp_ with actual prefix
		$sql_query = str_replace('wp_', $wpdb->prefix, $sql_query);
		
		// Execute query - we can't use prepare() here because there are no user inputs
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		$meta_keys = $wpdb->get_results($sql_query);
		
		// Cache the result for 1 hour
		wp_cache_set($cache_key, $meta_keys, '', HOUR_IN_SECONDS);
		
		return $meta_keys;
	}

	/**
	 * Sanitize options
	 */
	public function sanitize_options($input) {
		if (!is_array($input)) {
			return array();
		}
		
		$sanitized = array();
		foreach ($input as $key => $value) {
			// Only allow fields that start with our prefix and have value of 1
			if (strpos($key, ZINN_ZWRAM_FIELD_PREFIX) === 0 && $value == '1') {
				$sanitized[$key] = 1;
			}
		}
		
		return $sanitized;
	}
}
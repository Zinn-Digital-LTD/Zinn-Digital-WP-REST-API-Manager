<?php

namespace ZinnZWRAM;

Class CommentsMeta extends MetaObject {

	private $metaItemKeys = [];

	public function init()
	{
		global $wpdb;
		$this->metaItemKeys = $this->getMetaItems($wpdb->commentmeta);
		
		// Add sanitization callback
		register_setting(
			'zinn_zwram_comment', 
			'zinn_zwram_options_comment',
			array(
				'sanitize_callback' => array($this, 'sanitize_options')
			)
		);

		add_settings_section(
			'zinn_zwram_section_comments',
			'', // Empty string is fine here, no need to translate
			function ()  {
				?>
				<p><?php esc_html_e('Select comments metadata to include in REST API response', 'zinn-digital-wp-rest-api-manager'); ?></p>
				<p><a class="uncheck_all" data-status="0"><?php esc_html_e('Un/check all', 'zinn-digital-wp-rest-api-manager'); ?></a></p>
				<?php
			},
			'zinn_zwram_comment'
		);

		foreach ($this->metaItemKeys as $metaItem) {
			add_settings_field(
				ZINN_ZWRAM_FIELD_PREFIX . $metaItem,
				esc_html($metaItem), // Don't translate meta field names
				function ($args) use($metaItem) {
					$options = get_option('zinn_zwram_options_comment');
					?>
					<input type="checkbox" name="zinn_zwram_options_comment[<?php echo esc_attr($args['label_for']); ?>]" value="1" <?php if (isset($options[$args['label_for']]) && $options[$args['label_for']] == 1) echo 'checked' ?> id="<?php echo esc_attr(ZINN_ZWRAM_FIELD_PREFIX . $metaItem); ?>">
					<?php
				},
				'zinn_zwram_comment',
				'zinn_zwram_section_comments',
				[
					'label_for' => ZINN_ZWRAM_FIELD_PREFIX . $metaItem,
					'class' => 'zinn_zwram_row',
					'zinn_zwram_custom_data' => 'custom'
				]
			);
		}
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
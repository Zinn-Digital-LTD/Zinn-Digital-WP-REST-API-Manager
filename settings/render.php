<?php defined('ABSPATH') or die('No direct access allowed');


/**
 * Display options page as sub-item of Settings menu
 */
add_action ('admin_menu', function () {
	add_options_page(
		'WP REST API Manager',
		'WP REST API Manager',
		'manage_options',
		ZINN_ZWRAM_MENUITEM_IDENTIFIER,
		'zinn_zwram_options_page_html'
	);
});


/**
 * Render the options page HTML
 */
function zinn_zwram_options_page_html()
{
	if (!current_user_can('manage_options')) {
		return;
	}
	settings_errors('zinn_zwram_messages');
	?>
	<div class="zinn-zwram-admin">
		<div class="zinn-header">
			<?php
			// Logo display
			$logo_path = plugin_dir_path(dirname(__FILE__)) . 'assets/zinn-logo.png';
			if (file_exists($logo_path)) {
				$logo_url = plugin_dir_url(dirname(__FILE__)) . 'assets/zinn-logo.png';
				?>
				<img src="<?php echo esc_url($logo_url); ?>" 
					 alt="<?php echo esc_attr__('Zinn Digital', 'zinn-digital-wp-rest-api-manager'); ?>" 
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
			<strong><?php esc_html_e('⚠️ Important Note:', 'zinn-digital-wp-rest-api-manager'); ?></strong> 
			<?php esc_html_e('Select which custom metadata fields to include in REST API responses. This plugin works with all meta fields including those created by ACF, Meta Box, Pods, and other plugins.', 'zinn-digital-wp-rest-api-manager'); ?>
		</div>

		<div id="zinn_zwram_tabs" class="zinn_zwram_settings_container">
			<ul>
				<li><a href="#tabs-1"><?php esc_html_e('Posts', 'zinn-digital-wp-rest-api-manager'); ?></a></li>
				<li><a href="#tabs-2"><?php esc_html_e('Comments', 'zinn-digital-wp-rest-api-manager'); ?></a></li>
				<li><a href="#tabs-3"><?php esc_html_e('Users', 'zinn-digital-wp-rest-api-manager'); ?></a></li>
				<li><a href="#tabs-4"><?php esc_html_e('Terms', 'zinn-digital-wp-rest-api-manager'); ?></a></li>
			</ul>

			<div id="tabs-1">
				<form action="options.php" method="post" id="zinn_zwram_form_post">
					<?php
					settings_fields('zinn_zwram_post');
					do_settings_sections('zinn_zwram_post');
					submit_button('Save Settings');
					?>
				</form>
			</div>

			<div id="tabs-2">
				<form action="options.php" method="post" id="zinn_zwram_form_comment">
					<?php
					settings_fields('zinn_zwram_comment');
					do_settings_sections('zinn_zwram_comment');
					submit_button('Save Settings');
					?>
				</form>
			</div>

			<div id="tabs-3">
				<form action="options.php" method="post" id="zinn_zwram_form_user">
					<?php
					settings_fields('zinn_zwram_user');
					do_settings_sections('zinn_zwram_user');
					submit_button('Save Settings');
					?>
				</form>
			</div>

			<div id="tabs-4">
				<form action="options.php" method="post" id="zinn_zwram_form_term">
					<input type="hidden" name="object_type" value="term">
					<?php
					settings_fields('zinn_zwram_term');
					do_settings_sections('zinn_zwram_term');
					submit_button('Save Settings');
					?>
				</form>
			</div>
		</div>
		
		<div class="zinn-support-section"><br>
			<strong><?php esc_html_e('Need Support or Help?', 'zinn-digital-wp-rest-api-manager'); ?></strong><br>
			<?php esc_html_e('If you need help with our plugin, please email us at', 'zinn-digital-wp-rest-api-manager'); ?> 
			<a href="mailto:office@zinndigital.com">office@zinndigital.com</a>.<br>
			<ul>
				<li><?php esc_html_e('Please include full details of any errors or issues you are experiencing.', 'zinn-digital-wp-rest-api-manager'); ?></li>
				<li><?php esc_html_e('Attach screenshots where possible to help us understand your situation.', 'zinn-digital-wp-rest-api-manager'); ?></li>
			</ul>
			<?php esc_html_e("We're always happy to help and will respond as quickly as possible!", 'zinn-digital-wp-rest-api-manager'); ?>
		</div>
	</div>
	<?php
}

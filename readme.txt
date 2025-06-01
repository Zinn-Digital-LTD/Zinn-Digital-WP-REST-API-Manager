=== Zinn Digital™ WP REST API Manager ===
Contributors: zinndigital
Donate link: https://zinndigital.com/
Tags: rest api, custom fields, meta fields, acf, api
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Tested up to PHP: 8.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Manage and control REST API exposure for WordPress metadata fields. Works with ACF, Meta Box, Pods, and all custom fields.

== Description ==

**Zinn Digital™ WP REST API Manager** gives you complete control over which custom metadata fields are exposed in your WordPress REST API responses. Select exactly which meta fields from posts, comments, users, and terms should be included when accessing your content via the REST API.

This plugin is perfect for headless WordPress setups, mobile app development, or any integration that requires access to custom field data through the REST API. Works seamlessly with popular custom field plugins like Advanced Custom Fields (ACF), Meta Box, Pods, Custom Field Suite, and any plugin that stores data in WordPress meta tables.

Designed and maintained by [Zinn Digital™ LTD](https://zinndigital.com/).

= Key Features =

* **Selective Field Exposure** - Choose exactly which custom fields to include in REST API responses
* **Multi-Object Support** - Works with posts, pages, custom post types, comments, users, and taxonomies
* **Universal Meta Detection** - Automatically detects meta fields used across multiple post types
* **Third-Party Plugin Compatible** - Works with ACF, Meta Box, Pods, Toolset, and more
* **Intuitive Interface** - Organized tabbed interface for easy management
* **Bulk Operations** - Check/uncheck all fields with one click
* **Performance Optimized** - Minimal impact on REST API response times
* **Security First** - Built with WordPress security best practices
* **Developer Friendly** - Clean, well-documented code following WordPress standards

= How It Works =

1. The plugin automatically detects all custom fields (meta) in your WordPress database
2. Navigate to Settings → WP REST API Manager in your admin
3. Use the tabbed interface to select fields for Posts, Comments, Users, and Terms
4. Check the fields you want to expose in the REST API
5. Save your settings - the fields are immediately available in REST API responses

= Use Cases =

* **Headless WordPress** - Expose custom fields for your decoupled frontend
* **Mobile Apps** - Access all your custom data in mobile applications
* **Third-Party Integrations** - Share custom field data with external services
* **Content Migration** - Export complete content including custom fields
* **API Development** - Build robust APIs with full access to meta data

= Developer Features =

* Clean namespaced code (ZinnZWRAM namespace)
* Hooks and filters for extensibility
* Proper capability checks and nonce verification
* WordPress coding standards compliant
* Multisite compatible

== Installation ==

= Automatic Installation =

1. Go to **Plugins → Add New** in your WordPress admin
2. Search for "Zinn Digital WP REST API Manager"
3. Click **Install Now** and then **Activate**
4. Navigate to **Settings → WP REST API Manager** to configure

= Manual Installation =

1. Download the plugin zip file
2. Go to **Plugins → Add New** in your WordPress admin
3. Click **Upload Plugin** and choose the downloaded file
4. Click **Install Now** and then **Activate**
5. Navigate to **Settings → WP REST API Manager** to configure

= Via FTP =

1. Download and unzip the plugin
2. Upload the `zinn-digital-wp-rest-api-manager` folder to `/wp-content/plugins/`
3. Activate the plugin through the **Plugins** menu in WordPress
4. Navigate to **Settings → WP REST API Manager** to configure

== Frequently Asked Questions ==

= Which custom field plugins are supported? =

This plugin works with any plugin that stores data in WordPress meta tables, including:
- Advanced Custom Fields (ACF)
- Meta Box
- Pods
- Custom Field Suite
- Toolset Types
- Carbon Fields
- CMB2
- And many more!

= Will this work with my custom post types? =

Yes! The plugin automatically detects all registered post types (including custom ones) and their associated meta fields.

= How do I access the exposed fields via REST API? =

Once fields are enabled, they appear in the standard REST API responses. For example:
`https://yoursite.com/wp-json/wp/v2/posts/123`

The response will include your selected custom fields in the main response body.

= Can I expose fields for custom taxonomies? =

Yes! The plugin supports term meta for all taxonomies, including custom taxonomies.

= Is this secure? =

The plugin follows WordPress security best practices:
- Capability checks (manage_options) for all admin actions
- Nonce verification for all form submissions
- Proper data sanitization and escaping
- No direct database queries without preparation

= Will this slow down my REST API? =

The plugin is optimized for performance. It only adds the fields you specifically select, and uses WordPress's built-in REST API infrastructure.

= Can I use this in a multisite network? =

Yes! The plugin is fully multisite compatible. Each site in the network can have its own field configuration.

= Who developed this plugin? =

This plugin is developed and maintained by [Zinn Digital™ LTD](https://zinndigital.com/), a professional digital services company.

== Screenshots ==

1. **Post Meta Fields** - Select which post meta fields to expose in the REST API
2. **Comment Meta Fields** - Manage comment metadata exposure
3. **User Meta Fields** - Control which user meta fields are accessible via API
4. **Term Meta Fields** - Configure taxonomy term metadata visibility
5. **Tabbed Interface** - Clean, organized interface for managing all meta types
6. **Universal Meta Section** - Special section for meta keys used across multiple post types

== Changelog ==

= 1.0.0 =
* Initial release
* Support for post, comment, user, and term meta
* Tabbed interface for easy navigation
* Universal meta detection for fields used across post types
* Bulk check/uncheck functionality
* Full compatibility with WordPress 6.8 and PHP 8.4
* Zinn Digital branding and support integration

== Upgrade Notice ==

= 1.0.0 =
Initial release. No upgrade notices yet.

== Support ==

Need help? We're here for you!

📧 **Email**: [office@zinndigital.com](mailto:office@zinndigital.com)
🌐 **Website**: [zinndigital.com](https://zinndigital.com/)
🛒 **Marketplace**: [zinnhub.com](https://zinnhub.com/)

When contacting support, please include:
- Detailed description of the issue
- WordPress version
- PHP version
- List of active plugins
- Screenshots if applicable

== Privacy Policy ==

This plugin does not collect, store, or transmit any personal data. It only manages the visibility of existing metadata in your WordPress REST API responses. All data remains on your server.

== Credits ==

Developed by [Zinn Digital™ LTD](https://zinndigital.com/)

Special thanks to the WordPress community for continuous support and feedback.

=== Zinn Digital™ WP Rest API Manager ===
Contributors: zinndigital
Donate link: https://zinndigital.com/
Tags: rest api, admin, post types, taxonomies, custom fields, meta, api manager, acf, pods, meta box
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Tested up to PHP: 8.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Description ==

**Zinn WP REST API Manager** gives you total control over what your WordPress site exposes in the REST API—from post types to taxonomies to custom fields (meta). Toggle REST API exposure for all your data, including fields created by any third-party plugin (like ACF, Meta Box, Pods, and more), with a single click from a modern admin panel.

Built for agencies, developers, and site owners who want secure, modular, no-code REST API control. Designed and maintained by [Zinn Digital ™ LTD](https://zinndigital.com/).

= Features =
* Instantly view all core & custom post types and taxonomies, and see which are exposed in the REST API.
* Toggle REST API visibility for post types and taxonomies without code.
* Detect all custom fields (meta)—even those created by third-party plugins like ACF, Meta Box, Pods, etc.
* Enable or disable REST API exposure for custom fields per post type.
* Beautiful dark admin interface in Zinn branding (black & gold).
* Sectioned UI: Core, Custom, and Meta fields all organized for clarity.
* Notices and tooltips guide you through best practices.
* Security best practices: nonces, permissions, sanitization, and escaping.
* Persistent settings: your toggles are remembered on plugin updates.
* Full support: friendly Zinn Digital team available via email.
* Tested with all major custom field plugins and WordPress versions up to 6.8.1 and PHP 8.4+.

== Installation ==

1. Upload the `zinn-wp-rest-api-manager` folder to the `/wp-content/plugins/` directory, or install directly from Plugins > Add New.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Open 'API Manager' in the WordPress admin menu to begin managing REST API exposure for your site.
4. Toggle visibility for post types, taxonomies, or meta fields with a single click.

== Frequently Asked Questions ==

= Q: Why don’t I see custom fields (meta) for a post type? =
A: Custom fields will only be detected if at least **one post** exists using those fields. Publish a post with the custom field filled in, then refresh the API Manager screen.

= Q: Will this work with fields created by ACF, Meta Box, Pods, or other plugins? =
A: Yes! As long as the field data is saved to WordPress’s standard post meta system, you can toggle REST API visibility for it.

= Q: Is this plugin secure? =
A: Absolutely! All admin actions require the proper permissions, nonces, and input sanitization.

= Q: Who developed this plugin? =
A: This plugin is developed and maintained by [Zinn Digital ™ LTD](https://zinndigital.com/).

== Screenshots ==

1. **Core Post Types Management** – View and toggle REST API exposure for WordPress core post types like Posts, Pages, Media, etc.
2. **Custom Post Types Control** – Manage REST API visibility for custom post types created by themes and plugins  
3. **Custom Fields (Meta) & Support** – Enable REST API for custom meta fields and access built-in support section

== Changelog ==

= 1.0.0 =
* Initial release.  
* Toggle REST API visibility for post types, taxonomies, and meta fields.
* Automatic detection of fields created by other plugins.
* Modern admin UI with Zinn branding.
* Support/help section built in.

== Upgrade Notice ==

= 1.0.0 =
First public release. Always backup before upgrading!

== License ==

This program is free software; you can redistribute it and/or modify  
it under the terms of the GNU General Public License as published by  
the Free Software Foundation; either version 2 of the License, or  
(at your option) any later version.

This program is distributed in the hope that it will be useful,  
but WITHOUT ANY WARRANTY; without even the implied warranty of  
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the  
GNU General Public License for more details.

You should have received a copy of the GNU General Public License  
along with this program; if not, see https://www.gnu.org/licenses/gpl-2.0.html

== Support ==

If you need help, email us at [office@zinndigital.com](mailto:office@zinndigital.com) with full details and screenshots. We’re always happy to help!
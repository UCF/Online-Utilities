=== Online-Theme Utilities WP-CLI Package ===
Contributors: ucfwebcom
Tags: ucf, wp cli, wp, cli
Requires at least: 4.7.5
Tested up to: 4.7.5
Stable tag: 1.0.0
License: GPLv3 or later
License URI: http://www.gnu.org/copyleft/gpl-3.0.html

Provides utilities (jobs) to run for the online website.

== Description ==

Provides utilities (jobs) to run for the online website.


== Installation ==

= Manual Installation =
1. Upload the plugin files (unzipped) to the `/wp-content/plugins` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the "Plugins" screen in WordPress
3. Run commands via wp cli. See [WP-CLI Docs](http://wp-cli.org/commands/plugin/install/) for more command options.

= WP CLI Installation =
1. `$ wp plugin install --activate https://github.com/UCF/Online-Utilities/archive/master.zip`.  See [WP-CLI Docs](http://wp-cli.org/commands/plugin/install/) for more command options.
3. Run commands via wp cli.

== Commands ==

All commands are stored under the `online` core command. To see available options run `wp online`.

= Degree Commands =

All degree commands are stored under the `degrees` command. To see avilable options run `wp online degrees`.

Tuition and Fees: `wp online degrees tuition <api>`

Adds tuition and fee information to main site degrees.

- <api>
    - The url of the tuition feed

Imports degrees from various sources and writes them into degree custom post types.


== Changelog ==

= 1.0.0 =
* Initial release


== Upgrade Notice ==

n/a


== Installation Requirements ==

None


== Development & Contributing ==

NOTE: this plugin's readme.md file is automatically generated.  Please only make modifications to the readme.txt file, and make sure the `gulp readme` command has been run before committing readme changes.

=== UCF Online Utilities Plugin ===
Contributors: ucfwebcom
Tags: ucf, online
Requires at least: 4.9.8
Tested up to: 4.9.8
Stable tag: 2.0.3
Requires PHP: 5.4
License: GPLv3 or later
License URI: http://www.gnu.org/copyleft/gpl-3.0.html

Provides functionality and other utilities for the UCF Online website.

== Description ==

Provides functionality and other utilities for the UCF Online website.


== Documentation ==

Head over to the [Online Utilities wiki](https://github.com/UCF/Online-Utilities/wiki) for detailed information about this plugin, installation instructions, and more.


== Changelog ==

= 2.0.3 =
Documentation:
* Updated contributing doc to reflect the switch from slack to teams.

= 2.0.2 =
Bug Fixes:
* Corrected PHP warning that was being thrown when a function was put into the `array_shift` function instead of a variable.

= 2.0.1 =
* Modified markup generated for privacy policy links on forms, and where those links are injected (they're now positioned immediately after the form, instead of in the form footer.)
* Re-added form button filtering from the old Online theme, which modifies prev/next/submit buttons to use a `<button>` element instead of `<input>`. Doing this allows for more flexible styling options, necessary for some styles added in the Online Child Theme (for previous buttons.)
* Removed option in form settings for right-aligned form labels, as they're not supported by the Online Child Theme or Athena GravityForms Plugin.

= 2.0.0 =
* Added functionality for Online v3 that is not suitable for theme inclusion--primarily form data population and tuition-related logic
* Removed tuition import script in favor of using the Tuition and Fees plugin's importer

= 1.0.1 =
* Fixed missing Distance Learning fee from per credit hour totals

= 1.0.0 =
* Initial release


== Upgrade Notice ==

* v2.0.0
  * v2.0.0+ are intended to be used with the Online-Child-Theme.  Usage of the existing Online-Theme has been deprecated.


== Development ==

Note that compiled, minified css and js files are included within the repo.  Changes to these files should be tracked via git (so that users installing the plugin using traditional installation methods will have a working plugin out-of-the-box.)

[Enabling debug mode](https://codex.wordpress.org/Debugging_in_WordPress) in your `wp-config.php` file is recommended during development to help catch warnings and bugs.

= Requirements =
* node
* gulp-cli

= Instructions =
1. Clone the Online-Utilities repo into your local development environment, within your WordPress installation's `plugins/` directory: `git clone https://github.com/UCF/Online-Utilities.git`
2. `cd` into the new Online-Utilities directory, and run `npm install` to install required packages for development into `node_modules/` within the repo
3. Optional: If you'd like to enable [BrowserSync](https://browsersync.io) for local development, or make other changes to this project's default gulp configuration, copy `gulp-config.template.json`, make any desired changes, and save as `gulp-config.json`.

    To enable BrowserSync, set `sync` to `true` and assign `syncTarget` the base URL of a site on your local WordPress instance that will use this plugin, such as `http://localhost/wordpress/my-site/`.  Your `syncTarget` value will vary depending on your local host setup.

    The full list of modifiable config values can be viewed in `gulpfile.js` (see `config` variable).
3. Run `gulp default` to process front-end assets.
4. If you haven't already done so, create a new WordPress site on your development environment to test this plugin against, and [install and activate all plugin dependencies](https://github.com/UCF/Online-Utilities/wiki/Installation#installation-requirements).
5. Activate this plugin on your development WordPress site.
6. Configure plugin settings from the WordPress Customizer.
7. Run `gulp watch` to continuously watch changes to scss and js files.  If you enabled BrowserSync in `gulp-config.json`, it will also reload your browser when plugin files change.

= Other Notes =
* This plugin's README.md file is automatically generated. Please only make modifications to the README.txt file, and make sure the `gulp readme` command has been run before committing README changes.  See the [contributing guidelines](https://github.com/UCF/Online-Utilities/blob/master/CONTRIBUTING.md) for more information.


== Contributing ==

Want to submit a bug report or feature request?  Check out our [contributing guidelines](https://github.com/UCF/Online-Utilities/blob/master/CONTRIBUTING.md) for more information.  We'd love to hear from you!

=== GitHub Plugin Updater ===
Contributors: codepress,davidmosterd,tschutter
Tags: github,update,plugin,repository
Requires at least: 3.4
Tested up to: 3.5
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Update plugins that are hosted on GitHub.

== Description ==

Updates plugins that are hosted on GitHub. This is a beta release, so you might not use it production environments.

Example usage:

`
function my_github_plugin_updater() {

	if ( ! function_exists( 'github_plugin_updater_register' ) )
		return false;

	github_plugin_updater_register( array(
		'owner'	=> 'codepress',
		'repo'	=> 'github-plugin-updater',
		'slug'	=> 'github-plugin-updater/github-plugin-updater.php', // defaults to the repo value ('repo/repo.php')
	) );
}
add_action( 'plugins_loaded', 'my_github_plugin_updater' );
`

Currently we are working on the following features:

* Add a GUI to add plugins by their GitHub url
* Add a debug mode which forces to reset the plugin transients
* Add a description to your plugin for users to read before updating

== Installation ==

1. Upload github-plugin-updater to the /wp-content/plugins/ directory
2. Activate GitHub Plugin Updater through the 'Plugins' menu in WordPress
3. Configure the plugins you want to update via GitHub

== Frequently Asked Questions ==

= I have an idea for a great way to improve this plugin =

Great! We'd love to hear from you. The plugin is hosted on [GitHub](https://github.com/codepress/github-plugin-updater) for issues or pull requests.

= Why not get the zipball directly from GitHub? =

The zipball from GitHub might is not likely packaged similar to the the slug of your plugin. It's stored as a
temporary file, opened by the ZipArchive class and modified to match the plugin slug path before being send to WordPress.
This seems closest to the process of WordPress updating a plugin. If you know a neater way, let us know.

= Can I integrate this plugin with my own plugin? =

You can, should take little work. However, it may not be wise. We feel that separating the updater from the plugin itself is
safest. Any bug found in this plugin might affect your plugin and in the worst case your plugin won't be able to update.
Separating the two will allow us to fix any bugs in this plugin and keep the update process working just fine.

== Changelog ==

= 1.0 =

* Initial release.
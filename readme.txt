=== Github Plugin Updater ===
Contributors: codepress, tschutter, davidmosterd
Tags: github, update, plugin
Requires at least: 3.4
Tested up to: 3.5
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Updates plugins that are hosted on GitHub.

== Description ==

Updates plugins that are hosted on GitHub. This is a beta release, so be careful production environments.

Example usage:

`
if ( is_admin() ) {

	new GitHub_Plugin_Updater( array(
		'user'		  => 'codepress',
		'repository'  => 'codepress-test',
		'slug'		  => 'codepress-test/codepress-test.php',
		'http_args'	  => array(
			'sslverify' => true,
		)
	) );

}
`

== Installation ==

1. Upload github-plugin-updater to the /wp-content/plugins/ directory
2. Activate GitHub Plugin Updater through the 'Plugins' menu in WordPress
3. Configure the plugins you want to update via GitHub

== Frequently Asked Questions ==

= I have an idea for a great way to improve this plugin =

Great! We'd love to hear from you.

= Why not get the zipball directly from GitHub? =

The zipball from GitHub might is not likely packaged similar to the the slug of your plugin. It's is there stored as a
tempory file, opened by the ZipArchive class and modified to match the plugin slug path before being send to WordPress.
This seems closest to the process of WordPress updating a plugin. If you know a neater way, let us know.

== Changelog ==

= 1.0 =

* Initial release.
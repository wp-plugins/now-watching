=== Now Watching ===
Contributors: ZackAjmal
Tags: movies, widget, amazon
Requires at least: 2.7.0
Tested up to: 3.0.1
Stable tag: 1.1

Allows you to display the movies you're watching, have watched recently and plan to watch, with cover art fetched automatically from Amazon.

== Description ==

Now Watching is a fork of [Ben Gunnink's Now Reading Reloaded plugin](http://wordpress.org/extend/plugins/now-reading-reloaded/ "Now Reading Reloaded Plugin") modified to work with movies instead of  books.  It is forked from Ben's code as of 5.1.1.0.

With it, you can manage a library of your current movies, as well as historical and planned movies.

== Installation ==

1. Upload `now-watching` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Make any changes the to provided template files and store them in your theme directory (see the "Template Files" section)

== Frequently Asked Questions ==

= Why does my library page look funny? =

Now Watching comes with premade templates (`/templates/`) that were originally made for the default Kubrick theme that comes with Wordpress.  If your theme has more or less markup, the templates may look strange in your theme.

My suggestion to those who are having trouble is to open up the respect Now Watching template (such as `library.php`) side-by-side with one of your standard theme templates, and make sure that the markup matches.

= Can I see some blog where this plugin is running? =

Yes, it is running on [my blog](http://www.zackvision.com/movies/ "Procrastination - Movies").

== Screenshots ==

1. Adding a movie
2. Library view
3. Editing a movie
4. Now Watching Options

== Changelog ==

= 1.1 =
* Made it compatible with WordPress 3.0 so that movie info can be edited now.
* Fixed page titles when using permalinks.
* Allowed UTF-8 characters in widget title.

= 1.0 =
* First release forked from Now Reading Reloaded 5.1.1.0

== Template Files ==

The `templates` folder of the Now Watching plugin contains a default set of templates for displaying your movie data in various places (sidebar, library, etc.).  *Any changes you make to these templates should be in your own theme folder*.  Now Watching will first look inside your active theme folder for a directory called `now-watching` for template files;  if it doesn't find them, it will use its own.  Customized template should be stored in `/wp-content/yourtheme/now-watching/` so that your changes are not overwritten when you upgrade.
=== Plugin Name ===
Contributors: oltdev, enej
Donate link: http://example.com/
Tags: browse, history, breadcrumbs, story, widget, shortcode
Requires at least: 2.8
Tested up to: 2.9
Stable tag: trunk

Tracks the history the users visit and displays it to them, like the browser history but only for your website

== Description ==

Ever wanted your users to see all the pages that they have previously visited. Well now you can!

Here's how it works:

The value of the current url and an appropriate title of the page are stored in the users cookie. 
As the user browses the site more the number of links stored grows.

[shortcode](http://codex.wordpress.org/Shortcode_API "WordPress shortcode")


You can place the '[history-tracker size ="5" order="oldest"]' shorcode into any page or post or 
If order attribute is set to olderst the order will be reversed. 
There is no description or title attribute. 


== Installation ==

The usual vanilla installation:  

1. Upload `history-tracker` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Place `History Tracker` widget in your sidebar
1. or use the '[history-tracker]' [shortcode](http://codex.wordpress.org/Shortcode_API "WordPress shortcode") where you want it


== Screenshots ==

1. This is how the widget looks like showing you all the options.

2. This is how the widget might look like on your site. You will probably need to style it a bit to make it look as cool.


== Changelog ==

= 1.0 =
* Initial Release 


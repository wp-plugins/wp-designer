=== WP Designer ===
Contributors: varun21, aniash_29, ruchika_wp
Tags: designer, customizer, developer, wp customizer, wp customization, wordpress designer, wordpress customizations
Requires at least: 3.6
Tested up to: 4.1.1
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WP Designer helps you to customize your Wordpress site and retain your customizations regardless of the theme you use.

== Description ==

WP Designer allows you to add extra functionality to your site in a standard compliant way using customization best-practices. It allows you to keep the site functionality outside the theme so that it is not dependent on the theme. This comes in handy in various scenarios like when you want to create Custom Post Types, Custom Taxonomies etc. This also allows you to have one single place where you keep all your edits.

WP Designer also gives you a development friendly environment. It provides you options to conveniently disable your customizations when you want to troubleshoot.

Place all your php functions in functions.php and extra styles in style.css.

= WP Designer allows you to: =

1. Add extra functionality to any theme without hassles.
1. Extend the capabilities of existing theme.
1. Keep your customizations (php code snippets and css styles) outside the theme.
1. Theme independence allows to extend WordPress without creating a massive and painful update to an existing theme.
   
Also read:
1. http://justintadlock.com/archives/2013/09/14/why-custom-post-types-belong-in-plugins
1. http://justintadlock.com/archives/2011/02/02/creating-a-custom-functions-plugin-for-end-users

== Installation ==

Log in to your WordPress dashboard, navigate to the Plugins menu and click Add New. In the search field type **WP Designer** and click *Search Plugins*. Once you’ve found the plugin you can install it by simply clicking “Install Now”.

Or you can follow the steps given below:

1. Upload the entire `wp-designer` folder to the `/wp-content/plugins/` directory.
1. DO NOT change the name of the `wp-designer` folder.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Once activated, visit the **Settings** > **WP Designer** for usage instructions.

== Screenshots ==

1. WP Designer: Debug Tools
1. WP Designer: Directory Structure and Files

== Frequently Asked Questions ==

= How do I start my customizations? =

On activation, WP Designer automatically creates a wp-designer folder in the uploads directory which includes all the necessary files and folders required for designing the site. You can start by editing the functions.php for adding any custom functionality. If you need CSS customizations you can edit the style.css in the same folder.

= How do I disable my customizations? =

There are three ways to do this.

1. Go to Setting > WP Designer and check the option to disable functions.php and style.css.
1. Comment out your code.
1. Disable the WP Designer plugin itself.

= Some of the customizations in plugin's style.css are not working. =

If you have made any specific customizations using Wordpress in-built Customizer or your child theme, they may not work due to CSS priority or specificity. For instance, if you have set the `background-color` for the site using the Wordpress customizer, the plugin's style.css may not be able to override the same CSS rule. 

== Changelog ==

= 1.0 =
This is the initial release of the plugin.
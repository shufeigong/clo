=== Auto Hide Admin Bar ===
Contributors: mbootsman
Donate link: https://www.nostromo.nl/wordpress-plugins/auto-hide-admin-bar
Tags: adminbar, admin bar, autohide, auto, hide, toolbar
Requires at least: 3.1
Tested up to: 4.3
Stable tag: 0.9.1

This plugin adds an auto-hide feature to the WordPress Admin Bar or Toolbar.

== Description ==

Auto Hide Admin Bar makes the WordPress Toolbar disappear - and reappear when hovering the mouse pointer over the top of the browwer window.
You end up with a clean view of your site, and keep having access to the WordPress Toolbar.
If you have any comments or questions, please use the support forum: http://wordpress.org/support/plugin/auto-hide-admin-bar

== Installation ==

1. Upload `auto-hide-admin-bar` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. We're all done, now you have an auto hiding Toolbar.

== Frequently Asked Questions ==

== Screenshots ==
How do we make screenshots of things that are hidden? :)

== Changelog ==

= 0.9.1 =
* Code will not execute when in WordPress customizer view, to prevent top of page cut-off

= 0.9 =
* Changed description
* Moved settings page to settings menu.
* Minor code cleaning/reorganization
* Fixed some typo's in strings.
* Fixed the hidden div (the one that triggers te re-appearance of the Toolbar) to not be added on window resize. We only need one...

= 0.8.2 =
* Changed loading of scripts to wp_footer
* Changed wrapping of jQuery anonymous function to use a document ready function to prevent compatibility issues

= 0.8.1 =
* Replaced get_current_theme() with wp_get_theme(). Thanks to ElectricFeet via Support Forum; http://wordpress.org/support/topic/wp_debug-gives-message-that-get_current_theme-is-deprecated

= 0.8 =
* Some CSS changes due to larger Toolbar in WordPress 3.8
* Added option for hiding/showing the Toolbar on small screens
* Added support for Twenty Fourteen. Need to think of a solid way to support themes with fixed headers/navigation. Tips are welcome.

= 0.7 =
* Removed external jQuery library.
* Added options page.
* Added options for animation speed, delay and mouse polling interval.

= 0.6.3 =
* Changed background-position to background-position-y, because of IE8 problem (of course...). Thanks to per (feja@home.se) for submitting the bug, and the jQuery bugtracker for the hint: http://bugs.jquery.com/ticket/11838

= 0.6.2 =
* Added style adjustment for body background.

= 0.6.1 =
* Switched wp_enqueue_script sequence for jquery and jquery.hoverintent due to problems.

= 0.6 =
* By request, added delay for showing/hiding the Admin Bar. A settings page will be included in the future.

= 0.5 =
* Changed position of hidden div to fixed, so the admin bar is showed also when you have scrolled down on your site.

= 0.4 =
* Changed jQuery enqueue manner. Now using the wp_print_scripts hook. Thanks to Ralph from zonebattler.net for mentioning this bug.

= 0.3 =
* Added - Only activate the plugin when a user is logged in.

= 0.2 =
* Added  - wp_enqueue_script for jQuery

= 0.1 =
* Initial Release
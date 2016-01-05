=== Store Locator Plus : Pro Pack ===
Plugin Name:  Store Locator Plus : Pro Pack
Contributors: charlestonsw
Donate link: http://www.storelocatorplus.com/
Tags: store locator plus, pro pack
Requires at least: 3.4
Tested up to: 4.0
Stable tag: 4.2.02

A premium add-on for the Store Locator Plus location management system for WordPress.

== Description ==

This plugin will help site admins manage large listings of locations with tools for processing location data en mass.

== Installation ==

= Requirements =

* Store Locator Plus: 4.2+
* WordPress: 3.5.1+
* PHP: 5.2.4+

= Install After SLP =

1. Go fetch and install the latest version of Store Locator Plus.
2. Purchase this plugin from the [Store Locator Plus website](http://www.storelocatorplus.com) to get the latest .zip file.
3. Go to plugins/add new.
4. Select upload.
5. Upload the zip file.

== Frequently Asked Questions ==

= What are the terms of the license? =

The license is GPL.  You get the code, feel free to modify it as you wish.
Hopefully you like it enough to want to support the effort to bring useful software to market.
Learn more on the [CSA License Terms](http://www.storelocatorplus.com/products/general-eula/) page.

== Changelog ==

Visit the [Online Change Log for details](http://www.storelocatorplus.com/tag/slp4-changelog/).

= 4.2.02 (Nov-2014) =

* Enhancement: Add Swedish (sv_SE) translation by [Piani Sweden AB](http://piani.se).

= 4.2.01 (Oct-2014) =

* Fix: Make the report queries PHP 5.5+ compatible by passing them through wpdb prepare statements.
* Enhancement: Add a remote CSV file fetching option to grab CSV files from remote http/https servers.
* Enhancement: Set PHP execution time to 10 minutes during location exports if the server allows for it.

= 4.2.00 (Aug-2014) =

* Note: Requires SLP 4.2.04
* Change: Pro Pack / Tools has been removed.
* Change: Pro Pack / Look and Feel / Custom CSS has been moved to User Experience / View.
* Change: Pro Pack / Reporting / Enable Reporting has been moved to Reporting.
* Change: Location CSV Import defaults to update on duplicates and first line has field name enabled.
* Enhancement: Update to use SLP 4.2 add-on pack system, decreasing the memory footprint and PHP processing load.
* Enhancement: tags_for_dropdown is now an alias for shortcode attribute tags_for_pulldown.
* Enhancement: Convert reporting interface to standard SLP object type for improved future report improvements.
* Fix; Highlight Uncoded on Manage Locations on/off setting under General Settings (requires SLP 4.2.06+).

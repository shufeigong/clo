=== Store Locator Plus : Janitor ===
Plugin Name:  Store Locator Plus : Janitor
Contributors: charlestonsw
Donate link: http://www.storelocatorplus.com/product/store-locator-plus-janitor/
Tags: search form, google maps, cleaning, janitor, database, locator
Requires at least: 3.8
Tested up to: 4.4
Stable tag: 4.3.03

A free add-on to assist with extra maintenance during major upgrades or porting to new systems for the Store Locator Plus plugin.

== Description ==

A free add-on pack for the [Store Locator Plus](http://www.storelocatorplus.com/) location mapping system plugin.
This add-on assists in cleaning up the Store Locator Plus settings including clearing out all pre-existing settings that may 
interfere with upgrading or installing on a new server after restoring a WordPress backup.   Allows Store Locator Plus add-ons 
to behave as if the plugin activation is happening on a new install.

= Options Clean Up =

Delete all [Store Locator Plus](http://www.storelocatorplus.com/) settings from the WordPress options table while retaining location data.

Current add-on packs are also supported:

* [Premier](http://www.storelocatorplus.com/product/premier-subscription/)

* [Contact Extender](http://www.storelocatorplus.com/product/slp4-contact-extender/)
* [Directory Builder] (http://www.storelocatorplus.com/product/directory-builder/)
* [Enhanced Map](http://www.storelocatorplus.com/product/slp4-enhanced-map/)
* [Enhanced Results](http://www.storelocatorplus.com/product/slp4-enhanced-results/)
* [Enhanced Search](http://www.storelocatorplus.com/product/slp4-enhanced-search/)
* [Event Location Manager](http://www.storelocatorplus.com/product/event-location-manager/)
* [Multi Map](http://www.storelocatorplus.com/product/multi-map/)
* [Pro Pack](http://www.storelocatorplus.com/product/slp4-pro/)
* [Social Media Extender](http://www.storelocatorplus.com/product/slp4-social-media-extender/)
* [Tagalong](http://www.storelocatorplus.com/product/slp4-tagalong/)
* [User Managed Locations](http://www.storelocatorplus.com/product/slp4-user-managed-locations/)
* [Widget Pack](http://www.storelocatorplus.com/product/slp4-widgets/)


= Description Decoding =

Prior versions of Store Locator Plus may have encoded HTML while storing the data in the location descriptions.
A one-time-use tool will repair the location descriptions, converting the HTML encoded data to standard HTML
notation.   If you are seeing text such as &lt;br/&gt; instead of <br/> in your description data, you need this tool.

= Related Links =

* [Store Locator Plus](http://www.storelocatorplus.com/)
* [Other CSA Plugins](http://profiles.wordpress.org/charlestonsw/)

== Installation ==

= Requirements =

* Store Locator Plus: 4.3.13
* Wordpress: 3.8
* PHP: 5.2

= Install After SLP =

1. Go fetch and install Store Locator Plus version 4.0 or higher.
2. Purchase this plugin from CSA to get the latest .zip file.
3. Go to plugins/add new.
4. Select upload.
5. Upload the zip file.

== Frequently Asked Questions ==

= What are the terms of the license? =

The license is GPL.  You get the code, feel free to modify it as you
wish.  I prefer customers pay me because they like what I do and
want to support my efforts to bring useful software to market.  Learn more
on the [SLP License Terms](http://www.storelocatorplus.com/products/general-eula/).

== Changelog ==

= 4.3.03 ( 2015-Nov-27 ) =

* Change: More efficient code-loading sequence with Store Locator Plus.

= 4.3.02 =

* Verified to work with WP 4.3.1.
* Update to allow Janitor to run regardless of location of SLP install directory.

= 4.3.01 =

* Change: Requires SLP 4.3.08
* Enhancement: Better handling for PHP execution time limits.

= 4.3.00 =

* Enhancement: Added [Premier plugin](http://www.storelocatorplus.com/product/premier-subscription/) settings.
* Enhancement: Add hooks and filters to allow add-on packs to mark some settings as important so they can be saved/restored during reset settings.
* Change: requires SLP 4.3.00
* Change: Remove some legacy SLP settings.
* Tested with WordPress 4.3.00

= 4.2.01 =

* Enhancement: Automatically lists any SLP 4.2 plugins that are currently active with the settings management interface.
* Enhancement: Added MultiMap settings to the permanent settings list.

= 4.2.00 =

* Change: Requires SLP 4.2.63.
* Enhancement: Updated to use lighter memory footprint via SLP 4.2 addon framework.

= 4.1.20 =

* Fix: delete option on multisite.

= 4.1.19 =

* Update to work with SLP 4.2.59

= 4.1.18 =

* Mark some upcoming defunct ER settings.

= 4.2.17 =

* Added [Social Media Extender](http://www.storelocatorplus.com/product/slp4-social-media-extender/) settings.
* Mark Defunct settings in [SLPER](http://www.storelocatorplus.com/product/slp4-enhanced-results/).

= 4.2.16 =

* [SLP 4.2.48](http://www.storelocatorplus.com/product/store-locator-plus/) compatible with delete icon update.

=== Store Locator Plus : Tagalong ===
Plugin Name:  Store Locator Plus : Tagalong
Contributors: charlestonsw
Donate link: http://www.storelocatorplus.com/product/slp4-tagalong/
Tags: search form, google maps
Requires at least: 3.4
Tested up to: 4.0
Stable tag: 4.2.01

A premium add-on pack for Store Locator Plus that adds location categories as an advanced form of tagging.

== Description ==

Categorize locations, changing map markers and other information on a category basis.

This plugin requires the free Store Locator Plus base plugin.

= Related Links =

* [Tagalong Codex](http://www.storelocatorplus.com/codedoc/slp-tagalong/)
* [Store Locator Plus](http://www.storelocatorplus.com/)
* [Other CSA Plugins](http://profiles.wordpress.org/charlestonsw/)

== Installation ==

= Requirements =

* Store Locator Plus: 4.2.06+
* Wordpress: 3.7.0+
* PHP: 5.1+

= Install After SLP =

1. Go fetch and install the latest version of [Store Locator Plus](http://www.storelocatorplus.com/).
2. Purchase this plugin from CSA to get the latest .zip file.
3. Go to plugins/add new.
4. Select upload.
5. Upload the slp-tagalong.zip file.

== Frequently Asked Questions ==

= What are the terms of the license? =

The license is GPL.  You get the code, feel free to modify it as you
wish. I prefer that customers pay because they like what I do and
want to support the effort that brings useful software to market.  Learn more
on the [License Terms page](http://www.storelocatorplus.com/products/general-eula/).

= How can I translate the plugin into my language? =

* The easiest way to start the process it by installing the [CodeStyling Localization](http://wordpress.org/extend/plugins/codestyling-localization/) plugin.
* If everything is ok, email the files to info@charlestonsw.com and I will add them to the next release.
* For more information on POT files, domains, gettext and i18n have a look at the I18n for WordPress developers Codex page and more specifically at the section about themes and plugins.


This plugin has settings for many of the user-interface labels.
I am looking into working with the WPML plugin for added support for those of you that are not in North America.

== Changelog ==

Visit the [SLP Website for details](http://www.storelocatorplus.com/tag/slp4-changelog/).

= October 2014 (4.2.01+) =

* Note: requires SLP 4.2.06+
* Note: requires WP 3.7.0+
* Enhancement: Update Dutch (nl_NL) translation by [Jan de Baat](http://www.de-baat.nl/).
* Enhancement: Updated to use SLP 4.2 add on framework.
* Enhancement: Added a per-category URL setting, if set it will link the icon to the specified URL.
* Enhancement: Added a URL target for the per-category URL setting, used to determine the target window to open (_blank, etc.)
* Enhancement: Added a per-category rank (1,2,3...) to help with sorting out the map marker precedence.
* Enhancement: New fields, category_url, url_target, and rank have been added to the category import tool.
* Enhancement: Tweak the generic WordPress category add/edit/update messages.
* Enhancement: Add Icon and Map Marker to category manager page.
* Enhancement: Add a series of square colored icons to the icon picker.  Yellow, blue, black, green, orange, purple, red, and white 32px shadowed boxes.
* Enhancement: Rank attribute on store categories determines which map marker is to be displayed.  Lower numbers are higher priority.
* Fix: icon display fixed via SLP 4.2.06 update
* Fix (4.2.01) : Export Locations with CSV patched the Pro Pack export hooks.

= May 27th 2014 (4.1.06) =

* Fix: Tagalong categories interface icon formatting on admin.
* Fix: Quick Edit of categories no longer loses the map marker or icon.
* Fix: argument 2 should be an array warning.
* Fix: category export into proper column with Pro Pack exports.

= 4.1.03 =

* Enhancement: add category list to JSON output on search UI
* Enhancement: only_with_category now allows a comma-separated list of category slugs to show things in category A OR B OR C
* Change: relocate debugMP output on category icon array

= 4.1 =

* Enhancement: Add WPML support for the category select and any category labels.
* Enhancement: Locations Manager category display now links to the edit category page.
* Enhancement: A faster icon/legend/text rendering on the category icon output on UI and admin page.
* Enhancement: Update Dutch (nl_NL) files.
* Fix: Patch to update the categories properly on import.
* Fix: Order By Category Count is given the highest precedence of the output sort orders (this will impact featured listings as well, which will come later).
* Fix: Tagalong was stopping other add-on pack data queries "in their tracks" causing incomplete results.  This has been patched.
* Change: Categories on UI are now alphabetical versus date entered.

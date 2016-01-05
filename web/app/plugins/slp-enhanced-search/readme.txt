=== Store Locator Plus : Enhanced Search ===
Plugin Name:  Store Locator Plus : Enhanced Search
Contributors: charlestonsw
Donate link: http://www.charlestonsw.com/product/slp4-enhanced-search/
Tags: search form, google maps
Requires at least: 3.3
Tested up to: 4.0
Stable tag: 4.2.04

A premium add-on pack for Store Locator Plus that adds advanced search form modifications.

== Description ==

Get more control and customization of the Store Locator Plus search form.

= Features =

* Hide The Search Form

= Related Links =

* [Store Locator Plus](http://www.charlestonsw.com/product/store-locator-plus/)
* [Pro Pack](http://www.charlestonsw.com/product/store-locator-plus)
* [Other CSA Plugins](http://profiles.wordpress.org/charlestonsw/)

== Installation ==

= Requirements =

* Store Locator Plus: 4.2
* WordPress: 3.3.2+
* PHP: 5.2.4+

== Frequently Asked Questions ==

= How do I report a bug? =

Post in the [support forum](http://www.storelocatorplus.com/forums/).
You can also [contact me](http://www.storelocatorplus.com/product/product-support) to request premium support if you need immediate assistance.

= What are the premium add-ons? =

In response to feature requests from customers, I created a series of premium add-on packages for Store Locator Plus.
These features extend the functionality of the plugin beyond the basic service and features and can be purchased ala-carte.
This gives those customers who want more out of the plugin the extra features they desire while keeping the main plugin as
efficient as possible for everyone else.   It also provides a great way to support future development while getting a
"little something extra" when contributing to my efforts.

All plugins are true add-on packs.  They are non-destructive OPTIONAL additions to the base plugin.
They do not require the base plugin to be uninstalled or re-installed.
Installed an add-on pack will not change location data or modify base plugin settings.

= Who is Charleston Software Associates? =

Currently it is one guy hacking code in a home office.
I ONLY do WordPress plugins for a living.
Lately it has been almost exclusively work on Store Locator Plus due to the popularity and requests for added features that come in every month.

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

Visit the [Website for details](http://www.storelocatorplus.com/).

= 4.2.04 (Nov-2014) =

* Enhancement: Add Swedish (sv_SE) translation by [Piani Sweden AB](http://piani.se).

= 4.2.03 =

* Fix: Save General Settings tab.

= 4.2.02 =

* Note: Requires SLP 4.2+
* Enhancement: Add a city_selector="discrete|hidden|input" [shortcode attribute](http://www.charlestonsw.com/support/documentation/store-locator-plus/shortcodes/) for SLPLUS.
* Enhancement: Add a country_selector="discrete|hidden|input" [shortcode attribute](http://www.charlestonsw.com/support/documentation/store-locator-plus/shortcodes/) for SLPLUS.
* Enhancement: Add city="city value"  [shortcode attribute](http://www.charlestonsw.com/support/documentation/store-locator-plus/shortcodes/) for SLPLUS.
* Enhancement: Add country="country value"  [shortcode attribute](http://www.charlestonsw.com/support/documentation/store-locator-plus/shortcodes/) for SLPLUS.
* Enhancement: Add state="state value"  [shortcode attribute](http://www.charlestonsw.com/support/documentation/store-locator-plus/shortcodes/) for SLPLUS.
* Enhancement: Hide Address Input on search form is back.  It is both a global setting and a [shortcode attribute][shortcode attribute](http://www.charlestonsw.com/support/documentation/store-locator-plus/shortcodes/) for SLPLUS.
* Enhancement: Use the SLP 4.2 add-on framework to improve performance and consistency.
* Fix: Passing an address by URL will automatically force the map into immediate results mode.
* Fix: Passing an address by URL will automatically turn OFF the location sensor.
* Fix: Global "Hide Search Form" now hides search form output including add-on pack output.
* Fix(.01): Address the "non-object" issue when activating the plugin for the first time.
* Fix(.02): Patch a problem with discrete city, state, country search in 4.2.X prerelease.
* Fix(.02): Patch a problem with the radius selector behavior (only use radius when blank).
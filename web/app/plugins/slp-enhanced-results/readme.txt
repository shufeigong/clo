=== Store Locator Plus : Enhanced Results ===
Plugin Name:  Store Locator Plus : Enhanced Results
Contributors: charlestonsw
Donate link: http://www.storelocatorplus.com/product/slp4-enhanced-results/
Tags: search form, google maps, results
Requires at least: 3.8
Tested up to: 4.3
Stable tag: 4.3.02

A premium add-on pack for Store Locator Plus that adds enhanced search results to the plugin.

== Description ==

Get more control of the search results on Store Locator Plus with this enhanced results add-on.

= Related Links =

* [Store Locator Plus](http://www.storelocatorplus.com/product/store-locator-plus/)
* [Other CSA Plugins](http://profiles.wordpress.org/charlestonsw/)

= Special Thanks =

* [Visit On Web](http://www.visitonweb.com/) French translation.
* [DeBaat](https://www.de-baat.nl/) Dutch translation.

== Installation ==

= Requirements =

* Store Locator Plus: 4.3
* WordPress: 3.8
* PHP: 5.1

= Install After SLP =

1. Go fetch and install the latest version of Store Locator Plus.
2. Purchase this plugin from CSA to get the latest .zip file.
3. Go to plugins/add new.
4. Select upload.
5. Upload the slp-enhanced-results.zip file.

== Frequently Asked Questions ==

= What are the terms of the license? =

The license is GPL.  You get the code, feel free to modify it as you
wish.  We prefer that our customers pay us because they like what we do and
want to support our efforts to bring useful software to market.  Learn more
on our [CSA License Terms](http://www.storelocatorplus.com/products/general-eula/).

== Changelog ==

We update about once per month or more frequently as needed.

Visit the [Store Locator Plus Website for details](http://www.storelocatorplus.com/tag/slp4-changelog/).

= 4.3.02 =

* Fix: Order By settings now work on sites that have not set featured or rank on at least on location.
* Fix: Email Label with Email link

= 4.3.01  =

* Change: Requires SLP 4.3.07.
* Change: Updated for SLP 4.3.07 compatibility.
* Fix: Fix the email_link and popup_form settings for the email output  results.  They were "cross-wired".

= 4.3 =

* Change: Requires SLP 4.3
* Enhancement: Use modified 4.3 add-on framework.
* Fix: Stop forgetting that featured and rank are viable location data fields when all locations with featured/rank settings go away.

= 4.2.07 =

* Enhancement: Utilized the SLP 4.2 set_ValidOptions method from the add-on framework for consistency and lower memory footprint.
* Fix: Disable Initial Directory
* Change: Requires SLP 4.2.57

= 4.2.06 (Apr-29-2015) =

* Update: Change popup email formatting CSS location to match SLP 4.2.48.

= 4.2.05 (Apr-22-2015) =

* Enhancement: Update strings to make them WPML and i18n/l10n compatible.
* Enhancement: Update French translation.
* Enhancement: Update Dutch translation.

= 4.2.04 (Apr-2015) =

* Fix: Bug fix on foreach loop iteration on systems with PHP xDebug enabled.

= 4.2.03 (Feb-2015) =

* Enhancement: Make the location ordering processor more intelligent by eliminating duplicate order by clauses.
* Fix: Fix featured-listings first ordering.

= 4.2.02 (Jan-20-2015) =

* Fix: Fix the AJAX order by options.

= 4.2.01 (Jan-19-2015) =

* Fix: Make sure the rank and featured fields are added on ANY version of Enhanced Results.

= 4.2.0 (Jan-2015) =

* Enhancement: Update to use version 4.2 framework.
* Enhancement: Added email output selector for results: email label direct link, email address direct link, popup email form.
* Enhancement: Added an improved popup email form for email links.  Uses jQuery Dialog.  Uses a more secure AJAX handler for processing.
* Enhancement: Allow a WPML-compatible title to be set for the popup email form dialogue.
* Enhancement: Allow WPML-compatible text to be added for the from, subject, and message placeholders on the email popup form.
* Note: Email popup form uses the store ID to set the "to" address preventing email spoofing on the email form via hacker scripts.


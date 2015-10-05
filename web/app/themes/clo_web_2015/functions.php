<?php
/**
 * Author: Ole Fredrik Lie
 * URL: http://olefredrik.com
 *
 * FoundationPress functions and definitions
 *
 * Set up the theme and provides some helper functions, which are used in the
 * theme as custom template tags. Others are attached to action and filter
 * hooks in WordPress to change core functionality.
 *
 * @link https://codex.wordpress.org/Theme_Development
 * @package WordPress
 * @subpackage FoundationPress
 * @since FoundationPress 1.0.0
 */

/** Register all navigation menus */
require_once( 'library/navigation.php' );

/** Add desktop menu walker */
require_once( 'library/menu-walker.php' );

/** Enqueue scripts */
require_once( 'library/enqueue-scripts.php' );

require_once( 'library/wp-api-functions.php' );

require_once('library/custom-functions.php');

require_once ('library/shortcode.php');

//add_action('get_header', 'my_filter_head');

//function my_filter_head() {
//	remove_action('wp_head', '_admin_bar_bump_cb');
//}

//add_filter( 'show_admin_bar', '__return_false' );




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
require_once('library/navigation.php');

/** Add desktop menu walker */
require_once('library/menu-walker.php');

/** Enqueue scripts */
require_once('library/enqueue-scripts.php');

require_once('library/wp-api-functions.php');

require_once('library/custom-functions.php');

require_once('library/shortcode.php');

require_once('library/menu-custom-fields.php');

add_action('admin_menu', 'remove_default_post_type');

function remove_default_post_type()
{
    remove_menu_page('edit.php');
}

add_filter(
    'json_prepare_post',
    function ($data, $post, $context) {
//        $data['content'] = $post['post_content'];
        return $data;
    }, 10, 3
);
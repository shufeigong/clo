<?php
require_once(__DIR__ . '/vendor/autoload.php');

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
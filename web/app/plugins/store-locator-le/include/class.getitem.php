<?php

if (! class_exists('SLP_getitem')) {
	require_once( SLPLUS_PLUGINDIR . 'include/class.settings.php' );

	/**
	 * SLP_getitem.
	 *
	 * Only exists for $slplus->settings->get_item which needs to be replaced with $slplus->get_item().
	 *
	 * TODO: deprecate when (GFL,REX) convert to slplus->get_item() , better yet replace with options/options_nojs.
	 *
	 * @package   SLPlus\get_item
	 * @author    Lance Cleveland <lance@charlestonsw.com>
	 * @copyright 2015 Charleston Software Associates, LLC
	 *
	 */
	class SLP_getitem extends SLP_Settings {

		/**
		 * Get and item from the WP options table.
		 *
		 * @param            $name
		 * @param null       $default
		 * @param string     $separator
		 * @param bool|false $forceReload
		 *
		 * @return mixed|void
		 */
		function get_item( $name, $default = null, $separator = '-', $forceReload = false ) {
			global $slplus_plugin;

			return $slplus_plugin->get_item( $name, $default, $separator, $forceReload );
		}
	}
}
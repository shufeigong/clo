<?php
/**
 * Plugin Name: Store Locator Plus : Enhanced Map
 * Plugin URI: http://www.storelocatorplus.com/product/slp4-enhanced-map/
 * Description: A premium add-on pack for Store Locator Plus that adds enhanced map UI to the plugin.
 * Author: Store Locator Plus
 * Author URI: http://storelocatorplus.com/
 * Requires at least: 3.8
 * Tested up to : 4.4
 * Version: 4.3.01
 *
 * Text Domain: csa-slp-em
 * Domain Path: /languages/
 */

// Exit if access directly, dang hackers
if (!defined('ABSPATH')) {
    exit;
}

function SLPEnhancedMap_loader() {
    if (class_exists('SLPlus')) {
        require_once(SLPLUS_PLUGINDIR . 'include/base_class.addon.php');


        /**
         * The Enhanced Map Add-On Pack for Store Locator Plus.
         *
         * @package StoreLocatorPlus\EnhancedMap
         * @author Lance Cleveland <lance@charlestonsw.com>
         * @copyright 2012-2015 Charleston Software Associates, LLC
         */
        class SLPEnhancedMap extends SLP_BaseClass_Addon {

            //-------------------------------------
            // Properties
            //-------------------------------------

            /**
             * Settable options for this plugin.
             *
             * @var mixed[] $options
             */
            public $options = array(
                'bubblelayout' => '',
                'google_map_style' => '',
                'hide_bubble' => '0',
                'hide_map' => '0',
                'installed_version' => '',
                'no_autozoom' => '0',
                'no_homeicon_at_start' => '1',
                'maplayout' => '',
                'map_initial_display' => 'map',
                'show_maptoggle' => '0',
                'starting_image' => '',
            );

            //------------------------------------------------------
            // METHODS
            //------------------------------------------------------

            /**
             * Invoke the plugin.
             *
             * This ensures a singleton of this plugin.
             *
             * @static
             */
            public static function init() {
                static $instance = false;
                if (!$instance) {
                    load_plugin_textdomain('csa-slp-em', false, dirname(plugin_basename(__FILE__)) . '/languages/');
                    $instance = new SLPEnhancedMap(
                        array(
                            'version' => '4.3.01',
                            'min_slp_version' => '4.3.23',

                            'name' => __('Enhanced Map', 'csa-slp-em'),
                            'option_name' => 'csl-slplus-EM-options',
                            'file' => __FILE__,

                            'activation_class_name' => 'SLPEnhancedMap_Activation',
                            'admin_class_name' => 'SLPEnhancedMap_Admin',
                            'ajax_class_name' => 'SLPEnhancedMap_AJAX',
                            'userinterface_class_name' => 'SLPEnhancedMap_UI',
                        )
                    );
                }
                return $instance;
            }

            /**
             * Initialize the options properties from the WordPress database.
             */
            function init_options() {
                parent::init_options();
                if (empty($this->options['bubblelayout'])) {
                    $this->options['bubblelayout'] = $this->slplus->defaults['bubblelayout'];
                }
            }
        }

        SLPEnhancedMap::init();
    }
}

add_action( 'plugins_loaded' , 'SLPEnhancedMap_loader' );
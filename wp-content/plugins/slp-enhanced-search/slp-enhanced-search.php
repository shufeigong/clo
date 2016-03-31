<?php
/**
 * Plugin Name: Store Locator Plus : Enhanced Search
 * Plugin URI: http://www.charlestonsw.com/product/slp4-enhanced-search/
 * Description: A premium add-on pack for Store Locator Plus that adds enhanced search features to the plugin.
 * Version: 4.2.04
 * Author: Charleston Software Associates
 * Author URI: http://charlestonsw.com/
 * Requires at least: 3.3
 * Tested up to : 4.0
 *
 * Text Domain: csa-slp-es
 * Domain Path: /languages/
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// No SLP? Get out...
//
include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); 
if ( !function_exists('is_plugin_active') ||  !is_plugin_active( 'store-locator-le/store-locator-le.php')) {
    return;
}

// Make sure the class is only defined once.
//
if (!class_exists('SLPEnhancedSearch'   )) {
	require_once( WP_PLUGIN_DIR . '/store-locator-le/include/base_class.addon.php');

    /**
     * The Enhanced Search Add-On Pack for Store Locator Plus.
     *
     * @package StoreLocatorPlus\EnhancedSearch
     * @author Lance Cleveland <lance@charlestonsw.com>
     * @copyright 2012-2014 Charleston Software Associates, LLC
     */
    class SLPEnhancedSearch extends SLP_BaseClass_Addon {

        //-------------------------------------
        // Properties
        //-------------------------------------

        /**
         * Settable options for this plugin.
         *
         * o address_placeholder
         * o city : the city value for hidden city selector
         * o city_selector
         * o country : the country value for hidden country selector
         * o country_selector
         * o hide_address_entry
         * o hide_search_form
         * o ignore_radius -- depricated in favor of radius_behavior
         * o initial_results_returned : stub placeholder, this is stored in the base plugin options array
         * o name_placeholder
         * o label_for_city_selector
         * o label_for_country_selector
         * o label_for_state_selector
         * o search_by_name
         * o searchlayout
         * o searchnear
         * o state : the state value for hidden state selector
         * o state_selector
         *
         * TODO: Serialize these options...
         * These are only for shortcode atts, they have global settings but
         * are currently wired into the old-school non-serialized options data.
         *
         * o allow_addy_in_url
         *
         * @var mixed[] $options
         */
        public  $options                = array(
            'address_placeholder'           => ''           ,
            'allow_addy_in_url'             => '0'          ,
            'city'                          => ''           ,
            'ignore_radius'                 => '0'          , // used only to read in old value from versions < 4.1.05
            'radius_behavior'               => 'always_use' ,
            'city_selector'                 => 'hidden'     ,
            'country'                       => ''           ,
            'country_selector'              => 'hidden'     ,
            'hide_address_entry'            => '0'          ,
            'hide_search_form'              => '0'          ,
            'initial_results_returned'      => '999'        ,
            'installed_version'             => ''           ,
            'name_placeholder'              => ''           ,
            'label_for_city_selector'       => 'City'       ,
            'label_for_country_selector'    => 'Country'    ,
            'label_for_state_selector'      => 'State'      ,
            'search_by_name'                => '0'          ,
            'searchlayout'                  => ''           ,
            'searchnear'                    => 'world'      ,
            'state'                         => ''           ,
            'state_selector'                => 'hidden'     ,
            'append_to_search'              => ''           ,
        );

        //------------------------------------------------------
        // METHODS
        //------------------------------------------------------

        /**
         * Invoke the Enhanced Search plugin.
         *
         * @static
         * @return SLPEnhancedSearch
         */
        public static function init() {
            static $instance = false;
            if ( !$instance ) {
                load_plugin_textdomain( 'csa-slp-es', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
                $instance = new SLPEnhancedSearch(
	                array(
		                'version'               => '4.2.04',
		                'min_slp_version'       => '4.2.07',
		                'name'                  => __( 'Enhanced Search', 'csa-slp-es' )     ,
		                'option_name'           => 'csl-slplus-ES-options'                   ,
		                'slug'                  => plugin_basename( __FILE__ )               ,
		                'metadata'              => get_plugin_data( __FILE__, false, false ) ,
		                'url'                   => plugins_url( '', __FILE__ )               ,
		                'dir'                   => plugin_dir_path( __FILE__ )               ,
		                'activation_class_name'     => 'SLPES_Activation'                    ,
		                'admin_class_name'          => 'SLPEnhancedSearch_Admin'             ,
		                'ajax_class_name'           => 'SLPEnhancedSearch_AJAX'              ,
		                'userinterface_class_name'  => 'SLPEnhancedSearch_UI'                ,
	                )

                );
            }
            return $instance;
        }

        /**
         * Create a Map Settings Debug My Plugin panel.
         *
         * @return null
         */
        static function create_DMPPanels() {
            if (!isset($GLOBALS['DebugMyPlugin'])) { return; }
            if (class_exists('DMPPanelSLPES') == false) {
                require_once(plugin_dir_path(__FILE__).'class.dmppanels.php');
            }
            $GLOBALS['DebugMyPlugin']->panels['slp.es'] = new DMPPanelSLPES();
        }

        /**
         * Simplify the plugin debugMP interface.
         *
         * @param string $type
         * @param string $hdr
         * @param string $msg
         */
        function debugMP($type,$hdr,$msg='') {
            $this->slplus->debugMP('slp.es',$type,$hdr,$msg,NULL,NULL,true);
        }

        /**
         * Initialize the options properties from the WordPress database.
         *
         * @param boolean $force
         */
        function init_options($force = false) {
	        parent::init_options();
            if ( empty( $this->options['searchlayout'] ) ) {
                $this->options['searchlayout'] =  $this->slplus->defaults['searchlayout']; 
            }
        }

    }

    // Hook to invoke the plugin.
    //
    add_action('init'           ,array('SLPEnhancedSearch','init'               ));
    add_action('dmp_addpanel'   ,array('SLPEnhancedSearch','create_DMPPanels'   ));
}
// Dad. Explorer. Rum Lover. Code Geek. Not necessarily in that order.

<?php
/**
 * Plugin Name: Store Locator Plus : Enhanced Results
 * Plugin URI: http://www.storelocatorplus.com/product/slp4-enhanced-results/
 * Description: A premium add-on pack for Store Locator Plus that adds enhanced search results to the plugin.
 * Author: Store Locator Plus
 * Author URI: http://storelocatorplus.com/
 * Requires at least: 3.8
 * Tested up to : 4.3
 * Version: 4.3.02
 *
 * Text Domain: csa-slp-er
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
if (!class_exists('SLPEnhancedResults'   )) {
    require_once( WP_PLUGIN_DIR . '/store-locator-le/include/base_class.addon.php');

    /**
     * The Enhanced Results Add-On Pack for Store Locator Plus.
     *
     * @package StoreLocatorPlus\EnhancedResults
     * @author Lance Cleveland <lance@charlestonsw.com>
     * @copyright 2012-2015 Charleston Software Associates, LLC
     */
    class SLPEnhancedResults extends SLP_BaseClass_Addon {

        //-------------------------------------
        // Properties
        //-------------------------------------

        /**
         * Settable options for this plugin.
         * 
         * Since these options are being merged with the main plugin options on 
         * their way into the localize call for the JavaScript on the UI, it is
         * best to try to make these unique from all other options in the SLPlus
         * ecosystem.  That includes the base plugin and all add-on packs.
         *
         * Plugin meta data:
         * o extended_data_version <string> the current extended data table version that has been installed.
         * o installed_version <string> the current installed version of this add-on pack.
         *
         * UI options:
         * o add_tel_to_phone <boolean> add tel: prefix to phone field output '0'/'1'
         * o orderby <string> the order by sort
         * o resultslayout <strong> the custom results layout string
         * o show_country <boolean> show country toggle '0'/'1'
         * o show_hours <boolean> show hours toggle '0'/'1'
         * o featured_locations_display_type <string> how to render featured locations
         * o immediately_show_locations <boolean> a copy of slplus->options setting of the same name
         * o email_link_format <string> 'label_link' = "word" hyperlink, 'email_link' = email address link, 'popup_form' = popup form
         *
         * @var mixed[] $options
         */
        public  $options                = array(
            'add_tel_to_phone'               => '0'                  ,
            'disable_initial_directory'      => '0'                  ,
            'extended_data_version'          => ''                   ,
            'hide_distance'                  => '0'                  ,
            'hide_results'                   => '0'                  ,
            'immediately_show_locations'     => '1'                  ,
            'installed_version'              => ''                   ,
            'orderby'                        => 'sl_distance ASC'    ,
            'resultslayout'                  => ''                   ,
            'show_country'                   => '1'                  ,
            'show_hours'                     => '1'                  ,
            'featured_location_display_type' => 'show_within_radius' ,
            'email_link_format'              => 'label_link'         ,
            'popup_email_title'              => ''                   ,
            'popup_email_from_placeholder'   => ''                   ,
            'popup_email_subject_placeholder'   => ''                   ,
            'popup_email_message_placeholder'   => ''                   ,
        );

        //------------------------------------------------------
        // METHODS
        //------------------------------------------------------

        /**
         * Invoke the Enhanced Results plugin.
         *
         * @static
         * @return SLPEnhancedResults
         */
        public static function init() {
            static $instance = false;
            if ( !$instance ) {
                load_plugin_textdomain( 'csa-slp-er', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
                $instance = new SLPEnhancedResults (
                    array(
                        'version'                   => '4.3.02'                                 ,
                        'min_slp_version'           => '4.3.14'                                 ,

                        'name'                      => __( 'Enhanced Results', 'csa-slp-er' ),
                        'option_name'               => 'csl-slplus-ER-options'               ,

	                    'file'                      => __FILE__                              ,

                        'activation_class_name'     => 'SLPER_Activation'                       ,
                        'admin_class_name'          => 'SLPEnhancedResults_Admin'               ,
                        'ajax_class_name'           => 'SLPEnhancedResults_AJAX'                ,
                        'userinterface_class_name'  => 'SLPEnhancedResults_UI'                  ,
                    )
                );
            }
            return $instance;
        }

        /**
         * Add cross-element hooks & filters.
         *
         * Haven't yet moved all items to the AJAX and UI classes.
         */
        function add_hooks_and_filters() {

            // UI + AJAX
            add_filter( 'slp_js_options'                        , array( $this , 'filter_AddOptionsToJS'            )           );

            // ADMIN UI Locations
            add_filter('slp_column_data'                        , array( $this , 'filter_FieldDataToManageLocations'), 90 , 3   );

            // Pro Pack Export
            //
            add_filter('slp-pro-dbfields'                       , array( $this , 'filter_Locations_Export_Field'    ), 90       );
            add_filter('slp-pro-csvexport'                      , array( $this , 'filter_Locations_Export_Data'     ), 90       );                        
        }

        /**
         * Create a Map Settings Debug My Plugin panel.
         *
         * @return null
         */
        static function create_DMPPanels() {
            if (!isset($GLOBALS['DebugMyPlugin'])) { return; }
            if (class_exists('DMPPanelSLPER') == false) {
                require_once(plugin_dir_path(__FILE__).'class.dmppanels.php');
            }
            $GLOBALS['DebugMyPlugin']->panels['slp.er'] = new DMPPanelSLPER();
        }

        /**
         * Simplify the plugin debugMP interface.
         *
         * @param string $type
         * @param string $hdr
         * @param string $msg
         */
        function debugMP($type,$hdr,$msg='') {
            $this->slplus->debugMP('slp.er',$type,$hdr,$msg,NULL,NULL,true);
        }

        /**
         * Initialize the options properties from the WordPress database.
         */
        function init_options() {

            // Set the defaults for first-run
            // Especially useful for gettext stuff you cannot put in the property definitions.
            //
            $this->options_defaults = $this->options;
            $this->options_defaults['popup_email_title']                = __('Send An Email'                , 'csa-slp-er' );
            $this->options_defaults['popup_email_from_placeholder']     = __( 'Your email address.' 		, 'csa-slp-er' );
            $this->options_defaults['popup_email_subject_placeholder']  = __( 'Email subject line.' 		, 'csa-slp-er' );
            $this->options_defaults['popup_email_message_placeholder']  = __( 'What do you want to say?' 	, 'csa-slp-er' );

            // Load from DB and merge in defaults to set all options
            //
            parent::init_options();

            // Fix some stuff if empty.
            //
            if (empty($this->options['resultslayout'])) {
                $this->options['resultslayout'] = $this->slplus->defaults['resultslayout'];
            }
            $this->options['immediately_show_locations'] = $this->slplus->options['immediately_show_locations'];
        }

        /**
         * Add the options from this plugin to the list of options going to JavaScript on the UI.
         * 
         * @param mixed[] $options the current options array going into the JS localize call
         * @return mixed[] the options array with this plugin's options added to it
         */
        public function filter_AddOptionsToJS( $options ) {
            return
                array_merge(
                    $options,
                    $this->options
                );
        }

        /**
         * Change the export location row data.
         *
         * @param mixed[] $location A location data ih associated array
         * @return mixed[] Location data need to export
         */
        function filter_Locations_Export_Data($location) {
            $this->slplus->database->createobject_DatabaseExtension();
            $exData = $this->slplus->database->extension->get_data($location['sl_id']);
            $location['featured'] = isset($exData['featured']) && $this->slplus->is_CheckTrue($exData['featured']) ? '1' : '0';
            $location['rank'    ] = isset($exData['rank']) ? $exData['rank'] : '';

            return $location;
        }

        /**
         * Change the export location field.
         *
         * @param mixed[] $location Field array
         * @return mixed[] Fields need to export
         */
        function filter_Locations_Export_Field($dbFields) {
            array_push($dbFields, 'featured');
            array_push($dbFields, 'rank'    );

            return $dbFields;
        }

        /**
         * Render the extra fields on the manage location table.
         *
         * SLP Filter: slp_column_data
         *
         * @param string $theData  - the option_value field data from the database
         * @param string $theField - the name of the field from the database (should be sl_option_value)
         * @param string $theLabel - the column label for this column (should be 'Categories')
         * @return type
         */
        function filter_FieldDataToManageLocations($theData,$theField,$theLabel) {
            if (
                ($theField === 'featured') &&
                ($theData  === '0')
               ) {
                $theData = '';
            }
            return $theData;
        }

    }

    // Hook to invoke the plugin.
    //
    add_action('init'           ,array('SLPEnhancedResults','init'              ));
    add_action('dmp_addpanel'   ,array('SLPEnhancedResults','create_DMPPanels'  ));
}
// Dad. Explorer. Rum Lover. Code Geek. Not necessarily in that order.

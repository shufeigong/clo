<?php
if (!defined( 'ABSPATH'     )) { exit;   } // Exit if accessed directly, dang hackers

// Make sure the class is only defined once.
//
if (!class_exists('SLPTagalong_Admin_LocationFilters')) {
    
    /**
     * Admin Filters for Tagalong
     *
     * @package StoreLocatorPlus\Admin\Filters
     * @author Lance Cleveland <lance@charlestonsw.com>
     * @copyright 2014 Charleston Software Associates, LLC
     */
    class SLPTagalong_Admin_LocationFilters {

        //----------------------------------
        // Properties
        //----------------------------------        
        
        /**
         * The plugin object
         *
         * @var \SLPTagalong $plugin
         */
        var $addon;

        /**
         * The base plugin object.
         *
         * @var \SLPlus $slplus
         */
        var $slplus;
        
        
        /**
         * True if a filter is active.
         * 
         * @var boolean
         */
        public $filter_active = false;
        

        //----------------------------------
        // Methods
        //----------------------------------        

        /**
         *
         * @param mixed[] $params
         */
        function __construct($params) {
            // Do the setting override or initial settings.
            //
            if ($params != null) {
                foreach ($params as $name => $value) {
                    $this->$name = $value;
                }
            }            
        }

        /**
         * Create the location filter form for export filters.
         */
        function createstring_LocationFilterForm() {

           $HTML = $this->addon->admin->get_CheckList(0) ;

           return 
                '<div id="csa-slp-tagalong-location-filters">'               .
                apply_filters( 'slp-tag_locations_filter_ui', $HTML )   .
                '</div>'
                ;
        }
    }
}

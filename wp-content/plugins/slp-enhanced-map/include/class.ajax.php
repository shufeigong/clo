<?php
if (! class_exists('SLPEnhancedMap_AJAX')) {
    require_once(SLPLUS_PLUGINDIR.'/include/base_class.ajax.php');


    /**
     * Holds the ajax-only code.
     *
     * This allows the main plugin to only include this file in AJAX mode
     * via the slp_init when DOING_AJAX is true.
     *
     * @package StoreLocatorPlus\EnhancedMap\AJAX
     * @author Lance Cleveland <lance@charlestonsw.com>
     * @copyright 2014 Charleston Software Associates, LLC
     */
    class SLPEnhancedMap_AJAX extends SLP_BaseClass_AJAX {

        //-------------------------------------
        // Methods : Base Override
        //-------------------------------------

        /**
         * Things we do to latch onto an AJAX processing environment.
         *
         * Add WordPress and SLP hooks and filters only if in AJAX mode.
         *
         * WP syntax reminder: add_filter( <filter_name> , <function> , <priority> , # of params )
         *
         * Remember: <function> can be a simple function name as a string
         *  - or - array( <object> , 'method_name_as_string' ) for a class method
         * In either case the <function> or <class method> needs to be declared public.
         *
         * @link http://codex.wordpress.org/Function_Reference/add_filter
         *
         */
        public function do_ajax_startup() {
            if ( ! $this->is_valid_ajax_action() ) { return; }
            add_filter( 'slp_results_marker_data' , array( $this ,'modify_map_marker') , 50 , 1);
        }

        //-------------------------------------
        // Methods : Custom
        //-------------------------------------

        /**
         * Modify the marker data.
         *
         * @param mixed[] $marker the current marker data
         * @return mixed[]
         */
        public function modify_map_marker($marker) {
            $locationIcon = '';

            // The Location-specific Map Marker is set
            //
            if ( isset( $marker['attributes']['marker'] ) ) {
                $locationIcon = $marker['attributes']['marker'];
            } elseif ( isset( $marker['icon'] ) ) {
                $locationIcon = $marker['icon'];
            }

            return
                array_merge(
                    $marker,
                    array(
                        'icon' => $locationIcon
                    )
                );
        }

    }
}
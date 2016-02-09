<?php

if ( ! class_exists('SLPlus_AdminUI') ) {

    /**
     * Legacy Radius Selector
     *
     * @package StoreLocatorPlus\Legacy\RadiusSelector
     * @author Lance Cleveland <lance@charlestonsw.com>
     * @copyright 2015 Charleston Software Associates, LLC
     */
    class SLP_RadiusSelector extends SLPlus_BaseClass_Object {


        /**
         * Builds the HTML for the radius drop down on the UI.
         *
         * Either builds a hidden HTML field with the default radius
         * OR the innards of a select statement.
         *
         * Weird.
         *
         * Also, stores it in the slplus->data['radius_options'] setting but builds it every time.
         *
         * Even weirder.
         *
         * Doesnt' return a thing, just stores it in the data setting.
         *
         * What?
         *
         */
        public function create_html() {
            if ( ! $this->show_radius_selector() ) {
                $this->slplus->data['radius_options'] =
                    "<input type='hidden' id='radiusSelect' name='radiusSelect' value='". $this->slplus->UI->find_default_radius() . "'>";
            } else {
                $this->slplus->data['radius_options'] = $this->slplus->UI->create_string_radius_selector_options();
            }
        }

        /**
         * Should we show the radius selector or not?
         *
         * @return bool
         */
        private function show_radius_selector() {
            if ( ! $this->slplus->is_AddonActive( 'slp-enhanced-search' ) ) { return true; }
            return ( ! $this->slplus->is_CheckTrue( get_option(SLPLUS_PREFIX.'_hide_radius_selections',0 ) ) );
        }

    }
}
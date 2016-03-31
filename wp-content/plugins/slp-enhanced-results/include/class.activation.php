<?php
if (! class_exists('SLPES_Activation')) {
    require_once( SLPLUS_PLUGINDIR . 'include/base_class.activation.php' );

    /**
     * Manage plugin activation.
     *
     * @package StoreLocatorPlus\EnhancedResults\Activation
     * @author Lance Cleveland <lance@charlestonsw.com>
     * @copyright 2014-2015 Charleston Software Associates, LLC
     *
     */
    class SLPER_Activation extends SLP_BaseClass_Activation {

        //----------------------------------
        // Methods
        //----------------------------------

        /**
         * The plugin object
         *
         * @var \SLPEnhancedResults $plugin
         */
        public $addon;

        /**
         * Extended data fields that were updated during an upgrade.
         *
         * @var string[] $updated_slugs
         */
        private $updated_slugs = array();

        /**
         * Update or create the data tables.
         *
         * This can be run as a static function or as a class method.
         */
        function update() {

            // Version specific updates
            // Options do not need to be written/updated as the calling admin_init will do that.
            //
            $original_option_prefix = 'csl-slplus';
            $original_version = $this->addon->options['installed_version'];
            if (    isset( $this->addon->options['extended_data_version'] ) ) {
                $original_version = $this->addon->options['extended_data_version'];
                unset($this->addon->options['extended_data_version']);
            }

            // Upgrade To 4.2.07
            //
            // Move enhanced_results_hide_distance_in_table into serialized array.
            //
            if (  version_compare( $original_version , '4.2.07' , '<' )  ) {
                $optionName = $original_option_prefix.'-enhanced_results_hide_distance_in_table';
                $this->addon->options['hide_distance'] = get_option( $optionName , $this->addon->options['hide_distance'] );
                delete_option($optionName);

                $optionName = $original_option_prefix.'_disable_initialdirectory';
                $this->addon->options['disable_initial_directory'] = get_option( $optionName , $this->addon->options['disable_initial_directory'] );
                delete_option($optionName);

            }


            // Upgrade To 4.1.10
            //
            // Move enhanced_results_orderby into serialized array.
            // Move SLPLUS_PREFIX.'_slper' into csl-slplus-ER-options
            //
            if (  version_compare( $original_version , '4.1.10' , '<' )  ){
                $optionName = $original_option_prefix.'-enhanced_results_orderby';
                $this->addon->options['orderby'] = get_option($optionName,$this->addon->options['orderby']);
                delete_option($optionName);

                $optionName = $original_option_prefix.'-enhanced_results_add_tel_to_phone';
                $this->addon->options['add_tel_to_phone'] = get_option($optionName,$this->addon->options['add_tel_to_phone']);
                delete_option($optionName);

                $optionName = $original_option_prefix.'-enhanced_results_show_country';
                $this->addon->options['show_country'] = get_option($optionName,$this->addon->options['show_country']);
                delete_option($optionName);

                // Migrate old csl-slplu
                $optionName = $original_option_prefix . '_slper';
                $this->addon->options = array_merge( (array) get_option( $optionName , array() ) , $this->addon->options );
                delete_option($optionName);
            }

            // Upgrade To 4.0.012
            //
            if ( version_compare( $original_version , '4.0.012', '<') ){
                $optionName = $original_option_prefix.'-enhanced_results_show_hours';
                $this->addon->options['show_hours'] = get_option($optionName,$this->addon->options['show_hours']);
                delete_option($optionName);
            }


	        // Adds or updates a field.
	        //
	        $this->slplus->database->extension->add_field( __('Featured' ,'csa-slp-er') , 'boolean' , array( 'slug' => 'featured' , 'addon' => $this->addon->short_slug ) , 'wait' );
	        $this->slplus->database->extension->add_field( __('Rank'     ,'csa-slp-er') , 'int'     , array( 'slug' => 'rank'     , 'addon' => $this->addon->short_slug ) , 'wait' );
	        $this->slplus->database->extension->update_data_table( array('mode'=>'force'));
        }
    }
}
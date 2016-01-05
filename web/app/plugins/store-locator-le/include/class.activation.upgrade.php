<?php

if ( ! class_exists('SLP_Upgrade') ) {
	require_once( SLPLUS_PLUGINDIR . 'include/base_class.object.php');

	/**
	 * Class SLP_Upgrade
	 *
	 * Handles SLP Upgrades.  Only used on updates of previous installs.
	 *
	 * @package StoreLocatorPlus\Activation\Upgrade
	 * @author Lance Cleveland <lance@charlestonsw.com>
	 * @copyright 2015 Charleston Software Associates, LLC
	 *
	 *
	 * @since 4.3.00
	 */
	class SLP_Upgrade extends SLPlus_BaseClass_Object {

		/**
		 * @var array $legacy_options the named array of legacy options to their new serialized counterpart
		 *
		 * The following metadata rules apply:
		 *
		 * TYPE -
		 * For type = 'nojs' , store the new setting in the SLPlus->options_nojs[] array.
		 * For all other types, store the new setting in SLPlus->options[].
		 *
		 * If the legacy value is set in wp_options, bring that over to the serialized array.
		 *
		 * DEFAULT -
		 * If default is set AND there is no legacy setting in the wp_options table,
		 * set the options|option_nojs array entry to the specified default.
		 *
		 * If default is NOT set AND there is no legacy setting, use the value
		 * set in the SLPlus class.
		 *
		 * CALLBACK -
		 * If callback is set, process the legacy or default value through the specified function.
		 *
		$option_name                               = '';
		$serial_key                                = '';
		$this->slplus->options[ $serial_key ] = $option_value;
		 */
		private $legacy_options = array(

			// @since 4.1.03
			'csl-slplus-force_load_js'      => array(
				'key'       => 'force_load_js'      ,
				'type'      => 'nojs'               ,
			),

			// @since 4.3.00
			'csl-slplus_label_directions'   => array(
				'key'       => 'label_directions'   ,
				),

            // @since 4.3.00
            'label_email'       => array(
                'key'       => 'label_email'        ,
            ),

			// @since 4.3.00
			'csl-slplus_label_fax'   => array(
				'key'       => 'label_fax'   ,
			),

			// @since 4.3.00
			'csl-slplus_label_hours'       => array(
				'key'       => 'label_hours'        ,
				'type'      => 'nojs'               ,
			),

			// @since 4.3.00
			'csl-slplus_label_phone'   => array(
				'key'       => 'label_phone'   ,
			),


			// @since 4.2.67
			'csl-slplus_map_center'         => array(
				'key'       => 'map_center'         ,
			),

			// @since 4.3.00
			'csl-slplus-map_language'       => array(
				'key'       => 'map_language'       ,
				'type'      => 'nojs'               ,
			),

			// @since 4.0.033
			'csl-slplus_maxreturned'        => array(
				'key'       => 'max_results_returned',
				'type'      => 'nojs'
			),

			// @since 4.2.67
			'sl_admin_locations_per_page'   => array(
				'key'       => 'admin_locations_per_page'   ,
				'type'      => 'nojs'                       ,
			),

			// @since 4.2.04
			'sl_distance_unit'              => array (
				'key' => 'distance_unit',
			),

			// @since 4.3.00
			'sl_google_map_country'         => array(
				'key'       => 'default_country'    ,
				'type'      => 'nojs'               ,
				'callback'  => 'sanitize_key'       ,
			),

			// @since 4.2.04
			'sl_google_map_domain'          => array(
				'key'       => 'map_domain',
			),

			// @since 4.3.00
			'sl_instruction_message'         => array(
				'key'       => 'instructions'    ,
				'type'      => 'nojs'               ,
			),



			// @since 4.1.03
			'sl_load_locations_default'      => array(
				'key'       => 'immediately_show_locations'      ,
			),

			// @since 4.2.67
			'sl_map_height'                 => array(
				'key'       => 'map_height'         ,
				'type'      => 'nojs'               ,
			),

			// @since 4.2.67
			'sl_map_height_units'           => array(
				'key'       => 'map_height_units'   ,
				'type'      => 'nojs'               ,
			),

			// @since 4.2.67
			'sl_map_end_icon'               => array(
				'key'       => 'map_end_icon'       ,
			),

			// @since 4.2.67
			'sl_map_home_icon'              => array(
				'key'      => 'map_home_icon'       ,
			),

			// @since 4.2.67
			'sl_map_type'                   => array(
				'key'           => 'map_type'       ,
                'type'          => 'js'             ,
                'callback'      => 'fix_map_type'   ,
			),

			// @since 4.2.67
			'sl_map_width'                  => array(
				'key'       => 'map_width'          ,
				'type'      => 'nojs'               ,
			),

			// @since 4.2.67
			'sl_map_width_units'            => array(
				'key'       => 'map_width_units'    ,
				'type'      => 'nojs'               ,
			),

			// @since 4.0.033
			'sl_num_initial_displayed'      => array(
				'key'       => 'initial_results_returned'   ,
			),

			// @since 4.2.67
			'sl_remove_credits'             => array(
				'key'       => 'remove_credits'     ,
				'type'      => 'nojs'               ,
			),

			// @since 4.3.00
			'sl_website_label'              => array(
				'key'       => 'label_website'      ,
			),

			// @since 4.3.00
			'sl_zoom_level'                 => array(
				'key'       => 'zoom_level'         ,
			),

			// @since 4.3.00
			'sl_zoom_tweak'                 => array(
				'key'       => 'zoom_tweak'         ,
			),
		);

		/**
		 * Convert the legacy settings to the new serialized settings.
		 *
		 */
		private function convert_legacy_settings() {

			foreach ( $this->legacy_options as $legacy_option => $new_option_meta ) {

				// Get the legacy option
				//
				$option_value = get_option( $legacy_option , null );

				// No legacy option?  Is there a default?
				if ( is_null( $option_value ) && isset( $new_option_meta[ 'default' ] ) )  {
					$option_value = $new_option_meta[ 'default' ];
				}

				// If there was a legacy option or a default setting override.
				// Set that in the new serialized option string.
				// Otherwise leave it at the default setup in the SLPlus class.
				//
				if ( ! is_null( $option_value ) ) {

					// Callback processing
					//
					if ( isset( $new_option_meta[ 'callback' ] ) ) {
						$option_value = call_user_func_array( $new_option_meta[ 'callback' ] , array( $option_value ) );
					}

					// Set the serialized option
					//
					if ( isset( $new_option_meta['type'] ) && ( $new_option_meta['type'] === 'nojs' ) ) {
						$this->slplus->options_nojs[ $new_option_meta['key'] ] = $option_value;
					} else {
						$this->slplus->options[ $new_option_meta['key'] ] = $option_value;
					}

					// Delete the legacy option
					//
					delete_option( $legacy_option );
				}
			}

		}

		/**
		 * Move serialized settings into $options from $options_nojs as needed.
		 */
		private function convert_serial_settings() {
			$options_to_move = array(
				'label_email'		,
				'label_website'
			);
			foreach ( $options_to_move as $key ) {
				if ( isset( $this->options_nojs[ $key ] ) ) {
					$this->options[ $key ] = $this->options_nojs[ $key ];
					unset( $this->options_nojs[ $key ] );
				}
			}
		}

		/**
		 * Migrate the settings from older releases to their new serialized home.
		 */
		public function migrate_settings() {
			$installed_version = $this->slplus->installed_version;

			// No longer used
			//
			delete_option(SLPLUS_PREFIX.'_use_email_form');
			delete_option('sl_use_name_search');
			delete_option('sl_location_table_view');

			// Always re-load theme details data.
			//
			delete_option(SLPLUS_PREFIX.'-api_key');
			delete_option(SLPLUS_PREFIX.'-theme_details');
			delete_option(SLPLUS_PREFIX.'-theme_array');
			delete_option(SLPLUS_PREFIX.'-theme_lastupdated');

			// Migrate singular options to serialized options
			//
			$this->convert_legacy_settings();
			$this->convert_serial_settings();

			// Fix map domain
			//
			if ( $this->slplus->options['map_domain'] === 'maps.googleapis.com' ) {
				$this->slplus->options['map_domain'] = 'maps.google.com';
			}

			// Save Serialized Options
			//
			update_option(SLPLUS_PREFIX.'-options_nojs' , $this->slplus->options_nojs);
			update_option(SLPLUS_PREFIX.'-options'      , $this->slplus->options     );
		}
	}

    /**
     * Convert old road map types.
     *
     * @param $value
     *
     * @return string
     */
    function fix_map_type( $value ) {
        switch ( $value ) {
            case 'G_SATELLITE_MAP':
                return 'satellite';
            case 'G_HYBRID_MAP':
                return 'hybrid';
            case 'G_PHYSICAL_MAP':
                return 'terrain';
            default:
                return 'roadmap';
        }
    }
}

// Dad. Explorer. Rum Lover. Code Geek. Not necessarily in that order.
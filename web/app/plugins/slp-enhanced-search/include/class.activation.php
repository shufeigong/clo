<?php
if (! class_exists('SLPES_Activation')) {
	require_once( SLPLUS_PLUGINDIR . '/include/base_class.activation.php' );

	/**
	 * Manage plugin activation.
	 *
	 * @package StoreLocatorPlus\EnhancedSearch\Activation
	 * @author Lance Cleveland <lance@charlestonsw.com>
	 * @copyright 2013-2014 Charleston Software Associates, LLC
	 *
	 */
	class SLPES_Activation extends SLP_BaseClass_Activation {

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
		 * Update or create the data tables.
		 *
		 * This can be run as a static function or as a class method.
		 */
		function update() {

			// Version specific updates
			// Options do not need to be written/updated as the calling admin_init will do that.
			//

			// Prior to version 4.0.014?
			//
			// Move enhanced_results_orderby into serialized array.
			// Move SLPLUS_PREFIX.'_slper' into csl-slplus-ER-options
			//
			if ( ( version_compare( $this->addon->options['installed_version'], '4.0.014', '<' ) ) ) {

				// Serialize Hide Search Form
				$optionName                               = SLPLUS_PREFIX . '-enhanced_search_hide_search_form';
				$this->addon->options['hide_search_form'] = ( ( get_option( $optionName, $this->addon->options['hide_search_form'] ) === '1' ) ? '1' : '0' );
				delete_option( $optionName );

				// Serialize Search By Name
				$optionName                             = SLPLUS_PREFIX . '_show_search_by_name';
				$this->addon->options['search_by_name'] = get_option( $optionName, $this->addon->options['search_by_name'] );
				delete_option( $optionName );

				// Serialize Show State PD
				$optionName                             = 'slplus_show_state_pd';
				$this->addon->options['state_selector'] =
					get_option( $optionName, $this->addon->options['state_selector'] ) === '1' ?
						'dropdown_addressinput' :
						'hidden';
				delete_option( $optionName );

				// Rename _slpes Serial to -ES-options
				$optionName           = SLPLUS_PREFIX . '_slpes';
				$this->addon->options = array_merge( get_option( $optionName, array() ), $this->addon->options );
				delete_option( $optionName );
			}

			// 4.1
			//
			if ( ( version_compare( $this->addon->options['installed_version'], '4.1', '<' ) ) ) {

				// Serialize City Selector PD
				$optionName                            = 'sl_use_city_search';
				$this->addon->options['city_selector'] =
					get_option( $optionName, $this->addon->options['city_selector'] ) === '1' ?
						'dropdown_addressinput' :
						'hidden';
				delete_option( $optionName );

				// Serialize Country Selector PD
				$optionName                               = 'sl_use_country_search';
				$this->addon->options['country_selector'] =
					get_option( $optionName, $this->addon->options['country_selector'] ) === '1' ?
						'dropdown_addressinput' :
						'hidden';
				delete_option( $optionName );

			}

			// 4.2
			//
			if ( ( version_compare( $this->addon->options['installed_version'], '4.2', '<' ) ) ) {
				$optionName                                 = 'csl-slplus_hide_address_entry';
				$this->addon->options['hide_address_entry'] = get_option( $optionName, $this->addon->options['hide_address_entry'] );
				delete_option( $optionName );

				$ignore_radius = $this->addon->options['ignore_radius'];
				if ( $ignore_radius === '1' ) {
					$this->addon->options['radius_behavior'] = 'always_ignore'; // if this is an upgrade, check legacy setting of ignore_radius admin control and preserve it in new admin control, radius _behavior
				}
			}
		}
	}
}
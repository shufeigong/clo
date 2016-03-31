<?php

if (! class_exists('SLPlus_AdminUI_UserExperience')) {

	/**
	 * Store Locator Plus User Experience admin user interface.
	 *
	 * @property-read	string[]		$map_languages 		The Map Languages supported by the base plugin.
	 * @property-read	string[]		$option_cache
	 * @property		SLP_Settings	$settings
	 * @property-read	string[]		$update_info		A string array store user notify message
	 *
	 * @package   StoreLocatorPlus\AdminUI\UserExperience
	 * @author    Lance Cleveland <lance@charlestonsw.com>
	 * @copyright 2012-2015 Charleston Software Associates, LLC
	 */
	class SLPlus_AdminUI_UserExperience extends SLPlus_BaseClass_Object {
		private $map_languages 	= array();
		private $option_cache 	= array();
		private $update_info 	= array();
		public $settings;

		/**
		 * Invoke the User Experience object.
		 *
		 * @param array $options
		 */
		function __construct( $options = array() ) {
			parent::__construct( $options );

			$this->slplus->create_object_CountryManager();

			// Map Languages
			$this->map_languages =
				array(
					__( 'English', 'store-locator-le' )                 => 'en',
					__( 'Arabic', 'store-locator-le' )                  => 'ar',
					__( 'Basque', 'store-locator-le' )                  => 'eu',
					__( 'Bulgarian', 'store-locator-le' )               => 'bg',
					__( 'Bengali', 'store-locator-le' )                 => 'bn',
					__( 'Catalan', 'store-locator-le' )                 => 'ca',
					__( 'Czech', 'store-locator-le' )                   => 'cs',
					__( 'Danish', 'store-locator-le' )                  => 'da',
					__( 'German', 'store-locator-le' )                  => 'de',
					__( 'Greek', 'store-locator-le' )                   => 'el',
					__( 'English (Australian)', 'store-locator-le' )    => 'en-AU',
					__( 'English (Great Britain)', 'store-locator-le' ) => 'en-GB',
					__( 'Spanish', 'store-locator-le' )                 => 'es',
					__( 'Farsi', 'store-locator-le' )                   => 'fa',
					__( 'Finnish', 'store-locator-le' )                 => 'fi',
					__( 'Filipino', 'store-locator-le' )                => 'fil',
					__( 'French', 'store-locator-le' )                  => 'fr',
					__( 'Galician', 'store-locator-le' )                => 'gl',
					__( 'Gujarati', 'store-locator-le' )                => 'gu',
					__( 'Hindi', 'store-locator-le' )                   => 'hi',
					__( 'Croatian', 'store-locator-le' )                => 'hr',
					__( 'Hungarian', 'store-locator-le' )               => 'hu',
					__( 'Indonesian', 'store-locator-le' )              => 'id',
					__( 'Italian', 'store-locator-le' )                 => 'it',
					__( 'Hebrew', 'store-locator-le' )                  => 'iw',
					__( 'Japanese', 'store-locator-le' )                => 'ja',
					__( 'Kannada', 'store-locator-le' )                 => 'kn',
					__( 'Korean', 'store-locator-le' )                  => 'ko',
					__( 'Lithuanian', 'store-locator-le' )              => 'lt',
					__( 'Latvian', 'store-locator-le' )                 => 'lv',
					__( 'Malayalam', 'store-locator-le' )               => 'ml',
					__( 'Marathi', 'store-locator-le' )                 => 'mr',
					__( 'Dutch', 'store-locator-le' )                   => 'nl',
					__( 'Norwegian', 'store-locator-le' )               => 'no',
					__( 'Polish', 'store-locator-le' )                  => 'pl',
					__( 'Portuguese', 'store-locator-le' )              => 'pt',
					__( 'Portuguese (Brazil)', 'store-locator-le' )     => 'pt-BR',
					__( 'Portuguese (Portugal)', 'store-locator-le' )   => 'pt-PT',
					__( 'Romanian', 'store-locator-le' )                => 'ro',
					__( 'Russian', 'store-locator-le' )                 => 'ru',
					__( 'Slovak', 'store-locator-le' )                  => 'sk',
					__( 'Slovenian', 'store-locator-le' )               => 'sl',
					__( 'Serbian', 'store-locator-le' )                 => 'sr',
					__( 'Swedish', 'store-locator-le' )                 => 'sv',
					__( 'Tagalog', 'store-locator-le' )                 => 'tl',
					__( 'Tamil', 'store-locator-le' )                   => 'ta',
					__( 'Telugu', 'store-locator-le' )                  => 'te',
					__( 'Thai', 'store-locator-le' )                    => 'th',
					__( 'Turkish', 'store-locator-le' )                 => 'tr',
					__( 'Ukrainian', 'store-locator-le' )               => 'uk',
					__( 'Vietnamese', 'store-locator-le' )              => 'vi',
					__( 'Chinese (Simplified)', 'store-locator-le' )    => 'zh-CN',
					__( 'Chinese (Traditional)', 'store-locator-le' )   => 'zh-TW'
				);

			$this->create_object_settings();
		}

		/**
		 * Add the UX View Section on the User Experience Tab
		 */
		function action_AddUXViewSection() {
			$section_name = __( 'View', 'store-locator-le' );
			$this->settings->add_section( array( 'name' => $section_name ) );

			// Theme Selector
			//
			$this->slplus->themes->add_settings( $this->settings, $section_name, 'Style' );

			// ACTION: slp_uxsettings_modify_viewpanel
			//    params: settings object, section name
			do_action( 'slp_uxsettings_modify_viewpanel', $this->settings, $section_name );
		}

		/**
		 * Add Experience/Map/Appearance
		 * @param string $section_name
		 */
		private function add_group_map_appearance( $section_name ) {
			$group_name = __( 'Appearance', 'store-locator-le' );

			// Map Height
			$this->settings->add_ItemToGroup( array(
				'section'     => $section_name,
				'group'       => $group_name,
				'type'        => 'text',
				'setting'     => 'map_height',
				'use_prefix'  => false,
				'value'       => $this->slplus->options_nojs['map_height'],
				'label'       => __( 'Map Height', 'store-locator-le' ),
				'description' =>
					__( 'The initial map height in pixels or percent of initial page height. ', 'store-locator-le' ) .
					__( 'Can also use rules like auto and inherit if Height Units is set to blank ', 'store-locator-le' )
			) );

			// Height Units
			$this->settings->add_ItemToGroup( array(
				'section'     => $section_name,
				'group'       => $group_name,
				'type'        => 'dropdown',
				'setting'     => 'map_height_units',
				'use_prefix'  => false,
				'value'       => $this->slplus->options_nojs['map_height_units'],
				'label'       => __( 'Height Units', 'store-locator-le' ),
				'selectedVal' => $this->slplus->options_nojs['map_height_units'],
				'empty_ok'    => true,
				'custom'      =>
					array(
						array( 'label' => '%' ),
						array( 'label' => 'px' ),
						array( 'label' => 'em' ),
						array( 'label' => 'pt' ),
						array( 'label' => ' ' ),

					),
				'description' =>
					__( 'Is the width a percentage of page width or absolute pixel size? ', 'store-locator-le' ) .
					__( 'Select blank to use CSS rules like auto or inherit in the Map Height setting.', 'store-locator-le' )
			) );

			$this->settings->add_ItemToGroup( array(
				'section'     => $section_name,
				'group'       => $group_name,
				'type'        => 'text',
				'setting'     => 'map_width',
				'use_prefix'  => false,
				'value'       => $this->slplus->options_nojs['map_width'],
				'label'       => __( 'Map Width', 'store-locator-le' ),
				'description' =>
					__( 'The initial map width in pixels or percent of initial page width. ', 'store-locator-le' ) .
					__( 'Can also use rules like auto and inherit if Width Units is set to blank ', 'store-locator-le' )
			) );

			$this->settings->add_ItemToGroup( array(
				'section'     => $section_name,
				'group'       => $group_name,
				'type'        => 'dropdown',
				'setting'     => 'map_width_units',
				'use_prefix'  => false,
				'value'       => $this->slplus->options_nojs['map_width_units'],
				'label'       => __( 'Width Units', 'store-locator-le' ),
				'selectedVal' => $this->slplus->options_nojs['map_width_units'],
				'empty_ok'    => true,
				'custom'      =>
					array(
						array( 'label' => '%' ),
						array( 'label' => 'px' ),
						array( 'label' => 'em' ),
						array( 'label' => 'pt' ),
						array( 'label' => ' ' ),

					),
				'description' =>
					__( 'Is the width a percentage of page width or absolute pixel size? ', 'store-locator-le' ) .
					__( 'Select blank to use CSS rules like auto or inherit in the Map Width setting.', 'store-locator-le' )
			) );

			$this->settings->add_ItemToGroup( array(
				'section'     => $section_name,
				'group'       => $group_name,
				'type'        => 'dropdown',
				'setting'     => 'map_type',
				'use_prefix'  => false,
				'value'       => $this->slplus->options['map_type'],
				'label'       => __( 'Map Type', 'store-locator-le' ),
				'selectedVal' => $this->slplus->options['map_type'],
				'custom'      =>
					array(
						array( 'label' => 'roadmap' ),
						array( 'label' => 'hybrid' ),
						array( 'label' => 'satellite' ),
						array( 'label' => 'terrain' ),
					),
				'description' =>
					__( 'What style map do you want to use? ', 'store-locator-le' )
			) );


			$this->settings->add_ItemToGroup(
				array(
					'section'     => $section_name,
					'group'       => $group_name,
					'label'       => __( 'Remove Credits', 'store-locator-le' ),
					'type'        => 'checkbox',
					'setting'     => 'remove_credits',
					'value'       => $this->slplus->options_nojs['remove_credits'],
					'use_prefix'  => false,
					'description' =>
						__( 'Remove the search provided by tagline under the map.', 'store-locator-le' )
				)
			);
		}

		/**
		 * Add Experience / Map / Functionality
		 * @param $section_name
		 */
		private function add_group_map_functionality( $section_name ) {
			// Experience / Map / Functionality
			//
			$group_name = __( 'Functionality', 'store-locator-le' );

			/**
			 * @var SLP_Country $country
			 */
			$selections            = array();
			foreach ( $this->slplus->CountryManager->countries as $country_slug => $country ) {
				$selections[] = array(
					'label' => "{$country->name} ({$country->google_domain})",
					'value' => $country_slug
				);
			}
			$this->settings->add_ItemToGroup( array(
				'label'       => __( 'Map Domain', 'store-locator-le' ),
				'description' =>
					__( 'Select the Google maps API url to be used by default for location queries.', 'store-locator-le' ) .
					sprintf(
						__( 'Country code is used by %s to influence address guesses.' , 'store-locator-le'  ),
						$this->slplus->add_ons->get_product_url('slp-premier')
					)
			,
				'setting'     => 'default_country',
				'selectedVal' => $this->slplus->options_nojs['default_country'],
				'custom'      => $selections,
				'section'     => $section_name,
				'group'       => $group_name,
				'type'        => 'dropdown',
				'use_prefix'  => false,

			) );

			// Language Selection
			//
			$selections = array();
			foreach ( $this->map_languages as $label => $value ) {
				$selections[] = array( 'label' => $label, 'value' => $value );
			}
			$this->settings->add_ItemToGroup( array(

				'label'       => __( 'Map Language', 'store-locator-le' ),
				'description' =>
					__( 'Select the language to be used when sending and receiving data from the Google Maps API.', 'store-locator-le' ),
				'setting'     => 'map_language',
				'value'       => $this->slplus->options_nojs['map_language'],
				'selectedVal' => $this->slplus->options_nojs['map_language'],
				'custom'      => $selections,
				'section'     => $section_name,
				'group'       => $group_name,
				'type'        => 'dropdown',
				'use_prefix'  => false,

			) );

			$this->settings->add_ItemToGroup( array(

				'label'       => __( 'Zoom Level', 'store-locator-le' ),
				'description' =>
					__( 'Initial zoom level of the map if "immediately show locations" is NOT selected or if only a single location is found.', 'store-locator-le' ) .
					__( '0 = world view, 19 = house view.', 'store-locator-le' ),
				'setting'     => 'zoom_level',
				'value'       => $this->slplus->options['zoom_level'],
				'selectedVal' => $this->slplus->options['zoom_level'],
				'custom'      =>
					array(
						array( 'label' => '0' ),
						array( 'label' => '1' ),
						array( 'label' => '2' ),
						array( 'label' => '3' ),
						array( 'label' => '4' ),
						array( 'label' => '5' ),
						array( 'label' => '6' ),
						array( 'label' => '7' ),
						array( 'label' => '8' ),
						array( 'label' => '9' ),
						array( 'label' => '10' ),
						array( 'label' => '10' ),
						array( 'label' => '11' ),
						array( 'label' => '12' ),
						array( 'label' => '13' ),
						array( 'label' => '14' ),
						array( 'label' => '15' ),
						array( 'label' => '16' ),
						array( 'label' => '17' ),
						array( 'label' => '18' ),
						array( 'label' => '19' ),
					),
				'section'     => $section_name,
				'group'       => $group_name,
				'type'        => 'dropdown',
				'use_prefix'  => false,
				'empty_ok'    => true

			) );

			$this->settings->add_ItemToGroup( array(

				'label'       => __( 'Zoom Adjustment', 'store-locator-le' ),
				'description' =>
					__( 'Changes how tight auto-zoom bounds the locations shown.  Lower numbers are closer to the locations.', 'store-locator-le' ),
				'setting'     => 'zoom_tweak',
				'value'       => $this->slplus->options['zoom_tweak'],
				'selectedVal' => $this->slplus->options['zoom_tweak'],
				'custom'      =>
					array(
						array( 'label' => '-10' ),
						array( 'label' => '-9' ),
						array( 'label' => '-8' ),
						array( 'label' => '-7' ),
						array( 'label' => '-6' ),
						array( 'label' => '-5' ),
						array( 'label' => '-4' ),
						array( 'label' => '-3' ),
						array( 'label' => '-2' ),
						array( 'label' => '-1' ),
						array( 'label' => '0' ),
						array( 'label' => '1' ),
						array( 'label' => '2' ),
						array( 'label' => '3' ),
						array( 'label' => '4' ),
						array( 'label' => '5' ),
						array( 'label' => '6' ),
						array( 'label' => '7' ),
						array( 'label' => '8' ),
						array( 'label' => '9' ),
						array( 'label' => '10' ),
						array( 'label' => '11' ),
						array( 'label' => '12' ),
						array( 'label' => '13' ),
						array( 'label' => '14' ),
						array( 'label' => '15' ),
						array( 'label' => '16' ),
						array( 'label' => '17' ),
						array( 'label' => '18' ),
						array( 'label' => '19' ),
					),
				'section'     => $section_name,
				'group'       => $group_name,
				'type'        => 'dropdown',
				'use_prefix'  => false,
				'empty_ok'		=> true,

			) );
		}

		/**
		 * Add Experience / Map / Markers
		 * @param $section_name
		 */
		private function add_group_map_markers( $section_name ) {
			$group_name = __( 'Markers', 'store-locator-le' );

			// ===== Icons
			//
			$html =
				$this->slplus->data['iconNotice'] .

				"<div class='form_entry'>" .
				"<label for='map_home_icon'>" . __( 'Home Marker', 'store-locator-le' ) . "</label>" .
				"<input id='map_home_icon' name='map_home_icon' dir='rtl' size='45' " .
				"value='" . $this->slplus->options['map_home_icon'] . "' " .
				'onchange="document.getElementById(\'prev\').src=this.value">' .
				"<img id='home_icon_preview' src='" . $this->slplus->options['map_home_icon'] . "' align='top'><br/>" .
				$this->slplus->data['homeIconPicker'] .
				"</div>" .

				"<div class='form_entry'>" .
				"<label for='map_end_icon'>" . __( 'Destination Marker', 'store-locator-le' ) . "</label>" .
				"<input id='map_end_icon' name='map_end_icon' dir='rtl' size='45' " .
				"value='" . $this->slplus->options['map_end_icon'] . "' " .
				'onchange="document.getElementById(\'prev2\').src=this.value">' .
				"<img id='end_icon_preview' src='" . $this->slplus->options['map_end_icon'] . "'align='top'><br/>" .
				$this->slplus->data['endIconPicker'] .
				"</div>" .

				'<br/><p>' .
				__( 'Saved markers live here: ', 'store-locator-le' ) . SLPLUS_UPLOADDIR . "saved-icons/</p>";

			$this->settings->add_ItemToGroup(
				array(
					'section'    => $section_name,
					'group'      => $group_name,
					'label'      => '',
					'type'       => 'custom',
					'show_label' => false,
					'custom'     => $html
				)
			);
		}

		/**
		 * Add the Startup Group to Experience/Map
		 * @param string $section_name
		 */
		private function add_group_map_startup( $section_name ) {
			$group_name = __( 'At Startup', 'store-locator-le' );

			$this->settings->add_ItemToGroup( array(
				'label'       => __( 'Center Map At', 'store-locator-le' ),
				'description' =>
					__( 'Enter an address to serve as the initial focus for the map. ', 'store-locator-le' ) .
                    __( 'Set to blank to reset this to the center of your Map Domain country. ' , 'store-locator-le' ) .
					__( 'Default is the center of the country. ', 'store-locator-le' ) .
					sprintf(
						__( '%s add-on must be installed to set per-page with center_map_at="address" shortcode. ', 'store-locator-le' ),
						$this->slplus->add_ons->get_product_url('slp-enhanced-map')
					),
					__( 'Force JavaScript setting must be off when using the shortcode attribute. ', 'store-locator-le' ),
				'setting'     => 'map_center',
				'value'       => $this->slplus->options['map_center'],
				'type'        => 'textarea',
				'section'     => $section_name,
				'group'       => $group_name,
				'use_prefix'  => false,
				) );

            $this->settings->add_ItemToGroup( array(
                'label'       => __( 'Center Latitude Fallback', 'store-locator-le' ),
                'description' =>
                    __( 'Where to center the map when Google geocoding is offline. ', 'store-locator-le' ).
					__( 'Set to blank and save settings to reset to the center of the default country if Center Map At is Blank. ', 'store-locator-le' ) .
					__( 'If Center Map At has an address and this is set to blank, that address will be re-geocoded and stored here.' , 'store-locator-le' )
                    ,
                'setting'     => 'map_center_lat',
                'value'       => $this->slplus->options['map_center_lat'],
                'section'     => $section_name,
                'group'       => $group_name,
                'use_prefix'  => false,
            ) );

            $this->settings->add_ItemToGroup( array(
                'label'       => __( 'Center Longitude Fallback', 'store-locator-le' ),
                'description' =>
                    __( 'Where to center the map when Google geocoding is offline. ', 'store-locator-le' ).
					__( 'Set to blank and save settings to reset to the center of the default country if Center Map At is Blank. ', 'store-locator-le' ) .
					__( 'If Center Map At has an address and this is set to blank, that address will be re-geocoded and stored here.' , 'store-locator-le' )
            		,
                'setting'     => 'map_center_lng',
                'value'       => $this->slplus->options['map_center_lng'],
                'section'     => $section_name,
                'group'       => $group_name,
                'use_prefix'  => false,
            ) );


		}

		/**
		 * Add Experience / Results / Appearance
		 * @param string $section_name
		 */
		private function add_group_results_appearance( $section_name ) {
			$group_name = __( 'Appearance', 'store-locator-le' );
			$this->settings->add_ItemToGroup( array(
				'custom'   =>
					sprintf(
						__('Add on packs like %s will add appearance control settings here.' , 'store-locator-le'),
						$this->slplus->add_ons->get_product_url('slp-enhanced-results')
						),
				'type'    => 'details',
				'section' => $section_name,
				'group'   => $group_name
			) );
		}

		/**
		 * Add the Functionality Group to Experience/Results
		 * @param string $section_name
		 */
		private function add_group_results_functionality( $section_name ) {
			$group_name = __( 'Functionality', 'store-locator-le' );

			$this->settings->add_ItemToGroup(array(
				'label'       => __( 'Show Locations At Startup', 'store-locator-le' ),

				'description' =>
					__( 'Display locations as soon as map loads, based on map center and default radius. ', 'store-locator-le' ) .
					__( 'The settings in the startup section here impact how this mode works. ', 'store-locator-le' ) .
					sprintf(
						__( '%s provides the [slplus immediately_show_locations="true|false"] attribute option.', 'store-locator-le' ),
						$this->slplus->add_ons->get_product_url('slp-enhanced-search')
					),
				'setting'     => 'immediately_show_locations',
				'value'       => $this->slplus->options['immediately_show_locations'],

				'section'     => $section_name,
				'group'       => $group_name,
				'type'        => 'checkbox',
				'use_prefix'  => false,
				));
		}

		/**
		 * Add the Labels Group to Experience/Results
		 * @param string $section_name
		 */
		private function add_group_results_labels( $section_name ) {
			$group_name = __( 'Labels', 'store-locator-le' );

			// Website URL
			$this->settings->add_ItemToGroup(array(
				'label'       => __( 'Website URL', 'store-locator-le' ),
				'description' =>
					__( 'Search results text for the website link.', 'store-locator-le' ) .
					__( 'Changes the Locations tab field label on the admin interface. ', 'store-locator-le' )
					,
				'setting'     => 'label_website',
				'value'       => $this->slplus->WPML->get_text('label_website'),
				'section'     => $section_name,
				'group'       => $group_name,
				'use_prefix'  => false,
				));

			// Instructions
			$this->settings->add_ItemToGroup(array(
				'label'       => __( 'Instructions', 'store-locator-le' ),
				'description' =>
					__( 'Search results instructions shown if immediately show locations is not selected.', 'store-locator-le' )
					,
				'setting'     => 'instructions',
				'value'       => $this->slplus->WPML->get_text('instructions'),
				'section'     => $section_name,
				'group'       => $group_name,
				'use_prefix'  => false,
			));

			// Hours
			//
			$this->settings->add_ItemToGroup(array(
				'label'       => __( 'Hours', 'store-locator-le' ),
				'description' =>
					__( 'What to put in search results for hours.', 'store-locator-le' ) .
					__( 'Changes the Locations tab field label on the admin interface. ', 'store-locator-le' )
					,
				'setting'     => 'label_hours',
				'value'       => $this->slplus->WPML->get_text('label_hours'),
				'section'     => $section_name,
				'group'       => $group_name,
				'use_prefix'  => false,
				));

			// Email
			//
			$this->settings->add_ItemToGroup(array(
				'label'       => __( 'Email', 'store-locator-le' ),
				'description' =>
					__( 'What to put on the search results in place of an email address. ', 'store-locator-le' ) .
					__( 'Changes the Locations tab field label on the admin interface. ', 'store-locator-le' )
					,
				'setting'     => 'label_email',
				'value'       => $this->slplus->WPML->get_text('label_email'),
				'section'     => $section_name,
				'group'       => $group_name,
				'use_prefix'  => false,
				));

			// Directions
			//
			$this->settings->add_ItemToGroup(array(
				'label'       => __( 'Directions', 'store-locator-le' ),
				'description' =>
					__( 'What to put on the search results for the directions link. ', 'store-locator-le' )
					,
				'setting'     => 'label_directions',
				'value'       => $this->slplus->WPML->get_text('label_directions'),
				'section'     => $section_name,
				'group'       => $group_name,
				'use_prefix'  => false,
			));


			// Phone
			//
			$this->settings->add_ItemToGroup(array(
				'label'       => __( 'Phone', 'store-locator-le' ),
				'description' =>
					__( 'What to put on the search results preceding the phone number on search results. ', 'store-locator-le' ) .
					__( 'Changes the Locations tab field label on the admin interface. ', 'store-locator-le' )
					,
				'setting'     => 'label_phone',
				'value'       => $this->slplus->WPML->get_text('label_phone'),
				'section'     => $section_name,
				'group'       => $group_name,
				'use_prefix'  => false,
			));

			// Fax
			//
			$this->settings->add_ItemToGroup(array(
				'label'       => __( 'Fax', 'store-locator-le' ),
				'description' =>
					__( 'What to put on the search results preceding the fax number. ', 'store-locator-le' ).
					__( 'Changes the Locations tab field label on the admin interface. ', 'store-locator-le' )
					,
				'setting'     => 'label_fax',
				'value'       => $this->slplus->WPML->get_text('label_fax'),
				'section'     => $section_name,
				'group'       => $group_name,
				'use_prefix'  => false,
			));

			// Image
			$this->settings->add_ItemToGroup(array(
				'label'       => __( 'Image', 'store-locator-le' ),
				'description' =>
					__( 'Changes the Locations tab field label on the admin interface. ', 'store-locator-le' )
					,
				'setting'     => 'label_image',
				'value'       => $this->slplus->WPML->get_text('label_image'),
				'section'     => $section_name,
				'group'       => $group_name,
				'use_prefix'  => false,
			));


			// TOOD: deprecate when (ER) uses slp_ux_modify_admin_panel_results instead of sl_settings_results_labels
			//
			$this->settings->add_ItemToGroup(array(
				'custom' => apply_filters( 'slp_settings_results_labels', '' ),
				'section'     => $section_name,
				'group'       => $group_name,
				'type'        => 'custom',
				'show_label'  => false,
				));
		}

		/**
		 * Add the Search Group to Experience/Results
		 * @param string $section_name
		 */
		private function add_group_results_search( $section_name ) {
			$group_name = __( 'After Search', 'store-locator-le' );

			$this->settings->add_ItemToGroup(array(
				'label'       => __( 'Max Search Results', 'store-locator-le' ),

				'description' => __( 'How many locations does a search return? Default is 25.', 'store-locator-le' ),
				'setting'     => 'max_results_returned',
				'value'       => $this->slplus->options_nojs['max_results_returned'],

				'section'     => $section_name,
				'group'       => $group_name,
				'type'        => 'text',
				'use_prefix'  => false,
				));

		}

		/**
		 * Add the Startup Group to Experience/Results
		 * @param string $section_name
		 */
		private function add_group_results_startup( $section_name ) {
			$group_name = __( 'At Startup', 'store-locator-le' );

			$this->settings->add_ItemToGroup(array(
				'label'       => __( 'Number To Show Initially', 'store-locator-le' ),

				'description' =>
					__( 'How many locations should be shown when Immediately Show Locations is checked. ', 'store-locator-le' ) .
					__( 'Recommended maximum is 50.', 'store-locator-le' ),
				'setting'     => 'initial_results_returned',
				'value'       => $this->slplus->options['initial_results_returned'],

				'section'     => $section_name,
				'group'       => $group_name,
				'type'        => 'text',
				'use_prefix'  => false,
				));

			$this->settings->add_ItemToGroup(array(
				'label'       => __( 'Radius To Search Initially', 'store-locator-le' ),

				'description' =>
					__( 'What should immediately show locations use as the default search radius? Leave empty to use map radius default or set to a large number like 25000 to search everywhere.', 'store-locator-le' ) .
					sprintf(
						__( 'Can be set with <a href="%s" target="csa">shortcode attribute initial_radius</a> if Force Load JavaScript is turned off.', 'store-locator-le' ),
						$this->slplus->support_url . 'getting-started/shortcodes/'
					),
				'setting'     => 'initial_radius',
				'value'       => $this->slplus->options['initial_radius'],

				'section'     => $section_name,
				'group'       => $group_name,
				'type'        => 'text',
				'use_prefix'  => false,
				));


		}

		/**
		 * Create and attach settings.
		 */
		function create_object_settings() {
			if ( ! isset( $this->settings ) ) {
				require_once( 'class.settings.php' );
				$this->settings = new SLP_Settings( array(
						'name'        => $this->slplus->name . __( ' - User Experience', 'store-locator-le' ),
						'form_action' => '',
						'save_text'   => __( 'Save Settings', 'store-locator-le' )
					)
				);

			}
		}

		/**
		 * Generate the HTML for an input settings interface element.
		 *
		 * @param string $boxname
		 * @param string $label
		 * @param string $msg
		 * @param string $prefix
		 * @param string $default
		 * @param string $value - forced value
		 *
		 * @return string HTML for the div box.
		 */
		function CreateInputDiv( $boxname, $label = '', $msg = '', $prefix = SLPLUS_PREFIX, $default = '', $value = null ) {
			$whichbox = $prefix . $boxname;
			if ( $value === null ) {
				$value = $this->get_experience_option( $whichbox, $default );
			}

			return
				"<div class='form_entry'>" .
				"<div class='wpcsl-input wpcsl-list'>" .
				"<label for='$whichbox'>$label:</label>" .
				"<input  name='$whichbox' value='$value'>" .
				"</div>" .
				$this->slplus->helper->CreateHelpDiv( $boxname, $msg ) .
				"</div>";
		}

		/**
		 * Save or update custom CSS
		 *
		 * Called when "Save Settings" button is clicked
		 *
		 */
		function save_custom_css( ) {
			if ( ! is_dir( SLPLUS_UPLOADDIR . "css/" ) ) {
				wp_mkdir_p( SLPLUS_UPLOADDIR . "css/" );
			}
			$this->slplus->createobject_Activation();
			$css_file = $this->slplus->options_nojs['theme'] . '.css';
			$this->slplus->Activation->copy_newer_files( SLPLUS_PLUGINDIR . "css/$css_file", SLPLUS_UPLOADDIR . "css/$css_file" );
		}

		/**
		 * Execute the save settings action.
		 *
		 * Called when a $_POST is set when doing render_adminpage.
		 */
		function save_settings() {
            // Save Map Domain and Center Map At Settings
            $this->save_the_country();

			// Set height unit to blank, if height is "auto !important"
			if ( ( strpos( $_POST['map_height'] , 'auto' ) !== false ) && ( $_POST['map_height_units'] !== ' ' )  ) {
				$_REQUEST['map_height_units'] = ' ';
				array_push( $this->update_info, __( "Auto set height unit to blank when height is 'auto'", 'store-locator-le' ) );
			}
			// Set weight unit to blank, if height is "auto !important"
			if ( ( strpos( $_POST['map_width'] , 'auto' ) !== false ) && ( $_POST['map_width_units'] !== ' ' )  ) {
				$_REQUEST['map_width_units'] = ' ';
				array_push( $this->update_info, __( "Auto set width unit to blank when width is 'auto'", 'store-locator-le' ) );
			}

			// Height, strip non-digits, if % set range 0..100
			if ( in_array( $_POST['map_height_units'], array( '%', 'px', 'pt', 'em') ) ) {
				$_POST['map_height'] = preg_replace( '/[^0-9]/', '', $_POST['map_height'] );
				if ( $_POST['map_height_units'] == '%' ) {
					$_REQUEST['map_height'] = max( 0, min( $_POST['map_height'], 100 ) );
				}
			}

			// Width, strip non-digits, if % set range 0..100
			if ( in_array( $_POST['map_width_units'], array( '%', 'px', 'pt', 'em' ) ) ) {
				$_POST['map_width'] = preg_replace( '/[^0-9]/', '', $_POST['map_width'] );
				if ( $_POST['map_width_units'] == '%' ) {
					$_REQUEST['map_width'] = max( 0, min( $_POST['map_width'], 100 ) );
				}
			}

			// Standard Input Saves
			//
			$BoxesToHit =
				apply_filters( 'slp_save_map_settings_inputs',
					array(
						'sl_language',
						'sl_map_radii',
						'sl_instruction_message',
						'sl_radius_label',
						'sl_search_label',
						SLPLUS_PREFIX . '-theme',
					)
				);
			foreach ( $BoxesToHit as $JustAnotherBox ) {
				$this->slplus->helper->SavePostToOptionsTable( $JustAnotherBox );
			}
			// Register need translate text to WPML
			//
			$BoxesToHit =
				apply_filters( 'slp_regwpml_map_settings_inputs',
					array(
						'sl_radius_label',
						'sl_search_label',
					)
				);
			foreach ( $BoxesToHit as $JustAnotherBox ) {
				$this->slplus->WPML->register_post_options( $JustAnotherBox );
			}
			// Checkboxes
			//
			$BoxesToHit = apply_filters( 'slp_save_map_settings_checkboxes', array() );
			foreach ( (array) $BoxesToHit as $JustAnotherBox ) {
				$this->slplus->helper->SaveCheckboxToDB( $JustAnotherBox, '', '' );
			}

			// Serialized Checkboxes, Need To Blank If Not Received
			//
			$BoxesToHit = array(
				'immediately_show_locations',
				'remove_credits'
			);
			foreach ( $BoxesToHit as $BoxName ) {
				if ( ! isset( $_REQUEST[ $BoxName ] ) ) {
					$_REQUEST[ $BoxName ] = '0';
				}
			}

			// Process Theme and NOJS settings
			//
			$starting_theme = $this->slplus->options_nojs['theme'];
			$_REQUEST['theme'] = $_POST['csl-slplus-theme'];
			array_walk( $_REQUEST, array( $this->slplus, 'set_ValidOptionsNoJS' ) );
			$ending_theme = $this->slplus->options_nojs['theme'];

			// Save or Update a copy of the css file to the uploads\slp\css dir
			if ( $starting_theme !== $ending_theme ) {
				$this->save_custom_css();
			}

			// JS Options
			//
			array_walk( $_REQUEST, array( $this->slplus, 'set_ValidOptions' ) );

			update_option( SLPLUS_PREFIX . '-options'		, $this->slplus->options 		);
			update_option( SLPLUS_PREFIX . '-options_nojs'	, $this->slplus->options_nojs 	);

		}

        /**
         * Change the Center Map settings if the Map Domain (country) has changed and those settings are blank.
         *
         * @var     SLP_COUNTRY     $selected_country
         */
        private function save_the_country() {
            $selected_country = $this->slplus->CountryManager->countries[ $_POST['default_country'] ];
            $this->slplus->options['map_domain']       = $selected_country->google_domain;

            // Default Country (Map Domain) has changed, check map center settings.
            //
            if ( $_POST['default_country'] !== $this->slplus->options_nojs['default_country'] ) {
                $this->slplus->options_nojs['default_country'] = $_POST['default_country'];
            }

            if ( empty ( $_POST['map_center'    ] ) ) {
                $this->slplus->options['map_center'    ] = null;
            } else {
                $this->slplus->options['map_center'    ] = $_POST['map_center'];
            }
            if ( empty ( $_POST['map_center_lat'] ) ) {
                $this->slplus->options['map_center_lat'] = null;
            } else {
                $this->slplus->options['map_center_lat'] = $_POST['map_center_lat'];

            }
            if ( empty ( $_POST['map_center_lng'] ) ) {
                $this->slplus->options['map_center_lng'] = null;
            } else {
                $this->slplus->options['map_center_lng'] = $_POST['map_center_lng'];
            }

            $this->slplus->recenter_map();

            if ( ! is_null( $this->slplus->options['map_center'     ] ) ) { $_REQUEST['map_center'    ] = $this->slplus->options['map_center'    ]; }
            if ( ! is_null( $this->slplus->options['map_center_lat' ] ) ) { $_REQUEST['map_center_lat'] = $this->slplus->options['map_center_lat']; }
            if ( ! is_null( $this->slplus->options['map_center_lng' ] ) ) { $_REQUEST['map_center_lng'] = $this->slplus->options['map_center_lng']; }

        }

		/**
		 * Add the map panel to the map settings page on the admin UI.
		 *
		 */
		function map_settings() {
			$this->slplus->createobject_AddOnManager();

			// Experience / Map
			//
			$section_name = __( 'Map', 'store-locator-le' );
			$this->settings->add_section(array(
				'name'   => $section_name,
				'div_id' => 'map',
				));

			// Startup
			$this->add_group_map_startup( $section_name );

			// Functionality
			$this->add_group_map_functionality( $section_name );

			// Appearance
			$this->add_group_map_appearance( $section_name );

			// Markers
			$this->add_group_map_markers( $section_name );

			// ACTION: slp_ux_modify_adminpanel_map
			//    params: settings object, section name
			//
			do_action( 'slp_ux_modify_adminpanel_map', $this->settings, $section_name );
		}

		/**
		 * Retrieves options and caches them in this->option_cache or slplus->data
		 *
		 * @param string $optionName - the option name
		 * @param mixed  $default    - what the default value should be
		 *
		 * @return mixed the value of the option as saved in the database
		 */
		private function get_experience_option( $optionName, $default = '' ) {
			$matches = array();

			// If the option name is <blah>[setting] then use option_cache
			//
			if ( preg_match( '/^(.*?)\[(.*?)\]/', $optionName, $matches ) === 1 ) {
				if ( ! isset( $this->option_cache[ $matches[1] ] ) ) {
					$this->option_cache[ $matches[1] ] = get_option( $matches[1], $default );
				}

				return
					isset( $this->option_cache[ $matches[1] ][ $matches[2] ] ) ?
						$this->option_cache[ $matches[1] ][ $matches[2] ] :
						'';

			// Otherwise use slplus->data
			//
			} else {
				if (!isset($this->slplus->data[$optionName] )) {
					$this->slplus->data[$optionName] = get_option($optionName,$default);
				}
				return esc_html($this->slplus->data[$optionName]);


			}
		}

		/**
		 * Render the map settings admin page.
		 */
		function display() {

			/**
			 * @see http://goo.gl/UAXly - endIcon - the default map marker to be used for locations shown on the map
			 * @see http://goo.gl/UAXly - endIconPicker -  the icon selection HTML interface
			 * @see http://goo.gl/UAXly - homeIcon - the default map marker to be used for the starting location during a search
			 * @see http://goo.gl/UAXly - homeIconPicker -  the icon selection HTML interface
			 * @see http://goo.gl/UAXly - iconNotice - the admin panel message if there is a problem with the home or end icon
			 * @see http://goo.gl/UAXly - siteURL - get_site_url() WordPress call
			 */
			if ( ! isset( $this->slplus->data['homeIconPicker'] ) ) {
				$this->slplus->data['homeIconPicker'] = $this->slplus->AdminUI->CreateIconSelector( 'map_home_icon', 'home_icon_preview' );
			}
			if ( ! isset( $this->slplus->data['endIconPicker'] ) ) {
				$this->slplus->data['endIconPicker'] = $this->slplus->AdminUI->CreateIconSelector( 'map_end_icon', 'end_icon_preview' );
			}

			// Icon is the old path, notify them to re-select
			//
			$this->slplus->data['iconNotice'] = '';
			if ( ! isset( $this->slplus->data['siteURL'] ) ) {
				$this->slplus->data['siteURL'] = get_site_url();
			}
			if ( ! ( strpos( $this->slplus->options['map_home_icon'], 'http' ) === 0 ) ) {
				$this->slplus->options['map_home_icon'] = $this->slplus->data['siteURL'] . $this->slplus->options['map_home_icon'];
			}
			if ( ! ( strpos( $this->slplus->options['map_end_icon'], 'http' ) === 0 ) ) {
				$this->slplus->options['map_end_icon'] = $this->slplus->data['siteURL'] . $this->slplus->options['map_end_icon'];
			}
			if ( ! $this->slplus->helper->webItemExists( $this->slplus->options['map_home_icon'] ) ) {
				$this->slplus->data['iconNotice'] .=
					sprintf(
						__( 'Your home marker %s cannot be located, please select a new one.', 'store-locator-le' ),
						$this->slplus->options['map_home_icon']
					)
					.
					'<br/>';
			}
			if ( ! $this->slplus->helper->webItemExists( $this->slplus->options['map_end_icon'] ) ) {
				$this->slplus->data['iconNotice'] .=
					sprintf(
						__( 'Your destination marker %s cannot be located, please select a new one.', 'store-locator-le' ),
						$this->slplus->options['map_end_icon']
					)
					.
					'<br/>';
			}
			if ( $this->slplus->data['iconNotice'] != '' ) {
				$this->slplus->data['iconNotice'] =
					"<div class='highlight' style='background-color:LightYellow;color:red'><span style='color:red'>" .
					$this->slplus->data['iconNotice'] .
					"</span></div>";
			}

			//-------------------------
			// Navbar Section
			//-------------------------
			$this->settings->add_section(
				array(
					'name'        => 'Navigation',
					'div_id'      => 'navbar_wrapper',
					'description' => $this->slplus->AdminUI->create_Navbar(),
					'innerdiv'    => false,
					'is_topmenu'  => true,
					'auto'        => false,
					'headerbar'   => false
				)
			);

			//------------------------------------
			// Create The Search Form Settings Panel
			//
			add_action( 'slp_build_map_settings_panels', array( $this, 'search_form_settings' ), 10 );
			add_action( 'slp_build_map_settings_panels', array( $this, 'map_settings' ), 20 );
			add_action( 'slp_build_map_settings_panels', array( $this, 'results_settings' ), 30 );
			add_action( 'slp_build_map_settings_panels', array( $this, 'action_AddUXViewSection' ), 40 );

			//------------------------------------
			// Render It
			//
            if ( count( $this->update_info ) > 0 ) {
                $update_msg = "<div class='highlight'>" . __( 'Successful Update', 'store-locator-le' );
                foreach ( $this->update_info as $info_msg ) {
                    $update_msg .= '<br/>' . $info_msg;
                }
                $update_msg .= '</div>';
                $this->update_info = array();
                print $update_msg;
            }
			do_action( 'slp_build_map_settings_panels' );
			$this->settings->render_settings_page();
			$this->slplus->AdminUI->render_rate_box();
		}

		/**
		 * Create the results settings panel
		 *
		 */
		function results_settings() {
			$section_name = __( 'Results', 'store-locator-le' );
			$this->settings->add_section(array(
				'name'   => $section_name,
				'div_id' => 'results',
				));

			// Experience / Results / At Startup
			//
			$this->add_group_results_startup( $section_name );

			// Experience / Results / After Search
			//
			$this->add_group_results_search( $section_name );

			// Experience / Results / Functionality
			//
			$this->add_group_results_functionality( $section_name );

			// Experience / Results / Appearance
			//
			$this->add_group_results_appearance( $section_name );

			// Experience / Results / Labels
			//
			$this->add_group_results_labels( $section_name );

			// ACTION: slp_ux_modify_adminpanel_results
			//    params: settings object, section name
			//
			do_action( 'slp_ux_modify_adminpanel_results', $this->settings, $section_name );
		}


		/**
		 * Save the options to the WP database options table.
		 *
		 * @return string update message
		 */
		function save_options() {
			add_action( 'slp_save_map_settings', array( $this, 'save_settings' ), 10 );
			do_action( 'slp_save_map_settings' );
			// TODO: this can go away when all add-on packs convert to the parent::do_admin_startup() in base_class_admin.
			//
			do_action( 'slp_save_ux_settings' );
		}

		/**
		 * Add the search form panel to the map settings page on the admin UI.
		 */
		function search_form_settings() {

			// Add Search Section
			//
			$section_name = __( 'Search', 'store-locator-le' );
			$this->settings->add_section(
				array(
					'name'   => $section_name,
					'div_id' => 'search',
				)
			);

			// -- Features
			//
			$html =

				// TODO: serialize this variable into options or options_nojs
				//
				$this->CreateInputDiv(
					'sl_map_radii',
					__( 'Radii Options', 'store-locator-le' ),
					__( 'Separate each number with a comma ",". Put parenthesis "( )" around the default.', 'store-locator-le' ),
					'',
					'10,25,50,100,(200),500'
				) .

				"<div class='form_entry'>" .
				"<label for='distance_unit'>" . __( 'Distance Unit', 'store-locator-le' ) . ':</label>' .
				"<select name='distance_unit'>";

			foreach (
				array(
					__( 'Kilometers', 'store-locator-le' ) => __( 'km', 'store-locator-le' ),
					__( 'Miles', 'store-locator-le' )      => __( 'miles', 'store-locator-le' ),
				) as $key => $sl_value
			) {
				$selected = ( $this->slplus->options['distance_unit'] == $sl_value ) ? " selected " : "";
				$html .= "<option value='$sl_value' $selected>$key</option>\n";
			}

			$html .=
				'</select>' .
				'</div>';

			// Search Features Group
			//
			$this->settings->add_ItemToGroup(
				array(
					'section'    => $section_name,
					'group'      => __( 'Search Features', 'store-locator-le' ),
					'label'      => '',
					'type'       => 'custom',
					'show_label' => false,
					// TODO: Remove this when slp_settings_search_features is replaced with slp_ux_modify_adminpanel_search (GFI)
					// FILTER: slp_settings_search_features
					'custom'     => apply_filters( 'slp_settings_search_features', $html )
				)
			);


			// Search Labels Group
			//
			$html =

				// TODO: serialize this variable into options or options_nojs
				//
				$this->CreateInputDiv(
					'sl_search_label',
					__( 'Address', 'store-locator-le' ),
					__( 'Search form address label.', 'store-locator-le' ),
					'',
					__( 'Address / Zip', 'store-locator-le' )
				) .

				// TODO: serialize this variable into options or options_nojs
				//
				$this->CreateInputDiv(
					'sl_radius_label',
					__( 'Radius', 'store-locator-le' ),
					__( 'Search form radius label.', 'store-locator-le' ),
					'',
					__( 'Within', 'store-locator-le' )
				);

			// Search Labels Group
			//
			$this->settings->add_ItemToGroup(
				array(
					'section'    => $section_name,
					'group'      => __( 'Search Labels', 'store-locator-le' ),
					'label'      => '',
					'type'       => 'custom',
					'show_label' => false,
					// TODO: Remove this when slp_settings_search_labels is replaced with slp_ux_modify_adminpanel_search (GFI)
					// FILTER: slp_settings_search_labels
					'custom'     => apply_filters( 'slp_settings_search_labels', $html )
				)
			);

			// Legacy Add On Packs using slp_map_settings_searchform FILTER
			// TODO: Remove this when slp_map_settings_searchform is replaced with slp_ux_modify_adminpanel_search (REX)
			//
			$html = apply_filters( 'slp_map_settings_searchform', '' );
			if ( ! empty( $html ) ) {
				$this->settings->add_ItemToGroup(
					array(
						'section'    => $section_name,
						'group'      => __( 'Add On Packs', 'store-locator-le' ),
						'label'      => '',
						'type'       => 'custom',
						'show_label' => false,
						'custom'     => $html
					)
				);
			}

			// FILTER: slp_ux_modify_adminpanel_search
			//    params: settings object, section name
			//
			do_action( 'slp_ux_modify_adminpanel_search', $this->settings, $section_name );
		}


		//------------------------------------------------------------------------
		// DEPRECATED
		//------------------------------------------------------------------------

		/**
		 * Do not use, deprecated.
		 *
		 * @deprecated 4.0.00
		 */
		function createSettingsGroup( $a = null, $b = null, $c = null, $d = null, $e = null, $f = null ) {
			$this->slplus->helper->create_string_wp_setting_error_box( $this->slplus->createstring_Deprecated( __FUNCTION__ ) );
		}

		/**
		 * Do not use, deprecated.
		 *
		 * @deprecated 4.3.00
		 */
		function CreatePulldownDiv( $a = null, $b = null, $c = null, $d = null, $e = null, $f = null ) {
			$this->slplus->helper->create_string_wp_setting_error_box( $this->slplus->createstring_Deprecated( __FUNCTION__ ) );
		}

		/**
		 * Do not use, deprecated.
		 *
		 * @deprecated 4.3.00
		 */
		function CreateTextAreaDiv( $a = null, $b = null, $c = null, $d = null, $e = null, $f = null ) {
			$this->slplus->helper->create_string_wp_setting_error_box( $this->slplus->createstring_Deprecated( __FUNCTION__ ) );
		}
	}
}

// Dad. Explorer. Rum Lover. Code Geek. Not necessarily in that order.

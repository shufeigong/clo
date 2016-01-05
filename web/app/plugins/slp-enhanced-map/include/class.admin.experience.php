<?php
if ( ! class_exists( 'SLPEM_Admin_ExperienceSettings' ) ) {
	require_once( WP_PLUGIN_DIR . '/store-locator-le/include/base_class.object.php');

	/**
	 * Class SLPEM_Admin_ExperienceSettings
	 *
	 * The things that modify the Admin / Experience tab.
	 *
	 * @property SLPEnhancedMap $addon
	 *
	 * Text Domain: csa-slp-em
	 */
	class SLPEM_Admin_ExperienceSettings extends SLPlus_BaseClass_Object {
		public $addon;

		/**
		 * Add Experience / Map settings.
		 * @param Rename wpCSL_settings_slplus to SLP_Settings. $settings
		 * @param string $section_name
		 */
		public function add_map_settings( $settings , $section_name ) {
			$this->add_startup_settings( $settings , $section_name );
			$this->add_functionality_settings( $settings , $section_name );
			$this->add_appearance_settings( $settings , $section_name );
		}

		/**
		 * Experience / Map / Appearance
		 * @param Rename wpCSL_settings_slplus to SLP_Settings. $settings
		 * @param string $section_name
		 */
		private function add_appearance_settings( $settings , $section_name ) {
			$group_name = __( 'Appearance', 'csa-slp-em' );

			$settings->add_ItemToGroup( array(
				'section'       => $section_name        ,
				'group'         => $group_name          ,
				'label'         => $this->addon->name   ,
				'type'          => 'subheader'          ,
				'show_label'    => false
				));

			$settings->add_ItemToGroup(array(
				'label'         => __('Add Map Toggle On UI','csa-slp-em'),
				'description'   =>
					__('Add a map on/off toggle to the user interface.','csa-slp-em')
					,
				'setting'       => $this->addon->option_name .'[show_maptoggle]'   ,
				'value'         => $this->addon->options['show_maptoggle'],
				'type'          => 'checkbox'           ,
				'use_prefix'    => false                ,
				'section'       => $section_name        ,
				'group'         => $group_name          ,
				));

			$settings->add_ItemToGroup(array(
				'label'         => __('Hide The Map','csa-slp-em'),
				'description'   =>
					__('Do not show the map on the user interface.','csa-slp-em')
			,
				'setting'       => $this->addon->option_name .'[hide_map]'   ,
				'value'         => $this->addon->options['hide_map'],
				'type'          => 'checkbox'           ,
				'use_prefix'    => false                ,
				'section'       => $section_name        ,
				'group'         => $group_name          ,
			));

			$html  =
				$this->slplus->helper->CreateCheckboxDiv(
					'sl_map_overview_control',
					__('Show Map Inset Box','csa-slp-em'),
					__('When checked the map inset is shown.', 'csa-slp-em'),
					''
				) .
				$this->slplus->helper->CreateCheckboxDiv(
					'_disable_largemapcontrol3d',
					__('Hide map 3d control','csa-slp-em'),
					__('Turn the large map 3D control off.', 'csa-slp-em')
				) .
				$this->slplus->helper->CreateCheckboxDiv(
					'_disable_scalecontrol',
					__('Hide map scale','csa-slp-em'),
					__('Turn the map scale off.', 'csa-slp-em')
				) .
				$this->slplus->helper->CreateCheckboxDiv(
					'_disable_maptypecontrol',
					__('Hide map type','csa-slp-em'),
					__('Turn the map type selector off.', 'csa-slp-em')
				) .
				$this->slplus->helper->CreateCheckboxDiv(
					'hide_bubble',
					__('Hide Info Bubble', 'csa-slp-em'),
					__('Disable the on-map info bubble.','csa-slp-em'),
					'',
					false,
					0,
					$this->addon->options['hide_bubble']
				) .

				// TODO: serialize this variable into options or options_nojs
				//
				$this->slplus->AdminUI->Experience->CreateInputDiv(
					'-maptoggle_label',
					__('Toggle Label', 'csa-slp-em'),
					__('The text that appears before the display map on/off toggle.','csa-slp-em'),
					SLPLUS_PREFIX,
					__('Map','csa-slp-em')
				)
			;

			$settings->add_ItemToGroup(
				array(
					'section'       => $section_name                                        ,
					'group'         => $group_name                                          ,
					'show_label'    => false ,
					'type'          => 'custom'                                             ,
					'custom'        => $html
				)
			);

			// Map Layout
			$layout = empty( $this->addon->options['maplayout'] ) ? $this->slplus->defaults['maplayout'] : $this->addon->options['maplayout'];
			$settings->add_ItemToGroup(array(
				'label'         => __('Map Layout','csa-slp-em'),
				'description'   =>
					__('Enter your custom info bubble layout. ','csa-slp-em') .
					sprintf('<a href="%s/user-experience/map/" target="csa">%s</a> ',
						$this->slplus->support_url,
						sprintf(__('Uses HTML and %s shortcodes.','csa-slp-em'),$this->addon->name)
					) .
					__('Set it to blank to reset to the default layout. ','csa-slp-em')
			,
				'setting'       => $this->addon->option_name .'[maplayout]'   ,
				'value'         => $layout,
				'type'          => 'textarea'           ,
				'use_prefix'    => false                ,
				'section'       => $section_name        ,
				'group'         => $group_name          ,
			));

			// Bubble Layout
			$layout = empty( $this->addon->options['bubblelayout'] ) ? $this->slplus->defaults['bubblelayout'] : $this->addon->options['bubblelayout'];
			$settings->add_ItemToGroup(array(
				'label'         => __('Bubble Layout','csa-slp-em'),
				'description'   =>
					__('Enter your custom info bubble layout. ','csa-slp-em') .
					sprintf('<a href="%s/user-experience/map/bubble-layout/" target="csa">%s</a> ',
						$this->slplus->support_url,
						sprintf(__('Uses HTML plus %s shortcodes.','csa-slp-em'),$this->addon->name)
					) .
					__('Set it to blank to reset to the default layout. ','csa-slp-em')
					,
				'setting'       => $this->addon->option_name .'[bubblelayout]'   ,
				'value'         => $layout,
				'type'          => 'textarea'           ,
				'use_prefix'    => false                ,
				'section'       => $section_name        ,
				'group'         => $group_name          ,
			));

			// Map Style
			$settings->add_ItemToGroup(array(
				'label'         => __('Map Style','csa-slp-em'),
				'description'   =>
					__('Enter the JSON-style map style rules. ','csa-slp-em') .
					__('See places like <a href="https://snazzymaps.com/">Snazzy Maps</a>.','csa-slp-em')			,
				'setting'       => $this->addon->option_name .'[google_map_style]'   ,
				'value'         => $this->addon->options['google_map_style'],
				'type'          => 'textarea'           ,
				'use_prefix'    => false                ,
				'section'       => $section_name        ,
				'group'         => $group_name          ,
			));
		}

		/**
		 * Experience / Map / Functionality
		 * @param Rename wpCSL_settings_slplus to SLP_Settings. $settings
		 * @param string $section_name
		 */
		private function add_functionality_settings( $settings , $section_name ) {
			$group_name = __( 'Functionality' , 'csa-slp-em' );

			$settings->add_ItemToGroup( array(
				'section'       => $section_name        ,
				'group'         => $group_name          ,
				'label'         => $this->addon->name   ,
				'type'          => 'subheader'          ,
				'show_label'    => false
			));

			$settings->add_ItemToGroup(array(
				'section'       => $section_name                                        ,
				'group'         => $group_name                                          ,
				'show_label'    => false ,
				'type'          => 'custom'                                             ,
				'custom'        =>
					$this->slplus->helper->CreateCheckboxDiv(
						'_disable_scrollwheel',
						__('Disable Scroll Wheel','csa-slp-em'),
						__('Disable the scrollwheel zoom on the maps interface.', 'csa-slp-em')
					)
			));
		}

		/**
		 * Experience / Map / At Startup
		 * @param Rename wpCSL_settings_slplus to SLP_Settings. $settings
		 * @param string $section_name
		 */
		private function add_startup_settings( $settings , $section_name ) {
			$group_name = __( 'At Startup' , 'csa-slp-em' );

			$settings->add_ItemToGroup( array(
				'section'       => $section_name        ,
				'group'         => $group_name          ,
				'label'         => $this->addon->name   ,
				'type'          => 'subheader'          ,
				'show_label'    => false
			));

			$settings->add_ItemToGroup(array(
				'label'         => __('Hide Home Marker', 'csa-slp-em') ,
				'description'   => __('Do not include the home map marker for the initial map loading with immediately show locations enabled.','csa-slp-em'),
				'setting'       =>  $this->addon->option_name .'[no_homeicon_at_start]',
				'value'         => $this->addon->options['no_homeicon_at_start']  ,
				'type'          => 'checkbox'                                       ,
				'section'       => $section_name                                    ,
				'group'         => $group_name                                      ,
				'use_prefix'    => false
			));

			$settings->add_ItemToGroup(array(
				'label'         => __('Do Not Auto-zoom', 'csa-slp-em') ,
				'description'   =>
					__('Use only the "zoom level" setting when rendering the initial map for immediately show locations. ','csa-slp-em') .
					__('Do not automatically zoom the map to show all initial locations.','csa-slp-em')
			,
				'setting'       =>  $this->addon->option_name .'[no_autozoom]',
				'value'         => $this->addon->options['no_autozoom']  ,
				'type'          => 'checkbox'                                       ,
				'section'       => $section_name                                    ,
				'group'         => $group_name                                      ,
				'use_prefix'    => false
			));

			$settings->add_ItemToGroup( array(
				'label'         => __('Map Display' ,'csa-slp-em') ,

				'description'   =>
					__('Set what to display when the page loads. ', 'csa-slp-em') .
					__('Show Map - Display a map. ', 'csa-slp-em') .
					__('Hide Until Search - Display nothing until an address is searched. ', 'csa-slp-em') .
					__('Image Until Search - Display the image set by Starting Image.','csa-slp-em')
			,

				'setting'       => $this->addon->option_name . '[map_initial_display]'   ,
				'selectedVal'   => $this->addon->options['map_initial_display']          ,

				'custom'        => array(
					array( 'label' => __('Show Map', 'csa-slp-em')          , 'value' => 'map' ),
					array( 'label' => __('Hide Until Search', 'csa-slp-em') , 'value' => 'hide'),
					array( 'label' => __('Image Until Search', 'csa-slp-em'), 'value' => 'image')
				)   ,

				'type'          => 'dropdown'                                                  ,
				'section'       => $section_name                                        ,
				'group'         => $group_name                                          ,
				'use_prefix'    => false                                                ,
			));


			$settings->add_ItemToGroup(array(
				'label'         => __('Starting Image', 'csa-slp-em') ,
				'description'   =>
					__('If set, this image will be displayed until a search is performed.  Enter the full URL for the image.','csa-slp-em')
			,
				'setting'       =>  $this->addon->option_name .'[starting_image]'   ,
				'value'         => $this->addon->options['starting_image']          ,
				'section'       => $section_name                                    ,
				'group'         => $group_name                                      ,
				'use_prefix'    => false
			));
		}

	}
}
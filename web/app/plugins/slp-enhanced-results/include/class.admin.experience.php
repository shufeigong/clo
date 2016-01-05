<?php
if ( ! class_exists( 'SLPER_Admin_ExperienceSettings' ) ) {
	require_once( WP_PLUGIN_DIR . '/store-locator-le/include/base_class.object.php');

	/**
	 * Class SLPER_Admin_UXSettings
	 *
	 * The things that modify the Admin / General Settings UX.
	 *
	 * Text Domain: csa-slp-er
	 */
	class SLPER_Admin_ExperienceSettings extends SLPlus_BaseClass_Object {

		/**
		 * @var SLPEnhancedResults
		 */
		public $addon;

		/**
		 * Add ER settings to Experience / Results / Functionality
		 *
		 * @param SLP_Settings $settings
		 * @param string $section_name
		 */
		function add_results_enhancements( $settings , $section_name ) {
			$this->add_results_startup( $settings , $section_name );
			$this->add_results_functionality( $settings , $section_name );
			$this->add_results_appearance( $settings , $section_name );
		}

		/**
		 * Add Experience / Results / Appearance Items
		 *
		 * @param SLP_Settings $settings
		 * @param string $section_name
		 */
		private function add_results_appearance( $settings , $section_name ) {
			$group_name = __( 'Appearance', 'csa-slp-er' );
			$settings->add_ItemToGroup( array(
				'label'   => $this->addon->name,
				'type'    => 'subheader',
				'section' => $section_name,
				'group'   => $group_name
				));

			$settings->add_ItemToGroup(array(
				'label'         => __('Use tel URI','csa-slp-er'),
				'description'   =>
					__('When checked, wraps the phone number in the results in a tel: href tag.', 'csa-slp-er'),
				'setting'       => $this->addon->option_name .'[add_tel_to_phone]'   ,
				'value'         => $this->addon->options['add_tel_to_phone'],
				'type'          => 'checkbox'           ,
				'use_prefix'    => false                ,
				'section'       => $section_name        ,
				'group'         => $group_name          ,
			));

			$settings->add_ItemToGroup(array(
				'label'         => __('Hide Distance','csa-slp-er'),
				'description'   =>
					__('Do not show the distance to the location in the results table.', 'csa-slp-er'),
				'setting'       => $this->addon->option_name .'[hide_distance]'   ,
				'value'         => $this->addon->options['hide_distance'],
				'type'          => 'checkbox'           ,
				'use_prefix'    => false                ,
				'section'       => $section_name        ,
				'group'         => $group_name          ,
			));

			$settings->add_ItemToGroup(array(
				'label'         => __('Show Country','csa-slp-er'),
				'description'   =>
					__('Display the country in the results table address.', 'csa-slp-er'),
				'setting'       => $this->addon->option_name .'[show_country]'   ,
				'value'         => $this->addon->options['show_country'],
				'type'          => 'checkbox'           ,
				'use_prefix'    => false                ,
				'section'       => $section_name        ,
				'group'         => $group_name          ,
			));

			$settings->add_ItemToGroup(array(
				'label'         => __('Show Hours','csa-slp-er'),
				'description'   =>
					__('Display the hours in the results table under the Directions link.', 'csa-slp-er'),
				'setting'       => $this->addon->option_name .'[show_hours]'   ,
				'value'         => $this->addon->options['show_hours'],
				'type'          => 'checkbox'           ,
				'use_prefix'    => false                ,
				'section'       => $section_name        ,
				'group'         => $group_name          ,
			));


			$settings->add_ItemToGroup(array(
				'label'         => __('Email Format', 'csa-slp-er'),

				'description'   =>
					__('How email will be displayed and function in the results list.', 'csa-slp-er'),

				'setting'       => $this->addon->option_name . '[email_link_format]',
				'selectedVal'   => $this->addon->options['email_link_format']       ,

				'custom'        => array(
					array(
						'label' => __('Email Label with Email Link'    ,'csa-slp-er') ,
						'value' => 'label_link'
					),
					array(
						'label' => __('Email Address with Email Link'  ,'csa-slp-er') ,
						'value' => 'email_link'
					),
					array(
						'label' => __('Popup Form Linked To Email'     ,'csa-slp-er') ,
						'value' => 'popup_form'
					),
				),

				'type'          => 'dropdown'                             ,
				'use_prefix'    => false,

				'section'       => $section_name        ,
				'group'         => $group_name          ,

			));

			// Experience / Results / Appearance / Results Layout
			$layout = empty( $this->addon->options['resultslayout'] ) ? $this->slplus->defaults['resultslayout'] : $this->addon->options['resultslayout'];
			$settings->add_ItemToGroup(array(
				'label'         => __('Results Layout','csa-slp-er'),
				'description'   =>
					__('Enter your custom results area layout for the location results. ','csa-slp-er') .
					sprintf('<a href="%s/user-experience/results/results-layout/" target="csa">%s</a> ',
						$this->slplus->support_url,
						sprintf(__('Uses HTML plus %s shortcodes.','csa-slp-er'),$this->addon->name)
					) .
					__('Set it to blank to reset to the default layout. ','csa-slp-er') .
					__('Overrides all other results settings.','csa-slp-er'),
				'setting'       => $this->addon->option_name .'[resultslayout]'   ,
				'value'         => $layout,
				'type'          => 'textarea'           ,
				'use_prefix'    => false                ,
				'section'       => $section_name        ,
				'group'         => $group_name          ,
			));
		}

		/**
		 * Add Experience / Results / Functionality Items
		 *
		 * @param SLP_Settings $settings
		 * @param string $section_name
		 */
		private function add_results_functionality( $settings , $section_name ) {
			$group_name = __('Functionality' , 'csa-slp-er' );
			$settings->add_ItemToGroup(array(
				'label'         => $this->addon->name   ,
				'type'          => 'subheader'          ,
				'section'       => $section_name        ,
				'group'         => $group_name
				));

			$settings->add_ItemToGroup(array(
				'label'         => __('Order Results By', 'csa-slp-er'),

				'description'   =>
					__('Select a sort order for the results.  Default is Closest..Furthest.', 'csa-slp-er'),

				'setting'       => $this->addon->option_name . '[orderby]',
				'selectedVal'   => $this->addon->options['orderby']       ,

				'custom'        => array(
					array(
						'label' => __('Featured, Rank, Closest','csa-slp-er') ,
						'value' => 'featured DESC,rank ASC,sl_distance ASC'
					),
					array(
						'label' => __('Featured, Rank, A..Z'   ,'csa-slp-er') ,
						'value' => 'featured DESC,rank ASC,sl_store ASC'
					),
					array(
						'label' => __('Featured Then Closest','csa-slp-er') ,
						'value' => 'featured DESC,sl_distance ASC'
					),
					array(
						'label' => __('Featured Then A..Z'   ,'csa-slp-er') ,
						'value' => 'featured DESC,sl_store ASC'
					),
					array(
						'label' => __('Rank Then Closest','csa-slp-er') ,
						'value' => 'rank ASC,sl_distance ASC'
					),
					array(
						'label' => __('Rank Then A..Z'   ,'csa-slp-er') ,
						'value' => 'rank ASC,sl_store ASC'
					),
					array(
						'label' => __('Closest..Furthest'  ,'csa-slp-er')  ,
						'value' => 'sl_distance ASC'
					),
					array(
						'label' => __('Name A..Z'          ,'csa-slp-er')  ,
						'value' => 'sl_store ASC'
					),
					array(
						'label' => __('Random'             ,'csa-slp-er')  ,
						'value' => 'random'
					),
				),

				'type'          => 'dropdown'                             ,
				'use_prefix'    => false,

				'section'       => $section_name        ,
				'group'         => $group_name          ,

			));

			$settings->add_ItemToGroup(array(
				'label'         => __('Featured Locations' , 'csa-slp-er'),
				'description'   =>
					sprintf("%s<br/>%s<br/>%s"
						,__('Set if the featured location should be showed:', 'csa-slp-er')
						,__('Show If In Radius - Only when the location are in radius.', 'csa-slp-er')
						,__('Always Show - Always show featured locations.', 'csa-slp-er')) ,
				'setting'       => $this->addon->option_name .'[featured_location_display_type]'   ,
				'selectedVal'   => $this->addon->options['featured_location_display_type'],
				'custom'        => array(
					array( 'label' => __('Show If In Radius', 'csa-slp-er'), 'value' => 'show_within_radius' ),
					array( 'label' => __('Always Show',       'csa-slp-er'), 'value' => 'show_always'),
				),
				'type'          => 'dropdown'           ,
				'use_prefix'    => false                ,
				'section'       => $section_name        ,
				'group'         => $group_name          ,
			));
		}

		/**
		 * Add Experience / Results / Startup Items
		 *
		 * @param SLP_Settings $settings
		 * @param string $section_name
		 */
		private function add_results_startup( $settings , $section_name ) {
			$group_name = __('At Startup' , 'csa-slp-er' );
			$settings->add_ItemToGroup(array(
				'label'         => $this->addon->name   ,
				'type'          => 'subheader'          ,
				'section'       => $section_name        ,
				'group'         => $group_name
			));

			$settings->add_ItemToGroup(array(
				'label'         => __('Disable Initial Directory','csa-slp-er'),

				'description'   =>
					__('Do not display the listings under the map when "immediately show locations" is checked.', 'csa-slp-er'),

				'setting'       => $this->addon->option_name . '[disable_initial_directory]',
				'value'         => $this->addon->options['disable_initial_directory']       ,
				'type'          => 'checkbox'                                               ,
				'use_prefix'    => false,

				'section'       => $section_name        ,
				'group'         => $group_name          ,

			));
		}
	}
}
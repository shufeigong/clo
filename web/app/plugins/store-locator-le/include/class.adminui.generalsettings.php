<?php

if (! class_exists('SLPlus_AdminUI_GeneralSettings')) {

	/**
	 * Store Locator Plus General Settings Interface
	 *
	 * @property		SLP_Settings		$settings
	 *
	 * @package   StoreLocatorPlus\AdminUI\GeneralSettings
	 * @author    Lance Cleveland <lance@charlestonsw.com>
	 * @copyright 2013 - 2015 Charleston Software Associates, LLC
	 */
	class SLPlus_AdminUI_GeneralSettings extends SLPlus_BaseClass_Object {
		public $settings;

		/**
		 * @param array $options
		 */
		function __construct( $options = array() ) {
			parent::__construct( $options );
			$this->create_object_settings();
		}

		/**
		 * Add Premium Subscription section to settings page.
		 *
		 * @param string $section_name
		 * @param string $group_name
		 */
		function add_premium_subscription_settings( $section_name , $group_name ) {

			$this->settings->add_ItemToGroup(
				array(
					'section'     => $section_name,
					'group'       => $group_name,
					'label'       => __('Premier Members' , 'store-locator-le'),
					'type'        => 'subheader',
					'description' =>
						sprintf(
							__( 'These settings enable <a href="%s" target="slp">Premier Membership</a> features within the plugin. ', 'store-locator-le' ),
							$this->slplus->slp_store_url . 'product/premier-subscription'
						) .
						__( 'This includes updating the Premier Plugin to the latest release when an update is available. ', 'store-locator-le' ) .
						__( 'Premier Members also get priority support and access to real-time product update information. ', 'store-locator-le' )
				)
			);

			$this->settings->add_ItemToGroup(
				array(
					'section'     => $section_name,
					'group'       => $group_name,
					'label'       => __( 'User ID', 'store-locator-le' ),
					'setting'     => 'premium_user_id',
					'value'       => $this->slplus->options_nojs['premium_user_id'],
					'use_prefix'  => false,
					'description' =>
						__( 'Your StoreLocatorPlus.com Premium Membership user ID.', 'store-locator-le' )
				)
			);

			$this->settings->add_ItemToGroup(
				array(
					'section'     => $section_name,
					'group'       => $group_name,
					'label'       => __( 'Subscription ID', 'store-locator-le' ),
					'setting'     => 'premium_subscription_id',
					'value'       => $this->slplus->options_nojs['premium_subscription_id'],
					'use_prefix'  => false,
					'description' =>
						__( 'Your StoreLocatorPlus.com Premium Membership subscriptions ID.', 'store-locator-le' )
				)
			);

		}

		/**
		 * Create and attach settings.
		 */
		function create_object_settings() {
			if ( ! isset( $this->settings ) ) {
				require_once( 'class.settings.php' );
				$this->settings = new SLP_Settings( array(
					'name'        => $this->slplus->name . ' - ' . __( 'General Settings', 'store-locator-le' ),
					'form_action' => admin_url() . 'admin.php?page=slp_general'
				) );
			}
		}

		/**
		 * Execute the save settings action.
		 *
		 */
		function save_options() {
			do_action( 'slp_save_generalsettings' );

			// Standard Input Saves
			// FILTER: slp_save_general_settings_inputs
			//
			$BoxesToHit =
				apply_filters( 'slp_save_general_settings_inputs',
					array(
						SLPLUS_PREFIX . '-geocode_retries',
					)
				);
			foreach ( $BoxesToHit as $JustAnotherBox ) {
				$this->slplus->helper->SavePostToOptionsTable( $JustAnotherBox );
			}

			// Checkboxes
			// FILTER: slp_save_general_settings_checkboxes
			//
			$BoxesToHit =
				apply_filters( 'slp_save_general_settings_checkboxes',
					array(
						SLPLUS_PREFIX . '-no_google_js',
					)
				);
			foreach ( $BoxesToHit as $JustAnotherBox ) {
				$this->slplus->helper->SaveCheckboxToDB( $JustAnotherBox, '', '' );
			}

			// Serialized Checkboxes, Need To Blank If Not Received
			//
			$BoxesToHit = array(
				'extended_admin_messages',
				'force_load_js',
			);
			foreach ( $BoxesToHit as $BoxName ) {
				if ( ! isset( $_REQUEST[ $BoxName ] ) ) {
					$_REQUEST[ $BoxName ] = '0';
				}
			}

			// Serialized Options Setting for stuff going into slp.js.
			// This should be used for ALL new JavaScript options.
			//
			array_walk( $_REQUEST, array( $this->slplus, 'set_ValidOptions' ) );
			update_option( SLPLUS_PREFIX . '-options', $this->slplus->options );

			// Serialized Options Setting for stuff NOT going to slp.js.
			// This should be used for ALL new options not going to slp.js.
			//
			array_walk( $_REQUEST, array( $this->slplus, 'set_ValidOptionsNoJS' ) );
			update_option( SLPLUS_PREFIX . '-options_nojs', $this->slplus->options_nojs );
		}

		/**
		 * Build the admin settings panel.
		 */
		function build_AdminSettingsPanel() {
			$section_name = __( 'Admin', 'store-locator-le' );
			$this->settings->add_section( array( 'name' => $section_name ) );

			$group_name = __( 'Add On Packs ' , 'store-locator-le' );
			$this->settings->add_ItemToGroup(array(
				'label'       => __( 'SLP Plugin Updates', 'store-locator-le' ),
				'description' =>
					__( 'Which build target do you want to update your  plugins to? ', 'store-locator-le' ) .
					__( 'Leave this as production unless you really know what you are doing! ', 'store-locator-le' )
					,

				'setting'     => 'build_target',
				'selectedVal' => $this->slplus->options_nojs['build_target'],
				'type'        => 'dropdown',
				'custom'      => array(
					array( 'label' => 'production' ),
					array( 'label' => 'prerelease' )
					),

				'section'     => $section_name,
				'group'       => $group_name,
				'use_prefix'  => false,
				));

			$this->add_premium_subscription_settings( $section_name , $group_name );

			// ACTION: slp_generalsettings_modify_adminpanel
			//    params: settings object, section name
			do_action( 'slp_generalsettings_modify_adminpanel', $this->settings, $section_name );
		}

		/**
		 * Build the Google settings panel.
		 */
		function build_ServerSection() {
			$this->slplus->createobject_AddOnManager();

			$sectName = __( 'Server', 'store-locator-le' );
			$this->settings->add_section( array( 'name' => $sectName ) );

			$groupName = __( 'Geocoding', 'store-locator-le' );
			$this->settings->add_ItemToGroup(
				array(
					'section'     => $sectName,
					'group'       => $groupName,
					'label'       => __( 'Server-To-Server Speed', 'store-locator-le' ),
					'setting'     => 'http_timeout',
					'use_prefix'  => false,
					'type'        => 'list',
					'value'       => $this->slplus->options_nojs['http_timeout'],
					'custom'      =>
						array(
							'Slow'   => '30',
							'Normal' => '10',
							'Fast'   => '3',
						),
					'description' =>
						__( 'How fast is your server when communicating with other servers like Google? ', 'store-locator-le' ) .
						__( 'Set this to slow if you get frequent geocoding errors but geocoding works sometimes. ', 'store-locator-le' ) .
						__( 'Set this to fast if you never have geocoding errors and are bulk loading more than 100 locations at a time. ', 'store-locator-le' )
				)
			);
			$this->settings->add_ItemToGroup(
				array(
					'section'     => $sectName,
					'group'       => $groupName,
					'label'       => __( 'Geocode Retries', 'store-locator-le' ),
					'setting'     => 'geocode_retries',
					'type'        => 'list',
					'value'       => get_option( SLPLUS_PREFIX . '-geocode_retries', '3' ),
					'custom'      =>
						array(
							'None' => 0,
							'1'    => '1',
							'2'    => '2',
							'3'    => '3',
							'4'    => '4',
							'5'    => '5',
							'6'    => '6',
							'7'    => '7',
							'8'    => '8',
							'9'    => '9',
							'10'   => '10',
						),
					'description' =>
						__( 'How many times should we try to set the latitude/longitude for a new address? ', 'store-locator-le' ) .
						__( 'Higher numbers mean slower bulk uploads. ', 'store-locator-le' ) .
						__( 'Lower numbers make it more likely the location will not be set during bulk uploads. ', 'store-locator-le' ) .
						sprintf( __( 'Bulk import or re-geocoding is a %s feature.', 'store-locator-le' ), $this->slplus->add_ons->get_product_url( 'slp-pro' ) )
				)
			);

			$this->settings->add_ItemToGroup(
				array(
					'section'     => $sectName,
					'group'       => $groupName,
					'label'       => __( 'Maximum Retry Delay', 'store-locator-le' ),
					'setting'     => 'retry_maximum_delay',
					'use_prefix'  => false,
					'value'       => $this->slplus->options_nojs['retry_maximum_delay'],
					'description' =>
						__( 'Maximum time to wait between retries, in seconds. ', 'store-locator-le' ) .
						__( 'Use multiples of 1. ', 'store-locator-le' ) .
						__( 'Recommended value is 5. ', 'store-locator-le' ) .
						sprintf( __( 'Bulk import or re-geocoding is a %s feature.', 'store-locator-le' ), $this->slplus->add_ons->get_product_url( 'slp-pro' ) )
				)
			);

			// Google License
			//
			$groupName = __( 'Google Business License', 'store-locator-le' );
			$this->settings->add_ItemToGroup(
				array(
					'section'     => $sectName,
					'group'       => $groupName,
					'label'       => __( 'Google Client ID', 'store-locator-le' ),
					'setting'     => 'google_client_id',
					'use_prefix'  => false,
					'value'       => $this->slplus->options_nojs['google_client_id'],
					'description' =>
						__( 'If you have a Google Maps for Work client ID, enter it here. ', 'store-locator-le' ) .
						__( 'All Google API requests will go through your account at Google. ', 'store-locator-le' ) .
						__( 'You will receive higher quotas and faster maps I/O performance. ', 'store-locator-le' )
				)
			);
			$this->settings->add_ItemToGroup(
				array(
					'section'     => $sectName,
					'group'       => $groupName,
					'label'       => __( 'Google Private Key', 'store-locator-le' ),
					'setting'     => 'google_private_key',
					'use_prefix'  => false,
					'value'       => $this->slplus->options_nojs['google_private_key'],
					'description' =>
						__( 'Your Google private key (Crypto Key) for signing Geocoding requests. ', 'store-locator-le' ) .
						__( 'Do NOT share this with anyone and take extra measures to keep it private. ', 'store-locator-le' )
				)
			);

			// Google Developers API
			//
			$groupName = __( 'Google Developers Console', 'store-locator-le' );
			$this->settings->add_ItemToGroup(
				array(
					'section'     => $sectName,
					'group'       => $groupName,
					'label'       => __( 'Google API Server Key', 'store-locator-le' ),
					'setting'     => 'google_server_key',
					'use_prefix'  => false,
					'value'       => $this->slplus->options_nojs['google_server_key'],
					'description' =>
						__( 'If you have created a Google JavaScript For Maps API Server Key, enter it here. ', 'store-locator-le' ) .
						__( 'You will not gain performance benefits from using a Google API Server Key. ', 'store-locator-le' ) .
						__( 'You do have the option to setup pay-as-you-go at Google and get higher geocoding quotas. ', 'store-locator-le' )
				)
			);

			// ACTION: slp_generalsettings_modify_googlepanel
			//    params: settings object, section name
			do_action( 'slp_generalsettings_modify_googlepanel', $this->settings, $sectName );
		}

		/**
		 * Build the User Settings Panel
		 */
		function build_UserSettingsPanel() {
			$sectName = __( 'User Interface', 'store-locator-le' );
			$this->settings->add_section( array( 'name' => $sectName ) );

			$groupName = __( 'JavaScript', 'store-locator-le' );
			$this->settings->add_ItemToGroup(
				array(
					'section'     => $sectName,
					'group'       => $groupName,
					'label'       => '',
					'type'        => 'subheader',
					'show_label'  => false,
					'description' =>
						__( 'These settings change how JavaScript behaves on your site. ', 'store-locator-le' ) .
						( $this->slplus->javascript_is_forced ?
							'<br/><em>*' .
							sprintf(
								__( 'You have <a href="%s" target="csa">Force Load JavaScript</a> ON. ', 'store-locator-le' ),
								$this->slplus->support_url . 'general-settings/user-interface/javascript/'
							) .
							__( 'Themes that follow WordPress best practices and employ wp_footer() properly do not need this. ', 'store-locator-le' ) .
							__( 'Leaving it on slows down your site and disables a lot of extra features with the plugin and add-on packs. ', 'store-locator-le' ) .
							'</em>' :
							''
						)
				)
			);
			$this->settings->add_ItemToGroup(
				array(
					'section'     => $sectName,
					'group'       => $groupName,
					'type'        => 'checkbox',
					'use_prefix'  => false,
					'label'       => __( 'Force Load JavaScript', 'store-locator-le' ),
					'setting'     => 'force_load_js',
					'value'       => $this->slplus->is_CheckTrue( $this->slplus->options_nojs['force_load_js'] ),
					'description' =>
						__( 'Force the JavaScript for Store Locator Plus to load on every page with early loading. ', 'store-locator-le' ) .
						__( 'This can slow down your site, but is compatible with more themes and plugins. ', 'store-locator-le' ) .
						__( 'If you need to do this to make SLP work you should ask your theme author to add proper wp_footer() support to their code. ', 'store-locator-le' )
				)
			);

			// Map Interface
			//
			$groupName = __( 'Map Interface', 'store-locator-le' );
			$this->settings->add_ItemToGroup(
				array(
					'section'     => $sectName,
					'group'       => $groupName,
					'label'       => __( 'Turn Off SLP Maps', 'store-locator-le' ),
					'setting'     => 'no_google_js',
					'type'        => 'checkbox',
					'description' =>
						__( 'Check this box if your Theme or another plugin is providing Google Maps and generating warning messages. ' .
						    'THIS MAY BREAK THIS PLUGIN.',
							'store-locator-le' )
				)
			);

			// ACTION: slp_generalsettings_modify_userpanel
			//    params: settings object, section name
			do_action( 'slp_generalsettings_modify_userpanel', $this->settings, $sectName );
		}

		/**
		 * Add web app settings.
		 */
		function build_WebAppSettings() {
			$section   = __( 'Server', 'store-locator-le' );
			$groupName = __( 'Web App Settings', 'store-locator-le' );
			$this->settings->add_ItemToGroup(
				array(
					'section'     => $section,
					'group'       => $groupName,
					'label'       => __( 'PHP Time Limit', 'store-locator-le' ),
					'setting'     => 'php_max_execution_time',
					'use_prefix'  => false,
					'value'       => $this->slplus->options_nojs['php_max_execution_time'],
					'description' =>
						__( 'Maximum execution time, in seconds, for PHP processing. ', 'store-locator-le' ) .
						__( 'Affects all CSV imports for add-ons and Janitor delete all locations. ', 'store-locator-le' ) .
						__( 'SLP Default 600. ', 'store-locator-le' ) .
						__( 'On most servers you will need to edit this setting in the php.ini file. ', 'store-locator-le' ) .
						sprintf( __( 'Your server default %s. ', 'store-locator-le' ),
							ini_get( 'max_execution_time' )
						)
				)
			);
		}

		/**
		 * Render the map settings admin page.
		 */
		function render_adminpage() {
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

			// Panel building actions
			//
			add_action( 'slp_build_general_settings_panels', array( $this, 'build_UserSettingsPanel' ), 10 );
			add_action( 'slp_build_general_settings_panels', array( $this, 'build_AdminSettingsPanel' ), 20 );
			add_action( 'slp_build_general_settings_panels', array( $this, 'build_ServerSection' ), 30 );
			add_action( 'slp_build_general_settings_panels', array( $this, 'build_WebAppSettings' ), 40 );


			//------------------------------------
			// Render It
			//
			do_action( 'slp_build_general_settings_panels' );
			$this->settings->render_settings_page();
		}
	}
}
// Dad. Explorer. Rum Lover. Code Geek. Not necessarily in that order.
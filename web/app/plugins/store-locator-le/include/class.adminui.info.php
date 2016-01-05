<?php

if (! class_exists('SLPlus_AdminUI_GeneralSettings')) {

	/**
	 * Store Locator Plus manage locations admin / info tab.
	 *
	 * @package   StoreLocatorPlus\AdminUI\Info
	 * @author    Lance Cleveland <lance@charlestonsw.com>
	 * @copyright 2013 - 2015 Charleston Software Associates, LLC
	 *
	 */
	class SLPlus_AdminUI_Info extends SLPlus_BaseClass_Object {

		/**
		 * @var SLPlus_AdminUI
		 */
		public $adminui;

		/**
		 * @var boolean
		 */
		private $cache_expired = true;

		/**
		 * @var \wpCSL_settings__slplus $settings
		 */
		private $settings;

		/**
		 * Build the info object.
		 *
		 * @param $options
		 */
		function __construct( $options = array() ) {
			parent::__construct( $options );
			$this->create_object_settings();
		}

		/**
		 * Create and attach settings.
		 */
		function create_object_settings() {
			if ( ! isset( $this->settings ) ) {
				require_once( 'class.settings.php' );
				$this->settings = new SLP_Settings( array(
					'name'        => $this->slplus->name . __( ' Info', 'store-locator-le' ),
					'form_action' => '',
					'save_text'   => ''
				) );
			}
		}

		/**
		 * Create Environment Panel
		 */
		private function create_EnvironmentPanel() {

			// Add ON Packs
			//
			$addonStr = '';
			if ( isset( $this->slplus->add_ons ) ) {
				if ( ! $this->cache_expired ) {
					$new_versions = get_option( 'slplus_addon_versions' );
				} else {
					$new_versions = array();
				}

				foreach ( $this->slplus->add_ons->instances as $addon => $instantiated_addon ) {
					if ( strpos( $addon, 'slp.' ) === 0 ) {
						continue;
					}

					if ( $this->cache_expired ) {
						if ( isset( $instantiated_addon ) ) {
							$new_versions[$instantiated_addon->name] = $this->get_plugin_update_versions( $instantiated_addon );
						}
					}

					$newest_version = isset ( $new_versions[$instantiated_addon->name] ) ? $new_versions[$instantiated_addon->name] : '';

					$version =
						! is_null( $instantiated_addon ) && method_exists( $instantiated_addon, 'get_meta' ) ?
							$instantiated_addon->get_meta( 'Version' ) :
							'active';

					// If update is available, report it.
					//
					if ( $instantiated_addon != null ) {
						if ( ! empty( $newest_version ) && version_compare( $version, $newest_version, '<' ) ) {
							$version .= ' , ' . $newest_version;
							$url = $instantiated_addon->get_meta( 'PluginURI' );
							$version .= sprintf( '<a href="%s">%s</a>', $url, __( 'UPDATE HERE', 'store-locator-le' ) );

						}


						if ( ! empty( $version ) ) {
							$addonStr .= $this->create_EnvDiv( $instantiated_addon->name, $version );


						}
					}
				}

				// Cache was expired.
				//
				if ( $this->cache_expired ) {
					update_option( 'slplus_addon_versions' , $new_versions );
				}

			}

			// PHP Module Info
			//
			$php_modules = get_loaded_extensions();
			natcasesort( $php_modules );

			/** @var wpdb $wpdb */
			global $wpdb;
			$my_metadata = get_plugin_data( SLPLUS_FILE );
			$this->settings->add_section( array(
				'name'        => __( 'Plugin Environment' , 'store-locator-le' ),
				'description' =>
					$this->create_EnvDiv( $my_metadata['Name'] . __( ' Version' , 'store-locator-le' ), $my_metadata['Version'] ) .
					$addonStr .
					'<br/>' .
					$this->create_EnvDiv( __( 'This Info Cached' , 'store-locator-le' ) , $this->slplus->options_nojs['broadcast_timestamp'] ) .
					$this->create_EnvDiv( __( 'WordPress Version', 'store-locator-le' ) , $GLOBALS['wp_version'] ) .
					$this->create_EnvDiv( __( 'Site URL' 		 , 'store-locator-le' ) , get_option( 'siteurl' ) ) .
					'<br/>' .
					$this->create_EnvDiv( __( 'MySQL Version'	 , 'store-locator-le' ) , $wpdb->db_version() ) .
					$this->create_EnvDiv( __( 'PHP Version'		 , 'store-locator-le' ) , phpversion() ) .
					'<br/>' .

					$this->create_EnvDiv(
						__( 'PHP Limit', 'store-locator-le' ),
						ini_get( 'memory_limit' )
					) .

					$this->create_EnvDiv(
						__( 'WordPress General Limit', 'store-locator-le' ),
						WP_MEMORY_LIMIT
					) .

					$this->create_EnvDiv(
						__( 'WordPress Admin Limit', 'store-locator-le' ),
						WP_MAX_MEMORY_LIMIT
					) .


					$this->create_EnvDiv( __( 'PHP Peak RAM', 'store-locator-le' ) ,
						sprintf( '%0.2d MB', memory_get_peak_usage( true ) / 1024 / 1024 ) ) .

					$this->create_EnvDiv(
						__( 'PHP Post Max Size', 'store-locator-le' ) ,
						ini_get( 'post_max_size' )
					) .

					$this->create_EnvDiv( __( 'PHP Modules', 'store-locator-le' ) ,
						'<pre>' . print_r( $php_modules, true ) . '</pre>' )
			) );
		}

		/**
		 * Create a plugin environment div.
		 *
		 * @param string $label
		 * @param string $content
		 *
		 * @return string
		 */
		private function create_EnvDiv( $label, $content ) {
			return "<p class='envinfo'><span class='label'>{$label}:</span>{$content}</p>";
		}

		/**
		 * Render the How To Use text.
		 *
		 * @return string
		 */
		private function createstring_HowToUse() {
			$this->slplus->createobject_AddOnManager();

			return

				'<h4>' . __( 'Add A Location', 'store-locator-le' ) . '</h4>' .

				'<p style="padding-left: 30px;">' .

				sprintf(
					__( 'Add a location or two via the <a href="%s">Add Location form</a>.', 'store-locator-le' ),
					admin_url() . 'admin.php?page=slp_manage_locations'
				) .

				__( 'You will find this link, and other Store Locator Plus links, in the left sidebar under the Store Locator Plus entry. ', 'store-locator-le' ) .

				sprintf(
					__( 'If you have many locations to add, check out the %s and the bulk import options.', 'store-locator-le' ),
					$this->slplus->add_ons->get_product_url( 'slp-pro' )
				) .

				'</p>' .

				'<h4>' . __( 'Create A Page', 'store-locator-le' ) . '</h4>' .

				'<p style="padding-left: 30px;">' .

				__( 'Go to the sidebar and select "Add New" under the pages section.  You will be creating a standard WordPress page. ', 'store-locator-le' ) .

				sprintf(
					__( 'On that page add the [SLPLUS] <a href="%s" target="slplus">shortcode</a>.  When a visitor goes to that page it will show a default search form and a Google Map.', 'store-locator-le' ),
					$this->slplus->support_url . 'getting-started/shortcodes/'
				) .

				__( 'When someone searches for a zip code that is close enough to a location you entered it will show those locations on the map. ', 'store-locator-le' ) .

				'</p>' .

				'<h4>' . __( 'Tweak The Settings', 'store-locator-le' ) . '</h4>' .

				'<p style="padding-left: 30px;">' .

				sprintf(
					__( 'You can modify basic settings such as the options shown on the radius pull down list on the <a href="%s">Experience</a> page. ', 'store-locator-le' ),
					admin_url() . 'admin.php?page=slp_experience'
				) .

				sprintf(
					__( 'Even more settings are available via <a href="%s" target="slplus">the premium add-on packs</a>. ', 'store-locator-le' ),
					$this->slplus->slp_store_url
				) .

				'</p>' .

				'<h4>' . __( 'More Help?', 'store-locator-le' ) . '</h4>' .

				'<p style="padding-left: 30px;">' .

				sprintf(
					__( 'Check out the <a href="%s" target="slplus">online documentation and support forums</a>.', 'store-locator-le' ),
					$this->slplus->support_url
				) .

				'</p>'

				.

			    apply_filters( 'slp_how_to_use' , '' )
				;
		}


		/**
		 * Set the default HTML string if the server if offline.
		 *
		 * @return string
		 */
		private function default_broadcast() {
			return
				'
	                        <div class="csa-infobox">
	                         <h4>This plugin has been brought to you by <a href="http://www.charlestonsw.com"
	                                target="_new">Charleston Software Associates</a></h4>
	                         <p>If there is anything I can do to improve my work or if you wish to hire me to customize
	                            this plugin please
	                            <a href="http://www.storelocatorplus.com/mindset/contact-us/" target="slplus">contact us</a>.
	                         </p>
	                         </div>
	                         ';
		}

		/**
		 *  Setup and render the info tab.
		 */
		function display() {
			$this->cache_expired = $this->is_cache_expired();

			$this->settings->add_section(
				array(
					'name'        => __( 'Navigation', 'store-locator-le' ),
					'div_id'      => 'navbar_wrapper',
					'description' => $this->adminui->create_Navbar(),
					'innerdiv'    => false,
					'is_topmenu'  => true,
					'auto'        => false,
					'headerbar'   => false
				)
			);

			$this->settings->add_section(
				array(
					'name'        => __( 'How to Use', 'store-locator-le' ),
					'div_id'      => 'how_to_use',
					'description' => $this->createstring_HowToUse()
				)
			);

			$this->settings->add_section( array(
					'name'        => __( 'Plugin News', 'store-locator-le' ),
					'div_id'      => 'plugin_news',
					'description' => $this->get_broadcast(),
				)
			);

			$this->settings->add_section( array(
					'name'        => __( 'Plugin Environment', 'store-locator-le' ),
					'div_id'      => 'plugin_environment',
					'description' => $this->create_EnvironmentPanel(),
				)
			);


			$this->settings->render_settings_page();

			$this->adminui->render_rate_box();

			if ( $this->cache_expired ) {
				$this->update_cache_timestamp();
			}

		}


		/**
		 * Get the news broadcast from the remote server.
		 *
		 * @return string the HTML for the news panel
		 */
		private function get_broadcast() {

			// Fetch broadcast every 30 seconds.
			if ( ! $this->cache_expired ) {
				return get_option('slplus_broadcast');
			}

			$response = wp_remote_get( 'http://www.storelocatorplus.com/signage/index.php?sku=SLP4', array( 'timeout' => 30 ) );

			// Cache valid response and return it.
			//
			if ( is_array( $response ) ) {
				$broadcast_content = $response['body'];
				update_option( 'slplus_broadcast' , $broadcast_content );
				return $broadcast_content;
			}

			return $this->default_broadcast();
		}

		/**
		 * Get newer version info from the SLP updates server.
		 *
		 * @param SLP_BaseClass_Addon $instantiated_addon
		 */
		private function get_plugin_update_versions( $instantiated_addon ) {

			// Plugins using old-school top-level updates object
			//
			if ( isset( $instantiated_addon->Updates ) ) {
				$updates        = $instantiated_addon->Updates;
				$newest_version =
					isset( $updates->remote_version ) ?
						$updates->remote_version :
						$updates->getRemote_version();

				// Plugins using SLP 4.2-standard updates object under admin class
				//
			} elseif ( isset( $instantiated_addon->admin ) && isset( $instantiated_addon->admin->Updates ) ) {
				$updates        = $instantiated_addon->admin->Updates;
				$newest_version =
					isset( $updates->remote_version ) ?
						$updates->remote_version :
						$updates->getRemote_version();

				// Cannot find existing update object under main plugin or admin class
				//
			} else {
				$newest_version = '';

			}

		}

		/**
		 * Return true if the cache is expired.
		 *
		 * 1 H = 3600s
		 *
		 * @return bool
		 */
		private function is_cache_expired() {
			return ( time() - $this->slplus->options_nojs['broadcast_timestamp'] > ( 24 * 3600 ) );
		}

		/**
		 * Update cache timestamp
		 */
		private function update_cache_timestamp() {
			$this->slplus->options_nojs['broadcast_timestamp'] = time();
			update_option(SLPLUS_PREFIX . '-options_nojs', $this->slplus->options_nojs);
		}


	}
}
// Dad. Explorer. Rum Lover. Code Geek. Not necessarily in that order.

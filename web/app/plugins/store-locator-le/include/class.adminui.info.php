<?php

if (! class_exists('SLPlus_AdminUI_GeneralSettings')) {

	/**
	 * Store Locator Plus manage locations admin / info tab.
	 *
	 * @package   StoreLocatorPlus\AdminUI\Info
	 * @author    Lance Cleveland <lance@charlestonsw.com>
	 * @copyright 2013 - 2016 Charleston Software Associates, LLC
	 *
	 * text-domain: store-locator-le
	 *
	 * @property 		SLPlus_AdminUI	$adminui
	 * @property-read	boolean			$cache_expired
	 * @property-read   SLPlus_Settings	$settings
	 *
	 */
	class SLPlus_AdminUI_Info extends SLPlus_BaseClass_Object {
		public 	$adminui;
		private $cache_expired = true;
		private $settings;

		/**
		 * Things we do at startup.
		 */
		function initialize() {
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

			/** @var wpdb $wpdb */
			global $wpdb;
			$my_metadata = get_plugin_data( SLPLUS_FILE );
                        
			$html =
				'<div class="left_side">' .
					'<div class="content_pad">'.
					$this->create_EnvDiv( $my_metadata['Name'] , $my_metadata['Version'] ) .
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
					'</div>' .
				'</div>' .
				'<div class="right_side">' .
					'<div class="content_pad">'.
					$this->create_string_news_feed() .
					$this->create_string_social_outlets() .
					'</div>' .
				'</div>'
				;

			return $html;                        
                        
                        
			$this->settings->add_section( array(
				'name'        => __( 'Plugin Environment' , 'store-locator-le' ),
				'description' => $html

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
		 * The how to add location text.
		 */
		private function create_string_how_to_add_location() {
			return
			'<h4>' . __( 'Add A Location', 'store-locator-le' ) . '</h4>' .

			'<p>' .

			sprintf(
				__( 'Add a location or two via the <a href="%s">Add Location form</a>.', 'store-locator-le' ),
				admin_url() . 'admin.php?page=slp_manage_locations'
			) .

			__( 'You will find this link, and other Store Locator Plus links, in the left sidebar under the Store Locator Plus entry. ', 'store-locator-le' ) .

			sprintf(
				__( 'If you have many locations to add, check out the %s and the bulk import options.', 'store-locator-le' ),
				$this->slplus->get_product_url( 'slp-pro' )
			) .

			'</p>'
			;
		}

		/**
		 * The how to create a locations page info.
		 *
		 * @return string
		 */
		private function create_string_how_to_create_page() {
			return
			'<h4>' . __( 'Create A Page', 'store-locator-le' ) . '</h4>' .

			'<p>' .

			__( 'Go to the sidebar and select "Add New" under the pages section.  You will be creating a standard WordPress page. ', 'store-locator-le' ) .

			sprintf(
				__( 'On that page add the [SLPLUS] <a href="%s" target="slplus">shortcode</a>.  When a visitor goes to that page it will show a default search form and a Google Map.', 'store-locator-le' ),
				$this->slplus->support_url . 'getting-started/shortcodes/'
			) .

			__( 'When someone searches for a zip code that is close enough to a location you entered it will show those locations on the map. ', 'store-locator-le' ) .

			'</p>'
			;
		}

		/**
		 * The text for the news feed from SLP.
		 */
		private function create_string_news_feed() {
			$rss = fetch_feed('https://www.storelocatorplus.com/feed/');

			$item_quantity = 0;

			if (!is_wp_error($rss)) {
				$item_quantity = $rss->get_item_quantity(5);
				$rss_items = $rss->get_items(0, $item_quantity);
			}

			if ( $item_quantity == 0 ) {
				$news =
					'<p>' . $this->slplus->text_manager->get_admin_text( 'plugin_created_by' 	) . '</p>' .
					'<p>' . $this->slplus->text_manager->get_admin_text( 'improve_or_customize' ) . '</p>'
					;

			} else {
				$news = '';
				foreach ( $rss_items as $item ) {
					$title = esc_html( $item->get_title() );
					$news.=
						sprintf( '<li class="news_feed_item"><a href="%s" title="%s" alt="%s" target="slp">%s</a>%s</li>',
							esc_url( $item->get_permalink() ),
							$title ,
							$title ,
							$title ,
							$item->get_date('j F Y | g:i a')
						);
				}
				$news = '<ul class="news_feed_list">' . $news . '</ul>';
			}

			$html =
				'<h2>' . __( 'News' , 'store-locator-le' ) . '</h2>' .
				$news
				;

			return $html;
		}

		/**
		 * Create the social media icon array.
		 */
		private function create_string_social_outlets() {
			$html =
				$this->slplus->create_web_link( 'documentation_icon' , '', $this->get_sprite_48('documentation')    , $this->slplus->slp_store_url . 'support/documentation/' 	, __('Documentation'		,'store-locator-le')) .
				$this->slplus->create_web_link( 'twitter_icon' 		 , '', $this->get_sprite_48('twitter')   		, 'https://twitter.com/locatorplus/' 						, __('SLP at Twitter'	 	,'store-locator-le')) .
				$this->slplus->create_web_link( 'youtube_icon' 		 , '', $this->get_sprite_48('youtube')   		, 'https://www.youtube.com/channel/UCJIMv63upz-qIaB5EcursyQ', __('SLP YouTube Channel'	,'store-locator-le')) .
				$this->slplus->create_web_link( 'rss_icon' 	   		 , '', $this->get_sprite_48('rss')   	   		, $this->slplus->slp_store_url . 'feed/' 					, __('RSS Feed'			 	,'store-locator-le'))
				;

			return
				'<h2>' . __( 'More Info', 'store-locator-le' ) . '</h2>' .
				$html;
		}

		/**
		 * Generate a sprite output.
		 *
		 * @param $what
		 * @return string
		 */
		private function get_sprite_48( $what ) {
			return "<div class='sprite_48 {$what}'></div>";
		}

		/**
		 * How to tweak settings text.
		 *
		 * @return string
		 */
		private function create_string_how_to_tweak_settings() {
			return
			'<h4>' . __( 'Tweak The Settings', 'store-locator-le' ) . '</h4>' .

			'<p>' .

			sprintf(
				__( 'You can modify basic settings such as the options shown on the radius pull down list on the <a href="%s">Experience</a> page. ', 'store-locator-le' ),
				admin_url() . 'admin.php?page=slp_experience'
			) .

			sprintf(
				__( 'Even more settings are available via <a href="%s" target="slplus">the premium add-on packs</a>. ', 'store-locator-le' ),
				$this->slplus->slp_store_url
			) .

			'</p>'
			;
		}

		/**
		 * Create the YouTube iFrame and div.
		 *
		 * @return string
		 */
		private function create_string_how_to_video() {
			return

				'<div style="text-align:center; margin: 0px auto;">'.
				'<iframe width="560" height="315" src="https://www.youtube.com/embed/cSybeFLIkM0?list=PLP4RKgpdF-0eahOHMUuLqTfg8EjCQ58Wp" frameborder="0" allowfullscreen></iframe>' .
				'</div>';
		}

		/**
		 * Render the How To Use text.
		 *
		 * @return string
		 */
		private function createstring_HowToUse() {
			$this->slplus->createobject_AddOnManager();

			$html =
				'<div class="left_side">' .
					'<div class="content_pad">'.
					$this->create_string_how_to_add_location() .
					$this->create_string_how_to_create_page() .
					$this->create_string_how_to_tweak_settings() .
					$this->create_string_how_to_video() .
					apply_filters( 'slp_how_to_use' , '' ) .
					'</div>' .
				'</div>' .
				'<div class="right_side">' .
					'<div class="content_pad">'.
					$this->create_string_news_feed() .
					$this->create_string_social_outlets() .
					'</div>' .
				'</div>'
				;

			return $html;
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

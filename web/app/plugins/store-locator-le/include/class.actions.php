<?php
if ( ! class_exists('SLPlus_Actions') ) {

	/**
	 * Store Locator Plus action hooks.
	 *
	 * The methods in here are normally called from an action hook that is
	 * called via the WordPress action stack.
	 *
	 * @package   StoreLocatorPlus\Actions
	 * @author    Lance Cleveland <lance@charlestonsw.com>
	 * @copyright 2012-2015 Charleston Software Associates, LLC
	 */
	class SLPlus_Actions extends SLPlus_BaseClass_Object {

		/**
		 * @param array $options
		 */
		function __construct( $options = array() ) {

			parent::__construct( $options );

			// In order called
			//
			add_action('init'               , array($this,'init'                    ) , 11  );

			add_action( "load-post.php"     , array( $this, 'action_AddToPageHelp'  ) , 20  );
			add_action( "load-post-new.php" , array( $this, 'action_AddToPageHelp'  ) , 20  );

			add_action('wp_head'            , array($this,'wp_head'                 )       ); // UI

			add_action('wp_footer'          , array($this,'wp_footer'               )       ); // UI

			add_action('shutdown'           , array($this,'shutdown'                )       ); // BOTH

			add_action('dmp_addpanel'       , array($this,'create_DMPPanels'        )       );


		}

		/**
		 * Add SLP setting to the admin bar on the top of the WordPress site.
		 *
		 * @param $admin_bar
		 */
		function add_slp_to_admin_bar( $admin_bar ) {
            if( ! current_user_can( 'manage_slp_admin' ) ) { return; }

            $args = array(
				'parent' => 'site-name',
				'id'     => 'store-locator-plus',
				'title'  => $this->slplus->name,
				'href'   => esc_url( admin_url( 'admin.php?page=slp_info' ) ),
				'meta'   => false
			);
			$admin_bar->add_node( $args );
		}

		/**
		 * Attach and instantiated AdminUI object to the main plugin object.
		 *
		 * @return boolean - true unless the main plugin is not found
		 */
		private function create_object_AdminUI() {
			if ( ! isset( $this->slplus->AdminUI ) || ! is_object( $this->slplus->AdminUI ) ) {
				require_once( SLPLUS_PLUGINDIR . 'include/class.adminui.php' );
				$this->slplus->AdminUI = new SLPlus_AdminUI();     // Lets invoke this and make it an object
			}

			return true;
		}

		/**
		 * Add content tab help to the post and post-new pages.
		 */
		function action_AddToPageHelp() {
			get_current_screen()->add_help_tab(
				array(
					'id'      => 'slp_help_tab',
					'title'   => __( 'SLP Hints', 'store-locator-le' ),
					'content' =>
						'<p>' .
						sprintf(
							__( 'Check the <a href="%s" target="slp">Store Locator Plus documentation</a> online.<br/>', 'store-locator-le' ),
							$this->slplus->support_url
							) .
						sprintf(
							__( 'View the <a href="%s" target="csa">[slplus] shortcode documentation</a>.', 'store-locator-le' ),
							$this->slplus->support_url . 'shortcodes/'
							) .
						'</p>'

				)
			);
		}

		/**
		 * Add the Store Locator panel to the admin sidebar.
		 *
		 * Roles and Caps
		 * manage_slp_admin
		 * manage_slp_user
		 *
		 * WordPress Store Locator Plus Menu Roles & Caps
		 *
		 * Info : manage_slp_admin
		 * Locations: manage_slp_user
		 * Experience: manage_slp_admin
		 * General: manage_slp_admin
		 *
		 */
		function admin_menu() {
			$this->create_object_AdminUI();
			do_action( 'slp_admin_menu_starting' );

			// The main hook for the menu
			//
			add_menu_page(
				$this->slplus->name,
				$this->slplus->name,
				'manage_slp',
				SLPLUS_PREFIX,
				array( $this->slplus->AdminUI, 'renderPage_GeneralSettings' ),
				SLPLUS_PLUGINURL . '/images/icon_from_jpg_16x16.png'
			);

			// Default menu items
			//
			$force_load_indicator = $this->slplus->javascript_is_forced ? '*' : '';
			$menuItems            = array(
				array(
					'label'    => __( 'Info', 'store-locator-le' ),
					'slug'     => 'slp_info',
					'class'    => $this->slplus->AdminUI,
					'function' => 'render_info_tab'
				),
				array(
					'label'    => __( 'Locations', 'store-locator-le' ),
					'slug'     => 'slp_manage_locations',
					'class'    => $this->slplus->AdminUI,
					'function' => 'renderPage_Locations'
				),
				array(
					'label'    => __( 'Experience', 'store-locator-le' ),
					'slug'     => 'slp_experience',
					'class'    => $this->slplus->AdminUI,
					'function' => 'render_experience_tab'
				),
				array(
					'label'    => __( 'General', 'store-locator-le' ) . $force_load_indicator,
					'slug'     => 'slp_general',
					'class'    => $this->slplus->AdminUI,
					'function' => 'renderPage_GeneralSettings'
				),
			);

			// Third party plugin add-ons
			//
			$menuItems = apply_filters( 'slp_menu_items', $menuItems );

			// Attach Menu Items To Sidebar and Top Nav
			//
			foreach ( $menuItems as $menuItem ) {

				// Sidebar connect...
				//
				// Differentiate capability for User Managed Locations
				if ( $menuItem['label'] == __( 'Locations', 'store-locator-le' ) ) {
					$slpCapability = 'manage_slp_user';
				} else {
					$slpCapability = 'manage_slp_admin';
				}

				// Using class names (or objects)
				//
				if ( isset( $menuItem['class'] ) ) {
					add_submenu_page(
						SLPLUS_PREFIX,
						$menuItem['label'],
						$menuItem['label'],
						$slpCapability,
						$menuItem['slug'],
						array( $menuItem['class'], $menuItem['function'] )
					);

					// Full URL or plain function name
					//
				} else {
					add_submenu_page(
						SLPLUS_PREFIX,
						$menuItem['label'],
						$menuItem['label'],
						$slpCapability,
						$menuItem['url']
					);
				}
			}

			// Remove the duplicate menu entry
			//
			remove_submenu_page( SLPLUS_PREFIX, SLPLUS_PREFIX );
		}


		/**
		 * Create a Map Settings Debug My Plugin panel.
		 *
		 * @return null
		 */
		function create_DMPPanels() {
			if ( ! isset( $GLOBALS['DebugMyPlugin'] ) ) {
				return;
			}
			if ( class_exists( 'DMPPanelSLPMain' ) == false ) {
				require_once( SLPLUS_PLUGINDIR . 'include/class.dmppanels.php' );
			}
			$GLOBALS['DebugMyPlugin']->panels['slp.main']        = new DMPPanelSLPMain();
			$GLOBALS['DebugMyPlugin']->panels['slp.location']    = new DMPPanelSLPMapLocation();
			$GLOBALS['DebugMyPlugin']->panels['slp.mapsettings'] = new DMPPanelSLPMapSettings();
			$GLOBALS['DebugMyPlugin']->panels['slp.managelocs']  = new DMPPanelSLPManageLocations();
		}

		/**
		 * Called when the WordPress init action is processed.
		 *
		 * Current user is authenticated by this time.
		 */
		function init() {

			// Fire the SLP init starting trigger
			//
			do_action( 'slp_init_starting', $this );

			// Do not texturize our shortcodes
			//
			add_filter( 'no_texturize_shortcodes', array( 'SLPlus_UI', 'no_texturize_shortcodes' ) );

			/**
			 * Register the store taxonomy & page type.
			 *
			 * This is used in multiple add-on packs.
			 *
			 */
			if ( ! taxonomy_exists( 'stores' ) ) {
				// Store Page Labels
				//
				$storepage_labels =
					apply_filters(
						'slp_storepage_labels',
						array(
							'name'          => __( 'Store Pages', 'store-locator-le' ),
							'singular_name' => __( 'Store Page', 'store-locator-le' ),
							'add_new'       => __( 'Add New Store Page', 'store-locator-le' ),
						)
					);

				$storepage_features =
					apply_filters(
						'slp_storepage_features',
						array(
							'title',
							'editor',
							'author',
							'excerpt',
							'trackback',
							'thumbnail',
							'comments',
							'revisions',
							'custom-fields',
							'page-attributes',
							'post-formats'
						)
					);

				$storepage_attributes =
					apply_filters(
						'slp_storepage_attributes',
						array(
							'labels'          => $storepage_labels,
							'public'          => false,
							'has_archive'     => true,
							'description'     => __( 'Store Locator Plus location pages.', 'store-locator-le' ),
							'menu_postion'    => 20,
							'menu_icon'       => SLPLUS_PLUGINURL . '/images/icon_from_jpg_16x16.png',
							'show_in_menu'    => current_user_can( 'manage_slp_admin' ),
							'capability_type' => 'page',
							'supports'        => $storepage_features,
						)
					);

				// Register Store Pages Custom Type
				register_post_type( SLPlus::locationPostType, $storepage_attributes );

				register_taxonomy(
					SLPLus::locationTaxonomy,
					SLPLus::locationPostType,
					array(
						'hierarchical' => true,
						'labels'       =>
							array(
								'menu_name' => __( 'Categories', 'store-locator-le' ),
								'name'      => __( 'Store Categories', 'store-locator-le' ),
							),
						'capabilities' =>
							array(
								'manage_terms' => 'manage_slp_admin',
								'edit_terms'   => 'manage_slp_admin',
								'delete_terms' => 'manage_slp_admin',
								'assign_terms' => 'manage_slp_admin',
							)
					)
				);
			}

			// Fire the SLP initialized trigger
			//
			add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );

			// HOOK: slp_init_complete
			//
			do_action( 'slp_init_complete' );

			// TODO: remove when references to addons['slp.AjaxHandler']->plugin are replaced with slplus->AjaxHandler (GFI)
			$this->slplus->addons['slp.AjaxHandler'] = $this->slplus->AjaxHandler;

			//  If the current user can manage_slp (roles & caps), add these admin hooks.
			//
			if ( current_user_can( 'manage_slp' ) ) {
				add_action('admin_menu'		, array( $this , 'admin_menu'			)		); 	// ADMIN
				add_action('admin_bar_menu'	, array( $this , 'add_slp_to_admin_bar'	) , 999	); 	// ADMIN
			}

            // If current user can update plugins, hook in the updates system.
            //
            if ( current_user_can( 'update_plugins' ) ) {
                require_once(SLPLUS_PLUGINDIR . 'include/class.updates.php');
            }
		}

		/**
		 * This is called whenever the WordPress wp_enqueue_scripts action is called.
		 */
		function wp_enqueue_scripts() {

			//------------------------
			// Register our scripts for later enqueue when needed
			//
			if ( ! $this->slplus->is_CheckTrue( get_option( SLPLUS_PREFIX . '-no_google_js', '0' ) ) ) {
				$this->slplus->enqueue_google_maps_script();
			}

			$sslURL =
				( is_ssl() ?
					preg_replace( '/http:/', 'https:', SLPLUS_PLUGINURL ) :
					SLPLUS_PLUGINURL
				);


			// Force load?  Enqueue and localize.
			//
			if ( $this->slplus->javascript_is_forced ) {
				wp_enqueue_script(
					'csl_script',
					$sslURL . '/js/slp.js',
					array( 'jquery' ),
					SLPLUS_VERSION,
					! $this->slplus->javascript_is_forced
				);
				$this->slplus->UI->localize_script();
				$this->slplus->UI->setup_stylesheet_for_slplus();

				// No force load?  Register only.
				// Localize happens when rendering a shortcode.
				//
			} else {
				wp_register_script(
					'csl_script',
					$sslURL . '/js/slp.js',
					array( 'jquery' ),
					SLPLUS_VERSION,
					! $this->slplus->javascript_is_forced
				);
			}
		}


		/**
		 * This is called whenever the WordPress shutdown action is called.
		 */
		function wp_footer() {
			SLPlus_Actions::ManageTheScripts();
		}


		/**
		 * Called when the <head> tags are rendered.
		 */
		function wp_head() {
			if ( ! isset( $this->slplus ) ) {
				return;
			}


			echo '<!-- SLP Custom CSS -->' . "\n" . '<style type="text/css">' . "\n" .

			     // Map
			     "div#map.slp_map {\n" .
			     "width:{$this->slplus->options_nojs['map_width']}{$this->slplus->options_nojs['map_width_units']};\n" .
			     "height:{$this->slplus->options_nojs['map_height']}{$this->slplus->options_nojs['map_height_units']};\n" .
			     "}\n" .

			     // Tagline
			     "div#slp_tagline {\n" .
			     "width:{$this->slplus->options_nojs['map_width']}{$this->slplus->options_nojs['map_width_units']};\n" .
			     "}\n" .

			     // FILTER: slp_ui_headers
			     //
			     apply_filters( 'slp_ui_headers', '' ) .

			     '</style>' . "\n\n";
		}

		/**
		 * This is called whenever the WordPress shutdown action is called.
		 */
		function shutdown() {
			SLPlus_Actions::ManageTheScripts();
		}

		/**
		 * Unload The SLP Scripts If No Shortcode
		 */
		function ManageTheScripts() {
			if ( ! defined( 'SLPLUS_SCRIPTS_MANAGED' ) || ! SLPLUS_SCRIPTS_MANAGED ) {

				// If no shortcode rendered, remove scripts
				//
				if ( ! defined( 'SLPLUS_SHORTCODE_RENDERED' ) || ! SLPLUS_SHORTCODE_RENDERED ) {
					wp_dequeue_script( 'google_maps' );
					wp_deregister_script( 'google_maps' );
					wp_dequeue_script( 'csl_script' );
					wp_deregister_script( 'csl_script' );
				}
				define( 'SLPLUS_SCRIPTS_MANAGED', true );
			}
		}


		//------------------------------------------------------------------------
		// DEPRECATED
		//------------------------------------------------------------------------

		/**
		 * @deprecated 4.0.00
		 */
		function getCompoundOption( $a = null, $b = null, $c = null, $d = null, $e = null, $f = null ) {
			$this->slplus->helper->create_string_wp_setting_error_box( $this->slplus->createstring_Deprecated( __FUNCTION__ ) );
		}

	}
}

// These dogs are loaded up way before this class is instantiated.
//
add_action("load-post",array('SLPlus_Actions','init'));
add_action("load-post-new",array('SLPlus_Actions','init'));


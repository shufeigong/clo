<?php

if ( ! class_exists('SLPlus_AdminUI') ) {

	/**
	 * Store Locator Plus basic admin user interface.
	 *
	 * @package StoreLocatorPlus\AdminUI
	 * @author Lance Cleveland <lance@charlestonsw.com>
	 * @copyright 2012-2015 Charleston Software Associates, LLC
	 *
	 * @property-read   boolean     $already_enqueue    True if admin stylesheet enqueued.
	 * @property-read   boolean     $isOurAdminPage     True if we are on an admin page for the plugin.
	 * @property       string[]     $admin_slugs        The registered admin page hooks for the plugin.
	 * @property SLPlus_AdminUI_UserExperience $Experience
	 * @property SLPlus_AdminUI_GeneralSettings $GeneralSettings
	 * @property-read SLPlus_AdminUI_Info $Info The Info object.
	 * @property SLPlus_AdminUI_Locations $ManageLocations
	 * @property SLPlus_AdminUI_UserExperience $MapSettings
	 * @property string $styleHandle
	 */
	class SLPlus_AdminUI extends SLPlus_BaseClass_Object {
		private $already_enqueued   = false;
		public 	$Experience;
		public 	$GeneralSettings;
		private $Info;
		private $is_our_admin_page  = false;
	    public 	$ManageLocations;
	    public 	$MapSettings;
		public  $slp_admin_slugs    = array();
	    public 	$styleHandle;

	    /**
	     * Invoke the AdminUI class.
		 *
		 * @param	array	$options
	     *
	     */
	    function __construct( $options = array() ) {
		    add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_stylesheet' ) );

		    parent::__construct( $options );

	        $this->styleHandle = $this->slplus->styleHandle;
			$this->add_janitor_hooks();

            // Called after admin_menu and admin_init when the current screen info is available.
            //
            add_action( 'current_screen' , array ( $this , 'setup_admin_screen' ) );

		    /**
		     * HOOK: slp_admin_init_complete
		     */
		    do_action( 'slp_admin_init_complete' );
	    }

		/**
		 * Add filters to save/restore important settings for the Janitor reset.
		 */
		private function add_janitor_hooks() {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			if ( !function_exists('is_plugin_active') ||  !is_plugin_active( 'slp-janitor/slp-janitor.php')) {
				return;
			}
			add_filter( 'slp_janitor_important_settings' , array( $this , 'set_janitor_important_fields' )  );
			add_action( 'slp_janitor_restore_important_setting' , array( $this , 'restore_janitor_important_fields' ) , 5 , 2 );
		}


		/**
		 * Sets $this->isOurAdminPage true if we are on a SLP managed admin page.  Returns true/false accordingly.
		 *
		 * @param string $hook
		 * @return boolean
		 */
		function is_our_admin_page( $hook ) {
			if ( ! is_admin() ) {
				$this->is_our_admin_page = false;
				return false;
			}

			// Our Admin Page : true if we are on the admin page for this plugin
			// or we are processing the update action sent from this page
			//
			$this->is_our_admin_page = (
				( $hook == SLPLUS_PREFIX . '-options') ||
				( $hook === 'slp_info' )
			);
			if ($this->is_our_admin_page) {
				return true;
			}


			// Request Action is "update" on option page
			//
			$this->is_our_admin_page = isset($_REQUEST['action']) &&
			                        ($_REQUEST['action'] === 'update') &&
			                        isset($_REQUEST['option_page']) &&
			                        (substr($_REQUEST['option_page'], 0, strlen(SLPLUS_PREFIX)) === SLPLUS_PREFIX)
			;
			if ($this->is_our_admin_page) {
				return true;
			}

			// This test allows for direct calling of the options page from an
			// admin page call direct from the sidebar using a class/method
			// operation.
			//
			// To use: pass an array of strings that are valid admin page slugs for
			// this plugin.  You can also pass a single string, we catch that too.
			//
			$this->set_admin_slugs();
			foreach ($this->slp_admin_slugs as $admin_slug) {
				$this->is_our_admin_page = ( $hook === $admin_slug);
				if ($this->is_our_admin_page) {
					return true;
				}
			}

			return $this->is_our_admin_page;
		}

		/**
		 * Set the admin slugs.
		 */
		public function set_admin_slugs() {
			$this->slp_admin_slugs = array(
				'slp_general'                          ,
				'settings_page_csl-slplus-options'              ,
				'slp_general'  ,
				SLP_ADMIN_PAGEPRE . 'slp_general'  ,
				'slp_info'              ,
				SLP_ADMIN_PAGEPRE . 'slp_info'              ,
				'slp_manage_locations'  ,
				SLP_ADMIN_PAGEPRE . 'slp_manage_locations'  ,
				'slp_experience'      ,
				SLP_ADMIN_PAGEPRE . 'slp_experience'      ,
			);
			$this->slp_admin_slugs = (array) apply_filters('wpcsl_admin_slugs', $this->slp_admin_slugs);
		}

		/**
		 * Make options_nojs a setting we want to process during janitor reset settings.
		 *
		 * @param $field_array
		 *
		 * @return array
		 */
		public function set_janitor_important_fields( $field_array ) {
			return array_merge( $field_array , array( 'csl-slplus-options_nojs' ) );
		}

		/**
		 * @param $option_name
		 * @param $saved_setting
		 */
		public function restore_janitor_important_fields( $option_name , $saved_setting ) {
			if ( $option_name === 'csl-slplus-options_nojs' ) {
				$this->slplus->options_nojs = get_option( $option_name );
				$this->slplus->options_nojs['next_field_id' ]     = $saved_setting['next_field_id'];
				$this->slplus->options_nojs['next_field_ported' ] = $saved_setting['next_field_ported'];
				update_option( $option_name , $this->slplus->options_nojs );
			}
		}

	    /**
	     * Build a query string of the add-on packages.
	     *
	     * @return string
	     */
	    public function create_addon_query() {
	        $addon_slugs = array_keys( $this->slplus->add_ons->instances );
	        $addon_versions = array();
	        foreach ($addon_slugs as $addon_slug) {
	            if (is_object($this->slplus->add_ons->instances[$addon_slug])) {
	                $addon_versions[$addon_slug.'_version'] = $this->slplus->add_ons->instances[$addon_slug]->options['installed_version'];
	            }
	        }
	        return
	            http_build_query($addon_slugs,'addon_') . '&' .
	            http_build_query($addon_versions) ;
	    }

	    /**
	     * Render the admin page navbar (tabs)
	     *
	     * @global mixed[] $submenu the WordPress Submenu array
	     * @param boolean $addWrap add a wrap div
	     * @return string
	     */
	    function create_Navbar($addWrap = false) {
	        global $submenu;
	        if (!isset($submenu[SLPLUS_PREFIX]) || !is_array($submenu[SLPLUS_PREFIX])) {
	            echo apply_filters('slp_navbar','');
	        } else {
	            $content =
	                ($addWrap?"<div id='wpcsl-option-navbar_wrapper'>":'').
	                '<div id="slplus_navbar">' .
	                    '<div class="wrap"><h2 class="nav-tab-wrapper">';

	            // Loop through all SLP sidebar menu items on admin page
	            //
	            foreach ($submenu[SLPLUS_PREFIX] as $slp_menu_item) {

	                // Create top menu item
	                //
	                $selectedTab = ((isset($_REQUEST['page']) && ($_REQUEST['page'] === $slp_menu_item[2])) ? ' nav-tab-active' : '' );
	                $content .= apply_filters(
	                        'slp_navbar_item_tweak',
	                        '<a class="nav-tab'.$selectedTab.'" href="'.menu_page_url( $slp_menu_item[2], false ).'">'.
	                            $slp_menu_item[0].
	                        '</a>'
	                        );
	            }
	            $content .= apply_filters('slp_navbar_item','');
	            $content .='</h2></div></div>'.($addWrap?'</div>':'');
	            return apply_filters('slp_navbar',$content);
	        }
	    }

		/**
		 * Create Experience object.
		 */
		private function create_object_Experience() {
			if ( ! isset( $this->Experience ) ) {
				require_once( SLPLUS_PLUGINDIR . 'include/class.adminui.experience.php' );
				$this->Experience = new SLPlus_AdminUI_UserExperience();

				// TODO: remove MapSettings when all add-on packs start referencing AdminUI->Experience ( ES, GFI, PRO, REX )
				$this->MapSettings = $this->Experience;
			}
		}

        /**
         * Create General object.
         */
        private function create_object_General() {
            if ( ! isset( $this->GeneralSettings ) ) {
                require_once(SLPLUS_PLUGINDIR . 'include/class.adminui.generalsettings.php');
                $this->GeneralSettings = new SLPlus_AdminUI_GeneralSettings();
            }
        }

		/**
		 * Create Info object.
		 */
		private function create_object_Info() {
			if ( ! isset( $this->Info ) ) {
				require_once( SLPLUS_PLUGINDIR . 'include/class.adminui.info.php' );
				$this->Info = new SLPlus_AdminUI_Info( array( 'adminui'   => $this ) );
			}
		}

		/**
		 * Create Locations object.
		 */
		private function create_Object_Locations() {
			if ( ! isset( $this->ManageLocations ) ) {
				require_once( SLPLUS_PLUGINDIR . 'include/class.adminui.locations.php' );
				$this->ManageLocations = new SLPlus_AdminUI_Locations();
			}
		}

	    /**
	     * Return the icon selector HTML for the icon images in saved markers and default icon directories.
	     *
	     * @param string|null $inputFieldID
	     * @param string|null $inputImageID
	     * @return string
	     */
	     function CreateIconSelector($inputFieldID = null, $inputImageID = null) {
	        if (($inputFieldID == null) || ($inputImageID == null)) { return ''; }


	        $htmlStr = '';
	        $files=array();
	        $fqURL=array();


	        // If we already got a list of icons and URLS, just use those
	        //
	        if (
	            isset($this->slplus->data['iconselector_files']) &&
	            isset($this->slplus->data['iconselector_urls'] )
	           ) {
	            $files = $this->slplus->data['iconselector_files'];
	            $fqURL = $this->slplus->data['iconselector_urls'];

	        // If not, build the icon info but remember it for later
	        // this helps cut down looping directory info twice (time consuming)
	        // for things like home and end icon processing.
	        //
	        } else {

	            // Load the file list from our directories
	            //
	            // using the same array for all allows us to collapse files by
	            // same name, last directory in is highest precedence.
	            $iconAssets = apply_filters('slp_icon_directories',
	                    array(
	                            array('dir'=>SLPLUS_UPLOADDIR.'saved-icons/',
	                                  'url'=>SLPLUS_UPLOADURL.'saved-icons/'
	                                 ),
	                            array('dir'=>SLPLUS_ICONDIR,
	                                  'url'=>SLPLUS_ICONURL
	                                 )
	                        )
	                    );
	            $fqURLIndex = 0;
	            foreach ($iconAssets as $icon) {
	                if (is_dir($icon['dir'])) {
	                    if ($iconDir=opendir($icon['dir'])) {
	                        $fqURL[] = $icon['url'];
	                        while ($filename = readdir($iconDir)) {
	                            if (strpos($filename,'.')===0) { continue; }
	                            $files[$filename] = $fqURLIndex;
	                        };
	                        closedir($iconDir);
	                        $fqURLIndex++;
	                    } else {
	                        $this->slplus->notifications->add_notice(
	                                9,
	                                sprintf(
	                                        __('Could not read icon directory %s','store-locator-le'),
	                                        $icon['dir']
	                                        )
	                                );
	                         $this->slplus->notifications->display();
	                    }
	               }
	            }
	            ksort($files);
	            $this->slplus->data['iconselector_files'] = $files;
	            $this->slplus->data['iconselector_urls']  = $fqURL;
	        }

	        // Build our icon array now that we have a full file list.
	        //
	        foreach ($files as $filename => $fqURLIndex) {
	            if (
	                (preg_match('/\.(png|gif|jpg)/i', $filename) > 0) &&
	                (preg_match('/shadow\.(png|gif|jpg)/i', $filename) <= 0)
	                ) {
	                $htmlStr .=
	                    "<div class='slp_icon_selector_box'>".
	                        "<img
	                         	 data-filename='$filename'
	                        	 class='slp_icon_selector'
	                             src='".$fqURL[$fqURLIndex].$filename."'
	                             onclick='".
	                                "document.getElementById(\"".$inputFieldID."\").value=this.src;".
	                                "document.getElementById(\"".$inputImageID."\").src=this.src;".
	                             "'>".
	                     "</div>"
	                     ;
	            }
	        }

	        // Wrap it in a div
	        //
	        if ($htmlStr != '') {
	            $htmlStr = '<div id="'.$inputFieldID.'_icon_row" class="slp_icon_row">'.$htmlStr.'</div>';

	        }


	        return $htmlStr;
	     }

		/**
		 * Enqueue the admin stylesheet when needed.
		 *
		 * @param string $hook Current page hook.
		 */
		function enqueue_admin_stylesheet( $hook ) {
			if ( ! $this->is_our_admin_page( $hook ) || $this->already_enqueued ) { return; }

			// The CSS file must exists where we expect it and
			// The admin page being rendered must be in "our family" of admin pages
			//
			if ( file_exists( SLPLUS_PLUGINDIR . 'css/admin/admin.css') ) {
				$this->already_enqueued = true;
				wp_enqueue_style('slp_admin_style' , SLPLUS_PLUGINURL . '/css/admin/admin.css' );

				// jQuery Smoothness Theme
				//
				if (file_exists(SLPLUS_PLUGINDIR . 'css/admin/jquery-ui-smoothness.css')) {
					wp_enqueue_style(
						'jquery-ui-smoothness', SLPLUS_PLUGINURL . '/css/admin/jquery-ui-smoothness.css'
					);
				}

				if (file_exists(SLPLUS_PLUGINDIR . 'js/admin-interface.js')) {
					wp_enqueue_script( 'slp_admin_script' , SLPLUS_PLUGINURL . '/js/admin-interface.js', 'jquery', SLPLUS_VERSION, true );
				}
			}

			wp_enqueue_script('jquery-ui-dialog');
		}

		/**
		 * Render the experience tab.
		 */
		function render_experience_tab() {
			$this->Experience->display();
		}

		/**
		 * Render the Info page.
		 *
		 */
		function render_info_tab() {
			$this->Info->display();
		}

		/**
		 * Render the rating box.
		 */
		function render_rate_box() {
			$rating_url = 'https://wordpress.org/support/view/plugin-reviews/store-locator-le?filter=5#postform';
			print
				'<div class="box note">'.
				sprintf(
					__('If you like <strong>Store Locator Plus</strong> please leave me a <a target="_blank" href="%s" title="Rate Store Locator Plus on WordPress">★★★★★</a> rating ' , 'store-locator-le'),
					$rating_url
				).
				sprintf(
					__('on <a target="_blank" href="%s" title="Rate Store Locator Plus on WordPress">WordPress.org</a>. ' , 'store-locator-le'),
					$rating_url
				).
				'<br/>' .
				__('A huge thank you from Lance and his fellow code geeks!','store-locator-le') .
				'</div>'
			;
		}

		/**
		 * Render the General Settings page.
		 *
		 */
		function renderPage_GeneralSettings() {
			$this->GeneralSettings->render_adminpage();
			$this->render_rate_box();
		}

		/**
		 * Render the Locations admin page.
		 */
		function renderPage_Locations() {
			$this->slplus->set_php_timeout();
			$this->ManageLocations->render_adminpage();
		}

        /**
         * Attach the wanted screen object and save the settings if appropriate.
         *
         * @param   WP_Screen       $current_screen         The current screen object.
         */
        function setup_admin_screen( $current_screen ) {
            switch ( $current_screen->id ) {

                // Experience Tab
                //
                case SLP_ADMIN_PAGEPRE . 'slp_experience':
                    $this->create_object_Experience();
                    if ( isset( $_POST ) && ! empty( $_POST ) ) {
                        $this->Experience->save_options();
                    }
                    break;

                // General Tab
                //
                case SLP_ADMIN_PAGEPRE . 'slp_general':
                    $this->create_object_General();
                    if ( isset( $_POST ) && ! empty( $_POST ) ) {
                        $this->GeneralSettings->save_options();
                    }
                    break;

                // Info Tab
                //
                case SLP_ADMIN_PAGEPRE . 'slp_info':
                    $this->create_object_Info();
                    break;

                // Locations Tab
                case SLP_ADMIN_PAGEPRE . 'slp_manage_locations':
                    $this->create_Object_Locations();
                    break;

                // Unknown
                //
                default:
                    break;
            }
        }

	     /**
	      * Merge existing options and POST options, then save to the wp_options table.
	      *
	      * Typically used to merge post options from admin interface changes with
	      * existing options in a class.
	      *
	      * @param string $optionName name of option to update
	      * @param mixed[] $currentOptions current options as a named array
	      * @param string[] $cbOptionArray array of options that are checkboxes
	      * @return mixed[] the updated options
	      */
	     function save_SerializedOption($optionName,$currentOptions,$cbOptionArray=null) {

	        // If we did not send in a checkbox Array
	        // AND there are not post options
	        // get the heck out of here...
	        //
	        if (
	            ( $cbOptionArray === null ) &&
	            ! isset( $_POST[$optionName] )
	        ) {
	            return $currentOptions;
	        }


	        // Set a blank array if the post option name is not set
	        // We can only get here with a blank post[optionname] if
	        // we are given a cbOptionArray to process
	        //
	        $optionValue =
	            ( isset( $_POST[$optionName] ) ) ?
	                $_POST[$optionName]          :
	                array()                      ;

	        // Checkbox Pre-processor
	        //
	        if ( $cbOptionArray !== null ){
	            foreach ( $cbOptionArray as $cbname ) {
	                if ( ! isset( $optionValue[$cbname] ) ) {
	                    $optionValue[$cbname] = '0';
	                }
	            }
	        }

	        // Merge new options from POST with existing options
	        //
	        $optionValue = stripslashes_deep(array_merge($currentOptions,$optionValue));

	        // Make persistent, write back to the wp_options table
	        // Only write if something has changed.
	        //
	        if ($currentOptions != $optionValue) {
	            update_option($optionName,$optionValue);
	        }

	        // Send back the updated options
	        //
	        return $optionValue;
	     }

	     //------------------------------------------------------------------------
	     // DEPRECATED
	     //------------------------------------------------------------------------

		/**
		 * @deprecated 4.0.00
		 */
		function create_InputElement( $a = null, $b = null, $c = null, $d = null, $e = null, $f = null ) {
			$this->slplus->helper->create_string_wp_setting_error_box( $this->slplus->createstring_Deprecated( __FUNCTION__ ) );
		}
	}
}
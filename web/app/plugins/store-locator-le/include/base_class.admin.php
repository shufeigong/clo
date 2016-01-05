<?php
if (! class_exists('SLP_BaseClass_Admin')) {
	require_once( SLPLUS_PLUGINDIR . 'include/class.settings.php');

    /**
     * A base class that helps add-on packs separate admin functionality.
     *
     * Add on packs should include and extend this class.
     *
     * This allows the main plugin to only include this file in admin mode
     * via the admin_menu call.   Reduces the front-end footprint.
     *
     * @property        SLP_BaseClass_Addon     $addon
     * @property        string[]                $admin_checkboxes   	The expected checkboxes on each admin tab.
	 * @property		string					$admin_checkbox_page	Where do the admin checkboxes live? Which tab (admin page)?
     * @property        string                  $admin_page_slug    	The slug for the admin page.
     * @property        null|string[]           $js_pages           	Which pages do we put the admin JS on?
     * @property        string[]                $js_requirements    	An array of the JavaScript hooks that are needed by the userinterface.js script.
     * @property        string[]                $js_settings        	JavaScript settings that are to be localized as a <slug>_settings JS variable.
     * @property        SLPlus                  $slplus
     *
     * TODO: Add a method that invokes a base_class.admin.slp_page.php class for methods used only if on a SLP admin page (test admin_slugs)
     *
     * @package StoreLocatorPlus\BaseClass\Admin
     * @author Lance Cleveland <lance@charlestonsw.com>
     * @copyright 2013 - 2015 Charleston Software Associates, LLC
     */
    class SLP_BaseClass_Admin extends SLPlus_BaseClass_Object {
        protected $addon;
        protected $admin_checkboxes     = array();
		protected $admin_checkbox_page  = 'slp_experience';
        protected $admin_page_slug;
	    protected $js_pages             = null;
	    protected $js_requirements      = array();
	    protected $js_settings          = array();

        /**
         * Instantiate the admin panel object.
         *
         * @param array $options
         */
        function __construct( $options = array() ) {
	        parent::__construct( $options );

	        if ( !isset( $this->admin_page_slug ) || empty( $this->admin_page_slug ) ) {
		        $this->admin_page_slug = $this->addon->short_slug;
	        }
            
            $this->set_addon_properties();
            $this->do_admin_startup();
            $this->add_hooks_and_filters();
        }

        /**
         * Add the plugin specific hooks and filter configurations here.
         *
         * Add your hooks and filters in the class that extends this base class.
         * Then call parent::add_hooks_and_filters();
         *
         * Should include WordPress and SLP specific hooks and filters.
         */
        function add_hooks_and_filters() {
	        add_action( 'admin_enqueue_scripts' , array( $this , 'enqueue_admin_javascript' ) );
	        add_action( 'admin_enqueue_scripts' , array( $this , 'enqueue_admin_css'        ) );


	        // TODO: Remove this action hook when all add-on packs use this classes default do_admin_startup() in place of their own. (CEX,ELM,GFI,GFL,LEX,MM,PAGES,REX,SME,TAG,UML,W)
            add_action('slp_save_ux_settings' ,array( $this ,'save_my_settings' ) );
        }
        
        /**
         * Check for updates of active add on packs.
         */
        function check_for_updates() {
            if ( is_plugin_active( $this->addon->slug ) ) {
                if ( ! class_exists( 'SLPlus_Updates' ) ) {
                    require_once('class.updates.php');
                }
                if ( class_exists('SLPlus_Updates') ) {
                    $this->Updates = new SLPlus_Updates(
	                        $this->get_addon_version(),
                            $this->addon->slug
                    );
                }
            }
        }        
        
        /**
         * Things we want our add on packs to do when they start.
         *
         * Extend this by overriding this method and then calling parent::do_admin_startup()
         * before or after your extension.
         */
        function do_admin_startup() {
            $this->check_for_updates();
            $this->update_install_info();

			// Only save settings if the update action is set.
			if (
				! empty( $_POST ) &&
				isset($_REQUEST['action']) && ( $_REQUEST['action'] === 'update' ) &&
				isset($_REQUEST['page']  ) && ( $_REQUEST['page']   === $this->admin_checkbox_page )
			) {
				$this->save_my_settings();
			}
        }

	    /**
	     * If the file admin.css exists and the page prefix starts with slp_ , enqueue the admin style.
	     */
	    function enqueue_admin_css( $hook ) {
		    add_filter( 'wpcsl_admin_slugs'     , array( $this , 'filter_AddOurAdminSlug'   ) );
		    $this->slplus->AdminUI->enqueue_admin_stylesheet( $hook );
		    if (
		        file_exists( $this->addon->dir . 'css/admin.css' ) &&
		        ( strpos( $hook , SLP_ADMIN_PAGEPRE ) !== false )
		    ) {
			    wp_enqueue_style( $this->addon->slug . '_admin_css' , $this->addon->url . '/css/admin.css' );
		    }
	    }

	    /**
	     * If the file admin.js exists, enqueue it.
	     */
	    function enqueue_admin_javascript( $hook ) {
		    if ( ! $this->ok_to_enqueue_admin_js( $hook ) ) { return; }
		    wp_enqueue_script( $this->addon->slug . '_admin' , $this->addon->url . '/include/admin.js' , $this->js_requirements );
		    wp_localize_script( $this->addon->slug . '_admin' ,
			    preg_replace('/\W/' , '' , $this->addon->get_meta('TextDomain') ) . '_settings' ,
			    $this->js_settings
		    );
	    }

	    /**
	     * Get the add-on pack version.
	     *
	     * Required for backward compatibility.
	     *
	     * TODO : eliminate when all add-on packs use SLP 4.3 framework.
	     *
	     * @return string
	     */
		private function get_addon_version() {

			// Add on use the SLP 4.x add-on framework extending base_class.object
			if ( method_exists( $this->addon, 'get_meta' ) ) { return $this->addon->get_meta( 'Version' ); }

			// Defined Version Constant
			$class_name = get_class( $this->addon );
			if ( defined( $class_name.'::VERSION' ) ) {
				$this->retrofit_legacy_addons( $class_name );
				return $this->addon->version;
			}

			return '0.0';
		}


	    /**
	     * Check if it is OK to enqueue the admin JavaScript.
	     *
	     * @param $hook
	     *
	     * @return boolean
	     */
	    function ok_to_enqueue_admin_js( $hook ) {
		    if ( is_null( $this->js_pages ) ) { return false; }
		    if ( ! in_array( $hook , $this->js_pages ) ) { return false; }
		    if ( ! $this->slplus->AdminUI->is_our_admin_page( $hook ) ) { return false; }
		    if ( ! file_exists( $this->addon->dir . 'include/admin.js' ) ) { return false; }
		    return true;
	    }

	    /**
	     * Patch in some needed properties for addons not using the full framework WTF.
	     *
	     * @param $class_name
	     */
	    private function retrofit_legacy_addons( $class_name ) {

		    // version
		    if ( defined( $class_name.'::VERSION' ) ) {
				$this->addon->version = constant( $class_name.'::VERSION' );
		    }

		    // option name
		    if ( ! property_exists( $this->addon , 'option_name' ) ) {

			    if ( isset( $this->addon->settingsSlug ) && ! empty( $this->addon->settingsSlug ) ) {
				    $this->addon->option_name = $this->addon->settingsSlug . '-options';
			    }
			}
	    }

	    /**
	     * Use this to save option settings far earlier in the admin process.
	     *
	     * Necessary if you are going to use your options in localized admin scripts.
	     *
	     * Set $this->admin_checkboxes with all the expected checkbox names then call parent::save_my_settings.
		 *
		 * TODO: Refactor to save_experience_tab_settings
		 *
		 * Note: this is only called from the base clase on the experience tab.
		 * Only those settings associated with the experience tab go here.
		 * This is especially important for saving checkboxes.
	     */
	    function save_my_settings() {
		    array_walk( $_POST ,array( $this->addon ,'set_ValidOptions' ) );

		    $this->options =
			    $this->slplus->AdminUI->save_SerializedOption(
				    $this->addon->option_name,
				    $this->addon->options,
				    $this->admin_checkboxes
			    );

		    // TODO: eliminate when (PAGES) starts using full add-on framework
		    if ( method_exists( $this->addon , 'init_options' ) ) {
			    $this->addon->init_options();
		    }
	    }

        /**
         * Set base class properties so we can have more cross-add-on methods.
         */
        function set_addon_properties() {
            // Replace this with the properties from the parent add-on to set this class properties.
            //
            // $this->admin_page_slug = <class>::ADMIN_PAGE_SLUG
        }

        /**
         * Set valid options according to the addon options array.
         *
         * Use $this->addon->set_ValidOptions instead.
         *
         * @deprecated
         *
         * TODO: deprecate when all add-on packs use ($this->addon , 'set_ValidOptions') instead of $this->set_ValidOptions in admin class.
         *
         * @param $val
         * @param $key
         */
        function set_ValidOptions($val,$key) {
	        $this->addon->set_ValidOptions( $val , $key );
        }
        
        /**
         * Update the install info for this add on.
         */
        function update_install_info() {
            $installed_version =
                isset( $this->addon->options['installed_version'] ) ?
                    $this->addon->options['installed_version']      :
                    '0.0.0'                                         ;

            if ( version_compare( $installed_version , $this->addon->version , '<' ) ) {
                $this->update_prior_installs();
                $this->addon->options['installed_version'] = $this->addon->version;
                update_option( $this->addon->option_name , $this->addon->options);
            }
        }

        /**
         * Update prior add-on pack installations.
         */
        function update_prior_installs() {
                if ( ! empty ( $this->addon->activation_class_name ) ) {                
                    if ( class_exists( $this->addon->activation_class_name ) == false) {
                        if ( file_exists( $this->addon->dir.'include/class.activation.php' ) ) {
                            require_once($this->addon->dir.'include/class.activation.php');
                            $this->activation = new $this->addon->activation_class_name(array( 'addon' => $this->addon , 'slplus' => $this->slplus ));
                            $this->activation->update();
                        }
                    }
                }
        }
        

        //-------------------------------------
        // Methods : filters
        //-------------------------------------
        
        /**
         * Add our admin pages to the valid admin page slugs.
         *
         * @param string[] $slugs admin page slugs
         * @return string[] modified list of admin page slugs
         */
        function filter_AddOurAdminSlug($slugs) {
            return array_merge($slugs,
                    array(
                        $this->admin_page_slug,
                        SLP_ADMIN_PAGEPRE.$this->admin_page_slug,
                        )
                    );
        }
        
    }
}
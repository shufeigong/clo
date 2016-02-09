<?php
if (!class_exists('SLP_BaseClass_Admin')) {

    /**
     * A base class that helps add-on packs separate admin functionality.
     *
     * Add on packs should include and extend this class.
     *
     * This allows the main plugin to only include this file in admin mode
     * via the admin_menu call.   Reduces the front-end footprint.
     *
     * @property        SLP_BaseClass_Addon     $addon
     * @property        string                  $admin_page_slug    	The slug for the admin page.
     * @property        null|string[]           $js_pages           	Which pages do we put the admin JS on?
     * @property        string[]                $js_requirements    	An array of the JavaScript hooks that are needed by the userinterface.js script.
     * @property        string[]                $js_settings        	JavaScript settings that are to be localized as a <slug>_settings JS variable.
     * @property        SLPlus                  $slplus
     *
     * @property		array					$settings_pages			The settings pages we support and the checkboxes that live there:
     * 					$settings_pages[<slug>] = array( 'checkbox_option_1' , 'checkbox_option_2', ...);  // <slug> = the page name
     *
     * LEGACY DEPRECATED
     * @property      string[]                $admin_checkboxes   	The checkboxes for the slp_experience tab.  USE SETTINGS_PAGES PROPERTY.
     * @property		string					$admin_checkbox_page	The default page that admin checkboxes appear 'slp_experience'. USE SETTINGS_PAGES PROPERTY.
     *
     * TODO: Add a method that invokes a base_class.admin.slp_page.php class for methods used only if on a SLP admin page (test admin_slugs)
     *
     * @package StoreLocatorPlus\BaseClass\Admin
     * @author Lance Cleveland <lance@charlestonsw.com>
     * @copyright 2013 - 2015 Charleston Software Associates, LLC
     */
    class SLP_BaseClass_Admin extends SLPlus_BaseClass_Object {

        protected $addon;
        protected $admin_checkboxes = array();    // DEPRECATED
        protected $admin_checkbox_page = 'slp_experience';  // DEPRECATED
        protected $admin_page_slug;
        protected $js_pages = null;
        protected $js_requirements = array();
        protected $js_settings = array();
        protected $settings_pages;
        private $settings_have_been_saved = false;

        /**
         * Return an array of checkbox names for the current settings page being processed.
         *
         * If settings_pages is in use (and it should be) - return the array of checkbox names for the currently active tab (page slug).
         *
         * Otherwise, if the current tab matches the admin_checkbox_page property, return the admin_checkbox array of checkbox names.
         *
         * @return string[]
         */
        private function get_my_checkboxes() {
            if (isset($this->settings_pages) && isset($this->settings_pages[$_REQUEST['page']])) {
                return $this->settings_pages[$_REQUEST['page']];
            }

            if (!empty($this->admin_checkboxes) && ( $_REQUEST['page'] === $this->admin_checkbox_page )) {
                return $this->admin_checkboxes;
            }

            return array();
        }

        /**
         * Run these things during invocation. (called from base object in __construct)
         */
        protected function initialize() {
            if (!isset($this->admin_page_slug) || empty($this->admin_page_slug)) {
                $this->admin_page_slug = $this->addon->short_slug;
            }

            $this->set_addon_properties();
            if (!$this->being_deactivated()) {
                $this->do_admin_startup();
            }
            $this->add_hooks_and_filters();     // TODO: shouldn't this be moved into the ! being_deactivated() test above?
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
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_javascript'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_css'));


            // TODO: Remove this action hook when all add-on packs use this classes default do_admin_startup() in place of their own. (CEX,ELM,GFI,GFL,LEX,MM,PAGES,REX,SME,TAG,UML,W)
            add_action('slp_save_ux_settings', array($this, 'save_my_settings'));
        }

        /**
         * Check for updates of active add on packs.
         */
        function check_for_updates() {
            if (!class_exists('SLPlus_Updates')) {
                require_once('class.updates.php');
            }
            if (class_exists('SLPlus_Updates')) {
                $this->Updates = new SLPlus_Updates(
                        $this->get_addon_version(), $this->addon->slug
                );
            }
        }

        /**
         * Is this add-on being deactivated?
         *
         * @return bool
         */
        protected function being_deactivated() {
            $action_is_deactivate = isset($_REQUEST['action']) && ( $_REQUEST['action'] === 'deactivate' );
            $deactivate_is_true = isset($_REQUEST['deactivate']) && ( $_REQUEST['deactivate'] === 'true' );
            $plugin_is_this_one = isset($_REQUEST['plugin']) && ( $_REQUEST['plugin'] === $this->addon->slug );
            return ( $plugin_is_this_one && ( $action_is_deactivate || $deactivate_is_true ) );
        }

        /**
         * Things we want our add on packs to do when they start.
         *
         * Extend this by overriding this method and then calling parent::do_admin_startup()
         * before or after your extension.
         */
        function do_admin_startup() {
            if ($this->being_deactivated()) {
                return;
            }

            $this->check_for_updates();
            $this->update_install_info();

            // Only save settings if the update action is set.
            if (empty($_POST)) {
                return;
            }
            if (!isset($_REQUEST['page'])) {
                return;
            }
            if (!isset($_REQUEST['action'])) {
                return;
            }
            if ($_REQUEST['action'] !== 'update') {
                return;
            }
            if (
                    ( isset($this->settings_pages) && array_key_exists($_REQUEST['page'], $this->settings_pages) ) ||
                    ( $_REQUEST['page'] === $this->admin_checkbox_page )
            ) {

                $this->save_my_settings();
            }
        }

        /**
         * If the file admin.css exists and the page prefix starts with slp_ , enqueue the admin style.
         */
        function enqueue_admin_css($hook) {
            add_filter('wpcsl_admin_slugs', array($this, 'filter_AddOurAdminSlug'));
            $this->slplus->AdminUI->enqueue_admin_stylesheet($hook);
            if ( file_exists($this->addon->dir . 'css/admin.css') && 
                    ( $this->slplus->AdminUI->is_our_admin_page( $hook ) || ( strpos($hook, SLP_ADMIN_PAGEPRE) !== false ) )
                ) {
                wp_enqueue_style($this->addon->slug . '_admin_css', $this->addon->url . '/css/admin.css');
            }
        }

        /**
         * If the file admin.js exists, enqueue it.
         */
        function enqueue_admin_javascript($hook) {
            if (!$this->ok_to_enqueue_admin_js($hook)) {
                return;
            }
            wp_enqueue_script($this->addon->slug . '_admin', $this->addon->url . '/include/admin.js', $this->js_requirements);
            wp_localize_script($this->addon->slug . '_admin', preg_replace('/\W/', '', $this->addon->get_meta('TextDomain')) . '_settings', $this->js_settings
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
            if (method_exists($this->addon, 'get_meta')) {
                return $this->addon->get_meta('Version');
            }

            // Defined Version Constant
            $class_name = get_class($this->addon);
            if (defined($class_name . '::VERSION')) {
                $this->retrofit_legacy_addons($class_name);
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
        function ok_to_enqueue_admin_js($hook) {
            if (is_null($this->js_pages)) {
                return false;
            }
            if (!in_array($hook, $this->js_pages)) {
                return false;
            }
            if (!$this->slplus->AdminUI->is_our_admin_page($hook)) {
                return false;
            }
            if (!file_exists($this->addon->dir . 'include/admin.js')) {
                return false;
            }
            return true;
        }

        /**
         * Patch in some needed properties for addons not using the full framework WTF.
         *
         * @param $class_name
         */
        private function retrofit_legacy_addons($class_name) {

            // version
            if (defined($class_name . '::VERSION')) {
                $this->addon->version = constant($class_name . '::VERSION');
            }

            // option name
            if (!property_exists($this->addon, 'option_name')) {

                if (isset($this->addon->settingsSlug) && !empty($this->addon->settingsSlug)) {
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
         * This will expect the checkboxes to come in via a $_POST[addon->option_name] variable.
         *
         * TODO: Refactor to save_experience_tab_settings
         *
         * Make sure you set the settings_pages properties so the right checkboxes end up on the right pages.
         */
        function save_my_settings() {

            // Don't short circuit if we are using crappy old add-on code (that I probably wrote...)
            //
			if (!$this->slplus->needed_for_addon(get_class() . '::' . __FUNCTION__) && $this->settings_have_been_saved) {
                return;
            }

            array_walk($_POST, array($this->addon, 'set_ValidOptions'));

            $this->addon->options = $this->slplus->AdminUI->save_SerializedOption(
                    $this->addon->option_name, $this->addon->options, $this->get_my_checkboxes()
            );

            // TODO: eliminate when (PAGES) starts using full add-on framework
            if (method_exists($this->addon, 'init_options')) {
                $this->addon->init_options();
            }

            $this->settings_have_been_saved = true;
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
        function set_ValidOptions($val, $key) {
            $this->addon->set_ValidOptions($val, $key);
        }

        /**
         * Update the install info for this add on.
         */
        function update_install_info() {
            $installed_version = isset($this->addon->options['installed_version']) ?
                    $this->addon->options['installed_version'] :
                    '0.0.0';

            if (version_compare($installed_version, $this->addon->version, '<')) {
                $this->update_prior_installs();
                $this->addon->options['installed_version'] = $this->addon->version;
                update_option($this->addon->option_name, $this->addon->options);
            }
        }

        /**
         * Update prior add-on pack installations.
         */
        function update_prior_installs() {
            if (!empty($this->addon->activation_class_name)) {
                if (class_exists($this->addon->activation_class_name) == false) {
                    if (file_exists($this->addon->dir . 'include/class.activation.php')) {
                        require_once($this->addon->dir . 'include/class.activation.php');
                        $this->activation = new $this->addon->activation_class_name(array('addon' => $this->addon, 'slplus' => $this->slplus));
                        $this->activation->update();
                    }
                }
            }
        }

        /**
         * Add our admin pages to the valid admin page slugs.
         *
         * @param string[] $slugs admin page slugs
         * @return string[] modified list of admin page slugs
         */
        function filter_AddOurAdminSlug($slugs) {
            return array_merge($slugs, array(
                $this->admin_page_slug,
                SLP_ADMIN_PAGEPRE . $this->admin_page_slug,
                    )
            );
        }

    }

}
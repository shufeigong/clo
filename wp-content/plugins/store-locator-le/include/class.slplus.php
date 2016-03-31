<?php
if (! class_exists('SLPlus')) {
	require_once( SLPLUS_PLUGINDIR . 'include/base_class.object.php' );

    /**
     * The base plugin class for Store Locator Plus.
     *
     * @property    SLPlus_Activation       $Activation
     * @property    SLP_BaseClass_Addon[]   $addons                     active add-ons TODO: Remove when all references are moved to $this->add_ons->instances (DIR,ES,GFI,REX,W)
     * @property    SLP_Option_Manager      $option_manager             Manage the SLP options.
     * @property    string[]                $text_string_options        These are the options needing translation.
     * @property    string                  $slp_store_url              The SLP Store URL.
     * @property    string                  $support_url                The SLP Support Site URL.
     * @property    string                  $updater_url                The update engine URL.
     *
     * @package StoreLocatorPlus
     * @author Lance Cleveland <lance@charlestonsw.com>
     * @copyright 2012-2015 Charleston Software Associates, LLC
     *
     */
    class SLPlus {
        //-------------------------------------
        // Constants
        //-------------------------------------

        /**
         * Define the location post type.
         */
        const locationPostType = 'store_page';

        /**
         * Define the location post taxonomy.
         */
        const locationTaxonomy = 'stores';

        /**
         * PRO: Pro Pack web link.
         *
         * TODO: Remove this when UML removes constant reference.
         */
        const linkToPRO = '<a href="http://www.storelocatorplus.com/product/slp4-pro/" target="csa">Pro Pack</a>';

        //-------------------------------------
        // Properties
        //-------------------------------------

	    /**
	     * The options that the user has set for Store Locator Plus.
	     *
	     * Key is the name of a supported option, value is the default value.
	     *
	     * NOTE: Booleans must be set as a string '0' or '1'.
	     * That is how serialize/deserialize stores and pulls them from the DB.
	     *
	     * Anything stored in here also gets passed to the slp.js via the slplus.options object.
	     * Reference the settings in the slp.js via slplus.options.<key>
	     *
	     * These elements are LOADED EVERY TIME the plugin starts.
	     *
	     * TODO : Create a new MASTER options list called something like master_options.
	     * Master options is an array of options names with various properties.
	     * The init_options() call sets up those properties for things like:
	     *     needs_translation
	     *     javascript / nojavascript
	     *
	     * public $master_options = array(
	     *       'label_email' => array(   'javascript' => false , 'translate' => true , default_value=> 'Email' ) ,
	     * );
	     *
	     * @var mixed[]
	     */
	    public $options = array(
		    'bubblelayout'                  => '',
		    'distance_unit'                 => 'miles',
		    'immediately_show_locations'    => '1',
		    'initial_radius'                => '10000',
		    'initial_results_returned'      => '25',
		    'label_directions'              => '',      // get_string_default()
		    'label_fax'                     => '',      // get_string_default()
            'label_email'                   => '',      // get_string_default()
		    'label_phone'                   => '',      // get_string_default()
            'label_website'                 => '',      // get_string_default()
		    'map_center'                    => '',
            'map_center_lat'                => '',
            'map_center_lng'                => '',
		    'map_domain'                    => 'maps.google.com',
		    'map_end_icon'                  => '',
		    'map_home_icon'                 => '',
		    'map_type'                      => 'roadmap',
		    'no_autozoom'                   => '0',
		    'no_homeicon_at_start'          => '1',                 // EM has admin UI for this setting.
		    'slplus_version'                => SLPLUS_VERSION,
		    'zoom_level'                    => '12',
		    'zoom_tweak'                    => '1',
		    'ignore_radius'                 => '0',                 // Passed in as form var from Enhanced Search
	    );

	    /**
	     * Serialized plugin options that do NOT get passed to slp.js.
	     *
	     * @var mixed[]
	     */
	    public $options_nojs = array(
		    'admin_locations_per_page'  => '10',
		    'broadcast_timestamp'       => '0',
		    'build_target'              => 'production',
		    'data_is_extended'          => null,   // True if there are ANY extended data fields.
		    'default_country'           => 'unitedstates',
		    'extended_data_tested'      => '0',
		    'force_load_js'             => '0',
		    'google_client_id'          => '',
		    'google_private_key'        => '',
            'google_server_key'         => '',
		    'has_extended_data'         => '',
		    'http_timeout'              => '10',    // HTTP timeout for GeoCode Requests (seconds)
		    'instructions'              => '',      // get_string_default()
		    'invalid_query_message'     => '',      // get_string_default()
		    'label_image'               => '',      // get_string_default()
		    'label_hours'               => '',      // get_string_default()
		    'map_height'                => '480',
		    'map_height_units'          => 'px',
		    'map_language'              => 'en',
		    'map_width'                 => '100',
		    'map_width_units'           => '%',
		    'max_results_returned'      => '25',
		    'next_field_id'             => 1,
		    'next_field_ported'         => '',
		    'php_max_execution_time'    => '600',
		    'premium_user_id'           => '',
		    'premium_subscription_id'   => '',
		    'remove_credits'            => '0',
		    'retry_maximum_delay'       => '5.0',
            'slplus_plugindir'          => SLPLUS_PLUGINDIR,
            'slplus_basename'           => SLPLUS_BASENAME,
		    'theme'                     => 'twentyfifteen_rev03',
	    );

	    public $text_string_options = array(
		    'invalid_query_message',
		    'instructions' ,
		    'label_directions',
		    'label_email',
		    'label_fax',
		    'label_hours',
		    'label_image',
		    'label_phone',
		    'label_website',
	    );

        public $Activation;
        public $addons = array();
	    public $slp_store_url   = 'http://www.storelocatorplus.com/';
        public $support_url     = 'http://www.storelocatorplus.com/support/documentation/store-locator-plus/';
	    public $updater_url     = 'http://www.storelocatorplus.com/wp-admin/admin-ajax.php';    // TODO: Move to class.updates.php

        /**
         * The add on manager handles add on connections and data.
         *
         * @var SLPlus_AddOn_Manager
         */
        public $add_ons;

        /**
         * The Admin UI object.
         *
         * @var SLPlus_AdminUI $AdminUI
         */
        public $AdminUI;

        /**
         * The Ajax Handler object.
         *
         * @var SLPlus_AjaxHandler
         */
        public $AjaxHandler;

	    /**
	     * @var SLP_Country_Manager
	     **/
        public $CountryManager;

	    /**
	     * @var string
	     */
	    private $current_admin_page = '';

        /**
         * The current location.
         *
         * @var SLPlus_Location $currentLocation
         */
        public $currentLocation;

        /**
         * The global $wpdb object for WordPress.
         *
         * @var wpdb $db
         */
        public $db;

        /**
         * Default settings for the plugin.
         *
         * These elements are LOADED EVERY TIME the plugin starts.
         *
         * o bubblelayout - default html and shortcodes for map bubbles
         * o layout - default html structure for slplus shortcode page view
         * o maplayout - default html and shortcodes for map bubbles
         * o resultslayout -  default html and shortcodes for search results
         * o searchlayout -  default html and shortcodes for the search form
         * o theme - the default theme if not previously set (new installs)
         *
         * @var mixed[] $defaults
         */
        public $defaults = array(

            // Overall Layout
            //
            // If you change this change default.css theme as well.
            //
            'layout' =>
                '<div id="sl_div">[slp_search][slp_map][slp_results]</div>',


            // Bubble Layout shortcodes have the following format:
            // [<shortcode> <attribute> <optional modifier> <optional modifier argument>]
            //
            // shortcode:
            //    slp_location = marker data elements like the location name or address elements
            //
            //        attribute = a location attribute
            //            same marker fields as enhanced results
            //            'url' is a special marker built from store pages and web options to link to a web page
            //
            //        modifier  = one of the following:
            //            suffix = append the noted HTML or text after the location attribute is output according to the modifier argument:
            //                br     = append '<br/>'
            //                space  = append ' '
            //                comma  = append ','
            //
            //            wrap   = wrap the location attribute in a special tag or text according to the modifier arguement:
            //                img   = make the location attribute the source for an <img /> tag.
            //
            //    slp_option   = slplus options from the option property below
            //
            //        attribute = an option key name
            //
            //        modifier  = one of the following :
            //            ifset = output only if the noted location attribute is empty, the location attribute is specified in the modifier argument:
            //                for example [slp_option label_phone ifset phone] outputs the label_phone option only if the location phone is not empty.
            'bubblelayout' =>
                '<div id="sl_info_bubble" class="[slp_location featured]">
    <span id="slp_bubble_name"><strong>[slp_location name  suffix  br]</strong></span>
    <span id="slp_bubble_address">[slp_location address       suffix  br]</span>
    <span id="slp_bubble_address2">[slp_location address2      suffix  br]</span>
    <span id="slp_bubble_city">[slp_location city          suffix  comma]</span>
    <span id="slp_bubble_state">[slp_location state suffix    space]</span>
    <span id="slp_bubble_zip">[slp_location zip suffix  br]</span>
    <span id="slp_bubble_country"><span id="slp_bubble_country">[slp_location country       suffix  br]</span></span>
    <span id="slp_bubble_directions">[html br ifset directions]
    [slp_option label_directions wrap directions]</span>
    <span id="slp_bubble_website">[html br ifset url]
    [slp_location url           wrap    website][slp_option label_website ifset url][html closing_anchor ifset url][html br ifset url]</span>
    <span id="slp_bubble_email">[slp_location email         wrap    mailto ][slp_option label_email ifset email][html closing_anchor ifset email][html br ifset email]</span>
    <span id="slp_bubble_phone">[html br ifset phone]
    <span class="location_detail_label">[slp_option   label_phone   ifset   phone]</span>[slp_location phone         suffix    br]</span>
    <span id="slp_bubble_fax"><span class="location_detail_label">[slp_option   label_fax     ifset   fax  ]</span>[slp_location fax           suffix    br]<span>
    <span id="slp_bubble_description"><span id="slp_bubble_description">[html br ifset description]
    [slp_location description raw]</span>[html br ifset description]</span>
    <span id="slp_bubble_hours">[html br ifset hours]
    <span class="location_detail_label">[slp_option   label_hours   ifset   hours]</span>
    <span class="location_detail_hours">[slp_location hours         suffix    br]</span></span>
    <span id="slp_bubble_img">[html br ifset img]
    [slp_location image         wrap    img]</span>
    <span id="slp_tags">[slp_location tags]</span>
    </div>'
        ,


            // Map Layout
            // If you change this change default.css theme as well.
            //
            'maplayout' =>
                '[slp_mapcontent][slp_maptagline]',


            // Results Layout
            // If you change this change default.css theme as well.
            //
            'resultslayout' =>
                '<div id="slp_results_[slp_location id]" class="results_entry  [slp_location featured]">
        <div class="results_row_left_column"   id="slp_left_cell_[slp_location id]"   >
            <span class="location_name">[slp_location name]</span>
            <span class="location_distance">[slp_location distance_1] [slp_location distance_unit]</span>
        </div>
        <div class="results_row_center_column" id="slp_center_cell_[slp_location id]" >
            <span class="slp_result_address slp_result_street">[slp_location address]</span>
            <span class="slp_result_address slp_result_street2">[slp_location address2]</span>
            <span class="slp_result_address slp_result_citystatezip">[slp_location city_state_zip]</span>
            <span class="slp_result_address slp_result_country">[slp_location country]</span>
            <span class="slp_result_address slp_result_phone">[slp_location phone]</span>
            <span class="slp_result_address slp_result_fax">[slp_location fax]</span>
        </div>
        <div class="results_row_right_column"  id="slp_right_cell_[slp_location id]"  >
            <span class="slp_result_contact slp_result_website">[slp_location web_link]</span>
            <span class="slp_result_contact slp_result_email">[slp_location email_link]</span>
            <span class="slp_result_contact slp_result_directions"><a href="http://[slp_option map_domain]/maps?saddr=[slp_location search_address]&daddr=[slp_location location_address]" target="_blank" class="storelocatorlink">[slp_location directions_text]</a></span>
            <span class="slp_result_contact slp_result_hours">[slp_location hours]</span>
            [slp_location pro_tags]
            [slp_location iconarray wrap="fullspan"]
            [slp_location eventiconarray wrap="fullspan"]
            [slp_location socialiconarray wrap="fullspan"]
            </div>
    </div>'
        ,

            // Search Layout
            // If you change this change default.css theme as well.
            //
            // Use the slp_search_element shortcode processor to hook in add-on packs.
            // Look for the attributes add_on and location="..." to place items.
            //
            // TODO: update PRO, ES, GFI&GFL to use the add_on location="..." processing.
            // TODO: deprecate the add-on specific shortcodes at some point
            //
            'searchlayout' =>
                '<div id="address_search">
        [slp_search_element add_on location="very_top"]
        [slp_search_element input_with_label="name"]
        [slp_search_element input_with_label="address"]
        [slp_search_element dropdown_with_label="city"]
        [slp_search_element dropdown_with_label="state"]
        [slp_search_element dropdown_with_label="country"]
        [slp_search_element selector_with_label="tag"]
        [slp_search_element dropdown_with_label="category"]
        [slp_search_element dropdown_with_label="gfl_form_id"]
        [slp_search_element add_on location="before_radius_submit"]
        <div class="search_item">
            [slp_search_element dropdown_with_label="radius"]
            [slp_search_element button="submit"]
        </div>
        [slp_search_element add_on location="after_radius_submit"]
        [slp_search_element add_on location="very_bottom"]
    </div>'
        ,
        );

	    /**
	     * Holder for get_item call stored values.
	     *
	     * TODO: remove this when all get_item() calls reference options or options_nojs (GFL, MM, PRO, REX, SLP)
	     * @deprecated
	     *
	     * @var array
	     */
	    public $slp_items = array();

        /**
         * Array of slugs + booleans for plugins we've already fetched info for.
         *
         * @var array[] named array, key = slug, value = true
         */
        public $infoFetched = array();


        /**
         * The default options (before being read from DB)
         *
         * @var mixed[]
         */
        public $options_default = array();

        /**
         * The default options_nojs (before being read from DB)
         *
         * @var array
         */
        public $options_nojs_default = array();

        /**
         * The settings that impact how the plugin renders.
         *
         * These elements are ONLY WHEN wpCSL.helper.loadPluginData() is called.
         * loadPluginData via 'getoption' always reads a single entry in the wp_options table
         * loadPluginData via 'getitem' checks the settings RAM cache first, then loads single entries from wp_options
         * BOTH are horrible ideas.  Serialized data is far better.
         *
         * @var mixed $data
         */
        public $data = array();

        /**
         * The data interface helper.
         *
         * @var \SLPlus_Data $database
         */
        public $database;

	    /**
	     * True if debug-my-plugin is installed and active.
	     *
	     * @var bool
	     */
	    private $debugMP_is_active = false;

        /**
         * Full path to this plugin directory.
         *
         * @var string $dir
         */
        private $dir;

	    /**
	     * Debug My Plugin stack
	     *
	     * named array, key is the panel ID
	     *
	     * key is an array that is the params for the DMP function calls.
	     *
	     * @var mixed[]
	     */
	    private $dmpStack = array('main' => array());

        /**
         * Quick reference for the Force Load JavaScript setting.
         *
         * @var boolean
         */
        public $javascript_is_forced = true;

        public $option_manager;

	    /**
	     * The URL that reaches the home directory for the plugin.
	     *
	     * @var string $plugin_url
	     */
	    public $plugin_url = SLPLUS_PLUGINURL;

	    /**
	     * @var bool
	     */
	    public $shortcode_was_rendered = false;

        /**
         * What slug do we go by?
         *
         * @var string $slug
         */
        public $slug;

	    /**
	     * The style handle for CSS invocation.
	     *
	     * @var string
	     */
	    public $styleHandle = 'slp_admin_style';

	    /**
	     * The version that was installed at the start of the plugin (prior installed version).
	     *
	     * @var string
	     */
	    public $installed_version = null;

	    /**
	     * @var SLP_Helper
	     */
	    public $helper;

	    /**
	     * @var string
	     */
	    public $name = SLPLUS_NAME;

	    /**
	     * @var wpCSL_notifications__slplus
	     */
	    public $notifications;

	    /**
	     * @var string
	     */
	    public $prefix = SLPLUS_PREFIX;

	    /**
	     * TODO: deprecate when (GFL,REX) convert to slplus->get_item() , better yet replace with options/options_nojs.
	     * @var SLP_getitem
	     */
	    public $settings;

	    /**
	     * @var PluginTheme
	     */
	    public $themes;

	    /**
	     * @var SLPlus_UI
	     */
	    public $UI;

	    /**
	     * Full URL to this plugin directory.
	     *
	     * @var string $url
	     */
	    public $url;


	    /**
	     * The current plugin version intended to be run now.
	     *
	     * @var string
	     */
	    public $version;

	    /**
	     * @var SLPlus_WPML $WPML
	     */
	    public $WPML;

        //-------------------------------------
        // Methods
        //-------------------------------------

        /**
         * Initialize a new SLPlus Object
         */
        public function __construct()  {

	        // Properties set via define or hard calculation.
	        //
	        $this->dir                  = plugin_dir_path(SLPLUS_FILE);
	        $this->slug                 = plugin_basename(SLPLUS_FILE);
	        $this->url                  = plugins_url('', SLPLUS_FILE);

	        // Properties Set By Methods
	        //
	        $this->current_admin_page   = $this->get_admin_page();
	        $this->debugMP_is_active    = $this->is_debugMP_active();
        }

	    /**
	     * Add meta links.
	     *
	     * TODO: ADMIN ONLY
	     *
	     * @param string[] $links
	     * @param string $file
	     * @return string
	     */
	    function add_meta_links($links, $file) {
		    if ($file == SLPLUS_BASENAME) {
			    $links[] = '<a href="' . $this->support_url . '" title="' . __('Documentation', 'store-locator-le') . '">' .
			               __('Documentation', 'store-locator-le') . '</a>';
			    $links[] = '<a href="' . $this->slp_store_url . '" title="' . __('Buy Upgrades', 'store-locator-le') . '">' .
			               __('Buy Upgrades', 'store-locator-le') . '</a>';
			    $links[] = '<a href="'.admin_url( 'admin.php?page=slp_info' ) .'" title="' .
			               __('Settings', 'store-locator-le') . '">' . __('Settings', 'store-locator-le') . '</a>';
		    }
		    return $links;
	    }

	    /**
	     * Setup WordPress action scripts.
	     *
	     * Note: admin_menu is not called on every admin page load
	     * Reference: http://codex.wordpress.org/Plugin_API/Action_Reference
	     */
	    function add_actions() {
		    if (is_admin()) {
			    add_action('admin_notices'  , array($this->notifications, 'display'             )       );
			    add_filter('plugin_row_meta', array($this               , 'add_meta_links'      ), 10, 2);
		    }
		    add_action( 'plugins_loaded' , array( $this , 'initialize_after_plugins_loaded' ) );
	    }

	    /**
	     * Create the notifications object and attach it.
	     *
	     */
	    function attach_notifications() {
		    if ( ! isset( $this->notifications ) ) {
			    require_once( 'class.notifications.php' );
			    $this->notifications = new wpCSL_notifications__slplus(
				    array(
					    'prefix' => SLPLUS_PREFIX,
					    'name'   => $this->name,
					    'url'    => 'admin.php?page=slp_info',
				    )
			    );
		    }
	    }

	    /**
	     * Connect SLPlus_Activation object to Activation property.
	     */
	    public function createobject_Activation() {
		    if ( ! isset( $this->Activation ) ) {
			    require_once(SLPLUS_PLUGINDIR . 'include/class.activation.php');
			    $this->Activation = new SLPlus_Activation();
		    }
	    }

	    /**
	     * Create and attach the add on manager object.
	     */
	    public function createobject_AddOnManager()  {
		    if (!isset($this->add_ons)) {
			    require_once(SLPLUS_PLUGINDIR . 'include/class.addon.manager.php');
			    $this->add_ons = new SLPlus_AddOn_Manager();
		    }
	    }

	    /**
	     * Create the AJAX procssing object and attach to this->ajax
	     */
	    function createobject_AJAX() {
		    if ( ! isset( $this->ajax ) ) {
			    require_once('class.ajax.php');
			    $this->ajax = new SLP_AJAX();
			    $this->AjaxHandler = $this->ajax;

		    }
	    }

	    /**
	     * Create the Country Manager object.
	     */
	    public function create_object_CountryManager( ) {
		    if ( ! isset( $this->CountryManager ) ) {
			    require_once( SLPLUS_PLUGINDIR . 'include/class.country.manager.php' );
			    $this->CountryManager = new SLP_Country_Manager();
		    }
	    }

	    /**
	     * Create the help object and attach it.
	     */
	    function create_object_helper() {
		    if ( ! isset( $this->helper ) ) {
			    require_once( 'class.helper.php' );
			    $this->helper = new SLP_Helper();
		    }
	    }

        function create_object_option_manager() {
            if ( ! isset( $this->option_manager ) ) {
                require_once( 'class.option.manager.php' );
                $this->option_manager = new SLP_Option_Manager();
            }
        }

	    /**
	     * Create the settings object and attach it.
	     *
	     * Apparently only used for the get_item method.
	     *
	     * TODO: deprecate when (GFL,REX) convert to slplus->get_item() , better yet replace with options/options_nojs.
	     *
	     */
	    function create_object_settings() {
		    if ( ! isset( $this->settings ) ) {
			    require_once( 'class.getitem.php' );
			    $this->settings = new SLP_getitem();
		    }
	    }

	    /**
	     * Create the theme object and attach it.
	     */
	    function create_object_themes() {
		    if ( ! isset( $this->themes ) ) {
			    require_once( 'class.themes.php' );
			    $this->themes = new PluginTheme();
		    }
	    }

	    /**
	     * Create the WPML object.
	     */
	    function create_object_WPML() {
		    if ( ! isset( $this->WPML ) ) {
			    require_once( SLPLUS_PLUGINDIR . 'include/class.wpml.php' );
			    $this->WPML = new SLPlus_WPML();
		    }
	    }

	    /**
	     * Return a deprecated notification.
	     *
	     * TODO : move to a deprecated class, invoke with attach_deprecated.
	     *
	     * @param string $function_name name of function that is deprecated.
	     * @return string
	     */
	    public function createstring_Deprecated($function_name) {
		    return
			    sprintf(
				    __('The %s method is no longer available. ', 'store-locator-le'), $function_name
			    ) .
			    '<br/>' .
			    __('It is likely that one of your add-on packs is out of date. ', 'store-locator-le') .
			    '<br/>' .
			    sprintf(
				    __('You need to <a href="%s" target="csa">upgrade</a> to the latest %s compatible version ' .
				       'or <a href="%s" target="csa">downgrade</a> the %s plugin.', 'store-locator-le'), $this->slp_store_url, $this->name, 'https://wordpress.org/plugins/store-locator-le/developers/', $this->name
			    )
			    ;
	    }

	    /**
	     * Finish our starting constructor elements.
	     */
	    public function initialize() {
            if (class_exists('SLPlus_Location') == false) {
                require_once(SLPLUS_PLUGINDIR.'include/class.location.php');
            }
            $this->currentLocation = new SLPlus_Location(array('slplus' => $this));

            $this->create_object_option_manager();
            $this->option_manager->initialize();

		    // Attach objects
		    //
		    $this->attach_notifications();
		    $this->create_object_helper();
		    $this->create_object_settings();

		    // Setup pointers and WordPress connections
		    //
		    $this->add_actions();

		    $this->initDB();

		    // AJAX Processing
		    //
		    if ( defined('DOING_AJAX') && DOING_AJAX ) {
			    $this->createobject_AJAX();
		    }
	    }

	    /**
	     * Things to do after all plugins are loaded.
	     */
	    public function initialize_after_plugins_loaded() {
		    $this->create_object_themes();
		    $this->create_object_WPML();
	    }


        /**
         * Setup the database properties.
         *
         * latlongRegex = '^\s*-?\d{1,3}\.\d+,\s*\d{1,3}\.\d+\s*$';
         *
         * @global wpdb $wpdb
         */
        function initDB()
        {
            global $wpdb;
            $this->db = $wpdb;

            // Set the data object
            //
            require_once(SLPLUS_PLUGINDIR . 'include/class.data.php');
            $this->database = new SLPlus_Data();
        }

        /**
	     * Add DebugMyPlugin messages.
	     *
	     * @param string $panel - panel name
	     * @param string $type - what type of debugging (msg = simple string, pr = print_r of variable)
	     * @param string $header - the header
	     * @param string $message - what you want to say
	     * @param string $file - file of the call (__FILE__)
	     * @param int $line - line number of the call (__LINE__)
	     * @param boolean $notime - skipping showing the time? default = true
	     * @return null
	     */
	    function debugMP($panel = 'main', $type = 'msg', $header = 'wpCSL DMP', $message = '', $file = null, $line = null, $notime = true, $clearingStack = false) {
		    if ( ! $this->debugMP_is_active ) { return; }

		    // Escape HTML Messages
		    //
		    if (($type === 'msg') && ($message !== '')) {
			    $message = esc_html($message);
		    }

		    // TODO : Only if DebugMyPlugin Is Active
		    // otherwise we consume memory for no reason.
		    //
		    // Panel not setup yet?  Push onto stack.
		    //
		    if (
			    !isset($GLOBALS['DebugMyPlugin']) ||
			    !isset($GLOBALS['DebugMyPlugin']->panels[$panel])
		    ) {
			    if (!isset($this->dmpStack[$panel])) {
				    $this->dmpStack[$panel] = array();
			    }
			    array_push($this->dmpStack[$panel], array($type, $header, $message, $file, $line, $notime));
			    return;
		    }

		    // Have waiting messages?  Pop off stack.
		    //
		    if (!$clearingStack && isset($this->dmpStack[$panel]) && is_array($this->dmpStack[$panel])) {
			    while ($dmpMessage = array_shift($this->dmpStack[$panel])) {
				    $this->debugMP($panel, $dmpMessage[0], $dmpMessage[1], $dmpMessage[2], $dmpMessage[3], $dmpMessage[4], $dmpMessage[5], true);
			    }
		    }

		    // Do normal real-time message output.
		    //
		    switch (strtolower($type)):
			    case 'pr':
				    $GLOBALS['DebugMyPlugin']->panels[$panel]->addPR($header, $message, $file, $line, $notime);
				    break;
			    default:
				    $GLOBALS['DebugMyPlugin']->panels[$panel]->addMessage($header, $message, $file, $line, $notime);
		    endswitch;
	    }

	    /**
	     * Enqueue the Google Maps Script
	     */
	    public function enqueue_google_maps_script() {
		    wp_enqueue_script(
			    'google_maps',
			    $this->get_google_maps_url() ,
			    array(),
			    SLPLUS_VERSION,
			    ! $this->javascript_is_forced
		    );
	    }

	    /**
	     * Get the current admin page.
	     *
	     * @return string
	     */
	    private function get_admin_page() {
	        if (isset($_GET['page'])) {
		        $plugin_page = stripslashes($_GET['page']);
		        return plugin_basename($plugin_page);
	        }
		    return '';
	    }

	    /**
	     * Get the Google Maps URL
	     */
	    public function get_google_maps_url() {
		    // Google Maps API for Work client ID
		    //
		    $client_id =
			    ! empty ( $this->options_nojs['google_client_id'] ) ?
				    '&client=' . $this->options_nojs['google_client_id'] . '&v=3' :
				    '';

            // Google JavaScript API server Key
            //
            $server_key =
                ! empty ( $this->options_nojs['google_server_key'] )   ?
                    '&key=' . $this->options_nojs['google_server_key'] :
                    '';


		    // Set the map language
		    //
		    $language = 'language=' . $this->options_nojs['map_language'];

		    // Base Google API URL
		    //
		    $google_api_url =
			    'https://' .
			    'maps.googleapis.com' .
			    '/maps/api/' .
			    'js' .
			    '?';

			return $google_api_url . $language . $client_id . $server_key;
	    }

	    /**
	     * Manage the old settings->get_item() calls.
	     *
	     * TODO: remove this when all get_item() calls reference options or options_nojs (EM, GFL, MM, PRO, REX, SLP)
	     *
	     * @deprecated
	     *
	     * @param            $name
	     * @param null       $default
	     * @param string     $separator
	     * @param bool|false $forceReload
	     *
	     * @return mixed|void
	     */
	    function get_item($name, $default = null, $separator='-', $forceReload = false) {
		    $option_name = SLPLUS_PREFIX . $separator . $name;
		    if ( ! array_key_exists( $option_name , $this->slp_items ) ){
			    if ( is_null( $default ) ) { $default = false; }
			    $this->slp_items[ $option_name ] = get_option( $option_name , $default );
		    }

		    return $this->slp_items[ $option_name ];
	    }

        /**
         * Return true if the named add-on pack is active.
         *
         * TODO: Legacy code, move to class.addon.manager when UML is updated.
         *
         * @param string $slug
         * @return boolean
         */
        public function is_AddonActive($slug)  {
            $this->createobject_AddOnManager();
            return $this->add_ons->is_active($slug);
        }

	    /**
	     * Return '1' if the given value is set to 'true', 'on', or '1' (case insensitive).
	     * Return '0' otherwise.
	     *
	     * Useful for checkbox values that may be stored as 'on' or '1'.
	     *
	     * @param $value
	     * @param string $return_type
	     *
	     * @return bool|string
	     */
        public function is_CheckTrue($value, $return_type = 'boolean')  {
            if ($return_type === 'string') {
                $true_value = '1';
                $false_value = '0';
            } else {
                $true_value = true;
                $false_value = false;
            }

            if (strcasecmp($value, 'true') == 0) {
                return $true_value;
            }
            if (strcasecmp($value, 'on') == 0) {
                return $true_value;
            }
            if (strcasecmp($value, '1') == 0) {
                return $true_value;
            }
            if ($value === 1) {
                return $true_value;
            }
            if ($value === true) {
                return $true_value;
            }
            return $false_value;
        }

	    /**
	     * Check if the debugMP plugin is active and installed.
	     *
	     * @return bool
	     */
	    public function is_debugMP_active() {
		    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		    if (!function_exists('is_plugin_active') || !is_plugin_active('debug-my-plugin/debug-my-plugin.php')) {
			    return false;
		    }
		    return true;
	    }

        /**
         * Check if certain safe mode restricted functions are available.
         *
         * exec, set_time_limit
         *
         * @param $funcname
         * @return mixed
         */
        public function is_func_available( $funcname ) {
            static $available = array();

            if (!isset($available[$funcname])) {
                $available[$funcname] = true;
                if (ini_get('safe_mode')) {
                    $available[$funcname] = false;
                } else {
                    $d = ini_get('disable_functions');
                    $s = ini_get('suhosin.executor.func.blacklist');
                    if ("$d$s") {
                        $array = preg_split('/,\s*/', "$d,$s");
                        if (in_array($funcname, $array)) {
                            $available[$funcname] = false;
                        }
                    }
                }
            }

            return $available[$funcname];
        }

        /**
         * Checks if a URL is valid.
         *
         * @param $url
         * @return bool
         */
        public function is_valid_url($url)
        {
            $url = trim($url);
            return ((strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) &&
                filter_var($url, FILTER_VALIDATE_URL) !== false);
        }

        /**
         * Re-center the map as needed.
         *
         * Sets Center Map At ('map-center') and Lat/Lng Fallback if any of those entries are blank.
         *
         * Uses the Map Domain ('default_country') as the source for the new center.
         */
        public function recenter_map() {
            if ( empty( $this->options['map_center']     ) ) { $this->set_map_center();                 }
            if ( empty( $this->options['map_center_lat'] ) ) { $this->set_map_center_fallback( 'lat' ); }
            if ( empty( $this->options['map_center_lng'] ) ) { $this->set_map_center_fallback( 'lng' ); }
        }

        /**
         * Set the Center Map At if the setting is empty.
         */
        public function set_map_center() {
            $this->create_object_CountryManager();
            $this->options['map_center'] = $this->CountryManager->countries[$this->options_nojs['default_country']]->name;
        }

        /**
         * Set the map center fallback for the selected country.
         *
         * @param string $for   latlng | lat | lng
         */
        public function set_map_center_fallback( $for = 'latlng' ) {
            $this->create_object_CountryManager();

            // If the map center is set to the country.
            //
            if ( $this->options['map_center'] == $this->CountryManager->countries[$this->options_nojs['default_country']]->name ) {

                // Set the default country lat
                //
                if (($for === 'latlng') || ($for === 'lat')) {
                    $this->options['map_center_lat'] = $this->CountryManager->countries[$this->options_nojs['default_country']]->map_center_lat;
                }


                // Set the default country lng
                //
                if (($for === 'latlng') || ($for === 'lng')) {
                    $this->options['map_center_lng'] = $this->CountryManager->countries[$this->options_nojs['default_country']]->map_center_lng;
                }
            }

            // No Lat or Lng in Country Data?  Go ask Google.
            //
            if ( is_null( $this->options['map_center_lng'] ) || is_null( $this->options['map_center_lat'] ) ) {
                $json = $this->currentLocation->get_LatLong( $this->options['map_center'] );
                if ( ! empty( $json ) ) {
                    $json = json_decode( $json );
                    if ( $json->{'status'} === 'OK' ) {
                        if ( is_null( $this->options['map_center_lat'] ) ) {$this->options['map_center_lat'] = $json->results[0]->geometry->location->lat; }
                        if ( is_null( $this->options['map_center_lng'] ) ) {$this->options['map_center_lng'] = $json->results[0]->geometry->location->lng; }
                    }
                }
            }
        }

        /**
         * Set the PHP max execution time.
         */
        public function set_php_timeout() {
            ini_set( 'max_execution_time' , $this->options_nojs['php_max_execution_time'] );
            if ( $this->is_func_available( 'set_time_limit' ) ) {
                set_time_limit($this->options_nojs['php_max_execution_time']);
            }
        }


        /**
         * Set valid options from the incoming REQUEST
         *
         * @param mixed $val - the value of a form var
         * @param string $key - the key for that form var
         */
        function set_ValidOptions($val, $key) {
            if (array_key_exists($key, $this->options)) {
                if (is_numeric($val) || !empty($val)) {
                    $this->options[$key] = stripslashes_deep($val);
                } else {
                    $this->options[$key] = $this->options_default[$key];
                }

                // i18n/l10n translations may be needed.
                if (array_key_exists($key, $this->text_string_options)) {
                    $this->WPML->register_text($key, $this->options[$key]);
                }
            }
        }

        /**
         * Set valid options from the incoming REQUEST
         *
         * Set this if the incoming value is not an empty string.
         *
         * @param mixed $val - the value of a form var
         * @param string $key - the key for that form var
         */
        function set_ValidOptionsNoJS($val, $key) {
            if (array_key_exists($key, $this->options_nojs)) {
                if (is_numeric($val) || !empty($val)) {
                    $this->options_nojs[$key] = stripslashes_deep($val);
                } else {
                    $this->options_nojs[$key] = $this->options_nojs_default[$key];
                }

                // i18n/l10n translations may be needed.
                if (array_key_exists($key, $this->text_string_options)) {
                    $this->WPML->register_text($key, $this->options_nojs[$key]);
                }
            }
        }

	    /**
	     * Update the base plugin if necessary.
	     */
	    function activate_or_update_slplus() {
		    if ( is_null( $this->installed_version ) ) {
			    $this->installed_version = get_option( SLPLUS_PREFIX . "-installed_base_version", '' );
		    }

		    if ( version_compare( $this->installed_version, SLPLUS_VERSION , '<' ) ) {
			    $this->createobject_Activation();
			    $this->Activation->update();
		    }
	    }

        //----------------------------------------------------
        // DEPRECATED
        //----------------------------------------------------

        /**
         * Do not use, deprecated.
         *
         * @deprecated 4.3.21
         */
        function initOptions( $a = null, $b = null, $c = null, $d = null, $e = null, $f = null ) {
            $this->helper->create_string_wp_setting_error_box( $this->createstring_Deprecated( __FUNCTION__ ) );
            $this->create_object_option_manager();
            $this->option_manager->initialize();
            return false;
        }

	    /**
	     * Do not use, deprecated.
	     *
	     * @deprecated 4.1.00
	     */
	    function is_Extended( $a = null, $b = null, $c = null, $d = null, $e = null, $f = null ) {
		    $this->helper->create_string_wp_setting_error_box( $this->createstring_Deprecated( __FUNCTION__ ) );
		    return false;
	    }

	    /**
	     * Do not use, deprecated.
	     *
	     * @deprecated 4.2.63
	     */
	    function loadPluginData( $a = null, $b = null, $c = null, $d = null, $e = null, $f = null ) {
		    $this->helper->create_string_wp_setting_error_box( $this->createstring_Deprecated( __FUNCTION__ ) );
		    return false;
	    }

	    /**
	     * Do not use, deprecated.
	     *
	     * @deprecated 4.2.63
	     */
	    function register_addon( $a = null, $b = null, $c = null, $d = null, $e = null, $f = null ) {
		    $this->helper->create_string_wp_setting_error_box( $this->createstring_Deprecated( __FUNCTION__ ) );
		    return false;
	    }

	    /**
	     * Do not use, deprecated.
	     *
	     * @deprecated 4.2.63
	     */
	    function VersionCheck( $a = null, $b = null, $c = null, $d = null, $e = null, $f = null ) {
		    $this->helper->create_string_wp_setting_error_box( $this->createstring_Deprecated( __FUNCTION__ ) );
	    }
    }
}
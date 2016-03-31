<?php
/**
 * Plugin Name: Store Locator Plus : Tagalong
 * Plugin URI: http://www.storelocatorplus.com/product/slp4-tagalong/
 * Description: A premium add-on pack for Store Locator Plus that adds advanced location tagging features.
 * Version: 4.2.01
 * Author: Charleston Software Associates
 * Author URI: http://charlestonsw.com/
 * Requires at least: 3.7.0
 * Test up to : 4.0
 *
 * Text Domain: csa-slp-tagalong
 * Domain Path: /languages/
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// No SLP? Get out...
//
include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); 
if ( !function_exists('is_plugin_active') ||  !is_plugin_active( 'store-locator-le/store-locator-le.php')) {
    return;
}

// Make sure the class is only defined once.
//
if (!class_exists('SLPTagalong'   )) {

    require_once( WP_PLUGIN_DIR . '/store-locator-le/include/base_class.addon.php');

    /**
     * The Tagalong Add-On Pack for Store Locator Plus.
     *
     * @package StoreLocatorPlus\Tagalong
     * @author Lance Cleveland <lance@charlestonsw.com>
     * @copyright 2012-2014 Charleston Software Associates, LLC
     */
    class SLPTagalong extends SLP_BaseClass_Addon {

	    //-------------------------------------
	    // Properties
	    //-------------------------------------

	    /**
	     * The admin menu entries.
	     *
	     * @var mixed[]
	     */
	    public $admin_menu_entries;

	    /**
	     * The array of current location category IDs.
	     *
	     * @var int[] $current_location_categories
	     */
	    public $current_location_categories = array();

	    /**
	     * Custom attributes for a Tagalong category.
	     *
	     * Keys (custom):
	     * * category_url
	     * * map-marker
	     * * medium-icon
	     * * rank
	     * * url_target
	     *
	     * @var string[]
	     */
	    public $category_attributes = array(
		    'category_url'  => '',
		    'map-marker'    => '',
		    'medium-icon'   => '',
		    'rank'          => '',
		    'url_target'    => '',
	    );

	    /**
	     * The detailed category term data from the WP taxonomy with custom Tagalong data as well.
	     *
	     * @var mixed[] $category_details
	     */
	    private $category_details = array();

	    /**
	     * The base name for the category meta entry in the options table
	     *
	     * csl-slplus-TAGALONG-category_<term_id>
	     *
	     * @var string
	     */
	    public $category_meta_option_base;

	    /**
	     * Which location ID is the current_location_categories loaded with?
	     *
	     * @var int $categories_loaded_for
	     */
	    private $categories_loaded_for = null;

	    /**
	     * The data helper object.
	     *
	     * @var \Tagalong_Data $data
	     */
	    public $data;

	    /**
	     * Settable options for this plugin.
	     *
	     * @var mixed[] $options
	     */
	    public $options = array(
		    'ajax_orderby_catcount' => '0',
		    'default_icons'         => '0',
		    'hide_empty'            => '0',
		    'installed_version'     => '',
		    'label_category'        => 'Category: ',
		    'show_icon_array'       => '0',
		    'show_legend_text'      => '0',
		    'show_option_all'       => 'Any',
		    'show_cats_on_search'   => '',
	    );

	    /**
	     * True of the Store Pages add-on pack is active.
	     *
	     * @var boolean $StorePagesActive
	     */
	    public $StorePagesActive = false;

	    //------------------------------------------------------
	    // METHODS
	    //------------------------------------------------------

	    /**
	     * Invoke the plugin.
	     *
	     * This ensures a singleton of this plugin.
	     *
	     * @static
	     */
	    public static function init() {
		    static $instance = false;
		    if ( ! $instance ) {
			    load_plugin_textdomain( 'csa-slp-tagalong', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
			    $instance = new SLPTagalong(
				    array(
					    'version'               => '4.2.00',
					    'min_slp_version'       => '4.2.06',
					    'name'                  => __( 'Tagalong', 'csa-slp-tagalong' )     ,
					    'option_name'           => 'csl-slplus-TAGALONG-options'            ,
					    'slug'                  => plugin_basename( __FILE__ )              ,
					    'metadata'              => get_plugin_data( __FILE__, false, false ),
					    'url'                   => plugins_url( '', __FILE__ )              ,
					    'dir'                   => plugin_dir_path( __FILE__ )              ,
					    'activation_class_name'     => 'Tagalong_Activation'                    ,
					    'admin_class_name'          => 'SLPTagalong_Admin'                      ,
                        'ajax_class_name'           => 'SLPTagalong_AJAX'                       ,
                        'userinterface_class_name'  => 'SLPTagalong_UI'                         ,
				    )
			    );
		    }

		    return $instance;
	    }

	    /**
	     * Setup the custom things we need for this add-on pack.
	     */
	    function init_options() {
		    parent::init_options();

		    $this->category_meta_option_base = SLPLUS_PREFIX.'-TAGALONG-category_';
		    $this->category_attributes['taxonomy'] = SLPLUS::locationTaxonomy;

		    // Do NOT allow hide empty if Store Pages is not active.
		    //
		    $this->StorePagesActive = ( function_exists('is_plugin_active') &&  is_plugin_active( 'slp-pages/slp-pages.php'));
		    if (!$this->StorePagesActive) {
			    $this->options['hide_empty'] = '0';
		    }

		    $this->set_admin_menu_entries();
	    }

	    /**
	     * Add our general hooks and filter.
	     */
	    function add_hooks_and_filters() {
			$this->createobject_Data();
		    
		    // Extend Data
		    //
		    add_filter( 'slp_extend_get_SQL'            , array( $this , 'filter_AddTagalongSQL'        )       );

		    // Add Icons
		    //
		    add_filter('slp_icon_directories'          , array( $this , 'add_icon_directory'            )        ,10);


		    // Custom Taxonomy Processing from the WP stores category page
		    //
		    // wp-includes/taxonomy/ in wp_update_term()
		    //
		    //  do_action("edited_$taxonomy", $term_id, $tt_id);
		    //  do_action("create_$taxonomy", $term_id, $tt_id);
		    //
		    add_action( 'edited_' . SLPLUS::locationTaxonomy    , array( $this , 'create_or_edited_stores' ) , 10 , 2 );
		    add_action( 'create_' . SLPLUS::locationTaxonomy    , array( $this , 'create_or_edited_stores' ) , 10 , 2 );

            // Pro Pack AJAX Filters
            //
            add_filter('slp-pro-dbfields'                   ,array( $this ,'filter_AddCSVExportFields'), 100 );
            add_filter('slp-pro-csvexport'                  ,array( $this ,'filter_AddCSVExportData'  ), 100 );
	    }


	    /**
	     * Add our icon directory to the list used by SLP.
	     *
	     * @param mixed[] $directories - array of directories.
	     *
	     * @return mixed[]
	     */
	    function add_icon_directory($directories) {
		    $directories = array_merge(
			    $directories,
			    array(
				    array(
					    'dir' => plugin_dir_path(__FILE__)  .  'images/icons/',
					    'url' => plugins_url('',__FILE__)   . '/images/icons/'
				    )
			    )
		    );
		    return $directories;
	    }

	    /**
	     * Called after a store category is inserted or updated in the database.
	     *
	     * Creates an entry in the wp_options table with an option name
	     * based on the category ID and a tagalong prefix like this:
	     *
	     * csl-slplus-TAGALONG-category_14
	     *
	     * The data is serialized.  WordPress update_option() and get_option
	     * will take care of serializing and deserializing our data.
	     *
	     * @param int $term_id - the newly inserted category ID
	     */
	    function create_or_edited_stores($term_id,$ttid) {
		    $this->addon->debugMP('msg','SLPTagalong_Admin::'.__FUNCTION__);

		    if ( $this->isset_POSTCategoryAttribute() ) {

			    $TagalongData = $this->category_attributes;
			    foreach ( $TagalongData as $attribute => $default ) {
				    if ( isset( $_POST[$attribute] ) ) {
					    $TagalongData[$attribute] = $_POST[$attribute];
				    }
			    }

			    update_option( $this->addon->category_meta_option_base .$term_id , $TagalongData );
		    }
	    }

	    /**
	     * Check if any of the special category attributes are present in the form post.
	     * @return bool
	     */
	    function isset_POSTCategoryAttribute() {
	        foreach ( $this->category_attributes as $attribute => $default ) {
		        if ( isset( $_POST[$attribute] ) ) { return true; }
	        }
			return false;
	    }

	    /**
	     * Setup the data helper.
	     */
	    function createobject_Data() {
		    if (class_exists('Tagalong_Data') == false) {
			    require_once(plugin_dir_path(__FILE__).'include/class.data.php');
		    }
		    if (!isset($this->data)) {
			    $this->data = new Tagalong_Data(
				    array(
					    'addon'     => $this,
					    'slplus'    => $this->slplus
				    )
			    );
		    }
	    }

	    /**
         * Simplify the plugin debugMP interface.
         *
         * @param string $type
         * @param string $hdr
         * @param string $msg
         */
        function debugMP($type,$hdr,$msg='') {
            if (($type === 'msg') && ($msg!=='')) {
                $msg = esc_html($msg);
            }
            $this->slplus->debugMP('slp.tag',$type,$hdr,$msg,NULL,NULL,true);
        }

        /**
         * Create a category icon array.
         *
         * **$params values**
         * - **show_label** if true put text under the icons (default: false)
         * - **add_edit_link** if true wrap the output in a link to the category edit page (default: false)
         *
         * **Example**
         * /---code php
         * $this->create_LocationIcons($category_list, array('show_label'=>false, 'add_edit_link'=>false));
         * \---
         *
         * @param mixed[] $categories array of category details
         * @param mixed[] $params the parameters
         * @return string html of the icon array
         */
        function create_LocationIcons($categories,$params = array()) {
            $this->debugMP('msg',__FUNCTION__,'Parameters: ' . print_r($params,true));

            // Make sure all params have defaults
            //
            $params =
                array_merge(
                    array(
                        'show_label'    => false,
                        'add_edit_link' => false,
                    ),
                    $params
                );

            // Now build the image tags for each category
            //
            $locationIcons = '';
            ksort($categories);
            $this->debugMP('pr','',$categories);
            foreach ($categories as $category) {
                $locationIcons .= $this->createstring_CategoryIconHTML($category,$params);
            }

            return $locationIcons;
        }

        //----------------------------------
        // Create String Methods
        //----------------------------------

        /**
         * Create a link to the category editor if warranted.
         *
         * @param int $category_id the category ID
         * @param string $html the HTML output to be wrapped
         * @return string the HTML wrapped in a link to the category editor.
         */
        function createstring_CategoryEditLink($category_id, $html) {
            return
                sprintf(
                    "<a href='%s' title='edit category' alt='edit category'>%s</a>",
                    get_edit_tag_link( $category_id , SLPLUS::locationTaxonomy ),
                    $html
                );
        }

        /**
         * Create the category HTML output for admin and user interface with images and text.
         *
         * **$params values**
         * - **show_label** if true put text under the icons (default: false)
         * - **add_edit_link** if true wrap the output in a link to the category edit page (default: false)
         *
         * **Example**
         * /---code php
         * $this->createstring_CategoryIconHTML($category, array('show_label'=>false, 'add_edit_link'=>false));
         * \---
         *         
         * @param mixed[] $category a taxonomy array
         * @param mixed[] $params the parameters we accept
         * @return string HTML for the category output on UI and admin panels
         */
        function createstring_CategoryIconHTML($category,$params) {
            $this->debugMP('msg',__FUNCTION__,'parameters: '.print_r($params,true));
            $this->debugMP('pr','',$category);

            // Image URL
            //
            $HTML = $this->createstring_CategoryImageHTML($category);

            // Add label?
            //
            if ( $params['show_label'] ) {
                $HTML .= $this->createstring_CategoryLegendText($category);
            }

            // Category Edit Link
            //
            if ( $params['add_edit_link'] ) {
                $HTML =
                    $this->createstring_CategoryEditLink(
                        $category['term_id'],
                        $HTML
                    );
            }

            return $HTML;
        }

        /**
         * Create the image string HTML
         * 
         * @param mixed[] $category a taxonomy array
         * @param string $field_name which category field to get the image from
         *
         * @return string HTML for presenting an image
         */
        function createstring_CategoryImageHTML( $category , $field_name = 'medium-icon' ) {

	        if ( empty( $category[$field_name] ) ) { return ''; }

	        $image_HTML =
		        sprintf(
			        '<img src="%s" alt="%s" title="%s" width="32" height="32">',
			        $category[$field_name],
			        $category['name'],
			        $category['name']
		        );

	        // Wrap the icon in an anchor tag (link) if category_url is specified.
	        //
	        if (
		        ( $field_name === 'medium-icon' ) &&
		        ! empty( $category['category_url'] )
	        ) {
		        $image_HTML =
			        sprintf(
				      '<a href="%s" target="%s" title="%s" alt="%s" class="slp_tagalong_icon">%s</a>',
				      $category['category_url'],
				      $category['url_target'],
				      $category['slug'],
				      $category['slug'],
				      $image_HTML
			        );

	        }

            return $image_HTML;
        }

        /**
         * Create the category title span HTML
         * 
         * @param mixed[] $category a taxonomy array
         * @return string HTML for putting category title in a span
         */
        function createstring_CategoryLegendText($category) {
            return
                sprintf(
                    '<span class="legend_text">%s</span>',
                    $category['name']
                );
        }

        /**
         * Create the icon array for a given location.
         *
         * $params array values:
         *  'show_label' = if true show the labels under the icon strings
         *
         * @param mixed[] $params named array of settings
         * @return string
         */
        function createstring_IconArray( $params = array() ) {
            $this->debugMP('msg',__FUNCTION__,'Parameters: ' . print_r($params,true));

            // Set parameter defaults
            //
            $params =
                array_merge(
                    array(
                        'show_label' => false,
                    ),
                    $params
                );

            // Setup the location categories from the helper table
            //
            $this->set_LocationCategories( $this->current_location_categories );

            // If there are categories assigned to this location...
            //
            if ( count( $this->current_location_categories ) > 0 ) {
                foreach ( $this->current_location_categories as $category_id ) {
                    $category_details = $this->get_TermWithTagalongData( $category_id );
                    $assigned_categories[$category_details['slug']] = $category_details;
                }

                $icon_string = $this->create_LocationIcons($assigned_categories, $params);

            // Make the icon string blank if there are no categories.
            //
            } else {
                $icon_string = '';
            }

            // Return the icon string
            //
            return $icon_string;

        }

	    /**
	     * Add tagalong-specific SQL to base plugin get_SQL command.
	     *
	     * @param string $command The SQL command array
	     * @return string
	     */
	    function filter_AddTagalongSQL($command) {
		    $sql_statement = $this->data->get_SQL($command);
		    return $sql_statement;
	    }

	    /**
	     * Prep the admin class and call it to render the admin pages for Tagalong.
	     */
	    function renderPage_TagList() {
		    $this->admin->render_TagListPage();
	    }

	    /**
	     * Setup the admin menu entries.
	     */
	    function set_admin_menu_entries() {
		    $this->admin_menu_entries =
			    array(
				    array(
					    'label'     => __('Tagalong', 'csa-slp-tagalong'),
					    'slug'      => 'slp_tagalong',
					    'class'     => $this,
					    'function'   => 'renderPage_TagList'
				    )
			    );
	    }

        /**
         * Fill the current_location_categories array with the category IDs assigned to the current location.
         *
         * Assumes slplus->currentLocation is loaded with the current location data.
         */
        function set_LocationCategories() {

            $this->debugMP( 'msg' , 'SLPTagalong::' . __FUNCTION__ . "() location # {$this->slplus->currentLocation->id} ");

            if ( $this->categories_loaded_for == $this->slplus->currentLocation->id ) {
	            $this->debugMP( 'msg' , '' , ' categories already loaded for this location. ' );
	            return;
            }


            // Reset the current location categories
            //
            $this->current_location_categories = array();

            // Get the first record from tagalong helper table
            //
            $location_category = $this->slplus->database->get_Record(
                    'select_categories_for_location',
                    $this->slplus->currentLocation->id,
                    0
            );

            // First record exists, 
            // push category ID onto current_location_categories
            // and loop through other category records,
            // appending to array
            //
            if ( $location_category !== null ) {
                $this->current_location_categories[] = $location_category['term_id'];
                $offset = 1;
                while (
                    ($location_category =
                        $this->slplus->database->get_Record(
                            'select_categories_for_location',
                            $this->slplus->currentLocation->id,
                            $offset++
                            )
                     ) !== null
                ) {
                    $this->current_location_categories[] = $location_category['term_id'];
                }
            } else {
	            $this->debugMP('msg' , '' , 'No category data found.' );
            }

            $this->debugMP('pr','',$this->current_location_categories);

            $this->categories_loaded_for = $this->slplus->currentLocation->id;
        }

        //----------------------------------
        // Filters
        //----------------------------------


        /**
         * Add categories to the location data.
         *
         * @param mixed[] $locationArray
         * @return mixed[]
         */
        function filter_AddCSVExportData($locationArray) {
            $locationArray['category'] = '';
            $offset = 0;
            while ($category = $this->data->get_Record(array('tagalong_selectall','whereslid'),$locationArray['sl_id'],$offset++)) {
                $categoryData = get_term($category['term_id'],'stores');
                if (($categoryData !== null) && !is_wp_error($categoryData)) {
                    $locationArray['category'] .= $categoryData->slug . ',';
                } else {
                    if (is_wp_error($categoryData)) {
                        $locationArray['category'] .= $categoryData->get_error_message() . ',';
                    }
                }
            }
            $locationArray['category'] = preg_replace('/,$/','',$locationArray['category']);
            return $locationArray;
        }

        /**
         * Add the category field to the csv export
         *
         * @param string[] $dbFields
         * @return string[]
         */
        static function filter_AddCSVExportFields($dbFields) {
            return array_merge(
                        $dbFields,
                        array('category')
                    );
        }

        /**
         * Add extended tagalong data to the category array.
         *
         * @param int $term_id the category term id
         * @return mixed[] named array of category attributes
         */
        function get_TermWithTagalongData($term_id) {
            if ( !isset( $this->category_details[$term_id] ) ) {
	            $this->debugMP('msg','SLPTagalong::'.__FUNCTION__,"term id {$term_id}");

	            // Get the WordPress base taxonomy info for this category ID
	            //
                $category_array = get_term_by( 'id' , $term_id , SLPlus::locationTaxonomy, ARRAY_A );
                if ( ! is_array($category_array) ) { $category_array = array(); }

	            // Get Tagalong Custom Meta Info for this category ID
	            //
                $category_options = get_option( $this->category_meta_option_base . $term_id , array() );

	            // Build the complete custom taxonomy category structure.
	            //
	            // array_merge : later entries take precedence
	            //
                $this->category_details[$term_id] = array_merge(
	                $this->category_attributes,
	                $category_options,
	                $category_array
                );

	            $this->debugMP('pr','',$this->category_details[$term_id]);
            }

            return $this->category_details[$term_id];
        }

        /**
         * Create a Map Settings Debug My Plugin panel.
         *
         * @return null
         */
        static function create_DMPPanels() {
            if (!isset($GLOBALS['DebugMyPlugin'])) { return; }
            if (class_exists('DMPPanelSLPTag') == false) {
                require_once(plugin_dir_path(__FILE__).'include/class.dmppanels.php');
            }
            $GLOBALS['DebugMyPlugin']->panels['slp.tag']           = new DMPPanelSLPTag();
        }
	}

    // Hook to invoke the plugin.
    //
    add_action('init'           , array('SLPTagalong','init'                ));
    add_action('dmp_addpanel'   , array('SLPTagalong','create_DMPPanels'    ));

}
// Dad. Explorer. Rum Lover. Code Geek. Not necessarily in that order.

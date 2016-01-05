<?php
if (! class_exists('SLPro_Admin')) {
    require_once(SLPLUS_PLUGINDIR.'/include/base_class.admin.php');
    

    /**
     * Holds the admin-only code.
     *
     * This allows the main plugin to only include this file in admin mode
     * via the admin_menu call.   Reduces the front-end footprint.
     *
     * @package StoreLocatorPlus\SLPPro\Admin
     * @author Lance Cleveland <lance@charlestonsw.com>
     * @copyright 2014 Charleston Software Associates, LLC
     */
    class SLPPro_Admin extends SLP_BaseClass_Admin {

        //-------------------------------------
        // Properties
        //-------------------------------------

        /**
         * @var \SLPPro
         */
        public $addon;
        
        /**
         * Admin Location Filters UI Object
         * 
         * @var \SLPProAdminLocationFilters
         */
        public $admin_location_filters;

        /**
         * List of option keys that are checkboxes show in User Experience.
         *
         * Helps with processing during save of form posts.
         *
         * @var string[] $cb_options_uxtab
         */
        private $cb_options_uxtab = array(
            'csv_first_line_has_field_name' ,
            'csv_skip_first_line'           ,
            'csv_skip_geocoding'            ,
			'highlight_uncoded'				,
			'show_tag_any'					,
			'search_auto_submit'			,
			'use_email_form'
			);

        /**
         * The csvExporter object.
         *
         * @var \CSVExportLocations $csvLocationExporter
         */
        private $csvLocationExporter;

        /**
         * The csvImporter object.
         *
         * @var \CSVImportLocations $csvImporter
         */
        public $csvImporter;

        /**
         * The Pro Pack Settings Object
         *
         * @var wpCSL_settings__slplus $settings
         */
        private $ProPack_Settings;

        /**
         * The Pro Pack settings page slug.
         */
        private $ProPack_SettingsSlug = 'slp_propack';

        /**
         * The reports interface.
         *
         * @var \SLPPro_Admin_Reports
         */
        public $reports;
        
        /**
         * Secret iFrame source URL used to export locations.
         * 
         * @var string
         */
        private $secret_source;


        //-------------------------------------
        // Methods : Base Override
        //-------------------------------------

        /**
         * Admin specific hooks and filters.
         *
         */
        function add_hooks_and_filters() {

            // Admin skinning and scripts
            //
            add_filter('wpcsl_admin_slugs'                      , array($this,'filter_AddOurAdminSlug'      )    );
            add_action('admin_enqueue_scripts'                  , array($this, 'action_EnqueueAdminScripts' )    );

            // Manage Locations UI
            //
            add_filter('slp_build_locations_panels'             ,array($this,'action_AddLocationsPanels'                    ) ,30       );

            add_filter('slp_locations_subtabs'                  ,array($this,'filter_AddLocationsTabs'                      )           );
            add_filter('slp_locations_manage_bulkactions'       ,array($this,'filter_LocationsBulkAction'                   )           );
            add_filter('slp_locations_manage_filters'           ,array($this,'filter_LocationsFilters'                      )           );
            add_filter( 'slp_invalid_highlight'                 , array( $this , 'filter_HighlightUncoded'                  ), 10 , 1   );

            // Manage Location Fields
            // - tweak the add/edit form
            // - tweak the manage locations column headers
            // - tweak the manage locations column data
            //
            add_filter('slp_edit_location_right_column'         ,array($this,'filter_AddFieldsToEditForm'                   ),11   );
            add_filter('slp_manage_expanded_location_columns'   ,array($this,'filter_AddFieldHeadersToManageLocations'      )      );
            add_filter('slp_column_data'                        ,array($this,'filter_AddFieldDataToManageLocations'         ),90, 3);

            // Manage Locations Processing
            //
            add_action('slp_manage_locations_action'            ,array($this,'action_ManageLocationsProcessing'));
            
            // Map Settings Page
            //
            add_filter('slp_map_settings_searchform'            ,array($this,'filter_MapSettings_AddTagsBox'                )      );
            add_filter('slp_settings_results_locationinfo'      ,array($this,'filter_MapResults_LocationAddSettings'        )      );

            // UX tab : View section
            //
            add_action('slp_uxsettings_modify_viewpanel'        ,array($this,'action_AddUxViewFeatures'                     ),11,2 );

            // General Settings tab : Google section
            //
            add_action('slp_generalsettings_modify_userpanel'   ,array($this,'action_AddUserSettings'                       ),11,2  );
            add_filter('slp_save_general_settings_checkboxes'   ,array($this,'filter_SaveGeneralCBSettings'                 )       );
            add_action('slp_generalsettings_modify_adminpanel'  ,array($this,'action_AddAdminSettings'                      ),11,2  );
            add_action('slp_save_generalsettings'               ,array($this,'action_SaveGeneralSettings'                   )       );

            // User Experience tab : Save Settings
            //
            add_action('slp_save_map_settings'                  ,array($this,'filter_SaveSerialData'                        ),  9   );
            add_filter('slp_save_map_settings_checkboxes'       ,array($this,'filter_SaveMapCBSettings'                     )       );
            add_filter('slp_save_map_settings_inputs'           ,array($this,'filter_SaveMapInputSettings'                  )       );
        }


        /**
         * Execute some admin startup things for this add-on pack.
         */
        function do_admin_startup() {
            $this->check_for_updates();
            $this->update_install_info();

            $this->createobject_Reports();
        }


        //-------------------------------------
        // Methods : Custom : Actions
        //-------------------------------------

        /**
         * Enqueue the admin scripts.
         *
         * @param string $hook
         */
        function action_EnqueueAdminScripts($hook) {
            $styleHandle = '';
            $scriptData = array(
                'plugin_url' => SLPLUS_PLUGINURL,
                'core_url' => SLPLUS_COREURL,
            );

            switch ($hook) {
                case SLP_ADMIN_PAGEPRE . 'slp_reports':
                    $scriptData['message_nodata'] =
                        __('No data recorded yet. ','csa-slp-pro');
                    $scriptData['message_chartaftersearch'] =
                        __('Chart will be available after a Store Locator Plus search has been performed.','csa-slp-pro');
                    $scriptData['total_searches'] = $this->reports->data->total_searches;
                    $scriptData['count_dataset']  = $this->reports->data->counts_dataset;
                    $scriptData['chart_type']     = $this->reports->data->google_chart_type;

                    wp_enqueue_script('google_jsapi'        , 'https://www.google.com/jsapi'                                );
                    wp_enqueue_script('jquery_tablesorter'  , $this->addon->url . '/jquery.tablesorter.js', array('jquery') );

                    wp_enqueue_script ( 'slp_reporting' , $this->addon->url . '/reporting.js' );
                    wp_localize_script( 'slp_reporting' , 'slp_pro' , $scriptData );

                    $styleHandle = 'slp_pro_style';

                    break;

                case SLP_ADMIN_PAGEPRE . 'slp_manage_locations':
                    $styleHandle = 'slp_pro_style';
                    break;


                default:
                    break;
            }

            // Load up the admin.css style sheet for Pro Pack
            //
            if ($styleHandle !== '') {
                wp_register_style('slp_pro_style', $this->addon->url . '/css/pro_admin.css');
                wp_enqueue_style('slp_pro_style');
                wp_enqueue_style($this->slplus->AdminUI->styleHandle);
            }
        }


        /**
         * Add items to the General Settings tab : Admin section
         *
         * @param \SLPlus_AdminUI_GeneralSettings $settings
         * @param string $sectName
         */
        function action_AddAdminSettings($settings,$sectName) {
            $settings->add_ItemToGroup(
                    array(
                        'section'       => $sectName    ,
                        'group'         => __('Locations','csa-slp-pro') ,
                        'type'          => 'subheader'  ,
                        'label'         => $this->addon->name ,
                        'show_label'    => false
                    )
            );
            $settings->add_ItemToGroup(
                array(
                    'section'       => $sectName                                    ,
                    'group'         => __('Locations'         ,'csa-slp-pro')       ,
                    'label'         => __('Highlight Uncoded' ,'csa-slp-pro')       ,
                    'type'          => 'checkbox'                                   ,
                    'setting'       => 'highlight_uncoded'                          ,
                    'value'         => $this->addon->options['highlight_uncoded']   ,
                    'use_prefix'    => false                                        ,
                    'description'   =>
                        __('Highlight the uncoded locations in the Manage Locations panel.','csa-slp-pro')
                )
            );
        }

        /**
         * Add the import/export panels to the Locations tab.
         */
        function action_AddLocationsPanels() {

            // Do not allow users to import/export unless they have the manage_slp role.
            //
            if ( ! current_user_can('manage_slp_admin') ) { return; }
            
            $this->create_AdminLocationFilters();

            $this->create_CSVLocationImporter();
            $this->csvImporter->create_BulkUploadForm();
        }

        /**
         * Add items to the General Settings tab : Google section
         *
         * @param \SLPlus_AdminUI_GeneralSettings $settings
         * @param string $sectName
         */
        function action_AddUserSettings($settings,$sectName) {
            $settings->add_ItemToGroup(
                    array(
                        'section'       => $sectName                        ,
                        'group'         => __('JavaScript','csa-slp-pro')   ,
                        'type'          => 'subheader'                      ,
                        'label'         => $this->addon->name               ,
                        'show_label'    => false
                    ));
            $settings->add_ItemToGroup(
                    array(
                        'section'       => $sectName                                ,
                        'group'         => __('JavaScript','csa-slp-pro')           ,
                        'type'          => 'slider'                                 ,
                        'label'         => __('Use location sensor', 'csa-slp-pro') ,
                        'setting'       => 'use_location_sensor'                    ,
                        'separator'     => '_'                                      ,
                        'description'   =>
                            __('This turns on the location sensor (GPS) to set the default search address. ','csa-slp-pro').
                            __('This can be slow to load and customers are prompted whether or not to allow location sensing.', 'csa-slp-pro')
                    ));
        }

        /**
         * Add items to the UX tab : View section : Features panel
         *
         * @param string $HTML
         * @param $sectName
         * @return string modified HTML
         */
        function action_AddUxViewFeatures($settings,$sectName) {
            $settings->add_ItemToGroup(
                    array(
                        'section'       => $sectName            ,
                        'group'         => 'Page Layout'        ,
                        'type'          => 'subheader'          ,
                        'label'         => $this->addon->name   ,
                        'show_label'    => false
                    ));
            $settings->add_ItemToGroup(
                    array(
                        'section'       => $sectName        ,
                        'group'         => 'Page Layout'    ,
                        'type'          => 'textarea'       ,
                        'setting'       => 'layout'         ,
                        'value'         => (empty($this->addon->options['layout'])?$this->slplus->defaults['layout']:$this->addon->options['layout']),
                        'label'         => __('Locator Layout', 'csa-slp-pro'),
                        'description'   => __('Enter your custom page layout for the Store Locator Plus page. Set it to blank to reset to the default layout.','csa-slp-pro')
                    ));
            $settings->add_ItemToGroup(
                array(
                    'section'       => $sectName                        ,
                    'group'         => __('CSS Manipulation','csa-slp-pro'),
                    'label'         => __('Custom CSS','csa-slp-pro')   ,
                    'setting'       => 'custom_css'                     ,
                    'type'          => 'textarea'                       ,
                    'value'         => $this->addon->options['custom_css'] ,
                    'description'   =>
                        __('Enter your custom CSS, preferably for SLPLUS styling only but it can be used for any page element as this will go in your page header.','csa-slp-pro')
                )
            );
        }

        /**
         * Additional location processing on manage locations admin page.
         *
         */
        function action_ManageLocationsProcessing() {
            switch ($_REQUEST['act']) {
                
                // Export Locations To CSV
                // Also filter by property
                //
                case 'export':
                    $this->do_LocationExport();
                    $this->create_AdminLocationFilters();                              
                    add_action('slp_manage_location_where', array($this,'action_ManageLocations_ByProperty') );                                         
                    break;                

                // Import a CSV File
                //
                case 'import':

                    // Save the option check boxes in permanent store.
                    //
                    $this->addon->options =
                        $this->slplus->AdminUI->save_SerializedOption(
                            $this->addon->option_name,
                            $this->addon->options,
                            $this->cb_options_uxtab
                            );

                    // Setup the CSV Import Object
                    //
                    $this->create_CSVLocationImporter();

                    // Process the CSV File
                    //
                    $this->csvImporter->set_FileMeta();
                    $this->csvImporter->process_File( $this->csvImporter->file_meta );
                    
                    break;

                // Add tags to locations
                case 'add_tag':
                    if (isset($_REQUEST['sl_id'])) { $this->tag_Locations('add'); }
                    break;

                // Remove tags from locations
                case 'remove_tag':
                    if (isset($_REQUEST['sl_id'])) { $this->tag_Locations('remove'); }
                    break;
                    

                // Recode The Address
                case 'recode':
                    $this->slplus->notifications->delete_all_notices();
                    if (isset($_REQUEST['sl_id'])) {
                        $theLocations =
                            (!is_array($_REQUEST['sl_id']))         ?
                                array($_REQUEST['sl_id'])           :
                                $theLocations = $_REQUEST['sl_id']  ;

                        // Process SL_ID Array
                        // TODO: use where clause in database property
                        //
                        //
                        foreach ($theLocations as $locationID) {
                            $this->slplus->currentLocation->set_PropertiesViaDB($locationID);
                            $this->slplus->AdminUI->ManageLocations->do_geocoding();
                            if ($this->slplus->currentLocation->dataChanged) {
                                $this->slplus->currentLocation->MakePersistent();
                            }
                        }
                        $this->slplus->notifications->display();
                    }
                    break;                    
                
                // Filter to show uncoded locations only.
                case 'show_uncoded':
                    add_action('slp_manage_location_where', array($this,'action_ManageLocations_WhereUncoded') ); 
                    break;

                // Filter with specific location properties
                case 'add':
                case 'save':
                case 'filter_by_property':
                    $this->create_AdminLocationFilters();                              
                    add_action('slp_manage_location_where', array($this,'action_ManageLocations_ByProperty') );                      
                    add_filter( 'slp_manage_locations_actionbar_ui' , array( $this->admin_location_filters , 'createstring_FilterDisplay' ) );
                    break;
                
                // Reset on show_all
                case 'show_all':
                    $this->create_AdminLocationFilters();                              
                    $this->admin_location_filters->reset();
                    break;
                
                default:
                    break;
            }
        }
        

        /**
         * Setup the show uncoded fitler for manage locations.
         *
         * @param string $where
         * @return string
         */
        function action_ManageLocations_WhereUncoded($where) {
            return  
                $where .
                ( empty($where) ? '' : ' AND ' ) .
                ' (NOT (' . $this->slplus->database->filter_SetWhereValidLatLong('') . ') ' .
                "or sl_latitude IS NULL or sl_longitude IS NULL)"
                ;
        }       
        
        /**
         * Filter the manage locations list by property matches in the filter form.
         * 
         * @param string $where
         * @return string
         */
        function action_ManageLocations_ByProperty($where) {
            $this->addon->debugMP('msg','SLPPro_Admin::'.__FUNCTION__);            
            
            // Get the SQL command and where params
            //
            list($sqlCommands, $sqlParams) = $this->admin_location_filters->create_LocationSQLCommand( $this->create_LocationFilterInputArray() );            
            $whereclause = $this->slplus->database->get_SQL('where_default');
            if ( strpos( $whereclause, '%' ) !== false ) {
                $whereclause = $this->slplus->database->db->prepare( $whereclause , $sqlParams );
            }       
            $whereclause = preg_replace('/^\s+WHERE /i','',$whereclause);
            
            return $whereclause;
        }

        /**
         * Save general settings.
         *
         * We need this instead of just calling data_SaveOptions as we want to screen $_REQUEST for known checkboxes.
         * Checkboxes are evil and are NOT passed in the input stream if they are off.  WTF.   A decade of coding around a
         * bad design decision made in HTML and parameter passing... you think someone would have addressed this by now.
         */
        function action_SaveGeneralSettings() {
            foreach (array('highlight_uncoded') as $checkbox) {
                $_REQUEST[$checkbox] = (isset($_REQUEST[$checkbox])&&!empty($_REQUEST[$checkbox]))?1:0;
            }
            $this->data_SaveOptions($this->cb_options_uxtab);
        }

        /**
         * Create and attach the \CSVExportLocations object
         */
        function create_AdminLocationFilters() {
            if (!class_exists('SLPProAdminLocationFilters')) {
                require_once('class.admin.location.filters.php');
            }
            if (!isset($this->admin_location_filters)) {         
                $this->admin_location_filters = 
                    new SLPProAdminLocationFilters( array(
                        'addon'     =>  $this->addon,
                        'slplus'    =>  $this->slplus
                    ));
            }
        }        

        /**
         * Create and attach the \CSVImportLocations object
         */
        function create_CSVLocationImporter() {
            if (!class_exists('CSVImportLocations')) {
                require_once('class.csvimport.locations.php');
            }
            if (!isset($this->csvImporter)) {
                $this->csvImporter =
                    new CSVImportLocations(
                        array(
                            'addon'    => $this->addon   ,
                            'slplus'    => $this->slplus
                        )
                    );
            }
        }
        
        /**
         * Create an array of location filter inputs from $_REQUEST.
         * 
         * @return string[] named array
         */
        function create_LocationFilterInputArray() {
            $formdata_array = array();
            $location_filter_inputs = 
                array(
                'name'              ,
                'state_filter'      ,
                'state_joiner'      ,
                'zip_filter'        ,
                'zip_filter_joiner' ,
                'country_joiner'    ,
                'country_filter'    
                );  
            foreach ($location_filter_inputs as $input) {
                if ( ! empty( $_REQUEST[$input] ) ) { 
                    $formdata_array[$input] = $_REQUEST[$input];
                }
            }
            return $formdata_array;
        }

        /**
         * Create the reports interface object and attach to this->reports
         */
        function createobject_Reports() {
            if ( !isset( $this->reports ) ) {
                require_once($this->addon->dir . 'include/class.admin.reports.php');
                $this->reports = new SLPPro_Admin_Reports(
                    array(
                        'addon'     => $this->addon,
                        'slplus'    => $this->slplus,
                    )
                );
            }
        }
        
        /**
         * Create the filter by properties div.
         * 
         * @return string
         */
        function createstring_FilterByPropertiesDiv() {
            $this->create_AdminLocationFilters();            
            $HTML = 
                "<iframe id='secretIFrame' src='{$this->secret_source}' style='display:none; visibility:hidden;'></iframe>" .                                            
                '<div id="extra_filter_by_property" class="filter_extras">'.
                    $this->admin_location_filters->createstring_LocationFilterForm().
                '</div>'                            
                ;
                
            return $HTML;
        }
   
        /**
		 * Save the Pro Pack options to the database in serialized format.
		 *
		 * Make sure the options are valid first.
         *
         * @param string[] $cbArray The checkbox array need to handle for the null to false operation
         */
        function data_SaveOptions($cbArray) {
            $this->addon->debugMP('msg','SLPPro_Admin::'.__FUNCTION__);
            $_POST[$this->addon->option_name] = array();
            array_walk($_REQUEST,array($this,'set_ValidOptions'));

            // AdminUI->save_SerializedOption stores the $_POST[<option_name>] values plus the checkbox options (3rd param)
            // in the persistent store of wp_options
            //
            $this->options =
                $this->slplus->AdminUI->save_SerializedOption(
                    $this->addon->option_name,
                    $this->addon->options,
                    $cbArray
                    );

            $this->addon->init_options();
        }

        /**
         * Export locations to CSV.
         * 
         * Sets up the Secret iFrame source that will fire off an AJAX request to fetch locations.
         */
        function do_LocationExport() {
            $this->addon->debugMP('msg','SLPPro_Admin::'.__FUNCTION__);
            
            $query_array = 
                array_merge(
                    $this->create_LocationFilterInputArray() ,
                    array(
                        'action' => 'slp_download_locations_csv',
                        'filename' => 'locations',                        
                    )                        
                );
                        
            $this->secret_source = 
                admin_url('admin-ajax.php') . '?' . 
                build_query( $query_array )
                ;
            
            $this->addon->debugMP('msg','',$this->secret_source);
        }
                
        /**
         * Render the extra fields on the manage location table.
         *
         * SLP Filter: slp_column_data
         *
         * @param string $theData  - the option_value field data from the database
         * @param string $theField - the name of the field from the database (should be sl_option_value)
         * @param string $theLabel - the column label for this column (should be 'Categories')
         * @return type
         */
        function filter_AddFieldDataToManageLocations($theData,$theField,$theLabel) {
            if (
                ($theField === 'sl_tags') &&
                ($theLabel === __('Tags'        ,'csa-slp-pro'))
               ) {
                $theData =($this->slplus->currentLocation->tags!='')?
                    $this->slplus->currentLocation->tags :
                    "" ;
            }
            return $theData;
        }

        /**
         * Add the images column header to the manage locations table.
         *
         * SLP Filter: slp_manage_location_columns
         *
         * @param mixed[] $currentCols column name + column label for existing items
         * @return mixed[] column name + column labels, extended with our extra fields data
         */
        function filter_AddFieldHeadersToManageLocations($currentCols) {
            $this->addon->debugMP('msg','SLPPro_Admin::'.__FUNCTION__);
            return array_merge($currentCols,
                    array(
                        'sl_tags'       => __('Tags'     ,'csa-slp-pro'),
                    )
                );
        }

        /**
         * Add extra fields that show in results output to the edit form.
         *
         * SLP Filter: slp_edit_location_right_column
         *
         * @param string $theForm the original HTML form for the manage locations edit (right side)
         * @return string the modified HTML form
         */
        function filter_AddFieldsToEditForm($theHTML) {
            return
                $theHTML .
                '<div id="slp_pro_fields" class="slp_editform_section">'.
                $this->slplus->helper->create_SubheadingLabel($this->addon->name) .
                $this->slplus->AdminUI->ManageLocations->createstring_InputElement(
                        'tags',
                        __("Tags (separate with commas)", 'csa-slp-pro'),
                        $this->slplus->currentLocation->tags
                        ) .
                '</div>'
                ;
        }

        /**
         * Add bulk upload tab to the manage locations tabs.
         *
         * @param string[] $tabs
         * @return string[] array with our tab added
         */
        function filter_AddLocationsTabs($tabs) {
            $this->addon->debugMP('msg','SLPPro_Admin'.__FUNCTION__);

            // Do not allow users to import/export unless they have the manage_slp role.
            //
            if ( ! current_user_can('manage_slp_admin') ) { return $tabs; }

            return array_merge($tabs,
                        array(
                            __('Import','csa-slp-pro'),
                            __('Export','csa-slp-pro')
                        )
                    );
        }
        /**
         * Add our admin pages to the valid admin page slugs.
         *
         * @param string[] $slugs admin page slugs
         * @return string[] modified list of admin page slugs
         */
        function filter_AddOurAdminSlug($slugs) {
            return array_merge($slugs,
                    array(
                        SLP_ADMIN_PAGEPRE.'slp_propack',
                        SLP_ADMIN_PAGEPRE.'slp_reports'
                        )
                    );
        }

        /**
         * Add more actions to the Bulk Action drop down on the admin Locations/Manage Locations interface.
         *
         * @param mixed[] $items
         * @return mixed[]
         */
        function filter_LocationsBulkAction($items) {
            return
                array_merge(
                    $items,
                    array(
                        array(
                            'label'     =>  __('Export All To CSV','csa-slp-pro')   ,
                            'value'     => 'export'                             ,
                        ),
                        array(
                            'label'     =>  __('Geocode','csa-slp-pro')         ,
                            'value'     => 'recode'                             ,
                        ),
                        array(
                            'label'     =>  __('Tag','csa-slp-pro')             ,
                            'value'     => 'add_tag'                            ,
                            'extras'    =>
                                '<div id="extra_add_tag" class="bulk_extras">'.
                                    '<label for="sl_tags">'.__('Enter your comma-separated tags:','csa-slp-pro').'</label>'.
                                    '<input name="sl_tags">'.
                                '</div>'
                        ),
                        array(
                            'label'     =>  __('Remove Tags','csa-slp-pro') ,
                            'value'     => 'remove_tag'                     ,
                        ),
                    )
                );
        }

        /**
         * Add more actions to the Bulk Action drop down on the admin Locations/Manage Locations interface.
         *
         * @param mixed[] $items
         * @return mixed[]
         */
        function filter_LocationsFilters($items) {
            return
                array_merge(
                    $items,
                    array(
                        array(
                            'label'     =>  __('Show Uncoded','csa-slp-pro')  ,
                            'value'     => 'show_uncoded'                     ,
                        ),
                        array(
                            'label'     => __('With These Properties', 'csa-slp-pro'),
                            'value'     => 'filter_by_property'                      ,
                            'extras'    => $this->createstring_FilterByPropertiesDiv()
                        )
                    )
                );
        }

        /**
         * Add the input settings to be saved on the map settings page.
         *
         * @param string[] $inArray names of inputs already to be saved
         * @return string[] modified array with our Pro Pack inputs added.
         */
        function filter_SaveMapInputSettings($inArray) {


            $this->data_SaveOptions($this->cb_options_uxtab);

            return array_merge($inArray,
                    array(
                            SLPLUS_PREFIX.'_search_tag_label'       ,
                            SLPLUS_PREFIX.'_tag_pulldown_first'     ,
                            SLPLUS_PREFIX.'_tag_search_selections'  ,
                        )
                    );
        }

        /**
         * This is adding some settings to the Map Settings / Results / Location Info Panel
         */
        function filter_MapResults_LocationAddSettings($HTML) {

            // Tag pre-processor items.
            //
           $dropdownItems = array(
                array(
                    'label' => 'As Entered',
                    'value' => 'as_entered'
                ),
                array(
                    'label' => 'Hide Tags',
                    'value' => 'hide'
                ),
                array(
                    'label' => 'On Separate Lines',
                    'value' => 'replace_with_br'
                ),
           );

          return $HTML .

                $this->slplus->helper->create_SubheadingLabel($this->addon->name) .

                // Tags preprocessor setting.
                //
                $this->slplus->helper->createstring_DropDownDiv(
                       array(
                           'label'      => __('Show Tags In Output','csa-slp-pro') ,
                           'helptext'   => __('How should tags be output in the results below the map and the info bubble?','csa-slp-pro'),
                           'selectedVal'=> $this->addon->options['tag_output_processing'],
                           'id'         => $this->addon->option_name . '[tag_output_processing]',
                           'name'       => $this->addon->option_name . '[tag_output_processing]',
                           'items'      => $dropdownItems
                       )
                    )                     .

                $this->slplus->helper->CreateCheckboxDiv(
                    '_use_email_form',
                    __('Use Email Form','csa-slp-pro'),
                    __('Use email form instead of mailto: link when showing email addresses.', 'csa-slp-pro')
                    )
                ;
        }

        /**
         * Add tags box to the search form section of map settings.
         *
         * @param string $html starting html
         * @return string modified HTML
         */
        function filter_MapSettings_AddTagsBox($html) {
            $this->addon->debugMP('msg','SLPPro_Admin::'.__FUNCTION__);
            $html .=
                "<div class='section_column'>" .
                '<h2>'.$this->addon->name.'</h2>' .
                '<div class="section_column_content">' .
                $this->slplus->helper->createstring_DropDownDiv(
                       array(
                           'label'      => __('Search Form Tag Input', 'csa-slp-pro'),
                           'helptext'   => __('Select the type of tag input that you would like to see on the search form.','csa-slp-pro'),
                           'selectedVal'=> $this->addon->options['tag_selector'],
                           'id'         => $this->addon->option_name . '[tag_selector]',
                           'name'       => $this->addon->option_name . '[tag_selector]',
                           'items'      => array(
                                    array(
                                        'label' => __('None'         ,'csa-slp-pro'),
                                        'value' => 'none'
                                        ),
                                    array(
                                        'label' =>  __('Hidden'       ,'csa-slp-pro'),
                                        'value' => 'hidden'
                                        ),
                                    array(
                                        'label' => __('Drop Down'    ,'csa-slp-pro'),
                                        'value' => 'dropdown'
                                        ),
                                    array(
                                        'label' => __('Radio Button'    ,'csa-slp-pro'),
                                        'value' => 'radiobutton'
                                        ),
                                    array(
                                        'label' => __('Text Input'   ,'csa-slp-pro'),
                                        'value' => 'textinput'
                                        )
                                )
                       )
                    ).
                $this->slplus->AdminUI->MapSettings->CreateInputDiv(
                    '_search_tag_label',
                    __('Tags Label', 'csa-slp-pro'),
                    __('Search form label to prefix the tag selector.','csa-slp-pro')
                    ) .
                $this->slplus->AdminUI->MapSettings->CreateInputDiv(
                    '-PRO-options[tag_selections]',
                    __('Default Tag Selections', 'csa-slp-pro'),
                        __('For Hidden or Text tag input enter a default value to be used in the field, if any. ','csa-slp-pro') .
                        __('For Drop Down tag input enter a comma (,) separated list of tags to show in the search pulldown, mark the default selection with parenthesis (). ','csa-slp-pro') .
                        __('This is a default setting that can be overriden on each page within the shortcode.', 'csa-slp-pro')
                    ) .
                $this->slplus->helper->CreateCheckboxDiv(
                    '_show_tag_any',
                    __('Add "any" To Drop Down','csa-slp-pro'),
                    __('Add an "any" selection on the tag drop down list thus allowing the user to show all locations in the area, not just those matching a selected tag.', 'csa-slp-pro')
                    ).
                $this->slplus->AdminUI->MapSettings->CreateInputDiv(
                        '_tag_pulldown_first',
                        __('"Any" Tag Text', 'csa-slp-pro'),
                        __('What should the "any" tag say? ','csa-slp-pro').
                            __('The first entry on the search by tag pulldown.','csa-slp-pro')
                        ).
                $this->slplus->helper->CreateCheckboxDiv(
                    '-enhanced_search_auto_submit',
                    __('Tag Autosubmit','csa-slp-pro'),
                    __('Force the form to auto-submit when the tag is selected with a radio button.', 'csa-slp-pro')
                    ) .
               '</div></div>';
            return $html;
        }

        /**
         * Save the General Settings tab checkboxes.
         *
         * @param mixed[] $cbArray
         * @return mixed[]
         */
        function filter_SaveGeneralCBSettings($cbArray) {
            $this->addon->debugMP('msg','SLPPro_Admin::'.__FUNCTION__);
            return array_merge($cbArray,
                    array(
                            SLPLUS_PREFIX.'_use_location_sensor',
                        )
                    );
        }

        /**
         * Save our Pro Pack checkboxes from the map settings page.
         *
         * @param string[] $cbArray array of checkbox names to be saved
         * @return string[] augmented list of inputs to save
         */
        function filter_SaveMapCBSettings($cbArray) {
            return array_merge($cbArray,
                    array(
                            SLPLUS_PREFIX.'_show_tag_any'       ,
                            SLPLUS_PREFIX.'-enhanced_search_auto_submit'
                        )
                    );
        }

        /**
         * Save the serialized data an drop down to the database.
         */
        function filter_SaveSerialData() {
            $this->addon->options =
                $this->slplus->AdminUI->save_SerializedOption(
                    $this->addon->option_name,
                    $this->addon->options,
                    $this->cb_options_uxtab
                    );
            $this->slplus->initOptions(true);
        }

        /**
         * Set valid options from the incoming REQUEST
         *
         * @param mixed $val - the value of a form var
         * @param string $key - the key for that form var
         */
		function set_ValidOptions($val,$key) {
			$simpleKey = str_replace($this->slplus->prefix.'-','',$key);

            if (array_key_exists($simpleKey, $this->addon->options)) {
                $_POST[$this->addon->option_name][$simpleKey] = stripslashes_deep($val);
            }
         }

        /**
         * Remove the invalid class from non-geocoded locations if highlight uncoded is off.
         *
         * @param $class_string
         * @return mixed
         */
        function filter_HighlightUncoded( $class_string ) {
            if ( ! $this->slplus->is_CheckTrue( $this->addon->options['highlight_uncoded'] ) ) {
                $class_string = str_replace(' invalid','',$class_string);
            }
            return $class_string;
        }

    /**
     * Tag a location
     *
     * @param string $action = add or remove
     */
    function tag_Locations($action) {
        global $wpdb;

        //adding or removing tags for specified a locations
        //
        if (is_array($_REQUEST['sl_id'])) {
            $id_string='';
            foreach ($_REQUEST['sl_id'] as $sl_value) {
                $id_string.="$sl_value,";
            }
            $id_string=substr($id_string, 0, strlen($id_string)-1);
        } else {
            $id_string=$_REQUEST['sl_id'];
        }

        // If we have some store IDs
        //
        if ($id_string != '') {

            //adding tags
            if ( $action === 'add' ) {
                $wpdb->query("UPDATE ".$wpdb->prefix."store_locator SET ".
                        "sl_tags=CONCAT_WS(',',sl_tags,'".$_REQUEST['sl_tags']."') ".
                        "WHERE sl_id IN ($id_string)"
                        );

            //removing tags
            } elseif ( $action === 'remove' ) {
                if (empty($_REQUEST['sl_tags'])) {
                    //if no tag is specified, all tags will be removed from selected locations
                    $wpdb->query("UPDATE ".$wpdb->prefix."store_locator SET sl_tags='' WHERE sl_id IN ($id_string)");
                } else {
                    $wpdb->query("UPDATE ".$wpdb->prefix."store_locator SET sl_tags=REPLACE(sl_tags, ',{$_REQUEST['sl_tags']},', '') WHERE sl_id IN ($id_string)");
                }
            }
        }
    }
  }
}
<?php
if (! class_exists('SLPEnhancedResults_Admin')) {
    require_once(SLPLUS_PLUGINDIR.'/include/base_class.admin.php');

    /**
     * Holds the admin-only code.
     *
     * This allows the main plugin to only include this file in admin mode
     * via the admin_menu call.   Reduces the front-end footprint.
     *
     * @package StoreLocatorPlus\SLPEnhancedResults\Admin
     * @author Lance Cleveland <lance@charlestonsw.com>
     * @copyright 2014 Charleston Software Associates, LLC
     */
    class SLPEnhancedResults_Admin extends SLP_BaseClass_Admin {

        /**
         * List of option keys that are checkboxes.
         *
         * Helps with processing during save of form posts.
         *
         * @var string[] $admin_checkboxes
         */
        public $admin_checkboxes = array (
            'add_tel_to_phone'          ,
            'disable_initial_directory' ,
            'hide_distance'             ,
            'show_country'              ,
            'show_hours'                ,
        );

	    /**
	     * @var SLPER_Admin_UXSettings $experience
	     */
	    private $experience;

        //-------------------------------------
        // Methods : Base Override
        //-------------------------------------

        /**
         * Admin specific hooks and filters.
         *
         */
        function add_hooks_and_filters() {
            // Admin : Locations
            //
            add_filter('slp_locations_manage_cssclass'      ,array($this,'filter_HighlightFeatured'                     )           );
            
            // Pro Pack Imports
            //
            add_filter('slp_csv_locationdata_added'         ,array($this,'filter_CSVImportLocationFeatures'             ), 90 , 2   );

            // Admin : User Experience
            //
	        add_action( 'slp_ux_modify_adminpanel_results'  , array( $this , 'add_experience_results_enhancements'     ) , 20 , 2  );
            add_filter('slp_settings_results_labels'        ,array($this,'filter_AddLabelSettings'                      )           );

            // Data Saving
            //
            add_filter('slp_save_map_settings_inputs'       ,array($this,'filter_SaveMapInputSettings'                  )           );

            // Location bulk action
            //
            add_filter('slp_locations_manage_bulkactions'   ,array($this,'filter_LocationsBulkAction'                   )           );
            add_action('slp_manage_locations_action'        ,array($this,'action_ManageLocationsProcessing'             )           );
        }

        //-------------------------------------
        // Methods : Custom : String Creation
        //-------------------------------------

	    /**
	     * Add Experience / Results settings.
	     *
	     * @param SLP_Settings $settings
	     * @param string $section_name
	     */
        function add_experience_results_enhancements( $settings , $section_name ) {
	        $this->create_object_admin_ux();
	        $this->experience->add_results_enhancements( $settings , $section_name );
        }

	    /**
	     * Create and attach the user experience object.
	     */
	    private function create_object_admin_ux() {
		    if ( ! isset( $this->experience ) ) {
			    require_once('class.admin.experience.php');
			    $this->experience = new SLPER_Admin_ExperienceSettings(array( 'addon' => $this->addon ) );
		    }
	    }

        //-------------------------------------
        // Methods : Custom : Filters
        //-------------------------------------

        /**
         * Add more settings to the Result / Label section of Map Setings.
         *
         * @param string $HTML the initial form HTML
         * @return string the modified form HTML
         */
        function filter_AddLabelSettings($HTML) {
            return
                $HTML .

                '<strong>'.$this->addon->name.'</strong><br/>'.

                // TODO: serialize this variable into options or options_nojs
                //
                $this->slplus->AdminUI->Experience->CreateInputDiv(
                        '_message_noresultsfound',
                        __('No Results Message', 'csa-slp-er'),
                        __('No results found message that appears under the map.','csa-slp-er'),
                        SLPLUS_PREFIX,
                        __('No results found.','csa-slp-er')
                        ) .

                // TODO: serialize this variable into options or options_nojs
                //
                $this->slplus->AdminUI->Experience->CreateInputDiv(
                    'popup_email_title',
                    __('Popup Email Title', 'csa-slp-er'),
                    __('The title on the popup email dialogue form.','csa-slp-er'),
                    '',
                    $this->addon->options_defaults['popup_email_title']
                ) .

                // TODO: serialize this variable into options or options_nojs
                //
                $this->slplus->AdminUI->Experience->CreateInputDiv(
                    'popup_email_from_placeholder',
                    __('Popup From Placeholder', 'csa-slp-er'),
                    __('The placeholder for the from field on the popup email dialogue form.','csa-slp-er'),
                    '',
                    $this->addon->options_defaults['popup_email_from_placeholder']
                ) .

                // TODO: serialize this variable into options or options_nojs
                //
                $this->slplus->AdminUI->Experience->CreateInputDiv(
                    'popup_email_subject_placeholder',
                    __('Popup Subject Placeholder', 'csa-slp-er'),
                    __('The placeholder for the subject field on the popup email dialogue form.','csa-slp-er'),
                    '',
                    $this->addon->options_defaults['popup_email_subject_placeholder']
                ) .

                // TODO: serialize this variable into options or options_nojs
                //
                $this->slplus->AdminUI->Experience->CreateInputDiv(
                    'popup_email_message_placeholder',
                    __('Popup Message Placeholder', 'csa-slp-er'),
                    __('The placeholder for the message box on the popup email dialogue form.','csa-slp-er'),
                    '',
                    $this->addon->options_defaults['popup_email_message_placeholder']
                );
        }

        /**
         * Process incoming CSV import data and add our extended field attributes.
         *
         * note: CSV import field names always get the sl_ prefixed.
         *
         * @param mixed[] $locationData
         * @param string $result
         * @return the original data, unchanged
         */
        function filter_CSVImportLocationFeatures($locationData, $result) {
            $newData = array();
            $extended_data_fields = array('featured', 'rank');
            foreach ($extended_data_fields as $field) {
                if ( isset($locationData['sl_'.$field]) ) { $newData[$field] = $locationData['sl_'.$field]; }
            }
            if ( count( $newData ) > 0 ) {
                $this->slplus->database->extension->update_data(
                    $this->slplus->currentLocation->id,
                    $newData
                 );
            }
            return array($locationData,$result);
        }

        /**
         * Highlight the featured elements on the manage locations panel.
         *
         * @param string $extraCSSClasses
         * @return string
         */
        function filter_HighlightFeatured($extraCSSClasses) {
            return $extraCSSClasses . (($this->slplus->currentLocation->featured)?' featured ':'');
        }

        /**
         * Add the input settings to be saved on the map settings page.
         *
         * TODO: Get rid of legacy settings.
         * TODO: merge with plugin slp_save_my_settings() method.
         *
         * @param string[] $inArray names of inputs already to be saved
         * @return string[] modified array with our Pro Pack inputs added.
         */
        function filter_SaveMapInputSettings($inArray) {
            array_walk($_REQUEST,array($this,'set_ValidOptions'));
            update_option($this->addon->option_name, $this->addon->options);
            return array_merge($inArray,
                    array(
                        SLPLUS_PREFIX.'-enhanced_results_string',
                        SLPLUS_PREFIX.'_message_noresultsfound' ,
                        'popup_email_title' ,
                        'popup_email_from_placeholder' ,
                        'popup_email_subject_placeholder' ,
                        'popup_email_message_placeholder'
                        )
                    );
        }

        /**
         * Add more actions to the Bulk Action drop down on the admin Locations/Manage Locations interface.
         *
         * @param mixed[] $BulkActions
         * @return mixed[]
         */
        function filter_LocationsBulkAction($items) {
            return
                array_merge(
                    $items,
                    array(
                        array(
                            'label'     =>  __('Feature Location','csa-slp-er')      ,
                            'value'     => 'feature_location'                        ,
                        ),
                        array(
                            'label'     =>  __('Stop Featuring Location','csa-slp-er') ,
                            'value'     => 'remove_feature_location'                 ,
                        ),
                    )
                );
        }

        /**
         * Additional location processing on manage locations admin page.
         *
         */
        function action_ManageLocationsProcessing() {
            switch ($_REQUEST['act']) {

                // Add tags to locations
                case 'feature_location':
                    if (isset($_REQUEST['sl_id'])) { $this->feature_Locations('add'); }
                    break;

                // Remove tags from locations
                case 'remove_feature_location':
                    if (isset($_REQUEST['sl_id'])) { $this->feature_Locations('remove'); }
                    break;

                default:
                    break;
            }
        }

        /**
         * feature a location
         *
         * @param string $action = add or remove
         */
        function feature_Locations($action) {

            // Setup the location ID array
            //
            if ( is_array( $_REQUEST['sl_id'] ) ) {
                $locationIDs = $_REQUEST['sl_id'];
            } else {
                $locationIDs = array();
                $locationIDs[] = $_REQUEST['sl_id'];
            }
            foreach ( $locationIDs as $locationID ) {
                $this->slplus->database->extension->update_data(
                    $locationID,
                    array('featured' => ( $action === 'add' ) ? '1' : '0' )
                 );
            }
        }

    }
}
// Dad. Explorer. Rum Lover. Code Geek. Not necessarily in that order.

<?php
if (! class_exists('SLPEnhancedMap_Admin')) {

    require_once(SLPLUS_PLUGINDIR.'/include/base_class.admin.php');

    /**
     * Holds the admin-only code.
     *
     * This allows the main plugin to only include this file in admin mode
     * via the admin_menu call.   Reduces the front-end footprint.
     *
     * @property SLPEnhancedMap $addon
     *
     * @package StoreLocatorPlus\EnhancedMap\Admin
     * @author Lance Cleveland <lance@charlestonsw.com>
     * @copyright 2013 - 2015 Charleston Software Associates, LLC
     */
    class SLPEnhancedMap_Admin extends SLP_BaseClass_Admin {
        public $addon;

	    /**
	     * List of option keys that are checkboxes.
	     *
	     * Helps with processing during save of form posts.
	     *
	     * @var string[] $admin_checkboxes
	     */
	    public $admin_checkboxes = array (
		    'hide_map'              ,
		    'show_maptoggle'        ,
	    );

	    /**
	     * @var SLPEM_Admin_ExperienceSettings
	     */
	    private $userexperience;

        /**
         * Add our SLP hooks and Filters for Admin Mode
         */
        public function add_hooks_and_filters() {
	        parent::add_hooks_and_filters();

            // Manage Location Fields
            // - tweak the add/edit form
            //
            add_filter('slp_edit_location_right_column'         ,array($this,'filter_AddFieldsToEditForm'   ),11);

            // Map Settings Page
            //
	        add_action( 'slp_ux_modify_adminpanel_map'      , array( $this , 'add_ux_map_settings'      ) , 10 , 2 );

            // Save Data
            //
            add_filter('slp_save_map_settings_checkboxes'   ,array($this,'filter_SaveUXCheckboxes'           ),10);
            add_filter('slp_save_map_settings_inputs'       ,array($this,'filter_SaveUXInputs'               ),10);
        }

	    /**
	     * Create and attach the user experience object.
	     */
	    private function create_object_admin_ux() {
		    if ( ! isset( $this->userexperience ) ) {
			    require_once('class.admin.experience.php');
			    $this->userexperience = new SLPEM_Admin_ExperienceSettings(array( 'addon' => $this->addon ) );
		    }
	    }


        /**
         * Add extra fields that show in results output to the edit form.
         *
         * SLP Filter: slp_edit_location_right_column
         *
         * @param string $theHTML the original HTML form for the manage locations edit (right side)
         * @return string the modified HTML form
         */
        function filter_AddFieldsToEditForm($theHTML) {
            $theHTML .=
                '<div id="slp_em_fields" class="slp_editform_section">'.
                $this->slplus->helper->create_SubheadingLabel(__('Enhanced Map','csa-slp-em'))
                ;

            // Add or Edit
            //
            $theHTML .=
                $this->slplus->AdminUI->ManageLocations->createstring_InputElement(
                        'attributes[marker]',
                        __("Map Marker", 'csa-slp-em'),
                        isset($this->slplus->currentLocation->attributes['marker'])?
                            $this->slplus->currentLocation->attributes['marker']   :
                            '',
                        'iconfield',
                        true
                        ).
                 '<img id="location-marker" align="top" src="'.
                            (isset($this->slplus->currentLocation->attributes['marker'])?
                            $this->slplus->currentLocation->attributes['marker']   :
                            '')  .
                            '">' .
                 $this->slplus->AdminUI->CreateIconSelector('edit-attributes-'.$this->slplus->currentLocation->id.'[marker]','location-marker')
                 ;

            $theHTML .=
                '</div>'
                ;

            return $theHTML;
        }


	    /**
	     * Experience / Map
	     * @param SLP_Settings $settings
	     * @param string $section_name
	     */
	    function add_ux_map_settings( $settings , $section_name ) {
		    $this->create_object_admin_ux();
		    $this->userexperience->add_map_settings( $settings , $section_name );

        }

        /**
         * Augment the list of checkbox entries to save on the map settings page.
         *
         * @param mixed[] $theArray
         * @return mixed[]
         */
        function filter_SaveUXCheckboxes($theArray) {
            return array_merge(
                    $theArray,
                    array(
                       'sl_map_overview_control'                    ,
                       SLPLUS_PREFIX.'_disable_scrollwheel'         ,
                       SLPLUS_PREFIX.'_disable_largemapcontrol3d'   ,
                       SLPLUS_PREFIX.'_disable_scalecontrol'        ,
                       SLPLUS_PREFIX.'_disable_maptypecontrol'      ,
                    )
                );
        }

        /**
         * Augment the list of inputs to save on the map settings page.
         *
         * @param mixed[] $theArray
         * @return mixed[]
         */
        function filter_SaveUXInputs($theArray) {

            // Force Prefixed checkboxes to blank
            //
            $BoxesToHit = array(
                'no_autozoom',
                'no_homeicon_at_start'
                );
            foreach ($BoxesToHit as $BoxName) {
                if (!isset($_REQUEST[SLPLUS_PREFIX.'-'.$BoxName])) {
                    $_REQUEST[SLPLUS_PREFIX.'-'.$BoxName] = '';
                }
            }

            // Force Non-Prefixed checkboxes to blank
            //
            $BoxesToHit = array(
                'hide_bubble',
                );
            foreach ($BoxesToHit as $BoxName) {
                if (!isset($_REQUEST[$BoxName])) {
                    $_REQUEST[$BoxName] = '';
                }
            }

            // Serialized Save (use this as often as possible, one data I/O = faster)
            //
            array_walk($_REQUEST,array($this,'set_ValidOptions'));
            update_option(SLPLUS_PREFIX.'-EM-options', $this->addon->options);

            // Input/text areas
            //
            return array_merge(
                    $theArray,
                    array(
                        SLPLUS_PREFIX.'-maptoggle_label'    ,
                        'sl_starting_image'                 ,
                    )
                    );
        }

   }
}

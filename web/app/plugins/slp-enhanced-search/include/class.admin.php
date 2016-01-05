<?php
if (! class_exists('SLPEnhancedSearch_Admin')) {
	require_once(SLPLUS_PLUGINDIR.'/include/base_class.admin.php');

    /**
     * Holds the admin-only code.
     *
     * This allows the main plugin to only include this file in admin mode
     * via the admin_menu call.   Reduces the front-end footprint.
     *
     * @package StoreLocatorPlus\EnhancedSearch\Admin
     * @author Lance Cleveland <lance@charlestonsw.com>
     * @copyright 2014 Charleston Software Associates, LLC
     */
    class SLPEnhancedSearch_Admin extends SLP_BaseClass_Admin {

        //-------------------------------------
        // Properties
        //-------------------------------------

        /**
         * This addon pack.
         *
         * @var \SLPEnhancedSearch $addon
         */
        public $addon;

	    /**
	     * List of option keys that are checkboxes.
	     *
	     * Helps with processing during save of form posts.
	     *
	     * @var string[] $cb_options
	     */
	    private $cb_options = array (
		    'hide_address_entry',
		    'hide_search_form'  ,
		    'search_by_name'    ,
		    'csl-slplus_hide_radius_selections'
	    );

	    /**
	     * Shortcut to the map settings UI object.
	     *
	     * @var \SLPlus_AdminUI_MapSettings
	     */
	    private $msUI;

        //-------------------------------------
        // Methods (overrides)
        //-------------------------------------

	    /**
	     * Add our specific hooks and filters.
	     */
	    function add_hooks_and_filters() {
		    // Hooks that allow us to directly output HTML in the pre-existing
		    // SLP Admin UI / Map Settings / Search Form settings groups
		    //
		    add_action('slp_settings_search_features'           ,array($this,'filter_AddSearchSettings'             ), 9   );
		    add_action('slp_settings_search_labels'             ,array($this,'filter_AddSearchLabels'               ), 9   );

		    // General Settings tab : Google section
		    //
		    add_action('slp_generalsettings_modify_userpanel'   ,array($this,'action_AddUserSettings'               ), 9 , 2);
		    add_filter('slp_save_general_settings_checkboxes'   ,array($this,'filter_SaveGeneralCBSettings'         )       );

		    // Filter allows us to manipulate the Admin UI / Map Settings / Search Form HTML
		    //
		    add_filter('slp_map_settings_searchform'            ,array($this,'add_placeholders'                     )       );


		    add_action( 'slp_save_map_settings'                 , array( $this , 'save_uxtab_settings'      ) , 9   );
		    add_filter( 'slp_save_map_settings_checkboxes'      , array( $this , 'save_uxtab_checkboxes'    )       );
	    }

	    /**
	     * Stuff we do when starting up an admin session.
	     */
	    function do_admin_startup() {
		    $this->check_for_updates();
		    $this->update_install_info();
	    }

	    //-------------------------------------
	    // Methods (custom)
	    //-------------------------------------

	    /**
	     * Add placeholders to admin settings.
	     *
	     * @param string $currentHTML
	     *
	     * @return string
	     */
	    function add_placeholders($currentHTML) {
		    $this->connect_msUI();

		    $newHTML =
			    $this->msUI->CreateInputDiv(
				    '-ES-options[address_placeholder]',
				    __('Address', 'csa-slp-es'),
				    __('Instructions to place in the address input.','csa-slp-es')
			    ) .
			    $this->msUI->CreateInputDiv(
				    '-ES-options[name_placeholder]',
				    __('Name', 'csa-slp-es'),
				    __('Instructions to place in the name input.','csa-slp-es')
			    )
		    ;

		    // TODO: Convert to new panel builder with add_ItemToGroup() in wpCSL (see Tagalong admin panel)
		    return
			    $currentHTML .
			    $this->slplus->settings->create_SettingsGroup(
				    'slpes_input_placeholders',
				    __('Search Placeholders','csa-slp-es'),
				    __('Placeholders are text instructions that appear inside an input box before data is entered.','csa-slp-es'),
				    $newHTML
			    );
	    }

	    /**
	     * Connect the msUI shortcut.
	     */
	    private function connect_msUI() {
		    if (!is_object($this->msUI)) { $this->msUI = $this->slplus->AdminUI->MapSettings; }
	    }

	    /**
	     * Add items to the General Settings tab : Google section
	     *
	     * @param \SLPlus_AdminUI_GeneralSettings $settings
	     * @param string $sectName
	     */
	    function action_AddUserSettings($settings,$sectName) {
		    $groupName = __('Program Interface','csa-slp-es');
		    $settings->add_ItemToGroup(
			    array(
				    'section'       => $sectName        ,
				    'group'         => $groupName       ,
				    'type'          => 'subheader'      ,
				    'label'         => $this->addon->name      ,
				    'show_label'    => false
			    ));
		    $settings->add_ItemToGroup(
			    array(
				    'section'       => $sectName    ,
				    'group'         => $groupName   ,
				    'type'          => 'slider'     ,
				    'setting'       => 'es_allow_addy_in_url',
				    'label'         => __('Allow Address In URL','csa-slp-es'),
				    'description'   =>
					    __('If checked an address can be pre-loaded via a URL string ?address=blah.', 'csa-slp-es') .
					    ' ' .
					    __('This will disable the Pro Pack location sensor whenever the address is used in the URL.', 'csa-slp-es')
			    ));
	    }

	    /**
	     * Add new custom labels.
	     *
	     */
	    function filter_AddSearchLabels($HTML) {
		    $this->connect_msUI();

		    $HTML .=
			    $this->slplus->helper->create_SubheadingLabel($this->addon->name) .

			    $this->msUI->CreateInputDiv(
				    '_find_button_label',
				    __('Find Button', 'csa-slp-es'),
				    __('The label on the find button, if text mode is selected.','csa-slp-es'),
				    SLPLUS_PREFIX,
				    __('Find Locations','csa-slp-es')
			    ) .

			    $this->msUI->CreateInputDiv(
				    'sl_name_label',
				    __('Name', 'csa-slp-es'),
				    __('The label that precedes the name input box.','csa-slp-es'),
				    '',
				    'Name'
			    ) .

			    // City
			    //
			    $this->msUI->CreateInputDiv(
				    '-ES-options[label_for_city_selector]',
				    __('City Selector Label', 'csa-slp-es'),
				    __('The label that precedes the city selector.','csa-slp-es'),
				    SLPLUS_PREFIX,
				    $this->addon->options['label_for_city_selector'],
				    $this->addon->options['label_for_city_selector']
			    ) .
			    $this->msUI->CreateInputDiv(
				    '_search_by_city_pd_label',
				    __('City Selector First Entry', 'csa-slp-es'),
				    __('The first entry on the search by city selector.','csa-slp-es'),
				    SLPLUS_PREFIX,
				    __('--Search By City--','csa-slp-es')
			    ) .

			    // State
			    //
			    $this->msUI->CreateInputDiv(
				    '-ES-options[label_for_state_selector]',
				    __('State Selector Label', 'csa-slp-es'),
				    __('The label that precedes the state selector.','csa-slp-es'),
				    SLPLUS_PREFIX,
				    $this->addon->options['label_for_state_selector'],
				    $this->addon->options['label_for_state_selector']
			    ) .
			    $this->msUI->CreateInputDiv(
				    '_search_by_state_pd_label',
				    __('State Selector First Entry', 'csa-slp-es'),
				    __('The first entry on the search by state selector.','csa-slp-es'),
				    SLPLUS_PREFIX,
				    __('--Search By State--','csa-slp-es')
			    ) .

			    // Country
			    //
			    $this->msUI->CreateInputDiv(
				    '-ES-options[label_for_country_selector]',
				    __('Country Selector Label', 'csa-slp-es'),
				    __('The label that precedes the country selector.','csa-slp-es'),
				    SLPLUS_PREFIX,
				    $this->addon->options['label_for_country_selector'],
				    $this->addon->options['label_for_country_selector']
			    ) .
			    $this->msUI->CreateInputDiv(
				    '_search_by_country_pd_label',
				    __('Country Selector First Entry', 'csa-slp-es'),
				    __('The first entry on the search by country selector.','csa-slp-es'),
				    SLPLUS_PREFIX,
				    __('--Search By Country--','csa-slp-es')
			    )
		    ;

		    return $HTML;
	    }

	    /**
	     * Add new settings for the search for to the map settings/search form section.
	     *
	     * @return null
	     */
	    function filter_AddSearchSettings($HTML) {
		    $this->connect_msUI();

		    $HTML .=
			    $this->slplus->helper->create_SubheadingLabel($this->addon->name) .

			    $this->slplus->helper->CreateCheckboxDiv(
				    '-ES-options[hide_address_entry]',
				    __('Hide Address Entry','csa-slp-es'),
				    __('Hide the address input box on the locator search form.', 'csa-slp-es'),
				    null,false,0,
				    $this->addon->options['hide_address_entry']
			    ) .

			    $this->slplus->helper->CreateCheckboxDiv(
				    '-ES-options[hide_search_form]',
				    __('Hide Search Form','csa-slp-es'),
				    __('Hide the user input on the search page, regardless of the SLP theme used.', 'csa-slp-es'),
				    null,false,0,
				    $this->addon->options['hide_search_form']
			    ) .

			    $this->slplus->helper->CreateCheckboxDiv(
				    '_hide_radius_selections',
				    __('Hide Radius Selections','csa-slp-es'),
				    __('Hide the radius selection drop down.', 'csa-slp-es')
			    ) .

			    $this->slplus->helper->CreateCheckboxDiv(
				    '-ES-options[search_by_name]',
				    __('Show Search By Name', 'csa-slp-es'),
				    __('Shows the name search entry box to the user.', 'csa-slp-es'),
				    null,false,0,
				    $this->addon->options['search_by_name']
			    ) .

			    $this->slplus->helper->createstring_DropDownDiv(
				    array(
					    'id'        => 'radius_behavior',
					    'name'      => SLPLUS_PREFIX.'-ES-options[radius_behavior]',
					    'label'     => __('Radius Behavior','csa-slp-es'),
					    'helptext'  =>
						    __('Show the city selector on the search form.', 'csa-slp-es') .
						    sprintf(__('View the <a href="%s" target="csa">documentation</a> for more info. ','csa-slp-es'),$this->slplus->support_url) .
						    __('If you want the option to search by address OR by city on the same form, select: Use radius only with address. If you choose not to use radius, you consider also hiding the radius selector.', 'csa-slp-es')
				    ,
					    'selectedVal' => $this->addon->options['radius_behavior'],
					    'items' => array (
						    array(
							    'label' => __('Do not use','csa-slp-es'),
							    'value' => 'always_ignore',
						    ),
						    array(
							    'label' => __('Use only when address is entered','csa-slp-es'),
							    'value' => 'ignore_with_blank_addr',
						    ),
						    array(
							    'label' => __('Always use','csa-slp-es'),
							    'value' => 'always_use',
						    ),

					    )
				    )
			    ) .

			    $this->slplus->helper->createstring_DropDownDiv(
				    array(
					    'id'        => 'city_selector',
					    'name'      => SLPLUS_PREFIX.'-ES-options[city_selector]',
					    'label'     => __('City Selector','csa-slp-es'),
					    'helptext'  =>
						    __('Show the city selector on the search form.', 'csa-slp-es') .
						    sprintf(__('View the <a href="%s" target="csa">documentation</a> for more info. ','csa-slp-es'),$this->slplus->support_url) .
						    __('Consider setting do not use radius when using discrete search mode.', 'csa-slp-es')
				    ,
					    'selectedVal' => $this->addon->options['city_selector'],
					    'items' => array (
						    array(
							    'label' => __('Hidden','csa-slp-es'),
							    'value' => 'hidden',
						    ),
						    array(
							    'label' => __('Dropdown, Address Input','csa-slp-es'),
							    'value' => 'dropdown_addressinput',
						    ),
						    array(
							    'label' => __('Dropdown, Discrete Filter','csa-slp-es'),
							    'value' => 'dropdown_discretefilter',
						    ),
						    array(
							    'label' => __('Dropdown, Discrete + Address Input','csa-slp-es'),
							    'value' => 'dropdown_discretefilteraddress',
						    ),
					    )
				    )
			    ) .

			    $this->slplus->helper->createstring_DropDownDiv(
				    array(
					    'id'        => 'country_selector',
					    'name'      => SLPLUS_PREFIX.'-ES-options[country_selector]',
					    'label'     => __('Country Selector','csa-slp-es'),
					    'helptext'  =>
						    __('Show the country selector on the search form.', 'csa-slp-es') .
						    sprintf(__('View the <a href="%s" target="csa">documentation</a> for more info. ','csa-slp-es'),$this->slplus->support_url) .
						    __('Consider setting ignore radius when using discrete search mode.', 'csa-slp-es')
				    ,
					    'selectedVal' => $this->addon->options['country_selector'],
					    'items' => array (
						    array(
							    'label' => __('Hidden','csa-slp-es'),
							    'value' => 'hidden',
						    ),
						    array(
							    'label' => __('Dropdown, Address Input','csa-slp-es'),
							    'value' => 'dropdown_addressinput',
						    ),
						    array(
							    'label' => __('Dropdown, Discrete Filter','csa-slp-es'),
							    'value' => 'dropdown_discretefilter',
						    ),
						    array(
							    'label' => __('Dropdown, Discrete + Address Input','csa-slp-es'),
							    'value' => 'dropdown_discretefilteraddress',
						    ),

					    )
				    )
			    ) .

			    $this->slplus->helper->createstring_DropDownDiv(
				    array(
					    'id'        => 'state_selector',
					    'name'      => SLPLUS_PREFIX.'-ES-options[state_selector]',
					    'label'     => __('State Selector','csa-slp-es'),
					    'helptext'  =>
						    __('Show the state selector on the search form.', 'csa-slp-es') .
						    sprintf(__('View the <a href="%s" target="csa">documentation</a> for more info. ','csa-slp-es'),$this->slplus->support_url) .
						    __('Consider setting ignore radius when using discrete search mode.', 'csa-slp-es')
				    ,
					    'selectedVal' => $this->addon->options['state_selector'],
					    'items' => array (
						    array(
							    'label' => __('Hidden','csa-slp-es'),
							    'value' => 'hidden',
						    ),
						    array(
							    'label' => __('Dropdown, Address Input','csa-slp-es'),
							    'value' => 'dropdown_addressinput',
						    ),
						    array(
							    'label' => __('Dropdown, Discrete Filter','csa-slp-es'),
							    'value' => 'dropdown_discretefilter',
						    ),
						    array(
							    'label' => __('Dropdown, Discrete + Address Input','csa-slp-es'),
							    'value' => 'dropdown_discretefilteraddress',
						    ),

					    )
				    )
			    ) .

			    $this->slplus->helper->createstring_DropDownDiv(
				    array(
					    'id'        => 'searchnear',
					    'name'      => SLPLUS_PREFIX.'-ES-options[searchnear]',
					    'label'     => __('Search Address Nearest','csa-slp-es'),
					    'helptext'  =>
						    __('Worldwide is the default search, letting Google make the best guess which addres the user wants.','csa-slp-es') . ' '.
						    __('Current Map will find the best matching address nearest the current area shown on the map.','csa-slp-es'),
					    'selectedVal' => $this->addon->options['searchnear'],
					    'items'     => array(
						    array(
							    'label' => __('Worldwide','csa-slp-es'),
							    'value' => 'world'
						    ),
						    array(
							    'label' => __('Current Map','csa-slp-es'),
							    'value' => 'currentmap'
						    ),
					    )
				    )
			    );

		    $HTML .= $this->msUI->CreateTextAreaDiv(
			    '-ES-options[searchlayout]',
			    __('Search Layout','csa-slp-es'),
			    __('Enter your custom search form layout. ','csa-slp-es') .
			    sprintf('<a href="%s" target="csa">%s</a> ',
				    $this->slplus->support_url,
				    sprintf(__('Uses HTML plus %s shortcodes.','csa-slp-es'),$this->addon->name)
			    ) .
			    __('Set it to blank to reset to the default layout. ','csa-slp-es') .
			    __('Overrides all other search form settings.','csa-slp-es')
			    ,
			    SLPLUS_PREFIX,
			    $this->addon->options['searchlayout'],
			    true
		    );

		    $HTML .= $this->msUI->CreateInputDiv(
			    '-ES-options[append_to_search]',
			    __('Append This To Searches','csa-slp-es'),
			    __('Anything you enter in this box will automatically be appended to the address a user types into the locator search form address box on your site. ','csa-slp-es'),
			    SLPLUS_PREFIX,
			    $this->addon->options['append_to_search']
		    )
		    ;


		    return $HTML;
	    }

	    /**
	     * Save the map settings via SLP Action slp_save_map_settings.
	     *
	     * @return string
	     */
	    function save_uxtab_settings() {
		    $this->addon->debugMP( 'msg' , 'SLPEnhancedSearchAdmin::'.__FUNCTION__ );
		    $BoxesToHit = array(
			    SLPLUS_PREFIX.'_find_button_label'              ,
			    'sl_name_label'                                 ,
			    SLPLUS_PREFIX.'_state_pd_label'                 ,
			    SLPLUS_PREFIX.'_search_by_city_pd_label'        ,
			    SLPLUS_PREFIX.'_search_by_state_pd_label'       ,
			    SLPLUS_PREFIX.'_search_by_country_pd_label'     ,
		    );
		    foreach ($BoxesToHit as $JustAnotherBox) {
			    $this->slplus->helper->SavePostToOptionsTable($JustAnotherBox);
		    }

		    // Serialized : Compound Options
		    //
		    $this->addon->options =
			    $this->slplus->AdminUI->save_SerializedOption(
				    $this->addon->option_name,
				    $this->addon->options,
				    $this->cb_options
			    );
	    }

	    /**
	     * Save the General Settings tab checkboxes.
	     *
	     * @param mixed[] $cbArray
	     * @return mixed[]
	     */
	    function filter_SaveGeneralCBSettings($cbArray) {
		    $this->addon->debugMP('msg',__FUNCTION__);
		    return array_merge($cbArray,
			    array(
				    SLPLUS_PREFIX.'-es_allow_addy_in_url',
			    )
		    );
	    }

	    /**
	     * Save admin UX tab checkboxes to the options table in WP.
	     *
	     * @param string[] $cbArray array of checkbox names to be saved
	     *
	     * @return string[] augmented list of inputs to save
	     */
	    function save_uxtab_checkboxes( $cbArray ) {
		    $this->addon->debugMP( 'msg' , 'SLPEnhancedSearchAdmin::'.__FUNCTION__ );
		    return array_merge( $cbArray, $this->cb_options );
	    }

  }
}
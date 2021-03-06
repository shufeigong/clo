<?php

/**
 * Store Locator Plus basic user interface.
 *
 * @package StoreLocatorPlus\UI
 * @author Lance Cleveland <lance@charlestonsw.com>
 * @copyright 2012-2015 Charleston Software Associates, LLC
 */
class SLPlus_UI {

    //-------------------------------------
    // Properties
    //-------------------------------------

    /**
     * Name of this module.
     *
     * @var string $name
     */
    public $name;

    /**
     * Options for the UI class.  Needed for any registered module.
     *
     * @var string[]
     */
    public $options = array(
        'installed_version' => SLPLUS_VERSION
    );
    
    /**
     * @var \SLPlus
     */
    private $slplus;

    //----------------------------------
    // Methods
    //----------------------------------

    /**
     * Instantiate the UI Class.
     *
     * @param mixed[] $params
     */
    function __construct($params = null) {
        $this->name = 'UI';

        // Do the setting override or initial settings.
        //
        if ($params != null) {
            foreach ($params as $name => $sl_value) {
                $this->$name = $sl_value;
            }
        }
    }

    /**
     * Create a search form input div.
     */
    function createstring_InputDiv($fldID=null,$label='',$placeholder='',$hidden=false,$divID=null,$default='') {
        $this->slplus->debugMP('slp.main','msg',__FUNCTION__,"field ID: {$fldID} label {$label}");
        if ($fldID === null) { return; }
        if ($divID === null) { $divID = $fldID; }

        // Escape output for special char friendliness
        //
        if ($default     !==''){ $default     = esc_html($default);     }
        if ($placeholder !==''){ $placeholder = esc_html($placeholder); }
        if(ICL_LANGUAGE_CODE=='en'):
        $content =
            ($hidden?'':"<div id='$divID' class='search_item'>") .
                (($hidden || ($label === '')) ? '' : "<label for='$fldID'>$label</label>") .
                "<input type='".($hidden?'hidden':'text')."' id='$fldID' name='$fldID' placeholder='Enter a Town, City, or Postal Code' size='50' value='$default' />" .
            ($hidden?'':"</div>")
            ;
        elseif(ICL_LANGUAGE_CODE=='fr'):
        $content =
        ($hidden?'':"<div id='$divID' class='search_item'>") .
        (($hidden || ($label === '')) ? '' : "<label for='$fldID'>$label</label>") .
        "<input type='".($hidden?'hidden':'text')."' id='$fldID' name='$fldID' placeholder='Enter a Town, City, or Postal Code' size='50' value='$default' />" .
        ($hidden?'':"</div>")
        ;
        
        endif;
        return $content;
    }

    /**
     * Output the search form based on the search results layout.
     */
    function createstring_SearchForm() {
        $this->slplus->debugMP('slp.main','msg',get_class().'::'.__FUNCTION__);
        if ( $this->slplus->is_CheckTrue( $this->slplus->options['hide_search_form'] ) ) { return ''; }
        
        // Register our custom shortcodes
        // SHORTCODE: slp_search_element
        //
        add_shortcode('slp_search_element',array($this,'create_SearchElement'));

        // Process Layout With Shortcodes
        // 
        $HTML =
            do_shortcode(
                // FILTER: slp_searchlayout
                //
                apply_filters('slp_searchlayout',$this->slplus->defaults['searchlayout'])
            );

        // Disconnect shortcodes
        //
        remove_shortcode('slp_search_element');

        // Make sure the search form is wrapped in the form action to make it
        // work with the JS submit.
        //
        return
            '<form '                                                    .
                "onsubmit='cslmap.searchLocations(); return false;' "   .
                "id='searchForm' "                                      .
                "action=''>"                                            .
            $this->rawDeal($HTML)                                       .
            '</form>'
            ;
    }
    
    /**
     * Placeholder for the Tagalong legend placement in SLP Layout controls.
     * 
     * Does nothing but stop the [tagalong ...] shortcode text from appearing in output when Tagalong is not active.
     * 
     * @param mixed[] shortcode attributes array
     * @param string $content
     * @return string blank text
     */
    public function createstring_TagalongPlaceholder( $attributes , $content='' ) {
        return '';
    }

    /**
     * Return a plugin option value.
     *
     * Third party add-ons can use the shortcode_slp_option filter to set $this->slplus->options[<option_name>]
     * or $this->slplus->options_nojs[<option_name>] accordingly  OR set attributes['value'] to set it directly.
     *
     * [slp_option js="option_name"]
     * [slp_option nojs="option_name"]
     *
     * @param $attributes
     * @param null $content
     *
     * @return mixed[]
     */
    function create_string_slp_option_value( $attributes , $content = null ) {
        $attributes = apply_filters('shortcode_slp_option',$attributes);

        $value = isset( $attributes['value'] ) ? $attributes[ 'value' ] : '';

        // Value empty - look for it in slplus->options or slplus->options_nojs
        if ( empty( $value ) ) {
            foreach ($attributes as $type=>$key) {
                $type = strtolower($type);
                if ( $type === 'nojs' ) {
                    $value = isset( $this->slplus->options_nojs[$key] ) ? $this->slplus->options_nojs[$key] : '';
                    break;
                } else {
                    $value = isset( $this->slplus->options[$key] ) ? $this->slplus->options[$key] : '';
                    break;
                }
            }
        } else {
            $type = isset( $attributes['type'] ) ? $attributes['type'] : '';
            $key  = isset( $attributes['key']  ) ? $attributes['key' ] : '';
        }

        return sprintf('<span id="slp_option_%s_%s">%s</span>', $type, $key, $value );
    }

    /**
     * Render the SLP map
     *
     */
    function create_DefaultMap() {

        // Add our default map generator, priority 10
        // FILTER: slp_map_html
        //
        add_filter('slp_map_html',array($this,'filter_SetDefaultMapLayout'),10);
        $mapContent =  do_shortcode(apply_filters('slp_map_html',''));

        // Remove the credits
        //
        if ( $this->slplus->is_CheckTrue( $this->slplus->options_nojs['remove_credits'] ) ) {
            $mapContent = preg_replace( '/<div id="slp_tagline"(.*?)<\/div>/',  ''  ,  $mapContent  );
        }

        echo $mapContent;
    }

    /**
     *
     * @param string $HTML current map HTML default is blank
     * @return string modified map HTML
     */
    function filter_SetDefaultMapLayout($HTML) {
        // Only set default HTML if nothing has been defined yet.
        if (!empty($HTML)) { return $HTML; }        
       return $this->slplus->defaults['maplayout'];
    }

    /**
     * Create the default search address div.
     *
     * FILTER: slp_search_default_address
     */
    function createstring_DefaultSearchDiv_Address($placeholder='') {
        return $this->slplus->UI->createstring_InputDiv(
            'addressInput',
            $this->slplus->WPML->get_text('sl_search_label', get_option('sl_search_label',__('Address','store-locator-le'))),
            $placeholder,
            false,
            'addy_in_address',
            apply_filters('slp_search_default_address','')
            );
    }

    /**
     * Create the default search radius div.
     */
    private function create_DefaultSearchDiv_Radius() {
        // This is the old-school/bad way of doing this,
        // this option is only set with the ES plugin... this should
        // never have lived here, but for legacy support needs to remain (for now)
        //
        // TODO: deprecate this which will break very old versions of SLP:ES (pre 4.2)
        // move this using the slp_change_ui_radius_selector filter.
        //
        if ( ! $this->slplus->is_CheckTrue( get_option(SLPLUS_PREFIX.'_hide_radius_selections',0 ) ) ) {
            $HTML =
                "<div id='addy_in_radius'>".
                "<label for='radiusSelect'>".
                $this->slplus->WPML->get_text('sl_radius_label', get_option('sl_radius_label',__('Within','store-locator-le'))).
                '</label>'.
                "<select id='radiusSelect'>".$this->slplus->data['radius_options'].'</select>'.
                "</div>"
            ;
        } else {
            $HTML = $this->slplus->data['radius_options'];
        }

        // FILTER: slp_change_ui_radius_selector
        //
        return apply_filters( 'slp_change_ui_radius_selector' , $HTML ) ;
    }


    /**
     * Create the default search submit div.
     *
     * If we are not hiding the submit button.
     */
    private function create_DefaultSearchDiv_Submit() {
        $this->slplus->debugMP('msg',__FUNCTION__);
        if (get_option(SLPLUS_PREFIX.'_disable_search') == 0) {
        	
        	$src=get_stylesheet_directory_uri().'/dist/img/search_icon.svg';
            $button_style = 'type="image" class="slp_ui_button" src="'.$src.'"';

            // TODO: find_button_label get_option should move to Enhanced Search
            return
                "<div id='radius_in_submit'>".
                    "<input $button_style " .
                        "value='".
                            $this->slplus->WPML->get_text(
                                'find_button_label',
                                get_option(SLPLUS_PREFIX.'_find_button_label', __( 'Find Locations' , 'store-locator-le' ) ),
                                'store-locator-le'
                            ) .
                        "' ".
                        "id='addressSubmit'/>".
                "</div>"
                ;
        }

        return '';
    }

    /**
     * Render the search form for the map.
     *
     * FILTER: slp_search_form_html
     */
    function create_DefaultSearchForm() {
        // The search_form template sets up a bunch of DIV filters for the search form.
        //
        // apply_filters actually builds the output HTML from those div filters.
        //
        $HTML =
            "<form onsubmit='cslmap.searchLocations(); return false;' id='searchForm' action=''>".
            "<table  id='search_table' border='0' cellpadding='3px' class='sl_header'>".
                "<tbody id='search_table_body'>".
                    "<tr id='search_form_table_row'>".
                        "<td id='search_form_table_cell' valign='top'>".
                            "<div id='address_search'>".
            $this->createstring_DefaultSearchDiv_Address() .
            $this->create_DefaultSearchDiv_Radius()  .
            $this->create_DefaultSearchDiv_Submit()  .
            '</div></td></tr></tbody></table></form>'
            ;

        echo apply_filters('slp_search_form_html',$HTML);
    }

    /**
     * Create the HTML for the map.
     *
     * HOOK: slp_render_map
     */
    function create_Map() {
        ob_start();
        do_action('slp_render_map');
        return $this->rawDeal(ob_get_clean());
    }

    /**
     * Create the map div needed by Google
     *
     */
    function create_MapContent() {
        // FILTER: slp_googlemapdiv
        return apply_filters('slp_googlemapdiv','<div id="map" class="slp_map"></div>');
    }

    /**
     * Create the map tagline for SLP link
     *
     */
    function create_MapTagline() {
        return '<div id="slp_tagline">' . 
                sprintf(
                        __('search provided by %s', 'store-locator-le'),
                        "<a href='{$this->slplus->slp_store_url}' target='_blank'>{$this->slplus->name}</a>"
                        ) .
                '</div>';
    }
    /**
     * Create the HTML for the search results.
     */
    function create_Results() {
        return
            $this->rawDeal(
                '<div id="map_sidebar">'.
                    '<div class="text_below_map">'.
                        $this->slplus->options_nojs['instructions'] .
                    '</div>'.
                '</div>'
            );
    }

    /**
     * Process shortcodes for search form.
     */
    function create_SearchElement($attributes, $content = null) {

        // Pre-process the attributes.
        //
        // This allows third party plugins to man-handle the process by
        // tweaking the attributes.  If, for example, they were to return
        // array('hard_coded_value','blah blah blah') that is all we would return.
        //
        // FILTER: shortcode_slp_searchelement
        //
        $attributes = apply_filters('shortcode_slp_searchelement',$attributes);

        foreach ($attributes as $name=>$value) {

            switch (strtolower($name)) {

                // Hard coded entries take precedence.
                //
                case 'hard_coded_value':
                    return $value;
                    break;

                case 'dropdown_with_label':
                    switch ($value) {
                        case 'radius':
                            return $this->create_DefaultSearchDiv_Radius();
                            break;

                        default:
                            break;
                    }
                    break;

                case 'input_with_label':
                    switch ($value) {
                        case 'address':
                            return $this->createstring_DefaultSearchDiv_Address();
                            break;

                        default:
                            break;
                    }
                    break;

                case 'button':
                    switch ($value) {
                        case 'submit':
                            return $this->create_DefaultSearchDiv_Submit();
                            break;

                        default:
                            break;
                    }
                    break;

                default:
                    break;
            }
        }

        return '';
    }

    /**
     * Do not texturize our shortcodes.
     * 
     * @param array $shortcodes
     * @return array
     */
    static function no_texturize_shortcodes($shortcodes) {
       return array_merge($shortcodes,
                array(
                 'STORE-LOCATOR',
                 'SLPLUS',
                 'slplus',
                )
               );
    }

    /**
     * Process the store locator plus shortcode.
     *
     * Variables this function uses and passes to the template
     * we need a better way to pass vars to the template parser so we don't
     * carry around the weight of these global definitions.
     * the other option is to unset($GLOBAL['<varname>']) at then end of this
     * function call.
     *
     * We now use $this->plugin->data to hold attribute data.
     *
     * @link https://docs.google.com/drawings/d/10HCyJ8vSx8ew59TbP3zrTcv2fVZcedG-eHzY78xyWSA/edit?usp=sharing Flowchart for render_shortcode
     * @param type $attributes
     * @param type $content
     * @return string HTML the shortcode will render
     */
     function render_shortcode($attributes, $content = null) {
        if ( ! is_object( $this->slplus ) ) {
            return sprintf(__('%s is not ready','store-locator-le'),__('Store Locator Plus','store-locator-le'));
        }

        // Force some plugin data properties
        //
        $this->slplus->data['radius_options'] =
            ( isset( $this->slplus->data['radius_options'] ) ?
                $this->slplus->data['radius_options']        :
                ''
            );

        // Setup the base plugin allowed attributes
        //
        add_filter('slp_shortcode_atts',array($this,'filter_SetAllowedShortcodes'), 80, 3);

	     /**
	      * FILTER: slp_shortcode_atts
	      * Apply the filter of allowed attributes.
	      *
	      * @param array list of allowed attributes and their defaults
	      * @param array the attribute key=>value pairs from the shortcode being processed [slplus att='val']
	      * @param array content between the start and end shortcode block, always empty for slplus.
	      */
        $attributes =
            shortcode_atts(
                apply_filters('slp_shortcode_atts',array(),$attributes,$content),
                $attributes ,
                'slplus'
               );
        do_action( 'slp_before_render_shortcode', $attributes );
        
        // Set plugin data and options to include the attributes.
        // TODO: data needs to go away and become part of options.
        //
        $this->slplus->data =
            array_merge(
                $this->slplus->data,
                (array) $attributes
            );
        $this->slplus->options = 
            array_merge(
                $this->slplus->options, 
                (array) $attributes
            );

        // If Force Load JavaScript is NOT checked...
        // Localize the CSL Script - modifies the CSLScript with any shortcode attributes.
        // Setup the style sheets
        //
        if ( ! $this->slplus->javascript_is_forced ) {
            $this->localize_script( );
            wp_enqueue_script( 'csl_script' );
            $this->setup_stylesheet_for_slplus( $attributes['theme'] );
        }
        $this->set_RadiusOptions();

        // Map Actions
        //
        add_action('slp_render_map'         ,array($this,'create_DefaultMap'));

        // Shortcodes for SLPLUS layouts
        //
        add_shortcode('slp_option'      ,array( $this, 'create_string_slp_option_value'   ) );
        add_shortcode('slp_search'      ,array( $this, 'createstring_SearchForm'          ) );
        add_shortcode('slp_map'         ,array( $this, 'create_Map'                       ) );
        add_shortcode('slp_mapcontent'  ,array( $this, 'create_MapContent'                ) );
        add_shortcode('slp_maptagline'  ,array( $this, 'create_MapTagline'                ) );
        add_shortcode('slp_results'     ,array( $this, 'create_Results'                   ) );
        
        // Placeholders
        //
        $this->slplus->createobject_AddOnManager();
        if ( ! $this->slplus->is_AddOnActive('slp-tagalong') ) {
            add_shortcode('tagalong'        ,array( $this, 'createstring_TagalongPlaceholder' ) );
        }

        // Set our flag for later processing
        // of JavaScript files
        //
        if (!defined('SLPLUS_SHORTCODE_RENDERED')) {
            define('SLPLUS_SHORTCODE_RENDERED',true);
        }
        $this->slplus->shortcode_was_rendered = true;        
        
        // FILTER: slp_layout
        //
        $this->slplus->debugMP('slp.main','pr','',$this->slplus->options);
        $HTML = do_shortcode(apply_filters('slp_layout',$this->slplus->defaults['layout']));
        
        // Remove Shortcodes Not Used Outside Of Here
        //
        remove_shortcode('slp_search'      );
        remove_shortcode('slp_map'         );
        remove_shortcode('slp_mapcontent'  );
        remove_shortcode('slp_maptagline'  );
        remove_shortcode('slp_results'     );
        remove_shortcode( 'tagalong'       );
		do_action( 'slp_after_render_shortcode', $attributes );
        
        return $HTML;
    }
    
    /**
     * Set the allowed shortcode attributes
     * 
     * @param mixed[] $atts
     * @param mixed[] $attributes
     * @param string $content
     * @return mixed[] $atts modified attributes array
     */
    function filter_SetAllowedShortcodes($atts, $attributes, $content) {
        $ret_atts =  array_merge(
                array(
                    'initial_radius'     => $this->slplus->options['initial_radius'],
                    'theme'              => null, 
                    'id'                 => null,
                    'hide_search_form'   => null,                   
                    ),
                $atts
            );
        if ( isset($attributes['id']) ) {
            $locData =
                $this->slplus->database->get_Record(
                    array('selectall','whereslid'),
                    $attributes['id']
                );
            if (is_array($locData)) {
                $ret_atts['id_addr'] = $locData['sl_latitude'] . ', ' . $locData['sl_longitude'];
            }
        }

        return $ret_atts;
    }

    /**
     * Localize the CSL Script
     */
    public function localize_script( ) {
		// Handle any IconAttributes optionally set using the shortcode in combination with the Pro Pack
		$this->handleIconAttributes('map_home_icon', 'homeicon');
		$this->handleIconAttributes('map_end_icon',  'endicon' );

        $slplus_home_icon_file = str_replace(SLPLUS_ICONURL,SLPLUS_ICONDIR,$this->slplus->options['map_home_icon']);
        $slplus_end_icon_file  = str_replace(SLPLUS_ICONURL,SLPLUS_ICONDIR,$this->slplus->options['map_end_icon']);
        $this->slplus->data['home_size'] =(function_exists('getimagesize') && file_exists($slplus_home_icon_file))?
            getimagesize($slplus_home_icon_file) :
            array(0 => 20, 1 => 34);
        $this->slplus->data['end_size']  =(function_exists('getimagesize') && file_exists($slplus_end_icon_file)) ?
            getimagesize($slplus_end_icon_file)  :
            array(0 => 20, 1 => 34);

        add_shortcode('slp_location',array($this,'process_slp_location_Shortcode'));

        // Load up some basic options.
        // This can probably be handled better.
        //
        $this->slplus->options['use_sensor'] = (get_option(SLPLUS_PREFIX.'_use_location_sensor',0 )==1);

        // Set starting map center
        //
        $this->slplus->options['map_center'] = $this->set_MapCenter();

	    // Environment
	    //
	    $this->slplus->createobject_AddOnManager();
	    $environment['addons'] = $this->slplus->add_ons->get_versions();
        $environment['slp'] = SLPLUS_VERSION;

        add_filter( 'slp_js_options'    , array( $this , 'add_to_js_options') );

        // Lets get some variables into our script.
        // "Higher Level" JS Options are those noted below.
        //
        // TODO: ALL of these options should go inside the options property other than plugin_url, core_url, and environment.
        //
        $scriptData = array(
            'plugin_url'        => SLPLUS_PLUGINURL,
            'disable_scroll'    => (get_option(SLPLUS_PREFIX.'_disable_scrollwheel')==1),
            'map_3dcontrol'     => (get_option(SLPLUS_PREFIX.'_disable_largemapcontrol3d')==0),
            'map_home_sizew'    => $this->slplus->data['home_size'][0],
            'map_home_sizeh'    => $this->slplus->data['home_size'][1],
            'map_end_sizew'     => $this->slplus->data['end_size'][0],
            'map_end_sizeh'     => $this->slplus->data['end_size'][1],
            'map_scalectrl'     => (get_option(SLPLUS_PREFIX.'_disable_scalecontrol'  )==0),
            'map_typectrl'      => (get_option(SLPLUS_PREFIX.'_disable_maptypecontrol')==0),
            'msg_noresults'     => $this->slplus->get_item('message_noresultsfound','No results found.','_'),
            'results_string'    => $this->set_ResultsLayout( false ),
            'overview_ctrl'     => get_option('sl_map_overview_control',0),

	        'environment'       => apply_filters( 'slp_js_environment' , $environment ) ,

            // FILTER: slp_js_options
            'options'           => apply_filters('slp_js_options',$this->slplus->options)
            );

        remove_shortcode('slp_location');

        // AJAX URL Stuff
        //
        $scriptData['ajaxurl']  = admin_url('admin-ajax.php');
        $scriptData['nonce']    = wp_create_nonce('em');

        wp_localize_script('csl_script' ,'slplus'   , $scriptData);

    }

    /**
     * Show force load option in JS options.
     */
    function add_to_js_options( $options ) {
        return
            array_merge(
                $options,
                array( 'force_load_js' => $this->slplus->options_nojs['force_load_js'] )
            );
    }

	/**
	 * Handle any IconAttributes optionally set using the shortcode in combination with the Pro Pack.
	 *
	 * Uses data['sl_map_home_icon'] by default.
	 */
	function handleIconAttributes( $data_element, $attribute_element ) {
		$this->slplus->debugMP('slp.main','pr',get_class().'::'.__FUNCTION__ . ' Checking attribute[' . $attribute_element . '] with data:',$this->slplus->data);

		// Check Settings for $attribute_element
		//
		if (isset($this->slplus->data[$attribute_element]) && !empty($this->slplus->data[$attribute_element])) {

			// Start with attribute_element value
			$icon_url = $this->slplus->data[$attribute_element];

			// Prepends value with SLPLUS_ICONURL when it is not a url (could use url_test() )
            //  Try WordPress is_valid_url() from the common.php library.
            //
            if ( ! $this->slplus->is_valid_url( $icon_url ) ) {
				$icon_url = SLPLUS_ICONURL . $icon_url;

				// If file doesn't exist, try to make relative url into absolute url
				$icon_file = str_replace(SLPLUS_ICONURL,SLPLUS_ICONDIR,$icon_url);
				if (!file_exists($icon_file)) {
					$icon_url = get_site_url() . $this->slplus->data[$attribute_element];
				}
			}

			// Store value found in data_element
			$this->slplus->data[$data_element] = $icon_url;
		}

	}

    /**
     * Set the starting point for the center of the map.
     *
     * Uses country by default.
     */
    function set_MapCenter() {
	    $this->slplus->create_object_CountryManager();

        // Map Settings "Center Map At"
        //
        $customAddress = $this->slplus->options['map_center'];
        if ((preg_replace('/\W/','',$customAddress) != '')) {
            $customAddress = str_replace(array("\r\n","\n","\r"),', ',esc_attr($customAddress));
        } else {
            $customAddress = esc_attr(  $this->slplus->CountryManager->countries[ $this->slplus->options_nojs['default_country'] ] );
        }
        return apply_filters('slp_map_center',$customAddress);
    }

    /**
     * Set the plugin data radius options.
     */
    function set_RadiusOptions() {
        $radiusSelections = get_option('sl_map_radii','1,5,10,(25),50,100,200,500');

        // Hide Radius, set the only (or default) radius
        if ( $this->slplus->is_CheckTrue( get_option(SLPLUS_PREFIX.'_hide_radius_selections',0 ) ) ) {
            preg_match('/\((.*?)\)/', $radiusSelections, $selectedRadius);
            $selectedRadius = preg_replace('/[^0-9]/', '', (isset($selectedRadius[1])?$selectedRadius[1]:$radiusSelections));
            if (empty($selectedRadius) || ($selectedRadius <= 0)) { $selectedRadius = '2500'; }
            $this->slplus->data['radius_options'] =
                    "<input type='hidden' id='radiusSelect' name='radiusSelect' value='$selectedRadius'>";

        // Build Pulldown
        } else {
            $radiusSelectionArray  = explode(",",$radiusSelections);
            $this->slplus->data['radius_options'] = '';
            foreach ($radiusSelectionArray as $radius) {
                $selected=(preg_match('/\(.*\)/', $radius))? " selected='selected' " : "" ;
                $radius=preg_replace('/[^0-9\.]/', '', $radius);
                $this->slplus->data['radius_options'].=
                        "<option value='$radius' $selected>$radius ".$this->slplus->options['distance_unit']."</option>";
            }
        }
    }

    /**
     * Set the results layout string.
     *
     * @param bool $add_shortcode set to false if doing your own slp_location shortcode handling.
     * @param bool $raw set to true to skip the stripslashes and esc_textarea processing.
     * @return string $html
     */
    public function set_ResultsLayout( $add_shortcode = true , $raw = false ) {

        if ( $add_shortcode ) {
            add_shortcode('slp_location',array($this,'process_slp_location_Shortcode'));
        }

        $results_layout = apply_filters('slp_javascript_results_string',$this->slplus->defaults['resultslayout']);

        if ( ! $raw ) {
	        $results_layout =
	            do_shortcode(
	                stripslashes(
	                    esc_textarea(
	                       $results_layout
	                    )
	                )
	            );
        }

        if ( $add_shortcode ) {
            remove_shortcode('slp_location');
        }

        return $results_layout;
    }

    /**
     * Setup the CSS for the product pages.
     */
    function setup_stylesheet_for_slplus( $theme = null ) {
        if ( is_null( $theme ) || empty( $theme )  ) {
            $theme = $this->slplus->options_nojs['theme'];
        }
        $this->slplus->themes->assign_user_stylesheet( $theme ,true );
    }

    /**
     * Process the [slp_location] shortcode in a results string.
     *
     * Attributes for [slp_location] include:
     *     <field name> where field name is a locations table field.
     *
     * Usage: [slp_location country]
     *
     * @param mixed[] $atts
     * @return mixed[]
     */
    function process_slp_location_Shortcode($atts) {

		$shortcode_label    = 'slp_location';
	    $fldName            = '';
	    $attributes         = '';

        // Process the keys
        //
        if (is_array($atts)) {
            foreach ($atts as $key=>$value) {
                $key=strtolower($key);
                $value = preg_replace('/[\W^[.]]/','',htmlspecialchars_decode($value));
                switch ($key) {

                    // First attribute : field name placeholders
                    //
                    case '0':
                        $fldName = strtolower($value);

                        switch ($fldName):

	                        // slp_location with more attributes
	                        //
	                        case 'web_link':
	                        case 'pro_tags':
		                        $attributes .= ' raw';
		                        break;

	                        case 'distance_1'     :
		                        $fldName = 'distance';
		                        $attributes .= ' format="decimal1"';
		                        break;

	                        case 'hours':
		                        $attributes = ' format text';
		                        break;

	                        // convert to slp_option
	                        //
	                        case 'map_domain'     :
	                        case 'distance_unit'  :
		                        $shortcode_label = 'slp_option';
                                break;

                            case 'directions_text':
	                            $shortcode_label = 'slp_option';
								$fldName = 'label_directions';
                                break;

	                        // Leave untouched
	                        //
                            default:
                                break;

                        endswitch;
                        break;

                    default:
						$attributes .=
							' ' .
							(
								is_numeric($key)                        ?
									$value                              :
									$key . '="' . $value . '"'
							)
							;
                        break;
                }
            }
        }
        return "[{$shortcode_label} {$fldName}{$attributes}]";
    }

    /**
     * Strip all \r\n from the template to try to "unbreak" Theme Forest themes.
     *
     * This is VERY ugly, but a lot of people use Theme Forest.  They have a known bug
     * that MANY Theme Forest authors have introduced which will change this:
     * <table
     *    style="display:none"
     *    >
     *
     * To this:
     * <table<br/>
     *    style="display:none"<br/>
     *    >
     *
     * Which really fucks things up.
     *
     * Envato response?  "Oh well, we will tell the authors but can't really fix anything."
     *
     * Now our plugin has this ugly slow formatting function which sucks balls.   But we need it
     * if we are going to not alienate a bunch of Envato users that will never tell us they had an
     * issue. :/
     *
     * @param string $inStr
     * @return string
     */
    function rawDeal($inStr) {
        return str_replace(array("\r","\n"),'',$inStr);
    }

     //------------------------------------------------------------------------
     // DEPRECATED
     //------------------------------------------------------------------------

     /**
      * Do not use, deprecated.
      *
      * @deprecated 4.0
      *
      * @var null $addingLocation
      */
     public $resultsString = null;

	/**
	 * Do not use, deprecated.
	 *
	 * @deprecated 4.0
	 */
	function CreateSliderButton( $a = null, $b = null, $c = null, $d = null, $e = null, $f = null ) {
		$this->slplus->helper->create_string_wp_setting_error_box( $this->slplus->createstring_Deprecated( __FUNCTION__ ) );
	}

	/**
	 * Do not use, deprecated.
	 *
	 * @deprecated 4.0
	 */
	function setResultsString( $a = null, $b = null, $c = null, $d = null, $e = null, $f = null ) {
		$this->slplus->helper->create_string_wp_setting_error_box( $this->slplus->createstring_Deprecated( __FUNCTION__ ) );
	}

	/**
	 * Do not use, deprecated.
	 *
	 * @deprecated 4.0
	 */
	function slp_render_search_form_tag_list( $a = null, $b = null, $c = null, $d = null, $e = null, $f = null ) {
		$this->slplus->helper->create_string_wp_setting_error_box( $this->slplus->createstring_Deprecated( __FUNCTION__ ) );
	}

	/**
	 * Do not use, deprecated.
	 *
	 * @deprecated 4.0
	 */
	function ShortcodeOrSettingEnabled( $a = null, $b = null, $c = null, $d = null, $e = null, $f = null ) {
		$this->slplus->helper->create_string_wp_setting_error_box( $this->slplus->createstring_Deprecated( __FUNCTION__ ) );
	}

}

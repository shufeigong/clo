<?php
require_once( SLPLUS_PLUGINDIR . 'include/base_class.object.php');

/**
 * The SLP Settings Class
 *
 * @property string     $form_action    what action to take with the form.
 * @property string     $form_enctype   form encryption type  default: '' , often 'multipart/form-data'
 * @property string     $form_name
 * @property string     $name
 * @property string     $prefix         optional: The settings prefix (default: SLPLUS_PREFIX).
 * @property string     $save_text      optional: The text for the save button. (default: blank, don't show save button)
 *
 * @package StoreLocatorPlus\Settings
 * @author Lance Cleveland <lance@charlestonsw.com>
 * @copyright 2012-2015 Charleston Software Associates, LLC
 *
 */
class SLP_Settings extends SLPlus_BaseClass_Object {
	public $form_action     = 'options.php';
    public $form_enctype    = '';
    public $form_name       = '';
	public $name;
	public $prefix = SLPLUS_PREFIX;
	public $save_text       = '';

	/**
	 * @var SLPlus_Settings_Section[]
	 */
    private $sections;

    /**
     * Instantiate a settings object.
     *
     * @param mixed[] $options
     */
    function __construct( $options = array() ) {
        $this->save_text = __('Save Changes'        , 'store-locator-le' );
	    $this->name      = __('Store Locator Plus'  , 'store-locator-le' );
		parent::__construct( $options );

	    // TODO: deprecate when all instantiations of new wpCSL_settings__slplus add 'uses_slplus' => true (GFL , SME, UML)
	    if ( ! isset( $this->slplus ) ) {
		    global $slplus_plugin;
		    $this->slplus = $slplus_plugin;
	    }
    }

     /**
      * Create a settings page panel.
      *
      * Does not render the panel, it simply creates the container to add stuff to for later rendering.
      *
      * @param array $params named array of the section properties, name is required.
      */
    function add_section($params) {
        if (!isset($this->sections[$params['name']])) {
            $this->sections[$params['name']] = new SLPlus_Settings_Section(
                array_merge(
                    $params,
                    array(
	                    'slplus'       => $this->slplus
						)
                )
            );
        }            
    }

    /**
     * Same as add_item but uses named params.
     *
     * 'type' => textarea, text, checkbox, dropdown, slider, list, submit_button, ..custom..
     *
     * NOTE: If use_prefix is false the automatic option saving in SLP 4.2 add-on framework will be disabled.
     * This can be useful for admin settings you do not want saved/restored between sessions.
     * It can suck if you do want that to happen though and will likely find this comment after spending
     * the past 30 minutes tearing your hair out wondering WTF is going on.
     *
     *
     *
     * @param array $params optional parameters
     *     @var 'section'   => string   text for section heading to put the setting in
     *     @var 'label'     => string   text that precedes the input
     *     @var 'setting'   => string   name of the setting (input ID)
     *     @var 'type'      => string   type of interface element ('checkbox'|'custom'|'details'|'dropdown'|'slider'|'sidelabel'|'subheader'|'submit'|'text'|'textarea')
     *     @var 'show_label'=> boolean  set to true to show the label (default: true)
     *     @var 'custom'    => string   the custom HTML output to render.
     */
    function add_ItemToGroup($params) {
	    if ( ! isset($params['section'] ) ) { $params['section'] = 'Settings'; }
	    if ( ! isset( $this->sections[ $params['section'] ] ) ) { return; }

	    // Name not set, but Setting is...
	    //
        if ( !isset( $params['name' ] ) && isset( $params['setting'] ) )  {

	        // use_prefix is on by default
	        //
	        if ( !isset( $params['use_prefix'] ) ) { $params['use_prefix'] = true; }

	        // Using a prefix? Craft the name with that attached...
	        //
	        if ( $params['use_prefix'] ) {

		        // If we have a prefix, set the separator to '-' by default
		        //
		        if ( ! isset( $params['separator'] ) ) { $params['separator'] = '-'; }

		        $params['name'] = SLPLUS_PREFIX .$params['separator']. $params['setting'] ;

		    // No prefix?  Use the name without one.
		    //
	        } else {
		        $params['name'] = $params['setting'];
	        }
        }

	    if ( !isset( $params['show_label' ] ) ) { $params['show_label'] = true; }

	    if (
		     ( $params['show_label'] && ! isset( $params['label' ] ) ) ||
	         ( ! isset( $params['setting' ] ) ) ) {
		    $defaultSettingName = wp_generate_password( 8, false );
	    }

        if ( $params['show_label'] && !isset( $params['label'        ] ) ) {
	        $params['label'     ] = __('Setting ','store-locator-le') . $defaultSettingName;
        }

        if ( !isset( $params['setting'      ] ) ) { $params['setting'   ] = $defaultSettingName; }

	    if ( !isset( $params['type'         ] ) ) { $params['type'      ] = 'text'; }
	    if ( !isset( $params['show_label'   ] ) ) { $params['show_label'] = true;   }

	    $this->sections[ $params['section'] ]->add_item( array_merge( array( 'prefix'        => SLPLUS_PREFIX ) , $params ) );
    }

    /**
     * Create the HTML for the plugin settings page on the admin panel.
     */
    function render_settings_page() {

	    $selectedNav = isset($_REQUEST['selected_nav_element'])?$_REQUEST['selected_nav_element']:'';

	    print
		    '<div id="wpcsl_container" class="wrap">'                                           .

		    "<h1>{$this->name}</h1>"                                                            .

		    "<form method='post' "                                                              .
			    "action='{$this->form_action}' "                                                .
			    ( ( $this->form_name    !== '' ) ? "id='{$this->form_name}' "           : '' )  .
			    ( ( $this->form_name    !== '' ) ? "name='{$this->form_name}' "         : '' )  .
			    ( ( $this->form_enctype !== '' ) ? "enctype='{$this->form_enctype}' "   : '' )  .
		        'class ="slplus_settings_form" '                                                .
			    ">"                                                                             .

		    "<input type='hidden' "                                                             .
			    "id='selected_nav_element' "                                                    .
			    "name='selected_nav_element' "                                                  .
			    "value='{$selectedNav}' "                                                       .
			    "/>"                                                                            ;

		settings_fields($this->prefix.'-settings');


        /**
         * Render all top menus first.
         * @var SLPlus_Settings_Section $section
         */
        foreach ($this->sections as $section) {
            if (isset($section->is_topmenu) && ($section->is_topmenu)) {
                $section->display();
            }
        }

        // Main area under tabs
        //
        print '<div id="main">';                                        // Open Main

        // Menu Area
        //
        $selectedNav = isset($_REQUEST['selected_nav_element'])?
                $_REQUEST['selected_nav_element']:
                ''
                ;
        $firstOne = true;
        echo
            '<div id="wpcsl-nav" style="display: block;">' .            // Open Nav
            '<ul>'
            ;
        foreach ($this->sections as $section) {
            if ($section->auto) {
                $friendlyName = strtolower(strtr($section->name, ' ', '_'));
                $friendlyDiv  = (isset($section->div_id) ?  $section->div_id : $friendlyName);
                $firstClass   = (
                                 ("#wpcsl-option-{$friendlyDiv}" == $selectedNav) ||
                                 ($firstOne && ($selectedNav == ''))
                                )?
                                ' first current open' :
                                '';
                $firstOne = false;

                $link_id = "wpcsl-option-{$friendlyDiv}";
                print "<li class='top-level general {$firstClass}'>"                .
                      "<a id='{$link_id}_sidemenu' name='{$link_id}_sidemenu' href='#{$link_id}' "    .
                            "title='{$section->name}'>"                             .
                      $section->name                                                .
                      '</a>'                                                        .
                      '</li>'
                    ;                
            }
        }

        echo
                '</ul>' .

                $this->save_button() .

            '</div>' .                                                  // Close Nav

            '<div class="slp_settings_area" > '             .           // Open Settings Area

	        '<div id="content" class="js">'              // Open Content
            ;

        // Draw each settings section as defined in the plugin config file
        //
        $firstClass = true;
        foreach ($this->sections as $section) {
            if ($section->auto) {
                if ($firstClass) {
                    $section->first = true;
                    $firstClass = false;
                }
                $section->display();
            }
        }

        echo
                            '</div>' .   	        // Close Content

                        '</div>' .                  // Close Settings Area

                    '</div>' .                      // Close main

		        '</form>' .

            '</div>'
        ;
    }

	/**
	 * Create the save button text.
	 *
	 * Set save_text to '' to prevent the save button.
	 *
	 * @return string
	 */
    private function save_button() {
	    if ( empty( $this->save_text ) ) { return ''; }

        return
	        '<div class="navsave">' .
	            sprintf('<input type="submit" class="button-primary" value="%s" />', $this->save_text  ) .
            '</div>'
	        ;
    }

	//------------------------------------------------------------------------
	// DEPRECATED
	//------------------------------------------------------------------------

	/**
	 * @deprecated 4.3.00
	 */
	function add_checkbox($a=null,$b=null,$c=null,$d=null,$e=null,$f=null) {
		$this->slplus->helper->create_string_wp_setting_error_box( $this->slplus->createstring_Deprecated(__FUNCTION__) );
	}

	/**
	 * @deprecated 4.3.00
	 */
	function add_input($a=null,$b=null,$c=null,$d=null,$e=null,$f=null) {
		$this->slplus->helper->create_string_wp_setting_error_box( $this->slplus->createstring_Deprecated(__FUNCTION__) );
	}

	/**
	 * @deprecated 4.3.00
	 */
	function add_item($a=null,$b=null,$c=null,$d=null,$e=null,$f=null) {
		$this->slplus->helper->create_string_wp_setting_error_box( $this->slplus->createstring_Deprecated(__FUNCTION__) );
	}

	/**
	 * @deprecated 4.3.00
	 */
	function add_slider($a=null,$b=null,$c=null,$d=null,$e=null,$f=null) {
		$this->slplus->helper->create_string_wp_setting_error_box( $this->slplus->createstring_Deprecated(__FUNCTION__) );
	}

	/**
	 * @deprecated 4.3.00
	 */
	function add_textbox($a=null,$b=null,$c=null,$d=null,$e=null,$f=null) {
		$this->slplus->helper->create_string_wp_setting_error_box( $this->slplus->createstring_Deprecated(__FUNCTION__) );
	}

	/**
	 * @deprecated 4.3.00
	 */
	function check_required($a=null,$b=null,$c=null,$d=null,$e=null,$f=null) {
		$this->slplus->helper->create_string_wp_setting_error_box( $this->slplus->createstring_Deprecated(__FUNCTION__) );
	}

	/**
	 * @deprecated 4.3.00
	 */
	function create_SettingsGroup($a=null,$b=null,$c=null,$d=null,$e=null,$f=null) {
		$this->slplus->helper->create_string_wp_setting_error_box( $this->slplus->createstring_Deprecated(__FUNCTION__) );
	}

}

if (class_exists( 'SLPlus_Settings_Group' ) == false) {

    /**
     * Manage sections of admin settings pages.
     *
     * @package SLPlus\Settings\Group
     * @author Lance Cleveland <lance@charlestonsw.com>
     * @copyright 2013 - 2015 Charleston Software Associates, LLC
     *
     * @property string $header the header
     * @property string $intro the starting text
     * @property string $slug the slug
     */
    class SLPlus_Settings_Group extends SLPlus_BaseClass_Object {
        public $intro;
        public $header;
        public $slug;

	    /**
	     * @var SLPlus_Settings_Item[]
	     */
	    private $items;


        /**
         * Add an item to the group.
         * 
         * @param mixed[] $params
         */
        function add_item($params) {
            $this->items[] = new SLPlus_Settings_Item( $params );
        }

        /**
         * Render a group.
         */
        function render_Group() {
            $this->render_Header();
            if (isset($this->items)) {
                foreach ($this->items as $item) {
                    $item->display();
                }
            }
            $this->render_Footer();
        }

        /**
         * Output the group footer.
         */
        function render_Footer() {
            print '</div></div>';
        }

        /**
         * Output the group header.
         */
        function render_Header() {
            echo
                "<div class='postbox' id='wpcsl_settings_group-{$this->slug}'>" .
	            '<div class="handlediv" title="' . __('Click to toggle' , 'store-locator-le' ) . '"><br/></div>' .
                "<h3 class='hndle ui-sortable-handle'><span>{$this->header}</span></h3>" .
                "<div class='inside'>" .
                (
                    ($this->intro != '')                                                                                   ?
                    "<div class='section_column_intro' id='wpcsl_settings_group_intro-{$this->slug}'>{$this->intro}</div>" :
                    ''
                )
                ;
        }
    }

}

/**
 * Manage sections of admin settings pages.
 *
 * @property boolean    $auto
 * @property string     $closing_html
 * @property string     $description
 * @property string     $div_id
 * @property string     $first              True if the first rendered section on the panel.
 * @property SLPlus_Settings_Group[] $groups
 * @property boolean    $headerbar
 * @property boolean    $innerdiv
 * @property boolean    $is_topmenu
 * @property string     $name
 * @property string     $opening_html
 *
 * @package SLPlus\Settings\Section
 * @author Lance Cleveland <lance@charlestonsw.com>
 * @copyright 2013 - 2015 Charleston Software Associates, LLC
 */
class SLPlus_Settings_Section extends SLPlus_BaseClass_Object {
    public $auto = true;
	public $closing_html = '';
	public $description = '';
    public $div_id;
    public $first = false;
	public $groups;
	public $headerbar = true;
    public $innerdiv = true;
    public $is_topmenu = false;
    public $name;
	public $opening_html = '';

    /**
     * Add an item to a section.
     * 
     * @param string $params
     */
    function add_item($params) {        
        
        // Manage Groups
        //
        if (empty($params['group'])) { $params['group'] = 'Settings'; }
        $groupSlug = strtolower(str_replace(' ','_',$params['group']));
        if (!isset($this->groups[$groupSlug])) {
            $this->groups[$groupSlug] =
                    new SLPlus_Settings_Group(
                                array(
	                                'slplus'    => $this->slplus,
                                    'slug'      => $groupSlug,
                                    'header'    => $params['group'],
                                    'intro'     => isset($this->description)?$this->description:''
                                )
                            );
            $this->description = '';
        }

        $this->groups[$groupSlug]->add_item($params);
    }

    /**
     * Render a section panel.
     *
     * Panels are rendered in the order they are put in the stack, FIFO.
     */
    function display() {
        $this->header();
        if (isset($this->groups)) {
            foreach ($this->groups as $group) {
                $group->render_Group();
            }
        }
        $this->footer();
    }

    /**
     * Render a section header.
     */
    function header() {
        $friendlyName = strtolower(strtr($this->name, ' ', '_'));
        $friendlyDiv  = (isset($this->div_id) ?  $this->div_id : $friendlyName);
        $groupClass   = $this->is_topmenu?'':'group';

        echo '<div '                                        .
            "id='wpcsl-option-{$friendlyDiv}' "                          .
            "class='{$groupClass}' "  .
            ">";
        
        if ($this->headerbar) {
            echo "<h1 class='subtitle'>{$this->name}</h1>";
        }

        print $this->opening_html;

        if ($this->innerdiv) {
            echo "<div class='inside section meta-box-sortables'>";
            if ( ! empty( $this->description  ) ) { print "<div class='section_description'>";  }
         }

         if (!empty($this->description)) { echo $this->description; }

         if ($this->innerdiv) {
            if (!empty($this->description)) { echo '</div>'; }
         }


    }

    /**
     * Should the section be show (display:block) now?
     * 
     * @return boolean
     */
    function show_now() {
        return ($this->first || $this->is_topmenu);
    }

    /**
     * Render a section footer.
     */
    function footer() {
        if ($this->innerdiv) {
            echo '</table></div>';
        }
	    print $this->closing_html;
        echo '</div>';
    }

}

/**
 * This class manages individual settings on the admin panel settings page.
 *
 * Items go inside sections.
 *
 * @property string     $custom
 * @property string     $data_field         The data field.
 * @property string     $description
 * @property boolean    $disabled
 * @property string     $id                 ID for field, defaults to name ('setting') if not set.
 * @property string     $label
 * @property string     $name
 * @property string     $onChange           onChange JavaScript for an input item.
 * @property string     $onClick            onClick JavaScript for an input item.
 * @property boolean    $empty_ok           Empty drop down menu items OK?
 * @property boolean    $show_label
 * @property string     $selectedVal        Value of item to be selected for a drop down object.
 * @property string     $type              'checkbox'|'custom'|'dropdown'|'slider'|'sidelabel'|'subheader'|'submit'|'text'|'textarea'
 * @property string     $value              Value of item for text boxes, etc.   For checkboxes, evaluated for 'on'|'1' to check the box.
 *
 * @package SLPlus\Settings\Item
 * @author Lance Cleveland <lance@charlestonsw.com>
 * @copyright 2013 - 2015 Charleston Software Associates, LLC
 */
class SLPlus_Settings_Item extends SLPlus_BaseClass_Object {
	public $custom;
    public $data_field;
	public $description;
	public $disabled = false;
	public $id;
	public $label;
	public $name;
	public $onChange = '';
	public $onClick = '';
	public $empty_ok = false;
	public $show_label = true;
	public $selectedVal = '';
	public $type = 'custom';
	public $value;

    //-------------------------
    // Methods
    //-------------------------

    /**
     * Constructor.
     *
     * @param mixed[] $options
     */
    function __construct( $options = array() ) {
		parent::__construct( $options );
	    $this->set_defaults();
    }

	/**
	 * Create the details string for output.
	 *
	 * @return string
	 */
	private function create_string_details() {
		return $this->custom;
	}

	/**
	 * Create the HTML for the drop down selectors.
	 *
	 * @return string
	 */
	private function create_string_dropdown() {
		return $this->slplus->helper->createstring_DropDownMenu(
			array(
				'id'          => $this->name,
				'name'        => $this->name,
				'items'       => $this->custom,
				'onchange'    => $this->onChange,
				'disabled'    => $this->disabled,
				'selectedVal' => $this->selectedVal,
				'empty_ok'    => $this->empty_ok,
			)
		);
	}

	/**
	 * Create the drop down for the 'list' input types.
	 *
	 * If $type is 'list' then $custom is a hash used to make a <select>
	 * drop-down representing the setting.  This function returns a
	 * string with the markup for that list.
	 *
	 * The selected value will use the get_option() on the name of the drop down,
	 * with a default being allowed in the $value parameter.
	 *
	 * @return string
	 */
	private function create_string_list() {
		$content =
			"<select class='csl_select' ".
            "data-field='{$this->data_field}' ".
			"name='".$this->name."' ".
			$this->onChange .
			"/>"
		;
		$selectMatch = $this->value;

		foreach ($this->custom as $key => $value) {
			if ($selectMatch === $value) {
				$content .= "<option class='csl_option' value=\"$value\" " .
				            "selected=\"selected\">$key</option>\n";
			} else {
				$content .= "<option class='csl_option'  value=\"$value\">$key</option>\n";
			}
		}

		$content .= "</select>\n";

		return $content;
	}

    /**
     * Render the item to the page.
     *
     */
    function display() {


	    // Value Provided - Use That
	    //
        if ( isset ( $this->value ) ) {
	        $value_to_show = htmlspecialchars( $this->value );

	    // No value?  Get option from database.
	    //
        } else {
	        $this->value = get_option( $this->name );
	        if ( is_array( $this->value ) ) {
		        $this->value = print_r( $this->value, true );
	        }
	        $value_to_show = htmlspecialchars( $this->value );
        }

	    if ( ! isset( $this->id ) ) { $this->id = $this->name; }
        if ( ! isset( $this->data_field  ) ) { $this->data_field = $this->id; }

	    if ( ! empty( $this->onChange ) ) { $this->onChange = "onchange='{$this->onChange}'"; }

	    $disabled_class     = $this->disabled ? 'disabled'            : '' ;
	    $disabled_property  = $this->disabled ? 'disabled="disabled"' : '' ;

        echo '<div class="form_entry">';

        // Show label wrapper.
        //
        echo "<div class='wpcsl-input wpcsl-{$this->type} {$disabled_class}'>";
        if ($this->show_label) {
            echo "<label for='{$this->name}'>{$this->label}</label>";
        }

        // Type Processing
        //
        switch ($this->type) {
            case 'textarea':
                echo
	                '<textarea ' .
	                    "id='{$this->id}' ".
	                    "name='{$this->name}' ".
                        "data-field='{$this->data_field}' ".
	                    'cols="50" '.
	                    'rows="5" '.
	                    $disabled_property .
	                    '>'.$value_to_show .'</textarea>';
                break;

            case 'text':
                echo "<input type='text' id='{$this->id}' name='{$this->name}' data-field='{$this->data_field}' value='{$value_to_show}' {$disabled_property} {$this->onChange} />";
                break;

            case 'checkbox':
				$checked  = $this->slplus->is_CheckTrue($value_to_show) ? 'checked'             : '';
                echo  "<input type='checkbox' id='{$this->id}' name='{$this->name}'  data-field='{$this->data_field}' value='1' {$disabled_property} {$checked}/>";
                break;

            case 'slider':
                $setting = $this->name;
                $label   = '';
                $checked = ($value_to_show ? 'checked' : '');
                $onClick = 'onClick="'.
                    "jQuery('input[id={$setting}]').prop('checked',".
                        "!jQuery('input[id={$setting}]').prop('checked')" .
                        ");".
                    '" ';

                echo
                    "<input type='checkbox' id='$setting' name='$setting' style='display:none;' $checked>" .
                    "<div id='{$setting}_div' class='onoffswitch-block'>" .
                    "<span class='onoffswitch-pretext'>$label</span>" .
                    "<div class='onoffswitch'>" .
                    "<input type='checkbox' name='onoffswitch' class='onoffswitch-checkbox' data-field='{$this->data_field}' id='{$setting}-checkbox' $checked>" .
                    "<label class='onoffswitch-label' for='{$setting}-checkbox'  $onClick>" .
                    '<div class="onoffswitch-inner"></div>'.
                    "<div class='onoffswitch-switch'></div>".
                    '</label>'.
                    '</div>' .
                    '</div>'
                    ;

                    if ( ! isset( $this->slplus->slider_rendered ) ) {
                        $this->slplus->slider_rendered=true;
                        echo
                            "<style type='text/css'>" .
                                "    .onoffswitch-inner:before { content: '".__('ON','store-locator-le') ."'; } " .
                                "    .onoffswitch-inner:after  { content: '".__('OFF','store-locator-le')."'; } " .
                            "</style>"
                            ;
                    }

                break;


	        // TYPE: sidelabel
	        // Displays a vertical text label on the admin UI.
	        //
	        case 'sidelabel':
		        if ( ! empty( $this->label ) ) { echo "<div class='sidelabel_text'>{$this->label}</div>"; }
		        break;

            // TYPE: subheader
            // Displays  the label (label) in a H3 tag with the description in a paragraph below.
            //
            case 'subheader':
                if ( ! empty( $this->label ) ) { echo "<h3>{$this->label}</h3>"; }
                if ( ! empty( $this->description  ) ) { echo "<p class='slp_subheader_description' id='{$this->name}_p'>{$this->description}</p>"; }
                $this->description = null;
                break;

	        // TYPE: details
	        //
	        case 'details':
				echo $this->create_string_details();
		        break;

            // TYPE: dropdown
            //
            case 'dropdown':
                echo $this->create_string_dropdown();
                break;

            // TYPE: list
            //
            case 'list':
                echo $this->create_string_list();
                break;
                
            // TYPE: submit
            //
	        case 'submit':
            case 'submit_button':
                echo
                    '<input ' .
                        'class="button-primary" '   .
                        'type="submit" '            .
                        'value="'.$value_to_show.'" '    .
                        ( ! empty($this->onClick) ? 'onClick="'.$this->onClick.'" ' : '' ) .
                        '>';
                break;                

            // TYPE: custom
            //
            default:
                echo $this->custom;
                break;

        }

        // Close show label wrapper.
        //
        echo '</div>';

        // Help text via description.
        //
        if ($this->description != null) {
            print $this->slplus->helper->CreateHelpDiv($this->name,$this->description);
        }

        // Close the div.
        //
        echo '</div>';
    }

	/**
	 * Set defaults for various item types.
	 */
	function set_defaults() {
		switch ( $this->type ) {

			// DETAILS
			//
			// The text/HTML can go in 'custom' or 'description' when calling add_ItemToGroup
			// No other settings are required.
			//
			case 'details':
				if ( empty( $this->custom ) ) { $this->custom = $this->description; }
				$this->description  = null;
				$this->show_label   = false;
				$this->value        = '';
				break;

			// SIDELABEL
			//
			case 'sidelabel':
				$this->show_label = false;
				break;


			// SUBHEADER
			//
			// Place the subheader text in label.   Set the section and group name.
			// All else can be unset.
			//
			case 'subheader':
				$this->show_label = false;
				break;
		}
	}

}

// TODO: @deprecate when all wpCSL_settings__slplus references are removed (ELM, GFI, GFL, REX, SME, UML )
//	 Rename wpCSL_settings_slplus to SLP_Settings.
class  wpCSL_settings__slplus extends SLP_Settings {}

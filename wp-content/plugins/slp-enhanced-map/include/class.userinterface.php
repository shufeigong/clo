<?php
if (! class_exists('SLPEnhancedMap_UI')) {
    require_once(SLPLUS_PLUGINDIR.'/include/base_class.userinterface.php');

    /**
     * Holds the UI-only code.
     *
     * This allows the main plugin to only include this file in the front end
     * via the wp_enqueue_scripts call.   Reduces the back-end footprint.
     *
     * @package StoreLocatorPlus\EnhancedMap\UI
     * @author Lance Cleveland <lance@charlestonsw.com>
     * @copyright 2014 - 2015 Charleston Software Associates, LLC
     *
     * @property        SLPEnhancedMap      $addon
     *
     */
    class SLPEnhancedMap_UI  extends SLP_BaseClass_UI {
        public $addon;

        /**
         * Add WordPress and SLP hooks and filters.
         *
         * WP syntax reminder: add_filter( <filter_name> , <function> , <priority> , # of params )
         *
         * Remember: <function> can be a simple function name as a string
         *  - or - array( <object> , 'method_name_as_string' ) for a class method
         * In either case the <function> or <class method> needs to be declared public.
         *
         * @link http://codex.wordpress.org/Function_Reference/add_filter
         *
         */
        public function add_hooks_and_filters() {
            add_filter( 'slp_shortcode_atts'    , array( $this , 'extend_main_shortcode'    ) , 15 , 3 );
            add_filter( 'slp_js_options'        , array( $this , 'modify_js_options'        ) , 15 , 1 );
            add_filter( 'slp_map_html'          , array( $this , 'filter_ModifyMapOutput'   ) , 05 , 1 );
        }

        /**
         * Generate the HTML for the map on/off slider button if requested.
         *
         * @return string HTML for the map slider.
         */
        function createstring_MapDisplaySlider() {

            $content = '';

            if  ( $this->slplus->is_CheckTrue( $this->addon->options['show_maptoggle'] ) ) {
                $content =
                    $this->CreateSliderButton(
                        'maptoggle',
                        __('Map','csa-slp-em'),
                        ! $this->slplus->is_CheckTrue( $this->addon->options['hide_map'] ),
                        "jQuery('#map').toggle();jQuery('#slp_tagline').toggle();"
                    );
            }

            return $content;
        }

	    /**
	     * Return the HTML for a slider button.
	     *
	     * The setting parameter will be used for several things:
	     * the div ID will be "settingid_div"
	     * the assumed matching label option will be "settingid_label" for WP get_option()
	     * the a href ID will be "settingid_toggle"
	     *
	     * @param string $setting the ID for the setting
	     * @param string $label the default label to show
	     * @param boolean $isChecked default on/off state of checkbox
	     * @param string $onClick the onClick javascript
	     * @return string the slider HTML
	     */
	    function CreateSliderButton($setting=null, $label='', $isChecked = true, $onClick='') {
		    if ($setting === null) { return ''; }

		    $label   = $this->slplus->get_item($setting.'_label',$label);
		    $checked = ($isChecked ? 'checked' : '');
		    $onClick = (($onClick === '') ? '' : ' onClick="'.$onClick.'"');

		    $content =
			    "<div id='{$setting}_div' class='onoffswitch-block'>" .
			    "<span class='onoffswitch-pretext'>$label</span>" .
			    "<div class='onoffswitch'>" .
			    "<input type='checkbox' name='onoffswitch' class='onoffswitch-checkbox' id='{$setting}-checkbox' $checked>" .
			    "<label class='onoffswitch-label' for='{$setting}-checkbox'  $onClick>" .
			    '<div class="onoffswitch-inner"></div>'.
			    "<div class='onoffswitch-switch'></div>".
			    '</label>'.
			    '</div>' .
			    '</div>';
		    return $content;
	    }

        /**
         * Extends the main SLP shortcode approved attributes list, setting defaults.
         *
         * This will extend the approved shortcode attributes to include the items listed.
         * The array key is the attribute name, the value is the default if the attribute is not set.
         *
         * @param mixed[] $valid_attributes - current list of approved attributes
         * @param array $attributes_in_use attributes from the shortcode as entered
         * @param string $content
         * @return mixed[]
         */
        public function extend_main_shortcode($valid_attributes, $attributes_in_use , $content ) {

	        $this->manage_shortcode_attributes( $attributes_in_use );

            return array_merge(
                array(
                    'center_map_at'     => null,
                    'hide_map'          => null,
                    'show_maptoggle'    => null,
                ),
                $valid_attributes
            );
        }

	    /**
	     * Take the shortcode attributes and apply them to our addon or slplus options.
	     *
	     * Should be temporary as long as nothing in the execution chain calls update_options().
	     *
	     * @param null $attributes_in_use
	     */
	    private function manage_shortcode_attributes( $attributes_in_use = null ) {
		    if ( is_null( $attributes_in_use ) ) { return; }
		    if ( !is_array( $attributes_in_use ) ) { return; }
		    foreach ( $attributes_in_use as $attribute => $value ) {
			    switch ( $attribute ) {

				    case 'show_maptoggle':
					    $this->addon->options[ 'show_maptoggle' ] = $this->slplus->is_CheckTrue($value) ? '1' : '0';
					    break;

				    case 'center_map_at':
					    $this->slplus->options[ 'map_center' ] = $value;
					    break;

				    case 'hide_map':
					    $this->addon->options[ 'hide_map' ] = $this->slplus->is_CheckTrue($value) ? '1' : '0';
					    break;

			    }
		    }
	    }

        /**
         * Modify the slplus.options object going into SLP.js
         *
         * @param mixed[] $options
         * @return mixed
         */
        function modify_js_options( $options ) {
            if ( $this->slplus->is_CheckTrue( $this->addon->options['hide_bubble'] ) ) { $this->addon->options['bubblelayout'] = ''; }
			if ( ! empty( $this->addon->options['google_map_style'] ) ) {
				$this->addon->options['google_map_style'] = sanitize_text_field( $this->addon->options['google_map_style'] );
			}
            return array_merge( $options , $this->addon->options );
        }

        /**
         * Modify the map layout.
         *
         * @param string  $HTML
         * @return string
         */
        function filter_ModifyMapOutput( $HTML ) {

	        // Hide Map
            if ( $this->slplus->is_CheckTrue( $this->addon->options['hide_map'] ) ) {
                return '<div id="map" style="display:none;"></div>';
            }

            // Map Layout
            $HTML .=
                empty($this->addon->options['maplayout'])            ?
                    $this->slplus->defaults['maplayout']    :
                    $this->addon->options['maplayout']             ;


            // Map Toggle Interface Element
            //
            if (!isset($this->slplus->data['show_maptoggle'])) {
	            $this->slplus->data['show_maptoggle'] = $this->addon->options['show_maptoggle'];
            }

            $HTML = $this->createstring_MapDisplaySlider() . $HTML;

            // Map hidden
            //
            if ($this->addon->options['map_initial_display'] == 'hide') {
                $HTML =
                    '<div id="map_box_map">' .
                    $HTML .
                    '</div>';

            // Map Displayed
            //
            } else if ($this->addon->options['map_initial_display'] == 'image') {

                // Starting Image
                //
                $startingImage          = $this->addon->options['starting_image'];
                $startingImageActive    = !empty($startingImage);
                if ($startingImageActive) {

                    // Make sure URL starts with the plugin URL if it is not an absolute URL
                    //
                    $startingImage =
                        ((preg_match('/^http/',$startingImage) <= 0) ?SLPLUS_PLUGINURL:'') .
                        $startingImage
                    ;

                    $HTML =
                        '<div id="map_box_image" ' .
                        'style="'.
                        "width:". $this->slplus->options_nojs['map_width'].
                        $this->slplus->options_nojs['map_width_units'] .
                        ';'.
                        "height:".$this->slplus->options_nojs['map_height'].
                        $this->slplus->options_nojs['map_height_units'].
                        ';'.
                        '"'.
                        '>'.
                        "<img src='{$startingImage}'>".
                        '</div>' .
                        '<div id="map_box_map">' .
                        $HTML .
                        '</div>'
                    ;
                }
            }

            return $HTML;
        }
    }
}
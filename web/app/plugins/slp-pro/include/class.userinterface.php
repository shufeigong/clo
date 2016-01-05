<?php
if (! class_exists('SLPPro_UI')) {
    require_once(SLPLUS_PLUGINDIR.'/include/base_class.ui.php');

    /**
     * Holds the UI-only code.
     *
     * This allows the main plugin to only include this file in the front end
     * via the wp_enqueue_scripts call.   Reduces the back-end footprint.
     *
     * @package StoreLocatorPlus\SLPPro\UI
     * @author Lance Cleveland <lance@charlestonsw.com>
     * @copyright 2014 Charleston Software Associates, LLC
     */
    class SLPPro_UI extends SLP_BaseClass_UI {

        //-------------------------------------
        // Properties
        //-------------------------------------

        /**
         * This addon pack.
         *
         * @var \SLPPro $addon
         */
        public $addon;

        //-------------------------------------
        // Methods : activity
        //-------------------------------------

        /**
         * Add UI specific hooks and filters.
         *
         * Overrides the base class which is just a stub placeholder.
         */
        public function add_hooks_and_filters() {

            add_filter( 'slp_ui_headers'                , array( $this , 'createstring_CSSForHeader'    )       );

            add_filter( 'slp_shortcode_atts'            , array( $this , 'extend_main_shortcode'        )       );
            add_filter( 'shortcode_slp_searchelement'   , array( $this , 'process_SearchElement'        )       );

            add_filter( 'slp_layout'                    , array( $this , 'createstring_LocatorLayout'   ) , 5   );


            // Pro Pack Tag UI
            //
            if ($this->addon->options['tag_selector'] !== 'none') {
                add_filter( 'slp_searchlayout' , array( $this , 'modify_SearchLayout' ) , 999 );
            }

        }

        /**
         * Add Custom CSS to the UI header.
         *
         * @param string $HTML
         * @return string
         */
        function createstring_CSSForHeader($HTML) {
            return
                $HTML .
                strip_tags($this->addon->options['custom_css'])
                ;
        }

        /**
         * Take over the search form layout.
         *
         * @param string $HTML the original SLP layout
         * @return string the modified layout
         */
        function createstring_LocatorLayout($HTML) {
            if ( empty( $this->addon->options['layout' ]) ) { return $HTML; }
            return $this->addon->options['layout'];
        }

        /**
         * Puts the tag list on the search form for users to select tags by drop down.
         *
         * @param string[] $tags tags as an array of strings
         * @param boolean $showany show the any pulldown entry if true
         * @return string
         */
        function createstring_SearchFormTagDropdown($tags, $showany = false) {

            $HTML = "<select id='tag_to_search_for' >";

            // Show Any Option (blank value)
            //
            if ($showany) {
                $HTML.= "<option value=''>" .
                    get_option(SLPLUS_PREFIX . '_tag_pulldown_first', __('Any', 'csa-slp-pro')) .
                    '</option>';
            }

            foreach ($tags as $selection) {
                $clean_selection = preg_replace('/\((.*)\)/', '$1', $selection);
                $HTML.= "<option value='$clean_selection' ";
                $HTML.= (preg_match('#\(.*\)#', $selection)) ? " selected='selected' " : '';
                $HTML.= ">$clean_selection</option>";
            }
            $HTML.= "</select>";
            return $HTML;
        }

        /**
         *  Puts the tag list on the search form for users to select tags by radio buttons
         *
         * @param string[] $tags tags as an array of strings
         * @param bool $showany how the any pulldown entry if true
         * @return string
         */
        function createstring_SearchFormTagRadioButtons($tags, $showany = false) {
            $HTML = '';
            $thejQuery = "onClick='" .
                "jQuery(\"#tag_to_search_for\").val(this.value);" .
                (
                ($this->slplus->settings->get_item('enhanced_search_auto_submit', 0) == 1) ?
                    "jQuery(\"#searchForm\").submit();" :
                    ''
                ) .
                "'"
            ;
            $oneChecked = false;
            $hiddenValue = '';
            foreach ($tags as $tag) {
                $checked = false;
                $clean_tag = preg_replace("/\((.*?)\)/", '$1', $tag);
                if (
                    ($clean_tag != $tag) ||
                    (!$oneChecked && !$showany && ($tag == $tags[0]))
                ) {
                    $checked = true;
                    $oneChecked = true;
                    $hiddenValue = $clean_tag;
                }
                $HTML .=
                    '<span class="slp_checkbox_entry">' .
                    "<input type='radio' name='tag_to_search_for' value='$clean_tag' " .
                    $thejQuery .
                    ($checked ? ' checked' : '') . ">" .
                    $clean_tag .
                    '</span>'
                ;
            }

            if ($showany) {
                $HTML .=
                    '<span class="slp_radio_entry">' .
                    "<input type='radio' name='tag_to_search_for' value='' " .
                    $thejQuery .
                    ($oneChecked ? '' : 'checked') . ">" .
                    "Any" .
                    '</span>';
            }

            // Hidden field to store selected tag for processing
            //
            $HTML .= "<input type='hidden' id='tag_to_search_for' name='hidden_tag_to_search_for' value='$hiddenValue'>";

            if (!empty($HTML)) {
                $HTML = '<div class="slp_checkbox_group">' . $HTML . '</div>';
            }

            return $HTML;
        }

        /**
         * Create the tag input div wrapper and label.
         *
         * @param string $innerHTML the input field portion of the div.
         * @return string the div and label tags added.
         */
        function createstring_TagDiv( $innerHTML ) {
            return
                '<div id="search_by_tag" class="search_item">' .
                '<label for="tag_to_search_for">' .
                $this->addon->options['tag_label'] .
                '</label>' .
                $innerHTML .
                '</div>';
        }

        /**
         * Add our custom tag selection div to the search form.
         *
         * @return string the HTML for this div appended to the other HTML
         */
        function createstring_TagSelector() {
            $this->addon->debugMP('msg', __FUNCTION__);

            // Only With Tag shortcode.
            // Set tag selector to hidden.
            //
            if (!empty($this->slplus->data['only_with_tag'])) {
                $this->addon->options['tag_selector'] = 'hidden';
                $this->addon->options['tag_selections'] = sanitize_title($this->slplus->data['only_with_tag']);
                $showTagInput = true;
            }

            // No Tag List For Drop Down
            // Set tag selector to textinput
            //
            if ($this->addon->options['tag_selector'] === 'dropdown') {
                if (
                    isset( $this->slplus->data['tags_for_pulldown' ]) ||
                    isset( $this->slplus->data['tags_for_dropdown' ])
                ) {
                    $this->addon->options['tag_selections'] = $this->slplus->data['tags_for_pulldown'] . $this->slplus->data['tags_for_dropdown'];
                }

                // No pre-selected tags, use input box
                //
                if ($this->addon->options['tag_selections'] === '') {
                    $this->addon->options['tag_selector'] = 'textinput';
                    $this->addon->options['tag_selections'] = (isset($this->slplus->data['only_with_tag'])) ?
                        $this->slplus->data['only_with_tag'] :
                        '';
                }
            }

            // Process the category selector type
            //
            switch ($this->addon->options['tag_selector']) {

                // None
                //
                case 'none':
                    $HTML = '';
                    break;

                // Hidden Field
                //
                case 'hidden':
                    $HTML = "<input type='hidden' " .
                        "name='tag_to_search_for' " .
                        "id='tag_to_search_for' " .
                        "value='{$this->addon->options['tag_selections']}' " .
                        "textvalue='{$this->slplus->data['only_with_tag']}' " .
                        '/>';
                    break;

                // Text Input
                //
                case 'textinput':
                    $this->addon->options['tag_label'] = get_option(SLPLUS_PREFIX . '_search_tag_label', '');
                    $HTML = $this->createstring_TagDiv(
                        "<input type='text' " .
                        "name='tag_to_search_for' " .
                        "id='tag_to_search_for' size='50' " .
                        "value='{$this->addon->options['tag_selections']}' " .
                        "/>"
                    );
                    break;

                // Radio Button Selector for Tags on UI
                //
                case 'radiobutton':
                    $this->addon->options['tag_label'] = get_option(SLPLUS_PREFIX . '_search_tag_label', '');
                    $tag_selections = explode(",", $this->addon->options['tag_selections']);
                    if (count($tag_selections) > 0) {
                        $HTML = $this->createstring_SearchFormTagRadioButtons(
                            $tag_selections, $this->slplus->is_CheckTrue(get_option(SLPLUS_PREFIX . '_show_tag_any', '0'))
                        );
                        $HTML = $this->createstring_TagDiv($HTML);
                    } else {
                        $HTML = '';
                        $this->addon->debugMP('msg', '', 'The tag selection list is empty.');
                    }
                    break;

                // Drop Down Style Selector for Tags on UI
                //
                case 'dropdown':
                    $this->addon->options['tag_label'] = get_option(SLPLUS_PREFIX . '_search_tag_label', '');
                    $tag_selections = explode(",", $this->addon->options['tag_selections']);
                    if (count($tag_selections) > 0) {
                        $HTML = $this->createstring_SearchFormTagDropdown(
                            $tag_selections, $this->slplus->is_CheckTrue(get_option(SLPLUS_PREFIX . '_show_tag_any', '0'))
                        );
                        $HTML = $this->createstring_TagDiv($HTML);
                    } else {
                        $HTML = '';
                        $this->addon->debugMP('msg', '', 'The tag selection list is empty.');
                    }
                    break;

                default:
                    $HTML = '';
                    break;
            }

            return $HTML;
        }

        /**
         * Extends the main SLP shortcode approved attributes list, setting defaults.
         *
         * This will extend the approved shortcode attributes to include the items listed.
         * The array key is the attribute name, the value is the default if the attribute is not set.
         *
         * @param mixed[] $valid_atts - current list of approved attributes
         * @return mixed[]
         */
        function extend_main_shortcode( $valid_atts ) {

            return array_merge(
                array(
                    'endicon' => null,
                    'homeicon' => null,
                    'only_with_tag' => null,
                    'tags_for_pulldown' => null,
                    'tags_for_dropdown' => null,
                ), $valid_atts
            );
        }

        /**
         * Add tags selector to search layout if it is not already part of the layout.
         *
         * @param $layout
         * @return string
         */
        public function modify_SearchLayout($layout) {
            if (
                preg_match('/\[slp_search_element\s+.*dropdown_with_label="tag".*\]/i', $layout) ||
                preg_match('/\[slp_search_element\s+.*selector_with_label="tag".*\]/i', $layout)
            ) {
                return $layout;
                return $layout;
            }
            return $layout . '[slp_search_element selector_with_label="tag"]';
        }


        /**
         * Perform extra search form element processing.
         *
         * @param mixed[] $attributes
         * @return mixed[]
         */
        public function process_SearchElement( $attributes ) {
            $this->addon->debugMP('pr', __FUNCTION__, $attributes);

            foreach ($attributes as $name => $value) {

                switch (strtolower($name)) {

                    // dropdown_with_label="tag"
                    //
                    case 'dropdown_with_label':
                        switch ($value) {
                            case 'tag':
                                $this->addon->options['tag_selector'] = 'dropdown';
                                return array(
                                    'hard_coded_value' => $this->createstring_TagSelector()
                                );
                                break;

                            default:
                                break;
                        }
                        break;

                    // selector_with_label="tag"
                    //
                    case 'selector_with_label':
                        switch ($value) {
                            case 'tag':
                                return array(
                                    'hard_coded_value' => $this->createstring_TagSelector()
                                );
                                break;

                            default:
                                break;
                        }
                        break;

                    default:
                        break;
                }
            }

            return $attributes;
        }


    }
}
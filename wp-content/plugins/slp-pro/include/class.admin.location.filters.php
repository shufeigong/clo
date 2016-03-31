<?php
if (!defined( 'ABSPATH'     )) { exit;   } // Exit if accessed directly, dang hackers

// Make sure the class is only defined once.
//
if (!class_exists('SLPProAdminLocationFilters')) {
    
    /**
     * Admin Filters for Pro Pack
     *
     * @package StoreLocatorPlus\Admin\Filters
     * @author Lance Cleveland <lance@charlestonsw.com>
     * @copyright 2014 Charleston Software Associates, LLC
     */
    class SLPProAdminLocationFilters {

        //----------------------------------
        // Properties
        //----------------------------------        
        
        /**
         * The plugin object
         *
         * @var \SLPPro $plugin
         */
        var $addon;
        
        /**
         * If true, reset the filters.
         * 
         * @var \boolean
         */
        var $reset = false;

        /**
         * The base plugin object.
         *
         * @var \SLPlus $slplus
         */
        var $slplus;
        
        
        /**
         * True if a filter is active.
         * 
         * @var boolean
         */
        public $filter_active = false;
        

        //----------------------------------
        // Methods
        //----------------------------------        

        /**
         *
         * @param mixed[] $params
         */
        function __construct($params) {
            // Do the setting override or initial settings.
            //
            if ($params != null) {
                foreach ($params as $name => $value) {
                    $this->$name = $value;
                }
            }            
        }
        
        /**
         * 
         * @param type $HTML
         * @return type
         */
        public function createstring_FilterDisplay( $HTML ) {
            if ( $this->filter_active ) {
                $HTML .= 
                   '<div class="location_filters">' .
                        '<span class="location_filter_label">' . 
                            __('Location Filter: ' , 'csa-slp-pro' ) . 
                        '</span>' .
                        $this->addon->admin->action_ManageLocations_ByProperty('') .
                   '</div>';
            }
            return $HTML;
        }

        /**
         * Create an input field on the export locations filter page with a div wrapper.
         *
         * @param string $field_name field name and ID
         * @param string $placeholder placeholder text, defaults to ''
         * @param string $label
         * @param bool $joiner
         * @return string HTML for the input field and wrapping div
         */
        function createstring_FilterInput($field_name,$placeholder='', $label='', $joiner = true) {
            
            $value = ( ! $this->reset && isset( $_REQUEST[$field_name] ) ) ? $_REQUEST[$field_name] : '';
            
            return
                '<div class="form_entry">' .
                    ( ! empty ($label) ? "<label for='{$field_name}'>{$label}</label>"              : '' ) .
                    ( $joiner          ? $this->createstring_FilterJoinWith("{$field_name}_joiner") : '' ) .                            
                    "<input id='{$field_name}' name='{$field_name}' class='postform' type='text' value='{$value}' placeholder='{$placeholder}' />" .
                '</div>'
                ;
        }

        /**
         * Create the AND/OR logic joiner for export filters.
         * 
         * @param string $field_name the field name and ID
         * @return string HTML for the filter field "joiner" selector
         */
        function createstring_FilterJoinWith($field_name) {
            $value = ( ! $this->reset && isset( $_REQUEST[$field_name] ) ) ? $_REQUEST[$field_name] : '';
            $and_selected = ( $value === 'AND' ) ? ' selected ' : '';
            $or_selected  = ( $value === 'OR'  ) ? ' selected ' : '';            
            
            return
                "<select id='$field_name' name='$field_name' class='postform'>".
                    "<option value='AND' {$and_selected}>".__('and','csa-slp-pro').'</option>'.
                    "<option value='OR'  {$or_selected} >".__('or' ,'csa-slp-pro').'</option>'.
                '</select>'
                ;
        }

        /**
         * Create the location filter form for export filters.
         */
        function createstring_LocationFilterForm() {

           $HTML =
                $this->createstring_FilterInput('name',__('Store Name My Place or My* or *Place','csa-slp-pro'),__('Name','csa-slp-pro'), false) .
                $this->createstring_LocationPropertyDropdown( 'state' , true ) .
                $this->createstring_FilterInput('zip_filter',__('Zip Code 29464 or 294* or *464','csa-slp-pro'),__('Zip','csa-slp-pro')) .
                $this->createstring_LocationPropertyDropdown( 'country' , true ) 
                ;

           return 
                '<div id="csa-slp-pro-location-filters">'               .
                apply_filters( 'slp-pro_locations_filter_ui', $HTML )   .
                '</div>'
                ;
        }

        /**
         * Create the HTML string for a state selection drop down from the data tables.
         *
         * @param string $location_property - which location property to use to build the drop down options
         * @return string the HTML for the drop down menu.
         */
        function createstring_LocationPropertyDropdown( $location_property = 'state' , $joiner = true ) {

            $input_id   = $location_property . '_filter';
            switch ( $location_property ) {
                case 'country':
                    $label      = __('Country','csa-slp-pro');
                    $all_option = __('All Countries','csa-slp-pro');
                    break;

                case 'state':
                default:
                    $label      = __('State','csa-slp-pro');
                    $all_option = __('All States','csa-slp-pro');
                    break;
            }
            
            $selected_value = ( ! $this->reset &&  isset( $_REQUEST[$input_id] ) && ! empty( $_REQUEST[$input_id] ) ) ? $_REQUEST[$input_id] : NULL;

            return
                
                '<div class="form_entry">' .
                    "<label for='{$input_id}'>{$label}</label>" .
                    ( $joiner ? $this->createstring_FilterJoinWith( $location_property . "_joiner") : '' ) .
                    "<select id='{$input_id}' name='{$input_id}' class='postform'>".
                        "<option value=''>{$all_option}</option>".
                        $this->createstring_LocationPropertyDropdownOptions( $location_property , $selected_value ).
                    '</select>'.
                '</div>'
                ;
        }

        /**
         * Create the HTML string for the individual options on the state drop down.
         *
         * o location_property values 'state' (default), 'country'
         *
         * @param string $location_property - which location property to use to build the drop down options
         * @param string $selected_value - the value to be selected, usually from a prior form post
         * @return string the HTML all of the state option selectors in the dropdown.
         */
        function createstring_LocationPropertyDropdownOptions( $location_property = 'state' , $selected_value = NULL ) {

            // Set SQL command based on property
            //
            switch ( $location_property ) {
                case 'country':
                    $sql_command = 'select_country_list';
                    break;
                case 'state':
                default:
                    $sql_command = 'select_state_list';
                    break;
            }

            $HTML = '';
            $offset = 0;
            while ( ( $location = $this->slplus->database->get_Record( $sql_command , '' , $offset++ ) )!= NULL ) {
                $is_selected = ( $location[$location_property] === $selected_value ) ? 'selected' : '';
                $HTML .= "<option value='{$location[$location_property]}' $is_selected>{$location[$location_property]}</option>";
            }
            return $HTML;
        }
        
        /**
         * Create the location selector SQL command with where clause parameters.
         * 
         * @param \SLPlus_Data $database
         * @return mixed[] string = sql command, string[] = where clause parameters
         */
        public function create_LocationSQLCommand( $request_data ) {

            // Formdata Parsing
            //
            $this->formdata = array(
                'name'              => '',
                'state_filter'      => '',
                'state_joiner'      => 'AND',
                'zip_filter'        => '',
                'zip_joiner'        => 'AND',
                'country_filter'    => '',
                'country_joiner'    => 'AND',
            );
            $this->formdata = wp_parse_args($request_data,$this->formdata);

            // Which Records?
            //
            $sqlCommand = array('selectall','where_default');
            $sqlParams = array();

            // Export Name Pattern Matches
            //
            if ( ! empty( $this->formdata['name'] ) ) {
                add_filter('slp_ajaxsql_where',array($this,'filter_ExtendGetSQLWhere_Name'));
                $sqlParams[]  = $this->modifystring_WildCardToSQLLike($this->formdata['name']);
                $this->filter_active = true;
            }


            // State Filter
            //
            if ( ! empty( $this->formdata['state_filter'] ) ) {
                add_filter('slp_ajaxsql_where',array($this,'filter_ExtendGetSQLWhere_State'));
                $sqlParams[]  = sanitize_text_field( $this->formdata['state_filter'] );
                $this->filter_active = true;                
            }

            // Export Zip Pattern Matches
            // Use * as a wild card beginning or ending 294* or *464 or *94*.
            //
            if ( ! empty( $this->formdata['zip_filter'] ) ) {
                add_filter('slp_ajaxsql_where',array($this,'filter_ExtendGetSQLWhere_Zip'));
                $sqlParams[]  = $this->modifystring_WildCardToSQLLike($this->formdata['zip_filter']);
                $this->filter_active = true;                
            }
            
            // Country Filter
            //
            if ( ! empty( $this->formdata['country_filter'] ) ) {
                add_filter('slp_ajaxsql_where',array($this,'filter_ExtendGetSQLWhere_Country'));
                $sqlParams[]  = sanitize_text_field( $this->formdata['country_filter'] );
                $this->filter_active = true;
            }

            return array($sqlCommand , $sqlParams);
        }

       /**
         * Add name filters to the SQL where clause.
         *
         * @param string $where current where clause
         * @return string
         */
        public function filter_ExtendGetSQLWhere_Name($where) {
            return $this->slplus->database->extend_Where($where,"sl_store LIKE '%s' ");
        }

        /**
         * Add country filters to the SQL where clause.
         *
         * @param string $where current where clause
         * @return string
         */
        public function filter_ExtendGetSQLWhere_Country($where) {
            return $this->slplus->database->extend_Where($where,'sl_country = %s ',$this->formdata['country_joiner']);
        }

        /**
         * Add state filters to the SQL where clause.
         *
         * @param string $where current where clause
         * @return string
         */
        public function filter_ExtendGetSQLWhere_State($where) {
            return $this->slplus->database->extend_Where($where,'sl_state = %s ',$this->formdata['state_joiner']);
        }

        /**
         * Add zip filters to the SQL where clause.
         *
         * @param string $where current where clause
         * @return string
         */
        public function filter_ExtendGetSQLWhere_Zip($where) {
            return $this->slplus->database->extend_Where($where,"sl_zip LIKE '%s' ",$this->formdata['zip_filter_joiner']);
        }

        /**
         * Change wildcard strings to SQL Like Statements.
         *
         * Replace * with % in the string.
         *
         * @param type $wildcard_string
         */
        public function modifystring_WildCardToSQLLike($wildcard_string) {
            return str_replace('*','%',sanitize_text_field( $wildcard_string ));
        }     
        
        /**
         * Reset the filter variables.
         */
        public function reset() {
            $this->reset = true;
        }
    }
}

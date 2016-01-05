<?php
if (! class_exists('SLPEnhancedSearch_AJAX')) {
    require_once(SLPLUS_PLUGINDIR.'/include/base_class.ajax.php');


    /**
     * Holds the ajax-only code.
     *
     * This allows the main plugin to only include this file in AJAX mode
     * via the slp_init when DOING_AJAX is true.
     *
     * @package StoreLocatorPlus\EnhancedSearch\AJAX
     * @author Lance Cleveland <lance@charlestonsw.com>
     * @copyright 2014 Charleston Software Associates, LLC
     */
    class SLPEnhancedSearch_AJAX extends SLP_BaseClass_AJAX {

	    //-------------------------------------
	    // Properties
	    //-------------------------------------

	    /**
	     * @var \SLPEnhancedSearch
	     */
	    public $addon;

        //-------------------------------------
        // Methods : Base Override
        //-------------------------------------

        /**
         * Things we do to latch onto an AJAX processing environment.
         *
         * Add WordPress and SLP hooks and filters only if in AJAX mode.
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
        public function do_ajax_startup() {
	        if ( ! $this->is_valid_ajax_action() ) { return; }

	        add_filter( 'slp_location_filters_for_AJAX'         , array( $this , 'filter_JSONP_SearchByStore'       )       );
	        add_filter( 'slp_location_having_filters_for_AJAX'  , array( $this , 'filter_AJAX_AddHavingClause'      ) , 55  );
	        add_filter( 'slp_ajaxsql_fullquery'                 , array( $this , 'filter_JSONP_ModifyFullSQL'       )       );
	        add_filter( 'slp_ajaxsql_queryparams'               , array( $this , 'filter_JSONP_ModifyFullSQLParams' )       );
	        add_filter( 'slp_location_filters_for_AJAX'         , array( $this , 'filter_JSONP_SearchByStore'       )       );

	        add_filter( 'slp_ajaxsql_where'                     , array( $this , 'filter_JSONP_SearchFilters'       ) , 20  );

        }

        //-------------------------------------
        // Methods : Custom
        //-------------------------------------

	    /**
	     * Add to the AJAX having clause
	     *
	     * @param mixed[] having clause array
	     * @return mixed[]
	     */
	    function filter_AJAX_AddHavingClause( $clauseArray ) {
		    $this->init_OptionsViaAJAX();
			$ajax_formdata      = $this->slplus->addons['slp.AjaxHandler']->formdata; // AK: this code is duplicated in several places, is there a more efficient way?

			// Ignore Radius Is On
		    //
		    if ( $this->slplus->options['radius_behavior'] == "always_ignore" 			||
				( $this->slplus->options['radius_behavior'] == "ignore_with_blank_addr" &&
					empty( $ajax_formdata['addressInput'] ) // this does not check for AddressInputCity, AddressInputState, or AddressInputCountry fields, only the address field on the form.
				)) {
			    array_push( $clauseArray , ' OR (sl_distance > 0) ' );
		    }

		    return $clauseArray;
	    }


		/**
		 * Add the selected filters to the search results.
		 *
		 * @param $where
		 * @return string
		 */
	    function filter_JSONP_SearchFilters($where) {
		    if ( !isset( $this->slplus->addons['slp.AjaxHandler'] ) ) { return $where; }

		    $ajax_options       = $this->slplus->addons['slp.AjaxHandler']->plugin->options;
		    $ajax_formdata      = $this->slplus->addons['slp.AjaxHandler']->formdata;
		    $discrete_settings  = array('hidden', 'discrete' , 'dropdown_discretefilter' , 'dropdown_discretefilteraddress' );

		    // Discrete City Output
		    //
		    if (
			    ! empty( $ajax_formdata['addressInputCity'] )                   &&
			    in_array( $ajax_options['city_selector'] , $discrete_settings )
		    ){
			    $sql_city_expression =
				    (preg_match('/, /',$this->slplus->addons['slp.AjaxHandler']->formdata['addressInputCity']) === 1) ?
					    'CONCAT_WS(", ",sl_city,sl_state)=%s'   :
					    'sl_city=%s'                            ;

			    $where =
				    $this->slplus->database->extend_Where(
					    $where,
					    $this->slplus->db->prepare(
						    $sql_city_expression,
						    sanitize_text_field($this->slplus->addons['slp.AjaxHandler']->formdata['addressInputCity'])
					    )
				    );
		    }

		    // Discrete State Output
		    //
		    if (
			    ! empty( $ajax_formdata['addressInputState'] )                   &&
			    in_array( $ajax_options['state_selector'] , $discrete_settings )
		    ){
			    $where = $this->slplus->database->extend_WhereFieldMatches( $where , 'sl_state' , $this->slplus->addons['slp.AjaxHandler']->formdata['addressInputState']);
		    }

		    // Discrete Country Output
		    //
		    if (
			    ! empty( $ajax_formdata['addressInputCountry'] )                   &&
			    in_array( $ajax_options['country_selector'] , $discrete_settings )
		    ) {
			    $where = $this->slplus->database->extend_WhereFieldMatches( $where , 'sl_country' , $this->slplus->addons['slp.AjaxHandler']->formdata['addressInputCountry']);
		    }

		    return $where;
	    }

	    /**
	     * Modify the AJAX processor SQL statement.
	     *
	     * Remove the distance clause (having distance) if the ignore radius option is set.
	     *
	     * @param string $sqlStatement full SQL statement.
	     * @return string modified SQL statement
	     */
	    function filter_JSONP_ModifyFullSQL($sqlStatement) {

		    // radius_behavior
		    // if always ignore radius
		    // or, if ignore on blank address, and address is indeed blank
			$ajax_formdata      = $this->slplus->addons['slp.AjaxHandler']->formdata;

			if ( $this->slplus->options['radius_behavior'] == "always_ignore" 			||
				( $this->slplus->options['radius_behavior'] == "ignore_with_blank_addr" &&
					empty( $ajax_formdata['addressInput'] )
					)) {

			    $sqlStatement = str_replace('HAVING (sl_distance < %d)','HAVING (sl_distance >= 0)',$sqlStatement);
		    }

		    return $sqlStatement;
	    }

	    /**
	     * Modify the AJAX processor SQL statement params.
	     *
	     * Remove the distance param if the ignore radius option is set.
	     *
	     * @param mixed[] $paramArray the current list of parameters.
	     * @return mixed[] modified parameters list.
	     */
	    function filter_JSONP_ModifyFullSQLParams($paramArray) {
			$ajax_formdata      = $this->slplus->addons['slp.AjaxHandler']->formdata;

		    if (  $this->slplus->options['radius_behavior'] == "always_ignore" 			||
				( $this->slplus->options['radius_behavior'] == "ignore_with_blank_addr" &&
					empty( $ajax_formdata['addressInput'] )
				)) {
			    $limit  = array_pop($paramArray);
			    $radius = array_pop($paramArray);
			    $paramArray[] = $limit;
		    }
		    return $paramArray;
	    }

	    /**
	     * Add the store name condition to the MySQL statement used to fetch locations with JSONP.
	     *
	     * @param string $currentFilters
	     * @return string the modified where clause
	     */
	    function filter_JSONP_SearchByStore($currentFilters) {
		    if (empty($_POST['name'])) { return $currentFilters; }
		    $posted_name = preg_replace('/^\s+(.*?)/','$1',$_POST['name']);
		    $posted_name = preg_replace('/(.*?)\s+$/','$1',$posted_name);
		    return array_merge(
			    $currentFilters,
			    array(" AND (sl_store LIKE '%%".$posted_name."%%')")
		    );
	    }

	    /**
	     * Set options based on the AJAX formdata properties.
	     *
	     * This will allow AJAX entries to take precedence over local options.
	     * Typically these are passed via slp.js by using hidden fields with the name attribute.
	     * The name must match the options available to this add-on pack for jQuery to pass them along.
	     */
	    function init_OptionsViaAJAX() {
		    if (isset($this->slplus->addons['slp.AjaxHandler']->formdata)) {
			    if (is_array($this->slplus->addons['slp.AjaxHandler']->formdata)) {
				    array_walk($this->slplus->addons['slp.AjaxHandler']->formdata,array($this,'set_ValidOptions'));
			    }
		    }
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
			    $this->addon->options[$simpleKey] = stripslashes_deep($val);
		    }
	    }
   }
}
<?php
if (! class_exists('SLPTagalong_AJAX')) {
    require_once(SLPLUS_PLUGINDIR.'/include/base_class.ajax.php');


    /**
     * Holds the ajax-only code.
     *
     * This allows the main plugin to only include this file in AJAX mode
     * via the slp_init when DOING_AJAX is true.
     *
     * @package StoreLocatorPlus\Tagalong\AJAX
     * @author Lance Cleveland <lance@charlestonsw.com>
     * @copyright 2014 Charleston Software Associates, LLC
     */
    class SLPTagalong_AJAX extends SLP_BaseClass_AJAX {

	    //-------------------------------------
	    // Properties
	    //-------------------------------------

	    /**
	     * @var \SLPTagalong
	     */
	    public $addon;

	    /**
	     * @var \Tagalong_Data
	     */
	    private $data;

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

	        $this->addon->createobject_Data();
	        $this->data = $this->addon->data;

	        // AJAX and other ubiquitous stuff
	        // Save category data for stores taxonomy type
	        //
	        add_filter( 'slp_location_filters_for_AJAX' , array($this,'filter_JSONP_SearchByCategory')       );
	        add_filter( 'slp_ajaxsql_orderby'           , array($this,'filter_AJAX_ModifyOrderBy'    ),99    );
	        add_filter( 'slp_results_marker_data'       , array($this,'filter_SetMapMarkers'         )       );
        }

        //-------------------------------------
        // Methods : Custom
        //-------------------------------------

	    /**
	     * Change the results order.
	     *
	     * Precedence is given to the order by category count option over all other extensions that came before it.
	     * This is enacted by placing the special category count clause as the first parameter of extend_OrderBy,
	     * and by setting the filter to a high priority (run last).
	     *
	     * @param string $orderby
	     * @return string modified order by
	     */
	    function filter_AJAX_ModifyOrderBy($orderby) {
		    if (empty($this->addon->options['ajax_orderby_catcount'])) { return $orderby; }
		    return $this->slplus->database->extend_OrderBy('('.$this->data->get_SQL('select_categorycount_for_location').') DESC ',$orderby);
	    }

	    /**
	     * Add the category condition to the MySQL statement used to fetch locations with JSONP.
	     *
	     * @param string $currentFilters
	     * @return string
	     */
	    function filter_JSONP_SearchByCategory($currentFilters) {
		    if (!isset($_POST['formdata']) || ($_POST['formdata'] == '')){
			    return $currentFilters;
		    }

		    // Set our JSON Post vars
		    //
		    $JSONPost = wp_parse_args($_POST['formdata'],array());

		    // Don't have cat in the vars?  Don't add a new selection filter.
		    //
		    if (!isset($JSONPost['cat']) || ($JSONPost['cat'] <= 0)) {
			    return $currentFilters;
		    }

		    // Setup and clause to select stores by a specific category
		    //
		    $SQL_SelectStoreByCat =
			    ' AND ' .
			    ' sl_id IN ('.
			    sprintf(
				    'SELECT sl_id FROM ' . $this->data->plugintable['name'] . ' WHERE term_id IN ( %s )',
				    $JSONPost['cat']
			    ) .
			    ') '
		    ;

		    return array_merge($currentFilters,array($SQL_SelectStoreByCat));
	    }


	    /**
	     * Set custom map marker for this location.
	     *
	     * Add the icon marker and filters out any results that don't match the selected category.
	     *
	     * SLP Filter: slp_results_marker_data
	     *
	     * @param mixed[] $mapData
	     * @return mixed[]
	     */
	    function filter_SetMapMarkers($mapData) {
		    if (!ctype_digit($mapData['id'])) { return $mapData; }
		    $this->addon->set_LocationCategories();

		    // If we are looking for a specific category,
		    // check to see if it is assigned to this location
		    // Category searched for not in array, Skip this one.
		    //
		    //
		    $filterOut = isset($_POST['formflds']) && isset($_POST['formflds']['cat']) && ($_POST['formflds']['cat'] > 0);
		    if ($filterOut) {
			    $selectedCat = (int)$_POST['formflds']['cat'];
			    if ( ! in_array ( $selectedCat , $this->addon->current_location_categories ) ) { return array(); }
		    }

		    // Return our modified array
		    //
		    return array_merge(
			    $mapData,
			    array(
				    'attributes'    => $this->slplus->currentLocation->attributes   ,
				    'categories'    => $this->addon->current_location_categories    ,
				    'icon'          => $this->set_LocationMarker()                  ,
				    'iconarray'     => $this->addon->createstring_IconArray()       ,
			    )
		    );
	    }

	    /**
	     * Set the location marker based on categories.
	     */
	    private function set_LocationMarker() {

		    $locationMarker = '';

		    // Location Marker from Tagalong
		    //
		    if (
			    ( ! $this->slplus->is_CheckTrue( $this->addon->options['default_icons'] ) )         &&
			    ( count( $this->addon->current_location_categories ) > 0 )
		    ) {

			    $best_rank = 999999;
			    foreach ( $this->addon->current_location_categories as $term_id ) {
				    $category_details = $this->addon->get_TermWithTagalongData( $term_id );
				    if
				    (
						 ( (int)$category_details['rank'] < $best_rank )  &&
				        isset( $category_details['map-marker'] )
				    ) {
					    $best_rank = (int)$category_details['rank'];
					    $locationMarker = $category_details['map-marker'];
				    }
			    }

			// No category marker - use the default marker.
			//
		    } else {
			    $locationMarker = ( isset( $mapData['attributes']['marker'] ) ? $mapData['attributes']['marker'] : '' );
		    }
		    return $locationMarker;
	    }
    }
}
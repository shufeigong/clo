<?php
if (! class_exists('SLP_AJAX')) {
    require_once(SLPLUS_PLUGINDIR.'include/base_class.ajax.php');


    /**
     * Holds the ajax-only code.
     *
     * This allows the main plugin to only include this file in AJAX mode
     * via the slp_init when DOING_AJAX is true.
     *
     * @property-read   string                      $basic_query        The basic query string before the prepare.
     * @property-read   string                      $dbQuery            Database query string.
     * @property        array                       $formdata_defaults  Default formdata values.
     * @property-read   SLP_AJAX_Location_Manager   $location_manager
     * @property        string                      $name               TODO: LEGACY support for 4.2 addons
     * @property        array                       $options            TODO: LEGACY support for 4.2 addons
     * @property        SLPlus                      $plugin             TODO: LEGACY support for 4.2 addons
     * @property        int                         $query_limit        The query limit.
     * @property        string[]                    $valid_actions
     *
     * @package StoreLocatorPlus\Extension\AJAX
     * @author Lance Cleveland <lance@charlestonsw.com>
     * @copyright 2015 Charleston Software Associates, LLC
     */
    class SLP_AJAX extends SLP_BaseClass_AJAX {
	    public 	$valid_actions = array(
		    'csl_ajax_onload',
		    'csl_ajax_search',
		    'csl_ajax_hide_column',
		    'csl_ajax_unhide_column'
	    	);
	    public 	$formdata_defaults = array(
		    'addressInput'      => '',
		    'addressInputState' => '',
		    'nameSearch'        => '',
	    	);
	    public 	$query_params_valid = array();
	    private $basic_query;
	    private $dbQuery;
	    private $location_manager;
	    public 	$options = array( 'installed_version' => SLPLUS_VERSION );
	    public 	$query_limit;
	    public 	$name = 'AjaxHandler';
	    public 	$plugin;

	    /**
	     * Instantiate a new AJAX handler object.
	     *
	     * @param array $options
	     */
	    function __construct( $options = array() ) {
		    add_filter( 'slp_valid_ajax_query_params' , array( $this, 'set_valid_query_params' ) );
		    parent::__construct( $options );
		    $this->plugin = $this->slplus;
	    }

	    /**
	     * Add our AJAX hooks.
	     */
	    public function add_ajax_hooks() {
		    add_action('wp_ajax_csl_ajax_search'            , array( $this,'csl_ajax_search'    ));
		    add_action('wp_ajax_nopriv_csl_ajax_search'     , array( $this ,'csl_ajax_search'   ));

		    add_action('wp_ajax_csl_ajax_onload'            , array( $this,'csl_ajax_onload'    ));
		    add_action('wp_ajax_nopriv_csl_ajax_onload'     , array( $this,'csl_ajax_onload'    ));

		    add_action('wp_ajax_slp_hide_column'            , array( $this,'slp_hide_column'    ));
		    add_action('wp_ajax_slp_unhide_column'          , array( $this,'slp_unhide_column'  ));
	    }

	    /**
	     * Add sort by distance ASC as default order.
	     */
	    function add_distance_sort_to_orderby() {
		    $this->slplus->database->extend_order_array( 'sl_distance ASC' );
	    }

		/**
		 * Add and load search filters for onload and search methods.
		 */
		public function add_load_and_search_filters() {
			add_filter( 'slp_results_marker_data' , array( $this ,'modify_email_link') , 10 , 1);
		}

	    /**
	     * Attach the location_manager object.
	     */
	    private function create_location_manager() {
		    if ( ! isset( $this->location_manager ) ) {
			    require_once('class.ajax.location_manager.php');
			    $this->location_manager = new  SLP_AJAX_Location_Manager( array( 'ajax' => $this ) );
		    }
	    }

        /**
         * Modify the email link
         *
         * @param mixed[] $marker the current marker data
         * @return mixed[]
         */
        public function modify_email_link( $marker ) {
            $marker['email_link'] = '';

            if ( ! empty( $marker['email'] ) ) {
                $marker['email_link'] =
                    sprintf(
                        '<a href="mailto:%s" target="_blank" id="slp_marker_email" class="storelocatorlink"><nobr>%s</nobr></a>',
                        $marker['email'],
                        $this->slplus->WPML->get_text('label_email')
                    );
            }

            return $marker;
        }

        /**
         * Process the location manager requests.
         *
         * NOTE: CALLED FROM base_class_ajax.php via do_ajax_startup() and this->short_action.
         */
        public function process_location_manager() {
            $this->create_location_manager();
        }

		/**
		 * Handle csl_ajax_onload.
		 *
		 * NOTE: CALLED FROM base_class_ajax.php via do_ajax_startup() and this->short_action.
		 */
		public function process_onload() {
			$this->add_load_and_search_filters();
		}

		/**
		 * Handle csl_ajax_search
		 *
		 * NOTE: CALLED FROM base_class_ajax.php via do_ajax_startup() and this->short_action.
		 */
		public function process_search() {
			$this->add_load_and_search_filters();
		}

	    /**
	     * Hide a manage locations column.
	     */
	    function slp_hide_column() {
		    $this->create_location_manager();
		    $this->location_manager->hide_column();
	    }

	    /**
	     * Hide a manage locations column.
	     */
	    function slp_unhide_column() {
		    $this->create_location_manager();
		    $this->location_manager->unhide_column();
	    }


	    /**
	     * Handle AJAX request for OnLoad action.
	     *
	     */
	    function csl_ajax_onload() {
		    $this->slplus->notifications->enabled = false;

		    // Return How Many?
		    //
		    $response=array();
		    $this->query_limit = $this->slplus->options['initial_results_returned'];
		    $locations = $this->execute_LocationQuery();
		    foreach ($locations as $row){
			    $response[] = $this->slp_add_marker($row);
		    }

		    // Output the JSON and Exit
		    //
		    $this->renderJSON_Response(
			    array(
				    'count'         => count($response) ,
				    'type'          => 'load',
				    'query_params'  => $this->query_params,
				    'response'      => $response
			    )
		    );
	    }

	    /**
	     * Handle AJAX request for Search calls.
	     */
	    function csl_ajax_search() {
		    $this->slplus->notifications->enabled = false;

		    // Get Locations
		    //
		    $response = array();
		    $search_results_location_ids = array();
		    $this->query_limit = $this->slplus->options_nojs['max_results_returned'];
		    $locations = $this->execute_LocationQuery();
		    foreach ($locations as $row){
			    $thisLocation = $this->slp_add_marker($row);
			    if (!empty($thisLocation)) {
				    $response[] = $thisLocation;
				    $search_results_location_ids[] = $row['sl_id'];
			    }
		    }

		    // Do report work
		    //
		    do_action('slp_report_query_result', $this->query_params, $search_results_location_ids);

		    // Output the JSON and Exit
		    //
		    $this->renderJSON_Response(
			    array(
				    'count'         => count($response),
				    'option'        => $this->query_params['address'],
				    'type'          => 'search',
				    'query_params'  => $this->query_params,
				    'response'      => $response
			    )
		    );
	    }

	    /**
	     * Run a database query to fetch the locations the user asked for.
	     *
	     * @return object a MySQL result object
	     */
	    function execute_LocationQuery() {

		    // SLP options that tweak the query
		    //
		    $this->slplus->database->createobject_DatabaseExtension();

		    // Distance Unit (KM or MI) Modifier
		    // Since miles is default, if kilometers is selected, divide by 1.609344 in order to convert the kilometer value selection back in miles
		    //
		    $multiplier=($this->slplus->options['distance_unit']==__('km', 'store-locator-le'))? 6371 : 3959;

		    //........
		    // Post options that tweak the query
		    //........

		    // Add all the location filters together for SQL statement.
		    // FILTER: slp_location_filters_for_AJAX
		    //
		    $filterClause = '';
		    foreach (apply_filters('slp_location_filters_for_AJAX',array()) as $filter) {
			    $filterClause .= $filter;
		    }

		    // ORDER BY
		    //
		    add_action( 'slp_orderby_default' , array( $this , 'add_distance_sort_to_orderby') , 100 );

		    // Having clause filter
		    // Do filter after sl_distance has been calculated
		    //
		    // FILTER: slp_location_having_filters_for_AJAX
		    // append new having clause logic to the array and return the new array
		    // to extend/modify the having clause.
		    //
		    $havingClauseElements =
			    apply_filters(
				    'slp_location_having_filters_for_AJAX',
				    array(
					    '(sl_distance < %f) ',
					    'OR (sl_distance IS NULL) '
				    )
			    );

		    // If there are element for the having clause set it
		    // otherwise leave it as a blank string
		    //
		    $having_clause = '';
		    if ( count($havingClauseElements) > 0 ) {
			    foreach ($havingClauseElements as $filter) {
				    $having_clause .= $filter;
			    }
			    $having_clause = trim( $having_clause );
			    $having_clause = preg_replace( '/^OR /', '' , $having_clause );

			    if ( ! empty( $having_clause ) ) {
				    $having_clause = 'HAVING ' . $having_clause;
			    }

		    }

		    // WHERE clauses
		    //
		    add_filter( 'slp_ajaxsql_where' , array( $this , 'filter_out_private_locations' ) );

		    // FILTER: slp_ajaxsql_fullquery
		    //
		    $this->basic_query =
			    apply_filters(
				    'slp_ajaxsql_fullquery',
				    $this->slplus->database->get_SQL(
					    array(
						    'selectall_with_distance',
						    'where_default_validlatlong',
					    )
				    )                                                      .
				    "{$filterClause} "                                      .
				    "{$having_clause} "                                      .
				    $this->slplus->database->get_SQL('orderby_default')     .
				    'LIMIT %d'
			    );

		    // Set the query parameters
		    //
		    $default_query_parameters = array();
		    $default_query_parameters[] = $multiplier;
		    $default_query_parameters[] = $this->query_params['lat'];
		    $default_query_parameters[] = $this->query_params['lng'];
		    $default_query_parameters[] = $this->query_params['lat'];
		    if ( ! empty( $having_clause ) ) {
			    $default_query_parameters[] = $this->query_params['radius'];
		    }
		    $default_query_parameters[] = $this->query_limit;

		    // FILTER: slp_ajaxsql_queryparams
		    $queryParams = apply_filters( 'slp_ajaxsql_queryparams' , $default_query_parameters );

		    // Run the query
		    //
		    // First convert our placeholder basic_query into a string with the vars inserted.
		    // Then turn off errors so they don't munge our JSONP.
		    //
		    global $wpdb;
		    $this->dbQuery =
			    $wpdb->prepare(
				    $this->basic_query,
				    $queryParams
			    );
		    $wpdb->hide_errors();
		    $result = $wpdb->get_results($this->dbQuery, ARRAY_A);

		    // Problems?  Oh crap.  Die.
		    //
		    if ($result === null) {
			    wp_die(json_encode(array(
				    'success'       => false,
				    'response'      => 'Invalid query: ' . $wpdb->last_error,
				    'message'       => $this->slplus->options_nojs['invalid_query_message'],
				    'basic_query'   => $this->basic_query ,
				    'default_params'=> $default_query_parameters,
				    'query_params'  => $queryParams,
				    'dbQuery'       => $this->dbQuery
			    )));
		    }

		    // Return the results
		    // FILTER: slp_ajaxsql_results
		    //
		    return apply_filters('slp_ajaxsql_results',$result);
	    }

	    /**
	     * Do not return private locations by default.
	     *
	     * @param 	string $where 	the current where clause
	     * @return 	string 			the extended where clause
	     */
	    function filter_out_private_locations( $where ) {
		    return $this->slplus->database->extend_Where( $where , ' ( NOT sl_private OR sl_private IS NULL) ' );
	    }
	    /**
	     * Return true if the AJAX action is one we process.
	     */
	    function is_valid_ajax_action() {
		    if ( ! isset( $_REQUEST['action'] ) ) { return false; }

		    foreach ( $this->valid_actions as $valid_ajax_action ) {
			    if ( $_REQUEST['action'] === $valid_ajax_action ) { return true; }
		    }
		    return false;
	    }

	    /**
	     * Output a JSON response based on the incoming data and die.
	     *
	     * Used for AJAX processing in WordPress where a remote listener expects JSON data.
	     *
	     * @param mixed[] $data named array of keys and values to turn into JSON data
	     * @return null dies on execution
	     */
	    function renderJSON_Response($data) {

		    // What do you mean we didn't get an array?
		    //
		    if (!is_array($data)) {
			    $data = array(
				    'success'       => false,
				    'count'         => 0,
				    'message'       => __('renderJSON_Response did not get an array()','store-locator-le')
			    );
		    }

		    // Add our SLP Version and DB Query to the output
		    //
		    $data = array_merge(
			    array(
				    'success'       => true,
				    'slp_version'   => SLPLUS_VERSION,
				    'dbQuery'       => $this->dbQuery
			    ),
			    $data
		    );
		    $data = apply_filters('slp_ajax_response' , $data );

		    // Tell them what is coming...
		    //
		    header( "Content-Type: application/json" );

		    // Go forth and spew data
		    //
		    echo json_encode($data);

		    // Then die.
		    //
		    wp_die();
	    }

	    /**
	     * Set the valid AJAX params based on the incoming action.
	     * @param $valid_params
	     *
	     * @return array
	     */
	    public function set_valid_query_params( $valid_params ) {

		    switch ( $_REQUEST['action'] ) {
			    case 'slp_hide_column' :
			    case 'slp_unhide_column' :
				    $valid_params[] = 'data_field';
				    $valid_params[] = 'user_id';
				    break;

			    case 'csl_ajax_onload':
			    case 'csl_ajax_search':
				    $valid_params[] = 'address';
				    $valid_params[] = 'lat';
				    $valid_params[] = 'lng';
				    $valid_params[] = 'radius';
				    $valid_params[] = 'tags';
				    break;
		    }

		    return $valid_params;
	    }


	    /**
	     * Format the result data into a named array.
	     *
	     * We will later use this to build our JSONP response.
	     *
	     * @param null mixed[] $row
	     * @return mixed[]
	     */
	    function slp_add_marker($row = null) {
		    if ($row == null) { return ''; }

		    $this->slplus->currentLocation->set_PropertiesViaArray($row);

		    $marker = array(
			    'name'            => esc_attr($row['sl_store']),
			    'address'         => esc_attr($row['sl_address']),
			    'address2'        => esc_attr($row['sl_address2']),
			    'city'            => esc_attr($row['sl_city']),
			    'state'           => esc_attr($row['sl_state']),
			    'zip'             => esc_attr($row['sl_zip']),
			    'country'         => esc_attr($row['sl_country']),
			    'lat'             => $row['sl_latitude'],
			    'lng'             => $row['sl_longitude'],
			    'description'     => html_entity_decode($row['sl_description']),
			    'url'             => esc_url( $row['sl_url']       ),
			    'sl_pages_url'    => esc_url( $row['sl_pages_url'] ),
			    'email'           => esc_attr($row['sl_email']),
			    'email_link'      => esc_attr($row['sl_email']),
			    'hours'           => esc_attr($row['sl_hours']),
			    'phone'           => esc_attr($row['sl_phone']),
			    'fax'             => esc_attr($row['sl_fax']),
			    'image'           => esc_attr($row['sl_image']),
			    'distance'        => $row['sl_distance'],
			    'tags'            => esc_attr($row['sl_tags']),
			    'option_value'    => esc_js($row['sl_option_value']),
			    'attributes'      => maybe_unserialize($row['sl_option_value']),
			    'id'              => $row['sl_id'],
			    'linked_postid'   => $row['sl_linked_postid'],
			    'neat_title'      => esc_attr( $row['sl_neat_title'] ),
			    'data'            => $row,
		    );

		    $use_pages_link =
			    ( $this->slplus->is_AddOnActive( 'slp-pages' ) &&
			      $this->slplus->add_ons->instances['slp-pages']->use_page_link()
			    );
		    $use_same_window =
			    ( $this->slplus->is_AddOnActive( 'slp-pages' ) &&
			      $this->slplus->add_ons->instances['slp-pages']->use_same_window()
			    );

		    $web_link = $use_pages_link ? $marker['sl_pages_url'] : $marker['url'] ;
			//$web_link = esc_url( $web_link);

		    if ( ! empty( $web_link ) ) {
			    $marker['web_link'] =
				    sprintf(
					    "<a href='%s' target='%s' class='storelocatorlink'>%s</a><br/>",
					    $web_link,
					    ( $use_same_window ) ? '_self' : '_blank',
					    $this->slplus->WPML->get_text('label_website')
				    );

				$marker['url_link'] =
					sprintf(
						"<a href='%s' target='%s' class='storelocatorlink'>%s</a><br/>",
						$web_link,
						( $use_same_window ) ? '_self' : '_blank',
						$row['sl_url']
					);
		    } else {
			    $marker['web_link'] = '';
				$marker['url_link'] = '';
		    }

		    // FILTER: slp_results_marker_data
		    // Modify the map marker object that is sent back to the UI in the JSONP response.
		    //
		    $marker = apply_filters('slp_results_marker_data',$marker);

		    return $marker;
	    }


    }
}
<?php
if (! class_exists('SLP_BaseClass_AJAX')) {

    /**
     * A base class that helps add-on packs separate AJAX functionality.
     *
     * Add on packs should include and extend this class.
     *
     * This allows the main plugin to only include this file in AJAX mode.
     *
     * @property-read   array                       $query_params
     * @property        string[]                    $query_params_valid     Array of valid AJAX query parameters
     *
     * @package StoreLocatorPlus\BaseClass\AJAX
     * @author Lance Cleveland <lance@charlestonsw.com>
     * @copyright 2014 - 2015 Charleston Software Associates, LLC
     */
    class SLP_BaseClass_AJAX extends SLPlus_BaseClass_Object {
	    public    $query_params         = array();
	    protected $query_params_valid   = array();

        /**
         * This addon pack.
         *
         * @var \SLP_BaseClass_Addon $addon
         */
        protected $addon;

        /**
         * Form data that comes into the AJAX request in the formdata variable.
         *
         * @var mixed[] $formdata
         */
        protected $formdata = array();

        /**
         * @var bool has the formdata been set already?
         */
        private $formdata_set = false;

        /**
         * The formdata default values.
         *
         * @var mixed[] $formdata_defaults
         */
        protected $formdata_defaults = array();

	    /**
	     * @var string $short_action the shortened (csl_ajax prefix dropped) AJAX action.
	     */
	    private $short_action;

	    /**
	     * What AJAX actions are valid for this add on to process?
	     *
	     * Override in the extended class if not serving the default SLP actions:
	     * * csl_ajax_onload
	     * * csl_ajax_search
	     *
	     * @var array
	     */
	    protected $valid_actions = array(
		    'csl_ajax_onload',
		    'csl_ajax_search'
	    );

        //-------------------------------------
        // Methods : activity
        //-------------------------------------

        /**
         * Instantiate the admin panel object.
         *
         * Sets short_action property.
         * Calls do_ajax_startup.
         * - sets Query Params (formdata)
         * - Calls process_{short_action} if method exists.
         *
         * @param mixed[] $options
         */
        function __construct( $options = array() ) {
	        parent::__construct( $options );
	        $this->short_action = str_replace('csl_ajax_','', $_REQUEST['action'] );
            $this->do_ajax_startup();
        }

	    /**
	     * Override this with the WordPress AJAX hooks you want to invoke.
	     *
	     * example:
	     * 	    add_action('wp_ajax_csl_ajax_search' , array( $this,'csl_ajax_search' ));         // For logged in users
	     *      add_action('wp_ajax_nopriv_csl_ajax_search' , array( $this,'csl_ajax_search' ));  // Not logged-in users
	     */
	    function add_ajax_hooks() {

	    }

        /**
         * Things we want our add on packs to do when they start in AJAX mode.
         *
         * Add methods named process_{short_action_name} to the extended class,
         * or override this method.
         */
        function do_ajax_startup() {
	        $this->set_QueryParams();
	        $action_name = 'process_' . $this->short_action;
	        if ( method_exists( $this , $action_name ) ) {
		        $this->$action_name();
	        }
			$this->add_ajax_hooks();
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
         * Set incoming query and request parameters into object properties.
         */
        function set_QueryParams() {
            if ( ! $this->formdata_set ) {
                if ( isset( $_REQUEST['formdata'] ) ) {
                    $this->formdata = wp_parse_args($_REQUEST['formdata'], $this->formdata_defaults);
                }
                $this->formdata_set = true;
            }

	        // Incoming Query Params
	        //
	        $this->query_params_valid = apply_filters( 'slp_valid_ajax_query_params' , $this->query_params_valid );
	        $this->query_params['QUERY_STRING'] = isset( $_SERVER['QUERY_STRING'] ) ? $_SERVER['QUERY_STRING'] : '' ;
	        foreach ( $this->query_params_valid as $key ) {
		        $this->query_params[$key] = isset( $_POST[$key] ) ? $_POST[$key] : '';
	        }

	        // Incoming options - set them in SLPLUS for options or options_nojs.
	        //
	        if ( isset( $_REQUEST['options'] ) && is_array( $_REQUEST['options'] ) ) {
		        if ( isset( $this->addon ) ) {
			        array_walk( $_REQUEST['options'], array( $this->addon, 'set_ValidOptions' ) );
		        }
		        array_walk( $_REQUEST['options'] , array($this->slplus, 'set_ValidOptions'      ) );
		        array_walk( $_REQUEST['options'] , array($this->slplus, 'set_ValidOptionsNoJS'  ) );
	        }
        }

    }
}
<?php
if (! class_exists('SLP_BaseClass_Activation')) {

    /**
     * A base class that helps add-on packs separate activation functionality.
     *
     * Add on packs should include and extend this class.
     *
     * This allows the main plugin to only include this file during activation.
     *
     * @package StoreLocatorPlus\BaseClass\Activation
     * @author Lance Cleveland <lance@charlestonsw.com>
     * @copyright 2014 Charleston Software Associates, LLC
     */
    class SLP_BaseClass_Activation {

        //-------------------------------------
        // Properties
        //-------------------------------------

        /**
         * This addon pack.
         *
         * @var \SLP_BaseClass_Addon $addon
         */
        protected $addon;

	    /**
	     * Set this if you are converting legacy options.
	     *
	     * @see SLPLUS class.activation.upgrade for examples.
	     *
	     * @var array
	     */
	    protected $legacy_options;

        /**
         * The base SLPlus object.
         *
         * @var \SLPlus $slplus
         */
        protected $slplus;

        //-------------------------------------
        // Methods : activity
        //-------------------------------------

        /**
         * Instantiate the admin panel object.
         *
         * @param mixed[] $params
         */
        function __construct($params) {
            // Set properties based on constructor params,
            // if the property named in the params array is well defined.
            //
            if ($params !== null) {
                foreach ($params as $property=>$value) {
                    if (property_exists($this,$property)) { $this->$property = $value; }
                }
            }
        }

	    /**
	     * Convert the legacy settings to the new serialized settings.
	     *
	     */
	    private function convert_legacy_settings() {
			if ( ! isset( $this->legacy_options ) ) { return; }

		    foreach ( $this->legacy_options as $legacy_option => $new_option_meta ) {

			    // Get the legacy option
			    //
			    $option_value = get_option( $legacy_option , null );

			    // No legacy option?  Is there a default?
			    if ( is_null( $option_value ) && isset( $new_option_meta[ 'default' ] ) )  {
				    $option_value = $new_option_meta[ 'default' ];
			    }

			    // If there was a legacy option or a default setting override.
			    // Set that in the new serialized option string.
			    // Otherwise leave it at the default setup in the SLPlus class.
			    //
			    if ( ! is_null( $option_value ) ) {

				    // Callback processing
				    //
				    if ( isset( $new_option_meta[ 'callback' ] ) ) {
					    $option_value = call_user_func_array( $new_option_meta[ 'callback' ] , array( $option_value ) );
				    }

				    $this->addon->options[ $new_option_meta['key'] ] = $option_value;

				    // Delete the legacy option
				    //
				    delete_option( $legacy_option );
			    }
		    }

	    }

        /**
         * Do this whenever the activation class is instantiated.
         *
         * This is triggered via the update_prior_installs method in the admin class,
         * which is run via update_install_info() in the admin class.
         *
         * update_install_info should be something you put in any add-on pack
         * that is using the base add-on class.  It typically goes inside the
         * do_admin_startup() method which is overridden by the new add on
         * adminui class code.
         *
         * Set your $legacy_options.
         *
         */
        function update() {
            $this->convert_legacy_settings();
        }
    }
}
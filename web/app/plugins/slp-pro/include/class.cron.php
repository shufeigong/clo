<?php
if (! class_exists('SLPPro_Cron')) {

    /**
     * The cron job processing class.
     *
     * @package StoreLocatorPlus\SLPPro\Cron
     * @author Lance Cleveland <lance@charlestonsw.com>
     * @copyright 2014 Charleston Software Associates, LLC
     */
    class SLPPro_Cron {

        //-------------------------------------
        // Properties
        //-------------------------------------

        /**
         * The cron action to be performed.
         *
         * @var string
         */
        private $action;

        /**
         * The params received during the cron job call.
         *
         * @var mixed[]
         */
        private $action_params;

        /**
         * The cron status stack written to the wp_option table.
         *
         * @var string[]
         */
        private $cron_status = array();

        //-------------------------------------
        // Methods
        //-------------------------------------

        function __construct( $params ) {

            // Set properties based on constructor params,
            // if the property named in the params array is well defined.
            //
            if ($params !== null) {
                foreach ($params as $property=>$value) {
                    if (property_exists($this,$property)) { $this->$property = $value; }
                }
            }


            $this->add_cron_status( __('process started','csa-slp-pro') );
        }

        /**
         * Process the cron action.
         */
        function process_action() {
            switch ( $this->action ) {
                case 'import_csv':
                    $this->import_csv();
                    break;

                default:
                    break;
            }
        }

        /**
         * Import a location CSV file.
         */
        function import_csv() {

            if ( ! isset( $this->action_params['csvfile']['tmp_name'] ) ) {
                $this->add_cron_status( __('csv import failed, local file name not set', 'csa-slp-pro') );
                return;
            }

            if (   empty( $this->action_params['csvfile']['tmp_name'] ) ) {
                $this->add_cron_status( __('csv import failed, local file name blank'  , 'csa-slp-pro') );
                return;
            }

            $this->add_cron_status( __('starting csv import', 'csa-slp-pro') );

            $this->add_cron_status( 'action params ' . print_r($this->action_params,true) );

        }

        /**
         * Update the cron status message stack and make persistent in wp_options.
         *
         * @param $message
         */
        function add_cron_status( $message ) {
            $this->cron_status[] = date('Y-M-d G:i:s') . $message;
            update_option('slp-pro-cron' , $this->cron_status );
        }
  }
}
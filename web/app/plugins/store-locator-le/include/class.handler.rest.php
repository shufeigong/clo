<?php
if ( ! class_exists('SLP_REST_Handler') ) {

    /**
     * WP REST API interface.
     *
     * @package   StoreLocatorPlus\REST\Handlerinitialize
     * @author    Lance Cleveland <lance@charlestonsw.com>
     * @copyright 2015 Charleston Software Associates, LLC
     */
    class SLP_REST_Handler extends SLPlus_BaseClass_Object {
        function initialize() {
            if ( ! defined( 'REST_API_VERSION' )                    ) { return; }      // No WP REST API.  Leave.
            if ( version_compare( REST_API_VERSION , '2.0' , '<' )  ) { return; }      // Require REST API version 2.
            $this->set_rest_hooks();
        }

        private function set_rest_hooks() {
            add_action( 'rest_api_init' , array( $this , 'setup_rest' ) );
        }

        function setup_rest() {
            if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
                $this->setup_rest_endpoints();
            }
        }

        private function setup_rest_endpoints() {
            register_rest_route( 'store-locator-plus/v1' , '/locations/fetch/(?P<limit>\d+)' , array( 'methods' => 'GET' , 'callback' => array( $this, 'locations_fetch' ) ) );
        }

        function locations_fetch( $data ) {
            return 'fetch ' . $data['limit'] . ' locations ';
        }

    }

}
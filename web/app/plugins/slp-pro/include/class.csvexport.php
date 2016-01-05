<?php
if (!defined( 'ABSPATH'     )) { exit;   } // Exit if accessed directly, dang hackers

// Make sure the class is only defined once.
//
if (!class_exists('CSVExport')) {

    /**
     * CSV Export for Pro Pack
     *
     * @package StoreLocatorPlus\ProPack\CSVExport
     * @author Lance Cleveland <lance@charlestonsw.com>
     * @copyright 2013-2014 Charleston Software Associates, LLC
     */
    class CSVExport {

        //-------------------------
        // Properties
        //-------------------------
        
        /**
         * The plugin object
         *
         * @var \SLPPro $plugin
         */
        protected $addon;

        /**
         * The base plugin object.
         *
         * @var \SLPlus $slplus
         */
        protected $slplus;               

        /**
         * What type of export are we running?
         * 
         * @var string $type
         */
        private $type;

        //-------------------------
        // Methods
        //-------------------------

        /**
         * Invoke the object.
         */
        function __construct($params) {
            foreach ($params as $name => $value) {
                $this->$name = $value;
            }
        }

        /**
         * Create the export button JS
         *
         * @params string $action match the AJAX action hook name
         * @params string $filename the name of the file for the user to save
         * @return string
         */
        function createstring_ExportButtonJS($action,$filename='csvfile') {
            return
               "jQuery('#secretIFrame').attr('src', '".admin_url('admin-ajax.php') ."?' + jQuery.param(" .
                        "{".
                           "action: '{$action}',"           .
                           "filename: '{$filename}',"       .
                           "formdata: jQuery('#locationForm').serialize()," .
                        "}".
                    ")".
                ");"
            ;
        }

        /**
         * AJAX handler to send the data to a download file for the user.
         */
        function do_SendFile() {
            $this->send_Header();
            $fileaction = "do_Send".$this->type;
            $this->$fileaction();
            die();
        }

        /**
         * Send the CSV Header
         */
        function send_Header() {
            header( 'Content-Description: File Transfer' );
            header( 'Content-Disposition: attachment; filename=slplus_' . $_REQUEST['filename'] . '.csv' );
            header( 'Content-Type: application/csv;');
            header( 'Pragma: no-cache');
            header( 'Expires: 0');
        }
    }
}
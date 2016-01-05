<?php
if (! class_exists('SLPPro_AJAX')) {
    require_once(SLPLUS_PLUGINDIR.'/include/base_class.ajax.php');


    /**
     * Holds the ajax-only code.
     *
     * This allows the main plugin to only include this file in AJAX mode
     * via the slp_init when DOING_AJAX is true.
     *
     * @package StoreLocatorPlus\SLPPro\AJAX
     * @author Lance Cleveland <lance@charlestonsw.com>
     * @copyright 2014 Charleston Software Associates, LLC
     */
    class SLPPro_AJAX extends SLP_BaseClass_AJAX {

        //-------------------------------------
        // Methods : Base Override
        //-------------------------------------

        /**
         * Things we do to latch onto an AJAX processing environment.
         */
        public function do_ajax_startup() {

            // Do not add the filters if we are not processing a csl_ajax_onload or csl_ajax_search request.
            //
            if (! isset( $_REQUEST['action'] )                                          ) { return; }
            if (
                ( $_REQUEST['action'] !== 'csl_ajax_onload' ) &&
                ( $_REQUEST['action'] !== 'csl_ajax_search' )
            ) {
                return;
            }

            add_filter( 'slp_location_filters_for_AJAX' , array( $this, 'createstring_TagSelectionWhereClause'  ) );
            add_filter( 'slp_results_marker_data'       , array( $this, 'modify_AJAXResponseMarker'             ) );


            // Reporting Hooks
            //
            if( $this->slplus->is_CheckTrue( $this->addon->options['reporting_enabled'] ) ) {
                add_action( 'slp_report_query_result' , array( $this, 'insert_Report_QueryandResults' ), 10, 2);
            }
        }

        //-------------------------------------
        // Methods : Custom
        //-------------------------------------

        /**
         * Add the tags condition to the MySQL statement used to fetch locations with JSONP.
         *
         * @param string[] $currentFilters
         * @return string[]
         */
        public function createstring_TagSelectionWhereClause( $currentFilters ) {
            if (!isset($_POST['tags']) || ($_POST['tags'] == '')) {
                return $currentFilters;
            }

            $posted_tag = preg_replace('/^\s+(.*?)/', '$1', $_POST['tags']);
            $posted_tag = preg_replace('/(.*?)\s+$/', '$1', $posted_tag);
            return array_merge(
                $currentFilters, array(" AND ( sl_tags LIKE '%%" . $posted_tag . "%%') ")
            );
        }

        /**
         * Insert the query into the query DB
         * Insert the results into the reporting table
         *
         * @param Associate Array Contain query sql, tags, address and radius
         * @param string[] Query result row id array
         */
        function insert_Report_QueryandResults( $queryParams, $results ) {
            $qry =
				$this->slplus->db->prepare(
	                "INSERT INTO {$this->slplus->db->prefix}slp_rep_query " .
	                '(slp_repq_query,slp_repq_tags,slp_repq_address,slp_repq_radius) '.
	                'values (%s,%s,%s,%s)',
	                $queryParams['QUERY_STRING']  ,
	                $queryParams['tags']          ,
	                $queryParams['address']       ,
	                $queryParams['radius']
	            );
            $this->slplus->db->query($qry);

            // Insert the results into the reporting table
            //
            foreach ($results as $row_id) {
                $qry =
	                $this->slplus->db->prepare(
                        "INSERT INTO {$this->slplus->db->prefix}slp_rep_query_results (slp_repq_id,sl_id) values (%d,%d)",
                        $this->slplus->db->insert_id,
                        $row_id
                    );
                $this->slplus->db->query($qry);
            }
        }

        /**
         * Modify the marker data.
         *
         * @param mixed[] $marker the current marker data
         * @return mixed[]
         */
        function modify_AJAXResponseMarker( $marker ) {

            // Tag output processing
            //
            switch ($this->addon->options['tag_output_processing']) {
                case 'hide':
                    $marker['tags'] = '';
                    break;

                case 'replace_with_br':
                    $marker['tags'] = str_replace(',', '<br/>', $marker['tags']);
                    $marker['tags'] = str_replace('&#044;', '<br/>', $marker['tags']);
                    break;

                case 'as_entered':
                default:
                    break;
            }

            return $marker;
        }

   }
}
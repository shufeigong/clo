<?php
if (! class_exists('SLPEnhancedResults_AJAX')) {
    require_once(SLPLUS_PLUGINDIR.'/include/base_class.ajax.php');


    /**
     * Holds the ajax-only code.
     *
     * This allows the main plugin to only include this file in AJAX mode
     * via the slp_init when DOING_AJAX is true.
     *
     * @property        SLPEnhancedResults  $addon
     *
     * @package StoreLocatorPlus\EnhancedResults\AJAX
     * @author Lance Cleveland <lance@charlestonsw.com>
     * @copyright 2015 Charleston Software Associates, LLC
     */
    class SLPEnhancedResults_AJAX extends SLP_BaseClass_AJAX {

        /**
         * Check that ER extended data fields exist, if they do not then remove them from the order by clause.
		 *
		 * Also check if any extended data records exist.
         *
         * @param 	string $order_clause
		 * @return 	string
         */
		private function check_custom_fields( $order_clause ) {
			$fields_to_check = array( 'featured' , 'rank' );
			foreach ( $fields_to_check as $field ) {
				if ( strpos( $order_clause , $field ) !== false ) {
					if ( ! $this->slplus->database->extension->has_field( $field ) ||
						! $this->slplus->database->has_extended_data()
					) {
                        $order_clause = preg_replace( "/{$field}\s+\w+\W+/" , '' , $order_clause );
                    }
				}
			}
			return $order_clause;
		}

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
			$this->valid_actions[] = 'email_form';
	        if ( ! $this->is_valid_ajax_action() ) { return; }

			add_action( 'wp_ajax_email_form'					, array( $this , 'process_popup_email_form'       	)       	);
			add_action( 'wp_ajax_nopriv_email_form'     		, array( $this , 'process_popup_email_form'			)			);

			add_filter('slp_ajaxsql_results'                    , array( $this , 'filter_AJAX_ModifyResults'        ), 50       );
			add_filter('slp_location_having_filters_for_AJAX'   , array( $this , 'filter_AJAX_AddHavingClause'      ), 50       );

			add_filter(	'slp_results_marker_data'               , array( $this , 'modify_marker'                    ) , 15 , 1  );

			// ORDER BY
			//
			add_action( 'slp_orderby_default' , array( $this , 'add_custom_sort_to_orderby'		) , 15 		);
        }


		/**
		 * Replace the featured location sort order with the more advanced case statement.
		 *
		 * By default any pre-existing / non-edited locations will set the featured field to NULL
		 * Featured locations are set to 1
		 * Non-featured locations are set to 0
		 *
		 * MySQL sorts features in DESC order to be: 1, 0, NULL
		 *
		 * That messes up order by featured DESC when you have order by featured desc, rank asc, sl_distance
		 * as any location that was marked featured then unmarked will come BEFORE all unedited (non-featured)
		 * locations that may be closer.   This is not what the user expects.
		 *
		 * As such we must weight NULL to be the same as not featured.
		 *
		 * @param $our_order
		 *
		 * @return mixed
		 */
		private function create_string_CustomSQLOrder( $our_order ) {

			// Fix featured when it is null or 0
			//
			$find_this = 'featured DESC';
			$replace_with = 'COALESCE(featured,0) DESC';
			$our_order = str_replace( $find_this , $replace_with , $our_order );

			// Fix rank when it is null or 0
			// 0 should NOT be the first ranked
			//
			$find_this = 'rank ASC';
			$replace_with = 'CASE WHEN rank IS NULL THEN 99999 WHEN rank = 0 THEN 99999 WHEN rank > 0 THEN rank END DESC';
			$our_order = str_replace( $find_this , $replace_with , $our_order );


			return $our_order;
		}

		/**
		 * Add having clause to sql which do query work by ajaxhandler
		 *
		 * @param mixed[] having cluuse array
		 * @return mixed[]
		 */
		function filter_AJAX_AddHavingClause($clauseArray) {
			if (
				$this->slplus->database->has_extended_data() &&
				( $this->addon->options['featured_location_display_type'] === 'show_always' )
			){
				array_push( $clauseArray, ' OR (featured = 1) ');
			}
			return $clauseArray;
		}

		/**
		 * Add custom ER sort to Order By clause.
		 *
		 * TODO: finish moving the filter_AJAX_ModifyOrderBy / create_string_CustomSQLOrder to this method.
		 */
		function add_custom_sort_to_orderby() {
			$our_order =
				( ! empty( $this->slplus->options['orderby'] ) ) ?
					$this->slplus->options['orderby'] :
					$this->addon->options['orderby'];

			$our_order = $this->check_custom_fields( $our_order );

			switch ( $our_order ) {
				case 'random':
					$this->slplus->database->extend_order_array( 'sl_distance ASC' );
					break;

				default:
					$this->slplus->database->extend_order_array(
						$this->create_string_CustomSQLOrder(
							$our_order
						)
					);
					break;
			}
		}

		/**
		 * Randomize the results order if random order is selected for search results output.
		 *
		 * @param mixed[] $results the named array location results from an AJAX search
		 * @return mixed[]
		 */
		function filter_AJAX_ModifyResults($results) {
			if ( $this->addon->options['orderby'] === 'random' ) {
				shuffle($results);
			}
			return $results;
		}

		/**
		 * Get the email address from the location's email field, fetch by id.
		 *
		 * @param $id
		 * @return string
		 */
		function get_email_by_slid( $id ) {
			if ( ! $this->slplus->currentLocation->isvalid_ID( $id ) ) { return ''; }
			$this->slplus->currentLocation->set_PropertiesViaDB( $id );
			return $this->slplus->currentLocation->email;
		}

		/**
		 * Modify the marker array after it is loaded with SLP location data.
		 *
		 * @param named array $marker - the SLP data for a single marker
		 */
		function modify_marker($marker) {
			if (($this->addon->options['add_tel_to_phone'] == 1)) {
				$marker['phone'] = sprintf('<a href="tel:%s">%s</a>',$marker['phone'],$marker['phone']);
			}

			if (($this->addon->options['show_country'] == 0)) {
				$marker['country'] = '';
			}

			// Email Link
			//
			if ( ! empty( $marker['email'] ) ) {
				switch ( $this->addon->options['email_link_format'] ) {

					// Default for SLP: the email label linked to the mailto: address
					case 'label_link':
						$marker['email_link'] =
							sprintf(
								'<a href="mailto:%s" target="_blank" id="slp_marker_email" class="storelocatorlink"><nobr>%s</nobr></a>',
								$marker['email'],
								$this->slplus->WPML->get_text('label_email')
							);
						break;

					// The email itself linked to the mailto: address (pre SLP 4.2.26 default)
					case 'email_link':
						$marker['email_link'] =
							sprintf(
								'<a href="mailto:%s" target="_blank" id="slp_marker_email" class="storelocatorlink"><nobr>%s</nobr></a>',
								$marker['email'],
								$marker['email']
							);
						break;

					// An email popup form.
					case 'popup_form':
						$marker['email_link'] =
							sprintf(
								'<a href="#" target="_blank" id="slp_marker_email" class="storelocatorlink" ' .
									"alt='Email {$marker['email']}' title='Email {$marker['email']}' " .
									'onClick="return SLPER.email_form.show_form('.$marker['id'].');">' .
									'<nobr>%s</nobr>' .
								'</a>',
								$this->slplus->options['label_email']
							);
						break;
				}
			}

			// Add Extended Data Fields
			//
			if ( $this->slplus->database->is_Extended() && $this->slplus->database->has_extended_data() ) {
				$exData = $this->slplus->currentLocation->exdata;
                $marker['exdata'] = $exData;
                foreach ($exData as $slug => $value) {

                    // Special featured setting (v. just returning "on")
                    if (($slug === 'featured') && $this->slplus->is_CheckTrue($value)) {
                        $value = 'featured';
                    }

                    $marker[$slug] = $value;
                }
				if ( ! isset($marker['featured']) ) { $marker['featured'] = ''; }
				if ( ! isset($marker['rank'    ]) ) { $marker['rank'    ] = ''; }
			}

			return $marker;
		}

		/**
		 * Process a popup email form.
		 */
		function process_popup_email_form() {
			$this->set_QueryParams();

			if ( ! wp_verify_nonce( $this->formdata['email_nonce'] , 'email_form' ) ) {
				die( 'Cheatin huh?' );
			}

			// Find the to email from the store id
			//
			$to_email = $this->get_email_by_slid( $this->formdata[ 'sl_id' ] );

			if ( empty( $to_email ) ) {
				die( 'Cheatin huh?' );
			}

			$message_headers =
				"From: \"{$this->formdata[ 'email_from' ]}\" <{$this->formdata[ 'email_from' ]}>\n" .
				"Content-Type: text/plain; charset=\"" . get_option('blog_charset') . "\"\n";

			wp_mail(
				$to_email ,
				$this->formdata[ 'email_subject' ] ,
				$this->formdata[ 'email_message' ] ,
				$message_headers
				);

			die( __('email sent to ' . $to_email , 'csa-slp-er') );
		}
	}
}
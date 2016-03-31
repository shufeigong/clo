<?php
if (! class_exists('SLPEnhancedResults_UI')) {
	require_once(SLPLUS_PLUGINDIR.'/include/base_class.userinterface.php');

    /**
     * Holds the UI-only code.
     *
     * This allows the main plugin to only include this file in the front end
     * via the wp_enqueue_scripts call.   Reduces the back-end footprint.
     *
     * @package StoreLocatorPlus\EnhancedResults\UI
     * @author Lance Cleveland <lance@charlestonsw.com>
     * @copyright 2015 Charleston Software Associates, LLC
     */
    class SLPEnhancedResults_UI extends SLP_BaseClass_UI {

		//-------------------------------------
		// Properties
		//-------------------------------------

		/**
		 * This addon pack.
		 *
		 * @var \SLPEnhancedResults $addon
		 */
		public $addon;

		//-------------------------------------
		// Methods : activity
		//-------------------------------------

		/**
		 * Add WordPress and SLP hooks and filters.
		 */
		public function add_hooks_and_filters() {
			parent::add_hooks_and_filters();

			if ( $this->use_custom_js() ) {
				$this->js_requirements = array( 'jquery-ui-core', 'jquery-ui-dialog' );
			}

			add_filter('slp_shortcode_atts'                     , array( $this , 'filter_SetAllowedShortcodeAtts'   ), 90 , 3   );
			add_filter('slp_javascript_results_string'          , array( $this , 'mangle_results_output'            ), 90       );

			add_filter('slp_regwpml_map_settings_inputs'		, array( $this , 'set_wmpl_enabled_options' 		)			);

			// Add Email Form
			//
			if ( $this->addon->options['email_link_format'] === 'popup_form' ) {
				add_filter('slp_layout', array($this, 'add_email_form'));
			}
		}

		/**
		 * Add popup email form div to the map output.
		 *
		 * @param $slp_html
		 * @return string
		 */
		function add_email_form( $slp_html ) {
			$form_html =
				'<div id="email_form">' 	.
					'<form id="the_email_form"><fieldset>' 		.
						wp_nonce_field( 'email_form' , 'email_nonce' ) 			.
						'<input type="hidden" name="sl_id" value="" /> ' 		.
						"<input type='text' name='email_from' 	 " .
							"placeholder='{$this->addon->options['popup_email_from_placeholder']}'    />" 	.
						"<input type='text' name='email_subject' " .
							"placeholder='{$this->addon->options['popup_email_subject_placeholder']}' />" 	.
						"<textarea          name='email_message' " .
							"placeholder='{$this->addon->options['popup_email_message_placeholder']}' ></textarea> " .
 					'</fieldset></form>' 	.
				'</div>'
				;

			return $slp_html . $form_html;
		}

		/**
		 * Only enqueue userinterface.js if the popup form is wanted.
		 */
		function enqueue_ui_javascript() {
			if ( $this->addon->options['email_link_format'] === 'popup_form' ) {

				// Set JavaScript variables for stuff we want to access.
				//
				$this->js_settings['email_form_title'] = $this->addon->options['popup_email_title'];

				// jQuery Smoothness Theme
				//
				if ( file_exists( SLPLUS_PLUGINDIR . '/css/admin/jquery-ui-smoothness.css' ) ) {
					wp_enqueue_style(
						'jquery-ui-smoothness', $this->slplus->plugin_url . '/css/admin/jquery-ui-smoothness.css'
					);
				}
			}

            // Enqueue the custom JS if needed.
            //
            if ( $this->use_custom_js() ) {
                parent::enqueue_ui_javascript();
            }

		}

		/**
		 * Extends the main SLP shortcode approved attributes list, setting defaults.
		 *
		 * This will extend the approved shortcode attributes to include the items listed.
		 * The array key is the attribute name, the value is the default if the attribute is not set.
		 *
		 * NOTE: THIS SHOULD SET ACTUAL VALUES BASED ON ATTRIBUTES
		 * This is the last change to do this as localizeScript gets no data about shortcode
		 * attribute settings.
		 *
		 * The attribute values are set in the SLP UI class render_shortcode method via WordPress core
		 * functions.
		 *
		 * @param mixed[] $attArray current list of approved attributes in slug => default value pairs.
		 * @param mixed[] $attributes the shortcode attributes as entered by the user
		 * @param string $content
		 * @return mixed[]
		 */
		function filter_SetAllowedShortcodeAtts($attArray,$attributes,$content) {
			$allowed_atts = array();

			// Shortcode settable on/off switches
			//
			$attribute_names = array(
				'immediately_show_locations',
				'hide_results',
			);
			foreach ( $attribute_names as $attname ) {
				if ( isset( $attributes[$attname] ) ) {
					$this->addon->options[$attname] = $this->slplus->is_CheckTrue( $attributes[$attname] , 'string' );
				}
				$allowed_atts[ $attname ] = $this->addon->options[ $attname ];
			}

			// Shortcode attributes with Values
			//
			$allowed_atts['order_by'] = $this->addon->options['orderby'];

			// Return the allowed attributes merged with our updated array.
			// note: array_merge later values take precedence if there is a key match.
			//
			return array_merge($attArray, $allowed_atts);
		}

		/**
		 * Change how the information below the map is rendered.
		 *
		 * SLP Filter: slp_javascript_results_string (old school format, still used by JavaScript)
		 *
		 *              {0} aMarker.name,
		 *              {1} parseFloat(aMarker.distance).toFixed(1),
		 *              {2} slplus.distance_unit,
		 *              {3} street,
		 *              {4} street2,
		 *              {5} city_state_zip,
		 *              {6} thePhone,
		 *              {7} theFax,
		 *              {8} link,
		 *              {9} elink,
		 *              {10} slplus.map_domain,
		 *              {11} encodeURIComponent(this.address),
		 *              {12} encodeURIComponent(address),
		 *              {13} slplus.label_directions,
		 *              {14} tagInfo,
		 *              {15} aMarker.id
		 *              {16} aMarker.country
		 *              {17} aMarker.hours
		 */
		function mangle_results_output($resultString) {

			// Hide Results Turned On
			// shortcode hide_results only works if force load JS is not turned on
			//
			if ( $this->slplus->is_CheckTrue( $this->addon->options['hide_results'] ) ) { return ''; }

			// Get our saved result string
			// escape that text area value
			// strip the slashes
			// then run through the shortcode processor.
			//
			$resultString = $this->addon->options['resultslayout'];

			// Hide Distance
			//
			if ( $this->slplus->is_CheckTrue( $this->addon->options['hide_distance'] ) ) {
				$pattern = '/<span class="location_distance">\[slp_location distance_1\] \[slp_location distance_unit\]<\/span>/';
				$resultString = preg_replace($pattern,'',$resultString,1);
			}

			// Show Country
			//
			if (($this->addon->options['show_country'] == 0)) {
				$pattern = '/<span class="slp_result_address slp_result_country">\[slp_location country\]<\/span>/';
				$newPattern = '';
				$resultString = preg_replace($pattern,$newPattern,$resultString,1);
			}

			// Show Hours
			//
			if (($this->addon->options['show_hours'] == 0)) {
				$pattern = '/<span class="slp_result_contact slp_result_hours">\[slp_location hours\]<\/span>/';
				$newPattern = '';
				$resultString = preg_replace($pattern,$newPattern,$resultString,1);
			}

			// Send them back the string
			//
			return $resultString;
		}

		/**
		 * Set options that are registered with WPML.
		 *
		 * @param $wmpl_options
		 * @return array
		 */
		function set_wmpl_enabled_options( $wmpl_options ) {
			return
				array_merge(
					$wmpl_options ,
					array(
						'popup_email_title' ,
						'popup_email_from_placeholder' ,
						'popup_email_subject_placeholder' ,
						'popup_email_message_placeholder' ,
					)
				);
		}

        /**
         * Returns true if one of our settings requires custom JS.
         *
         * @return bool
         */
        function use_custom_js() {
            if ( $this->addon->options['email_link_format'] === 'popup_form' ) { return true; }
            if ( $this->slplus->is_CheckTrue( $this->addon->options['disable_initial_directory'] ) ) { return true; }
            return false;
        }

	}
}
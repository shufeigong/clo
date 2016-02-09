<?php

require_once( SLPLUS_PLUGINDIR . 'include/base_class.object.php');

/**
 * Class SLP_Text_Manager
 *
 * Methods to help manage the SLP text strings in an i18n compatible fashion.
 *
 * @package StoreLocatorPlus\Text\Manager
 * @author Lance Cleveland <lance@charlestonsw.com>
 * @copyright 2015 Charleston Software Associates, LLC
 *
 * @since 4.4.06
 *
 *
 * @property-read       array       $text_strings       Named array of i18n strings.  Key is the slug.
 *                      key: 'option-default:<slug>'    denotes text to be used a option defaults that can be over-riden by users, fetched via get_option_default() method.
 */
class SLP_Text_Manager extends SLPlus_BaseClass_Object {
    private     $text_strings     ;

    /**
     * Allow add-on packs to extend the text strings array via the slp_text_strings filter.
     */
    private function extend_text_strings() {
        /**
         * FILTER: slp_text_strings
         *
         * @params  array   $text_strings   empty by default
         * @return  array                   new text_strings named array in the <string-slug> => __( 'text' , 'textdomain' ) format.
         */
        $new_strings = apply_filters( 'slp_text_strings' , array() );
        if ( ! empty( $new_strings) ) {
            foreach ($new_strings as $slug => $string) {
                $this->text_strings[$slug] = $string;
            }
        }
    }

    /**
     * Build web links we like to use.
     */
    private function create_web_links() {
        
        // Globally available
        //
        $this->slplus->create_web_link( 'CSA'       , '' , $this->get_text_string( 'CSA'        )  , $this->slplus->slp_store_url                           );
        $this->slplus->create_web_link( 'contactus' , '' , $this->get_text_string( 'contactus'  )  , $this->slplus->slp_store_url . 'mindset/contact-us/'   );

        $this->slplus->add_ons->get_product_url( 'slp-premier'    ); // Also sets 'slp-premier' web_link
        $this->slplus->add_ons->get_product_url( 'slp-experience' ); // Also sets 'slp-experience' web_link
        $this->slplus->add_ons->get_product_url( 'slp-power'      ); // Also sets 'slp-power' web_link
        
        // Specific SLP Pages
        //
        switch ( $this->slplus->current_admin_page ) {
            
            case 'slp_experience':
                $this->slplus->create_web_link( 
                        'documentation_plugin_styles' ,  
                        $this->get_link_content( 'view_the' ),
                        $this->get_text_string( 'documentation' ), 
                        $this->slplus->support_url . 'user-experience/view/styles-themes-custom-css/'
                    );
                break;
        }
    }

    /**
     * Initialize this object.
     *
     * Setup the starting text strings.
     */
    public function initialize() {
        $this->slplus->createobject_AddOnManager();

        // Simple Strings
        //
        $this->text_strings['CSA'                                   ] = __( 'Charleston Software Associates'                                                                        , 'store-locator-le' );
        $this->text_strings['contactus'                             ] = __( 'Contact Us'                                                                                            , 'store-locator-le' );
        $this->text_strings['documentation'                         ] = __( 'documentation'                                                                                         , 'store-locator-le' );

        // Default option strings that can be overridden by the user.
        //
        $this->text_strings['option-default:instructions'           ] = __( 'Enter an address or zip code and click the find locations button.'                                     , 'store-locator-le' );
        $this->text_strings['option-default:invalid_query_message'  ] = __( 'Store Locator Plus did not send back a valid JSONP response.'                                          , 'store-locator-le' );
        $this->text_strings['option-default:label_directions'       ] = __( 'Directions'                                                                                            , 'store-locator-le' );
        $this->text_strings['option-default:label_fax'              ] = __( 'Fax'                                                                                                   , 'store-locator-le' );
        $this->text_strings['option-default:label_for_find_button'  ] = __( 'Find Locations'                                                                                        , 'store-locator-le' );
        $this->text_strings['option-default:label_email'            ] = __( 'Email'                                                                                                 , 'store-locator-le' );
        $this->text_strings['option-default:label_hours'            ] = __( 'Hours'                                                                                                 , 'store-locator-le' );
        $this->text_strings['option-default:label_image'            ] = __( 'Image'                                                                                                 , 'store-locator-le' );
        $this->text_strings['option-default:label_phone'            ] = __( 'Phone'                                                                                                 , 'store-locator-le' );
        $this->text_strings['option-default:label_website'          ] = __( 'Website'                                                                                               , 'store-locator-le' );
        $this->text_strings['option-default:message_no_results'     ] = __( 'No locations found.'                                                                                   , 'store-locator-le' );


        // Admin Text
        //
        $this->text_strings['admin:location_form_incorrect'         ] = __( 'Location not added.'                                                                                   , 'store-locator-le' );
        $this->text_strings['admin:location_not_added'              ] = __( 'The add location form on your server is not rendering properly.'                                       , 'store-locator-le' );
        $this->text_strings['admin:plugin_style_active_addons'      ] = __( 'These add-ons are helping you get the most of this plugin style: '                                     , 'store-locator-le' );
        $this->text_strings['admin:plugin_style_inactive_addons'    ] = __( 'This plugin style works best if the following add-ons are active: '                                    , 'store-locator-le' );
        $this->text_strings['admin:premium_member_support'          ] = __( 'Premier Members get priority support and access to real-time product update information. '             , 'store-locator-le' );
        $this->text_strings['admin:premium_members'                 ] = __( 'Subscription Accounts'                                                                                 , 'store-locator-le' );
        $this->text_strings['admin:premium_subscription_id_help'    ] = __( 'Your StoreLocatorPlus.com subscription ID for Premier, Experience, and Power add-ons.'                 , 'store-locator-le' );
        $this->text_strings['admin:premium_subscription_id_label'   ] = __( 'User ID'                                                                                               , 'store-locator-le' );
        $this->text_strings['admin:premium_user_id_help'            ] = __( 'Your StoreLocatorPlus.com user ID for Premier, Experience, and Power add-ons.'                         , 'store-locator-le' );
        $this->text_strings['admin:premium_user_id_label'           ] = __( 'Subscription ID'                                                                                       , 'store-locator-le' );

        // Admin Variable Text
        //
        $this->text_strings['admin_variable:successfully_completed' ] = __( '%s successfully.'                                                                                      , 'store-locator-le' );

        // Link Content
        //
        $this->text_strings['linkcontent:improve_or_customize'           ] = __( 'Please %s with any product feedback or for customization inquiries.'                              , 'store-locator-le' );
        $this->text_strings['linkcontent:plugin_created_by'              ] = __( 'This plugin has been brought to you by %s.'                                                       , 'store-locator-le' );
        $this->text_strings['linkcontent:premium_member_updates'         ] = __( 'The %s, %s, and %s add-ons require an active subscription to receive the latest updates. '        , 'store-locator-le' );
        $this->text_strings['linkcontent:view_the'                       ] = __( 'View the %s for more info.'                                                                       , 'store-locator-le' );


        // Linked Text
        //
        $this->create_web_links();
        $this->text_strings['linked:improve_or_customize'            ] = sprintf( $this->get_link_content('improve_or_customize'  ) , $this->slplus->get_web_link( 'contactus'            ));
        $this->text_strings['linked:plugin_created_by'               ] = sprintf( $this->get_link_content('plugin_created_by'     ) , $this->slplus->get_web_link( 'CSA'                  ));
        $this->text_strings['linked:premium_member_updates'          ] = sprintf( $this->get_link_content('premium_member_updates') , $this->slplus->get_web_link( 'premier-subscription' ) , $this->slplus->get_web_link( 'power' ) , $this->slplus->get_web_link( 'experience' ) );

            // Extend (for add-ons)
        //
        $this->extend_text_strings();
    }

    /**
     * Get a basic text string
     */
    public function get_text_string( $slug ) {
        return ( isset( $this->text_strings[ $slug ] ) ? $this->text_strings[ $slug ] : '' );
    }

    /**
     * Get the default option text for the specified slug.
     *
     * @param   string  $slug
     * @return  string
     */
    public function get_option_default( $slug ) {
        return $this->get_text_string('option-default:' . $slug );
    }

    /**
     * Get an admin-specific text string as specified by the slug.
     *
     * @param  string   $slug
     * @return string           the i18n text
     */
    public function get_admin_text( $slug ) {
        return $this->get_text_string('admin:' . $slug );
    }

    /**
     * Get content for lined text strings as specified by the slug.
     *
     * @param  string   $slug
     * @return string           the i18n text
     */
    public function get_link_content( $slug ) {
        return $this->get_text_string('linkcontent:' . $slug );
    }

    /**
     * Get an linked text string as specified by the slug.
     *
     * @param  string   $slug
     * @return string           the i18n text
     */
    public function get_linked_text( $slug ) {
        return $this->get_text_string('linked:' . $slug );
    }


    /**
     * Get an admin-specific variable (sprintf) text string as specified by the slug.
     *
     * @param   string  $slug       The text slug , 'admin_variable:' will be prefixed and looked up.
     * @param   mixed   $params     The parameters for the sprintf replacements
     *
     * @return  string              the i18n text
     */
    public function get_variable_admin_text( $slug , $params ) {
        if ( ! is_array( $params ) ) {
            $params = (array) $params;
        }
        return vsprintf( $this->get_text_string('admin_variable:' . $slug ) , $params );
    }
}

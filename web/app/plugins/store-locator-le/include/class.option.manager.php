<?php

require_once( SLPLUS_PLUGINDIR . 'include/base_class.object.php');

/**
 * Class SLP_Option_Manager
 *
 * Methods to help manage the SLP options.
 *
 * @package StoreLocatorPlus\Options\Manager
 * @author Lance Cleveland <lance@charlestonsw.com>
 * @copyright 2015 Charleston Software Associates, LLC
 *
 * @since 4.3.20
 *
 * @property-read   string      $option_slug        Used by the get_option_from_wp('js') call.
 * @property-read   string      $option_nojs_slug    Used by the get_option_from_wp('nojs') call.
 *
 */
class SLP_Option_Manager extends SLPlus_BaseClass_Object {
    private     $option_slug     ;
    private     $option_nojs_slug;

    /**
     * Get string defaults.
     *
     * @param string $key key name for string to translate
     *
     * @return string
     */
    private function get_string_default( $key ) {
        $this->slplus->create_object_text_manager();
        $text_to_return = $this->slplus->text_manager->get_option_default( $key );
        if ( empty( $text_to_return ) ) {
            $text_to_return = apply_filters('slp_string_default', '', $key);
        }
        return $text_to_return;
    }

    /**
     * Fetch the Store Locator Plus options from the WordPress options table.
     *
     * Default option name is csl-slplus-options per $this->option_slug.
     *
     * @param   string  $which_option   Should be 'js' (default) or 'nojs'.
     *
     * @return mixed|void
     */
    public function get_option_from_wp( $which_option = 'js' ) {

        switch ( $which_option ) {
            case 'js':
                $slug_to_fetch = $this->option_slug;
                break;
            case 'nojs':
                $slug_to_fetch  = $this->option_nojs_slug;
                break;
            default:
                $slug_to_fetch  = $which_option;
                break;
        }

        /**
         * FILTER: slp_option_slug
         *
         * @param   string      $slug_to_fetch      the name of the wp_options table key to fetch with get_option().
         *
         * @return  string      a modified slug
         */
        $slug_to_fetch = apply_filters( 'slp_option_slug' , $slug_to_fetch );

        return get_option( $slug_to_fetch );
    }

    /**
     * Initialize the options properties from the WordPress database.
     */
    public function initialize()  {
        load_plugin_textdomain( 'store-locator-le', false, plugin_basename( dirname( SLPLUS_PLUGINDIR . 'store-locator-le.php' ) ) . '/languages' );
        $this->option_slug        = SLPLUS_PREFIX . '-options';
        $this->option_nojs_slug   = SLPLUS_PREFIX   . '-options_nojs';
        $this->set_text_string_defaults();

        // Set options defaults to values set in property definition above.
        //
        $this->slplus->options['map_home_icon'] = SLPLUS_ICONURL . 'box_yellow_home.png';
        $this->slplus->options['map_end_icon']  = SLPLUS_ICONURL . 'bulb_azure.png';
        $this->slplus->options_default = $this->slplus->options;

        // Serialized Options from DB for JS parameters
        //
        $dbOptions = $this->get_option_from_wp( 'js' );
        if (is_array($dbOptions)) {
            array_walk($dbOptions, array($this->slplus, 'set_ValidOptions'));
        }

        // Map Center Fallback
        //
        $this->slplus->recenter_map();

        // Load serialized options for noJS parameters
        //
        $this->slplus->options_nojs_default = $this->slplus->options_nojs;
        $dbOptions = $this->get_option_from_wp( 'nojs' );
        if (is_array($dbOptions)) {
            array_walk($dbOptions, array($this->slplus, 'set_ValidOptionsNoJS'));
        }
        $this->slplus->javascript_is_forced = $this->slplus->is_CheckTrue($this->slplus->options_nojs['force_load_js']);

        // Options that get passed to the JavaScript
        // loaded from properties
        //
        foreach ($this->slplus->options as $name => $value) {
            if ( empty( $this->slplus->options[$name] ) && ( ! empty($this->slplus->defaults[$name] ) ) ) {
                $this->slplus->options[$name] = $this->slplus->defaults[$name];
            }
        }
    }

    /**
     * Set text string defaults.
     */
    private function set_text_string_defaults() {
        foreach ( $this->slplus->text_string_options as $key ) {

            if ( array_key_exists( $key , $this->slplus->options ) ) {
                $this->slplus->options[$key] = $this->get_string_default( $key );

            } elseif ( array_key_exists( $key , $this->slplus->options_nojs ) ) {
                $this->slplus->options_nojs[$key] = $this->get_string_default( $key );

            }
        }
    }

}

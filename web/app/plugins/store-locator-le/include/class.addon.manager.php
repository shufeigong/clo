<?php

/**
 * The Store Locator Plus Add On Manager
 *
 * @property 	array                  	$available      				An array of all the available add-on packs we know about. $this->slplus->add_ons->available['slp-pro']['link']
 * 			@var     string                  $available[<slug>]['name']   translated text name of add on
 * 			@var     string                  $available[<slug>]['link']   full HTML anchor link to the product.  <a href...>Name</a>.
 *
 * @property 	SLP_BaseClass_Addon[]  	$instances      				The add on objects in a named array.  The slug is the key. $instances['slp-pro'] => \SLPPro instance.
 *
 * @property	string[]				$recommended_upgrades			Array of add-on slugs that have recommended upgrades.
 * @property	string[]				$upgrades_already_recommended	Array of add-on slugs for those we already told the user about.
 * @property	array					$upgrade_paths					key = the add-on slug to be upgraded, value = the slug for the add-on to upgrade to.
 *
 * @package StoreLocatorPlus\AddOn_Manager
 * @author Lance Cleveland <lance@charlestonsw.com>
 * @copyright 2014-2016 Charleston Software Associates, LLC
 *
 */
class SLPlus_AddOn_Manager extends SLPlus_BaseClass_Object {
    public 	$available = array();
	public 	$instances = array();
	private $recommended_upgrades = array();
	private $upgrades_already_recommended = array();
	private $upgrade_paths = array();

	/**
	 * Custom stuff we do when this object is created.
	 */
	public function initialize() {
		$this->make_always_available();
	}

    /**
     * Given the text to display and the leaf (end) portion of the product URL, return a full HTML link to the product page.
     * 
     * @param string $text
     * @param string $addon_slug
     *
     * @return string
     */
    private function create_string_product_link( $text , $addon_slug ) {

		// If addon_slug is not a simple product slug but a URL:
		//
	    if ( strpos( $addon_slug , '/' ) !== false ) {
			$addon_slug = preg_replace( '/^(.*\/)/', '$1' , $addon_slug );
	    }

        return  $this->slplus->create_web_link( $addon_slug , '', $text , 'https://www.storelocatorplus.com/product/' . $addon_slug . '/' );
    }

	/**
	 * Return an upgrade recommended notice.
	 *
	 * @param   string  $slug   The slug for the add-on needed an upgrade.
	 * @return  string
	 */
	private function create_string_for_recommendations( $slug ) {
		$legacy_name  = $this->instances[ $slug ]->name;
		$upgrade_slug = $this->recommend_upgrade( $slug );
		$upgrade_name = $this->get_product_url( $upgrade_slug );
		return
			sprintf( __('The %s add-on is not running as efficiently as possible. ', 'store-locator-le'), $legacy_name ) .
			'<br/>' .
			sprintf( __('Upgrading to the latest %s add-on is recommended. ', 'store-locator-le'), $upgrade_name );
	}

	/**
	 * Create the recommended upgrades notification text.
	 *
	 * @return string
	 */
	public function get_recommendations_text() {
		$html = '';
		foreach ( $this->recommended_upgrades as $slug ) {
			if ( ! in_array( $slug, $this->upgrades_already_recommended ) ) {
				$html .= $this->create_string_for_recommendations($slug);
				$this->upgrades_already_recommended[] = $slug;
			}
		}
		return $html;
	}

	/**
	 * Fetched installed and active version info.
	 *
	 * @return array
	 */
	public function get_versions() {
		$version_info = array();

		foreach ( $this->instances as $slug => $instance ) {
			$version_info[$slug] = $this->get_version( $slug );
		}

		return $version_info;
	}

	/**
	 * Return the version of the specified registered/active add-on pack.
	 *
	 * @param string $slug
	 *
	 * @return string
	 */
	public function get_version( $slug ) {
		if ( isset( $this->instances[$slug] ) && is_object( $this->instances[$slug] ) ) {
			return $this->instances[ $slug ]->options['installed_version'];
		}
		return '';
	}

	/**
	 * Return the product URL of the specified registered/active add-on pack.
	 *
	 * @param string $slug
	 *
	 * @return string
	 */
	public function get_product_url( $slug ) {

		// Active object, get from meta
		//
		if ( isset( $this->instances[$slug] ) && is_object( $this->instances[$slug] ) ) {

			// Newer meta interface
			//
			if ( method_exists( $this->instances[$slug] , 'get_meta' ) ) {
				return $this->create_string_product_link($this->instances[$slug]->name , $this->instances[$slug]->get_meta('PluginURI') );
			}

			// Older meta interface
			// Remove after all plugins are updated to have get_meta()
			//
			return $this->create_string_product_link($this->instances[$slug]->name , $this->instances[$slug]->metadata['PluginURI'] );
		}

		// Manually registered in available array, link exists.
		//
		if ( isset( $this->available[$slug]['link'] ) ) { return $this->available[$slug]['link']; }
		if ( isset( $this->available[$slug] ) ) {
			switch ( $slug ) {

				case 'slp-enhanced-map':
					return $this->create_string_product_link( $this->available[$slug]['name'] , 'slp4-enhanced-map'            );

				case 'slp-enhanced-results':
					return $this->create_string_product_link( $this->available[$slug]['name'] , 'slp4-enhanced-results'        );

				case 'slp-enhanced-search':
					return $this->create_string_product_link( $this->available[$slug]['name'] , 'slp4-enhanced-search'         );

				case 'slp-experience':
					return $this->create_string_product_link( $this->available[$slug]['name'] , 'experience'         		   );

				case 'slp-power':
					return $this->create_string_product_link( $this->available[$slug]['name'] , 'power'         		       );

				case 'slp-premier':
					return $this->create_string_product_link( $this->available[$slug]['name'] , 'premier-subscription'         );

				case 'slp-pro' :
					return $this->create_string_product_link( $this->available[$slug]['name'] , 'slp4-pro'                     );

				case 'slp-tagalong' :
					return $this->create_string_product_link( $this->available[$slug]['name'] , 'slp4-tagalong'                );
			}


		}

		// Unknown
		//
		return $this->create_string_product_link( __( 'add on' , 'store-locator-le' ) , ''                );
	}

    /**
     * Returns true if an add on, specified by its slug, is active.
     * 
     * @param string $slug
     * @return boolean
     */
    public function is_active ( $slug ) {
        return (
                array_key_exists( $slug, $this->instances ) &&
                is_object($this->instances[$slug]) &&
                !empty($this->instances[$slug]->options['installed_version'])
                );
    }

	/**
	 * Is legacy support needed for any active add-on packs?
	 *
	 * Some add-on packs break when things are updated in the base plugin.
	 *
	 * @param       string      $fq_method     The specific method we are checking in ClassName::MethodName format.
	 * @returns     boolean                    Whether or not an add-on is in use that requires legacy support.
	 */
	public function is_legacy_needed_for( $fq_method ) {
		$active_versions = $this->get_versions();
		foreach ( $active_versions as $slug => $version ) {
			if ( in_array( $slug, $this->recommended_upgrades ) ) { return true; }
			switch ( $slug ) {

				// EM
				case 'slp-enhanced-map':
					if ( version_compare( $version, '4.4' , '<' ) ) {
						if ( $fq_method === 'SLP_BaseClass_Admin::save_my_settings' ) {
							$this->recommended_upgrades[] = $slug;
							return true;
						}
					}
					break;

			}
		}
		return false;
	}

	/**
	 * Add a sanctioned add on pack to the available add ons array.
	 *
	 * @param string $slug
	 * @param string $name
	 * @param boolean $active
	 *
	 * @param SLP_BaseClass_Addon $instance
	 */
	private function make_available( $slug , $name , $active = false, $instance = null ) {
		if (
			! isset( $this->available[$slug] ) ||
		    is_null( $this->available[$slug]['addon'] ) && ! is_null( $instance )
		   ) {

			$this->available[ $slug ] = array(
				'name'   => $name,
				'active' => $active,
				'addon'  => $instance
			);

			// Pro Pack is the only direct-referenced available link in other plugins.
			//
			// TODO: Remove this when other plugins use the get_product_url() method. (ELM,TAG)
			//
			if ( $slug === 'slp-pro' ) {
				$this->available[ $slug ]['link'] =
					is_null( $instance ) ?
						$this->create_string_product_link( $name , 'slp4-pro' ) :
						$this->get_product_url( $slug )                         ;
			}
		}
	}

	/**
     * Make these add ons always available, mostly to be able to reference product links.
     */
    private function make_always_available() {
        $this->make_available( 'slp-enhanced-map'           , __( 'Enhanced Map'           , 'store-locator-le' ) );
        $this->make_available( 'slp-enhanced-results'       , __( 'Enhanced Results'       , 'store-locator-le' ) );
        $this->make_available( 'slp-enhanced-search'        , __( 'Enhanced Search'        , 'store-locator-le' ) );
		$this->make_available( 'slp-experience'        		, __( 'Experience'        	   , 'store-locator-le' ) );
		$this->make_available( 'slp-power'        			, __( 'Power'        	   	   , 'store-locator-le' ) );
	    $this->make_available( 'slp-premier'                , __( 'Premier'                , 'store-locator-le' ) );
        $this->make_available( 'slp-pro'                    , __( 'Pro Pack'               , 'store-locator-le' ) );
        $this->make_available( 'slp-tagalong'               , __( 'Tagalong'               , 'store-locator-le' ) );
    }

	/**
	 * Recommend an add-on for upgrading a legacy plugin.
	 *
	 * @param 	string	$slug
	 * @return 	string	the slug of the add-on to upgrade to.
	 */
	public function recommend_upgrade( $slug ) {
		if ( empty( $this->upgrade_paths ) ) { $this->set_upgrade_paths(); }
		return ( array_key_exists( $slug , $this->upgrade_paths ) ? $this->upgrade_paths[ $slug ] : $slug );
	}

	/**
	 * Register an add on object to the manager class.
	 *
	 * @param string $slug
	 * @param SLP_BaseClass_Addon $object
	 */
	public function register( $slug , $object ) {
		if ( ! is_object( $object ) ) { return; }

		if ( ( $slug === 'slp-experience' ) && version_compare( $object->version , '4.4.03' , '<' ) ) {
			include_once(ABSPATH . 'wp-admin/includes/plugin.php');
			if (is_plugin_active('slp-experience/slp-experience.php')) {
				deactivate_plugins('slp-experience/slp-experience.php');
				add_action(
					'admin_notices',
					create_function(
						'',
						"echo '<div class=\"error\"><p>" .
						__('You must upgrade Experience add-on to 4.4.03 or higher or your site will crash. ', 'store-locator-le') .
						"</p></div>';"
					)
				);
			}
			delete_plugins( array('slp-experience/slp-experience.php') );
			return;
		}

		if ( property_exists( $object , 'short_slug' ) ) {
			$short_slug = $object->short_slug;
		} else {
			$slug_parts = explode( '/', $slug );
			$short_slug = str_replace( '.php', '', $slug_parts[ count( $slug_parts ) - 1 ] );
		}			

		if ( ! isset( $this->instances[$short_slug] ) ||  is_null( $this->instances[$short_slug] )  ) {
			$this->instances[$short_slug] = $object;
			$this->make_available( $short_slug, $object->name, true , $object );
		}
	}

	/**
	 * Set the add-on upgrade paths.
	 */
	private function set_upgrade_paths() {
		$this->upgrade_paths['slp-enhanced-map'] 		= 'slp-experience';
		$this->upgrade_paths['slp-enhanced-results'] 	= 'slp-experience';
		$this->upgrade_paths['slp-enhanced-search'] 	= 'slp-experience';
		$this->upgrade_paths['slp-widget'] 				= 'slp-experience';
	}

}

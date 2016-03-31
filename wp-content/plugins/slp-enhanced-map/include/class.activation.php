<?php
if (! class_exists('SLPEnhancedMap_Activation')) {

    require_once(SLPLUS_PLUGINDIR.'/include/base_class.activation.php');

    /**
     * Manage plugin activation.
     *
     * @package StoreLocatorPlus\EnhancedMap\Activation
     * @author Lance Cleveland <lance@charlestonsw.com>
     * @copyright 2013 - 2014 Charleston Software Associates, LLC
     *
     */
    class SLPEnhancedMap_Activation extends SLP_BaseClass_Activation {

	    /**
	     * key = old setting key , value = new options array key
	     * @var array
	     */
	    public $legacy_options = array(
		    'csl-slplus-enmap_hidemap'      => array( 'key' => 'hide_map'           ),
		    'csl-slplus-show_maptoggle'     => array( 'key' => 'show_maptoggle'     ),
		    'sl_starting_image'             => array( 'key' => 'starting_image'     ),
	    );

    }
}
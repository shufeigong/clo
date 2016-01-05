<?php

if ( ! class_exists('SLPlus_BaseClass_Object') ) {

	/**
	 * Class SLPlus_BaseClass_Object
	 *
	 * @package StoreLocatorPlus\BaseClass\Addon
	 * @author Lance Cleveland <lance@charlestonsw.com>
	 * @copyright 2014-2015 Charleston Software Associates, LLC
	 */
	class SLPlus_BaseClass_Object {

		/**
		 * @var SLPlus
		 */
		protected $slplus;

		/**
		 * @var boolean set to true if this object uses the slplus base object
		 */
		protected $uses_slplus = true;

		/**
		 * @param array $options
		 */
		function __construct( $options = array() ) {

			if ( is_array( $options ) && ! empty( $options ) ) {
				foreach ( $options as $property => $value ) {
					if ( property_exists( $this, $property ) ) {
						$this->$property = $value;
					}
				}
			}

			if ( $this->uses_slplus ) {
				global $slplus_plugin;
				$this->slplus = $slplus_plugin;
			}
		}
	}

}
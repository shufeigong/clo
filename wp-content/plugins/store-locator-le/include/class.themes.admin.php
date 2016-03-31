<?php

/**
 * SLP Styles Admin Class
 *
 * @package Store Locator Plus\Styles\Admin
 * @author Lance Cleveland <lance@charlestonsw.com>
 * @copyright 2014 - 2015 Charleston Software Associates, LLC
 *
 * @property        string                          $css_dir                The theme CSS directory, absolute.
 * @property        string                          $css_url                The theme CSS URL, absolute.
 * @property-read   string                          $current_slug           The current theme slug.
 * @property        wpCSL_notifications__slplus     $notifications          Plugin notifications system.
 * @property-read   mixed[]                         $themeDetails           A named array containing meta data about the CSS theme.
 * @property-read   mixed[]                         $theme_options_field    The array of theme meta data option fields in slug => full_text format.
 */
class SLP_Styles_Admin  extends SLPlus_BaseClass_Object {
    public $css_dir;
    public $css_url;
    private $current_slug;
    public $notifications;
    private $themeDetails;
    private $theme_option_fields;

    /**
     * Build an HTML string to show under the theme selection box.
     * 
     * @return string
     */
    private function createstring_ThemeDetails() {
        $HTML = "<div id='{$this->current_slug}_details' class='theme_details'>";

        // Description
        //
        $HTML .= $this->slplus->helper->create_SubheadingLabel(__('About This Style','store-locator-le') , true );
        if ( empty ( $this->themeDetails[$this->current_slug]['description'] ) ) {
            $HTML .= __('No description has been set for this style.','store-locator-le');
        } else {
            $HTML .= $this->themeDetails[$this->current_slug]['description'];
        }

        // Theme Image
        // Show Image
        //
        if ( is_readable( SLPLUS_PLUGINDIR . 'images/plugin_styles/' . $this->current_slug . '.png' ) ) {
            $HTML .=
                sprintf('<span class="style_sample"><img src="%s" alt="%s example" title="%s example"></span>',
                    SLPLUS_PLUGINURL . '/images/plugin_styles/' . $this->current_slug . '.png',
                    $this->current_slug,
                    $this->current_slug
                );
        }
        
        $HTML .= 
            '<p>' .
            __('Learn more about changing the Store Locator Plus interface via the ' , 'store-locator-le') .
            sprintf(
                '<a href="%s" target="csa">%s</a>',
                $this->slplus->support_url . 'user-experience/view/styles-themes-custom-css/',
                __('Plugin Styles documentation.','store-locator-le')
            ) .
            '</p>';

        // Add On Packs
        //
        if ( ! empty( $this->themeDetails[$this->current_slug]['add-ons'] ) ) {
            $HTML .= $this->create_string_addon_packs();
        }

        $HTML .= '</div>';

        return $HTML;
    }

    /**
     * Create the output string for add on packs used by themes.
     *
     * @return string
     */
    private function create_string_addon_packs() {
        $this->slplus->createobject_AddOnManager();
        $this->setup_ThemeOptionFields();
        $addon_list = explode(',', $this->themeDetails[$this->current_slug]['add-ons']);

        $html = '';

        foreach ($addon_list as $slug) {
            $slug = trim(strtolower($slug));
            $theme_slug = $this->get_theme_slug( $slug );
            $theme_settings = ! is_null( $theme_slug ) ? $this->theme_option_fields[$theme_slug] : null;

            if (isset($this->slplus->add_ons->available[$slug])) {
                $product_link = $this->slplus->add_ons->get_product_url($slug);
                $active_class = $this->slplus->is_AddOnActive($slug) ? 'active' : 'inactive';
                $html .= $this->create_string_addon_layout( $theme_slug, $theme_settings, $product_link, $active_class);
            }
        }

        if ( ! empty( $html ) ) {
            $html =
                $this->slplus->helper->create_SubheadingLabel(__('Add On Packs', 'store-locator-le') , true ) .
                __('This plugin style will make use of the following add-on packs. ', 'store-locator-le')    .
                __('Layouts for active add-on packs will be changed when you change the style and click save settings. ', 'store-locator-le')    .
                __('Greyed-out entries are not installed or activated. ' , 'store-locator-le' )       .
                $html
            ;
        }

        return $html;
    }

    /**
     * Create the string for the theme layouts.
     *
     * @param string $slug
     * @param string[] $settings
     * @param string $activity_class
     * @return string
     */
    private function create_string_addon_layout( $slug, $settings , $product_link, $activity_class ) {
        if ( is_null( $slug ) || is_null( $settings ) ) { return ''; }
        return
            "<div class='theme_option {$settings['slug']} {$activity_class}'> "             .
                "<span class='theme_option_label'>{$product_link} {$settings['short_name']}</span>"               .
                "<pre class='theme_option_value' settings_field='{$settings['field']}'>"    .
                    esc_textarea( $this->themeDetails[$this->current_slug][$slug] )         .
                '</pre>' .
            '</div>'
            ;
    }

    /**
     * Get the theme slug for a given plugin slug.
     *
     * @param $plugin_slug
     * @return int|null|string
     */
    private function get_theme_slug( $plugin_slug ) {
        foreach ( $this->theme_option_fields as $key => $option ) {
            if( $option['slug'] === $plugin_slug ) { return $key; }
        }
        return null;
    }

    /**
     * Extract the label & key from a CSS file header.
     *
     * @param string $filename - a fully qualified path to a CSS file
     * @return mixed - a named array of the data.
     */
    private function get_ThemeInfo ($filename) {
        $dataBack = array();
        if ($filename != '') {
           $default_headers =
               array(
                'add-ons'       => 'add-ons',
                'description'   => 'description',
                'file'          => 'file',
                'label'         => 'label',
               );
           $all_headers = $this->setup_PluginThemeHeaders($default_headers);
           $dataBack = get_file_data($filename,$all_headers,'plugin_theme');
           $dataBack['file'] = preg_replace('/.css$/','',$dataBack['file']);
        }

        return $dataBack;
     }

    /**
     * Add the theme settings to the admin panel.
     *
     * @param SLP_Settings $settings
     */
    public function add_settings($settings ,$section_name ,$group_name ) {

        // Exit is directory does not exist
        //
        if (!is_dir($this->css_dir)) {
            if (isset($this->notifications)) {
                $this->notifications->add_notice(
                    2,
                    sprintf( __('The styles directory:<br/>%s<br/>is missing. ', 'store-locator-le'), $this->css_dir ) .
                    __( 'Create it to enable styles and get rid of this message.', 'store-locator-le' )
                );
            }
            return;
        }

        // The Styles
        // No styles? Force the default at least
        //
        $themeArray = get_option(SLPLUS_PREFIX.'-theme_array');
        if (count($themeArray, COUNT_RECURSIVE) < 2) {
            $themeArray = array('Default' => 'default');
        }
		
		// Remove from drop down list if style file does not exist in the plugin dir
		//
		foreach( $themeArray as $k => $theme ){
			if( ! file_exists( $this->css_dir . $theme . '.css' ) ){
				unset( $themeArray[$k] );
			}
			
		}
		
        // Check for theme files
        //
        $lastNewThemeDate = get_option(SLPLUS_PREFIX.'-theme_lastupdated');
        $newEntry = array();
        if ($dh = opendir($this->css_dir)) {
            while (($file = readdir($dh)) !== false) {
	            if ( ! is_readable( $this->css_dir.$file ) ) { continue; }

                // If not a hidden file
                //
                if (!preg_match('/^\./',$file)) {
                    $thisFileModTime = filemtime($this->css_dir.$file);

                    // We have a new style file possibly...
                    //
                    if ($thisFileModTime > $lastNewThemeDate) {
                        $newEntry = $this->get_ThemeInfo($this->css_dir.$file);
                        $themeArray = array_merge($themeArray, array($newEntry['label'] => $newEntry['file']));
                        update_option(SLPLUS_PREFIX.'-theme_lastupdated', $thisFileModTime);
                    }
                }
            }
            closedir($dh);
        }


        // Remove empties and sort
        $themeArray = array_filter($themeArray);
        uksort($themeArray , 'strcasecmp' );

        // Delete the default style if we have specific ones
        //
        $resetDefault = false;

        if ((count($themeArray, COUNT_RECURSIVE) > 1) && isset($themeArray['Default'])){
            unset($themeArray['Default']);
            $resetDefault = true;
        }

        // We added at least one new theme
        //
        if ((count($newEntry, COUNT_RECURSIVE) > 1) || $resetDefault) {
            update_option(SLPLUS_PREFIX.'-theme_array',$themeArray);
        }


        if ($section_name==null) { $section_name = 'Display Settings'; }

        $settings->add_ItemToGroup(array(
            'label'       => __('Plugin Style' , 'store-locator-le' )   ,
            'type'        => 'subheader'                          ,
            'description' => __( 'Select a plugin style to change the CSS styling and layout of the slplus shortcode elements.' , 'store-locator-le' ),
            'section'     => $section_name                        ,
            'group'       => $group_name                          ,

        ));

        $settings->add_itemToGroup(array(
			'description'   =>
			    __('How should the plugin UI elements look?  ','store-locator-le') .
			    sprintf(
			        __('Learn more in the <a href="%s" target="slp">online documentation</a>.','store-locator-le'),
			        $this->slplus->support_url . 'user-experience/view/themes-custom-css/'
			    )
			    ,
			'setting'       => 'theme'                                      ,
            'show_label'    => false,
			'value'         => $this->slplus->options_nojs['theme']         ,
			'onChange'      => "AdminUI.show_ThemeDetails(this);"           ,
			'type'          => 'list'                                       ,
			'section'       => $section_name                                ,
			'group'         => $group_name                                  ,
			'custom'        => $themeArray                                  ,
			));

        // Add Style Details Divs
        //
        $settings->add_ItemToGroup(array(
	            'label'         => '',
                'description'   => $this->setup_ThemeDetails($themeArray),
                'section'       => $section_name     ,
                'group'         => $group_name       ,
                'setting'       => 'themedesc'  ,
                'type'          => 'subheader'  ,
                'show_label'    => false
                ));
    }

    /**
     * Add the style-specific headers to the get_file_data header processor.
     * 
     * @param string[] $headers
     * @return array
     */
    private function setup_PluginThemeHeaders($headers) {
        $this->setup_ThemeOptionFields();
        $option_headers = array();
        foreach ( $this->theme_option_fields as $option_slug => $option_settings ) {
            $option_headers[$option_slug] = $option_settings['name'];
        }
        return array_merge($headers,$option_headers);
    }

    /**
     * Setup the array of style meta data options fields.
     */
    private function setup_ThemeOptionFields() {
        if ( count($this->theme_option_fields) > 0 ) { return; }

        $this->theme_option_fields =
            array(
                'PRO.layout'    => array(
                    'slug'  => 'slp-pro',
                    'name'  => 'Pro Pack Locator Layout',
                    'short_name' => __( 'Locator Layout' , 'store-locator-le' ) ,
                    'field' => 'csl-slplus-layout'
                ),
                'EM.layout'    => array(
                    'slug'  => 'slp-enhanced-map',
                    'name'  => 'Enhanced Map Bubble Layout',
                    'short_name' => __( 'Bubble Layout' , 'store-locator-le' )  ,
                    'field' => 'bubblelayout'
                ),
                'ER.layout'    => array(
                    'slug'  => 'slp-enhanced-results',
                    'name'  => 'Enhanced Results Results Layout',
                    'short_name'  => __( 'Results Layout' , 'store-locator-le' )  ,
                    'field' => 'csl-slplus-ER-options[resultslayout]'
                ),
                'ES.layout'    => array(
                    'slug'  => 'slp-enhanced-search',
                    'name'  => 'Enhanced Search Search Layout',
                    'short_name'  => __( 'Search Layout' , 'store-locator-le' )  ,
                    'field' => 'csl-slplus-ES-options[searchlayout]'
                ),
                'results_header'    => array(
                    'slug' => 'slp-premier',
                    'name' => 'Results Header',
                    'short_name' => __( 'Results Header' , 'store-locator-le' )  ,
                    'field' => 'slp-premier-options[results_header]',
                ),
            );
    }

    /**
     * Create the details divs for the SLP styles.
     *
     * @param mixed[] $themeArray
     * @return string the div HTML
     */
    private function setup_ThemeDetails($themeArray) {
        $HTML = '';
        $newDetails = false;

        // Get an array of metadata for each style present.
        //
        $this->themeDetails = get_option(SLPLUS_PREFIX.'-theme_details');

        // Check all our styles for details
        //
        foreach ($themeArray as $label=>$theme_slug) {

            // No details? Read from the CSS File.
            //
            if (
                !isset($this->themeDetails[$theme_slug]) || empty($this->themeDetails[$theme_slug]) ||
                !isset($this->themeDetails[$theme_slug]['label']) || empty($this->themeDetails[$theme_slug]['label'])
                ) {

	            if ( is_readable( $this->css_dir . $theme_slug . '.css' ) ) {
		            $themeData            = $this->get_ThemeInfo( $this->css_dir . $theme_slug . '.css' );
		            $themeData['fqfname'] = $this->css_dir . $theme_slug . '.css';

		            $this->themeDetails[ $theme_slug ] = $themeData;
		            $newDetails                        = true;
	            }
            }

            $this->current_slug = $theme_slug;
            $HTML .= $this->createstring_ThemeDetails();
        }

        // If we read new details, go save to disk.
        //
        if ($newDetails) {
            update_option(SLPLUS_PREFIX.'-theme_details',$this->themeDetails);
        }

        return $HTML;
    }
}

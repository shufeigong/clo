<?php

/**
 * The wpCSL Themes Class
 *
 * @property        SLP_Styles_Admin        $admin
 * @property        string                  $css_dir        The theme CSS directory, absolute.
 * @property-read   string                  $css_urls       The theme CSS URL, absolute.
 *
 * @package StoreLocatorPlus\Themes
 * @author Lance Cleveland <lance@charlestonsw.com>
 * @copyright 2012-2015 Charleston Software Associates, LLC
 *
 */
class PluginTheme extends SLPlus_BaseClass_Object
{
    public $admin;
    public $css_dir = 'css/';
    private $css_url;

    /**
     * Theme constructor.
     *
     * @param mixed[] $options named array of properties
     */
    function __construct($options = array()) {
        parent::__construct($options);

        $this->css_url = SLPLUS_PLUGINURL . '/' . $this->css_dir;
        $this->css_dir = SLPLUS_PLUGINDIR . $this->css_dir;
    }

    /**
     * Add settings for admin.
     *
     * @param $settings
     * @param $section_name
     * @param $group_name
     */
    function add_settings($settings, $section_name, $group_name) {
        $this->create_object_admin();
        $this->admin->add_settings( $settings, $section_name, $group_name );
    }


     /**
      * Assign the plugin specific UI stylesheet.
      *
      * For this to work with shortcode testing you MUST call it via the WordPress wp_footer action hook.
      *
      * @param string $themeFile if set use this theme v. the database setting
      * @param boolean $preRendering
      */
    function assign_user_stylesheet($themeFile = '',$preRendering = false) {
        // If themefile not passed, fetch from db
        //
        if ($themeFile == '') {
            $themeFile = $this->options_nojs['theme'] . '.css';

        } else {
            // append .css if left off
            if ((strlen($themeFile) < 4) || substr_compare($themeFile, '.css', -strlen('.css'), strlen('.css')) != 0) {
                $themeFile .= '.css';
            }
        }

        // go to default if theme file is missing
        //
        if ( !file_exists($this->css_dir.$themeFile)) {
            $themeFile = 'default.css';
        }

        // If the theme file exists (after forcing default if necessary)
        // queue it up
        //
        if ( file_exists($this->css_dir.$themeFile)) {
            wp_deregister_style(SLPLUS_PREFIX.'_user_header_css');
            wp_dequeue_style(SLPLUS_PREFIX.'_user_header_css');
            if ($this->slplus->shortcode_was_rendered || $preRendering) {
                wp_enqueue_style(SLPLUS_PREFIX.'_user_header_css', $this->css_url .$themeFile);
            }
        }
    }

    /**
     * Attach admin object to this->admin.
     */
    private function create_object_admin() {
        if ( ! isset( $this->admin ) ) {
            require_once('class.themes.admin.php');
            $this->admin = new SLP_Styles_Admin(
                    array(
                        'css_dir' => $this->css_dir,
                        'css_url' => $this->css_url
                    )
            );
        }
    }

}

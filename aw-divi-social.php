<?php
/*
 * Plugin Name: AW Divi Social
 * Version: 2.1.2
 * Plugin URI: http://atlanticwave.co/
 * Description: Additional Social Media icons for your headers and footers
 * Author: Atlantic Wave
 * Author URI: http://atlanticwave.co/
 * Requires at least: 4.0
 * Tested up to: 4.0
 *
 * Text Domain: aw-divi-social
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Atlantic Wave
 * @since 1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

define('AW_DS_VERSION', '2.1.3');


class AW_Divi_Social_Media
{

    /**
     * The single instance of WordPress_Plugin_Template.
     * @var     object
     * @access  private
     * @since   2.0.0
     */
    private static $_instance = null;
    /**
     * Settings class object
     * @var     object
     * @access  public
     * @since   2.0.0
     */
    public $settings = null;
    /**
     * The version number.
     * @var     string
     * @access  public
     * @since   2.0.0
     */
    public $_version;
    /**
     * The token.
     * @var     string
     * @access  public
     * @since   2.0.0
     */
    public $_token;
    /**
     * The main plugin file.
     * @var     string
     * @access  public
     * @since   2.0.0
     */
    public $file;
    /**
     * The main plugin directory.
     * @var     string
     * @access  public
     * @since   2.0.0
     */
    public $dir;
    /**
     * The plugin assets directory.
     * @var     string
     * @access  public
     * @since   2.0.0
     */
    public $assets_dir;
    /**
     * The plugin assets URL.
     * @var     string
     * @access  public
     * @since   2.0.0
     */
    public $assets_url;
    /**
     * Suffix for Javascripts.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $script_suffix;
    /**
     * Flag for if debug mode is enabled
     * @var bool
     * @access public
     * @since 2.1.3
     */
    public $debug_enabled = false;

    /**
     * Constructor function.
     * @access  public
     * @param string $file
     * @param string $version
     *
     * @return  void
     * @since   1.0.0
     *
     */
    public function __construct($file = '', $version = '1.0.0')
    {
        // log init
        aw_debug('-------------------------------------');
        aw_debug(get_class($this) . ' Initialized ');
        aw_debug(date("F j, Y, g:i a"));

        $this->_version = $version;
        $this->_token = 'aw_divi_social';
        // Load plugin environment variables
        $this->file = $file;
        $this->dir = dirname($this->file);
        $this->assets_dir = trailingslashit($this->dir) . 'assets';
        $this->assets_url = esc_url(trailingslashit(plugins_url('/assets/', $this->file)));
        $this->script_suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

        // Load API for generic admin functions
        if (is_admin()) {
            aw_debug(' - attaching admin hooks and styles');
            add_filter('et_epanel_layout_data', array($this, 'et_epanel_layout_data'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_styles_admin'), 10);
        }

        // log
        aw_debug(' - attaching public hooks and styles');

        // Load frontend JS & CSS
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'), 10);

        // add_action( 'init', array( $this, 'load_localisation' ), 0 );

        /**
         * Inject social media icons into header
         */
        add_filter('et_html_top_header', array($this, 'update_et_html_top_header'));

        /**
         * Inject social media icons into footer
         */
        add_action('et_after_main_content', array($this, 'ob_start'));
        add_action('wp_footer', array($this, 'ob_end'));

        //
        add_action('plugins_loaded', array($this, 'update_debug_mode'));
    }

    /**
     *
     */
    function update_debug_mode() {
        $this->debug_enabled = get_option('divi_enable_AWDiviSocialDebug', $this->debug_enabled);
    }

    /**
     * Cloning is forbidden.
     *
     * @since 1.0.0
     */
    public function __clone()
    {
        // log
        aw_debug(' - WARNING: as attempt to clone class detected');
        // fail
        _doing_it_wrong(__FUNCTION__, __('Please don\'t try to clone this object'), $this->_version);
    }

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 1.0.0
     */
    public function __wakeup()
    {
        //
        aw_debug(' - WARNING: an attempt to give class coffee was detected');
        //
        _doing_it_wrong(__FUNCTION__, __('Please don\'t try to wakeup this object'), $this->_version);
    }

    /**
     * Enqueue admin only scripts
     *
     * @since 2.1.3
     */
    public function enqueue_styles_admin()
    {
        //
        aw_debug(' - function executed: enqueue_styles_admin');
        //
        wp_enqueue_script('aw-divi-social', esc_url($this->assets_url) . '/aw-divi-social.js', ['epanel_functions_init'], '0.1');
    }

    /**
     * Load frontend CSS.
     * @access  public
     * @return void
     * @since   1.0.0
     */
    public function enqueue_styles()
    {
        //
        aw_debug(' - function executed: enqueue_styles');
        //
        wp_register_style($this->_token . '-font-awesome', esc_url($this->assets_url) . 'font-awesome/fontawesome-all.min.css', array(), $this->_version);
        wp_enqueue_style($this->_token . '-font-awesome');


    }

    /**
     * Main WordPress_Plugin_Template Instance
     *
     * Ensures only one instance of WordPress_Plugin_Template is loaded or can be loaded.
     *
     * @param string $file
     * @param string $version
     *
     * @static
     * @return object $_instance
     * @see WordPress_Plugin_Template()
     *
     * @since 1.0.0
     *
     */
    public static function instance($file = '', $version = '1.0.0')
    {
        //
        aw_debug(' - function executed: instance');
        //
        if (is_null(self::$_instance)) {
            self::$_instance = new self($file, $version);
        }

        return self::$_instance;
    }

    /**
     * Hooked into the et_epanel_layout_data filter
     * Add additional social media options to the Divi Theme Options panel
     *
     * @param $options
     *
     * @return array
     */
    public function et_epanel_layout_data($original_options)
    {
        //
        $new_options = array();

        //
        aw_debug(' - function executed: et_epanel_layout_data');

        // if this array is updated, need to update array in templates/social-icons.php
        $additional_options = array(
            'dribbble' => 'Dribbble',
            //'facebook'
            'flikr' => 'Flikr',
            //google
            'houzz' => 'Houzz',
            //instagram
            'linkedin' => 'Linkedin',
            'meetup' => 'Meetup',
            'myspace' => 'MySpace',
            'pinterest' => 'Pinterest',
            'podcast' => 'Podcast',
            'skype' => 'Skype',
            'soundcloud' => 'SoundCloud',
            'spotify' => 'Spotify',
            'tumblr' => 'Tumblr',
            //twitter
            'yelp' => 'Yelp',
            'youtube' => 'YouTube',
            'vimeo' => 'Vimeo',
            'vine' => 'Vine',
        );

        //
        aw_debug(' - found ' . count($additional_options) . ' to inject in to setting form');

        // build new social setting options
        foreach ($additional_options as $option_name => $option_title) {

            //
            aw_debug(' - building setting form field block for ' . $option_name);

            $new_options[] = array(
                'name' => sprintf(
                /* translators: %s: option title */
                    esc_html__('Show %s Icon', 'divi'),
                    $option_title
                ),
                'id' => 'divi_show_' . $option_name . '_icon',
                'type' => 'checkbox',
                'std' => 'on',
                'desc' => sprintf(
                /* translators: %s: option title */
                    esc_html__('Here you can choose to display the %s Icon on your homepage', 'divi'),
                    $option_title
                ),
                'add_visible_toggle' => $option_name . "_panelwrapper_tag"
            );

            $new_options[] = array(
                "name" => $option_name . "_panelwrapper_div",
                "type" => "panelwrapper-start",
                'visible_toggle_default' => 'hidden',
                'visible_toggle_test_value' => 'divi_show_' . $option_name . '_icon'
            );

            $new_options[] = array(
                'name' => sprintf(
                /* translators: %s: option title */
                    esc_html__('%s Url', 'divi'),
                    $option_title
                ),
                'id' => 'divi_' . $option_name . '_url',
                'std' => '#',
                'type' => 'text',
                'validation_type' => 'url',
                'desc' => sprintf(
                /* translators: %s: option title */
                    esc_html__('Enter your  %s Url', 'divi'),
                    $option_title
                ),
            );

            $new_options[] = array(
                "name" => esc_html__("Set Mouseover Title", 'divi'),
                "id" => "divi_{$option_name}_title",
                "std" => "",
                "type" => "text",
                "validation_type" => "nohtml",
                "desc" => esc_html__("Enter the title to use when the user hovers the social icon. ", 'divi')
            );


            $new_options[] = array("name" => "general-1a", "type" => "panelwrapper-end",);
        }

        return $this->extract_preset_social_links($original_options, $new_options);
    }

    private function extract_preset_social_links($options, $new_options)
    {
        //
        aw_debug(' - function executed: extract_preset_social_links');

        //
        $original_options = $options;

        // insert locations for preset social tags
        $haystackInsertLocList = [
            'facebook' => 'flikr',
            'google' => 'houzz',
            'instagram' => 'linkedin',
            'twitter' => 'yelp',
            'rss' => 'et_use_google_fonts'
        ];

        // existing social icon reference info
        $haystack = [
            'divi_show_facebook_icon' => 'facebook',
            'divi_facebook_url' => 'facebook',

            'divi_show_twitter_icon' => 'twitter',
            'divi_twitter_url' => 'twitter',

            'divi_show_google_icon' => 'google',
            'divi_google_url' => 'google',

            'divi_show_instagram_icon' => 'instagram',
            'divi_instagram_url' => 'instagram',

            'divi_show_rss_icon' => 'rss',
            'divi_rss_url' => 'rss'
        ];

        // extract the pre-existing social form elements from the settings array
        $updateOptions = [];
        foreach ($original_options as $optionKey => $optionValue) {
            // check if the options id is in the list to update
            if (in_array($optionValue['id'], array_keys($haystack))) {
                // extract the option from the main settings array
                $updateOptions[$haystack[$optionValue['id']]][] = $optionValue;
                unset($original_options[$optionKey]);
            }
        }

        // reset the index for the $original_options array to account for the removed items
        $original_options = array_values($original_options);

        //
        aw_debug(' - found ' . count($updateOptions) . ' to extend in settings page');

        // setup the extra toggle for the debug log
        $extracted_options = [];
        $extracted_options[] = array(
            'name' => sprintf(
                /* translators: %s: option title */
                esc_html__('Enable %s Icon', 'divi'),
                "AW Divi Social Debug Log"
            ),
            'id' => 'divi_enable_AWDiviSocialDebug',
            'type' => 'checkbox',
            'std' => 'on',
            'desc' => sprintf(
                /* translators: %s: option title */
                esc_html__('Here you can choose to enable the %s', 'divi'),
                'AW Divi Social Debug Log'
            )
        );

        // insert the special debug enable first in the new options array
        array_splice($new_options, 0, 0, $extracted_options);

        // clear the array
        $extracted_options = [];

        // should not happen, but just incase
        if (!empty($updateOptions)) {

            // process the extracted items
            foreach ($updateOptions as $option_name => $option_value) {

                // $option_value[]
                // 0 = checkbox
                // 1 = url

                // add in the toggle tag to the existing checkbox option array
                $option_value[0]['add_visible_toggle'] = $option_name . "_panelwrapper_tag";

                // add the checkbox to the options list
                $extracted_options[] = $option_value[0];

                // start the pannelwrapper div
                $extracted_options[] = array(
                    "name" => $option_name . "_panelwrapper_div",
                    "type" => "panelwrapper-start",
                    'visible_toggle_default' => 'hidden',
                    'visible_toggle_test_value' => 'divi_show_' . $option_name . '_icon'
                );

                // add the url to the options list
                $extracted_options[] = $option_value[1];

                // add the new mouseover to the options list
                $extracted_options[] = array(
                    "name" => esc_html__("Set Mouseover Title", 'divi'),
                    "id" => "divi_{$option_name}_title",
                    "std" => "",
                    "type" => "text",
                    "validation_type" => "nohtml",
                    "desc" => esc_html__("Enter the title to use when the mouse of over the social icon. ", 'divi')
                );

                // end the pannelwrapper div
                $extracted_options[] = array("name" => "general-1a", "type" => "panelwrapper-end",);

                //
                aw_debug(' - updated setting form fields for ' . $option_name);

                if (isset($haystackInsertLocList[$option_name])) {

                    //
                    // if rss, then keep it in the general tab but update the layout
                    if ($option_name == 'rss') {
                        $insertLocCustom = $this->find_option_location1($original_options,
                            ['id' => $haystackInsertLocList[$option_name]]);

                        // if the item is not found for some reason, fall back to the base array insert location
                        if (!$insertLocCustom[0]) {
                            $insertLocCustom[1] = count($new_options) - 1;
                        }

                        // insert the new options in to the main options array
                        array_splice($original_options, $insertLocCustom[1], 0, $extracted_options);

                    } else {
                        $insertLocCustom = $this->find_option_location1($new_options,
                            ['id' => 'divi_show_' . $haystackInsertLocList[$option_name] . '_icon']);

                        // if the item is not found for some reason, fall back to the base array insert location
                        if (!$insertLocCustom[0]) {
                            $insertLocCustom[1] = count($new_options) - 1;
                        }

                        // insert the new options in to the main options array
                        array_splice($new_options, $insertLocCustom[1], 0, $extracted_options);
                    }

                    // reset the new_options array
                    $extracted_options = [];
                } else {
                    // insert the new options in to the main options array
                    array_splice($new_options, count($new_options) - 1, 0, $extracted_options);

                    // reset the new_options array
                    $extracted_options = [];
                }

            }

            // build the new tab content for the socials tab
            $options = array(

                array(
                    "name" => "wrap-socialicons",
                    "type" => "contenttab-wrapstart",
                ),

                array("type" => "subnavtab-start",),

                array(
                    "name" => "social-1",
                    "type" => "subnav-tab",
                    "desc" => esc_html__("Social Icons", 'divi')
                ),

                array("type" => "subnavtab-end",),

                array(
                    "name" => "social-1",
                    "type" => "subcontent-start",
                ),

                // insert new array here
                //$new_options,

                array(
                    "name" => "social-1",
                    "type" => "subcontent-end",
                ),

                array(
                    "name" => "wrap-socialicons",
                    "type" => "contenttab-wrapend",
                ),
            );

            // find the spot to insert the settings options
            $loc = $this->find_option_location1($options, array("name" => "social-1", "type" => "subcontent-start"));
            array_splice($options, $loc[1] + 1, 0, $new_options);

            // insert the new options in to the main options array
            $optionKey = $this->find_option_location1($original_options,
                array("name" => "wrap-general", "type" => "contenttab-wrapend"));
            array_splice($original_options, $optionKey[1] + 1, 0, $options);

            // return the updated settings array
            return $original_options;
        }

        // something didnt work, return the true original options list
        return $options;
    }


    private function find_option_location1($options_list, $search_list)
    {
        //
        aw_debug(' - function executed: find_option_location');

        //
        foreach ($options_list as $optionKey => $optionValue) {

            //var_dump($optionKey . '] ' . $optionValue['id'] . ' - ' . $optionValue['name']. ' , ' . $optionValue['type']);

            $match = false;
            foreach ($search_list as $searchKey => $searchValue) {
                if ($optionValue[$searchKey] === $searchValue) {
                    $match = true;
                } else {
                    $match = false;
                }
            }

            if ($match) {
                return [true, $optionKey];
            }

        }
        return [false];
    }

    private function find_option_location($options_list, $search_item, $search_key)
    {
        //
        aw_debug(' - function executed: find_option_location');

        //
        foreach ($options_list as $optionKey => $optionValue) {

            //var_dump($optionKey . '] ' . $optionValue['id'] . ' - ' . $optionValue['name']);

            // watch for tag to be used later as insert new options location
            if ($optionValue[$search_key] === $search_item) {
                // move one item before, for insert location
                return [true, $optionKey];
            }
        }
        return [false];
    }

    /**
     * Start output buffering
     */
    public function ob_start()
    {
        //
        aw_debug(' - function executed: ob_start');

        //
        ob_start();
    }

    /**
     * End output buffering, replace default social media icons with updated icons and return updated content
     */
    public function ob_end()
    {
        //
        aw_debug(' - function executed: ob_end');

        //
        $content = ob_get_clean();
        $social_icons = $this->get_social_icons();
        $content = preg_replace('/<ul class=\"et-social-icons\">.*?<\/ul>/is', $social_icons, $content);
        // @todo determine correct escaping function here
        echo $content;
    }

    /**
     * Update the social media icons in the header
     *
     * Hooks into the et_html_top_header filter to update the $top_content variable
     *
     * @param $top_header
     *
     * @return null|string|string[]
     */
    public function update_et_html_top_header($top_header)
    {
        //
        aw_debug(' - function executed: update_et_html_top_header');

        //
        $social_icons = $this->get_social_icons();
        $top_header = preg_replace('/<ul class=\"et-social-icons\">.*?<\/ul>/is', $social_icons, $top_header);
        return $top_header;
    }

    /**
     * Get updated social media icons from database
     *
     * @return string
     */
    public function get_social_icons()
    {
        //
        aw_debug(' - function executed: get_social_icons');

        //
        ob_start();
        require $this->dir . '/templates/social-icons.php';
        return ob_get_clean();
    }

}

// Instantiate the plugin class
$aw_divi_social_media = AW_Divi_Social_Media::instance(__FILE__, AW_DS_VERSION);



function aw_debug($data)
{
    global $aw_divi_social_media;

    if ($aw_divi_social_media->debug_enabled) {
        $file = plugin_dir_path(__FILE__) . 'awds.log.' . date('d-m-Y') . '.txt';
        if (!is_file($file)) {
            file_put_contents($file, '');
        }
        $data_string = print_r($data, true) . "\n";
        file_put_contents($file, $data_string, FILE_APPEND);
    }
}


/* --------------------------------------------- */
/*
 * Overwrite the et_build_epanel function to support new features
 *
 */
if (!function_exists('et_build_epanel') && is_admin()) {
    global $divi_ePanel_handler;

    /* load the Divi ePanel handler class*/
    include_once 'et_build_epanel_handler.php';

    /* init the custom Divi ePanel handler */
    $divi_ePanel_handler = new et_build_epanel_handler();

    // verify that the social plugin (this plugin file) is active
    if ($divi_ePanel_handler->is_plugin_active(ltrim(str_replace(realpath(__DIR__ . '/..'), '', __FILE__), '/'))) {
        // overwrite the Divi et_build_epanel function
        function et_build_epanel()
        {
            /** @var et_build_epanel_handler $divi_ePanel_handler */
            global $divi_ePanel_handler;

            /* pass any function calls to the class */
            $divi_ePanel_handler->et_build_epanel();
        }
    }
}
/* --------------------------------------------- */
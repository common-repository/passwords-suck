<?php
include_once("config.php");
class SwoopForWordpressSettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;
    private $swoop_page_slug = 'swoop-for-wordpress';

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Swoop For Wordpress',
            'Swoop',
            'manage_options',
            $this->swoop_page_slug,
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( SWOOP_OPTIONS_KEY );
        ?>
        <div class="wrap">
            <h1>Swoop For Wordpress</h1>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'my_option_group' );
                do_settings_sections( $this->swoop_page_slug );
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {
        register_setting(
            'my_option_group', // Option group
            SWOOP_OPTIONS_KEY, // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_swoop', // ID
            'Swoop Website Settings', // Title
            array( $this, 'print_section_info' ), // Callback
            $this->swoop_page_slug // Page
        );

        add_settings_field(
            SWOOP_WEBSITE_ID_KEY,
            'Swoop Website ID',
            array( $this, SWOOP_WEBSITE_ID_KEY . '_callback' ),
            $this->swoop_page_slug,
            'setting_section_swoop'
        );

        add_settings_field(
            SWOOP_API_KEY_KEY, // ID
            'Swoop Website Password', // Title
            array( $this, SWOOP_API_KEY_KEY . '_callback' ), // Callback
            $this->swoop_page_slug, // Page
            'setting_section_swoop' // Section
        );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input[SWOOP_API_KEY_KEY] ) )
            $new_input[SWOOP_API_KEY_KEY] = sanitize_text_field( $input[SWOOP_API_KEY_KEY] );

        if( isset( $input[SWOOP_WEBSITE_ID_KEY] ) )
            $new_input[SWOOP_WEBSITE_ID_KEY] = sanitize_text_field( $input[SWOOP_WEBSITE_ID_KEY] );

        return $new_input;
    }

    /**
     * Print the Section text
     */
    public function print_section_info()
    {
        print '
          <ol>
            <li>Navigate to <a href="https://swoopnow.com/" target="_BLANK">swoopnow.com</a> and signup for an account.</li>
            <li>Click \'Websites\' and the click \'Add New Project\'</li>
            <li>Give you project a name</li>
            <li>Copy the \'Website id\' and paste below</li>
            <li>Copy the \'Website Password\' and paste below</li>
            <li>In Swoop Admin, set your \'Login Endpoint\' to <strong>'.site_url(  "wp-json/" . SWOOP_PLUGIN_NAMESPACE . "/" . SWOOP_PLUGIN_CALLBACK ).'</strong></li>
            <li>You will now be able to login  to Wordpress without a password using Swoop</li>
          </ol>
        ';
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function swoop_api_key_callback()
    {
        printf(
            '<input type="text" id="'.SWOOP_API_KEY_KEY.'" name="'.SWOOP_OPTIONS_KEY.'['.SWOOP_API_KEY_KEY.']" value="%s" />',
            isset( $this->options[SWOOP_API_KEY_KEY] ) ? esc_attr( $this->options[SWOOP_API_KEY_KEY]) : ''
        );
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function swoop_website_id_callback()
    {
        printf(
            '<input type="text" id="'.SWOOP_WEBSITE_ID_KEY.'" name="'.SWOOP_OPTIONS_KEY.'['.SWOOP_WEBSITE_ID_KEY.']" value="%s" />',
            isset( $this->options[SWOOP_WEBSITE_ID_KEY] ) ? esc_attr( $this->options[SWOOP_WEBSITE_ID_KEY]) : ''
        );
    }
}

if( is_admin() )
    $swoop_for_wordress_settings_ = new SwoopForWordpressSettingsPage();
?>

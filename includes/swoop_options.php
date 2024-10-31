<?php
include_once("config.php");
class SwoopOptions
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;
    private $swoop_page_slug = SWOOP_PLUGIN_SLUG;

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
            SWOOP_OPTIONS_MENU_TITLE,
            SWOOP_OPTIONS_MENU_NAME,
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
            <h1>Swoop: Password-Free Authentication</h1>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( SWOOP_OPTIONS_GROUP );
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
            SWOOP_OPTIONS_GROUP, // Option group
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
            SWOOP_CLIENT_ID_KEY, // ID
            'Swoop CLIENT ID', // Title
            array( $this, SWOOP_CLIENT_ID_KEY . '_callback' ), // Callback
            $this->swoop_page_slug, // Page
            'setting_section_swoop' // Section
        );

        add_settings_field(
            SWOOP_CLIENT_SECRET_KEY,
            'Swoop CLIENT SECRET',
            array( $this, SWOOP_CLIENT_SECRET_KEY . '_callback' ),
            $this->swoop_page_slug,
            'setting_section_swoop'
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
        if( isset( $input[SWOOP_CLIENT_ID_KEY] ) )
            $new_input[SWOOP_CLIENT_ID_KEY] = sanitize_text_field( $input[SWOOP_CLIENT_ID_KEY] );

        if( isset( $input[SWOOP_CLIENT_SECRET_KEY] ) )
            $new_input[SWOOP_CLIENT_SECRET_KEY] = sanitize_text_field( $input[SWOOP_CLIENT_SECRET_KEY] );

        return $new_input;
    }

    /**
     * Print the Section text
     */
    public function print_section_info()
    {
        print '
          <ol>
            <li>Navigate to <a href="https://dashboard.swoop.email" target="_BLANK">dashboard.swoop.email</a> and signup for an account.</li>
            <li>Click \'Websites\' and the click \'Add Property\'</li>
            <li>Give you property a name</li>
            <li>Your <strong>REDIRECT URL</strong> is '.site_url(  "wp-json/" . SWOOP_PLUGIN_NAMESPACE . "/" . SWOOP_PLUGIN_CALLBACK ).'</li>
            <li>Your <strong>HOMEPAGE URL</strong> is '.site_url().'</li>
            <li>Click \'Submit\'</li>
            <li>Copy the value of <strong>CLIENT ID</strong> and paste it below</li>
            <li>Copy the value of <strong>CLIENT SECRET</strong> and paste it below</li>
            <li>You will now be able to login  to Wordpress without a password using Swoop</li>
          </ol>
        ';
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function swoop_client_id_callback()
    {
        printf(
            '<input type="text" id="'.SWOOP_CLIENT_ID_KEY.'" name="'.SWOOP_OPTIONS_KEY.'['.SWOOP_CLIENT_ID_KEY.']" value="%s" />',
            isset( $this->options[SWOOP_CLIENT_ID_KEY] ) ? esc_attr( $this->options[SWOOP_CLIENT_ID_KEY]) : ''
        );
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function swoop_client_secret_callback()
    {
        printf(
            '<input type="text" id="'.SWOOP_CLIENT_SECRET_KEY.'" name="'.SWOOP_OPTIONS_KEY.'['.SWOOP_CLIENT_SECRET_KEY.']" value="%s" />',
            isset( $this->options[SWOOP_CLIENT_SECRET_KEY] ) ? esc_attr( $this->options[SWOOP_CLIENT_SECRET_KEY]) : ''
        );
    }
}

if( is_admin() )
    $swoop_for_wordress_settings_ = new SwoopOptions();
?>

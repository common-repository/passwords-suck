<?php
include_once("config.php");

class SwoopCore {

  private $options;

  public function __construct($file) {

    $this->options = get_option( SWOOP_OPTIONS_KEY );
    register_uninstall_hook($file, array('SwoopCore', 'uninstall'));

    add_action( 'rest_api_init', function () {
      register_rest_route(SWOOP_PLUGIN_NAMESPACE , SWOOP_PLUGIN_CALLBACK , array(
        'methods' => 'GET',
        'callback' => array('SwoopCore','swoop_callback'),
        'args' => array('code')
      ) );
    } );

    // Admin Actions
    add_action( 'login_form', array($this, 'add_swoop_login_button') );
    add_action( 'register_form', array($this, 'add_swoop_signup_button'));

    // Filters.
    add_filter( 'allowed_http_origins', array($this, 'add_swoop_to_origins') );

    $this->remove_login_form();
  }

  static function swoop_callback( $data ) {
    $options = get_option( SWOOP_OPTIONS_KEY );
    $client_id = $options[SWOOP_CLIENT_ID_KEY];
    $client_secret = $options[SWOOP_CLIENT_SECRET_KEY];
    $response = wp_remote_post( SWOOP_URL . SWOOP_TOKEN_ENDPOINT,array(
      'method' => 'POST',
      'timeout' => 45,
      'redirection' => 5,
      'httpversion' => '1.0',
      'blocking' => true,
      'headers' => array(),
      'body' => array(
          'code' => $data['code'],
          'client_id' => $client_id,
          'client_secret' => $client_secret,
          'redirect_uri' => site_url(  "wp-json/" . SWOOP_PLUGIN_NAMESPACE . "/" . SWOOP_PLUGIN_CALLBACK )
      ),
      'cookies' => array()
    ));

    if ( is_wp_error( $response ) ) {
       $error_message = $response->get_error_message();
       echo "Something went wrong: $error_message";
       exit(0);
    } else {
      if($response['response']['code'] == 401) {
        echo '<pre>';
        print_r(json_decode($response['body']));
        echo '</pre>';
        exit(0);
      }
    }

    $body = json_decode($response['body']);
    $id_token = $body->{'id_token'};
    $decoded = json_decode(base64_decode(str_replace('_', '/', str_replace('-','+',explode('.', $id_token)[1]))));
    $email_address = $decoded->{'email'};
    $user = get_user_by('email', $email_address);
    if ($user) {
      try {
        $user_id = $user->ID;
        wp_set_auth_cookie($user_id);
      } catch (Exception $e) {
        error_log('exception');
      }
    } else {
      try {
        if (get_option('users_can_register')) {
          $random_password = wp_generate_password();
          $user_id = wp_create_user($email_address, $random_password, $email_address);
          wp_set_auth_cookie($user_id);
        } else {
          //  TODO: Do something if users cant register
          // Actually it's not super important
        }
      } catch (Exception $e) {
        error_log('exception');
      }
    }

    wp_redirect(admin_url());
    exit;
  }

  public function add_swoop_login_button() {
    if (isset($_GET['reauth'])) {
      echo '<div>Unable to log in. Please ensure that you have a valid account on this website or that users are allowed to register.</div><br>';
    }

    echo '<a href="'.SWOOP_URL.SWOOP_AUTH_ENDPOINT.
    '?client_id='.$this->options[SWOOP_CLIENT_ID_KEY].
    '&redirect_uri='.site_url(  "wp-json/" . SWOOP_PLUGIN_NAMESPACE . "/" . SWOOP_PLUGIN_CALLBACK ).
    '&scope=email'.
    '&response_type=code">'.
    '<img id=\'swoop_button\' style=\'display: block; max-width: 100%; margin: 0px auto 15px;\' src=\''.plugins_url(SWOOP_PLUGIN_SLUG . '/assets/button-swoop.svg' ).'\' alt=\'Swoop button\' >
    </a>';
  }

  public function add_swoop_signup_button() {
    echo '<a href="'.SWOOP_URL.SWOOP_AUTH_ENDPOINT.
    '?client_id='.$this->options[SWOOP_CLIENT_ID_KEY].
    '&redirect_uri='.site_url(  "wp-json/" . SWOOP_PLUGIN_NAMESPACE . "/" . SWOOP_PLUGIN_CALLBACK ).
    '&scope=email'.
    '&response_type=code">'.
    '<img id=\'swoop_button\' style=\'display: block; max-width: 100%; margin: 0px auto 15px;\' src=\''.plugins_url(SWOOP_PLUGIN_SLUG . '/assets/button-swoop.svg' ).'\' alt=\'Swoop button\' >
    </a>';
  }

  public function add_swoop_to_origins( $origins ) {
    $origins[] = 'https://auth.swoop.email';
    return $origins;
  }

  public static function uninstall() {
    delete_option(SWOOP_OPTIONS_KEY);
  }

  function enqueue_swoop_js($hook) {
    wp_enqueue_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js', array(), null, true);
    wp_enqueue_script('swoop_js', plugin_dir_url(__FILE__) . 'js/swoop.js',10);
  }

  // Remove Login Form
  public function remove_login_form() {
	  add_action('login_enqueue_scripts', array($this,'enqueue_swoop_js'),10);
  }
}

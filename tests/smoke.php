<?php
/** Lightweight WordPress stubs for PHP/SMTP/API regression checks. */
define( 'ABSPATH', __DIR__ );
$GLOBALS['fake_options'] = array();
$GLOBALS['fake_http_responses'] = array();
$GLOBALS['fake_http_requests'] = array();
function add_action() {}
function add_filter() {}
function get_option( $name, $default = false ) { return array_key_exists( $name, $GLOBALS['fake_options'] ) ? $GLOBALS['fake_options'][ $name ] : $default; }
function wp_parse_args( $args, $defaults ) { return array_merge( $defaults, $args ); }
function current_user_can() { return true; }
function wp_unslash( $x ) { return $x; }
function sanitize_text_field( $x ) { return is_string( $x ) ? trim( strip_tags( $x ) ) : ''; }
function sanitize_email( $x ) { return is_string( $x ) ? filter_var( $x, FILTER_SANITIZE_EMAIL ) : ''; }
function is_email( $x ) { return (bool) filter_var( $x, FILTER_VALIDATE_EMAIL ); }
function absint( $x ) { return abs( (int) $x ); }
function __( $text ) { return $text; }
function is_wp_error( $x ) { return $x instanceof WP_Error; }
class WP_Error {
    private $code;
    private $message;
    private $data;
    public function __construct( $code, $message, $data = null ) { $this->code = $code; $this->message = $message; $this->data = $data; }
    public function get_error_code() { return $this->code; }
    public function get_error_message() { return $this->message; }
}
function wp_remote_get( $url, $args ) {
    $GLOBALS['fake_http_requests'][] = array( 'url' => $url, 'args' => $args );
    return array_shift( $GLOBALS['fake_http_responses'] );
}
function wp_remote_retrieve_response_code( $r ) { return $r['response']['code']; }
function wp_remote_retrieve_body( $r ) { return $r['body']; }
class FakeMailer {
    public $Host = '';
    public $Port = 0;
    public $SMTPAuth = false;
    public $Username = '';
    public $Password = '';
    public $SMTPSecure = '';
    public $SMTPAutoTLS = false;
    public $smtp = false;
    public function isSMTP() { $this->smtp = true; }
}
function check( $condition, $message ) {
    if ( ! $condition ) {
        fwrite( STDERR, "FAIL: $message\n" );
        exit( 1 );
    }
    echo "OK: $message\n";
}
function response( $code, $body = '' ) { return array( 'response' => array( 'code' => $code ), 'body' => $body ); }
require dirname( __DIR__ ) . '/mailtrap-for-wp.php';
$plugin = new MailtrapPlugin();

// Existing v0.7 credentials should remain sandbox-only until re-saved.
$GLOBALS['fake_options'] = array(
    'mailtrap_enabled' => '1', 'mailtrap_port' => '2525',
    'mailtrap_username' => 'old-user', 'mailtrap_password' => 'old-pass',
    'mailtrap_api_token' => 'old-token',
);
$settings = MailtrapPlugin::settings();
check( 'sandbox' === $settings['mode'] && '1' === $settings['enabled'], 'Legacy settings stay enabled in sandbox mode' );
check( 'old-pass' === $settings['sandbox_password'] && '' === $settings['live_password'], 'Legacy password does not become production password' );
$mailer = new FakeMailer();
$plugin->mailer_setup( $mailer );
check( $mailer->smtp && 'sandbox.smtp.mailtrap.io' === $mailer->Host && 2525 === $mailer->Port, 'Old install uses current Sandbox host' );
check( 'tls' === $mailer->SMTPSecure && 'old-user' === $mailer->Username, 'Sandbox credentials and STARTTLS configured' );

// Secret fields remain unchanged when blank, and an explicit clear removes a secret.
$updated = $plugin->sanitize_settings( array(
    'enabled' => '1', 'mode' => 'transactional',
    'sandbox_username' => 'old-user', 'sandbox_password' => '',
    'live_username' => 'api', 'live_password' => 'new-live-key', 'live_port' => '465',
    'sandbox_api_token' => '', 'from_email' => 'hello@example.com', 'from_name' => 'Test Store',
) );
check( 'old-pass' === $updated['sandbox_password'], 'Blank password update preserves old password' );
check( 'old-token' === $updated['sandbox_api_token'], 'Blank token update preserves old token' );
$GLOBALS['fake_options']['mailtrap_wp_config'] = $updated;
$mailer = new FakeMailer();
$plugin->mailer_setup( $mailer );
check( 'live.smtp.mailtrap.io' === $mailer->Host && 'new-live-key' === $mailer->Password, 'Transactional credentials are separate' );
check( 'ssl' === $mailer->SMTPSecure && 465 === $mailer->Port, 'Port 465 uses implicit TLS' );
check( 'hello@example.com' === $plugin->filter_mail_from( 'admin@another.example' ), 'Configured From address used' );
check( 'Test Store' === $plugin->filter_mail_from_name( 'WordPress' ), 'Configured From name used' );

$updated = $plugin->sanitize_settings( array( 'mode' => 'sandbox', 'clear_sandbox_api_token' => '1', 'sandbox_username' => 'old-user' ) );
check( '' === $updated['sandbox_api_token'], 'Clear API token is supported' );
check( '0' === $updated['enabled'], 'Unchecked enable checkbox turns SMTP off' );

// Browser uses current Sandbox paths and Bearer authentication; HTML->TXT fallback is bounded.
$GLOBALS['fake_options']['mailtrap_wp_config'] = $settings;
$GLOBALS['fake_http_responses'] = array( response( 200, '[{"id":123,"name":"QA"}]' ) );
$items = MailtrapSandboxAPI::sandboxes();
check( 123 === $items[0]['id'], 'Sandbox response parsed' );
check( 'https://mailtrap.io/api/sandboxes' === $GLOBALS['fake_http_requests'][0]['url'], 'Current Sandbox list API used' );
check( 'Bearer old-token' === $GLOBALS['fake_http_requests'][0]['args']['headers']['Authorization'], 'Bearer token header used' );
$GLOBALS['fake_http_responses'] = array( response( 404 ), response( 200, 'Plain text fallback' ) );
$body = MailtrapSandboxAPI::message_body( 123, 456 );
check( 'Plain text fallback' === $body, '404 HTML body falls back once to text' );
check( strpos( $GLOBALS['fake_http_requests'][2]['url'], '/api/sandboxes/123/messages/456/body.txt' ) !== false, 'Uses current body.txt endpoint' );
$GLOBALS['fake_http_responses'] = array( response( 500, '{}' ) );
check( is_wp_error( MailtrapSandboxAPI::messages( 123 ) ), 'Server errors are WP_Error rather than leaked markup' );
echo "All smoke checks passed.\n";

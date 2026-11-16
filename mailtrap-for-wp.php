<?php
/**
 * Plugin Name: Mailtrap for WordPress
 * Plugin URI: https://github.com/eduardo-marcolino/mailtrap-for-wordpress
 * Description: Send WordPress email through Mailtrap or capture it safely in Email Sandbox.
 * Version: 0.8.0
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * Author: Eduardo Marcolino
 * License: GPLv2 or later
 * Text Domain: mailtrap-for-wp
 * Domain Path: /languages
 * GitHub Plugin URI: https://github.com/eduardo-marcolino/mailtrap-for-wordpress
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * The existing, third-party plugin. Not an official Mailtrap product.
 * Maintains the old mailtrap_* SMTP options as a Sandbox-only migration path.
 */
final class MailtrapPlugin {
    const OPTION = 'mailtrap_wp_config';
    const TEXT_DOMAIN = 'mailtrap-for-wp';

    private $last_mail_error = '';

    public static function init() {
        $plugin = new self();
        add_action( 'phpmailer_init', array( $plugin, 'mailer_setup' ) );
        add_action( 'admin_menu', array( $plugin, 'menu_setup' ) );
        add_action( 'admin_init', array( $plugin, 'register_settings' ) );
        add_action( 'wp_mail_failed', array( $plugin, 'record_mail_failure' ) );
        add_filter( 'wp_mail_from', array( $plugin, 'filter_mail_from' ) );
        add_filter( 'wp_mail_from_name', array( $plugin, 'filter_mail_from_name' ) );
        add_action( 'init', function () {
            load_plugin_textdomain( self::TEXT_DOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
        } );
    }

    public static function settings() {
        $stored = get_option( self::OPTION, false );
        $defaults = array(
            'enabled'          => '0',
            'mode'             => 'sandbox',
            'sandbox_username' => '',
            'sandbox_password' => '',
            'sandbox_port'     => '2525',
            'live_username'    => 'api',
            'live_password'    => '',
            'live_port'        => '587',
            'from_email'       => '',
            'from_name'        => '',
            'sandbox_api_token'=> '',
        );

        // Existing installations remain in testing mode after upgrading.
        if ( false === $stored ) {
            $defaults['enabled']           = get_option( 'mailtrap_enabled', false ) ? '1' : '0';
            $defaults['sandbox_username']  = (string) get_option( 'mailtrap_username', '' );
            $defaults['sandbox_password']  = (string) get_option( 'mailtrap_password', '' );
            $defaults['sandbox_api_token'] = (string) get_option( 'mailtrap_api_token', '' );
            $old_port = (string) get_option( 'mailtrap_port', '2525' );
            if ( in_array( $old_port, array( '25', '465', '587', '2525' ), true ) ) {
                $defaults['sandbox_port'] = $old_port;
            }
            return $defaults;
        }

        return wp_parse_args( is_array( $stored ) ? $stored : array(), $defaults );
    }

    public static function credential( $key, $constant = '' ) {
        if ( $constant && defined( $constant ) ) {
            return (string) constant( $constant );
        }
        $config = self::settings();
        return isset( $config[ $key ] ) ? (string) $config[ $key ] : '';
    }

    public function register_settings() {
        register_setting( 'mailtrap-wp-settings', self::OPTION, array(
            'type'              => 'array',
            'sanitize_callback' => array( $this, 'sanitize_settings' ),
        ) );
    }

    public function sanitize_settings( $input ) {
        if ( ! current_user_can( 'manage_options' ) || ! is_array( $input ) ) {
            return self::settings();
        }

        $input = wp_unslash( $input );
        $previous = self::settings();
        $result = $previous;
        $result['enabled'] = ! empty( $input['enabled'] ) ? '1' : '0';
        $result['mode'] = isset( $input['mode'] ) && 'transactional' === $input['mode'] ? 'transactional' : 'sandbox';

        foreach ( array( 'sandbox_username', 'live_username' ) as $name ) {
            $result[ $name ] = isset( $input[ $name ] ) ? sanitize_text_field( $input[ $name ] ) : $previous[ $name ];
        }
        foreach ( array( 'sandbox_port', 'live_port' ) as $name ) {
            $requested = isset( $input[ $name ] ) ? (string) $input[ $name ] : '';
            if ( in_array( $requested, array( '25', '465', '587', '2525' ), true ) ) {
                $result[ $name ] = $requested;
            }
        }
        $result['from_email'] = isset( $input['from_email'] ) ? sanitize_email( $input['from_email'] ) : '';
        $result['from_name'] = isset( $input['from_name'] ) ? sanitize_text_field( $input['from_name'] ) : '';

        // Empty secret fields mean 'keep existing'; never echo saved secrets in HTML.
        foreach ( array( 'sandbox_password', 'live_password', 'sandbox_api_token' ) as $name ) {
            if ( ! empty( $input[ 'clear_' . $name ] ) ) {
                $result[ $name ] = '';
            } elseif ( isset( $input[ $name ] ) && '' !== $input[ $name ] ) {
                $result[ $name ] = sanitize_text_field( $input[ $name ] );
            }
        }
        return $result;
    }

    public function menu_setup() {
        add_options_page( 'Mailtrap for WordPress', 'Mailtrap', 'manage_options', 'mailtrap-settings', array( $this, 'settings_page' ) );
        add_submenu_page( null, 'Mailtrap Test', 'Mailtrap Test', 'manage_options', 'mailtrap-test', array( $this, 'test_page' ) );
        add_submenu_page( null, 'Mailtrap Sandbox', 'Mailtrap Sandbox', 'manage_options', 'mailtrap-inbox', array( $this, 'inbox_page' ) );
    }

    public function settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You cannot manage these settings.', self::TEXT_DOMAIN ) );
        }
        $config = self::settings();
        include plugin_dir_path( __FILE__ ) . 'includes/settings.php';
    }

    public function record_mail_failure( $error ) {
        if ( is_wp_error( $error ) ) {
            $this->last_mail_error = $error->get_error_message();
        }
    }

    public function test_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Access denied.', self::TEXT_DOMAIN ) );
        }
        $config = self::settings();
        $email_sent = null;
        $error_message = '';
        if ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
            check_admin_referer( 'mailtrap_test_action' );
            $to = isset( $_POST['to'] ) && is_string( $_POST['to'] ) ? sanitize_email( wp_unslash( $_POST['to'] ) ) : '';
            $message = isset( $_POST['message'] ) && is_string( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
            if ( '1' !== $config['enabled'] ) {
                $error_message = __( 'Enable Mailtrap before testing.', self::TEXT_DOMAIN );
            } elseif ( ! is_email( $to ) ) {
                $error_message = __( 'Enter a valid recipient email.', self::TEXT_DOMAIN );
            } else {
                $email_sent = wp_mail( $to, 'Mailtrap for WordPress test', $message );
                if ( ! $email_sent ) {
                    $error_message = $this->last_mail_error ?: __( 'WordPress could not submit the email.', self::TEXT_DOMAIN );
                }
            }
        }
        include plugin_dir_path( __FILE__ ) . 'includes/test.php';
    }

    public function inbox_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Access denied.', self::TEXT_DOMAIN ) );
        }
        $config = self::settings();
        include plugin_dir_path( __FILE__ ) . 'includes/inbox.php';
    }

    public function filter_mail_from( $value ) {
        $config = self::settings();
        return '1' === $config['enabled'] && is_email( $config['from_email'] ) ? $config['from_email'] : $value;
    }

    public function filter_mail_from_name( $value ) {
        $config = self::settings();
        return '1' === $config['enabled'] && '' !== $config['from_name'] ? $config['from_name'] : $value;
    }

    public function mailer_setup( $phpmailer ) {
        $config = self::settings();
        if ( '1' !== $config['enabled'] ) {
            return;
        }
        $is_live = 'transactional' === $config['mode'];
        $username = $is_live ? $config['live_username'] : $config['sandbox_username'];
        $password = $is_live
            ? self::credential( 'live_password', 'MAILTRAP_WP_LIVE_PASSWORD' )
            : self::credential( 'sandbox_password', 'MAILTRAP_WP_SANDBOX_PASSWORD' );
        if ( '' === $username || '' === $password ) {
            // Fail closed: never fall back to PHP mail() if SMTP is enabled but incomplete.
            $phpmailer->isSMTP();
            $phpmailer->Host = '127.0.0.1';
            $phpmailer->Port = 1;
            $phpmailer->SMTPAuth = false;
            return;
        }

        $port = (int) ( $is_live ? $config['live_port'] : $config['sandbox_port'] );
        $phpmailer->isSMTP();
        $phpmailer->Host       = $is_live ? 'live.smtp.mailtrap.io' : 'sandbox.smtp.mailtrap.io';
        $phpmailer->Port       = $port;
        $phpmailer->SMTPAuth   = true;
        $phpmailer->Username   = $username;
        $phpmailer->Password   = $password;
        $phpmailer->SMTPSecure = 465 === $port ? 'ssl' : 'tls';
        $phpmailer->SMTPAutoTLS = true;
    }
}

/** Read-only wrapper for the current Mailtrap Email Sandbox REST API. */
final class MailtrapSandboxAPI {
    const BASE_URL = 'https://mailtrap.io';

    private static function request( $path, $json = true ) {
        $token = MailtrapPlugin::credential( 'sandbox_api_token', 'MAILTRAP_WP_SANDBOX_API_TOKEN' );
        if ( '' === $token ) {
            return new WP_Error( 'mailtrap_token_missing', __( 'Add a Mailtrap Sandbox API token under Settings.', 'mailtrap-for-wp' ) );
        }
        $response = wp_remote_get( self::BASE_URL . $path, array(
            'headers'     => array( 'Authorization' => 'Bearer ' . $token, 'Accept' => $json ? 'application/json' : 'text/plain' ),
            'timeout'     => 15,
            'redirection' => 0,
        ) );
        if ( is_wp_error( $response ) ) {
            return $response;
        }
        $status = (int) wp_remote_retrieve_response_code( $response );
        if ( 404 === $status ) {
            return new WP_Error( 'mailtrap_not_found', __( 'Resource not found.', 'mailtrap-for-wp' ), array( 'status' => 404 ) );
        }
        if ( 200 !== $status ) {
            return new WP_Error( 'mailtrap_api_error', sprintf( __( 'Mailtrap API returned HTTP %d. Check the token permissions and account.', 'mailtrap-for-wp' ), $status ) );
        }
        $body = wp_remote_retrieve_body( $response );
        if ( ! $json ) {
            return $body;
        }
        $decoded = json_decode( $body, true );
        return JSON_ERROR_NONE === json_last_error() && is_array( $decoded )
            ? $decoded : new WP_Error( 'mailtrap_invalid_json', __( 'Mailtrap returned an invalid API response.', 'mailtrap-for-wp' ) );
    }

    public static function sandboxes() {
        return self::request( '/api/sandboxes' );
    }

    public static function messages( $sandbox_id, $page = 1 ) {
        return self::request( '/api/sandboxes/' . absint( $sandbox_id ) . '/messages?page=' . min( 1000, max( 1, absint( $page ) ) ) );
    }

    public static function message( $sandbox_id, $message_id ) {
        return self::request( '/api/sandboxes/' . absint( $sandbox_id ) . '/messages/' . absint( $message_id ) );
    }

    public static function message_body( $sandbox_id, $message_id ) {
        $base = '/api/sandboxes/' . absint( $sandbox_id ) . '/messages/' . absint( $message_id );
        $html = self::request( $base . '/body.html', false );
        // Only retry as plain text when HTML does not exist; do not recurse forever.
        if ( is_wp_error( $html ) && 'mailtrap_not_found' === $html->get_error_code() ) {
            return self::request( $base . '/body.txt', false );
        }
        return $html;
    }
}

add_action( 'plugins_loaded', array( 'MailtrapPlugin', 'init' ) );

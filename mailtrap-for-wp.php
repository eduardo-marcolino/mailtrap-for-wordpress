<?php
/*
Plugin Name: Mailtrap for WordPress
Plugin URI: http://eduardomarcolino.com/plugins/mailtrap-for-wordpress
Description: Easily configure wordpress to send emails to Mailtrap.io
Version: 0.7
Author: Eduardo Marcolino
Author URI: http://eduardomarcolino.com
Text Domain: mailtrap-for-wp
Domain Path: /languages
License: GPL v2
GitHub Plugin URI: https://github.com/eduardo-marcolino/mailtrap-for-wordpress

Mailtrap for WordPress
Copyright (C) 2015, Eduardo Marcolino, eduardo.marcolino@gmail.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

if( ! defined( 'ABSPATH' ) ) {
  exit;
}

if ( ! class_exists( 'MailtrapPlugin' ) ) :

final class MailtrapPlugin {

  public
    $plugin_url,
    $plugin_path
  ;

  public static function init()
  {
    $plugin = new MailtrapPlugin();
    $plugin->plugin_setup();
  }

  public function plugin_setup()
  {
    $this->plugin_url    = plugins_url( '/', __FILE__ );
    $this->plugin_path   = plugin_dir_path( __FILE__ );

    // Start session for debugging if not already started
    if (!session_id() && !headers_sent()) {
      session_start();
    }
    
    // Clear debug log if requested
    if (isset($_GET['clear_debug']) && $_GET['clear_debug'] == 1) {
      unset($_SESSION['mailtrap_debug_log']);
      wp_redirect(remove_query_arg('clear_debug'));
      exit;
    }
    
    // Clear error log if requested
    if (isset($_GET['clear_errors']) && $_GET['clear_errors'] == 1) {
      unset($_SESSION['mailtrap_errors']);
      wp_redirect(remove_query_arg('clear_errors'));
      exit;
    }

    add_action( 'phpmailer_init', array($this, 'mailer_setup' ) );
    add_action( 'admin_menu', array($this, 'menu_setup' ) );
    add_action( 'admin_init', array($this, 'register_settings') );
    add_action( 'wp_mail_failed', array($this, 'wp_mail_failed'), 99, 1 );

    add_filter( 'wp_mail_from', array($this, 'filter_mail_from') );
    add_filter( 'wp_mail_from_name', array($this, 'filter_mail_from_name') );

    load_plugin_textdomain( 'mailtrap-for-wp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
  }

  public function menu_setup() {
    add_options_page( 'Mailtrap for Wordpress', 'Mailtrap', 'manage_options', 'mailtrap-settings', array($this, 'settings_page' ) );
    add_submenu_page( null, 'Mailtrap for Wordpress', 'Mailtrap Test', 'manage_options', 'mailtrap-test', array($this, 'test_page' ));
    add_submenu_page( null, 'Mailtrap for Wordpress', 'Mailtrap Inbox', 'manage_options', 'mailtrap-inbox', array($this, 'inbox_page' ));
  }

  public function wp_mail_failed($wp_error) {
    $error_message = $wp_error->get_error_message();
    
    // Log the error to debug.log if debug is enabled
    if (defined('WP_DEBUG') && WP_DEBUG) {
      error_log('MAILTRAP EMAIL ERROR: ' . $error_message);
    }
    
    // Store in session for display
    if (!isset($_SESSION['mailtrap_errors'])) {
      $_SESSION['mailtrap_errors'] = array();
    }
    $_SESSION['mailtrap_errors'][] = $error_message;
    
    // Display error message
    echo sprintf('<div class="notice notice-error"><p>%s</p></div>',
      __( 'Email Delivery Failure:') . $error_message
    );
  }

  public function settings_page() {
    include $this->plugin_path.'/includes/settings.php';
  }

  public function filter_mail_from($value) {
    return get_option('admin_email');
  }

  public function filter_mail_from_name($value) {
    return get_option('blogname');
  }

  public function test_page()
  {
    $email_sent = null;

    if($_SERVER['REQUEST_METHOD'] == 'POST')
    {
      if (!wp_verify_nonce( $_POST['_wpnonce'], 'mailtrap_test_action' ) ) {
        die( 'Failed security check' );
      }

      $headers = array(
        'From: ' . wp_get_current_user()->display_name . ' <' . $_POST['from'] . '>'
      );
      
      $email_sent = wp_mail( $_POST['to'], __( 'Mailtrap for Wordpress Plugin', 'mailtrap-for-wp' ), $_POST['message'], $headers);
    }

    include $this->plugin_path.'/includes/test.php';
  }

  public function inbox_page() {
    include $this->plugin_path.'/includes/inbox.php';
  }

  public function register_settings()
  {
    register_setting( 'mailtrap-settings', 'mailtrap_enabled' );
    register_setting( 'mailtrap-settings', 'mailtrap_host' );
    register_setting( 'mailtrap-settings', 'mailtrap_port' );
    register_setting( 'mailtrap-settings', 'mailtrap_username' );
    register_setting( 'mailtrap-settings', 'mailtrap_password' );
    register_setting( 'mailtrap-settings', 'mailtrap_secure' );
    register_setting( 'mailtrap-settings', 'mailtrap_api_token' );
    register_setting( 'mailtrap-settings', 'mailtrap_isDebug' );
    register_setting( 'mailtrap-settings', 'mailtrap_base_url' );
  }

  public function mailer_setup($phpmailer)
  {
    if(get_option('mailtrap_enabled', false))
    {
      $phpmailer->IsSMTP();
      $phpmailer->Host = get_option('mailtrap_host', 'live.smtp.mailtrap.io');
      $phpmailer->SMTPAuth = true;
      $phpmailer->Port = get_option('mailtrap_port');
      $phpmailer->Username = get_option('mailtrap_username');
      $phpmailer->Password = get_option('mailtrap_password');
      $phpmailer->SMTPSecure = get_option('mailtrap_secure');
      $phpmailer->setFrom(get_option('admin_email'));
      
      // Enable SMTP debugging based on setting
      $phpmailer->SMTPDebug = intval(get_option('mailtrap_isDebug', 0)); // 0-4, default 1
      $phpmailer->Debugoutput = function($str, $level) {
        // Log to WordPress debug.log
        if (defined('WP_DEBUG') && WP_DEBUG) {
          error_log('MAILTRAP SMTP DEBUG: ' . $str);
        }
        // Also store in a session variable for display in admin area
        if (!isset($_SESSION['mailtrap_debug_log'])) {
          $_SESSION['mailtrap_debug_log'] = '';
        }
        $_SESSION['mailtrap_debug_log'] .= $str . "\n";
      };
    }
  }
}

class MailtrapAPIClient
{
  public static function get_base_url() {
    $url = get_option('mailtrap_base_url');
    if ($url && filter_var($url, FILTER_VALIDATE_URL)) {
      return rtrim($url, '/');
    }
    return 'https://mailtrap.io/api/v1';
  }

  public static function getInboxes()
  {
    $response = wp_remote_get( self::get_base_url().'/inboxes', [
      'headers' => [
          'Api-Token' => get_option('mailtrap_api_token')
      ]
    ]);

    if ( is_array( $response ) && ! is_wp_error( $response ) ) {
      $http_code  = $response['response']['code'];

      if ($http_code != 200) {
        throw new Exception($response['response']['message'], $http_code);
      }

      $body       = json_decode( $response['body'] );
      return $body;
    }
  }

  public static function getInboxMessages($id)
  {
    $response = wp_remote_get( self::get_base_url().'/inboxes/'.$id.'/messages', [
      'headers' => [
          'Api-Token' => get_option('mailtrap_api_token')
      ]
    ]);

    if ( is_array( $response ) && ! is_wp_error( $response ) ) {
      $http_code  = $response['response']['code'];

      if ($http_code != 200) {
        throw new Exception($response['response']['message'], $http_code);
      }

      $body       = json_decode( $response['body'] );
      return $body;
    }
  }

  public static function getMessage($inbox_id, $message_id)
  {
    $response = wp_remote_get( self::get_base_url().'/inboxes/'.$inbox_id.'/messages/'.$message_id, [
      'headers' => [
          'Api-Token' => get_option('mailtrap_api_token')
      ]
    ]);

    if ( is_array( $response ) && ! is_wp_error( $response ) ) {
      $http_code  = $response['response']['code'];

      if ($http_code != 200) {
        throw new Exception($response['response']['message'], $http_code);
      }

      $body       = json_decode( $response['body'] );
      return $body;
    }
  }

  public static function getMessageBody($inbox_id, $message_id, $format = 'html')
  {
    $response = wp_remote_get( self::get_base_url().'/inboxes/'.$inbox_id.'/messages/'.$message_id.'/body.'.$format, [
      'headers' => [
          'Api-Token' => get_option('mailtrap_api_token')
      ]
    ]);

    if ( is_array( $response ) && ! is_wp_error( $response ) ) {
      $http_code  = $response['response']['code'];

      if ($http_code != 200 && $http_code != 404) {
        throw new Exception($response['response']['message'], $http_code);
      }

      if ($http_code == 404) {
        return self::getMessageBody($inbox_id, $message_id, 'txt');
      }
      return $response['body'];
    }
  }

  public static function time2str($ts)
  {
      if(!ctype_digit($ts))
          $ts = strtotime($ts);

      $diff = time() - $ts;
      if($diff == 0)
          return 'now';
      elseif($diff > 0)
      {
          $day_diff = floor($diff / 86400);
          if($day_diff == 0)
          {
              if($diff < 60) return 'just now';
              if($diff < 120) return '1 minute ago';
              if($diff < 3600) return floor($diff / 60) . ' minutes ago';
              if($diff < 7200) return '1 hour ago';
              if($diff < 86400) return floor($diff / 3600) . ' hours ago';
          }
          if($day_diff == 1) return 'Yesterday';
          if($day_diff < 7) return $day_diff . ' days ago';
          if($day_diff < 31) return ceil($day_diff / 7) . ' weeks ago';
          if($day_diff < 60) return 'last month';
          return date('F Y', $ts);
      }
      else
      {
          $diff = abs($diff);
          $day_diff = floor($diff / 86400);
          if($day_diff == 0)
          {
              if($diff < 120) return 'in a minute';
              if($diff < 3600) return 'in ' . floor($diff / 60) . ' minutes';
              if($diff < 7200) return 'in an hour';
              if($diff < 86400) return 'in ' . floor($diff / 3600) . ' hours';
          }
          if($day_diff == 1) return 'Tomorrow';
          if($day_diff < 4) return date('l', $ts);
          if($day_diff < 7 + (7 - date('w'))) return 'next week';
          if(ceil($day_diff / 7) < 4) return 'in ' . ceil($day_diff / 7) . ' weeks';
          if(date('n', $ts) == date('n') + 1) return 'next month';
          return date('F Y', $ts);
      }
  }
}

add_action( 'plugins_loaded', array( 'MailtrapPlugin', 'init' ) );

endif;

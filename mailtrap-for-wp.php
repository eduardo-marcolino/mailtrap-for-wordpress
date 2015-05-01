<?php
/*
Plugin Name: Mailtrap for WordPress
Plugin URI: http://eduardomarcolino.com/plugins/mailtrap-for-wordpress
Description: 
Version: 0.0.1
Author: Eduardo Marcolino
Author URI: http://eduardomarcolino.com
Text Domain: mailtraṕ-for-wp
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

if ( ! class_exists( 'Mailtrap' ) ) :
  
class Mailtrap {
  
  public $version = '0.0.1';
  
  /**
   * Instantiate Mailtrap class
   * @return void
   */
  public static function init() 
  {
    $plugin = new Mailtrap();
    $plugin->plugin_setup(); 
  }
  
  /**
   * Setting up actions and filters
   * @return void
   */
  public function plugin_setup() 
  {
    $this->plugin_url    = plugins_url( '/', __FILE__ );
    $this->plugin_path   = plugin_dir_path( __FILE__ );
    
    add_action( 'phpmailer_init', array($this, 'mailer_setup' ) );
  }
  
  
  public function mailer_setup(PHPMailer $phpmailer) 
  {
    $phpmailer->IsSMTP();
    $phpmailer->Host = '';
    $phpmailer->Port = '';
    $phpmailer->SMTPAuth = '';
    $phpmailer->Username = '';
    $phpmailer->Password = '';
  }
}

add_action( 'plugins_loaded', array( 'Mailtrap', 'init' ) );

endif;
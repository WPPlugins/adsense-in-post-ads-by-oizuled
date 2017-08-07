<?php
   /*
   Plugin Name: AdSense In-Post Ads
   Plugin URI: https://wpinpostads.com
   Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6UHZNUWTHW9W2
   Description: A plugin to display a shortcode to insert your Google AdSense ads inside your posts.
   Version: 1.1.1
   Author: Scott DeLuzio
   Author URI: https://scottdeluzio.com
   License: GPL2
   Text Domain: adsense-inpost-ads
   */
   
	/*  Copyright 2016  Scott DeLuzio  (email : me (at) scottdeluzio.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
	*/
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}
/* Load Text Domain */
add_action('plugins_loaded', 'adsense_inpost_ads_plugin_init');
function adsense_inpost_ads_plugin_init() {
  load_plugin_textdomain( 'adsense-inpost-ads', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
}

/*
 * Includes for Adsense Inpost Ads
 */
if ( ! defined( 'ADSENSE_INPOST_ADS' ) ) {
  define( 'ADSENSE_INPOST_ADS', __FILE__ );
}
if( ! defined( 'ADSENSE_INPOST_ADS_PLUGIN_DIR' ) ) {
  define( 'ADSENSE_INPOST_ADS_PLUGIN_DIR', dirname( __FILE__ ) );
}
if( ! defined( 'ADSENSE_INPOST_ADS_PLUGIN_URL' ) ) {
  define( 'ADSENSE_INPOST_ADS_PLUGIN_URL', plugins_url( '', __FILE__ ) );
}

$aip_options = get_option( 'aip_settings' );

include( ADSENSE_INPOST_ADS_PLUGIN_DIR . '/includes/admin-settings-page.php' );
include( ADSENSE_INPOST_ADS_PLUGIN_DIR . '/includes/shortcodes.php' );
include( ADSENSE_INPOST_ADS_PLUGIN_DIR . '/includes/updater.php' );

if ( ! class_exists( 'INPOST_ADS_UPGRADE_NOTICE' ) ) :
class INPOST_ADS_UPGRADE_NOTICE {
  private static $instance;
  public static function instance() {
    if ( ! isset( self::$instance ) && ! ( self::$instance instanceof INPOST_ADS_UPGRADE_NOTICE ) ) {
      
      self::$instance = new INPOST_ADS_UPGRADE_NOTICE;
      self::$instance->constants();
      self::$instance->hooks();
    }
    return self::$instance;
  }
  /**
   *  Define plugin constants
   */
  public function constants() {
    if ( ! defined( 'ADSENSE_INPOST_ADS_PLUGIN_DIR' ) ) {
      define( 'ADSENSE_INPOST_ADS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
    }
    if ( ! defined( 'ADSENSE_INPOST_ADS_PLUGIN_URL' ) ) {
      define( 'ADSENSE_INPOST_ADS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
    }
    if ( ! defined( 'ADSENSE_INPOST_ADS' ) ) {
      define( 'ADSENSE_INPOST_ADS', __FILE__ );
    }
    if ( ! defined( 'ADSENSE_INPOST_ADS_PLUGIN_BASENAME' ) ) {
      define( 'ADSENSE_INPOST_ADS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
    }
    if ( ! defined( 'ADSENSE_INPOST_ADS_VERSION' ) ) {
      define( 'ADSENSE_INPOST_ADS_VERSION', '1.1.1' );
    }
  }
  /**
   *  Kick everything off
   */
  public function hooks() {
    add_action( 'admin_enqueue_scripts', array( $this, 'enqueues' ) );
    add_action( 'wp_enqueue_scripts', array( $this, 'enqueues' ) );
    add_action( 'admin_notices', array( $this, 'admin_notice' ) );
    // Will run for users that are logged in (wp_ajax_nopriv_{action} will run for users that are not logged in)
    add_action( 'wp_ajax_adsense_inpost_ads_admin_notice', array( $this, 'dismiss_admin_notice' ) );
  }
  /**
   *  Enqueue the assets in the admin
   */
  public function enqueues() {
    // Add the admin JS if the notice has not been dismissed
    if ( is_admin() && get_user_meta( get_current_user_id(), 'adsense_inpost_ads_admin_notice', true ) !== 'dismissed' ) {
      
      // Adds our JS file to the queue that WordPress will load
      wp_enqueue_script( 'adsense_inpost_ads_admin_script', ADSENSE_INPOST_ADS_PLUGIN_URL . '/includes/js/dismiss-notice.js', array( 'jquery' ), ADSENSE_INPOST_ADS_VERSION, true );
      // Make some data available to our JS file
      wp_localize_script( 'adsense_inpost_ads_admin_script', 'adsense_inpost_ads_admin', array(
        'adsense_inpost_ads_admin_nonce' => wp_create_nonce( 'adsense_inpost_ads_admin_nonce' ),
      ));
    }    
  }
  /**
   *  Add our admin notice if the user has not previously dismissed it.
   */
  public function admin_notice() { ?>

    <?php
    // Bail if the user has previously dismissed the notice (doesn't show the notice)
    if ( get_user_meta( get_current_user_id(), 'adsense_inpost_ads_admin_notice', true ) === 'dismissed' ) {
      return;
    }
    ?>

    <div id="adsense-admin-upgrade-notice" class="notice is-dismissible update-nag">
      <?php 
      $url = 'https://wpinpostads.com';
      $message = sprintf( wp_kses( __( 'Update to <a href="%s">Inpost Ads Pro</a> to automatically insert ads into your posts! Never manually insert an ad again!', 'adsense-inpost-ads' ), array(  'a' => array( 'href' => array() ) ) ), esc_url( $url ) );
      echo $message; ?>
    </div>

  <?php }
  /**
   *  Process the AJAX request on the server and send a response back to the JS.
   *  If nonce is valid, update the current user's meta to prevent notice from displaying.
   */
  public function dismiss_admin_notice() {
    // Verify the security nonce and die if it fails
    if ( ! isset( $_POST['adsense_inpost_ads_admin_nonce'] ) || ! wp_verify_nonce( $_POST['adsense_inpost_ads_admin_nonce'], 'adsense_inpost_ads_admin_nonce' ) ) {
      wp_die( __( 'Your request failed permission check.', 'adsense-inpost-ads' ) );
    }
    // Store the user's dimissal so that the notice doesn't show again
    update_user_meta( get_current_user_id(), 'adsense_inpost_ads_admin_notice', 'dismissed' );
    // Send success message
    wp_send_json( array(
      'status' => 'success',
      'message' => __( 'Your request was processed. See ya!', 'adsense-inpost-ads' )
    ) );
  }
}
endif;
/**
 *  Main function
 *  @return object INPOST_ADS_UPGRADE_NOTICE instance
 */
function INPOST_ADS_UPGRADE_NOTICE() {
  return INPOST_ADS_UPGRADE_NOTICE::instance();
}
INPOST_ADS_UPGRADE_NOTICE();
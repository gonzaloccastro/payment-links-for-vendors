<?php
/**
 * Plugin Name:       Payment Links for Vendors
 * Plugin URI:        https://github.com/gonzaloccastro
 * Description:       Allows vendors to generate WooCommerce payment links for customers.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Gonzalo Castro
 * Author URI:        https://github.com/gonzaloccastro
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       plfv
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'PLFV_VERSION',    '1.0.0' );
define( 'PLFV_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PLFV_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once PLFV_PLUGIN_DIR . 'includes/class-role-manager.php';
require_once PLFV_PLUGIN_DIR . 'includes/class-product-creator.php';
require_once PLFV_PLUGIN_DIR . 'includes/class-vendor-dashboard.php';
require_once PLFV_PLUGIN_DIR . 'includes/class-settings.php';

register_activation_hook( __FILE__, [ 'PLFV_Role_Manager', 'add_vendor_role' ] );
register_deactivation_hook( __FILE__, [ 'PLFV_Role_Manager', 'remove_vendor_role' ] );

add_action( 'plugins_loaded', 'plfv_init' );

function plfv_init() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        add_action( 'admin_notices', function() {
            echo '<div class="notice notice-error"><p><strong>Payment Links for Vendors</strong> requires WooCommerce to be active.</p></div>';
        });
        return;
    }

    // Allow vendors into wp-admin (WC blocks non-editor users by default).
    add_filter( 'woocommerce_prevent_admin_access', function( $prevent ) {
        if ( PLFV_Role_Manager::is_vendor() ) {
            return false;
        }
        return $prevent;
    } );

    new PLFV_Vendor_Dashboard();
    new PLFV_Product_Creator();
    new PLFV_Settings();
}

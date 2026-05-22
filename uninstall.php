<?php
/**
 * Fired when the plugin is uninstalled (deleted, not just deactivated).
 *
 * Removes:
 *  - Plugin options stored in wp_options
 *  - Vendor postmeta from all products created by this plugin
 *
 * Products themselves are intentionally preserved — the store admin
 * should decide what to do with them.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Remove plugin options
delete_option( 'plfv_currency_label' );
delete_option( 'plfv_default_image_id' );

// Remove vendor postmeta from all products created by this plugin
global $wpdb;

$wpdb->delete( $wpdb->postmeta, [ 'meta_key' => '_plfv_vendor_product' ], [ '%s' ] );
$wpdb->delete( $wpdb->postmeta, [ 'meta_key' => '_plfv_vendor_id' ],      [ '%s' ] );
$wpdb->delete( $wpdb->postmeta, [ 'meta_key' => '_plfv_created_date' ],   [ '%s' ] );

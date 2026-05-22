<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class PLFV_Product_Creator {

    public function __construct() {
        add_action( 'wp_ajax_plfv_create_product', [ $this, 'ajax_create_product' ] );
        add_action( 'wp_ajax_plfv_update_product', [ $this, 'ajax_update_product' ] );
        add_action( 'pre_get_posts',               [ $this, 'filter_vendor_products' ] );
    }

    /**
     * Restrict WP_Query in admin so vendors only see their own products.
     */
    public function filter_vendor_products( $query ) {
        if ( ! is_admin() || ! $query->is_main_query() ) return;
        if ( ! PLFV_Role_Manager::is_vendor() ) return;

        $query->set( 'author', get_current_user_id() );
    }

    public function ajax_create_product() {
        check_ajax_referer( 'plfv_nonce', 'nonce' );

        if ( ! PLFV_Role_Manager::is_vendor() ) {
            wp_send_json_error( [ 'message' => __( 'Permission denied.', 'plfv' ) ] );
        }

        $price       = isset( $_POST['price'] )       ? floatval( $_POST['price'] ) : 0;
        $description = isset( $_POST['description'] ) ? wp_kses_post( wp_unslash( $_POST['description'] ) ) : '';
        $image_id    = isset( $_POST['image_id'] )    ? absint( $_POST['image_id'] ) : 0;

        if ( $price <= 0 ) {
            wp_send_json_error( [ 'message' => __( 'Price must be greater than zero.', 'plfv' ) ] );
        }

        if ( empty( $description ) ) {
            wp_send_json_error( [ 'message' => __( 'Description is required.', 'plfv' ) ] );
        }

        $product = new WC_Product_Simple();
        $product->set_name( wp_trim_words( $description, 10, '...' ) );
        $product->set_description( $description );
        $product->set_regular_price( $price );
        $product->set_catalog_visibility( 'hidden' );
        $product->set_status( 'publish' );
        $product->set_virtual( true );
        $product->set_tax_status( 'none' ); // No tax on vendor payment links

        if ( $image_id ) {
            $product->set_image_id( $image_id );
        } elseif ( PLFV_Settings::get_default_image_id() ) {
            $product->set_image_id( PLFV_Settings::get_default_image_id() );
        }

        $product_id = $product->save();

        if ( ! $product_id ) {
            wp_send_json_error( [ 'message' => __( 'Failed to create product.', 'plfv' ) ] );
        }

        // Set the post author to the current vendor
        wp_update_post( [
            'ID'          => $product_id,
            'post_author' => get_current_user_id(),
        ] );

        // Store metadata
        update_post_meta( $product_id, '_plfv_vendor_product', 1 );
        update_post_meta( $product_id, '_plfv_vendor_id',      get_current_user_id() );
        update_post_meta( $product_id, '_plfv_created_date',   current_time( 'mysql' ) );

        $payment_url = add_query_arg( 'add-to-cart', $product_id, wc_get_checkout_url() );

        wp_send_json_success( [
            'product_id'  => $product_id,
            'product_url' => get_permalink( $product_id ),
            'payment_url' => esc_url( $payment_url ),
        ] );
    }

    public function ajax_update_product() {
        check_ajax_referer( 'plfv_nonce', 'nonce' );

        if ( ! PLFV_Role_Manager::is_vendor() ) {
            wp_send_json_error( [ 'message' => __( 'Permission denied.', 'plfv' ) ] );
        }

        $product_id  = isset( $_POST['product_id'] )  ? absint( $_POST['product_id'] ) : 0;
        $price       = isset( $_POST['price'] )        ? floatval( $_POST['price'] ) : 0;
        $description = isset( $_POST['description'] )  ? wp_kses_post( wp_unslash( $_POST['description'] ) ) : '';
        $image_id    = isset( $_POST['image_id'] )     ? absint( $_POST['image_id'] ) : 0;

        if ( ! $product_id ) {
            wp_send_json_error( [ 'message' => __( 'Invalid product.', 'plfv' ) ] );
        }

        // Verify ownership
        $post = get_post( $product_id );
        if ( ! $post || (int) $post->post_author !== get_current_user_id() ) {
            wp_send_json_error( [ 'message' => __( 'You do not own this product.', 'plfv' ) ] );
        }

        if ( $price <= 0 ) {
            wp_send_json_error( [ 'message' => __( 'Price must be greater than zero.', 'plfv' ) ] );
        }

        if ( empty( $description ) ) {
            wp_send_json_error( [ 'message' => __( 'Description is required.', 'plfv' ) ] );
        }

        $product = wc_get_product( $product_id );
        $product->set_name( wp_trim_words( $description, 10, '...' ) );
        $product->set_description( $description );
        $product->set_regular_price( $price );
        $product->set_tax_status( 'none' ); // No tax on vendor payment links

        if ( $image_id ) {
            $product->set_image_id( $image_id );
        } elseif ( PLFV_Settings::get_default_image_id() ) {
            $product->set_image_id( PLFV_Settings::get_default_image_id() );
        } else {
            $product->set_image_id( 0 );
        }

        $product->save();

        $payment_url = add_query_arg( 'add-to-cart', $product_id, wc_get_checkout_url() );

        wp_send_json_success( [
            'product_id'  => $product_id,
            'payment_url' => esc_url( $payment_url ),
        ] );
    }
}

<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class PLFV_Vendor_Dashboard {

    public function __construct() {
        add_action( 'admin_menu',            [ $this, 'register_menu' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        add_action( 'admin_bar_menu',        [ $this, 'remove_admin_bar_items' ], 999 );

        // Redirect vendors away from unauthorized pages
        add_action( 'current_screen', [ $this, 'restrict_admin_access' ] );

        // Vendor column in WC product list (admins only)
        add_filter( 'manage_product_posts_columns',       [ $this, 'add_vendor_column' ] );
        add_action( 'manage_product_posts_custom_column', [ $this, 'render_vendor_column' ], 10, 2 );
    }

    public function register_menu() {
        add_menu_page(
            __( 'Payment Links', 'plfv' ),
            __( 'Payment Links', 'plfv' ),
            'edit_products',
            'plfv-dashboard',
            [ $this, 'render_dashboard' ],
            'dashicons-admin-links',
            3
        );

        add_submenu_page(
            'plfv-dashboard',
            __( 'New Payment Link', 'plfv' ),
            __( 'New Payment Link', 'plfv' ),
            'edit_products',
            'plfv-new',
            [ $this, 'render_form' ]
        );

        add_submenu_page(
            'plfv-dashboard',
            __( 'Edit Payment Link', 'plfv' ),
            __( 'Edit Payment Link', 'plfv' ),
            'edit_products',
            'plfv-edit',
            [ $this, 'render_form' ]
        );

        // Hide the edit page from the menu — it's only accessed via direct link
        remove_submenu_page( 'plfv-dashboard', 'plfv-edit' );
    }

    public function restrict_admin_access() {
        // Never restrict administrators
        if ( current_user_can( 'manage_options' ) ) return;
        if ( ! PLFV_Role_Manager::is_vendor() ) return;

        $screen          = get_current_screen();
        $allowed_screens = [ 'toplevel_page_plfv-dashboard', 'payment-links_page_plfv-new', 'payment-links_page_plfv-edit' ];

        if ( $screen && ! in_array( $screen->id, $allowed_screens, true ) ) {
            wp_redirect( admin_url( 'admin.php?page=plfv-dashboard' ) );
            exit;
        }
    }

    public function remove_admin_bar_items( $wp_admin_bar ) {
        if ( ! PLFV_Role_Manager::is_vendor() ) return;
        $wp_admin_bar->remove_node( 'new-content' );
        $wp_admin_bar->remove_node( 'comments' );
    }

    public function add_vendor_column( $columns ) {
        if ( ! current_user_can( 'manage_options' ) ) return $columns;
        $columns['plfv_vendor'] = __( 'Vendor', 'plfv' );
        return $columns;
    }

    public function render_vendor_column( $column, $post_id ) {
        if ( 'plfv_vendor' !== $column ) return;
        if ( ! current_user_can( 'manage_options' ) ) return;

        $vendor_id = (int) get_post_meta( $post_id, '_plfv_vendor_id', true );
        if ( ! $vendor_id ) {
            echo '—';
            return;
        }
        $user = get_userdata( $vendor_id );
        if ( $user ) {
            echo '<a href="' . esc_url( admin_url( 'user-edit.php?user_id=' . $vendor_id ) ) . '">'
                . esc_html( $user->display_name )
                . '</a>';
        } else {
            echo '—';
        }
    }

    public function enqueue_assets( $hook ) {
        $plfv_hooks = [
            'toplevel_page_plfv-dashboard',
            'payment-links_page_plfv-new',
            'payment-links_page_plfv-edit',
            'payment-links_page_plfv-settings',
        ];
        if ( ! in_array( $hook, $plfv_hooks, true ) ) return;

        wp_enqueue_media();

        wp_enqueue_style(
            'plfv-dashboard',
            PLFV_PLUGIN_URL . 'assets/css/vendor-dashboard.css',
            [],
            PLFV_VERSION
        );

        wp_enqueue_script(
            'plfv-dashboard',
            PLFV_PLUGIN_URL . 'assets/js/vendor-dashboard.js',
            [ 'jquery' ],
            PLFV_VERSION,
            true
        );

        wp_localize_script( 'plfv-dashboard', 'plfvData', [
            'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
            'nonce'          => wp_create_nonce( 'plfv_nonce' ),
            'dashUrl'        => admin_url( 'admin.php?page=plfv-dashboard' ),
            'currencyLabel'  => PLFV_Settings::get_currency_label(),
            'i18n'           => [
                'copied'          => __( 'Link copied!', 'plfv' ),
                'copyFailed'      => __( 'Copy failed. Please copy manually.', 'plfv' ),
                'saving'          => __( 'Saving...', 'plfv' ),
                'selectImage'     => __( 'Select Image', 'plfv' ),
                'useImage'        => __( 'Use This Image', 'plfv' ),
                'saveChanges'     => __( 'Save Changes', 'plfv' ),
                'generateLink'    => __( 'Generate Payment Link', 'plfv' ),
                'updatedNotice'   => __( 'Payment link updated successfully.', 'plfv' ),
                'errorPrice'      => __( 'Please enter a valid price.', 'plfv' ),
                'errorDesc'       => __( 'Please enter a description.', 'plfv' ),
                'errorServer'     => __( 'Server error. Please try again.', 'plfv' ),
            ],
        ] );
    }

    public function render_dashboard() {
        $vendor_id = get_current_user_id();

        $query = new WP_Query( [
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'author'         => $vendor_id,
            'meta_query'     => [
                [
                    'key'   => '_plfv_vendor_product',
                    'value' => '1',
                ],
            ],
            'fields'         => 'ids',
            'no_found_rows'  => true, // Skip SQL_CALC_FOUND_ROWS — we don't need pagination here
        ] );

        $products = array_filter(
            array_map( 'wc_get_product', $query->posts ),
            fn( $p ) => $p instanceof WC_Product
        );

        include PLFV_PLUGIN_DIR . 'templates/vendor-dashboard.php';
    }

    public function render_form() {
        $product    = null;
        $product_id = isset( $_GET['product_id'] ) ? absint( $_GET['product_id'] ) : 0;

        if ( $product_id ) {
            $raw = wc_get_product( $product_id );

            // Ownership check
            $post = get_post( $product_id );
            if ( $raw && $post && (int) $post->post_author === get_current_user_id() ) {
                $product = $raw;
            }
        }

        include PLFV_PLUGIN_DIR . 'templates/product-form.php';
    }
}

<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class PLFV_Role_Manager {

    public static function add_vendor_role() {
        add_role(
            'plfv_vendor',
            __( 'Vendor', 'plfv' ),
            [
                'read'                   => true,
                'edit_products'          => true,
                'edit_published_products'=> true,
                'upload_files'           => true,
            ]
        );
    }

    public static function remove_vendor_role() {
        remove_role( 'plfv_vendor' );
    }

    public static function is_vendor( $user_id = null ) {
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }
        $user = get_userdata( $user_id );
        if ( ! $user ) return false;
        // Admins can also use the vendor tools
        if ( user_can( $user_id, 'manage_options' ) ) return true;
        return in_array( 'plfv_vendor', (array) $user->roles, true );
    }
}

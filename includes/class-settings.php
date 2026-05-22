<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class PLFV_Settings {

    const OPTION_CURRENCY_LABEL   = 'plfv_currency_label';
    const OPTION_DEFAULT_IMAGE_ID = 'plfv_default_image_id';

    public function __construct() {
        add_action( 'admin_menu',    [ $this, 'register_settings_page' ] );
        add_action( 'admin_init',    [ $this, 'register_settings' ] );
        add_action( 'admin_post_plfv_save_settings', [ $this, 'save_settings' ] );
    }

    public function register_settings_page() {
        // Only admins see this submenu
        if ( ! current_user_can( 'manage_options' ) ) return;

        add_submenu_page(
            'plfv-dashboard',
            __( 'Settings', 'plfv' ),
            __( 'Settings', 'plfv' ),
            'manage_options',
            'plfv-settings',
            [ $this, 'render_settings_page' ]
        );
    }

    public function register_settings() {
        register_setting( 'plfv_settings_group', self::OPTION_CURRENCY_LABEL,   [ 'sanitize_callback' => 'sanitize_text_field', 'default' => 'CAD' ] );
        register_setting( 'plfv_settings_group', self::OPTION_DEFAULT_IMAGE_ID, [ 'sanitize_callback' => 'absint', 'default' => 0 ] );
    }

    public function save_settings() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
        check_admin_referer( 'plfv_settings_nonce', 'plfv_settings_nonce' );

        $currency  = isset( $_POST[ self::OPTION_CURRENCY_LABEL ] )   ? sanitize_text_field( wp_unslash( $_POST[ self::OPTION_CURRENCY_LABEL ] ) ) : 'CAD';
        $image_id  = isset( $_POST[ self::OPTION_DEFAULT_IMAGE_ID ] ) ? absint( $_POST[ self::OPTION_DEFAULT_IMAGE_ID ] ) : 0;

        update_option( self::OPTION_CURRENCY_LABEL,   $currency );
        update_option( self::OPTION_DEFAULT_IMAGE_ID, $image_id );

        wp_redirect( admin_url( 'admin.php?page=plfv-settings&saved=1' ) );
        exit;
    }

    public function render_settings_page() {
        $currency       = get_option( self::OPTION_CURRENCY_LABEL, 'CAD' );
        $default_img_id = (int) get_option( self::OPTION_DEFAULT_IMAGE_ID, 0 );
        $default_img_url = $default_img_id ? wp_get_attachment_image_url( $default_img_id, 'thumbnail' ) : '';
        ?>
        <div class="wrap plfv-wrap">
            <h1 class="plfv-page-title"><?php esc_html_e( 'Payment Links – Settings', 'plfv' ); ?></h1>

            <?php if ( isset( $_GET['saved'] ) ) : ?>
                <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Settings saved.', 'plfv' ); ?></p></div>
            <?php endif; ?>

            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <input type="hidden" name="action" value="plfv_save_settings" />
                <?php wp_nonce_field( 'plfv_settings_nonce', 'plfv_settings_nonce' ); ?>

                <div class="plfv-form-card" style="max-width:520px;">

                    <div class="plfv-field">
                        <label for="plfv-currency-label">
                            <?php esc_html_e( 'Currency Label', 'plfv' ); ?>
                        </label>
                        <p class="description" style="margin-bottom:8px;">
                            <?php esc_html_e( 'Shown next to the price on payment links (e.g. CAD, USD). This is a display label only — actual currency is controlled by WooCommerce.', 'plfv' ); ?>
                        </p>
                        <input
                            type="text"
                            id="plfv-currency-label"
                            name="<?php echo esc_attr( self::OPTION_CURRENCY_LABEL ); ?>"
                            value="<?php echo esc_attr( $currency ); ?>"
                            maxlength="10"
                            style="max-width:120px;"
                        />
                    </div>

                    <div class="plfv-field">
                        <label><?php esc_html_e( 'Default Product Image', 'plfv' ); ?></label>
                        <p class="description" style="margin-bottom:8px;">
                            <?php esc_html_e( 'Used when a vendor does not upload an image.', 'plfv' ); ?>
                        </p>
                        <input type="hidden" id="plfv-default-image-id" name="<?php echo esc_attr( self::OPTION_DEFAULT_IMAGE_ID ); ?>" value="<?php echo esc_attr( $default_img_id ); ?>" />
                        <div id="plfv-default-image-preview" class="plfv-image-preview <?php echo $default_img_id ? 'has-image' : ''; ?>" style="margin-bottom:10px;">
                            <?php if ( $default_img_url ) : ?>
                                <img src="<?php echo esc_url( $default_img_url ); ?>" alt="" />
                            <?php endif; ?>
                        </div>
                        <div class="plfv-image-buttons">
                            <button type="button" id="plfv-default-upload-btn" class="plfv-btn plfv-btn-secondary">
                                <?php esc_html_e( 'Select Image', 'plfv' ); ?>
                            </button>
                            <button type="button" id="plfv-default-remove-btn" class="plfv-btn plfv-btn-danger" <?php echo $default_img_id ? '' : 'style="display:none;"'; ?>>
                                <?php esc_html_e( 'Remove', 'plfv' ); ?>
                            </button>
                        </div>
                    </div>

                    <div class="plfv-form-actions">
                        <button type="submit" class="plfv-btn plfv-btn-primary plfv-btn-large">
                            <?php esc_html_e( 'Save Settings', 'plfv' ); ?>
                        </button>
                    </div>

                </div>
            </form>
        </div>
        <?php
    }

    // ── Static helpers ──────────────────────────────────────────

    public static function get_currency_label() {
        return get_option( self::OPTION_CURRENCY_LABEL, 'CAD' );
    }

    public static function get_default_image_id() {
        return (int) get_option( self::OPTION_DEFAULT_IMAGE_ID, 0 );
    }
}

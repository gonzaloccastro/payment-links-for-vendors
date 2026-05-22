<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/** @var WC_Product|null $product */

$is_edit    = ! is_null( $product );
$product_id = $is_edit ? $product->get_id() : 0;
$price      = $is_edit ? $product->get_regular_price() : '';
$desc       = $is_edit ? $product->get_description() : '';
$image_id   = $is_edit ? $product->get_image_id() : 0;
$image_url  = $image_id ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : '';
?>
<div class="wrap plfv-wrap">
    <h1 class="plfv-page-title">
        <?php echo $is_edit ? esc_html__( 'Edit Payment Link', 'plfv' ) : esc_html__( 'New Payment Link', 'plfv' ); ?>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=plfv-dashboard' ) ); ?>" class="plfv-btn plfv-btn-secondary">
            ← <?php esc_html_e( 'Back to list', 'plfv' ); ?>
        </a>
    </h1>

    <div class="plfv-form-card">
        <div class="plfv-field">
            <label for="plfv-price"><?php esc_html_e( 'Price', 'plfv' ); ?> <span class="plfv-required">*</span></label>
            <div class="plfv-price-wrap">
                <span class="plfv-currency"><?php echo esc_html( get_woocommerce_currency_symbol() ); ?> <strong><?php echo esc_html( PLFV_Settings::get_currency_label() ); ?></strong></span>
                <input
                    type="number"
                    id="plfv-price"
                    name="price"
                    value="<?php echo esc_attr( $price ); ?>"
                    min="0.01"
                    step="0.01"
                    placeholder="0.00"
                    required
                />
            </div>
        </div>

        <div class="plfv-field">
            <label for="plfv-description"><?php esc_html_e( 'Description', 'plfv' ); ?> <span class="plfv-required">*</span></label>
            <textarea
                id="plfv-description"
                name="description"
                rows="5"
                placeholder="<?php esc_attr_e( 'Describe what the customer is paying for...', 'plfv' ); ?>"
                required
            ><?php echo esc_textarea( $desc ); ?></textarea>
        </div>

        <div class="plfv-field">
            <label><?php esc_html_e( 'Image (optional)', 'plfv' ); ?></label>
            <div class="plfv-image-uploader">
                <input type="hidden" id="plfv-image-id" name="image_id" value="<?php echo esc_attr( $image_id ); ?>" />
                <div id="plfv-image-preview" class="plfv-image-preview <?php echo $image_id ? 'has-image' : ''; ?>">
                    <?php if ( $image_url ) : ?>
                        <img src="<?php echo esc_url( $image_url ); ?>" alt="" />
                    <?php endif; ?>
                </div>
                <div class="plfv-image-buttons">
                    <button type="button" id="plfv-upload-btn" class="plfv-btn plfv-btn-secondary">
                        <?php esc_html_e( 'Select Image', 'plfv' ); ?>
                    </button>
                    <?php if ( $image_id ) : ?>
                    <button type="button" id="plfv-remove-btn" class="plfv-btn plfv-btn-danger">
                        <?php esc_html_e( 'Remove', 'plfv' ); ?>
                    </button>
                    <?php else : ?>
                    <button type="button" id="plfv-remove-btn" class="plfv-btn plfv-btn-danger" style="display:none;">
                        <?php esc_html_e( 'Remove', 'plfv' ); ?>
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="plfv-form-actions">
            <button type="button" id="plfv-submit-btn" class="plfv-btn plfv-btn-primary plfv-btn-large"
                data-action="<?php echo $is_edit ? 'plfv_update_product' : 'plfv_create_product'; ?>"
                data-product-id="<?php echo esc_attr( $product_id ); ?>"
            >
                <?php echo $is_edit ? esc_html__( 'Save Changes', 'plfv' ) : esc_html__( 'Generate Payment Link', 'plfv' ); ?>
            </button>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="plfv-modal" class="plfv-modal" style="display:none;" role="dialog" aria-modal="true">
    <div class="plfv-modal-backdrop"></div>
    <div class="plfv-modal-box">
        <div class="plfv-modal-icon">✓</div>
        <h2><?php esc_html_e( 'Payment Link Generated!', 'plfv' ); ?></h2>
        <p><?php esc_html_e( 'Share this link with your customer:', 'plfv' ); ?></p>
        <div class="plfv-link-box">
            <input type="text" id="plfv-generated-link" readonly />
            <button type="button" id="plfv-copy-modal-btn" class="plfv-btn plfv-btn-primary">
                <?php esc_html_e( 'Copy Link', 'plfv' ); ?>
            </button>
        </div>
        <div class="plfv-modal-actions">
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=plfv-dashboard' ) ); ?>" class="plfv-btn plfv-btn-secondary">
                <?php esc_html_e( 'Back to Dashboard', 'plfv' ); ?>
            </a>
            <button type="button" id="plfv-create-another" class="plfv-btn plfv-btn-outline">
                <?php esc_html_e( 'Create Another', 'plfv' ); ?>
            </button>
        </div>
    </div>
</div>

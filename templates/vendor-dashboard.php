<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/** @var WC_Product[] $products */
?>
<div class="wrap plfv-wrap">
    <h1 class="plfv-page-title">
        <?php esc_html_e( 'Payment Links', 'plfv' ); ?>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=plfv-new' ) ); ?>" class="plfv-btn plfv-btn-primary">
            + <?php esc_html_e( 'New Payment Link', 'plfv' ); ?>
        </a>
    </h1>

    <?php if ( empty( $products ) ) : ?>
        <div class="plfv-empty-state">
            <span class="dashicons dashicons-admin-links"></span>
            <p><?php esc_html_e( 'No payment links yet.', 'plfv' ); ?></p>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=plfv-new' ) ); ?>" class="plfv-btn plfv-btn-primary">
                <?php esc_html_e( 'Create your first payment link', 'plfv' ); ?>
            </a>
        </div>
    <?php else : ?>
        <table class="plfv-table widefat">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Product', 'plfv' ); ?></th>
                    <th><?php esc_html_e( 'Description', 'plfv' ); ?></th>
                    <th><?php esc_html_e( 'Price', 'plfv' ); ?></th>
                    <th><?php esc_html_e( 'Date', 'plfv' ); ?></th>
                    <th><?php esc_html_e( 'Actions', 'plfv' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $products as $product ) :
                    $product_id  = $product->get_id();
                    $payment_url = esc_url( add_query_arg( 'add-to-cart', $product_id, wc_get_checkout_url() ) );
                    $created     = get_post_meta( $product_id, '_plfv_created_date', true );
                    $image_id    = $product->get_image_id();
                ?>
                <tr>
                    <td class="plfv-col-thumb">
                        <?php if ( $image_id ) : ?>
                            <?php echo wp_get_attachment_image( $image_id, [ 48, 48 ] ); ?>
                        <?php else : ?>
                            <span class="plfv-no-image dashicons dashicons-format-image"></span>
                        <?php endif; ?>
                    </td>
                    <td class="plfv-col-desc">
                        <strong><?php echo esc_html( $product->get_name() ); ?></strong>
                        <p class="plfv-desc-snippet"><?php echo esc_html( wp_trim_words( $product->get_description(), 15 ) ); ?></p>
                    </td>
                    <td class="plfv-col-price">
                        <?php echo wc_price( $product->get_regular_price() ); ?>
                        <span class="plfv-currency-badge"><?php echo esc_html( PLFV_Settings::get_currency_label() ); ?></span>
                    </td>
                    <td class="plfv-col-date">
                        <?php echo $created ? esc_html( date_i18n( get_option( 'date_format' ), strtotime( $created ) ) ) : '—'; ?>
                    </td>
                    <td class="plfv-col-actions">
                        <button
                            class="plfv-btn plfv-btn-copy"
                            data-url="<?php echo esc_attr( $payment_url ); ?>"
                        >
                            <?php esc_html_e( 'Copy Link', 'plfv' ); ?>
                        </button>
                        <a
                            href="<?php echo esc_url( admin_url( 'admin.php?page=plfv-edit&product_id=' . $product_id ) ); ?>"
                            class="plfv-btn plfv-btn-secondary"
                        >
                            <?php esc_html_e( 'Edit', 'plfv' ); ?>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

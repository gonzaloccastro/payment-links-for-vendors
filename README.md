# Payment Links for Vendors

A WordPress/WooCommerce plugin that lets vendors generate shareable payment links from the wp-admin — no Stripe dashboard, no manual invoicing, no product catalog exposure.

## The Problem

Service businesses using WooCommerce often need to collect one-off payments from clients: a deposit, a custom order, a session fee. The standard WooCommerce flow forces them to either:

- Create a product in the catalog (which customers might find and buy at wrong prices), or
- Use Stripe/PayPal dashboards separately from their WooCommerce setup (split reporting, split gateway fees)

For an agency running WooCommerce with Moneris as the payment gateway, neither option works. The checkout is already configured, trusted, and PCI-compliant — they just needed a way to generate ad-hoc payment links without touching the product catalog.

## The Solution

This plugin introduces a `Vendor` role with a stripped-down wp-admin interface. Vendors can:

1. Set a price and description
2. Optionally upload an image
3. Click **Generate Payment Link** → receive a WooCommerce checkout URL

The generated link uses WooCommerce's native `?add-to-cart=` parameter. It works with any gateway already configured on the store — Moneris, Stripe, PayPal, anything.

The underlying product is created as `catalog_visibility = hidden`, so it never appears in shop listings or search results.

## Features

**Vendor role:**
- Simplified wp-admin dashboard (Payment Links only — no access to posts, pages, settings, other WooCommerce menus)
- Create payment links: price, description, optional image
- Edit their own links
- Copy the payment URL with one click
- Cannot see or edit other vendors' links

**Admin:**
- Standard WooCommerce product list shows a "Vendor" column with the creator's name
- Settings: currency label (e.g. CAD, USD) displayed next to prices
- Settings: default product image used when vendor doesn't upload one
- Full control over all products via standard WooCommerce

## Technical Notes

- **Vendor scoping via `pre_get_posts`** — vendors' WP_Query in admin is filtered to `author = current_user_id`, so they never see each other's products at the query level
- **Ownership check on edit** — `ajax_update_product` verifies `post_author === current_user_id` server-side before allowing any update
- **Hidden products** — `catalog_visibility = hidden` + `virtual = true` + `tax_status = none` keeps vendor products out of shop, search, and tax calculations
- **Nonces on all AJAX** — `check_ajax_referer()` on every `wp_ajax_*` handler
- **All strings translation-ready** — `__()` / `_e()` / `esc_html__()` throughout, text domain `plfv`
- **Assets loaded conditionally** — `wp_enqueue_*` only fires on plugin pages, checked via `$hook`
- **Clean uninstall** — `uninstall.php` removes all options and postmeta on plugin deletion; products are preserved for the admin to manage

## File Structure

```
payment-links-for-vendors/
├── payment-links-for-vendors.php   # Plugin bootstrap, constants, init hook
├── uninstall.php                   # Cleanup on deletion
├── readme.txt                      # WordPress.org format
├── includes/
│   ├── class-role-manager.php      # Vendor role: add/remove/check
│   ├── class-product-creator.php   # AJAX handlers: create/update products
│   ├── class-vendor-dashboard.php  # Admin menu, asset enqueue, vendor column
│   └── class-settings.php          # Currency label + default image settings
├── templates/
│   ├── vendor-dashboard.php        # Payment links table
│   └── product-form.php            # Create/Edit form + success modal
└── assets/
    ├── css/vendor-dashboard.css
    └── js/vendor-dashboard.js      # Copy-to-clipboard, WP media uploader, AJAX form
```

## Requirements

- WordPress 6.0+
- WooCommerce (any recent version)
- PHP 7.4+

## Installation

1. Upload the `payment-links-for-vendors` folder to `/wp-content/plugins/`
2. Activate via **Plugins → Installed Plugins**
3. Go to **Settings → Permalinks** and click **Save Changes** (flushes rewrite rules)
4. Go to **Users → Add New**, create a user, assign the **Vendor** role
5. Optionally configure currency label and default image under **Payment Links → Settings**

## What I'd Add Next

This plugin was built to solve a specific real-world need. If extended into a general-purpose product, the logical next steps would be:

- **Payment link expiry** — auto-unpublish products after N days or after first purchase
- **Purchase notifications** — email the vendor when their link is paid
- **Link analytics** — track views vs. conversions per link
- **WP-CLI commands** — `wp plfv list-links --vendor=5` for bulk operations

## License

GPL v2 or later — see [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html)

=== Payment Links for Vendors ===
Contributors: gonzaloccastro
Tags: woocommerce, vendors, payment links, checkout
Requires at least: 6.0
Tested up to: 6.7
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Allows vendors to generate WooCommerce payment links they can share directly with customers.

== Description ==

Payment Links for Vendors adds a restricted "Vendor" role to WordPress/WooCommerce. Vendors can log into the admin, create a simple payment link (price + description + optional image), and share it with a customer. The customer clicks the link, is taken straight to checkout, and pays using the store's existing payment gateway.

**Vendor capabilities:**
* Create payment links (hidden WooCommerce products)
* Edit their own links
* Copy the payment URL with one click
* Cannot delete products or see other vendors' products

**Admin capabilities:**
* See a "Vendor" column on the WooCommerce product list showing who created each link
* Set a currency label (e.g. CAD or USD) displayed next to prices
* Set a default product image used when the vendor does not upload one
* Full control over all products via the standard WooCommerce interface

**Technical notes:**
* Generated products have `catalog_visibility = hidden` — they never appear in shop listings or search results
* Products are associated to the vendor via `post_author` and the `_plfv_vendor_id` meta key
* The payment link uses WooCommerce's native add-to-cart URL, so it works with any payment gateway including Moneris
* Compatible with any WooCommerce-compatible theme

== Installation ==

1. Upload the `payment-links-for-vendors` folder to `/wp-content/plugins/`
2. Activate the plugin through **Plugins → Installed Plugins**
3. Go to **Settings → Permalinks** and click **Save Changes** (required to flush rewrite rules)
4. Go to **Users → Add New**, create a user and assign the **Vendor** role
5. Optionally configure currency label and default image under **Payment Links → Settings**

== Frequently Asked Questions ==

= Does this work with my payment gateway? =
Yes. The payment link is a standard WooCommerce add-to-cart URL. Any gateway that works with your WooCommerce checkout will work automatically.

= Can vendors see each other's products? =
No. Vendors only see products they created. A `pre_get_posts` filter restricts their admin product list by author.

= Will the products appear in my shop or search results? =
No. All products created by vendors have `catalog_visibility` set to `hidden`.

= Can I use different currencies on different sites? =
Yes. Install the plugin on each site and set the currency label independently under **Payment Links → Settings** on each installation.

= What happens to vendor products if I deactivate the plugin? =
The products remain in WooCommerce as standard hidden products. The Vendor role is removed on deactivation. Re-activating the plugin restores the role.

== Changelog ==

= 1.0.0 =
* Initial release
* Vendor role with restricted wp-admin access
* Create / edit payment links via simplified form
* Image upload with fallback to admin-defined default
* Currency label setting (CAD, USD, etc.)
* Admin column showing vendor name on product list
* Compatible with Moneris and standard WooCommerce checkout

== Upgrade Notice ==

= 1.0.0 =
Initial release.

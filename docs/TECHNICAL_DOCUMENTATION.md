# MotoShop Parts — Technical Documentation

> **Project DNA:** WordPress 6.5+ / WooCommerce 8.0+ e-commerce · PHP 8.2 · SQLite (via mu-plugin) · COD-first checkout · Southeast Asia market  
> **Theme:** `swiftcart-child` (child of Twenty Twenty-Five)  
> **Version:** 1.5.0  
> **Last updated:** 2026-05-04

---

## Tech Stack

| Layer | Technology | Version |
|-------|-----------|---------|
| CMS | WordPress | 6.5+ |
| E-commerce | WooCommerce | 8.0+ |
| Language | PHP | 8.2 |
| Database | SQLite (via `sqlite-database-integration` mu-plugin) | — |
| Parent Theme | Twenty Twenty-Five | latest |
| Fonts | Inter (Google Fonts) | — |
| CSS | Vanilla CSS (custom design system, no Tailwind) | — |
| JS | Vanilla JS (no framework, no build step) | — |

---

## 1. Architecture Overview

```
motoshop-parts/
├── wp-config.php                          # DB config, debug flags, SQLite integration
├── wp-content/
│   ├── mu-plugins/
│   │   └── sqlite-database-integration/   # SQLite drop-in (replaces MySQL)
│   ├── plugins/
│   │   ├── motoparts/                     # Core product/order CPTs + fitment DB
│   │   └── swiftcart-cod/                 # COD checkout, warehouse ops, admin dashboard
│   ├── themes/
│   │   └── swiftcart-child/               # Child theme (Twenty Twenty-Five parent)
│   │       ├── style.css                  # Design system tokens + component styles (693 lines)
│   │       ├── functions.php              # Theme setup, enqueue, WooCommerce hooks, redirects
│   │       ├── front-page.php             # Homepage template (636 lines, inline JS)
│   │       ├── login-admin.css            # Branded admin login page
│   │       ├── seed-products.php          # Product seeding script for dev/demo
│   │       ├── assets/
│   │       │   ├── css/swiftcart-enhancements.css  # Comprehensive enhancement styles (3032 lines)
│   │       │   └── images/                # SVG logos (horizontal, badge, card variants)
│   │       └── woocommerce/               # Template overrides
│   │           ├── content-product.php    # Dual CTA product cards
│   │           └── myaccount/             # form-login.php, orders.php
│   └── database/                          # SQLite database file
```

### Key Design Decisions

| Decision | Rationale |
|----------|-----------|
| **SQLite instead of MySQL** | Zero-config local dev; no MySQL server needed. Uses `sqlite-database-integration` mu-plugin to translate MySQL queries. |
| **Two separate plugins** | `motoparts` handles product catalog (CPTs, fitment). `swiftcart-cod` handles COD-specific business logic. Decoupled for reuse. |
| **COD-first checkout** | Built for Philippine e-commerce where 70%+ of transactions are Cash on Delivery. |
| **Warehouse frontend (not wp-admin)** | `/warehouse/*` routes render distraction-free, tablet-optimised full-screen UIs outside wp-admin for floor staff. |

---

## 2. Plugin: `swiftcart-cod`

> COD-first e-commerce for Southeast Asia — Philippine address fields, 3-step checkout, blacklist management, warehouse ops, and COD reporting.

### 2.1 Entry Point & Lifecycle

**File:** [`swiftcart-cod.php`](file:///Users/jay/Studio/motoshop-parts/wp-content/plugins/swiftcart-cod/swiftcart-cod.php)

```
Boot sequence:
1. Define constants (SWIFTCART_VERSION, SWIFTCART_DIR, SWIFTCART_URL)
2. Declare WooCommerce HPOS compatibility
3. Register SPL autoloader (SwiftCart\Autoloader)
4. Register activation / deactivation / uninstall hooks
5. On `plugins_loaded`:
   a. Check WooCommerce is active (bail with admin_notice if not)
   b. Version-guarded table creation (only on install/upgrade)
   c. Bootstrap SwiftCart singleton → wires all sub-modules
```

### 2.2 Autoloader

**File:** [`class-autoloader.php`](file:///Users/jay/Studio/motoshop-parts/wp-content/plugins/swiftcart-cod/includes/class-autoloader.php)

Maps `SwiftCart\*` namespaces to filesystem paths using WordPress naming convention:

| Namespace | Directory |
|-----------|-----------|
| `SwiftCart\Checkout\*` | `includes/checkout/` |
| `SwiftCart\Stock\*` | `includes/stock/` |
| `SwiftCart\Admin\*` | `includes/admin/` |
| `SwiftCart\Warehouse\*` | `includes/warehouse/` |
| `SwiftCart\*` | `includes/` |

**Convention:** `SwiftCart\Checkout\Checkout_Fields` → `includes/checkout/class-checkout-fields.php`

### 2.3 Module Map

| Module | Class | Responsibility |
|--------|-------|---------------|
| **Core** | `SwiftCart` | Singleton bootstrap, wires all sub-modules |
| **Activator** | `Activator` | Creates DB tables, warehouse_staff role, default options |
| **Deactivator** | `Deactivator` | Flushes rewrite rules |
| **Checkout Fields** | `Checkout\Checkout_Fields` | PH address cascade (Province → City → Barangay), landmark, phone validation, blacklist check |
| **Checkout Steps** | `Checkout\Checkout_Steps` | Custom order statuses, COD handling fee, order-received title |
| **Stock Status** | `Stock\Stock_Status` | Decoupled stock labels (In Stock / Low Stock / Out of Stock), metabox, badges, WC availability filter |
| **Orders Dashboard** | `Admin\Orders_Dashboard` | Admin KPI widgets, filterable order table, inline status updates, risk flagging |
| **Customer Management** | `Admin\Customer_Management` | Customer list with cancellation rates, blacklist view |
| **COD Reports** | `Admin\COD_Reports` | Financial KPIs by date range (today/yesterday/week/month) |
| **Delivery Zones** | `Admin\Delivery_Zones` | Phase 2 stub — redirects to WC shipping zones |
| **Product Stock Meta** | `Admin\Product_Stock_Meta` | Bulk stock status actions on Products admin list |
| **Warehouse** | `Warehouse\Warehouse_Dashboard` | 5-page tablet-optimised frontend (pick list, packing, dispatch, returns, inventory) |

### 2.4 Custom Order Statuses

| Status Slug | Display Label | Stage |
|-------------|--------------|-------|
| `pending` | Pending | Order placed, awaiting confirmation |
| `processing` | Confirmed | Staff confirmed, ready to pack |
| `wc-sc-packed` | Packed | Warehouse packed, awaiting rider |
| `wc-sc-dispatch` | Dispatched | Out for delivery |
| `completed` | Delivered | COD collected, order complete |
| `cancelled` | Cancelled | Cancelled by staff or customer |
| `wc-sc-returned` | Returned | Failed delivery / customer refused |

### 2.5 Admin Menu Structure

```
SwiftCart (dashicons-cart, position 55)
├── Orders          → swiftcart          (manage_swiftcart)
├── Customers       → swiftcart-customers (manage_swiftcart)
├── COD Reports     → swiftcart-reports  (view_swiftcart_reports)
└── Delivery Zones  → swiftcart-zones    (manage_swiftcart)
```

### 2.6 Custom Role & Capabilities

**Role:** `warehouse_staff`

| Capability | Purpose |
|-----------|---------|
| `read` | WordPress base |
| `swiftcart_view_pick_list` | Access warehouse frontend |
| `swiftcart_pack_orders` | Mark orders as packed |
| `swiftcart_dispatch_orders` | Dispatch orders to riders |
| `swiftcart_manage_returns` | Process returns |
| `swiftcart_edit_stock_status` | Toggle product stock labels |

**Administrator** inherits all the above plus `manage_swiftcart` and `view_swiftcart_reports`.

### 2.7 AJAX Endpoints

| Action Hook | Handler | Nonce | Capability |
|------------|---------|-------|------------|
| `sc_update_order_status` | `Orders_Dashboard::ajax_update_order_status` | `sc_admin` | `manage_woocommerce` |
| `sc_toggle_blacklist` | `Orders_Dashboard::ajax_toggle_blacklist` | `sc_admin` | `manage_swiftcart` |
| `swiftcart_get_cities` | `Checkout_Fields::ajax_get_cities` | `swiftcart_checkout` | public |
| `swiftcart_get_barangays` | `Checkout_Fields::ajax_get_barangays` | `swiftcart_checkout` | public |
| `sc_warehouse_action` | `Warehouse_Dashboard::ajax_warehouse_action` | `sc_warehouse` | `swiftcart_pack_orders` |

### 2.8 Warehouse Frontend

Rendered at `/warehouse/*` via custom rewrite rules. Bypasses the normal WordPress template hierarchy for a distraction-free, tablet-optimised UI.

| Route | Page | Description |
|-------|------|-------------|
| `/warehouse/pick-list` | Pick List | Orders ready to pack, sorted by zone (NCR/Cebu/Davao/Other) |
| `/warehouse/packing?order=ID` | Packing | Item-by-item checklist for a single order with print label |
| `/warehouse/dispatch` | Dispatch Board | Packed orders ready for rider assignment |
| `/warehouse/returns?order=ID` | Returns Intake | Return reason form with COD non-collection confirmation |
| `/warehouse/inventory` | Inventory | Inline stock status toggle with live search |

**Access control:** Requires login + `swiftcart_view_pick_list` capability.

---

## 3. Plugin: `motoparts`

> Automotive parts e-commerce — catalogue, orders, cart, checkout, and inventory management.

### 3.1 Entry Point

**File:** [`motoparts.php`](file:///Users/jay/Studio/motoshop-parts/wp-content/plugins/motoparts/motoparts.php)

Registers autoloader, lifecycle hooks, then bootstraps `MotoParts\MotoParts` singleton on `plugins_loaded`.

### 3.2 Module Map

| Module | Class | Responsibility |
|--------|-------|---------------|
| **Core** | `MotoParts` | Singleton, wires CPTs + capabilities + assets |
| **Activator** | `Activator` | Registers CPTs, creates DB tables, assigns caps, seeds statuses |
| **Deactivator** | `Deactivator` | Flushes rewrite rules |
| **Product CPT** | `CPT\Product_CPT` | Custom post type for products |
| **Order CPT** | `CPT\Order_CPT` | Custom post type for orders, seeds default status terms |
| **DB Tables** | `DB\DB_Tables` | Creates 5 custom tables via `dbDelta()` |
| **Capabilities** | `Admin\Capabilities` | Role-based access for product/order management |
| **Assets** | `Assets` | Enqueues frontend scripts and styles |

### 3.3 Custom Database Tables

| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `wp_mp_order_items` | Line items for orders | `order_id`, `product_id`, `quantity`, `price`, `variant_meta` |
| `wp_mp_vehicles` | Vehicle year/make/model catalogue | `year`, `make`, `model` |
| `wp_mp_product_fitments` | Product ↔ Vehicle compatibility | `product_id`, `vehicle_id` (unique pair) |
| `wp_mp_coupons` | Discount codes | `code`, `type`, `value`, `expiry`, `usage_limit` |
| `wp_mp_inventory_log` | Stock change audit trail | `product_id`, `delta`, `reason`, `created_at` |

---

## 4. Database Schema — `swiftcart-cod`

### 4.1 Custom Table

| Table | Purpose | Columns |
|-------|---------|---------|
| `wp_sc_blacklist` | Phone number blacklist for fraud prevention | `id`, `phone`, `reason`, `active`, `created_by`, `created_at` |

### 4.2 Post Meta Keys

| Meta Key | Used On | Purpose |
|----------|---------|---------|
| `_billing_barangay` | Orders | Barangay address field |
| `_billing_landmark` | Orders | Landmark for rider navigation |
| `_billing_province` | Orders | Province/region |
| `_sc_blacklist_flag` | Orders | Manual blacklist flag by admin |
| `_sc_packing_started` | Orders | Packing in progress indicator |
| `_sc_packing_started_by` | Orders | User ID who started packing |
| `_sc_packing_started_at` | Orders | Timestamp of packing start |
| `_sc_stock_status` | Products | Stock label (`in_stock`, `low_stock`, `out_of_stock`) |

### 4.3 Options

| Option Key | Default | Purpose |
|-----------|---------|---------|
| `swiftcart_cod_fee` | `20` | COD handling fee (₱) |
| `swiftcart_cutoff_hour` | `10` | Same-day dispatch cutoff hour |
| `swiftcart_call_threshold` | `3000` | ₱ threshold for verification call |
| `swiftcart_blacklist_threshold` | `3` | Cancellations before auto-flag |
| `swiftcart_version` | `1.0.0` | Plugin version |
| `swiftcart_db_version` | `1.0.0` | DB schema version (migration guard) |

---

## 5. Theme: `swiftcart-child`

> Child theme of Twenty Twenty-Five. Blaze Orange + Forest Green palette (mobile-first, 390px primary breakpoint).

### 5.1 Design System Tokens

Defined in [`style.css`](file:///Users/jay/Studio/motoshop-parts/wp-content/themes/swiftcart-child/style.css) `:root` and extended in [`swiftcart-enhancements.css`](file:///Users/jay/Studio/motoshop-parts/wp-content/themes/swiftcart-child/assets/css/swiftcart-enhancements.css):

| Token | Value | Purpose |
|-------|-------|---------|
| `--sc-primary` | `#FF5722` | Primary CTA (blaze orange) |
| `--sc-primary-dark` | `#E64A19` | Primary hover (burnt orange) |
| `--sc-primary-light` | `#FFCCBC` | Tinted backgrounds |
| `--sc-primary-mid` | `#FF8A65` | Accents / badges |
| `--sc-green` | `#2E7D32` | Success / forest green |
| `--sc-amber` | `#F57F17` | Warning / low-stock |
| `--sc-red` | `#D32F2F` | Danger / error |
| `--sc-blue` | `#1E88E5` | Info / trust (style.css) · `#E64A19` (enhancements override) |
| `--sc-surface` | `#F5F6F8` | Page background |
| `--sc-text` | `#111111` | Carbon black |
| `--sc-text-muted` | `#757575` | Secondary text |
| `--sc-border` | `#E0E0E0` | Steel border |
| `--sc-radius` | `8px` | Border radius |
| `--sc-radius-pill` | `999px` | Pill border radius |
| `--sc-font` | `'Inter', 'Poppins', system-ui` | Font stack |

**Enhancement tokens** (enhancements.css only):

| Token | Value | Purpose |
|-------|-------|---------|
| `--sc-shadow-md` | `0 4px 16px rgba(12,24,5,.14)` | Medium elevation |
| `--sc-shadow-lg` | `0 8px 32px rgba(12,24,5,.18)` | High elevation |
| `--sc-shadow-glow` | `0 0 20px rgba(153,189,73,.25)` | Glow effect |
| `--sc-glass-bg` | `rgba(255,255,255,0.08)` | Glassmorphism background |
| `--sc-glass-border` | `rgba(255,255,255,0.18)` | Glassmorphism border |
| `--sc-backdrop` | `blur(16px)` | Backdrop filter |
| `--sc-ease-spring` | `cubic-bezier(0.34,1.56,0.64,1)` | Spring animation |
| `--sc-ease-smooth` | `cubic-bezier(0.25,0.46,0.45,0.94)` | Smooth animation |

### 5.2 CSS Component Library

Located across `style.css` (693 lines) and `swiftcart-enhancements.css` (3032 lines):

- **Buttons:** `.sc-btn`, `.sc-btn--primary`, `.sc-btn--ghost`, `.sc-btn--outline`, `.sc-btn--lg`, `.sc-btn--sm`, `.sc-btn--block`, `.sc-btn--buy-now`, `.sc-btn--add-to-cart`, `.sc-btn--cancel`
- **Badges:** `.sc-cod-badge`, `.sc-cod-pill`, `.sc-cod-tag`, `.sc-stock-badge--{in,low,out}-of-stock`, `.sc-status--{status}`, `.sc-risk-badge--{normal,watchlist,flagged}`
- **Announcement Bar:** `.sc-announcement-bar` (animated gradient bg, rotating items)
- **Hero:** `.sc-hero`, `.sc-hero__inner`, `.sc-hero__floating-card` (ambient glow orbs, staggered fade-up)
- **Trust Bar:** `.sc-trust-bar`, `.sc-trust-bar__grid`, `.sc-trust-bar__item` (mobile scroll-snap)
- **Category Cards:** `.sc-cat-card`, `.sc-cat-card__image`, `.sc-cat-card__info` (hover zoom + gradient overlay)
- **Product Cards:** `.sc-product-card`, `.sc-product-card__image-wrap`, `.sc-product-card__actions` (dual CTA grid, hover accent bar)
- **How COD Works:** `.sc-how-cod__step`, `.sc-how-cod__connector`, `.sc-how-cod__note`
- **Delivery Coverage:** `.sc-coverage`, `.sc-coverage__city` (glassmorphism pills), `.sc-coverage__stat`
- **FAQ:** `.sc-faq__item`, `.sc-faq__question`, `.sc-faq__answer` (smooth slide animation)
- **Checkout:** `.sc-progress-bar`, `.sc-progress-step`, `.sc-checkout-section`, `.sc-cod-confirm`
- **Shipping Options:** `.sc-shipping-option`, `.sc-shipping-option__radio`
- **Cutoff Timer:** `.sc-cutoff-timer`
- **Orders Timeline:** `.sc-order-timeline`, `.sc-timeline-step`, `.sc-timeline-dot` (pulse animation)
- **Warehouse UI:** `.sc-wh-table`, `.sc-wh-zone-badge--{ncr,cebu,davao,other}`, `.sc-packing-status`
- **Admin KPIs:** `.sc-widgets-row`, `.sc-widget`, `.sc-kpi-card`
- **Buyer Login:** `.sc-account-wrapper`, `.sc-account-tabs`, `.sc-account-body`
- **Sticky Header:** `#sc-sticky-header` (3-col grid, transparent→dark on scroll)
- **Navigation:** `.sc-main-nav`, `.sc-main-nav__link` (bottom pill on mobile)
- **Site Logo:** `.sc-site-logo`
- **Footer:** `.sc-footer`, `.sc-footer__grid`, `.sc-footer__badge`
- **Reassurance Banner:** `.sc-reassurance-banner`
- **Animations:** `scFadeUp`, `scFadeIn`, `scSlideInLeft`, `scScaleIn`, `scFloat`, `scPulse`, `scBadgePulse`
- **Utilities:** `.sc-reveal` / `.sc-visible` (IntersectionObserver scroll reveal), `.sc-sr-only`, `.sc-container`

### 5.3 Homepage Template

**File:** [`front-page.php`](file:///Users/jay/Studio/motoshop-parts/wp-content/themes/swiftcart-child/front-page.php)

Sections (top to bottom):
1. **Hero Banner** — Animated gradient background with ambient glow orbs, staggered entrance animations, COD pill badge, floating info cards (order status + rating)
2. **Announcement Bar** — *Inside hero section*. Animated gradient bg, 3 rotating messages (4s interval), dismiss-to-sessionStorage
3. **Trust Bar** — 4-item grid (COD Payment, Verified Seller, Easy Returns, Money-Back). Scroll-snap on mobile
4. **Featured Categories** — Auto-fill responsive grid (brake-parts, engine-parts, tires-wheels, lights-electrical). Pulls real WC `product_cat` terms with thumbnail support
5. **Featured Products** — Custom WP_Query (8 products, excludes hidden). Full product cards with stock badges, COD tags, sale % badges
6. **How COD Works** — 3-step visual explainer (Add to Cart → Enter Address → Pay on Delivery). Anti-scam warning
7. **Delivery Coverage** — Dark gradient section with glassmorphism city pills and animated stat counters (50+ cities, 2400+ deliveries, 97% rate)
8. **FAQ Preview** — 5-question accordion with smooth slide animation. First item auto-expanded
9. **Reassurance Banner** — Anti-scam notice (red)
10. **Footer** — 4-column grid (Brand + social, Shop links, Support links, Contact). Bottom bar with trust badges

**Inline JavaScript** (bottom of template):
- Announcement bar dismiss + rotation
- FAQ accordion (aria-expanded, data-visible states)
- IntersectionObserver scroll reveals on all sections

### 5.4 functions.php

**File:** [`functions.php`](file:///Users/jay/Studio/motoshop-parts/wp-content/themes/swiftcart-child/functions.php) (541 lines)

Key registrations:
- Google Fonts (Inter only — Poppins kept in font-stack fallback but not enqueued)
- `swiftcart-enhancements.css` enqueue (dependency: `swiftcart-child`)
- WooCommerce theme support (gallery, zoom, lightbox, slider)
- WooCommerce default styles removed (`woocommerce-general`, `woocommerce-layout`, `woocommerce-smallscreen`)
- Navigation menus: `primary`, `footer`, `warehouse`
- Widget areas: `shop-sidebar`, `footer-1`, `footer-2`
- Body class additions: `sc-checkout-page`, `sc-account-page`, `sc-woo-page`
- **COD info on single product:** `swiftcart_product_page_cod_info()` — COD badge, delivery estimate (dispatch day + date range), return policy. Hooked at `woocommerce_single_product_summary` priority 25
- **Redirect rules:** Non-admin users blocked from wp-admin (`admin_init`); logged-in customers redirected from `wp-login.php` to My Account (`login_init`, POST-safe, logout-safe)
- **Admin login redirect:** Admins/managers → wp-admin after login (`woocommerce_login_redirect` + `login_redirect`)
- **Admin login branding:** `login-admin.css` enqueue, header URL/text, role label
- **Buy Now redirect:** `woocommerce_add_to_cart_redirect` filter for `?buy-now=1` (guests → login)
- **Guest cart block:** `woocommerce_add_to_cart_validation` blocks guests + JS AJAX intercept via `wp_footer`
- **Unified sticky header:** Single `wp_footer` hook (priority 10) outputs `<header id="sc-sticky-header">` with 3-column grid: nav (left) → logo (centre) → role-based account links (right). Inline scroll-state JS adds `.scrolled` class at 10px
- **Registration enforcement:** `after_setup_theme` ensures WC registration options are always enabled
- **WC block template override:** `woocommerce_has_block_template` → `__return_false` to force classic PHP templates
- **WC loop hooks removed:** Default image/title/price/button hooks stripped on `woocommerce_init` — `content-product.php` handles everything

### 5.5 Product Archive

**File:** [`archive-product.php`](file:///Users/jay/Studio/motoshop-parts/wp-content/themes/swiftcart-child/archive-product.php)

Forces the classic WooCommerce product loop (instead of Gutenberg blocks) so that our `content-product.php` template override is used. Twenty Twenty-Five is a block theme, so without `woocommerce_has_block_template` → `__return_false`, WooCommerce would use block-based rendering which bypasses the template.

### 5.6 WooCommerce Template Overrides

| File | WC Version | Purpose |
|------|-----------|----------|
| `archive-product.php` | — | Classic product loop (bypasses block rendering) |
| `content-product.php` | 9.4.0 | Dual CTA product cards (Buy Now + Add to Cart), stock badges, COD badge, sale badge |
| `myaccount/form-login.php` | 9.9.0 | Tabbed buyer login/register page |
| `myaccount/orders.php` | Custom | Order timeline with status pipeline |

### 5.7 Admin Login Branding

**File:** [`login-admin.css`](file:///Users/jay/Studio/motoshop-parts/wp-content/themes/swiftcart-child/login-admin.css)

Dark-themed (`#0C1805` background) staff login page with MotoShop Parts branding.  
Skipped when `?context=buyer` is present. Shows "🔒 Staff / Admin Access" label.

### 5.8 Responsive Breakpoints

| Breakpoint | Behaviour |
|-----------|----------|
| `≤ 360px` | Product card CTAs stack to single column |
| `≤ 767px` | Sticky header hidden; nav becomes bottom-fixed pill. Trust bar → horizontal scroll-snap. Footer → single column. Hero padding reduced |
| `≥ 768px` | Trust bar 4-column grid. Hero visual shown. Category grid 4-col. COD steps horizontal. Coverage stats side-by-side |
| `≥ 1024px` | Section padding 80px. Hero headline 56px. Product grid 4-col. Hero visual 480px wide |

---

## 6. Checkout Flow

The checkout is a **3-step wizard** (Address → Shipping → Confirm) powered by `Checkout_Fields` + `Checkout_Steps`.

### 6.1 Custom Billing Fields

| Field | Type | Priority | Notes |
|-------|------|----------|-------|
| `billing_phone` | text | 5 | "09XX XXX XXXX" format, validated |
| `billing_first_name` | text | 10 | Standard |
| `billing_last_name` | text | 20 | Standard |
| `billing_province` | select | 65 | Cascading — loads cities via AJAX |
| `billing_city` | select | 70 | Cascading — loads barangays via AJAX |
| `billing_barangay` | text | 75 | Required |
| `billing_address_1` | text | 80 | "House No., Street Name" |
| `billing_landmark` | text | 85 | Required — "helps our rider find you" |
| `billing_postcode` | text | 90 | Standard |

**Removed:** `billing_company`, `billing_address_2`  
**Hidden:** Shipping address (`woocommerce_cart_needs_shipping_address` returns `false`)

### 6.2 Address Cascade Data

**File:** [`data/ph-locations.php`](file:///Users/jay/Studio/motoshop-parts/wp-content/plugins/swiftcart-cod/data/ph-locations.php)

Coverage: Metro Manila, Cebu, Davao del Sur, Laguna, Bulacan, Rizal, Pampanga. Returns `array<province-slug => array<city => array<barangay>>>`.

### 6.3 Validation Rules

1. **Barangay** — Required, not empty
2. **Landmark** — Required, not empty
3. **Phone** — Must match `/^(0\d{10}|63\d{10})$/` (PH mobile format)
4. **Blacklist** — Phone checked against `wp_sc_blacklist` table

### 6.4 COD Fee

A flat ₱20 fee (configurable via `swiftcart_cod_fee` option) is added to the cart when the selected payment method is `cod`.

---

## 7. Risk Management

### 7.1 Customer Risk Levels

Displayed in the admin Orders Dashboard per order row:

| Risk Level | Condition | Badge Color |
|-----------|-----------|-------------|
| **Flagged for Review** | Blacklist flag set OR cancel rate ≥ 50% | Red |
| **Watchlist** | Cancel rate ≥ 30% | Amber |
| **Normal** | Cancel rate < 30% | Default |

### 7.2 Cancellation Rate

Calculated per phone number: `(cancelled orders / total orders) × 100`.

### 7.3 Blacklist

- **Table-level:** `wp_sc_blacklist` — phone numbers blocked from checkout
- **Order-level:** `_sc_blacklist_flag` post meta — manual flag per order
- **Auto-flag:** Configurable via `swiftcart_blacklist_threshold` (default: 3 cancellations)

---

## 8. SQLite Compatibility Notes

> [!WARNING]
> The project uses `sqlite-database-integration` as a mu-plugin. This creates compatibility constraints.

| Constraint | Workaround Applied |
|-----------|-------------------|
| `CREATE TABLE IF NOT EXISTS` doesn't work idempotently | Version-guarded table creation via `swiftcart_db_version` / `motoparts_db_version` options |
| No `ENUM` type | `varchar(20)` used for type columns |
| No `JSON` column type | `longtext` used for JSON data |
| `AUTO_INCREMENT` syntax | Handled automatically by the SQLite driver |
| `get_charset_collate()` returns empty string | Harmless — SQLite ignores it |

---

## 9. Assets

### 9.1 Frontend Assets

| Asset | Handle | Hook |
|-------|--------|------|
| `style.css` | `swiftcart-child` | `wp_enqueue_scripts` |
| `swiftcart-enhancements.css` | `swiftcart-enhancements` | `wp_enqueue_scripts` |
| `login-admin.css` | `swiftcart-login-admin` | `login_enqueue_scripts` |
| `checkout.js` | `swiftcart-checkout` | `wp_enqueue_scripts` (checkout only) |
| Google Fonts (Inter) | `swiftcart-fonts` | `wp_enqueue_scripts` |
| Homepage JS (FAQ, Announce, Reveals) | inline `<script>` | Bottom of `front-page.php` |
| Scroll-state header JS | inline `<script>` | `wp_footer` (inside sticky header hook) |

### 9.2 Admin Assets

| Asset | Handle | Hook |
|-------|--------|------|
| `admin.css` | `swiftcart-admin` | `admin_enqueue_scripts` (SwiftCart pages only) |
| `admin.js` | `swiftcart-admin` | `admin_enqueue_scripts` (SwiftCart pages only) |

### 9.3 Localized Script Data

**`swiftcartCheckout`** (frontend checkout):
- `ajaxUrl`, `nonce`, `cutoffHour`, `codFee`, `callThreshold`, `i18n.*`

**`scAdmin`** (admin dashboard):
- `ajaxUrl`, `nonce`

---

## 10. WooCommerce Integration Points

| Hook | Type | Module | Purpose |
|------|------|--------|---------|
| `woocommerce_checkout_fields` | filter | Checkout_Fields | Add PH address fields |
| `woocommerce_checkout_process` | action | Checkout_Fields | Validate phone, barangay, landmark, blacklist |
| `woocommerce_checkout_update_order_meta` | action | Checkout_Fields | Save custom fields to order meta |
| `woocommerce_admin_order_data_after_shipping_address` | action | Checkout_Fields | Display custom fields in admin |
| `woocommerce_cart_needs_shipping_address` | filter | Checkout_Fields | Returns `false` (COD = billing only) |
| `woocommerce_cart_calculate_fees` | action | Checkout_Steps | Adds COD handling fee |
| `wc_order_statuses` | filter | Checkout_Steps | Registers packed/dispatched/returned statuses |
| `woocommerce_get_availability` | filter | Stock_Status | Replaces WC availability text with label-only |
| `woocommerce_after_shop_loop_item_title` | action | Stock_Status | Renders stock badge in product loop |
| `woocommerce_single_product_summary` | action (×2) | Stock_Status + Theme | Stock badge (plugin, pri 6) + COD info/delivery estimate (theme, pri 25) |
| `before_woocommerce_init` | action | Entry | Declares HPOS compatibility |
| `woocommerce_init` | action | Theme | Removes all default shop loop hooks (image, title, price, button) |
| `woocommerce_has_block_template` | filter | Theme | Returns `false` — forces classic PHP templates |
| `woocommerce_enqueue_styles` | filter | Theme | Strips WC general/layout/smallscreen CSS |
| `woocommerce_add_to_cart_redirect` | filter | Theme | Guest → login; Buy Now → checkout |
| `woocommerce_add_to_cart_validation` | filter | Theme | Block guest add-to-cart with error notice |
| `woocommerce_login_redirect` | filter | Theme | Admin/manager → wp-admin after WC login |
| `login_redirect` | filter | Theme | Admin/manager → wp-admin after WP login |
| `admin_init` | action | Theme | Redirect non-admin users away from wp-admin |
| `login_init` | action | Theme | Redirect logged-in customers from `wp-login.php` (POST-safe) |
| `login_enqueue_scripts` | action | Theme | Enqueue branded admin login CSS |
| `body_class` | filter | Theme | Adds `sc-checkout-page`, `sc-account-page`, `sc-woo-page` |
| `wp_footer` | action (×2) | Theme | Unified sticky header (pri 10) + guest JS redirect (pri 30) |

---

## 11. Configuration

### 11.1 wp-config.php

| Constant | Value | Notes |
|---------|-------|-------|
| `WP_DEBUG` | `true` | Debug mode enabled |
| `WP_DEBUG_LOG` | `true` | Logs to `wp-content/debug.log` |
| `WP_DEBUG_DISPLAY` | `false` | Errors not shown to visitors |
| `DB_NAME` | `sqlite_database` | SQLite placeholder |
| `DB_HOST` | `localhost` | SQLite placeholder |

### 11.2 Plugin Options

See [Section 4.3](#43-options) for the full options table.

### 11.3 WooCommerce Settings

| Setting | Value | Notes |
|---------|-------|-------|
| `woocommerce_enable_myaccount_registration` | `yes` | Buyer self-registration enabled |
| `woocommerce_registration_generate_username` | `yes` | Auto-generate username from email |
| `woocommerce_registration_generate_password` | `no` | Buyer chooses own password |
| `woocommerce_myaccount_page_id` | `11` | My Account page (WC default) |

---

## 12. Known Issues & Debt

| Issue | Status | File |
|-------|--------|------|
| SQLite `CREATE TABLE IF NOT EXISTS` fails on repeated calls | ✅ Fixed — version-guarded | `swiftcart-cod.php` |
| My Account redirect loop after registration | ✅ Fixed — changed `template_redirect` to `login_init` | `functions.php` |
| Admin users had no logout button | ✅ Fixed — added logout link to unified sticky header | `functions.php` |
| Separate nav/account/logo hooks caused duplicate DOM output | ✅ Fixed — consolidated into single sticky header hook | `functions.php` |
| WC default styles conflicting with custom design | ✅ Fixed — `swiftcart_dequeue_wc_styles()` strips legacy CSS | `functions.php` |
| Delivery Zones is a Phase 2 stub | Pending | `class-delivery-zones.php` |
| WP-CLI deprecation warnings (`cli/Colors.php`) | External dependency, low priority | `debug.log` |
| `Action Scheduler` table syntax errors with SQLite | Intermittent, WooCommerce upstream | `debug.log` |
| No automated tests exist | Technical debt | — |
| Cancellation rate is computed on every page load (N+1 queries) | Performance concern at scale | `class-orders-dashboard.php` |
| FSE block-theme header/site-title suppressed via CSS `display:none` | Works but inelegant | `swiftcart-enhancements.css` |
| `--sc-blue` token overridden to `#E64A19` in enhancements.css | Intentional but confusing naming | `swiftcart-enhancements.css` |

---

## 13. Custom Hook

| Hook | Type | Fired When |
|------|------|-----------|
| `sc_order_dispatched` | action | Order status changes to `sc-dispatch` via warehouse AJAX |

This is intended as an integration point for an SMS notification plugin to alert the customer.

---

## 14. Development Setup

```bash
# Clone the project
git clone <repo> motoshop-parts

# No MySQL needed — SQLite is the database backend.
# WordPress will use wp-content/database/.ht.sqlite

# Start a local PHP dev server (or use wp-cli server)
cd motoshop-parts
php -S localhost:8080

# Or with wp-cli (if installed):
wp server --port=8080
```

> [!IMPORTANT]
> After first boot, you may need to flush rewrite rules for warehouse URLs to work:
> **Settings → Permalinks → Save Changes** (no changes needed, just re-save).

---

## 15. User Accounts

### 15.1 Default Admin

| Field | Value |
|-------|-------|
| Username | `admin` |
| Email | `admin@localhost.com` |
| Role | Administrator |
| Login URL | `/wp-login.php` |

### 15.2 Buyer Registration

Buyers self-register at `/my-account/` via the **Create Account** tab.  
Usernames are auto-generated from email. Buyers choose their own password.

### 15.3 Access Control Matrix

| User Type | wp-admin | /my-account/ | /warehouse/* | Header Links |
|-----------|----------|-------------|-------------|-------------|
| **Admin** | ✅ Full access | ✅ | ✅ | ⚙ Admin Panel · Logout |
| **Shop Manager** | ✅ WooCommerce only | ✅ | ✅ | ⚙ Admin Panel · Logout |
| **Warehouse Staff** | ❌ Redirected | ✅ | ✅ | My Account · Logout |
| **Customer (Buyer)** | ❌ Redirected | ✅ | ❌ | My Account · Logout |
| **Guest** | ❌ | Login/Register form | ❌ | Login / Register |

# MotoShop Parts — Issue Logs

> Tracking bugs, fixes, and technical debt for the MotoShop Parts project.  
> **Last updated:** 2026-05-04

---

## Resolved Issues

| Issue # | Description | Log Date | Remarks | Solution |
|---------|-------------|----------|---------|----------|
| ISS-001 | SQLite `CREATE TABLE IF NOT EXISTS` fails on repeated plugin activations | 2026-02-15 | `sqlite-database-integration` mu-plugin doesn't handle idempotent table creation. Caused fatal errors on re-activation. | Implemented version-guarded table creation via `swiftcart_db_version` / `motoparts_db_version` options. Tables only created when stored version differs from plugin version. |
| ISS-002 | My Account redirect loop after buyer registration | 2026-03-01 | Hook on `template_redirect` caused infinite loop — user was already logged in post-registration but redirect kept firing on every page load. | Changed redirect hook from `template_redirect` to `login_init`. Now only fires on `wp-login.php` requests. Added guards for POST requests and logout actions. |
| ISS-003 | Admin users had no logout button in header | 2026-03-10 | Original header account bar only showed admin-specific links (Admin Panel, Orders, Products) but omitted a logout link. | Added universal logout link for all logged-in users in the unified sticky header using `wp_logout_url( home_url() )`. |
| ISS-004 | Duplicate DOM output from separate header hooks | 2026-04-20 | Navigation bar, site logo, and account bar were output by three separate `wp_footer` hooks at different priorities. Caused ordering issues and occasional duplicate markup. | Consolidated all three elements into a single `wp_footer` hook (priority 10). Unified `<header id="sc-sticky-header">` uses 3-column CSS grid: nav (left) → logo (centre) → account links (right). |
| ISS-005 | WooCommerce default styles conflicting with custom design system | 2026-04-22 | WC's `woocommerce-general.css`, `woocommerce-layout.css`, and `woocommerce-smallscreen.css` overrode custom design tokens, causing visual inconsistencies on shop/checkout pages. | Added `swiftcart_dequeue_wc_styles()` filter on `woocommerce_enqueue_styles` to strip all three legacy CSS files. All styling handled by `style.css` + `swiftcart-enhancements.css`. |
| ISS-006 | Missing `header.php` and `footer.php` triggers PHP Deprecated warnings | 2026-05-04 | WordPress emits "Theme without header.php is deprecated" on every page load. Classic templates trigger the deprecation check. | Added empty `header.php` and `footer.php` stubs to `swiftcart-child/`. Stubs contain only a comment explaining they are intentionally empty for the FSE block theme. |
| ISS-007 | FSE block-theme header/site-title suppressed via CSS `display:none !important` | 2026-05-04 | Twenty Twenty-Five's FSE header hidden via CSS. DOM weight is negligible but approach is a hack. | Created empty FSE template part overrides at `parts/header.html` and `parts/footer.html`. Parent theme's block header/footer no longer renders in the DOM. Removed the CSS `display: none !important` hack from `swiftcart-enhancements.css`. |
| ISS-008 | `current_time('timestamp')` usage is deprecated in WordPress 5.3+ | 2026-05-04 | Found in `class-orders-dashboard.php` and `functions.php`. WordPress docs recommend `wp_date()` instead. | Replaced all `date('...', current_time('timestamp'))` with `wp_date('...')`. Also replaced bare `date()` calls in KPI queries (ISS-020 merged). |
| ISS-009 | Admin dashboard inline styles instead of CSS classes | 2026-05-04 | 15+ inline `style="..."` attributes across `class-orders-dashboard.php`, `class-customer-management.php`, and `class-cod-reports.php`. | Moved all inline styles to BEM classes in `admin.css`: `.sc-date-sub`, `.sc-filters`, `.sc-row--flagged`, `.sc-order-date`, `.sc-cancel-rate`, `.sc-blacklist-icon`, `.sc-cancel-btn`, `.sc-barangay-sub`, `.sc-empty-row`, `.sc-report-tabs`, `.sc-report-note`, `.sc-rate--warn/ok`, `.sc-bl-status--yes/no`. |
| ISS-010 | `!important` overuse in CSS — 32 occurrences | 2026-05-04 | Audited all 32 occurrences. **Result:** 31 are required to override WooCommerce inline styles and FSE high-specificity selectors. 1 was the `display:none` hack removed by ISS-007. | **Resolved-Accepted.** All remaining `!important` flags are necessary. Added ISS-010 explanatory comments to each block documenting why they're required: WC button overrides (16), WC product grid layout (9), responsive media query overrides (6). |
| ISS-011 | `--sc-blue` token overridden to orange — misleading name | 2026-05-04 | Token `#1E88E5` (blue) in `style.css` overridden to `#E64A19` (orange) in enhancements.css. Naming is misleading. | Renamed `--sc-blue` → `--sc-accent` and `--sc-blue-light` → `--sc-accent-light` across `style.css` (7 refs), `swiftcart-enhancements.css` (6 refs), and `admin.css` (1 ref). Same hex values, zero visual change. |
| ISS-012 | Cancellation rate computed via N+1 queries on every Orders Dashboard page load | 2026-05-04 | `get_customer_cancel_rate()` runs 2 `wc_get_orders()` queries per order row. 50 rows = 100+ DB queries per page load. | Added `private static $cancel_rate_cache` for per-request memoization. After first calculation per phone number, subsequent lookups are O(1). Reduces from 100 → ~40 queries for 20 unique phones. |
| ISS-013 | KPI widgets run unbounded `wc_get_orders()` with `limit => -1` | 2026-05-04 | Fetches ALL orders to compute delivery rate. Iterates each today-order individually after fetching IDs. | Optimized: today's orders loaded directly as objects (no double-fetch). All-time stats use ID-only count queries. Eliminated `$all_ids` intermediate array. |
| ISS-014 | Orders Dashboard has no pagination | 2026-05-04 | `get_orders()` hardcodes `limit => 50` with no page parameter. Orders beyond 50 are inaccessible. | Added `paged` GET parameter support. `get_orders()` now accepts `$paged`/`$per_page` args. Added `count_orders()` method for total count. Prev/Next navigation rendered below table. |
| ISS-015 | `get_post_meta()` / `update_post_meta()` used for order data — HPOS incompatible | 2026-05-04 | Plugin declares HPOS compatibility but uses WordPress post meta functions for order data in 15+ locations. | Migrated all order-related meta to `$order->get_meta()` / `$order->update_meta_data()` + `$order->save_meta_data()` across 4 files: `class-orders-dashboard.php`, `class-checkout-fields.php`, `class-warehouse-dashboard.php`, `class-customer-management.php`. Product meta (`class-stock-status.php`) correctly kept as `get_post_meta` — HPOS applies only to orders. |
| ISS-019 | No automated tests exist | 2026-05-04 | No unit, integration, or e2e tests. All validation is manual. | Bootstrapped PHPUnit test infrastructure: `tests/bootstrap.php`, `tests/phpunit.xml`. Created initial test suites: `test-checkout-validation.php` (7 tests: barangay/landmark required, phone format validation with data provider for 5 invalid formats, clean checkout scenario) and `test-stock-status.php` (7 tests: valid/invalid status handling, default fallback, badge HTML, availability text override). |
| ISS-020 | `date()` used without timezone argument in KPI queries | 2026-05-04 | `date('Y-m-d 00:00:00')` uses server timezone, not WordPress timezone. | Merged with ISS-008. Replaced with `wp_date('Y-m-d 00:00:00')`. |
| ISS-021 | No CSRF protection on admin filter form submissions | 2026-05-04 | Filter GET params have no nonce verification. Violates WP security best practices. | Added `wp_nonce_field('sc_orders_filter', '_sc_nonce')` to filter form. Nonce verified at top of `render_orders_page()` when filter params are present. Pagination links also include nonce via `wp_nonce_url()`. |
| ISS-022 | Homepage featured categories use hardcoded slugs | 2026-05-04 | Hardcoded 4 category slugs. Homepage won't update when categories change. | Replaced with dynamic `get_terms(['hide_empty' => true, 'number' => 4, 'orderby' => 'count', 'order' => 'DESC'])`. Excludes "Uncategorized". Emoji icon map kept as fallback for known slugs; unknown categories use '🔩'. |

---

## Backlog / Future Tasks

| Task # | Description | Origin | Priority | Acceptance Criteria |
|--------|-------------|--------|----------|---------------------|
| TASK-001 | **Delivery Zones Management UI** — Build custom admin page for managing per-barangay delivery zones, fee structures, and coverage rules. Replace the current WC shipping zones redirect stub in `class-delivery-zones.php`. | ISS-016 | Medium | Admin can create/edit/delete delivery zones. Each zone maps to a set of barangays with a flat or tiered delivery fee. Zone data persists in `wp_sc_delivery_zones` table. Checkout dynamically selects zone based on selected barangay. |
| TASK-002 | **Suppress WP-CLI Colors.php deprecation** — Monitor WP-CLI releases for a fix to the `cli/Colors.php` PHP deprecation warning. If no upstream fix arrives by Q3 2026, evaluate pinning WP-CLI version or adding a `@` error suppression in `wp-config.php` for CLI context only. | ISS-017 | Low | Deprecation warning no longer appears in `debug.log` during WP-CLI operations. |
| TASK-003 | **Action Scheduler SQLite compatibility** — Monitor WooCommerce releases for Action Scheduler SQLite support. If no upstream fix arrives by Q3 2026, evaluate: (a) disabling the AS cron runner if scheduled tasks aren't critical, or (b) migrating from SQLite to MySQL for production. | ISS-018 | Low | `RuntimeException: Unable to claim actions` errors no longer appear in `debug.log`, or a documented mitigation strategy is in place. |

---

## Issue Template

```
| ISS-XXX | [Description] | YYYY-MM-DD | [Context / Impact] | [Fix applied or recommended] |
```

**Status Legend:**
- **Resolved Issues** table = fixed and verified
- **Backlog / Future Tasks** table = planned work with acceptance criteria


# MotoShop Parts — UI/UX Issue Logs

> Tracking user interface, user experience, accessibility, and visual design issues.  
> **Last updated:** 2026-05-04

---

## Open Issues

*(none — all issues resolved)*

---

## Resolved Issues

| Issue # | Category | Description | Log Date | Solution Applied |
|---------|----------|-------------|----------|-----------------|
| UX-001 | Accessibility | No `prefers-reduced-motion` media query | 2026-05-04 | Added `@media (prefers-reduced-motion: reduce)` block at end of `swiftcart-enhancements.css`. Disables all animations and transitions (`animation-duration: 0.01ms`, `transition-duration: 0.01ms`). Targets hero bg animation, announcement bar gradient, floating cards, orb pulse, sale badge pulse, scroll reveals, and button shimmer. |
| UX-002 | Accessibility | `:focus-visible` styles only on `.sc-btn` and `.sc-site-logo` | 2026-05-04 | Added comprehensive `:focus-visible` rules for 19 interactive element selectors: nav links, product card links, FAQ buttons, category cards, header account links, cart icon, footer links, social links, CTA buttons, account tabs, tab-switch buttons, and login form links. Also added form input focus styles. Uses `outline: 3px solid var(--sc-primary); outline-offset: 3px;`. |
| UX-003 | Inline Styles | Inline `style="..."` in `content-product.php` and `form-login.php` | 2026-05-04 | Extracted all inline styles to BEM classes. **content-product.php:** sale badge → `.sc-product-card__badge--sale`, COD badge → `.sc-cod-badge--sm`, OOS grid → `.sc-product-card__actions--single`. **form-login.php:** remember row → `.sc-login-form__remember-row`, forgot link → `.sc-login-form__forgot`, switch buttons → `.sc-login-form__switch-btn` / `.sc-login-form__switch-wrap`, password hint → `.sc-login-form__password-hint`. |
| UX-004 | Inline Styles | `functions.php` inline `style="font-size:13px;color:#E55E00;"` in COD info | 2026-05-04 | Replaced with `.sc-product-cod-badge__sub` class using `color: var(--sc-amber)` token. |
| UX-005 | Mobile UX | Sticky header hidden on mobile, account links inaccessible | 2026-05-04 | Changed mobile header from `display: none` to a slimmed-down grid layout. Desktop nav and text links hidden; mobile-only account (👤) and cart (🛒) icon links added with `.sc-header-account--mobile` class. Logo remains centred. Bottom nav pill kept for page navigation. |
| UX-006 | Mobile UX | No cart icon or cart count indicator in header | 2026-05-04 | Added `.sc-header-cart` element to desktop header with live count badge (`.sc-header-cart__count`). Uses `WC()->cart->get_cart_contents_count()` for initial render. Cart also available in mobile header icons. Badge hides when count is 0 via CSS `:empty` / `[data-count="0"]`. |
| UX-007 | Visual | Hero image `onerror` hides broken image silently | 2026-05-04 | Replaced `onerror="this.style.display='none'"` with `onerror="this.classList.add('sc-img-failed');this.nextElementSibling.style.display='flex';"`. Failed images get `.sc-img-failed` (hidden via CSS). A fallback `<div class="sc-hero__image-wrap--fallback">` shows a motorcycle emoji with gradient background. |
| UX-008 | Accessibility | Social links use emoji without `aria-hidden` | 2026-05-04 | Wrapped emoji content in `<span aria-hidden="true">` so screen readers use only the `aria-label` attribute on each link ("Facebook", "Instagram", "TikTok"). |
| UX-009 | Typography | `--sc-text-muted` set to `#e64a19` (burnt orange) | 2026-05-04 | Changed from `#e64a19` to `#757575` (medium grey) in `swiftcart-enhancements.css` `:root` block. Now matches `style.css` fallback value. All 20+ elements using this token will now render as subdued grey instead of attention-grabbing orange. |
| UX-010 | Design Token | `--sc-green-light` set to `#ff8a65` (orange) — misleading name | 2026-05-04 | Added new token `--sc-success-bg: #e8f5e9` (mint green). Set `--sc-green-light: var(--sc-success-bg)` as backward-compatible alias. In-stock badges and success backgrounds now render as green instead of orange. Also fixed `--sc-amber-light` from `#c4cc82` (khaki) to `#fff3e0` (proper light amber). |
| UX-011 | Visual | Duplicate/conflicting tokens between `style.css` and `swiftcart-enhancements.css` | 2026-05-04 | Added canonical source comment to both files. `swiftcart-enhancements.css` is now the authoritative source; `style.css` explicitly labeled as fallback. Fixed `--sc-accent` in `style.css` from `#1e88e5` (blue) to `#e64a19` (burnt orange) to match. Removed circular self-referencing aliases. |
| UX-012 | Performance | Hero SVG asset — no build-time validation | 2026-05-04 | Added `admin_init` health check in `functions.php`. Validates `assets/images/hero-delivery.svg` and `assets/images/logo-3-card.svg` exist. Displays `admin_notices` warning if any critical image asset is missing. |
| UX-013 | UX | Product images not optimally lazy-loaded | 2026-05-04 | First 4 products in the homepage featured grid now use `loading="eager"` (above-the-fold LCP optimization). Remaining products use `loading="lazy"`. Controlled via `$featured_query->current_post < 4` check. |
| UX-014 | Accessibility | FAQ accordion missing `aria-labelledby` | 2026-05-04 | Added `id="sc-faq-q-{$i}"` to each question `<button>` and `aria-labelledby="sc-faq-q-{$i}"` to each answer `<div role="region">`. Screen readers now announce the question text when focus enters the answer region. |
| UX-015 | UX | Homepage ATC button links to product page instead of actual add-to-cart | 2026-05-04 | Simple products now use real add-to-cart URL (`add_query_arg('add-to-cart', $id, wc_get_cart_url())`) with `add_to_cart_button` class and `data-product_id`/`data-quantity` attributes. Variable products show "Select Options" label linking to the product page. |
| UX-016 | Mobile UX | Bottom nav pill overlaps page content | 2026-05-04 | Added `padding-bottom: 64px` to `body:not(.wp-admin)` in the `≤768px` media query. Footer content and bottom badges now clear the floating nav pill. Also added `background`, `backdrop-filter`, `border-radius`, and `border` to the nav pill for better visual separation. |

---

## Issue Template

```
| UX-XXX | [Category] | [Description] | YYYY-MM-DD | [Severity] | [Impact / Context] | [Recommended Fix] |
```

**Severity Legend:**
- **High** — Blocks core user flow or violates critical accessibility standards
- **Medium** — Degrades experience for a significant user segment or creates visual inconsistency
- **Low** — Minor polish item, does not block functionality

**Category Tags:**
- `Accessibility` — WCAG compliance, screen reader, keyboard navigation
- `Mobile UX` — Mobile-specific layout, interaction, or navigation issues
- `Visual` — Design inconsistencies, broken layouts, missing assets
- `Typography` — Font, color, spacing issues
- `Design Token` — CSS variable naming, conflicts, or misuse
- `Inline Styles` — Violations of BEM/CSS-class convention
- `Performance` — Render performance, loading, LCP/CLS impact
- `UX` — General user experience flow or interaction issues

# Aso Signature storefront — UI/UX review

**Date:** 13 September 2026
**Site tested:** `http://aso-signature-backend-v2.test` (local Valet build, current `main` branch)
**Method:** Scripted walkthrough with Playwright/Chromium at desktop (1440×900) and mobile (390×844, iPhone user agent), as a guest and as a logged-in test customer. Four passes, 156 screenshots, plus a DOM/accessibility/performance probe on every page. Data checks were run against the local database. No orders were placed.
**Evidence:** every finding links to a screenshot in [`screenshots/`](screenshots/). Raw per-page diagnostics are in [`diagnostics.json`](diagnostics.json); the scripts that produced them are in [`scripts/`](scripts/) so the run can be repeated after fixes.

> Note on the screenshots: the red Laravel Debugbar strip visible in many images is a local-development tool (`APP_DEBUG=true`). It is not part of the design, but it is worth confirming it is off in production.

---

## 1. Executive summary

The store has a strong foundation: a distinctive visual identity, a genuinely good "How to Measure" guide, a well-thought-out measurement-profile system, and a checkout that already knows about tailoring. The problems are mostly at the seams: **links that go to the wrong place, placeholder content that shipped, a measurement step that can be skipped, and a guest path that dead-ends at a login page.** Most of the high-impact fixes are configuration or copy changes rather than new features.

### The ten things to fix first

| # | Issue | Impact | Effort |
|---|---|---|---|
| 1 | Both hero "How it works" buttons go to the measurement guide, not the How It Works page. "Shop Now" lands on an unnamed search page. | Critical | Config |
| 2 | Checkout lets a customer proceed with **no measurements** or **incomplete measurements** without a warning. | Critical | Small dev |
| 3 | Guest "Proceed to Checkout" silently redirects to the login page. No explanation, no inline sign-up. | High | Small dev |
| 4 | Category pages open with a large blank grey block: the banner images 404. Descriptions read "Men Category" / "New In Collection". | High | Content |
| 5 | 404 (and 500/503) pages are the bare Laravel error screen. The branded error page in the theme is never used. | High | Small dev |
| 6 | Three `$2.00` test products are live and featured on the home page. Designer "H0USE OF MEKA" is spelt with a zero. Footer says "Nigeria1". | High | Content |
| 7 | Product names break mid-word ("premi um fabric", "Blac k agbada") on cards and product pages. | High | 2-line CSS |
| 8 | Mini-cart promises "Get Up To 30% OFF on your 1st order" (default Bagisto copy). | High | Copy |
| 9 | DHL options show "MEDICAL EXPRESS" and raw timestamps like `2026-09-18T23:59:00`. Shipping is ~50% of the order value and appears only at step 3. | High | Small dev |
| 10 | Account home is an empty page with a Logout button. | Medium | Small dev |

### Scorecard

| Area | Rating | One-line verdict |
|---|---|---|
| Visual design & brand | ★★★★☆ | Coherent, premium feel. Copy tone slips in places ("Fun Newsletter", "Become User"). |
| Home page | ★★★☆☆ | Good hero, but CTAs mislinked; hero has no controls; test products featured. |
| Navigation | ★★★☆☆ | Mega menu works well; 5th category clipped on desktop; hidden menus in tab order. |
| Catalog / listing | ★★☆☆☆ | Broken banners, HS-code and size filters, "Expensive First" default, word-breaking. |
| Product page | ★★★☆☆ | Clean, but does not sell the made-to-measure promise; empty reviews tab. |
| Cart & checkout | ★★★☆☆ | Solid structure; measurement gating missing; shipping labels raw; guest dead end. |
| Measurements | ★★★★☆ | Best part of the site. Guide covers 8 of 13 fields; small editor issues. |
| Account | ★★☆☆☆ | Empty landing page, placeholder-only forms, wrong page titles. |
| Content pages | ★★★★☆ | How It Works and How to Measure are excellent; How It Works is too tall. |
| Forms & auth | ★★★☆☆ | Inline validation works; login error clears the form; register copy. |
| Accessibility | ★★☆☆☆ | Skip link and focus rings exist; labels not associated; 24px tap targets; hidden links focusable. |
| Performance | ★★★☆☆ | Fast server; home ships 1.8 MB; one 588 KB product image. |
| Trust & data quality | ★★☆☆☆ | Test data, typos, conflicting addresses, false promo. |

---

## 2. Detailed findings

Severity: **Critical** blocks or misleads a purchase · **High** costs conversions or trust · **Medium** friction · **Low** polish.

### 2.1 Home page

**C1. Hero buttons link to the wrong pages** — Critical
Both slides carry a "How it works" button, and both point at `/how-to-measure` instead of `/how-it-works`. "Shop Now" goes to `/search?sort=name-desc`, which renders as a bare "Search results for :" page. "Shop New In" goes to `/search?sort=created_at-desc` rather than a curated New In collection (the NEW IN category itself is disabled).
Evidence: [desktop-home.png](screenshots/desktop-home.png), [desktop-hero-slide-2.png](screenshots/desktop-hero-slide-2.png). The links live in the theme customization named "Hero Section" (id 27), editable in Admin → Settings → Themes.
Fix: point "How it works" at the How It Works page; point "Shop Now" at a real landing (menswear/womenswear or a "Shop all" category with a heading); either enable and curate the NEW IN category or rename the button to "Shop the collection".

**H1. Hero carousel has no controls and rotates on a clock schedule** — High
The component deliberately renders no arrows or dots ("slides follow the clock schedule"). In practice a visitor sees one of two different messages with different CTAs and cannot reach the other. The two hidden-slide links are still keyboard-focusable. Evidence: [desktop-home-keyboard-focus.png](screenshots/desktop-home-keyboard-focus.png); component `packages/Webkul/Shop/src/Resources/views/components/hero-carousel/index.blade.php`.
Fix: pick one primary message (the "Made to your measurements" slide is the stronger, more differentiated one) or add dots/arrows and pause-on-hover; add `inert`/`aria-hidden` to inactive slides.

**H2. Test products are featured on the home page** — High
"White Senator", "Brown Adire Skirt Set" and "Timeless Kaftan made from superior fabric" are priced at $2.00 and appear in Featured Products and All Products with a "New" badge. Evidence: [desktop-home-loaded.png](screenshots/desktop-home-loaded.png).
Fix: disable or reprice them; add a rule that products under a sane price floor cannot be visible.

**M1. Product carousels load images lazily with flat grey boxes** — Medium
Until a card scrolls into view it is an empty grey rectangle with a "New" badge and price, which reads as a broken product. Evidence: [mobile-home.png](screenshots/mobile-home.png).
Fix: eager-load the first row of each carousel; use a shimmer or blurred placeholder instead of a flat block.

**M2. "Browse by Designer" circles are blank on first paint** — Medium
Same lazy-loading behaviour as above; logos appear only after scroll. Evidence: [desktop-home.png](screenshots/desktop-home.png) vs [desktop-home-loaded.png](screenshots/desktop-home-loaded.png).

**L1. Section titles and rhythm** — Low
"Featured Products" and "All Products" are near-duplicates (the same items appear in both). Consider replacing "All Products" with something with a job: "New this week", "Under $150", or "For the wedding season".

### 2.2 Header, navigation and footer

**H3. Fifth category is clipped in the desktop header** — High
At 1440px the nav shows "DESIGNERS · OCCASION-BASED · AFRICAN MENSWEAR · AFRICAN WOMENSWEAR · AFRI…" with "African Accessories" cut off behind the search box. Evidence: [desktop-home.png](screenshots/desktop-home.png).
Fix: reduce the nav gaps at ≤1440px, shorten labels ("Menswear", "Womenswear", "Accessories", "Occasions"), or collapse the search to an icon until focused.

**H4. Mega-menu content is in the keyboard tab order and causes horizontal overflow** — High
Every desktop page reports 302px of horizontal overflow from the hidden mega-menu, and tabbing from the logo walks through nine invisible designer links and every category link before reaching the page. The "Designers" trigger itself has `href="#"`, `tabindex="-1"` and `pointer-events: none`, so it cannot be opened by keyboard or on touch laptops. File: `components/layouts/header/desktop/bottom.blade.php`.
Fix: render dropdown panels with `display:none`/`visibility:hidden` until open (or `inert`), make "Designers" a real button with `aria-expanded`, and clip the header with `overflow-x: clip`.

**M3. Language switcher with one language** — Medium
The top bar shows an "English" dropdown but only one locale is configured. The currency switcher (USD/NGN) is useful; the locale one is noise. Evidence: [desktop-locale-dropdown.png](screenshots/desktop-locale-dropdown.png).

**M4. Logo accessible name is "Bagisto"** — Medium
The logo link's `aria-label` uses the lang key `shop::app.components.layouts.header.bagisto`, so screen readers announce "Bagisto" and the keyboard audit shows `A:Bagisto` as the first link. Fix the translation string to "Aso Signature home".

**M5. Footer address errors and inconsistency** — Medium
Desktop footer: "Abuja 102215, Federal Capital Territory, **Nigeria1**". The mobile footer shows a *different* Nigeria address ("The Cans Park, Ibrahim Babangida Blvd, Maitama"). File: `components/layouts/footer/index.blade.php` lines 118 and 242.

**M6. Mobile footer collapses everything into "Footer Content"** — Medium
Policy links and contact details are hidden behind a generic accordion label; when opened, the three columns are so narrow that "Contact Us" wraps to two lines. Evidence: [mobile-footer-accordion-open.png](screenshots/mobile-footer-accordion-open.png).
Fix: stack the groups vertically on mobile, name the accordion ("Help & policies"), and keep contact details visible.

**L2. Newsletter copy** — Low
"Get Ready for our Fun Newsletter!" does not match the premium, hand-crafted tone used elsewhere. Suggest "Be first to see new designers and collections."

**L3. Casing of category names is inconsistent** — Low
The desktop nav forces uppercase, so the mixed data ("Occasion-Based", "AFRICAN MENSWEAR", "African Accessories") only shows in the mobile drawer. Normalise the names in the catalog. Evidence: [mobile-drawer-open.png](screenshots/mobile-drawer-open.png).

**L4. "Measurement Guide" appears four ways on one page** — Low
Top-bar pill, product-page link, footer link, drawer card. Good to promote, but pick two placements and make them consistent (pill in header, card in drawer).

### 2.3 Category, search and designer pages

**H5. Category banner images are broken** — High
`AFRICAN MENSWEAR` and `NEW IN` reference banner files that return 404 (`/storage/category/3/qkovim…webp`, `/storage/category/2/kcK3…webp`), so the page opens with a large empty grey panel on desktop and mobile. Evidence: [desktop-category-menswear.png](screenshots/desktop-category-menswear.png), [mobile-category-top-clean.png](screenshots/mobile-category-top-clean.png).
Fix: re-upload the banners in Admin → Catalog → Categories, and make the banner block render nothing when the file is missing.

**H6. Category descriptions are placeholders and there is no page heading** — High
Under the banner the only text is "Men Category" / "New In Collection". Category pages have no `<h1>`; the category name never appears on the page. Meta title is "Men's, Fashion"; meta description is empty.
Fix: render the category name as an `h1`, write two-line descriptions, fill meta fields.

**H7. Filters expose "HS Code" and clothing sizes on a made-to-measure store** — High
The layered navigation shows an **HS Code** filter ("6204.43 — Women's dress / kaftan (synthetic fibres)"), which is a customs classification, and a **Size S–XXL** filter that contradicts the "cut to your measurements" promise. The Colour filter has overlapping values (White / Crisp White, Black / Black Stripes, Blue / Deep Indigo Blue, Green / Emerald Green, Yellow / Golden Yellow). Evidence: [desktop-designer.png](screenshots/desktop-designer.png).
Fix: untick "Use in layered navigation" for HS Code and Size in Admin → Catalog → Attributes; consolidate colours to ~8 families; add filters shoppers actually use here (Gender, Occasion, Designer, Fabric).

**H8. Product names break mid-word** — High
Cards and the product `h1` use `break-all`, producing "Emerald Sunrise Kafta n Set", "Classic Kaftan tailored from premi um fabric", "Men's three-piece Blac k agbada". Files: `components/products/card.blade.php:120`, `products/view.blade.php:305`.
Fix: replace `break-all` with `break-words` (and `hyphens-auto` if desired).

**M7. Default sort is "Expensive First"** — Medium
Every listing and search opens sorted by price descending, so a $415 agbada is the first thing a new visitor sees. Set the storefront default to "Newest First" or a curated position order (Admin → Configuration → Catalog → Products → Storefront).

**M8. Search empty state uses category copy and offers nothing** — Medium
A no-result search shows a shopping-bag icon and "No products available in this category" with the full filter panel still open. Evidence: [desktop-search-empty.png](screenshots/desktop-search-empty.png).
Fix: "We couldn't find anything for 'zzzqqq'. Try a designer name or browse Menswear / Womenswear", plus a few popular products. Also tidy "Search results for : kaftan" → "Results for "kaftan"".

**M9. "Gender:Male" has no space and is low-value** — Medium
The card meta renders "Gender:Male" (the Vue template drops the whitespace between spans). It is also the only attribute shown, so cards read like a database record. Show designer name and a fit label ("Made to measure") instead. File: `components/products/card.blade.php:127-132`.

**M10. Designer page filter panel repeats the whole catalog filter set** — Medium
MIRAGE has four products but the page shows Price, 16 colours, five sizes and HS codes. Hide filters with fewer than two matching values.

**L5. Designer names** — Low
"H0USE OF MEKA" (digit zero) in the designers table (id 8) shows in the menu, drawer, carousel and URL slug `h0use-of-meka`. Evidence: [desktop-menu-designers-hover.png](screenshots/desktop-menu-designers-hover.png).

### 2.4 Product page

**H9. The page does not sell "made to your measurements"** — High
The buy box shows title, designer, price, a one-line blurb, "Production Timeline: Timeline varies by product", quantity and Add To Cart. Nothing tells a first-time visitor that the garment will be cut to their measurements, that measurements are collected at checkout, or how long it takes. The How to Measure link sits in the share row next to Compare. Evidence: [desktop-product.png](screenshots/desktop-product.png).
Fix: add a "Made to measure" module above the button: 3 bullets (send measurements at checkout · tailored by *Designer* · ships in *N* days), a "How measuring works" link, and the actual production timeline per product (the fallback text "Timeline varies by product" is a non-answer).

**M11. Reviews tab is blank** — Medium
With no reviews, the tab shows an empty area: no "No reviews yet" message and no "Write a review" prompt (guest and logged-in). Evidence: [desktop-pdp-reviews-tab.png](screenshots/desktop-pdp-reviews-tab.png). File: `products/view/reviews.blade.php`.

**M12. Compare is prominent for a bespoke store** — Medium
Compare appears in the header, on every card and on the product page. For one-of-a-kind, made-to-order pieces it adds clutter and a second "arrows" icon that is easily mistaken for share. Consider disabling `catalog.products.settings.compare_option`.

**M13. Mobile: no sticky Add To Cart** — Medium
On mobile the button scrolls away after the first screen; the page is long (description, reviews, footer). A sticky bottom bar with price + Add To Cart is standard here. Evidence: [mobile-product.png](screenshots/mobile-product.png).

**L6. Single product image, no zoom cue** — Low
Most products have 1–3 images. Encourage designers to supply front/back/detail; show the image count on cards.

**L7. Related / up-sell carousels render as empty headings** — Low
"Related Products" and "Up-sell" sections mount but there is no linked data, so they add whitespace. Hide when empty or fall back to "More from *Designer*".

### 2.5 Cart and mini-cart

**H10. Mini-cart promises a 30% discount** — High
"Get Up To 30% OFF on your 1st order" is Bagisto's default string (`lang/en/app.php:760`, key `offer-on-orders`). Unless there is such a promotion, this is a false claim. Evidence: [desktop-mini-cart.png](screenshots/desktop-mini-cart.png).

**M14. Cart shows "Delivery Charges $0.00" before any shipping is known** — Medium
Because DHL rates can be half the order value (see H12), a $0 line here sets up a shock at step 3. Show "Calculated at checkout" and expand the "Estimate Shipping" accordion by default, or show a country-based estimate.

**M15. Bulk-select UI on a one-item cart** — Medium
"☐ 0 Items Selected" with per-row checkboxes and an "Update Cart" button is a wholesale pattern. With ± steppers the update button is redundant. Evidence: [desktop-cart-with-item.png](screenshots/desktop-cart-with-item.png).

**M16. Cart says nothing about measurements** — Medium
This is the moment to reassure: "You'll add your measurements at the next step (takes ~3 minutes)".

**L8. Empty cart, wishlist and compare pages have no next step** — Low
"You don't have a product in your cart." / "No products were added to the wishlist page." / "You have no items in your compare list" — each needs a "Browse menswear / womenswear" button. Evidence: [desktop-cart-empty.png](screenshots/desktop-cart-empty.png), [desktop-account-wishlist.png](screenshots/desktop-account-wishlist.png).

### 2.6 Checkout

**C2. The measurement step can be skipped, silently** — Critical
With **no** profile, the step shows "No measurement profiles yet" and Proceed still opens Shipping. With an **incomplete** profile (54%, "6 measurement(s) missing", "Needs attention", "0 of 1 ready") Proceed still opens Shipping with no confirmation. A tailor will receive orders they cannot make. Evidence: [desktop-checkout-step-5.png](screenshots/desktop-checkout-step-5.png) (no profile), [desktop-checkout-after-measurement.png](screenshots/desktop-checkout-after-measurement.png) (incomplete profile). File: `checkout/onepage/measurement-step.blade.php` (Proceed is only disabled while loading).
Fix: disable Proceed until every item has a profile that meets the minimum set for its garment type; if the business wants to allow "send measurements later", make it an explicit choice with a clear consequence ("Production starts only once we have your measurements").

**H11. Guest checkout dead-ends at the login page** — High
Guest checkout is enabled in configuration, but "Proceed To Checkout" redirects to `/customer/login` with no message about why an account is needed (measurements). Evidence: [desktop-checkout-guest.png](screenshots/desktop-checkout-guest.png).
Fix: on the cart page, replace the link with "Sign in to add your measurements" + "Create account" side by side; on the login page show a one-line reason and keep the register form one click away; or allow guest checkout with an inline measurement form and create the account afterwards.

**H12. Shipping options leak carrier product names and raw timestamps; cost is a surprise** — High
Options read "EXPRESS WORLDWIDE - Delivery: 2026-09-18T23:59:00", "EXPRESS EASY - Delivery: 2026-09-18T23:59:00", and **"MEDICAL EXPRESS"** at $1,237.90. On a $960 cart the cheapest option was $480.31. On a smaller cart the fallback label was "DHL (Estimated) - Estimated shipping cost". Evidence: [desktop-checkout-shipping-selected.png](screenshots/desktop-checkout-shipping-selected.png). Source: `packages/Webkul/Shipping/src/Carriers/DHL.php:373-381`.
Fix: map DHL product codes to friendly names ("DHL Express — 3–5 business days"), format the date ("Arrives by Fri 18 Sep"), filter out non-retail products (medical, freight), and show a shipping estimate on the product page and cart.

**M17. Address form: "Save" instead of "Continue", placeholder-only labels for assistive tech** — Medium
The step's primary button is "Save" (it actually advances). Labels are visible but not linked to inputs (`for`/`id`), and the checkout address inputs carry no `aria-label`, so screen readers get only the placeholder. Labels: "Zip/Postcode", "Telephone" vs "Phone" elsewhere. Evidence: [desktop-checkout-auth-address.png](screenshots/desktop-checkout-auth-address.png).

**M18. Two `h1` elements ("Cart Summary") on the checkout page** — Medium
The mobile and desktop summary blocks are both in the DOM. Use `h2` for the summary and one `h1` "Checkout".

**M19. Payment method copy** — Medium
"Flutterwave — Flutterwave Gateway" reads like a dev label. "Pay with card, bank transfer or USSD (Flutterwave)" tells a Nigerian customer what they get. Stripe's line is good. Evidence: [desktop-checkout-shipping-selected.png](screenshots/desktop-checkout-shipping-selected.png).

**L9. Mobile checkout** — Low
The summary sits above the form, so the first screen is the order total, not the task. Consider a collapsed "Order summary ($960.00) ▾" bar. Evidence: [mobile-checkout-step-2.png](screenshots/mobile-checkout-step-2.png).

### 2.7 Measurements (account and guide)

**What works:** profiles with a progress bar, "Still needed" list, CM/Inches toggle, per-field help popovers with "Watch how to measure", a Smart Measurement path, and a checkout step that shows per-item readiness. This is the product's differentiator and it shows. Evidence: [desktop-account-measurements.png](screenshots/desktop-account-measurements.png), [desktop-measurement-help-popover.png](screenshots/desktop-measurement-help-popover.png).

**H13. The guide covers 8 measurements; the form asks for 13** — High
How to Measure teaches Neck, Shoulder, Chest, Waist, Hip, Sleeve, Top length, Inseam. The Women profile asks for Shoulder, Bust, Waist, Round Sleeve, Sleeve Length, Top Length, Hip, Skirt Length, Dress Length, Thighs/Laps, Round Knee and more. A customer following the guide finishes at ~50%. Evidence: [desktop-how-to-measure.png](screenshots/desktop-how-to-measure.png) vs [desktop-measurement-list-after-save.png](screenshots/desktop-measurement-list-after-save.png).
Fix: generate the guide from the same field definitions as the form (config `packages/Webkul/Shop/src/Config/how-to-measure.php`), with a Men/Women toggle that matches the editor.

**H14. Guide videos are missing** — High
The "Play video" cards swap in a native player that points at `/videos/ASO CLOTHING MALE.mp4` and `…FEMALE.mp4`; `public/videos` does not exist in this build, so the player shows 0:00 and nothing else. Evidence: [desktop-how-to-measure-video.png](screenshots/desktop-how-to-measure-video.png). Confirm the files are deployed, add a poster frame, and show a friendly error if a file is missing.

**M20. Measurement editor details** — Medium
- Empty fields show "0" as placeholder, which looks like an entered value.
- The "Save new profile" bar overlaps the last form row and the "Still needed…" line is truncated with an ellipsis.
- Saving with all fields empty gives no feedback (no error, no success).
- After saving under the "Men" tab, reopening the page shows the "Women" tab with empty fields (verify: the tab does not persist with the profile).
- Letters A–H on the guide icons are 10px.
Evidence: [desktop-measurement-filled.png](screenshots/desktop-measurement-filled.png), [desktop-measurement-list-after-save.png](screenshots/desktop-measurement-list-after-save.png).

**L10. A search box for eight measurements** — Low
The "Search a measurement — neck, waist, sleeve…" field on the guide is more UI than the content needs.

### 2.8 Account

**H15. Account home is empty** — High
`/customer/account` shows the side navigation, a divider, and a "Logout" button. Page title is "Orders". Evidence: [desktop-account-home.png](screenshots/desktop-account-home.png). File: `customers/account/index.blade.php`.
Fix: a dashboard with measurement completeness, latest order status, default address, and quick links, or redirect to Orders.

**M21. Page titles and headings** — Medium
Addresses page title is "Add Address"; no account page has an `h1`; profile edit uses "Add Image/Video" for an avatar; Gender is required on profile edit but empty for existing customers, so the first save fails. Evidence: [desktop-account-profile-edit.png](screenshots/desktop-account-profile-edit.png).

**M22. Empty orders/reviews/wishlist tables** — Medium
"No Records Available." in a datagrid with a search box and a Filter button is enterprise UI. Replace with an empty state and a "Start shopping" button.

### 2.9 Auth and forms

**M23. Register page copy** — Medium
Title "Become User"; subtitle "If you are new to our store, we glad to have you as member." (`lang/en/app.php:59-61`). Submitting empty shows "The Password field confirmation does not match" alongside "required". No "Show password" toggle (the login page has one). Evidence: [desktop-register-validation.png](screenshots/desktop-register-validation.png).

**M24. Login error clears the form** — Medium
A wrong password returns a toast in the top-right and empties both fields; the email must be retyped. Keep the email, put the error under the form. Evidence: [desktop-login-wrong-password.png](screenshots/desktop-login-wrong-password.png).

**L11. Brand name inconsistency** — Low
Auth pages' footer says "AsoSignature"; everywhere else "Aso Signature".

**Works well:** inline validation on contact and newsletter forms, the terms checkbox on sign-up, and the forgot-password flow. Evidence: [desktop-contact-validation.png](screenshots/desktop-contact-validation.png), [desktop-newsletter-invalid.png](screenshots/desktop-newsletter-invalid.png).

### 2.10 Content pages

**How It Works** — good narrative, seven clear steps, matching illustrations. It is 7,200px tall on desktop and 9,900px on mobile because every step gets a full screen of whitespace. Halve the vertical gaps and consider a two-column desktop layout. The closing CTA ("Start your design / Read the measurement guide") is right. Evidence: [desktop-how-it-works.png](screenshots/desktop-how-it-works.png).

**How to Measure** — the strongest page on the site. Prerequisites, A–H picker, per-measurement tips with a "Tip" callout, prev/next, modal with "View full page", closes on Escape, locks background scroll. Evidence: [desktop-how-to-measure-modal.png](screenshots/desktop-how-to-measure-modal.png).

**About / Terms / policies** — no `h1`, no meta description; the "About Us" tab title is lowercase "about us"; the "Our Promise" list is plain lines rather than a list. Evidence: [desktop-cms-about.png](screenshots/desktop-cms-about.png).

**Contact** — clear, but the contact block is repeated in the footer directly below it. Evidence: [desktop-contact-us.png](screenshots/desktop-contact-us.png).

### 2.11 Error pages

**H16. Unbranded 404** — High
`/this-page-does-not-exist` renders Laravel's bare "404 | NOT FOUND" with no header, logo, or link home. The theme ships a branded error page (`packages/Webkul/Shop/src/Resources/views/errors/index.blade.php`) but `app/Exceptions/Handler.php` extends Laravel's base handler instead of `Webkul\Core\Exceptions\Handler`, so it is never rendered. The same applies to 403/500/503. Evidence: [desktop-not-found.png](screenshots/desktop-not-found.png).

### 2.12 Accessibility

Positives: a "Skip to main content" link, visible focus rings on every focused element, sensible heading fonts, `lang="en"`.

- **Form labels are not programmatically associated.** The label component renders `<label>` without `for`; inputs get an `id` but no matching label. Sign-up and contact inputs have `aria-label`, so they are usable; checkout address, profile edit and account address forms do not, leaving placeholder-only names.
- **Tap targets below 40px on mobile:** header icons (compare, cart, account) 24px; wishlist/compare icons on every card 24px; quantity ± 24px; "View All" 71×20px. Target 44px.
- **Hidden content in the tab order:** mega-menu links, inactive hero slide links, and each designer in the carousel is focusable twice (image + name).
- **"Designers" nav item is not keyboard or touch operable** (see H4).
- **Duplicate/missing `h1`:** none on category, CMS, cart and account pages; two on checkout.
- **Toast-only errors** (login) are not announced; use `role="alert"` or an inline message.
- **Mobile search input** has a label `for="organic-search"` but the input has no matching `id`.

### 2.13 Performance and SEO

| Page | Transfer | Images | Requests | Note |
|---|---|---|---|---|
| Home (desktop) | 1.78 MB | 0.94 MB | 41 | Two hero WebPs plus designer logos |
| Home (mobile) | 1.72 MB | 0.88 MB | 41 | Same assets served to mobile |
| Product "Timeless Kaftan" | 0.66 MB | 0.61 MB | 55 | One 588 KB WebP |
| Category (menswear) | 0.12 MB | 0.06 MB | 56 | Fast; images lazy |

- Serve hero and product images with `srcset`/`sizes` so mobile gets ~800px variants; cap product images at ~200 KB.
- Server timing is excellent locally (TTFB 70–200 ms); the designer page's DOMContentLoaded was 2.9 s once, worth a second look.
- SEO: meta descriptions are empty on CMS, contact and account pages; category meta title "Men's, Fashion"; the 404 lacks a skip link; product JSON-LD is enabled (good).

### 2.14 Content and data quality checklist

- [ ] Remove or reprice the three `$2.00` products.
- [ ] Fix "H0USE OF MEKA" → "HOUSE OF MEKA" (and its slug).
- [ ] Footer "Nigeria1"; align the mobile and desktop Nigeria addresses.
- [ ] Remove "Get Up To 30% OFF" or replace with a real offer.
- [ ] Category descriptions ("Men Category", "New In Collection") and meta fields.
- [ ] Very short product blurbs ("Boubou", "Agbada Senator Set") — several products have under 40 characters of short description.
- [ ] Production timeline per product (currently all fall back to "Timeline varies by product").
- [ ] Confirm `APP_DEBUG=false` and Debugbar disabled in production.
- [ ] Confirm the two guide videos exist under `public/videos` on the server.

---

## 3. What is working well

- **Brand and typography.** The serif display face, purple/navy palette and cream footer feel considered and consistent across pages.
- **How to Measure** (page and modal) is genuinely helpful and unusually well written.
- **Measurement profiles**: progress, unit toggle, help popovers, "Watch how to measure", Smart Measurement, and per-item readiness in checkout.
- **Designer mega menu and designer pages** with logos give the marketplace angle a face.
- **How It Works** explains the service in seven honest steps.
- **Forms** validate inline with clear messages; newsletter and contact behave correctly.
- **Sticky header, product-card hover actions, mini-cart slide-over, currency switcher** all behave as expected.
- **Mobile basics** are sound: drawer navigation, bottom Filter/Sort bar on listings, readable type sizes.

---

## 4. Recommended order of work

### Week 1 — configuration, copy and content (no code or trivial code)
1. Fix hero CTA links (C1) and choose one hero message (H1).
2. Replace the 30% OFF string (H10); fix register copy (M23); "Bagisto" aria-label (M4); "Nigeria1" and the mobile address (M5).
3. Disable/reprice test products (H2); fix the designer name (L5).
4. Re-upload category banners; write category descriptions and meta (H5, H6).
5. Remove HS Code and Size from layered navigation; consolidate colours (H7). Set default sort to Newest (M7).
6. `break-all` → `break-words` on cards and PDP (H8). Add the missing space in "Gender: Male" (M9).
7. Remove the language switcher (M3).

### Sprint 1 — flows that lose orders
1. Gate the checkout measurement step (C2).
2. Explain the login requirement and offer inline sign-up from the cart (H11).
3. Friendly DHL names, formatted dates, filter out non-retail products; show a shipping estimate on PDP and cart (H12, M14).
4. Extend `Webkul\Core\Exceptions\Handler` so branded 404/500/503 pages render (H16).
5. Account dashboard or redirect (H15); empty states with CTAs (L8, M22).
6. Keep the email on failed login; inline error (M24). Reviews tab empty state (M11).

### Sprint 2 — experience and accessibility
1. "Made to measure" module on the product page with per-product timeline (H9); sticky mobile Add To Cart (M13).
2. Generate the guide from the same 13-field definitions as the form; deploy videos with posters (H13, H14).
3. Header: fit five categories at 1440px, make "Designers" a real button, keep hidden menus out of the tab order and clip overflow (H3, H4).
4. Label/`for` association and `aria-label`s across checkout, profile and address forms; 44px tap targets; `inert` on hidden slides (2.12).
5. Responsive images and a 200 KB cap (2.13).
6. Tighten How It Works spacing; restructure the mobile footer (2.10, M6).

---

## 5. Appendix

### Pages and flows covered

Home (both hero slides, mega menus, designer menu, account dropdown, keyboard walk) · Category: African Menswear, African Womenswear, New In · Product: Classic Kaftan, Timeless Kaftan · Designer: MIRAGE · How It Works · How to Measure (page, modal from header and drawer, video cards) · Contact (incl. empty submit) · About · Terms · Search (results, empty, header search) · Login (incl. wrong password) · Register (incl. empty submit) · Forgot password · Cart (empty, with item, mini-cart) · Checkout as guest · Checkout as customer through Address → Measurements (no profile and incomplete profile) → Shipping → Payment (not placed) · Account: home, profile, edit profile, addresses, add address, orders, measurements (create/save profile, help popover), wishlist (add/view), reviews · Compare · 404 · Thank-you redirect · Newsletter invalid email · Currency and locale dropdowns · Logout.

### Screenshot index

All files are in [`screenshots/`](screenshots/), prefixed `desktop-` or `mobile-`. Suffix `-loaded` or `-clean` means the page was scrolled first so lazy images loaded (and, for `-clean`, the Debugbar was hidden).

| Area | Files |
|---|---|
| Home | `home`, `home-loaded`, `home-clean`, `hero-slide-2`, `home-keyboard-focus`, `menu-category-hover`, `menu-designers-hover`, `account-dropdown-guest`, `account-dropdown-auth`, `currency-dropdown`, `locale-dropdown` |
| Listing | `category-menswear(-loaded)`, `category-womenswear-loaded`, `category-new-in`, `category-top-clean`, `category-scrolled-clean`, `card-hover`, `designer(-loaded)`, `search-results(-loaded)`, `search-empty`, `header-search-agbada` |
| Product | `product`, `product-cheap`, `pdp-reviews-tab`, `pdp-buybox-clean`, `product-added-flash`, `product-added-viewport`, `wishlist-added-flash` |
| Cart / checkout | `cart-empty`, `cart-with-item`, `mini-cart`, `checkout-guest`, `checkout-auth-address(-filled)`, `checkout-step-2…5`, `checkout-measurement-step`, `checkout-after-measurement`, `checkout-shipping-selected`, `checkout-payment-step`, `checkout-final` |
| Measurements | `account-measurements`, `measurement-editor`, `measurement-help-popover`, `measurement-filled`, `measurement-after-save`, `measurement-list-after-save`, `measurement-save-empty` |
| Account | `account-home`, `account-profile`, `account-profile-edit`, `account-addresses`, `account-address-create`, `account-orders`, `account-wishlist`, `wishlist-with-item`, `account-reviews`, `after-login`, `after-logout` |
| Auth & forms | `login`, `login-wrong-password`, `register`, `register-validation`, `forgot-password`, `contact-us`, `contact-validation`, `newsletter-invalid` |
| Content | `how-it-works`, `how-to-measure`, `how-to-measure-modal`, `how-to-measure-video`, `cms-about`, `cms-terms`, `compare`, `not-found`, `thank-you` |
| Mobile-only | `drawer-open`, `drawer-category-expanded`, `drawer-designers`, `drawer-auth`, `category-viewport`, `footer-accordion-open` |

### Files

- `diagnostics.json` — per-page title, headings, overflow, broken images, unnamed controls, small tap targets, unlabeled inputs, tiny text, console errors, failed requests, timing and transfer sizes (pass 1).
- `pass2-notes.txt`, `pass3-notes.txt` — flow logs from passes 2 and 3.
- `scripts/` — the four Playwright scripts. Run with `node scripts/ux-audit.mjs` from the repo root (they use the Playwright bundled in `packages/Webkul/Shop/node_modules`). They create a test customer `ux.reviewer@example.com`; that account and its data were removed after this review.

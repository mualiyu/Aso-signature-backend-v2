// UX audit crawler for the Aso Signature storefront.
// Walks pages + flows at desktop and mobile widths, saves screenshots and a diagnostics JSON.
import { chromium } from '/Users/mukeey/projects/Aso Signature/Aso-signature-backend-v2/packages/Webkul/Shop/node_modules/playwright/index.mjs';
import fs from 'node:fs';
import path from 'node:path';

const BASE = 'http://aso-signature-backend-v2.test';
const OUT = '/Users/mukeey/projects/Aso Signature/Aso-signature-backend-v2/ux-review/screenshots';
const EXEC = '/Users/mukeey/Library/Caches/ms-playwright/chromium_headless_shell-1234/chrome-headless-shell-mac-arm64/chrome-headless-shell';
const CUSTOMER = { email: 'ux.reviewer@example.com', password: 'Reviewer123!' };
const PRODUCT = '/classic-kaftan-tailored-from-premium-fabric';

fs.mkdirSync(OUT, { recursive: true });
const results = [];
const log = (...a) => console.log(...a);

const VIEWPORTS = {
  desktop: { viewport: { width: 1440, height: 900 }, deviceScaleFactor: 1 },
  mobile: { viewport: { width: 390, height: 844 }, deviceScaleFactor: 2, isMobile: true, hasTouch: true,
    userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1' },
};

function attachCollectors(page) {
  const state = { consoleErrors: [], pageErrors: [], failedRequests: [] };
  page.on('console', (m) => { if (m.type() === 'error') state.consoleErrors.push(m.text().slice(0, 200)); });
  page.on('pageerror', (e) => state.pageErrors.push(String(e.message).slice(0, 200)));
  page.on('response', (r) => { if (r.status() >= 400) state.failedRequests.push(`${r.status()} ${r.url().replace(BASE, '')}`.slice(0, 160)); });
  page.__state = state;
  return state;
}
function resetCollectors(page) { const s = page.__state; s.consoleErrors = []; s.pageErrors = []; s.failedRequests = []; }

async function settle(page, ms = 1800) {
  await page.waitForLoadState('networkidle', { timeout: 12000 }).catch(() => {});
  await page.waitForTimeout(ms);
}

async function goto(page, url, ms) {
  resetCollectors(page);
  const t0 = Date.now();
  const resp = await page.goto(BASE + url, { waitUntil: 'domcontentloaded', timeout: 45000 }).catch((e) => ({ error: e.message }));
  await settle(page, ms);
  return { status: resp?.status?.() ?? null, error: resp?.error ?? null, navMs: Date.now() - t0 };
}

async function diagnostics(page) {
  return page.evaluate(() => {
    const vw = document.documentElement.clientWidth;
    const sw = document.documentElement.scrollWidth;
    const isVisible = (el) => { const r = el.getBoundingClientRect(); const s = getComputedStyle(el); return r.width > 0 && r.height > 0 && s.visibility !== 'hidden' && s.display !== 'none' && s.opacity !== '0'; };
    const imgs = [...document.images];
    const broken = imgs.filter((i) => i.complete && i.naturalWidth === 0 && i.getAttribute('src') && !i.getAttribute('src').startsWith('data:')).map((i) => i.getAttribute('src')).slice(0, 10);
    const noAlt = imgs.filter((i) => !i.hasAttribute('alt') && isVisible(i)).length;
    const h1 = [...document.querySelectorAll('h1')].map((h) => h.textContent.trim().replace(/\s+/g, ' ')).filter(Boolean);
    const headings = [...document.querySelectorAll('h1,h2,h3')].filter(isVisible).map((h) => h.tagName + ': ' + h.textContent.trim().replace(/\s+/g, ' ').slice(0, 50)).slice(0, 25);
    const accName = (el) => (el.getAttribute('aria-label') || el.textContent || el.getAttribute('title') || (el.querySelector('img') && el.querySelector('img').alt) || '').trim();
    const interactive = [...document.querySelectorAll('a[href], button, [role=button], input:not([type=hidden]), select, textarea')].filter(isVisible);
    const unnamed = interactive.filter((el) => el.tagName === 'A' || el.tagName === 'BUTTON' || el.getAttribute('role') === 'button').filter((el) => !accName(el)).map((el) => el.outerHTML.replace(/\s+/g, ' ').slice(0, 110)).slice(0, 8);
    const smallTargets = interactive.filter((el) => { const r = el.getBoundingClientRect(); return r.width < 40 || r.height < 40; });
    const smallSamples = smallTargets.filter((el) => { const r = el.getBoundingClientRect(); return r.width < 32 || r.height < 32; }).map((el) => ({ tag: el.tagName, name: accName(el).replace(/\s+/g, ' ').slice(0, 30), w: Math.round(el.getBoundingClientRect().width), h: Math.round(el.getBoundingClientRect().height) })).slice(0, 12);
    const inputs = [...document.querySelectorAll('input:not([type=hidden]):not([type=submit]):not([type=checkbox]):not([type=radio]), select, textarea')].filter(isVisible);
    const unlabeled = inputs.filter((i) => { if (i.getAttribute('aria-label') || i.getAttribute('aria-labelledby')) return false; if (i.id && document.querySelector(`label[for="${CSS.escape(i.id)}"]`)) return false; if (i.closest('label')) return false; return true; }).map((i) => `${i.tagName.toLowerCase()}[name=${i.name || '?'}] placeholder="${i.placeholder || ''}"`).slice(0, 10);
    const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
    let tiny = 0; const tinySamples = [];
    while (walker.nextNode()) { const n = walker.currentNode; if (!n.textContent.trim()) continue; const el = n.parentElement; if (!el || !isVisible(el)) continue; const fs = parseFloat(getComputedStyle(el).fontSize); if (fs < 12) { tiny++; if (tinySamples.length < 6) tinySamples.push({ text: n.textContent.trim().replace(/\s+/g, ' ').slice(0, 40), fs }); } }
    const body = document.body.innerText || '';
    const leaks = ['Bagisto', 'Lorem', 'lorem ipsum', 'undefined', 'NaN', '{{', 'TODO', 'Nigeria1', 'Lorem ipsum', 'test', 'Test'].filter((w) => new RegExp(`\\b${w}\\b`).test(body));
    const metaDesc = document.querySelector('meta[name=description]')?.content || '';
    const overflowing = [...document.querySelectorAll('body *')].filter((el) => { const r = el.getBoundingClientRect(); return r.right > vw + 2 && r.width > 0 && isVisible(el); }).map((el) => el.tagName.toLowerCase() + (typeof el.className === 'string' && el.className ? '.' + el.className.trim().split(/\s+/).slice(0, 3).join('.') : '')).slice(0, 6);
    const nav = performance.getEntriesByType('navigation')[0];
    const res = performance.getEntriesByType('resource');
    const bytes = res.reduce((s, r) => s + (r.transferSize || 0), 0);
    const imgBytes = res.filter((r) => r.initiatorType === 'img' || /\.(png|jpe?g|webp|gif|svg)(\?|$)/i.test(r.name)).reduce((s, r) => s + (r.transferSize || 0), 0);
    const bigImages = res.filter((r) => (r.transferSize || 0) > 400 * 1024 && /\.(png|jpe?g|webp|gif)(\?|$)/i.test(r.name)).map((r) => `${Math.round(r.transferSize / 1024)}KB ${r.name.split('/').slice(-2).join('/')}`).slice(0, 8);
    const skipLink = !!document.querySelector('a[href="#main"], a.skip-link, a[href^="#"][class*="skip"]');
    const lang = document.documentElement.lang;
    const buttons = [...document.querySelectorAll('button, a.primary-button, a.secondary-button, [class*="primary-button"], [class*="secondary-button"]')].filter(isVisible).map((b) => accName(b).replace(/\s+/g, ' ').slice(0, 30)).filter(Boolean);
    return {
      title: document.title.trim(), lang, metaDescLen: metaDesc.length, h1, headings,
      horizontalOverflowPx: Math.max(0, sw - vw), overflowing,
      brokenImages: broken, imagesMissingAlt: noAlt, unnamedControls: unnamed,
      smallTargets: smallTargets.length, smallSamples, unlabeledInputs: unlabeled,
      tinyTextNodes: tiny, tinySamples, textLeaks: leaks, skipLink,
      domNodes: document.getElementsByTagName('*').length,
      perf: nav ? { domContentLoadedMs: Math.round(nav.domContentLoadedEventEnd), loadMs: Math.round(nav.loadEventEnd), ttfbMs: Math.round(nav.responseStart), requests: res.length, transferKB: Math.round(bytes / 1024), imageKB: Math.round(imgBytes / 1024), bigImages } : null,
      buttons: [...new Set(buttons)].slice(0, 20),
    };
  });
}

async function capture(page, name, { fullPage = true, nav = null, note = '' } = {}) {
  const file = `${name}.png`;
  await page.screenshot({ path: path.join(OUT, file), fullPage, timeout: 30000 }).catch((e) => log('  screenshot failed', name, e.message));
  let diag = null;
  try { diag = await diagnostics(page); } catch (e) { diag = { error: e.message }; }
  const s = page.__state;
  const entry = { name, file, url: page.url().replace(BASE, '') || '/', note, nav, diag, consoleErrors: [...new Set(s.consoleErrors)].slice(0, 8), pageErrors: [...new Set(s.pageErrors)].slice(0, 5), failedRequests: [...new Set(s.failedRequests)].slice(0, 8) };
  results.push(entry);
  log(`  [${name}] ${entry.url} h1=${JSON.stringify(diag?.h1)} overflow=${diag?.horizontalOverflowPx} broken=${diag?.brokenImages?.length} errs=${entry.consoleErrors.length}/${entry.pageErrors.length} failed=${entry.failedRequests.length}`);
  return entry;
}

async function tryStep(label, fn) {
  try { await fn(); } catch (e) { log(`  !! ${label} failed: ${e.message.split('\n')[0]}`); results.push({ name: label, error: e.message.split('\n')[0] }); }
}

async function login(page) {
  await goto(page, '/customer/login', 800);
  await page.fill('input[name="email"]', CUSTOMER.email);
  await page.fill('input[name="password"]', CUSTOMER.password);
  await Promise.all([page.waitForNavigation({ timeout: 20000 }).catch(() => {}), page.click('button[type="submit"]')]);
  await settle(page, 1000);
}

async function addProductToCart(page) {
  await goto(page, PRODUCT, 1500);
  const btn = page.locator('button:has-text("Add To Cart")').first();
  await btn.waitFor({ timeout: 15000 });
  await btn.click();
  await page.waitForTimeout(2500);
}

async function keyboardFocusAudit(page) {
  await page.evaluate(() => window.scrollTo(0, 0));
  const seq = [];
  for (let i = 0; i < 14; i++) {
    await page.keyboard.press('Tab');
    await page.waitForTimeout(80);
    const info = await page.evaluate(() => {
      const el = document.activeElement; if (!el || el === document.body) return { tag: 'BODY' };
      const s = getComputedStyle(el);
      const ring = (s.outlineStyle !== 'none' && parseFloat(s.outlineWidth) > 0) || (s.boxShadow && s.boxShadow !== 'none');
      const r = el.getBoundingClientRect();
      return { tag: el.tagName, name: (el.getAttribute('aria-label') || el.textContent || el.getAttribute('placeholder') || '').trim().replace(/\s+/g, ' ').slice(0, 30), visibleRing: ring, onScreen: r.top >= 0 && r.bottom <= innerHeight && r.width > 0 };
    });
    seq.push(info);
  }
  return seq;
}

const PAGES = [
  ['home', '/'],
  ['category-menswear', '/african-menswear'],
  ['category-new-in', '/new-in'],
  ['product', PRODUCT],
  ['product-cheap', '/timeless-kaftan-made-from-superior-fabric'],
  ['designer', '/designer/mirage'],
  ['how-it-works', '/how-it-works'],
  ['how-to-measure', '/how-to-measure'],
  ['contact-us', '/contact-us'],
  ['cms-about', '/page/about-us'],
  ['cms-terms', '/page/terms-conditions'],
  ['search-results', '/search?query=kaftan'],
  ['search-empty', '/search?query=zzzqqq'],
  ['login', '/customer/login'],
  ['register', '/customer/register'],
  ['forgot-password', '/customer/forgot-password'],
  ['cart-empty', '/checkout/cart'],
  ['compare', '/compare'],
  ['not-found', '/this-page-does-not-exist'],
  ['thank-you', '/thank-you'],
];

const browser = await chromium.launch({ executablePath: EXEC, headless: true });

for (const [vpName, vp] of Object.entries(VIEWPORTS)) {
  log(`\n=== ${vpName.toUpperCase()} ===`);
  const ctx = await browser.newContext({ ...vp, locale: 'en-US' });
  const page = await ctx.newPage();
  attachCollectors(page);
  const P = (n) => `${vpName}-${n}`;

  // --- Static pages (guest) ---
  for (const [name, url] of PAGES) {
    await tryStep(P(name), async () => { const nav = await goto(page, url); await capture(page, P(name), { nav }); });
  }

  // --- Home interactions ---
  await tryStep(P('home-keyboard'), async () => {
    await goto(page, '/', 1200);
    const seq = await keyboardFocusAudit(page);
    results.push({ name: P('home-keyboard'), keyboard: seq });
    log('  keyboard:', seq.map((s) => `${s.tag}:${s.name || ''}${s.visibleRing ? '' : '(no ring)'}`).join(' > '));
    await page.screenshot({ path: path.join(OUT, `${P('home-keyboard-focus')}.png`) });
  });

  if (vpName === 'desktop') {
    await tryStep(P('menu-designers'), async () => {
      await goto(page, '/', 1200);
      await page.hover('text=Designers');
      await page.waitForTimeout(700);
      await capture(page, P('menu-designers-hover'), { fullPage: false });
    });
    await tryStep(P('menu-category'), async () => {
      await goto(page, '/', 1200);
      const cat = page.locator('a:has-text("AFRICAN MENSWEAR")').first();
      await cat.hover();
      await page.waitForTimeout(700);
      await capture(page, P('menu-category-hover'), { fullPage: false });
    });
    await tryStep(P('account-dropdown'), async () => {
      await goto(page, '/', 1200);
      await page.click('[aria-label="Profile"], .icon-users >> nth=0');
      await page.waitForTimeout(500);
      await capture(page, P('account-dropdown-guest'), { fullPage: false });
    });
  } else {
    await tryStep(P('drawer'), async () => {
      await goto(page, '/', 1200);
      await page.click('.icon-hamburger');
      await page.waitForTimeout(900);
      await capture(page, P('drawer-open'), { fullPage: false });
      const cat = page.locator('a:has-text("AFRICAN MENSWEAR")').first();
      if (await cat.count()) { await cat.locator('xpath=..').locator('span').first().click().catch(() => {}); await page.waitForTimeout(500); await capture(page, P('drawer-category-open'), { fullPage: false }); }
    });
    await tryStep(P('drawer-designers'), async () => {
      await goto(page, '/', 1200);
      await page.click('.icon-hamburger');
      await page.waitForTimeout(700);
      await page.locator('span:has-text("Designers")').first().click();
      await page.waitForTimeout(900);
      await capture(page, P('drawer-designers'), { fullPage: false });
    });
    await tryStep(P('category-filters'), async () => {
      await goto(page, '/african-menswear', 1500);
      const f = page.locator('text=/^Filter/i').first();
      if (await f.count()) { await f.click(); await page.waitForTimeout(800); await capture(page, P('category-filter-drawer'), { fullPage: false }); }
    });
  }

  // --- Footer "How to measure" modal ---
  await tryStep(P('how-to-measure-modal'), async () => {
    await goto(page, '/', 1200);
    const link = page.locator('a[href$="/how-to-measure"]').last();
    await link.scrollIntoViewIfNeeded();
    await link.click();
    await page.waitForTimeout(1200);
    await capture(page, P('how-to-measure-modal'), { fullPage: false });
  });

  // --- Search from header ---
  await tryStep(P('header-search'), async () => {
    await goto(page, '/', 1000);
    await page.fill('input[name="query"] >> visible=true', 'agbada');
    await page.keyboard.press('Enter');
    await settle(page, 1500);
    await capture(page, P('header-search-agbada'));
  });

  // --- Register: empty submit validation ---
  await tryStep(P('register-validation'), async () => {
    await goto(page, '/customer/register', 800);
    await page.click('button[type="submit"]');
    await page.waitForTimeout(800);
    await capture(page, P('register-validation'));
  });

  // --- Contact: empty submit ---
  await tryStep(P('contact-validation'), async () => {
    await goto(page, '/contact-us', 800);
    await page.click('button[type="submit"]');
    await page.waitForTimeout(800);
    await capture(page, P('contact-validation'));
  });

  // --- Login: wrong password ---
  await tryStep(P('login-wrong'), async () => {
    await goto(page, '/customer/login', 800);
    await page.fill('input[name="email"]', CUSTOMER.email);
    await page.fill('input[name="password"]', 'wrong-password');
    await Promise.all([page.waitForNavigation({ timeout: 20000 }).catch(() => {}), page.click('button[type="submit"]')]);
    await settle(page, 800);
    await capture(page, P('login-wrong-password'));
  });

  // --- Guest add-to-cart -> cart -> checkout ---
  await tryStep(P('guest-cart'), async () => {
    await addProductToCart(page);
    await capture(page, P('product-added-flash'), { fullPage: false });
    const cartIcon = page.locator('.icon-cart, [aria-label*="Cart"], [aria-label*="cart"]').first();
    if (await cartIcon.count()) { await cartIcon.click(); await page.waitForTimeout(900); await capture(page, P('mini-cart'), { fullPage: false }); await page.keyboard.press('Escape'); }
    await goto(page, '/checkout/cart', 1500);
    await capture(page, P('cart-with-item'));
    const proceed = page.locator('text=Proceed To Checkout').first();
    await proceed.click();
    await settle(page, 2000);
    await capture(page, P('checkout-guest'));
  });

  // --- Logged-in flows ---
  await tryStep(P('login-ok'), async () => { await login(page); await capture(page, P('after-login')); });

  for (const [name, url] of [
    ['account-home', '/customer/account'],
    ['account-profile', '/customer/account/profile'],
    ['account-profile-edit', '/customer/account/profile/edit'],
    ['account-addresses', '/customer/account/addresses'],
    ['account-address-create', '/customer/account/addresses/create'],
    ['account-orders', '/customer/account/orders'],
    ['account-measurements', '/customer/account/measurements'],
    ['account-wishlist', '/customer/account/wishlist'],
    ['account-reviews', '/customer/account/reviews'],
  ]) {
    await tryStep(P(name), async () => { const nav = await goto(page, url); await capture(page, P(name), { nav }); });
  }

  await tryStep(P('account-dropdown-auth'), async () => {
    if (vpName !== 'desktop') return;
    await goto(page, '/', 1200);
    await page.click('[aria-label="Profile"], .icon-users >> nth=0');
    await page.waitForTimeout(500);
    await capture(page, P('account-dropdown-auth'), { fullPage: false });
  });

  await tryStep(P('measurement-create'), async () => {
    await goto(page, '/customer/account/measurements', 1500);
    const add = page.locator('button:has-text("Add"), a:has-text("Add"), button:has-text("Create"), a:has-text("Create"), button:has-text("New"), a:has-text("New")').first();
    if (await add.count()) { await add.click(); await page.waitForTimeout(1200); await capture(page, P('measurement-editor')); }
  });

  await tryStep(P('wishlist-add'), async () => {
    await goto(page, PRODUCT, 1500);
    await page.click('[aria-label="Add To Wishlist"], [aria-label="Add to Wishlist"], .icon-heart >> nth=0');
    await page.waitForTimeout(1200);
    await capture(page, P('wishlist-added-flash'), { fullPage: false });
  });

  await tryStep(P('auth-checkout'), async () => {
    await addProductToCart(page);
    await goto(page, '/checkout/onepage', 2500);
    await capture(page, P('checkout-auth-address'));
    // Best-effort fill address
    const fill = async (sel, val) => { const l = page.locator(sel).first(); if (await l.count() && await l.isVisible()) await l.fill(val).catch(() => {}); };
    await fill('input[name="billing.first_name"]', 'UX');
    await fill('input[name="billing.last_name"]', 'Reviewer');
    await fill('input[name="billing.email"]', CUSTOMER.email);
    await fill('input[name="billing.address.[0]"]', '600 N Broad Street');
    const country = page.locator('select[name="billing.country"]').first();
    if (await country.count()) { await country.selectOption('US').catch(() => {}); await page.waitForTimeout(800); }
    const state = page.locator('select[name="billing.state"]').first();
    if (await state.count() && await state.isVisible()) await state.selectOption({ index: 1 }).catch(() => {});
    else await fill('input[name="billing.state"]', 'Delaware');
    await fill('input[name="billing.city"]', 'Middletown');
    await fill('input[name="billing.postcode"]', '19709');
    await fill('input[name="billing.phone"]', '2029923505');
    await capture(page, P('checkout-auth-address-filled'));
    const proceed = page.locator('button:has-text("Proceed")').first();
    if (await proceed.count()) { await proceed.click(); await page.waitForTimeout(3500); await capture(page, P('checkout-auth-step2')); }
    const proceed2 = page.locator('button:has-text("Proceed")').last();
    if (await proceed2.count() && await proceed2.isVisible()) { await proceed2.click(); await page.waitForTimeout(3500); await capture(page, P('checkout-auth-step3')); }
  });

  await ctx.close();
}

await browser.close();
fs.writeFileSync(path.join(OUT, '..', 'diagnostics.json'), JSON.stringify(results, null, 2));
log('\nDONE. entries:', results.length);

// Pass 2: lazy-image re-shoots + the flows that failed in pass 1 + deeper checkout/measurement flows.
import { chromium } from '/Users/mukeey/projects/Aso Signature/Aso-signature-backend-v2/packages/Webkul/Shop/node_modules/playwright/index.mjs';
import fs from 'node:fs';
import path from 'node:path';

const BASE = 'http://aso-signature-backend-v2.test';
const OUT = '/Users/mukeey/projects/Aso Signature/Aso-signature-backend-v2/ux-review/screenshots';
const EXEC = '/Users/mukeey/Library/Caches/ms-playwright/chromium_headless_shell-1234/chrome-headless-shell-mac-arm64/chrome-headless-shell';
const CUSTOMER = { email: 'ux.reviewer@example.com', password: 'Reviewer123!' };
const PRODUCT = '/classic-kaftan-tailored-from-premium-fabric';
const notes = [];
const log = (...a) => { console.log(...a); notes.push(a.join(' ')); };

const VIEWPORTS = {
  desktop: { viewport: { width: 1440, height: 900 }, deviceScaleFactor: 1 },
  mobile: { viewport: { width: 390, height: 844 }, deviceScaleFactor: 2, isMobile: true, hasTouch: true,
    userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1' },
};

async function settle(page, ms = 1500) { await page.waitForLoadState('networkidle', { timeout: 12000 }).catch(() => {}); await page.waitForTimeout(ms); }
async function goto(page, url, ms) { await page.goto(BASE + url, { waitUntil: 'domcontentloaded', timeout: 45000 }); await settle(page, ms); }
async function scrollThrough(page) {
  await page.evaluate(async () => { const h = document.body.scrollHeight; for (let y = 0; y < h; y += 450) { window.scrollTo(0, y); await new Promise((r) => setTimeout(r, 150)); } window.scrollTo(0, 0); });
  await page.waitForLoadState('networkidle', { timeout: 10000 }).catch(() => {});
  await page.waitForTimeout(1500);
}
async function shot(page, name, fullPage = true) { await page.screenshot({ path: path.join(OUT, `${name}.png`), fullPage, timeout: 30000 }).catch((e) => log('  shot fail', name, e.message)); log(`  [${name}] ${page.url().replace(BASE, '')}`); }
async function tryStep(label, fn) { try { await fn(); } catch (e) { log(`  !! ${label} failed: ${e.message.split('\n')[0]}`); } }
async function login(page) {
  await goto(page, '/customer/login', 600);
  await page.fill('input[name="email"]', CUSTOMER.email);
  await page.fill('input[name="password"]', CUSTOMER.password);
  await Promise.all([page.waitForNavigation({ timeout: 20000 }).catch(() => {}), page.click('form button[type="submit"]:visible')]);
  await settle(page, 800);
}
async function addToCart(page) {
  await goto(page, PRODUCT, 1200);
  const btn = page.locator('button:has-text("Add To Cart")').first();
  await btn.waitFor({ timeout: 15000 });
  await btn.click();
  await page.waitForTimeout(2500);
}
async function emptyCart(page) {
  await goto(page, '/checkout/cart', 1500);
  for (let i = 0; i < 6; i++) {
    const rm = page.locator('span:has-text("Remove"), button:has-text("Remove")').first();
    if (!(await rm.count()) || !(await rm.isVisible().catch(() => false))) break;
    await rm.click().catch(() => {});
    await page.waitForTimeout(700);
    const yes = page.locator('button:has-text("Agree"), button:has-text("Yes"), button:has-text("Confirm")').first();
    if (await yes.count() && await yes.isVisible().catch(() => false)) { await yes.click(); await page.waitForTimeout(1200); }
  }
}

const browser = await chromium.launch({ executablePath: EXEC, headless: true });

for (const [vpName, vp] of Object.entries(VIEWPORTS)) {
  log(`\n=== ${vpName.toUpperCase()} ===`);
  const ctx = await browser.newContext({ ...vp, locale: 'en-US' });
  const page = await ctx.newPage();
  const P = (n) => `${vpName}-${n}`;

  // Lazy-load re-shoots
  for (const [name, url] of [['home', '/'], ['category-menswear', '/african-menswear'], ['category-womenswear', '/african-womenswear'], ['search-results', '/search?query=kaftan'], ['designer', '/designer/mirage']]) {
    await tryStep(P(name + '-loaded'), async () => {
      await goto(page, url, 1200);
      await scrollThrough(page);
      const imgs = await page.evaluate(() => { const all = [...document.images].filter((i) => i.getBoundingClientRect().width > 40); return { total: all.length, blank: all.filter((i) => !i.complete || i.naturalWidth === 0).length, placeholders: all.filter((i) => /placeholder|shimmer|data:image\/svg/.test(i.currentSrc || i.src)).length }; });
      log(`  images: ${JSON.stringify(imgs)}`);
      await shot(page, P(name + '-loaded'));
    });
  }

  // Product cards: hover actions (desktop) and image presence per card
  await tryStep(P('card-hover'), async () => {
    await goto(page, '/african-menswear', 1200);
    await scrollThrough(page);
    const cards = await page.evaluate(() => [...document.querySelectorAll('.grid a[href] img, .product-card img')].slice(0, 12).map((i) => ({ src: (i.currentSrc || i.src).split('/').slice(-1)[0].slice(0, 40), w: i.naturalWidth, alt: i.alt })));
    log('  card imgs:', JSON.stringify(cards).slice(0, 600));
    if (vpName === 'desktop') { const card = page.locator('a[href*="kaftan"] img').first(); await card.hover(); await page.waitForTimeout(600); await shot(page, P('card-hover'), false); }
  });

  // Mobile category: is there a sort/filter control?
  if (vpName === 'mobile') {
    await tryStep(P('category-toolbar'), async () => {
      await goto(page, '/african-menswear', 1500);
      const txt = await page.evaluate(() => document.body.innerText);
      log('  mobile category has Sort text:', /\bSort\b/i.test(txt), 'Filter text:', /\bFilter\b/i.test(txt));
      const fixed = await page.evaluate(() => [...document.querySelectorAll('body *')].filter((el) => getComputedStyle(el).position === 'fixed' && el.getBoundingClientRect().height > 0 && el.getBoundingClientRect().height < 200).map((el) => el.className.toString().slice(0, 80)));
      log('  fixed bars:', JSON.stringify(fixed).slice(0, 400));
      await shot(page, P('category-viewport'), false);
    });
  }

  // Designers mega menu (desktop) / drawer designers (mobile)
  await tryStep(P('designers-menu'), async () => {
    await goto(page, '/', 1200);
    if (vpName === 'desktop') {
      await page.locator('div.group', { hasText: 'Designers' }).first().hover();
      await page.waitForTimeout(800);
      await shot(page, P('menu-designers-hover'), false);
    } else {
      await page.click('.icon-hamburger');
      await page.waitForTimeout(800);
      await page.locator('span:visible', { hasText: /^\s*Designers\s*$/ }).first().click();
      await page.waitForTimeout(1200);
      await shot(page, P('drawer-designers'), false);
      await page.keyboard.press('Escape').catch(() => {});
    }
  });

  // Mobile drawer: expand a category
  if (vpName === 'mobile') {
    await tryStep(P('drawer-category'), async () => {
      await goto(page, '/', 1200);
      await page.click('.icon-hamburger');
      await page.waitForTimeout(800);
      const row = page.locator('div:visible', { hasText: /^\s*AFRICAN MENSWEAR\s*$/ }).last();
      await row.locator('span').last().click();
      await page.waitForTimeout(700);
      await shot(page, P('drawer-category-expanded'), false);
    });
  }

  // How-to-measure modal via header/drawer entry points
  await tryStep(P('how-to-measure-modal'), async () => {
    await goto(page, '/', 1200);
    if (vpName === 'desktop') { await page.locator('a:visible', { hasText: 'Measurement Guide' }).first().click(); }
    else { await page.click('.icon-hamburger'); await page.waitForTimeout(700); await page.locator('a:visible', { hasText: 'Measurement Guide' }).first().click(); }
    await page.waitForTimeout(1500);
    await shot(page, P('how-to-measure-modal'), false);
    const bodyLocked = await page.evaluate(() => getComputedStyle(document.body).overflow);
    log('  modal open; body overflow =', bodyLocked, ' url=', page.url().replace(BASE, ''));
    await page.keyboard.press('Escape');
    await page.waitForTimeout(600);
    const stillOpen = await page.locator('text=Before you start').first().isVisible().catch(() => false);
    log('  after Escape, modal still visible:', stillOpen);
  });

  // How-to-measure page: click a video card
  await tryStep(P('video'), async () => {
    await goto(page, '/how-to-measure', 1200);
    const play = page.locator('text=Play video').first();
    await play.scrollIntoViewIfNeeded();
    await play.click();
    await page.waitForTimeout(2000);
    await shot(page, P('how-to-measure-video'), false);
    const iframes = await page.evaluate(() => [...document.querySelectorAll('iframe, video')].map((f) => (f.src || f.currentSrc || '').slice(0, 100)));
    log('  media after click:', JSON.stringify(iframes));
    const guideSteps = await page.evaluate(() => [...document.querySelectorAll('button, a')].map((b) => b.textContent.trim()).filter((t) => /^(Neck|Shoulder|Chest|Waist|Hip|Sleeve|Top length|Inseam)$/i.test(t)));
    log('  guide steps:', guideSteps.join(','));
  });

  // Contact validation
  await tryStep(P('contact-validation'), async () => {
    await goto(page, '/contact-us', 800);
    await page.locator('form:has(textarea[name="message"]) button[type="submit"]').click();
    await page.waitForTimeout(900);
    await shot(page, P('contact-validation'));
  });

  // Newsletter invalid email
  await tryStep(P('newsletter'), async () => {
    await goto(page, '/', 800);
    const input = page.locator('footer input[name="email"]:visible').first();
    await input.scrollIntoViewIfNeeded();
    await input.fill('not-an-email');
    await page.locator('footer button:has-text("Subscribe"):visible').first().click();
    await page.waitForTimeout(900);
    await shot(page, P('newsletter-invalid'), false);
  });

  // PDP: description visibility + reviews tab + qty + wishlist for guest
  await tryStep(P('pdp-details'), async () => {
    await goto(page, PRODUCT, 1500);
    const desc = await page.evaluate(() => { const el = [...document.querySelectorAll('p, div')].find((e) => /Embrace effortless style/.test(e.textContent) && e.children.length < 5); if (!el) return null; const s = getComputedStyle(el); return { opacity: s.opacity, color: s.color, fontSize: s.fontSize, visible: el.getBoundingClientRect().height > 0 }; });
    log('  description style:', JSON.stringify(desc));
    const tabs = await page.evaluate(() => [...document.querySelectorAll('[role=tab], .tabs button, ul li')].map((t) => t.textContent.trim()).filter((t) => /Description|Reviews|Additional/.test(t)).slice(0, 5));
    log('  tabs:', tabs.join('|'));
    const rev = page.locator('text=Reviews').first();
    if (await rev.count()) { await rev.scrollIntoViewIfNeeded(); await rev.click(); await page.waitForTimeout(800); await shot(page, P('pdp-reviews-tab')); }
    // Guest wishlist click -> where?
    await goto(page, PRODUCT, 1200);
    await page.locator('[aria-label="Add To Wishlist"]').first().click();
    await page.waitForTimeout(1500);
    log('  guest wishlist click ->', page.url().replace(BASE, ''));
  });

  // Currency switcher (desktop)
  if (vpName === 'desktop') {
    await tryStep(P('currency'), async () => {
      await goto(page, '/', 1000);
      await page.locator('text=USD').first().click();
      await page.waitForTimeout(600);
      await shot(page, P('currency-dropdown'), false);
      await page.locator('text=English').first().click();
      await page.waitForTimeout(600);
      await shot(page, P('locale-dropdown'), false);
    });
    await tryStep(P('hero-slide2'), async () => {
      await goto(page, '/', 500);
      await page.waitForTimeout(6500);
      await shot(page, P('hero-slide-2'), false);
      const dots = await page.evaluate(() => document.querySelectorAll('[aria-label*="slide" i], [class*="dot"], [class*="indicator"]').length);
      log('  hero indicators/dots:', dots);
    });
  }

  // Guest mobile mini cart + cart + checkout
  if (vpName === 'mobile') {
    await tryStep(P('guest-cart'), async () => {
      await addToCart(page);
      await shot(page, P('product-added-viewport'), false);
      await page.locator('.icon-cart:visible, [aria-label="Shopping Cart"]:visible').first().click();
      await page.waitForTimeout(1000);
      await shot(page, P('mini-cart'), false);
      await goto(page, '/checkout/cart', 1500);
      await shot(page, P('cart-with-item'));
      await page.locator('text=Proceed To Checkout').first().click();
      await settle(page, 1500);
      log('  guest proceed ->', page.url().replace(BASE, ''));
      await shot(page, P('checkout-guest'));
    });
  }

  // ---- Logged in ----
  await tryStep(P('login'), async () => { await login(page); log('  logged in ->', page.url().replace(BASE, '')); });

  await tryStep(P('measurement-editor'), async () => {
    await goto(page, '/customer/account/measurements', 1500);
    const smart = await page.locator('a:has-text("Start now")').first().getAttribute('href').catch(() => null);
    log('  smart measurement href:', smart);
    const newBtn = page.locator('button:has-text("New profile")').first();
    if (await newBtn.count()) { await newBtn.click(); await page.waitForTimeout(800); }
    await page.locator('input[placeholder*="Muktar"]').first().fill('UX Test Profile').catch(() => {});
    const help = page.locator('button:has-text("?")').first();
    if (await help.count()) { await help.click(); await page.waitForTimeout(700); await shot(page, P('measurement-help-popover'), false); await page.keyboard.press('Escape'); }
    await page.locator('button:has-text("Save new profile")').first().scrollIntoViewIfNeeded().catch(() => {});
    await page.locator('button:has-text("Save new profile")').first().click().catch(() => {});
    await page.waitForTimeout(1200);
    await shot(page, P('measurement-save-empty'));
    const err = await page.evaluate(() => [...document.querySelectorAll('[class*="text-red"], [role=alert]')].map((e) => e.textContent.trim()).filter(Boolean).slice(0, 6));
    log('  save-empty messages:', JSON.stringify(err));
    // Fill upper-body numbers and save
    const inputs = page.locator('input[type="number"]:visible');
    const n = await inputs.count();
    for (let i = 0; i < n; i++) await inputs.nth(i).fill(String(20 + i)).catch(() => {});
    await page.locator('button:has-text("Save new profile")').first().click().catch(() => {});
    await page.waitForTimeout(2000);
    await shot(page, P('measurement-saved'));
  });

  await tryStep(P('checkout-steps'), async () => {
    await emptyCart(page);
    await addToCart(page);
    await goto(page, '/checkout/onepage', 2500);
    const fill = async (sel, val) => { const l = page.locator(sel).first(); if (await l.count() && await l.isVisible().catch(() => false)) await l.fill(val).catch(() => {}); };
    const existing = page.locator('text=Select an address, text=Add new address, input[type=radio][name*="billing"]').first();
    if (await page.locator('input[name="billing.first_name"]').count()) {
      await fill('input[name="billing.first_name"]', 'UX');
      await fill('input[name="billing.last_name"]', 'Reviewer');
      await fill('input[name="billing.email"]', CUSTOMER.email);
      await fill('input[name="billing.address.[0]"]', '600 N Broad Street');
      const country = page.locator('select[name="billing.country"]').first();
      if (await country.count()) { await country.selectOption('US').catch(() => {}); await page.waitForTimeout(900); }
      const state = page.locator('select[name="billing.state"]').first();
      if (await state.count() && await state.isVisible().catch(() => false)) await state.selectOption({ label: 'Delaware' }).catch(async () => state.selectOption({ index: 1 }));
      else await fill('input[name="billing.state"]', 'Delaware');
      await fill('input[name="billing.city"]', 'Middletown');
      await fill('input[name="billing.postcode"]', '19709');
      await fill('input[name="billing.phone"]', '2029923505');
      const saveCb = page.locator('text=Save this to address book').first();
      if (await saveCb.count()) await saveCb.click().catch(() => {});
    } else { log('  address step shows existing addresses'); await shot(page, P('checkout-existing-address')); }
    const save = page.locator('button:has-text("Save"):visible, button:has-text("Proceed"):visible').first();
    await save.click();
    await page.waitForTimeout(3500);
    await shot(page, P('checkout-step-2'));
    const step2Text = await page.evaluate(() => document.body.innerText.slice(0, 1500));
    log('  step2 has measurement:', /measurement/i.test(step2Text), ' shipping:', /shipping method/i.test(step2Text), ' payment:', /payment/i.test(step2Text));
    // Try to proceed through measurement -> shipping -> payment
    for (let i = 3; i <= 5; i++) {
      const btn = page.locator('button:has-text("Proceed"):visible, button:has-text("Continue"):visible').last();
      if (!(await btn.count())) break;
      await btn.click().catch(() => {});
      await page.waitForTimeout(3000);
      await shot(page, P(`checkout-step-${i}`));
      const t = await page.evaluate(() => document.body.innerText);
      log(`  step${i}: shippingMethods=${/DHL|Flat|Free Shipping/i.test(t)} paymentMethods=${/Stripe|Card|Pay/i.test(t)} placeOrder=${/Place Order/i.test(t)}`);
      const radios = await page.evaluate(() => [...document.querySelectorAll('input[type=radio]:not(:checked)')].filter((r) => r.getBoundingClientRect().width > 0 || r.parentElement).length);
      const firstRadio = page.locator('input[type=radio]').last();
      if (await firstRadio.count()) await firstRadio.check({ force: true }).catch(() => {});
      await page.waitForTimeout(1500);
    }
    await shot(page, P('checkout-final'));
  });

  await tryStep(P('wishlist-page'), async () => {
    await goto(page, PRODUCT, 1200);
    const heart = page.locator('[aria-label="Add To Wishlist"]').first();
    const filled = await heart.evaluate((el) => el.className.includes('icon-heart-fill')).catch(() => false);
    if (!filled) { await heart.click(); await page.waitForTimeout(1200); }
    await goto(page, '/customer/account/wishlist', 1500);
    await scrollThrough(page);
    await shot(page, P('wishlist-with-item'));
  });

  if (vpName === 'mobile') {
    await tryStep(P('drawer-auth'), async () => {
      await goto(page, '/', 1000);
      await page.click('.icon-hamburger');
      await page.waitForTimeout(800);
      await shot(page, P('drawer-auth'), false);
    });
  }

  // Logout flow
  await tryStep(P('logout'), async () => {
    if (vpName === 'desktop') { await goto(page, '/', 1000); await page.click('.icon-users >> nth=0'); await page.waitForTimeout(500); await page.locator('a:has-text("Logout")').first().click(); await settle(page, 1000); log('  after logout ->', page.url().replace(BASE, '')); await shot(page, P('after-logout'), false); }
  });

  await ctx.close();
}

await browser.close();
fs.writeFileSync(path.join(OUT, '..', 'pass2-notes.txt'), notes.join('\n'));
log('\nDONE pass 2');

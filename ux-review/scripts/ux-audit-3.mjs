// Pass 3: debugbar hidden. Mobile toolbar, lazy-load verification, measurement profile save, checkout to payment.
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
const HIDE = '.phpdebugbar{display:none!important}';

const VIEWPORTS = {
  desktop: { viewport: { width: 1440, height: 900 }, deviceScaleFactor: 1 },
  mobile: { viewport: { width: 390, height: 844 }, deviceScaleFactor: 2, isMobile: true, hasTouch: true,
    userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1' },
};

async function settle(page, ms = 1500) { await page.waitForLoadState('networkidle', { timeout: 12000 }).catch(() => {}); await page.waitForTimeout(ms); await page.addStyleTag({ content: HIDE }).catch(() => {}); }
async function goto(page, url, ms) { await page.goto(BASE + url, { waitUntil: 'domcontentloaded', timeout: 45000 }); await settle(page, ms); }
async function slowScroll(page) {
  await page.evaluate(async () => { const h = document.body.scrollHeight; for (let y = 0; y < h; y += 300) { window.scrollTo(0, y); await new Promise((r) => setTimeout(r, 350)); } });
  await page.waitForLoadState('networkidle', { timeout: 10000 }).catch(() => {});
  await page.waitForTimeout(1500);
}
async function shot(page, name, fullPage = true) { await page.addStyleTag({ content: HIDE }).catch(() => {}); await page.screenshot({ path: path.join(OUT, `${name}.png`), fullPage, timeout: 30000 }).catch((e) => log('  shot fail', name, e.message)); log(`  [${name}] ${page.url().replace(BASE, '')}`); }
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

const browser = await chromium.launch({ executablePath: EXEC, headless: true });

for (const [vpName, vp] of Object.entries(VIEWPORTS)) {
  log(`\n=== ${vpName.toUpperCase()} ===`);
  const ctx = await browser.newContext({ ...vp, locale: 'en-US' });
  const page = await ctx.newPage();
  const P = (n) => `${vpName}-${n}`;

  // Lazy-load check with slow scroll: count blank product images after a human-speed scroll
  await tryStep(P('home-slow'), async () => {
    await goto(page, '/', 1500);
    await slowScroll(page);
    const blank = await page.evaluate(() => { const all = [...document.images].filter((i) => i.getBoundingClientRect().width > 60 && i.closest('a[href]')); return { total: all.length, blank: all.filter((i) => !i.complete || i.naturalWidth === 0).length, sample: all.filter((i) => !i.complete || i.naturalWidth === 0).map((i) => i.alt || i.src.slice(-30)).slice(0, 5) }; });
    log('  home after slow scroll:', JSON.stringify(blank));
    await page.evaluate(() => window.scrollTo(0, 0));
    await page.waitForTimeout(500);
    await shot(page, P('home-clean'));
  });

  if (vpName === 'mobile') {
    await tryStep(P('category-toolbar'), async () => {
      await goto(page, '/african-menswear', 1500);
      await shot(page, P('category-top-clean'), false);
      await page.evaluate(() => window.scrollTo(0, 600));
      await page.waitForTimeout(600);
      await shot(page, P('category-scrolled-clean'), false);
      const bar = await page.evaluate(() => { const els = [...document.querySelectorAll('body *')].filter((el) => getComputedStyle(el).position === 'fixed' && !el.closest('.phpdebugbar')); return els.map((el) => ({ cls: el.className.toString().slice(0, 60), text: el.innerText.replace(/\s+/g, ' ').slice(0, 60), h: Math.round(el.getBoundingClientRect().height), vis: getComputedStyle(el).display !== 'none' && el.getBoundingClientRect().width > 0 })); });
      log('  fixed elements:', JSON.stringify(bar));
      for (const label of ['Filter', 'Sort']) {
        const b = page.locator(`div[role=button]:visible:has-text("${label}"), button:visible:has-text("${label}"), span:visible:has-text("${label}")`).first();
        if (await b.count()) { await b.click(); await page.waitForTimeout(900); await shot(page, P(`category-${label.toLowerCase()}-drawer`), false); await page.keyboard.press('Escape'); await page.waitForTimeout(400); const close = page.locator('.icon-cancel:visible, [aria-label="Close"]:visible').first(); if (await close.count()) await close.click().catch(() => {}); await page.waitForTimeout(400); }
        else log(`  no visible ${label} control`);
      }
    });
    await tryStep(P('pdp-clean'), async () => {
      await goto(page, PRODUCT, 1500);
      await page.evaluate(() => window.scrollTo(0, 560));
      await page.waitForTimeout(500);
      await shot(page, P('pdp-buybox-clean'), false);
      await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
      await page.waitForTimeout(800);
      const sticky = await page.evaluate(() => !![...document.querySelectorAll('button')].find((b) => /add to cart/i.test(b.textContent) && getComputedStyle(b.closest('div') || b).position === 'fixed'));
      log('  mobile sticky add-to-cart:', sticky);
    });
    await tryStep(P('footer-clean'), async () => {
      await goto(page, '/', 1000);
      const acc = page.locator('text=Footer Content').first();
      await acc.scrollIntoViewIfNeeded();
      await acc.click();
      await page.waitForTimeout(700);
      await shot(page, P('footer-accordion-open'), false);
    });
  }

  await tryStep(P('login'), async () => { await login(page); });

  // Measurement profile: dismiss popovers, fill, save; verify a profile card appears
  await tryStep(P('measurement-save'), async () => {
    await goto(page, '/customer/account/measurements', 1500);
    await page.locator('button:has-text("New profile")').first().click().catch(() => {});
    await page.waitForTimeout(600);
    await page.locator('input[placeholder*="Muktar"]').first().fill('UX Test Profile');
    const men = page.locator('button:has-text("Men")').first();
    if (await men.count()) await men.click().catch(() => {});
    await page.waitForTimeout(500);
    // open every collapsed section
    for (const t of ['Lower Body', 'Fit preference']) { const s = page.locator(`text=${t}`).first(); if (await s.count()) { await s.click().catch(() => {}); await page.waitForTimeout(400); } }
    const inputs = page.locator('input[type="number"]:visible');
    const n = await inputs.count();
    for (let i = 0; i < n; i++) await inputs.nth(i).fill(String(18 + i)).catch(() => {});
    log('  filled numeric inputs:', n);
    const progress = await page.evaluate(() => (document.body.innerText.match(/\d+ of \d+ complete/) || [''])[0]);
    log('  progress:', progress);
    await shot(page, P('measurement-filled'));
    const save = page.locator('button:has-text("Save new profile")').first();
    await save.scrollIntoViewIfNeeded();
    await save.click();
    await page.waitForTimeout(2500);
    await shot(page, P('measurement-after-save'));
    const flash = await page.evaluate(() => [...document.querySelectorAll('[class*="flash"], [role=alert], [class*="bg-green"], [class*="bg-red"]')].map((e) => e.innerText.trim()).filter(Boolean).slice(0, 4));
    log('  after save messages:', JSON.stringify(flash), ' url:', page.url().replace(BASE, ''));
    await goto(page, '/customer/account/measurements', 1500);
    const cards = await page.evaluate(() => document.body.innerText.includes('UX Test Profile'));
    log('  profile listed after reload:', cards);
    await shot(page, P('measurement-list-after-save'));
  });

  // Checkout with a profile present: measurement assignment UI, shipping, payment
  await tryStep(P('checkout-full'), async () => {
    await addToCart(page);
    await goto(page, '/checkout/onepage', 2500);
    const proceed1 = page.locator('button:has-text("Proceed"):visible').first();
    if (await proceed1.count()) { await proceed1.click(); await page.waitForTimeout(3000); }
    await shot(page, P('checkout-measurement-step'));
    const t1 = await page.evaluate(() => document.body.innerText);
    log('  measurement step text sample:', t1.slice(t1.indexOf('Tailoring'), t1.indexOf('Tailoring') + 400).replace(/\s+/g, ' '));
    // Try to proceed without choosing a profile to see if it blocks
    const proceed2 = page.locator('button:has-text("Proceed"):visible').last();
    if (await proceed2.count()) { await proceed2.click(); await page.waitForTimeout(3000); }
    const t2 = await page.evaluate(() => document.body.innerText);
    log('  after measurement Proceed: shipping visible =', /Shipping Method/i.test(t2), ' warning =', /(select|choose|assign).{0,40}profile|profile.{0,40}(required|select)/i.test(t2));
    await shot(page, P('checkout-after-measurement'));
    // choose shipping if visible
    const ship = page.locator('input[type=radio]:visible').last();
    if (await ship.count()) { await ship.check({ force: true }).catch(() => {}); await page.waitForTimeout(2500); }
    const proceed3 = page.locator('button:has-text("Proceed"):visible').last();
    if (await proceed3.count()) { await proceed3.click().catch(() => {}); await page.waitForTimeout(3000); }
    const t3 = await page.evaluate(() => document.body.innerText);
    log('  payment visible =', /Payment/i.test(t3), ' stripe =', /stripe|card/i.test(t3), ' place order =', /Place Order/i.test(t3));
    await shot(page, P('checkout-payment-step'));
    const pay = page.locator('input[type=radio]:visible').last();
    if (await pay.count()) { await pay.check({ force: true }).catch(() => {}); await page.waitForTimeout(2500); }
    await shot(page, P('checkout-ready-to-place'));
    const t4 = await page.evaluate(() => document.body.innerText);
    log('  final: place order =', /Place Order/i.test(t4), ' delivery charge line:', (t4.match(/Delivery Charges\s*\$[\d.]+/) || [''])[0]);
  });

  await tryStep(P('smart-measure'), async () => {
    const resp = await page.request.get(BASE + '/customer/account/measurements/smart?redirect=' + encodeURIComponent(BASE + '/customer/account/measurements'), { maxRedirects: 0 }).catch((e) => null);
    log('  smart measurement endpoint:', resp ? `${resp.status()} -> ${(resp.headers()['location'] || '').replace(/\?.*/, '?…')}` : 'no response');
  });

  await ctx.close();
}

await browser.close();
fs.writeFileSync(path.join(OUT, '..', 'pass3-notes.txt'), notes.join('\n'));
log('\nDONE pass 3');

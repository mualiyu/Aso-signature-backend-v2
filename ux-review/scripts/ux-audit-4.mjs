// Pass 4: desktop checkout through shipping -> payment with patient waits (no order placed). Adaptive to current step.
import { chromium } from '/Users/mukeey/projects/Aso Signature/Aso-signature-backend-v2/packages/Webkul/Shop/node_modules/playwright/index.mjs';
import path from 'node:path';

const BASE = 'http://aso-signature-backend-v2.test';
const OUT = '/Users/mukeey/projects/Aso Signature/Aso-signature-backend-v2/ux-review/screenshots';
const EXEC = '/Users/mukeey/Library/Caches/ms-playwright/chromium_headless_shell-1234/chrome-headless-shell-mac-arm64/chrome-headless-shell';
const CUSTOMER = { email: 'ux.reviewer@example.com', password: 'Reviewer123!' };
const HIDE = '.phpdebugbar{display:none!important}';
const log = (...a) => console.log(...a);

const browser = await chromium.launch({ executablePath: EXEC, headless: true });
const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 }, locale: 'en-US' });
const page = await ctx.newPage();
const failed = [];
page.on('response', (r) => { if (r.status() >= 400) failed.push(`${r.status()} ${r.url().replace(BASE, '')}`); });
page.on('console', (m) => { if (m.type() === 'error') failed.push('console: ' + m.text().slice(0, 160)); });
const shot = async (n) => { await page.addStyleTag({ content: HIDE }).catch(() => {}); await page.screenshot({ path: path.join(OUT, `${n}.png`), fullPage: true }); log('  [' + n + ']'); };
const text = () => page.evaluate(() => document.body.innerText);
const waitText = async (re, ms) => { const t0 = Date.now(); while (Date.now() - t0 < ms) { if (re.test(await text())) return Date.now() - t0; await page.waitForTimeout(500); } return -1; };
const sections = async () => (await text()).match(/^(Address|Tailoring Measurements|Shipping Method|Payment Method|Cart Summary)[^\n]*/gm) || [];

await page.goto(BASE + '/customer/login', { waitUntil: 'networkidle' });
await page.waitForTimeout(800);
await page.fill('input[name="email"]', CUSTOMER.email);
await page.fill('input[name="password"]', CUSTOMER.password);
await Promise.all([page.waitForNavigation({ timeout: 20000 }).catch(() => {}), page.click('form button[type="submit"]:visible')]);
await page.waitForTimeout(1000);
log('after login url:', page.url().replace(BASE, ''), '| flash:', (await text()).match(/(Too many|credentials|Logged in|Welcome[^\n]*)/i)?.[0] || 'none');

// make sure the cart has an item
await page.goto(BASE + '/checkout/cart', { waitUntil: 'domcontentloaded' });
await page.waitForLoadState('networkidle', { timeout: 15000 }).catch(() => {});
const cartText = await text();
log('cart state:', /Proceed To Checkout/.test(cartText) ? 'has items' : 'EMPTY -> ' + cartText.slice(0, 120).replace(/\s+/g, ' '));
if (!/Proceed To Checkout/.test(cartText)) {
  await page.goto(BASE + '/classic-kaftan-tailored-from-premium-fabric', { waitUntil: 'domcontentloaded' });
  await page.waitForLoadState('networkidle', { timeout: 15000 }).catch(() => {});
  await page.locator('button:has-text("Add To Cart")').first().click();
  await page.waitForTimeout(2500);
}

await page.goto(BASE + '/checkout/onepage', { waitUntil: 'domcontentloaded' });
await page.waitForLoadState('networkidle', { timeout: 15000 }).catch(() => {});
await page.waitForTimeout(2000);
log('sections on load:', JSON.stringify(await sections()));
await shot('desktop-checkout-pass4-load');

for (let i = 0; i < 4; i++) {
  const t = await text();
  if (/Payment Method/i.test(t)) break;
  if (/Shipping Method/i.test(t)) {
    const radios = page.locator('input[type=radio]:visible');
    log('  shipping radios:', await radios.count());
    const opts = await page.evaluate(() => [...document.querySelectorAll('label')].map((l) => l.innerText.replace(/\s+/g, ' ').trim()).filter((s) => /^\$/.test(s)).slice(0, 5));
    log('  shipping options:', JSON.stringify(opts));
    page.on('response', (r) => { if (/shipping-methods|payment-methods|summary/.test(r.url())) log('    net', r.request().method(), r.url().replace(BASE, ''), r.status()); });
    const card = page.locator('label:has-text("EXPRESS EASY"), label:has-text("$480.31")').first();
    if (await card.count()) { await card.click(); log('  clicked EXPRESS EASY label'); }
    else { const allRadios = page.locator('input[type=radio]'); log('  total radios (incl hidden):', await allRadios.count()); await allRadios.nth(1).check({ force: true }).catch(() => {}); }
    log('  payment in', await waitText(/Payment Method/i, 25000), 'ms');
    break;
  }
  const btn = page.locator('button:has-text("Proceed"):visible').last();
  if (!(await btn.count())) { log('  no Proceed button; sections:', JSON.stringify(await sections())); break; }
  await btn.click();
  await page.waitForTimeout(3500);
  log('  after proceed', i, JSON.stringify(await sections()));
}
await page.waitForTimeout(1500);
await shot('desktop-checkout-shipping-selected');
const payText = await page.evaluate(() => { const t = document.body.innerText; const i = t.search(/Payment Method/i); return i >= 0 ? t.slice(i, i + 400).replace(/\s+/g, ' ') : '(no payment section)'; });
log('payment text:', payText);
log('summary:', await page.evaluate(() => { const t = document.body.innerText; return ((t.match(/Delivery Charges[\s\S]{0,30}/) || [''])[0] + ' | ' + (t.match(/Grand Total[\s\S]{0,30}/) || [''])[0]).replace(/\s+/g, ' '); }));
const radios = page.locator('input[type=radio]:visible');
const n = await radios.count();
if (/Payment Method/i.test(await text())) {
  await radios.last().check({ force: true }).catch(() => {});
  log('place order in', await waitText(/Place Order/, 20000), 'ms');
  await page.waitForTimeout(2000);
  await shot('desktop-checkout-payment-selected');
  log('stripe/place-order:', JSON.stringify(await page.evaluate(() => ({ iframes: [...document.querySelectorAll('iframe')].map((f) => f.src.slice(0, 70)), placeOrder: [...document.querySelectorAll('button')].filter((b) => /Place Order/.test(b.textContent)).map((b) => ({ disabled: b.disabled, visible: b.getBoundingClientRect().width > 0 })) }))));
}
log('failed requests / console errors:', JSON.stringify([...new Set(failed)].slice(0, 10)));
await browser.close();
log('DONE pass 4');

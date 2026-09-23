// Usage: NODE_PATH=<path to Playwright> node tools/check-admin.cjs
// Renders GET views only. Data stays in memory; no admin forms are submitted.
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const cp = require('node:child_process');
const {chromium} = require('playwright');
const root = path.resolve(__dirname, '..');
const admin = path.join(root, 'src/admin');
const pages = fs.readdirSync(admin).filter(name => name.endsWith('.php') && /(?:include|require)(?:_once)?\s*['"]header.php/.test(fs.readFileSync(path.join(admin,name),'utf8')) && !/^(test|update_template)/.test(name));
pages.push('login.php');
if (process.argv.length > 2) pages.splice(0, pages.length, ...pages.filter(name => process.argv.slice(2).includes(name)));
let errors = 0;
(async () => {
    const browser = await chromium.launch({headless: true, channel: 'chrome'});
    try {
        for (const name of pages) {
            const render = cp.spawnSync('php', ['-d','extension=pdo_mysql',path.join(__dirname,'render-admin-check.php'),name], {encoding:'utf8',maxBuffer:8e6,timeout:30000});
            let data;
            try { data = JSON.parse(render.stdout); } catch (_) { console.log(name + ': renderer did not complete'); errors++; continue; }
            if (data.skip) { console.log(name + ': SKIP ' + data.skip); continue; }
            if (data.errors.length) { console.log(name + ': PHP ' + data.errors.join('; ')); errors++; }
            const page = await browser.newPage({viewport:{width:1280,height:900}});
            const jsErrors = [];
            page.on('pageerror', error => jsErrors.push(error.message));
            await page.route('**/*', async route => {
                const url = new URL(route.request().url());
                if (url.hostname === 'admin-check.local') {
                    if (url.pathname.endsWith('/api/update_product_quick.php')) return route.fulfill({contentType:'application/json',headers:{'Access-Control-Allow-Origin':'*'},body:JSON.stringify({success:false,message:'Test failure'})});
                    const filename = path.resolve(root,'src',decodeURIComponent(url.pathname).replace(/^\//,''));
                    if (filename.startsWith(path.join(root,'src') + path.sep) && fs.existsSync(filename) && !filename.endsWith('.php')) {
                        const contentType = filename.endsWith('.css') ? 'text/css' : filename.endsWith('.js') ? 'application/javascript' : undefined;
                        return route.fulfill({body:fs.readFileSync(filename),contentType});
                    }
                    return route.fulfill({status:404,body:''});
                }
                // Only fetch public asset libraries, never application URLs or user data.
                if (['cdn.jsdelivr.net','cdnjs.cloudflare.com','code.jquery.com'].includes(url.hostname)) return route.continue();
                return route.abort();
            });
            await page.setContent('<base href="http://admin-check.local/admin/">' + data.html, {waitUntil:'networkidle',timeout:30000});
            const findings = await page.evaluate(() => ({
                heading: [...document.querySelectorAll('h1')].filter(el => !el.closest('.note-editor')).length,
                main: document.querySelectorAll('main').length,
                outside: [...document.querySelectorAll('.card')].filter(el => !el.closest('main,.modal') && document.querySelector('main')).length,
                overflow: document.documentElement.scrollWidth > innerWidth + 2,
                controls: [...document.querySelectorAll('button,a.btn,input:not([type=hidden]),select,textarea')].filter(el => el.getClientRects().length && !el.textContent.trim() && !el.labels?.length && !el.getAttribute('aria-label') && !el.getAttribute('aria-labelledby') && !el.getAttribute('title')).length
            }));
            await page.setViewportSize({width:375,height:812});
            const mobileOverflow = await page.evaluate(() => document.documentElement.scrollWidth > innerWidth + 2);
            if (name === 'products.php') {
                await page.locator('#toggle-sidebar-btn').click();
                if (!await page.evaluate(() => document.getElementById('mainContent').inert && document.activeElement.closest('#adminSidebar'))) throw new Error('Sidebar did not take focus');
                await page.keyboard.press('Escape');
                if (await page.evaluate(() => document.getElementById('mainContent').inert)) throw new Error('Sidebar did not restore page interaction');
                await page.evaluate(() => { window.confirmResult = null; AdminUI.confirm('Delete test item?').then(value => window.confirmResult = value); });
                await page.locator('#adminConfirmModal').getByRole('button',{name:'Cancel',exact:true}).click();
                await page.waitForFunction(() => window.confirmResult === false);
                const price = page.locator('input[onchange*="price"]').first();
                const oldPrice = await price.inputValue();
                await price.fill('123');
                await price.dispatchEvent('change');
                await page.waitForFunction(() => !document.querySelector('input[onchange*="price"]').disabled);
                if (await price.inputValue() !== oldPrice) throw new Error('Failed quick edit did not restore its saved value');
            }
            if (name === 'orders.php' && await page.locator('.order-checkbox').count()) {
                await page.locator('#selectAllMobile').check();
                if (await page.evaluate(() => document.documentElement.scrollWidth > innerWidth + 2)) throw new Error('Selected-order toolbar overflows on mobile');
                await page.getByRole('button',{name:'Cancel',exact:true}).filter({visible:true}).click();
            }
            if (name === 'categories.php' && await page.locator('.category-toggle').count()) {
                await page.locator('.category-toggle').first().focus();
                await page.keyboard.press('Enter');
            }
            if (['add_product.php','edit_product.php'].includes(name)) {
                const before = await page.locator('.variant-card').count();
                await page.getByRole('button',{name:'Add Variant',exact:true}).click();
                if (await page.locator('.variant-card').count() !== before + 1) throw new Error('Shared variant creation failed');
                const unique = await page.evaluate(() => { const ids = [...document.querySelectorAll('[id^="attributes-container-"]')].map(node => node.id); return new Set(ids).size === ids.length; });
                if (!unique) throw new Error('Duplicate variant field indexes');
                await page.locator('.variant-card').last().getByRole('button',{name:'Remove',exact:true}).click();
                if (await page.locator('.variant-card').count() !== before) throw new Error('Variant removal failed');
            }
            if (process.env.ADMIN_SCREENSHOT_DIR && ['add_product.php','products.php','dashboard.php'].includes(name)) {
                fs.mkdirSync(process.env.ADMIN_SCREENSHOT_DIR,{recursive:true});
                await page.evaluate(() => { window.scrollTo(0,0); document.getElementById('mainContent')?.scrollTo(0,0); });
                await page.screenshot({path:path.join(process.env.ADMIN_SCREENSHOT_DIR,name+'.mobile.png')});
                await page.setViewportSize({width:1440,height:1000});
                await page.screenshot({path:path.join(process.env.ADMIN_SCREENSHOT_DIR,name+'.desktop.png')});
            }
            if (jsErrors.length || findings.overflow || mobileOverflow || findings.outside || findings.controls || (name !== 'login.php' && (findings.heading !== 1 || findings.main !== 1))) errors++;
            console.log(name + ': ' + JSON.stringify({...findings,mobileOverflow,jsErrors}));
            await page.close();
        }
    } finally { await browser.close(); }
    console.log('Pages checked: ' + pages.length + '; pages with errors: ' + errors);
    process.exitCode = errors ? 1 : 0;
})().catch(error => {console.error(error.message);process.exitCode = 1;});

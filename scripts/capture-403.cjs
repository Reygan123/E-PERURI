const puppeteer = require('puppeteer');
const path = require('path');

const BASE_URL = 'http://127.0.0.1:8088';
const OUTPUT_DIR = path.resolve(__dirname, '../dokumentasi/report-assets');

(async () => {
    const browser = await puppeteer.launch({
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--window-size=1920,1080'],
    });

    const page = await browser.newPage();
    await page.setViewport({ width: 1920, height: 1080 });

    console.log('Logging in as operatorpk1...');
    await page.goto(`${BASE_URL}/login`, { waitUntil: 'networkidle2' });
    await page.type('#login', 'operatorpk1');
    await page.type('#password', 'password');
    await page.click('button[type="submit"]');

    // Wait until URL changes to /dashboard
    await page.waitForFunction(() => window.location.pathname.includes('/dashboard'), { timeout: 10000 });
    console.log('Operator on dashboard:', page.url());

    // Screenshot 9: Portal Staf/Operator
    await new Promise(r => setTimeout(r, 1000));
    await page.screenshot({ path: path.join(OUTPUT_DIR, '09_portal_staf_operator.png') });

    // Navigate to /platform/org-units -> expect 403
    console.log('Navigating to /platform/org-units (should trigger 403)...');
    await page.goto(`${BASE_URL}/platform/org-units`, { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 1500));
    console.log('Page title on 403:', await page.title());
    await page.screenshot({ path: path.join(OUTPUT_DIR, '10_error_403_ptkp.png') });

    console.log('403 captured successfully!');
    await browser.close();
})();

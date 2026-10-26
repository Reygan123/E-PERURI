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

    console.log('Logging in as adminsdm...');
    await page.goto(`${BASE_URL}/login`, { waitUntil: 'networkidle2' });
    await page.type('#login', 'adminsdm');
    await page.type('#password', 'password');
    await page.click('button[type="submit"]');

    await page.waitForFunction(() => window.location.pathname.includes('/dashboard'), { timeout: 10000 });
    console.log('Navigating to meja persetujuan...');
    await page.goto(`${BASE_URL}/arsip/persetujuan`, { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 1500));

    // Look for button with text "Lihat / Pratinjau Surat"
    console.log('Finding preview button...');
    const btn = await page.evaluateHandle(() => {
        const buttons = Array.from(document.querySelectorAll('button'));
        return buttons.find(b => b.innerText && b.innerText.includes('Pratinjau'));
    });

    if (btn && btn.asElement()) {
        console.log('Clicking preview button...');
        await btn.asElement().click();
        // Wait for modal dialog and iframe
        await new Promise(r => setTimeout(r, 3000));
        await page.screenshot({ path: path.join(OUTPUT_DIR, '07_preview_pdf_modal.png') });
        console.log('Modal preview screenshot captured successfully!');
    } else {
        console.warn('Button not found');
    }

    await browser.close();
})();

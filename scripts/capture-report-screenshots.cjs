const puppeteer = require('puppeteer');
const path = require('path');
const fs = require('fs');

const BASE_URL = 'http://127.0.0.1:8088';
const OUTPUT_DIR = path.resolve(__dirname, '../dokumentasi/report-assets');

if (!fs.existsSync(OUTPUT_DIR)) {
    fs.mkdirSync(OUTPUT_DIR, { recursive: true });
}

async function loginUser(page, username, password = 'password') {
    const client = await page.target().createCDPSession();
    await client.send('Network.clearBrowserCookies');
    await page.goto(`${BASE_URL}/login`, { waitUntil: 'networkidle2' });
    await page.type('#login', username);
    await page.type('#password', password);
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle2' }),
        page.click('button[type="submit"]'),
    ]);
    console.log(`Logged in as ${username}, redirected to:`, page.url());
}

async function capture() {
    console.log('Launching browser...');
    const browser = await puppeteer.launch({
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--window-size=1920,1080'],
    });

    const page = await browser.newPage();
    await page.setViewport({ width: 1920, height: 1080, deviceScaleFactor: 1 });

    // 1. Screenshot Login Page
    console.log('1. Capturing Login Page...');
    await page.goto(`${BASE_URL}/login`, { waitUntil: 'networkidle2' });
    await page.screenshot({ path: path.join(OUTPUT_DIR, '01_login_ptkp.png') });

    // 2. Superadmin Session
    console.log('Logging in as superadmin...');
    await loginUser(page, 'superadmin');

    // 2. Portal Aplikasi Superadmin
    console.log('2. Capturing Portal Aplikasi Superadmin...');
    await page.goto(`${BASE_URL}/dashboard`, { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 1200));
    await page.screenshot({ path: path.join(OUTPUT_DIR, '02_portal_aplikasi_superadmin.png') });

    // 3. Platform Core Master Organisasi
    console.log('3. Capturing Platform Core Master Data Organisasi...');
    await page.goto(`${BASE_URL}/platform/org-units`, { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 1500));
    await page.screenshot({ path: path.join(OUTPUT_DIR, '03_platform_organisasi.png') });

    // 4. Workspace E-Arsip
    console.log('4. Capturing Workspace E-Arsip (Sidebar Terpecah)...');
    await page.goto(`${BASE_URL}/arsip/surat-keluar`, { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 1500));
    await page.screenshot({ path: path.join(OUTPUT_DIR, '04_earsip_workspace_sidebar.png') });

    // 5. Form Pembuatan Naskah Dinas
    console.log('5. Capturing Form Pembuatan Naskah Dinas...');
    await page.goto(`${BASE_URL}/arsip/naskah-dinas/create?type_code=ND`, { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 1500));
    await page.screenshot({ path: path.join(OUTPUT_DIR, '05_form_naskah_dinas.png') });

    // 8. Halaman Naskah Revisi
    console.log('8. Capturing Halaman Revisi Naskah...');
    await page.goto(`${BASE_URL}/arsip/surat-keluar/bb209f47-40d1-4f3e-8672-d3c2086d5baf`, { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 1500));
    await page.screenshot({ path: path.join(OUTPUT_DIR, '08_revisi_naskah_dinas.png') });

    // 3. Admin SDM Session (Approver)
    console.log('Logging in as adminsdm (Approver)...');
    await loginUser(page, 'adminsdm');

    // 6. Meja Persetujuan Naskah
    console.log('6. Capturing Meja Persetujuan (Adminsdm)...');
    await page.goto(`${BASE_URL}/arsip/persetujuan`, { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 1500));
    await page.screenshot({ path: path.join(OUTPUT_DIR, '06_meja_persetujuan.png') });

    // 7. Modal Preview PDF
    console.log('7. Capturing Modal Preview PDF...');
    try {
        // Find button that contains text "Lihat / Pratinjau Surat"
        const previewBtn = await page.evaluateHandle(() => {
            const btns = Array.from(document.querySelectorAll('button'));
            return btns.find(b => b.innerText && b.innerText.includes('Pratinjau'));
        });
        if (previewBtn && previewBtn.asElement()) {
            await previewBtn.asElement().click();
            console.log('Clicked preview button, waiting for PDF viewer...');
            await new Promise(r => setTimeout(r, 3500));
            await page.screenshot({ path: path.join(OUTPUT_DIR, '07_preview_pdf_modal.png') });
        } else {
            console.warn('Preview button not found, fallback screenshot');
            await page.screenshot({ path: path.join(OUTPUT_DIR, '07_preview_pdf_modal.png') });
        }
    } catch (e) {
        console.error('Error during preview modal capture:', e.message);
    }

    // 4. Operator Session
    console.log('Logging in as operatorpk1...');
    await loginUser(page, 'operatorpk1');

    // 9. Portal Staf/Operator
    console.log('9. Capturing Portal Staf/Operator (Platform Core Hidden)...');
    await page.goto(`${BASE_URL}/dashboard`, { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 1200));
    await page.screenshot({ path: path.join(OUTPUT_DIR, '09_portal_staf_operator.png') });

    // 10. Error 403 PTKP
    console.log('10. Capturing Error 403 PTKP...');
    await page.goto(`${BASE_URL}/platform/org-units`, { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 1500));
    await page.screenshot({ path: path.join(OUTPUT_DIR, '10_error_403_ptkp.png') });

    console.log('All 10 screenshots captured perfectly!');
    await browser.close();
}

capture().catch(err => {
    console.error('Capture script error:', err);
    process.exit(1);
});

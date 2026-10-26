const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: 'new' });
    const page = await browser.newPage();
    page.on('console', msg => console.log('PAGE LOG:', msg.text()));
    page.on('pageerror', err => console.log('PAGE ERROR:', err.message));
    await page.goto('http://127.0.0.1:8088/login', { waitUntil: 'networkidle2' });
    console.log('Login URL:', page.url());
    await page.type('#login', 'superadmin');
    await page.type('#password', 'password');
    await page.click('button[type="submit"]');
    await new Promise(r => setTimeout(r, 3000));
    console.log('After submit URL:', page.url());
    const errText = await page.evaluate(() => document.body.innerText);
    console.log('Page text excerpt:', errText.substring(0, 300));
    await browser.close();
})();

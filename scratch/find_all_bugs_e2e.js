const puppeteer = require('./node_modules/puppeteer-core');

(async () => {
    const browser = await puppeteer.launch({
        executablePath: 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
        headless: 'new',
        args: ['--no-sandbox', '--disable-gpu', '--window-size=1440,900']
    });

    const page = await browser.newPage();
    const errors = [];
    const failedRequests = [];

    page.on('console', msg => {
        if (msg.type() === 'error') {
            errors.push(`[CONSOLE ERROR] on ${page.url()}: ${msg.text()}`);
        }
    });

    page.on('pageerror', err => {
        errors.push(`[PAGE ERROR] on ${page.url()}: ${err.message}`);
    });

    page.on('response', res => {
        if (res.status() >= 400) {
            failedRequests.push(`[HTTP ${res.status()}] ${res.url()} requested from ${page.url()}`);
        }
    });

    console.log("=== PHASE 1: TESTING DEAN SESSION ===");
    await page.goto('http://127.0.0.1:8000/login.php', { waitUntil: 'networkidle2' });
    await page.type('#username', 'dean');
    await page.type('#password', 'admin123');
    await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle2' }), page.click('button[type="submit"]')]);

    const deanPages = [
        'dashboard.php',
        'vault.php',
        'vault.php?view=grid',
        'upload.php',
        'export.php',
        'users.php',
        'audit_logs.php'
    ];

    for (const p of deanPages) {
        console.log(`Checking http://127.0.0.1:8000/${p}...`);
        await page.goto(`http://127.0.0.1:8000/${p}`, { waitUntil: 'networkidle2' });
        // Check for PHP notices or warnings in DOM
        const bodyText = await page.evaluate(() => document.body.innerText);
        if (bodyText.includes('Notice:') || bodyText.includes('Warning:') || bodyText.includes('Fatal error:')) {
            errors.push(`[PHP ERROR IN DOM] on ${p}: Found PHP warning/notice/error text!`);
        }
    }

    console.log("\n=== PHASE 2: TESTING FACULTY SESSION ===");
    await page.goto('http://127.0.0.1:8000/logout.php', { waitUntil: 'networkidle2' });
    await page.type('#username', 'mikhail');
    await page.type('#password', 'faculty123');
    await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle2' }), page.click('button[type="submit"]')]);

    const facultyPages = [
        'vault.php',
        'vault.php?view=grid',
        'upload.php',
        'export.php'
    ];

    for (const p of facultyPages) {
        console.log(`Checking http://127.0.0.1:8000/${p}...`);
        await page.goto(`http://127.0.0.1:8000/${p}`, { waitUntil: 'networkidle2' });
        const bodyText = await page.evaluate(() => document.body.innerText);
        if (bodyText.includes('Notice:') || bodyText.includes('Warning:') || bodyText.includes('Fatal error:')) {
            errors.push(`[PHP ERROR IN DOM] on ${p}: Found PHP warning/notice/error text!`);
        }
    }

    // Test Dean route guard on faculty
    console.log("Checking route guard on users.php for faculty...");
    await page.goto('http://127.0.0.1:8000/users.php', { waitUntil: 'networkidle2' });
    const content = await page.content();
    if (!content.includes('403') && !content.includes('Access Denied') && page.url().includes('users.php')) {
        errors.push(`[SECURITY BUG] Faculty was able to access users.php!`);
    }

    console.log("\n=== SUMMARY OF DETECTED ISSUES ===");
    console.log("Console & Page Errors:", errors.length);
    errors.forEach(e => console.log(e));

    console.log("\nFailed HTTP Requests:", failedRequests.length);
    failedRequests.forEach(f => console.log(f));

    await browser.close();
})();

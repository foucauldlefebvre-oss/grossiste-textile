const puppeteer = require('puppeteer-core');
const sleep = ms => new Promise(r => setTimeout(r, ms));

(async () => {
    const browser = await puppeteer.launch({
        executablePath: 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
        headless: 'new',
        args: ['--no-sandbox']
    });

    const page = await browser.newPage();
    await page.setViewport({ width: 1400, height: 900 });

    // Cookies
    await page.goto('https://www.imbretex.fr', { waitUntil: 'networkidle2', timeout: 20000 }).catch(() => {});
    await sleep(2000);
    try {
        const buttons = await page.$$('button');
        for (const btn of buttons) {
            const text = await btn.evaluate(el => el.textContent);
            if (text.includes('accepter')) { await btn.click(); break; }
        }
    } catch (e) {}
    await sleep(1000);

    // Page Just Hoods
    await page.goto('https://www.imbretex.fr/marques/just-hoods-8_106', { waitUntil: 'networkidle2', timeout: 30000 }).catch(() => {});
    await sleep(5000);

    // Compter les produits sur la page
    const count1 = await page.evaluate(() => document.querySelectorAll('img[src*="resize-image"]').length);
    console.log('Images resize-image page 1: ' + count1);

    // Chercher le bouton pagination
    const paginationInfo = await page.evaluate(() => {
        const links = Array.from(document.querySelectorAll('a, button'));
        const pagItems = links.filter(el => {
            const text = el.textContent.trim();
            const href = el.href || '';
            return text.match(/^[2-9]$|^>$|^→$|next|suivant/i) || href.includes('page=2') || el.getAttribute('aria-label')?.includes('Next');
        });
        return pagItems.map(el => ({
            tag: el.tagName,
            text: el.textContent.trim().substring(0, 30),
            href: el.href || '',
            class: el.className.substring(0, 50),
            ariaLabel: el.getAttribute('aria-label') || ''
        }));
    });
    console.log('\nPagination elements:');
    paginationInfo.forEach(p => console.log('  ' + p.tag + ' | ' + p.text + ' | ' + p.href + ' | ' + p.class));

    // Cliquer sur page 2
    const clicked = await page.evaluate(() => {
        const next = Array.from(document.querySelectorAll('a, button')).find(el => {
            const text = el.textContent.trim();
            return text === '2' || text === '→' || text === '>';
        });
        if (next) { next.click(); return next.textContent.trim(); }
        return null;
    });
    console.log('\nClicked: ' + clicked);
    await sleep(5000);

    const count2 = await page.evaluate(() => document.querySelectorAll('img[src*="resize-image"]').length);
    console.log('Images resize-image apres clic page 2: ' + count2);

    // Extraire les refs pour comparer
    const refs1 = await page.evaluate(() => {
        return Array.from(document.querySelectorAll('img[src*="resize-image"]')).map(img => {
            const card = img.closest('a, div');
            const text = card ? card.textContent : '';
            const m = text.match(/\b([A-Z]{2,4}\d{2,5}[A-Z]{0,2})\b/);
            return m ? m[1] : '';
        }).filter(Boolean);
    });
    console.log('Refs page 2: ' + refs1.join(', '));

    await page.screenshot({ path: 'public/debug-imbretex-page2.png' });
    console.log('Screenshot page 2 sauvegarde');

    await browser.close();
})();

const puppeteer = require('puppeteer-core');
const fs = require('fs');
const path = require('path');
const https = require('https');

const CHROME_PATH = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const STORAGE_DIR = path.join(__dirname, '..', 'storage', 'app', 'public', 'products');
const sleep = ms => new Promise(r => setTimeout(r, ms));

const BRANDS = [
    { supplier: 'AWDis', urls: ['https://www.imbretex.fr/marques/just-hoods-8_106', 'https://www.imbretex.fr/marques/just-cool_76'] },
    { supplier: 'B&C', urls: ['https://www.imbretex.fr/marques/bc-3_86'] },
    { supplier: 'Fruit of the Loom', urls: ['https://www.imbretex.fr/marques/fruit-of-the-loom_41'] },
    { supplier: 'Result', urls: ['https://www.imbretex.fr/marques/result_2'] },
    { supplier: 'Bag Base', urls: ['https://www.imbretex.fr/marques/bag-base_28'] },
    { supplier: 'Beechfield', urls: ['https://www.imbretex.fr/marques/beechfield_38'] },
    { supplier: 'Westford Mill', urls: ['https://www.imbretex.fr/marques/westford-mill-1_35'] },
    { supplier: 'BabyBugz', urls: ['https://www.imbretex.fr/marques/babybugz_92'] },
];

function downloadImage(url, filepath) {
    return new Promise((resolve, reject) => {
        const dir = path.dirname(filepath);
        if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
        https.get(url, { headers: { 'User-Agent': 'Mozilla/5.0' } }, res => {
            if ([301, 302, 307].includes(res.statusCode))
                return downloadImage(res.headers.location, filepath).then(resolve).catch(reject);
            if (res.statusCode !== 200) return reject(new Error(`HTTP ${res.statusCode}`));
            const chunks = [];
            res.on('data', d => chunks.push(d));
            res.on('end', () => {
                const buf = Buffer.concat(chunks);
                if (buf.length < 2000) return reject(new Error('Too small'));
                fs.writeFileSync(filepath, buf);
                resolve(buf.length);
            });
            res.on('error', reject);
        }).on('error', reject);
    });
}

function slugify(s) {
    return s.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
}

async function run() {
    console.log('Chrome headless...');
    const browser = await puppeteer.launch({
        executablePath: CHROME_PATH,
        headless: 'new',
        args: ['--no-sandbox']
    });

    const totals = { downloaded: 0, failed: 0, skipped: 0 };
    const mapping = [];

    for (const brand of BRANDS) {
        for (const url of brand.urls) {
            console.log(`\n=== ${brand.supplier} (${url.split('/').pop()}) ===`);
            try {
                const items = await scrapeBrandPage(browser, url);
                console.log(`${items.length} produits trouves`);

                for (const item of items) {
                    if (!item.ref) continue;
                    const supplierSlug = slugify(brand.supplier);
                    const filename = `mannequin-${item.ref}.jpg`;
                    const absPath = path.join(STORAGE_DIR, supplierSlug, filename);
                    const relPath = `products/${supplierSlug}/${filename}`;

                    if (fs.existsSync(absPath) && fs.statSync(absPath).size > 2000) {
                        totals.skipped++;
                        continue;
                    }

                    try {
                        // Telecharger en grande taille (1000px)
                        const bigUrl = item.imageUrl.replace(/\/450\/450/, '/1000/1000');
                        await downloadImage(bigUrl, absPath);
                        mapping.push({ supplier: brand.supplier, ref: item.ref, relPath });
                        totals.downloaded++;
                        process.stdout.write('.');
                    } catch (e) {
                        totals.failed++;
                    }
                }
                console.log('');
            } catch (e) {
                console.error(`Erreur: ${e.message}`);
            }
        }
    }

    await browser.close();

    const mappingPath = path.join(__dirname, '..', 'storage', 'app', 'public', 'imbretex-mannequin-mapping.json');
    fs.writeFileSync(mappingPath, JSON.stringify(mapping, null, 2));

    console.log(`\n=== TERMINE ===`);
    console.log(`Telecharges: ${totals.downloaded}`);
    console.log(`Echoues: ${totals.failed}`);
    console.log(`Ignores: ${totals.skipped}`);
    console.log(`Mapping sauvegarde: ${mappingPath}`);
}

async function scrapeBrandPage(browser, url) {
    const page = await browser.newPage();
    await page.setViewport({ width: 1400, height: 900 });

    const collectedImages = new Map(); // ref -> imageUrl

    // Intercepter les images resize-image chargees
    page.on('response', response => {
        const reqUrl = response.url();
        if (reqUrl.includes('resize-image') && !reqUrl.includes('logo')) {
            // On stocke temporairement sans ref, on associera apres
            collectedImages.set(reqUrl, { imageUrl: reqUrl, ref: '' });
        }
    });

    // Accepter cookies
    await page.goto('https://www.imbretex.fr', { waitUntil: 'networkidle2', timeout: 20000 }).catch(() => {});
    await sleep(1500);
    try {
        const buttons = await page.$$('button');
        for (const btn of buttons) {
            const text = await btn.evaluate(el => el.textContent);
            if (text.includes('accepter')) { await btn.click(); break; }
        }
    } catch (e) {}
    await sleep(500);

    // Page marque
    await page.goto(url, { waitUntil: 'networkidle2', timeout: 45000 }).catch(() => {});
    await sleep(5000);

    // Scroller pour charger toutes les images
    let prevH = 0;
    for (let i = 0; i < 50; i++) {
        await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
        await sleep(1200);
        const h = await page.evaluate(() => document.body.scrollHeight);
        if (h === prevH) break;
        prevH = h;
        if (i % 10 === 0) console.log(`  scroll ${i}`);
    }
    await sleep(2000);

    // Extraire les produits avec leur image et reference depuis le DOM
    const products = await page.evaluate(() => {
        const results = [];
        // Chercher toutes les images resize-image dans le DOM
        document.querySelectorAll('img[src*="resize-image"]').forEach(img => {
            const card = img.closest('a, div, article');
            if (!card) return;

            const text = card.textContent || '';
            const refMatch = text.match(/\b([A-Z]{2,4}\d{2,5}[A-Z]{0,2})\b/);
            const ref = refMatch ? refMatch[1].toLowerCase() : '';

            if (ref) {
                results.push({ imageUrl: img.src, ref });
            }
        });

        // Aussi chercher les background-image avec resize-image
        document.querySelectorAll('*').forEach(el => {
            const bg = getComputedStyle(el).backgroundImage;
            if (bg && bg.includes('resize-image')) {
                const urlMatch = bg.match(/url\(["']?([^"')]+)["']?\)/);
                if (!urlMatch) return;

                const card = el.closest('a, div, article');
                const text = card ? card.textContent : '';
                const refMatch = text.match(/\b([A-Z]{2,4}\d{2,5}[A-Z]{0,2})\b/);
                const ref = refMatch ? refMatch[1].toLowerCase() : '';

                if (ref) {
                    results.push({ imageUrl: urlMatch[1], ref });
                }
            }
        });

        return results;
    });

    console.log(`  ${products.length} depuis DOM, ${collectedImages.size} depuis reseau`);

    // Pagination : cliquer sur les boutons de page
    let allProducts = [...products];
    let pageNum = 2;
    while (pageNum <= 50) {
        // Cliquer sur le lien de la page suivante
        const hasNext = await page.evaluate((pn) => {
            const link = Array.from(document.querySelectorAll('a.page-link'))
                .find(a => a.textContent.trim() === String(pn));
            if (link) { link.click(); return true; }
            // Essayer le bouton "next"
            const next = document.querySelector('a.page-link.next');
            if (next) { next.click(); return true; }
            return false;
        }, pageNum);

        if (!hasNext) break;

        console.log(`  Page ${pageNum}...`);
        await sleep(4000);

        // Scroller pour lazy load
        await page.evaluate(() => window.scrollTo(0, 0));
        await sleep(500);
        let ph = 0;
        for (let i = 0; i < 10; i++) {
            await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
            await sleep(600);
            const h = await page.evaluate(() => document.body.scrollHeight);
            if (h === ph) break;
            ph = h;
        }
        await sleep(1000);

        const moreProducts = await page.evaluate(() => {
            const results = [];
            document.querySelectorAll('img[src*="resize-image"]').forEach(img => {
                const card = img.closest('a, div, article');
                if (!card) return;
                const text = card.textContent || '';
                const refMatch = text.match(/\b([A-Z]{2,4}\d{2,5}[A-Z]{0,2})\b/);
                if (refMatch) results.push({ imageUrl: img.src, ref: refMatch[1].toLowerCase() });
            });
            return results;
        });

        if (moreProducts.length === 0) break;
        allProducts.push(...moreProducts);
        console.log(`  +${moreProducts.length} produits`);
        pageNum++;
    }

    await page.close();

    // Dedupliquer par ref
    const unique = {};
    for (const p of allProducts) {
        if (p.ref && !unique[p.ref]) unique[p.ref] = p;
    }
    return Object.values(unique);
}

run().catch(console.error);

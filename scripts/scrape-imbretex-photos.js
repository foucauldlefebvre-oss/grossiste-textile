/**
 * Scrape les photos mannequin depuis imbretex.fr
 * et les enregistre en local pour chaque produit Imbretex.
 *
 * Usage: node scripts/scrape-imbretex-photos.js
 */

const puppeteer = require('puppeteer-core');
const fs = require('fs');
const path = require('path');
const https = require('https');
const http = require('http');

const CHROME_PATH = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const STORAGE_DIR = path.join(__dirname, '..', 'storage', 'app', 'public', 'products');
const DB_CONFIG = {
    host: '127.0.0.1',
    user: 'root',
    password: '',
    database: 'marquage_textile'
};

// Marques Imbretex et leurs slugs sur le site
const BRANDS = [
    { supplier: 'AWDis', slugs: ['just-hoods', 'just-cool'] },
    { supplier: 'B&C', slugs: ['bc'] },
    { supplier: 'Fruit of the Loom', slugs: ['fruit-of-the-loom'] },
    { supplier: 'Result', slugs: ['result'] },
    { supplier: 'Bag Base', slugs: ['bag-base'] },
    { supplier: 'Beechfield', slugs: ['beechfield'] },
    { supplier: 'Westford Mill', slugs: ['westford-mill'] },
    { supplier: 'BabyBugz', slugs: ['babybugz'] },
];

async function downloadImage(url, filepath) {
    return new Promise((resolve, reject) => {
        const dir = path.dirname(filepath);
        if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });

        const protocol = url.startsWith('https') ? https : http;
        protocol.get(url, { headers: { 'User-Agent': 'Mozilla/5.0' } }, (res) => {
            if (res.statusCode === 301 || res.statusCode === 302) {
                return downloadImage(res.headers.location, filepath).then(resolve).catch(reject);
            }
            if (res.statusCode !== 200) {
                reject(new Error(`HTTP ${res.statusCode}`));
                return;
            }
            const chunks = [];
            res.on('data', d => chunks.push(d));
            res.on('end', () => {
                const buffer = Buffer.concat(chunks);
                if (buffer.length < 1000) {
                    reject(new Error('Image too small'));
                    return;
                }
                fs.writeFileSync(filepath, buffer);
                resolve(filepath);
            });
            res.on('error', reject);
        }).on('error', reject);
    });
}

function slugify(str) {
    return str.toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-|-$/g, '');
}

async function scrapeAllBrands() {
    console.log('Lancement Chrome headless...');
    const browser = await puppeteer.launch({
        executablePath: CHROME_PATH,
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage']
    });

    const results = { downloaded: 0, failed: 0, skipped: 0 };

    for (const brand of BRANDS) {
        for (const brandSlug of brand.slugs) {
            console.log(`\n=== ${brand.supplier} (/${brandSlug}) ===`);
            try {
                await scrapeBrand(browser, brandSlug, brand.supplier, results);
            } catch (e) {
                console.error(`Erreur brand ${brandSlug}: ${e.message}`);
            }
        }
    }

    await browser.close();
    console.log(`\n=== TERMINE ===`);
    console.log(`Telecharges: ${results.downloaded}`);
    console.log(`Echoues: ${results.failed}`);
    console.log(`Ignores: ${results.skipped}`);
}

async function scrapeBrand(browser, brandSlug, supplierName, results) {
    const page = await browser.newPage();
    await page.setViewport({ width: 1400, height: 900 });

    const url = `https://www.imbretex.fr/${brandSlug}`;
    console.log(`Navigation vers ${url}...`);

    try {
        await page.goto(url, { waitUntil: 'networkidle2', timeout: 30000 });
    } catch (e) {
        console.log(`Timeout sur ${url}, on continue...`);
    }

    // Attendre que les produits se chargent
    await page.waitForTimeout(3000);

    // Scroller pour charger tous les produits (lazy loading)
    let previousHeight = 0;
    let scrollAttempts = 0;
    while (scrollAttempts < 30) {
        await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
        await page.waitForTimeout(1500);
        const currentHeight = await page.evaluate(() => document.body.scrollHeight);
        if (currentHeight === previousHeight) break;
        previousHeight = currentHeight;
        scrollAttempts++;
    }
    console.log(`Page scrollee ${scrollAttempts} fois`);

    // Extraire toutes les images produit visibles
    const products = await page.evaluate(() => {
        const items = [];
        // Chercher les cards produit - adapter les selecteurs au site
        const cards = document.querySelectorAll('[class*="product"], [class*="card"], article, .item');

        cards.forEach(card => {
            const img = card.querySelector('img');
            const link = card.querySelector('a[href]');
            const ref = card.querySelector('[class*="ref"], [class*="sku"], small, .text-muted');

            if (img && img.src && img.src.length > 10) {
                items.push({
                    imageUrl: img.src || img.dataset?.src || '',
                    href: link ? link.href : '',
                    ref: ref ? ref.textContent.trim() : '',
                    alt: img.alt || '',
                });
            }
        });
        return items;
    });

    console.log(`${products.length} produits trouves sur la page`);

    if (products.length === 0) {
        // Essayer une approche plus generique : toutes les images de grande taille
        const allImages = await page.evaluate(() => {
            return Array.from(document.querySelectorAll('img'))
                .filter(img => img.naturalWidth > 200 && img.naturalHeight > 200)
                .map(img => ({
                    imageUrl: img.src,
                    alt: img.alt || '',
                    ref: '',
                    href: img.closest('a')?.href || ''
                }));
        });
        console.log(`Fallback: ${allImages.length} grandes images trouvees`);
        products.push(...allImages);
    }

    // Telecharger chaque image
    const supplierSlug = slugify(supplierName);
    for (const product of products) {
        if (!product.imageUrl || product.imageUrl.includes('logo') || product.imageUrl.includes('icon')) continue;

        // Extraire la reference depuis l'alt, l'URL ou le texte
        let ref = product.ref || product.alt || '';
        ref = ref.replace(/[^a-zA-Z0-9]/g, '').substring(0, 20);
        if (!ref) {
            const urlParts = product.href.split('/');
            ref = urlParts[urlParts.length - 1] || 'unknown';
        }

        const filename = `mannequin-${slugify(ref)}.jpg`;
        const dirPath = path.join(STORAGE_DIR, supplierSlug);
        const filepath = path.join(dirPath, filename);

        if (fs.existsSync(filepath)) {
            results.skipped++;
            continue;
        }

        try {
            await downloadImage(product.imageUrl, filepath);
            results.downloaded++;
            process.stdout.write('.');
        } catch (e) {
            results.failed++;
        }
    }

    await page.close();
    console.log('');
}

// Lancer
scrapeAllBrands().catch(console.error);

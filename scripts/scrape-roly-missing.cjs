const puppeteer = require('puppeteer-core');
const fs = require('fs');
const path = require('path');
const https = require('https');
const http = require('http');

const CHROME_PATH = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const OUTPUT_FILE = path.join(__dirname, '..', 'storage', 'app', 'roly-missing-data.json');
const IMAGES_DIR = path.join(__dirname, '..', 'storage', 'app', 'public', 'products');
const sleep = ms => new Promise(r => setTimeout(r, ms));

// 37 missing products with their target categories
const MISSING_REFS = [
    { ref: 'CA6564', name: 'baby', cat: 105, sec: [11], star: true },
    { ref: 'CA7203', name: 'baby ls', cat: 105, sec: [13], star: true },
    { ref: 'PA9110', name: 'BONATI', cat: 49, sec: [], star: false },
    { ref: 'CA6550', name: 'braco', cat: null, sec: [], star: true },
    { ref: 'CA6698', name: 'breda', cat: 98, sec: [11], star: true },
    { ref: 'CA6683', name: 'capri', cat: 11, sec: [], star: false },
    { ref: 'CA2200', name: 'CORGI', cat: 11, sec: [], star: false },
    { ref: 'CA6652', name: 'detroit', cat: 90, sec: [], star: false },
    { ref: 'HV9304', name: 'EPSYLON', cat: 83, sec: [], star: false },
    { ref: 'PK5707', name: 'ermin', cat: 94, sec: [47], star: false },
    { ref: 'SU1115', name: 'espiro', cat: 224, sec: [], star: true },
    { ref: 'CA2201', name: 'Fiyi', cat: 11, sec: [], star: false },
    { ref: 'CA6690', name: 'Golden', cat: 98, sec: [11], star: true },
    { ref: 'CA6696', name: 'Golden woman', cat: 98, sec: [11], star: true },
    { ref: 'DB7200', name: 'Honey', cat: 104, sec: [11], star: true },
    { ref: 'BD7202', name: 'Honey ls', cat: 104, sec: [13], star: false },
    { ref: 'CA6689', name: 'Husky', cat: 11, sec: [], star: false },
    { ref: 'CA6691', name: 'Husky woman', cat: 11, sec: [], star: false },
    { ref: 'BM8401', name: 'JIMMY', cat: 80, sec: [], star: false },
    { ref: 'HV9320', name: 'MERAK', cat: 83, sec: [], star: false },
    { ref: 'CQ1120', name: 'MINSK', cat: 44, sec: [], star: false },
    { ref: 'CA6401', name: 'monaco', cat: 90, sec: [], star: false },
    { ref: 'PO6629', name: 'Montreal', cat: 16, sec: [], star: false },
    { ref: 'PA0306', name: 'MURRAY', cat: 91, sec: [], star: false },
    { ref: 'PA0341', name: 'rodas', cat: 93, sec: [], star: false },
    { ref: 'FR9402', name: 'SANTANA', cat: 85, sec: [], star: false },
    { ref: 'PA0307', name: 'SERENA', cat: 91, sec: [51], star: false },
    { ref: 'CB5201', name: 'sitka', cat: 226, sec: [47], star: false },
    { ref: 'CB5202', name: 'sitka woman', cat: 226, sec: [47], star: false },
    { ref: 'CA0304', name: 'slam', cat: 90, sec: [], star: false },
    { ref: 'CA0305', name: 'slam woman', cat: 90, sec: [], star: false },
    { ref: 'BE0434', name: 'STRATOS', cat: 91, sec: [51], star: false },
    { ref: 'BE0433', name: 'THEMA', cat: 91, sec: [], star: false },
    { ref: 'SU1074', name: 'VINSON', cat: 23, sec: [], star: false },
    { ref: 'CQ8420', name: 'VIVARIO', cat: 46, sec: [], star: false },
    { ref: 'CA6653', name: 'zolder', cat: 90, sec: [], star: false },
    { ref: 'CA6663', name: 'zolder woman', cat: 90, sec: [], star: false },
];

function downloadImage(url, filepath) {
    return new Promise((resolve, reject) => {
        const dir = path.dirname(filepath);
        if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
        const client = url.startsWith('https') ? https : http;
        client.get(url, { headers: { 'User-Agent': 'Mozilla/5.0' } }, res => {
            if ([301, 302, 307].includes(res.statusCode))
                return downloadImage(res.headers.location, filepath).then(resolve).catch(reject);
            if (res.statusCode !== 200) return reject(new Error(`HTTP ${res.statusCode}`));
            const chunks = [];
            res.on('data', d => chunks.push(d));
            res.on('end', () => {
                const buf = Buffer.concat(chunks);
                if (buf.length < 1000) return reject(new Error('Image too small'));
                fs.writeFileSync(filepath, buf);
                resolve(filepath);
            });
        }).on('error', reject);
    });
}

async function scrapeProduct(page, ref) {
    const url = `https://www.roly.es/model_${ref}`;
    console.log(`  Scraping ${ref} from ${url}...`);

    try {
        await page.goto(url, { waitUntil: 'networkidle2', timeout: 30000 });
        await sleep(2000); // Let SPA render

        // Extract product data from the rendered page
        const data = await page.evaluate(() => {
            const getText = (sel) => {
                const el = document.querySelector(sel);
                return el ? el.textContent.trim() : '';
            };
            const getAll = (sel) => [...document.querySelectorAll(sel)].map(el => el.textContent.trim());

            // Product name - usually in h1 or prominent heading
            let name = getText('h1') || getText('.product-name') || getText('.model-name') || '';

            // Description
            let description = getText('.product-description') || getText('.description') || '';
            if (!description) {
                // Try to find description in any large text block
                const paras = document.querySelectorAll('p');
                for (const p of paras) {
                    if (p.textContent.length > 50) {
                        description = p.textContent.trim();
                        break;
                    }
                }
            }

            // Composition/material
            let material = '';
            const allText = document.body.innerText;
            const compMatch = allText.match(/(?:composition|matière|material|fabric)[:\s]*([^\n]+)/i);
            if (compMatch) material = compMatch[1].trim();

            // Weight/grammage
            let grammage = '';
            const gramMatch = allText.match(/(\d+)\s*(?:g\/m²|gsm|gr\/m)/i);
            if (gramMatch) grammage = gramMatch[1];

            // Colors
            const colors = [];
            const colorElements = document.querySelectorAll('[class*="color"], [data-color], .swatch, .color-option');
            colorElements.forEach(el => {
                const colorName = el.getAttribute('title') || el.getAttribute('data-color') || el.getAttribute('alt') || el.textContent.trim();
                const bg = window.getComputedStyle(el).backgroundColor;
                if (colorName && colorName.length < 50) {
                    colors.push({ name: colorName, hex: bg });
                }
            });

            // Images
            const images = [];
            const imgElements = document.querySelectorAll('img[src*="product"], img[src*="model"], img[src*="image"], .product-image img, .gallery img');
            imgElements.forEach(img => {
                const src = img.src || img.getAttribute('data-src');
                if (src && !src.includes('logo') && !src.includes('icon')) {
                    images.push(src);
                }
            });

            // Also check for background images
            const allImgs = document.querySelectorAll('img');
            allImgs.forEach(img => {
                const src = img.src || '';
                if (src && src.includes('gorfactory') || src.includes('roly')) {
                    images.push(src);
                }
            });

            // Sizes
            const sizes = [];
            const sizeElements = document.querySelectorAll('[class*="size"], .size-option, [data-size]');
            sizeElements.forEach(el => {
                const size = el.textContent.trim();
                if (size && size.length < 10 && !sizes.includes(size)) {
                    sizes.push(size);
                }
            });

            return {
                name,
                description,
                material,
                grammage,
                colors: [...new Set(colors.map(c => JSON.stringify(c)))].map(c => JSON.parse(c)),
                images: [...new Set(images)],
                sizes,
                pageTitle: document.title,
                bodyTextLength: document.body.innerText.length,
                fullText: document.body.innerText.substring(0, 3000),
            };
        });

        return data;
    } catch (err) {
        console.error(`  ERROR scraping ${ref}: ${err.message}`);
        return { error: err.message };
    }
}

async function main() {
    console.log('Launching Chrome...');
    const browser = await puppeteer.launch({
        executablePath: CHROME_PATH,
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--window-size=1920,1080'],
    });

    const page = await browser.newPage();
    await page.setViewport({ width: 1920, height: 1080 });
    await page.setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');

    // First, let's visit the homepage to understand the SPA structure
    console.log('Loading roly.es homepage...');
    await page.goto('https://www.roly.es/fr', { waitUntil: 'networkidle2', timeout: 30000 });
    await sleep(3000);

    // Check what language/locale options exist
    const homeTitle = await page.title();
    console.log(`Homepage title: ${homeTitle}`);

    // Now scrape the first product to understand the page structure
    console.log('\n=== Scraping first product to understand structure ===');
    const testData = await scrapeProduct(page, MISSING_REFS[0].ref);
    console.log('Test result:', JSON.stringify(testData, null, 2).substring(0, 1000));

    // If test worked, scrape all
    const results = [];

    for (const item of MISSING_REFS) {
        const data = await scrapeProduct(page, item.ref);
        results.push({
            ref: item.ref,
            targetName: item.name,
            cat: item.cat,
            sec: item.sec,
            star: item.star,
            scraped: data,
        });
        await sleep(1500); // Be polite
    }

    // Save results
    fs.writeFileSync(OUTPUT_FILE, JSON.stringify(results, null, 2));
    console.log(`\nResults saved to ${OUTPUT_FILE}`);
    console.log(`Total: ${results.length} products scraped`);
    console.log(`Errors: ${results.filter(r => r.scraped.error).length}`);

    await browser.close();
}

main().catch(err => {
    console.error('Fatal error:', err);
    process.exit(1);
});

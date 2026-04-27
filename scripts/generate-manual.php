<?php
/**
 * Génère le manuel technique du site Marquage Textile au format Word (.docx)
 * Usage: php scripts/generate-manual.php
 */

require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Font;

$word = new PhpWord();
$word->setDefaultFontName('Calibri');
$word->setDefaultFontSize(11);

// Styles
$word->addTitleStyle(1, ['bold' => true, 'size' => 22, 'color' => '6B1D2A'], ['spaceBefore' => 400, 'spaceAfter' => 200]);
$word->addTitleStyle(2, ['bold' => true, 'size' => 16, 'color' => '1F2937'], ['spaceBefore' => 300, 'spaceAfter' => 100]);
$word->addTitleStyle(3, ['bold' => true, 'size' => 13, 'color' => '374151'], ['spaceBefore' => 200, 'spaceAfter' => 80]);

$bold = ['bold' => true];
$code = ['name' => 'Consolas', 'size' => 9, 'color' => '1F2937', 'bgColor' => 'F3F4F6'];
$note = ['italic' => true, 'color' => '6B7280'];

// ══════════════════════════════════════════════════════════════
// PAGE DE GARDE
// ══════════════════════════════════════════════════════════════
$section = $word->addSection();
$section->addTextBreak(6);
$section->addText('MANUEL TECHNIQUE', ['bold' => true, 'size' => 36, 'color' => '6B1D2A'], ['alignment' => Jc::CENTER]);
$section->addText('Marquage-Textile.fr', ['bold' => true, 'size' => 24, 'color' => '374151'], ['alignment' => Jc::CENTER]);
$section->addTextBreak(2);
$section->addText('Guide complet de reconstruction et maintenance', ['size' => 14, 'color' => '6B7280'], ['alignment' => Jc::CENTER]);
$section->addTextBreak(4);
$section->addText('Version : ' . date('d/m/Y'), $note, ['alignment' => Jc::CENTER]);
$section->addText('Ce document permet de reconstruire intégralement le site à partir de zéro.', $note, ['alignment' => Jc::CENTER]);

// ══════════════════════════════════════════════════════════════
// 1. PRÉSENTATION GÉNÉRALE
// ══════════════════════════════════════════════════════════════
$section = $word->addSection();
$section->addTitle('1. Présentation générale', 1);

$section->addTitle('Stack technique', 2);
$section->addListItem('Framework : Laravel 12 (PHP 8.3)', 0, $bold);
$section->addListItem('Admin : Filament 3', 0, $bold);
$section->addListItem('Frontend réactif : Livewire 3', 0, $bold);
$section->addListItem('CSS : Tailwind CSS 3 + Vite', 0, $bold);
$section->addListItem('Base de données : MySQL 8', 0, $bold);
$section->addListItem('PDF : DomPDF + Factur-X (horstoeko/zugferd)', 0, $bold);
$section->addListItem('Paiement : Systempay (Banque Populaire)', 0, $bold);
$section->addListItem('Excel : Maatwebsite/Excel', 0, $bold);
$section->addListItem('Hébergement : OVH', 0, $bold);

$section->addTitle('Domaines', 2);
$section->addListItem('Production : marquage-textile.fr', 0);
$section->addListItem('Local : marquage-textile.test (WAMP)', 0);
$section->addListItem('Admin : /admin (Filament)', 0);

$section->addTitle('Structure du projet', 2);
$section->addText('Le projet suit la structure standard Laravel :', $note);
$section->addListItem('app/Models/ — 32 modèles Eloquent', 0);
$section->addListItem('app/Livewire/ — 13 composants réactifs', 0);
$section->addListItem('app/Filament/ — 13 resources + 8 pages + 3 widgets', 0);
$section->addListItem('app/Services/ — 16 services métier', 0);
$section->addListItem('app/Console/Commands/ — 25 commandes artisan', 0);
$section->addListItem('app/Mail/ — 13 classes d\'email', 0);
$section->addListItem('database/migrations/ — 60+ migrations', 0);

// ══════════════════════════════════════════════════════════════
// 2. INSTALLATION DEPUIS ZÉRO
// ══════════════════════════════════════════════════════════════
$section->addTitle('2. Installation depuis zéro', 1);

$section->addTitle('Prérequis serveur', 2);
$section->addListItem('PHP 8.3+ avec extensions : gd, mbstring, mysql, xml, zip, curl, intl', 0);
$section->addListItem('MySQL 8+', 0);
$section->addListItem('Composer 2+', 0);
$section->addListItem('Node.js 18+ + npm', 0);

$section->addTitle('Étapes d\'installation', 2);
$section->addText('1. Cloner le dépôt Git :', $bold);
$section->addText('git clone [URL_DEPOT] marquage-textile', $code);
$section->addText('cd marquage-textile', $code);
$section->addTextBreak();

$section->addText('2. Installer les dépendances PHP :', $bold);
$section->addText('composer install', $code);
$section->addTextBreak();

$section->addText('3. Installer les dépendances JS et compiler :', $bold);
$section->addText('npm install', $code);
$section->addText('npm run build', $code);
$section->addTextBreak();

$section->addText('4. Copier et configurer l\'environnement :', $bold);
$section->addText('cp .env.example .env', $code);
$section->addText('php artisan key:generate', $code);
$section->addTextBreak();

$section->addText('5. Créer la base de données et migrer :', $bold);
$section->addText('mysql -u root -e "CREATE DATABASE marquage_textile"', $code);
$section->addText('php artisan migrate', $code);
$section->addTextBreak();

$section->addText('6. Créer le lien symbolique storage :', $bold);
$section->addText('php artisan storage:link', $code);

// ══════════════════════════════════════════════════════════════
// 3. CONFIGURATION .ENV
// ══════════════════════════════════════════════════════════════
$section = $word->addSection();
$section->addTitle('3. Configuration .env', 1);

$section->addTitle('Variables essentielles', 2);

$envVars = [
    ['Section', 'Variable', 'Description'],
    ['App', 'APP_URL', 'URL du site (marquage-textile.fr en prod)'],
    ['App', 'APP_ENV', 'production en prod, local en dev'],
    ['App', 'APP_DEBUG', 'false en prod, true en dev'],
    ['DB', 'DB_DATABASE', 'marquage_textile'],
    ['DB', 'DB_USERNAME', 'Utilisateur MySQL'],
    ['DB', 'DB_PASSWORD', 'Mot de passe MySQL'],
    ['Session', 'SESSION_DRIVER', 'database'],
    ['Cache', 'CACHE_STORE', 'database'],
    ['Log', 'LOG_LEVEL', 'warning en prod (IMPORTANT sinon le disque se remplit)'],
    ['Mail', 'MAIL_HOST', 'ssl0.ovh.net'],
    ['Mail', 'MAIL_PORT', '465'],
    ['Mail', 'MAIL_USERNAME', 'contact@marquage-textile.fr'],
    ['Mail', 'MAIL_ENCRYPTION', 'ssl'],
    ['Mail', 'MAIL_FACTURES_FROM', 'factures@marquage-textile.fr'],
    ['Mail', 'MAIL_COMMANDES_FROM', 'commandes@marquage-textile.fr'],
    ['Mail', 'MAIL_PAO_FROM', 'pao@marquage-textile.fr'],
    ['Paiement', 'SYSTEMPAY_SHOP_ID', '39994382'],
    ['Paiement', 'SYSTEMPAY_MODE', 'PRODUCTION en prod, TEST en dev'],
    ['Toptex', 'TOPTEX_URL', 'https://api.toptex.io'],
    ['Toptex', 'TOPTEX_API_KEY', 'Clé API Toptex'],
    ['Valento', 'VALENTO_URL', 'https://www.valento.es/rest'],
    ['Solo', 'SOLO_BASE_URL', 'https://api.sologroup-paris.com/BEE/V1'],
    ['Roly', 'ROLY_EMAIL', 'Email compte GorFactory'],
];

$table = $section->addTable(['borderSize' => 1, 'borderColor' => 'D1D5DB']);
foreach ($envVars as $i => $row) {
    $style = $i === 0 ? ['bgColor' => '6B1D2A', 'bold' => true, 'color' => 'FFFFFF'] : [];
    $bgColor = $i === 0 ? '6B1D2A' : ($i % 2 === 0 ? 'F9FAFB' : 'FFFFFF');
    $table->addRow();
    $table->addCell(1500, ['bgColor' => $bgColor])->addText($row[0], $style ?: ['bold' => true, 'size' => 9]);
    $table->addCell(3000, ['bgColor' => $bgColor])->addText($row[1], $style ?: $code);
    $table->addCell(5500, ['bgColor' => $bgColor])->addText($row[2], $style ?: ['size' => 9]);
}

// ══════════════════════════════════════════════════════════════
// 4. FOURNISSEURS
// ══════════════════════════════════════════════════════════════
$section = $word->addSection();
$section->addTitle('4. Intégrations fournisseurs', 1);

$suppliers = [
    ['Toptex', 'REST API', 'Horaire', 'import:toptex', 'Stock horaire, catalogue complet'],
    ['Valento', 'REST API', '3h-6h uniquement', 'import:valento', 'Stock + prix, créneau limité'],
    ['Solo (SOL\'S)', 'OAuth2 API', 'Horaire', 'import:solo', 'Stock horaire'],
    ['Roly', 'REST API (GorFactory)', 'En attente', 'import:roly', 'Credentials à renouveler'],
    ['Imbretex', 'REST API', 'Manuel', 'import:imbretex', 'Via page Filament'],
];

$table = $section->addTable(['borderSize' => 1, 'borderColor' => 'D1D5DB']);
$table->addRow();
foreach (['Fournisseur', 'Type API', 'Fréquence', 'Commande', 'Notes'] as $h) {
    $table->addCell(2000, ['bgColor' => '6B1D2A'])->addText($h, ['bold' => true, 'color' => 'FFFFFF', 'size' => 9]);
}
foreach ($suppliers as $i => $row) {
    $bg = $i % 2 === 0 ? 'F9FAFB' : 'FFFFFF';
    $table->addRow();
    foreach ($row as $cell) {
        $table->addCell(2000, ['bgColor' => $bg])->addText($cell, ['size' => 9]);
    }
}

// ══════════════════════════════════════════════════════════════
// 5. SYSTÈME DE MAILS
// ══════════════════════════════════════════════════════════════
$section->addTitle('5. Système d\'emails (12 mails)', 1);

$mails = [
    ['1', 'Confirmation commande', 'commandes@', 'Client', 'Paiement CB reçu ou virement confirmé'],
    ['2', 'Facture complète', 'factures@', 'Client', 'Paiement intégral reçu'],
    ['3', 'Attente virement', 'commandes@', 'Client', 'Client choisit virement'],
    ['4', 'Expiration devis', 'commandes@', 'Client', 'Scheduler: J-7, J-2, J-1'],
    ['5', 'BAT prêt', 'pao@', 'Client', 'Admin uploade le BAT'],
    ['6', 'BAT modif/validation', 'pao@', 'Client', 'Client modifie ou valide'],
    ['7', 'Paiement reçu', 'commandes@', 'Client', 'Admin valide paiement (Filament)'],
    ['8', 'Notif admin commande', 'admin@+gmail', 'Admin', 'Nouvelle commande virement'],
    ['9', 'BAT validé', 'pao@+admin@', 'Admin', 'Client valide le BAT'],
    ['10', 'Prêt à expédier', 'commandes@', 'Client', 'Commande prête'],
    ['11', 'Facture acompte', 'factures@', 'Client', 'Paiement 50% reçu'],
    ['12', 'Facture soldée', 'factures@', 'Client', 'Solde payé, facture régénérée'],
];

$table = $section->addTable(['borderSize' => 1, 'borderColor' => 'D1D5DB']);
$table->addRow();
foreach (['N°', 'Objet', 'Expéditeur', 'Dest.', 'Déclencheur'] as $h) {
    $table->addCell(null, ['bgColor' => '6B1D2A'])->addText($h, ['bold' => true, 'color' => 'FFFFFF', 'size' => 9]);
}
foreach ($mails as $i => $row) {
    $bg = $i % 2 === 0 ? 'F9FAFB' : 'FFFFFF';
    $table->addRow();
    foreach ($row as $cell) {
        $table->addCell(null, ['bgColor' => $bg])->addText($cell, ['size' => 9]);
    }
}

// ══════════════════════════════════════════════════════════════
// 6. FACTURATION
// ══════════════════════════════════════════════════════════════
$section = $word->addSection();
$section->addTitle('6. Système de facturation', 1);

$section->addText('Les factures sont au format Factur-X (normes françaises 2027) :', $bold);
$section->addListItem('PDF/A-3 avec XML embarqué (norme EN 16931)', 0);
$section->addListItem('Numérotation séquentielle : FA-YYYYMM-XXXX', 0);
$section->addListItem('Statuts : deposit (acompte), paid (payée), settled (soldée)', 0);
$section->addListItem('Stockées dans storage/app/private/invoices/', 0);
$section->addListItem('Disponibles 18 mois côté client, indéfiniment en back-office', 0);

$section->addTitle('Flux paiement CB', 3);
$section->addText('Systempay IPN → markAsPaid() → facture auto + mail 1 + mail 2/11');

$section->addTitle('Flux virement', 3);
$section->addText('Client confirme → mail 1 + mail 3 + mail 8');
$section->addText('Admin "Valider paiement" (Filament) → markAsPaid() → facture + mails');
$section->addText('Admin "Solder" → settleInvoice() → facture soldée + mail 12');

// ══════════════════════════════════════════════════════════════
// 7. TÂCHES PLANIFIÉES
// ══════════════════════════════════════════════════════════════
$section->addTitle('7. Tâches planifiées (cron)', 1);

$section->addText('Ajouter au crontab du serveur :', $bold);
$section->addText('* * * * * cd /path/to/marquage-textile && php artisan schedule:run >> /dev/null 2>&1', $code);
$section->addTextBreak();

$tasks = [
    ['Horaire', 'import:toptex --stock-only', 'Sync stock Toptex'],
    ['Horaire', 'import:solo --stock-only', 'Sync stock Solo'],
    ['03:15', 'import:valento --stock-only', 'Sync stock Valento (créneau 3h-6h)'],
    ['03:30', 'import:valento --prices-only', 'Sync prix Valento'],
    ['04:00', 'maintenance:cleanup', 'Nettoyage sessions/cache/logs/fichiers'],
    ['05:00', 'import:valento --stock-only', 'Sync stock Valento (2e passe)'],
    ['06:00', 'quotes:expire', 'Expiration des devis'],
    ['09:00', 'quotes:remind', 'Relance devis expirants'],
];

$table = $section->addTable(['borderSize' => 1, 'borderColor' => 'D1D5DB']);
$table->addRow();
foreach (['Fréquence', 'Commande', 'Description'] as $h) {
    $table->addCell(null, ['bgColor' => '6B1D2A'])->addText($h, ['bold' => true, 'color' => 'FFFFFF', 'size' => 9]);
}
foreach ($tasks as $i => $row) {
    $bg = $i % 2 === 0 ? 'F9FAFB' : 'FFFFFF';
    $table->addRow();
    foreach ($row as $cell) {
        $table->addCell(null, ['bgColor' => $bg])->addText($cell, ['size' => 9]);
    }
}

// ══════════════════════════════════════════════════════════════
// 8. GRILLES TARIFAIRES
// ══════════════════════════════════════════════════════════════
$section = $word->addSection();
$section->addTitle('8. Grilles tarifaires marquage', 1);

$grids = [
    ['Sérigraphie', 'serigraphie_pricings', '42 lignes', 'Clair/foncé, 1-6 couleurs, quantités'],
    ['DTF', 'transfer_pricings (technique=dtf)', '40 lignes', 'Formats A7→A3, quantités'],
    ['Transfert Offset', 'transfer_pricings (technique=transfert-offset)', '30 lignes', 'Formats A7→A3'],
    ['Broderie', 'transfer_pricings (technique=broderie)', '28 lignes', 'Formats A7→A4'],
    ['Transfert Sérigraphique', 'transfer_pricings (technique=transfert-serigraphique)', '30 lignes', 'Formats A7→A3, 1 couleur'],
    ['DTG', 'transfer_pricings (technique=dtg)', 'Variable', 'Formats, quantités'],
    ['Autres', 'technique_pricings', 'Variable', 'Anciennes données scrapées'],
];

$table = $section->addTable(['borderSize' => 1, 'borderColor' => 'D1D5DB']);
$table->addRow();
foreach (['Technique', 'Table', 'Lignes', 'Paramètres'] as $h) {
    $table->addCell(null, ['bgColor' => '6B1D2A'])->addText($h, ['bold' => true, 'color' => 'FFFFFF', 'size' => 9]);
}
foreach ($grids as $i => $row) {
    $bg = $i % 2 === 0 ? 'F9FAFB' : 'FFFFFF';
    $table->addRow();
    foreach ($row as $cell) {
        $table->addCell(null, ['bgColor' => $bg])->addText($cell, ['size' => 9]);
    }
}

// ══════════════════════════════════════════════════════════════
// 9. DÉPLOIEMENT PRODUCTION
// ══════════════════════════════════════════════════════════════
$section->addTitle('9. Déploiement en production (OVH)', 1);

$section->addTitle('Points critiques', 2);
$section->addListItem('LOG_LEVEL=warning dans .env (PAS debug, sinon le disque OVH se remplit)', 0, $bold);
$section->addListItem('SYSTEMPAY_MODE=PRODUCTION', 0, $bold);
$section->addListItem('APP_DEBUG=false', 0, $bold);
$section->addListItem('APP_ENV=production', 0, $bold);

$section->addTitle('Commandes de déploiement', 2);
$section->addText('composer install --no-dev --optimize-autoloader', $code);
$section->addText('npm install && npm run build', $code);
$section->addText('php artisan migrate --force', $code);
$section->addText('php artisan config:cache', $code);
$section->addText('php artisan route:cache', $code);
$section->addText('php artisan view:cache', $code);
$section->addText('php artisan storage:link', $code);

$section->addTitle('Cron à configurer', 2);
$section->addText('* * * * * cd /home/marquage-textile && php artisan schedule:run >> /dev/null 2>&1', $code);

$section->addTitle('Problème connu : ancien site PrestaShop', 2);
$section->addText('L\'ancien site crashait car le flux de visiteurs gonflait la mémoire/disque OVH. Le nettoyage automatique (maintenance:cleanup) tourne chaque nuit à 4h pour éviter ce problème. Il purge : sessions > 7j, cache expiré, logs > 10 Mo, fichiers commandes archivées > 60j, visites > 6 mois.', $note);

// ══════════════════════════════════════════════════════════════
// 10. URLS SEO
// ══════════════════════════════════════════════════════════════
$section = $word->addSection();
$section->addTitle('10. Structure des URLs SEO', 1);

$urls = [
    ['Accueil', '/', 'HomeController'],
    ['Catalogue', '/catalogue', 'CatalogueController@index'],
    ['Catégorie', '/{slug}', 'Catch-all → CatalogueController@category'],
    ['Produit', '/{categorie}/{produit}', 'CatalogueController@product'],
    ['Technique', '/{seo_url}', 'Catch-all → TechniqueController@show'],
    ['Devis/Contact', '/devis?sujet=...', 'DemandeDevisController'],
    ['Compte client', '/mon-compte/...', 'AccountController'],
    ['Checkout', '/commande/checkout', 'Livewire Checkout'],
    ['Admin', '/admin', 'Filament'],
];

$table = $section->addTable(['borderSize' => 1, 'borderColor' => 'D1D5DB']);
$table->addRow();
foreach (['Page', 'URL', 'Controller'] as $h) {
    $table->addCell(null, ['bgColor' => '6B1D2A'])->addText($h, ['bold' => true, 'color' => 'FFFFFF', 'size' => 9]);
}
foreach ($urls as $i => $row) {
    $bg = $i % 2 === 0 ? 'F9FAFB' : 'FFFFFF';
    $table->addRow();
    foreach ($row as $cell) {
        $table->addCell(null, ['bgColor' => $bg])->addText($cell, ['size' => 9]);
    }
}

$section->addText('Redirections 301 automatiques depuis les anciennes URLs (/catalogue/{slug} et /produit/{slug}).', $note);

// ══════════════════════════════════════════════════════════════
// 11. FONCTIONNALITÉS PRINCIPALES
// ══════════════════════════════════════════════════════════════
$section->addTitle('11. Fonctionnalités principales', 1);

$features = [
    'Catalogue' => 'Plus de 5000 produits de 5 fournisseurs, synchronisation automatique des stocks et prix.',
    'Devis en ligne' => 'Bloc devis amovible (FloatingQuote), recommandation automatique de technique (moins cher + premium), multi-logos, groupes figés.',
    'Commandes' => 'Checkout avec paiement CB (Systempay) ou virement, acompte 50%, flux BAT pour marquage.',
    'Facturation' => 'Factures Factur-X (normes 2027), PDF/A-3 avec XML, numérotation séquentielle, export Excel.',
    'Back-office' => 'Filament 3 avec dashboard interactif, statistiques, chat live, gestion commandes/factures/produits.',
    'Chat' => 'Widget chatbot côté client (FAQ + suivi commande + chat live), interface admin avec polling.',
    'SEO' => 'URLs optimisées, JSON-LD, méta-descriptions, contenu technique, sitemap, redirections 301.',
    'Sécurité' => 'Honeypot anti-bot sur tous les formulaires, rate limiting sur le chat, sanitization XSS.',
    'Maintenance' => 'Nettoyage automatique nocturne (sessions, cache, logs, fichiers, visites).',
    'Images' => 'Toutes les images en local (pas de hotlink fournisseur), compressées < 150 Ko, nommage SEO.',
];

foreach ($features as $name => $desc) {
    $section->addListItem($name . ' : ' . $desc, 0);
}

// ══════════════════════════════════════════════════════════════
// 12. ROADMAP
// ══════════════════════════════════════════════════════════════
$section->addTitle('12. Roadmap / À faire', 1);
$section->addListItem('Module BAT prévisualisation : preview visuelle du marquage sur le textile, greffé sur la page devis (post-prod)', 0);
$section->addListItem('API Roly : renouveler les identifiants GorFactory', 0);
$section->addListItem('Notifications push : pour le chat live sur mobile (Service Worker)', 0);
$section->addListItem('Coûts marquage : intégrer les coûts par technique pour le calcul de marge dans les statistiques', 0);

// ══════════════════════════════════════════════════════════════
// 13. CONTACTS & ACCÈS
// ══════════════════════════════════════════════════════════════
$section = $word->addSection();
$section->addTitle('13. Contacts et accès', 1);
$section->addListItem('Hébergeur : OVH', 0);
$section->addListItem('Domaine : marquage-textile.fr', 0);
$section->addListItem('SIRET : 794 260 954 00033', 0);
$section->addListItem('TVA intra : FR54 790260954', 0);
$section->addListItem('Adresse : 19 rue de la Résistance, 59155 Faches-Thumesnil', 0);
$section->addListItem('Téléphone : 03 20 40 06 90', 0);
$section->addListItem('Email principal : contact@marquage-textile.fr', 0);
$section->addListItem('Systempay Shop ID : 39994382', 0);

// Sauvegarde
$filename = __DIR__ . '/../public/manuel-technique-marquage-textile.docx';
$writer = \PhpOffice\PhpWord\IOFactory::createWriter($word, 'Word2007');
$writer->save($filename);

echo "Manuel généré : $filename\n";
echo "Taille : " . round(filesize($filename) / 1024) . " Ko\n";

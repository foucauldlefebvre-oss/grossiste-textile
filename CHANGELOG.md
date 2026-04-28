# Changelog — grossiste-textile.fr

Toutes les modifications notables apportées au fork depuis sa création.

Format : sections par étape de transformation depuis le code parent (marquage-textile.fr).

## [Étape 2c] DROP COLUMN BAT/marking — 2026-04-28

### Backup DB obligatoire avant migrations

- `backups/grossiste_textile_pre_2c_2026-04-28.sql` (33 MB) — backup intégral grossiste avant drop columns

### Migrations (4)

| Migration | Table | Colonnes droppées |
|-----------|-------|-------------------|
| `010001_drop_bat_columns_from_orders_table` | orders | has_marking, status_bat, bat_status, bat_client_comment, bat_client_decided_at, bat_token, bat_done_at (7 cols) |
| `010002_drop_marking_columns_from_order_items_table` | order_items | marking_technique_id (+FK), marking_price_ht, visual_file, marking_zone, visual_colors, bat_pdf, bat_status (7 cols) |
| `010003_drop_compatible_techniques_from_products_table` | products | compatible_techniques (1 col) |
| `010004_drop_default_techniques_from_categories_table` | categories | default_techniques (1 col, orphelin trouvé pendant l'inventaire) |

**Total : 16 colonnes droppées + 1 FK.**

### Cleanup code orphelin (post drop)

- `Order.php` : retrait des `// TODO 2c` + retrait de la relation `quote()` orpheline
- `OrderItem.php` : retrait des `// TODO 2c` + relation `technique()` orpheline retirée
- `Product.php` : retrait des `// TODO 2c` sur `compatible_techniques`
- `ChatWidget::lookupOrder()` : retrait du `bat_status` orphelin du `select(...)`

### Tests passés

- ✅ artisan about (boot OK)
- ✅ artisan route:list (92 routes)
- ✅ Home 200, catégorie 200, fiche produit 200, /panier guest 302, /admin/login 200
- ✅ Aucune erreur SQL dans `storage/logs/laravel.log`

### Note technique — migration 010002

La première tentative de migration a échoué car j'avais inclus `marking_group` dans la liste des colonnes à drop, alors qu'elle n'existait que sur `quote_items` (pas sur `order_items`). Conséquence : la `dropForeign` avait été exécutée mais pas les `dropColumn`. La migration corrigée vérifie l'existence de la FK avant de tenter `dropForeign` (idempotent), puis drop les bonnes colonnes.

## [Étape 2b] Refactor panier B2B (Cart/CartItem + Checkout/Sélecteur produit) — 2026-04-28

### Architecture B2B simplifiée

- **Pas de guest cart** : ajout au panier réservé aux comptes professionnels (Q2)
- **Sidebar flottante + page `/panier` dédiée** (Q1)
- **Sélecteur produit Livewire** : couleurs + tailles + quantités + prix dégressif live
- **Visiteur non connecté** : prix masqués, CTA "Créer un compte gratuit pour acheter"

### Migrations (3)

- `create_carts_table` : panier user (status active/converted/abandoned, totals, shipping_zone, vat_exemption)
- `create_cart_items_table` : lignes panier (FK product/color/size, quantité, prix)
- `drop_quote_id_from_orders_table` : retrait FK orders.quote_id (Q6)

### Nouveaux fichiers (10 PHP + 6 Blade)

- `app/Models/Cart.php`, `CartItem.php`
- `app/Services/CartService.php` (constants TVA + shipping zones, addItem/updateQty/recalculate/convertToOrder/abandonStaleCarts)
- `app/Livewire/CartCounter.php`, `CartSidebar.php`, `ProductSelector.php`, `CartPage.php`, `CheckoutForm.php` (+ vues)
- `resources/views/cart/index.blade.php`
- `app/Filament/Resources/CartResource.php` + `Pages/ListCarts.php`, `ViewCart.php` (Q3 — CartResource minimal lecture seule pour suivi commercial)

### Suppressions définitives (~20 fichiers)

- Modèles : `Quote`, `QuoteItem`
- Services : `QuoteService`, `MarkingRecommendationService`, `BatService`, `QuotePdfService` (déjà fait en 2a)
- Livewire : `FloatingQuote`, `Checkout`
- Filament : `QuoteResource` + Pages + RelationManager
- Controllers : `Front\QuoteController`
- Console : `ExpireQuotes`, `SendQuoteExpiringReminders`
- Mails : `QuoteBatReadyMail`, `QuoteExpiringMail`
- Vues : `account/quotes.blade.php`, `front/quote/`

### Modifications (15+ fichiers)

- **Routes** : ajout `/panier` (auth), retrait définitif des routes /devis et /bat (déjà commentées en 2a), `/commande/checkout` utilise `<livewire:checkout-form>`
- **OrderService** : `createFromQuote()` → `createFromCart()`, utilise `CartService::TVA_RATE`
- **PaymentController** : utilise `Cart` + `OrderService::createFromCart()`, redirect `/cart` au lieu de `/devis`
- **app.blade.php** : `<livewire:cart-counter />` (badge header) + `<livewire:cart-sidebar />` (drawer)
- **catalogue/product.blade.php** : remplace placeholder par `<livewire:product-selector :product="$product" />`
- **OrderResource** (Filament) : `QuoteService::SHIPPING_ZONES` → `CartService::SHIPPING_ZONES` ; suppression bloc quote_id
- **StatsOverviewWidget** : `Quote::count()` → `Cart::active()->where('total_ht', '>', 0)->count()` (Q4)
- **DashboardChartWidget** : filter 'quotes' → 'carts'
- **Statistics page** : `quotes_created/accepted` → `carts_created/converted` ; suppression `getTopTechniques()`
- **MaintenanceCleanup** : `cleanExpiredQuotes()` → `abandonStaleCarts()` (30j → abandoned, 90j → suppression)
- **routes/console.php** : suppression schedules `quotes:expire` et `quotes:remind`
- **ChatWidget** : `getFileFormats()` retourne `[]` (MarkingTechnique dégagé), suppression bloc BAT dans `lookupOrder()`
- **GroupShopProduct** : commenter `marking_technique_id` du fillable + relation `technique()`
- **GroupShopOrderForm** : retrait `use QuoteService` orphelin
- **AuthController, AccountController, account/dashboard.blade.php** : nettoyages additionnels (suppression `route('account.quotes')`, `mon-devis`)
- **account/order-show.blade.php** : neutralisation des blocs `has_marking`, `bat_status`, `$item->technique` via `@if(false)` (TODO 2b)

### Tests fonctionnels passés

- ✅ `artisan about` (Application Name = "Grossiste Textile", boot OK)
- ✅ `artisan route:list` (92 routes, aucune erreur)
- ✅ Home `/` HTTP 200 (276 KB)
- ✅ Catégorie `/t-shirts` HTTP 200 (357 KB)
- ✅ Fiche produit `/bebe/baby-soft-cap` HTTP 200 (258 KB) — affiche CTA "Créer un compte" en guest
- ✅ `/panier` guest → HTTP 302 (redirect login attendu)
- ✅ `/admin/login` HTTP 200

### Limites & TODO

- Pages `account/order-show.blade.php` neutralisent les blocs BAT via `@if(false)` — à refondre proprement en étape contenu/UX
- Étape 2c restant : DROP COLUMN sur `quotes`, `quote_items`, `orders` (BAT cols), `order_items` (marking cols), `products` (compatible_techniques)
- Étape 2d restant : DROP TABLE pour `quotes`, `quote_items`, `marking_techniques`, `technique_*`, `serigraphie_pricings`, `transfer_pricings`, `techniques_marquage`
- Tests automatisés : aucun pour l'instant (validation manuelle)

## [Étape 2-import] Copie catalogue depuis marquage_textile — 2026-04-28

### Données importées dans `grossiste_textile`

8 tables copiées depuis `marquage_textile` via mysqldump puis import (FOREIGN_KEY_CHECKS=0) :

| Table | Lignes |
|-------|--------|
| brand_colors | 1 955 |
| brand_sizes | 575 |
| pricing_rules | 55 |
| categories | 224 |
| products | 5 260 |
| product_colors | 27 860 |
| product_sizes | 122 346 |
| product_categories | 2 836 |

**Backups conservés** dans `/backups/` (gitignored) :
- `marquage_textile_full_2026-04-27.sql` (37 MB) — backup intégral marquage
- `grossiste_import_2026-04-27/` — 8 dumps individuels (39 MB)

### Modifications collatérales pendant l'import

- `CatalogueController::product()` : retrait du eager-load `techniqueRules` (relation supprimée en 2a)
- `catalogue/product.blade.php` : retrait du `<livewire:product-configurator>` (composant supprimé en 2a) → remplacé par un placeholder en attendant 2b
- `.gitignore` : ajout `/backups`

### Tests fonctionnels passés

- ✅ Home : HTTP 200 (275 KB)
- ✅ Catégorie `/t-shirts` : HTTP 200 (357 KB)
- ✅ Fiche produit `/bebe/baby-soft-cap` : HTTP 200 (254 KB)
- ✅ `display_price` calculé correctement (3.91 EUR sur le test)
- ✅ Stats : 127 catégories actives, 5065 produits actifs

### ⚠️ État connu — prix à recalculer

Les prix `base_price` actuellement en DB grossiste correspondent aux **coefficients B2C marquage-textile** (trop élevés pour du grossiste). À traiter dans une phase ultérieure :
1. Adapter les coefficients dans `pricing_rules` selon la marge B2B grossiste
2. Lancer `php artisan products:apply-pricing` pour recalculer `base_price` sur tous les produits

## [Étape 2a] Suppression code purement marquage — 2026-04-27

### Supprimé (~70 fichiers)

**Modèles** : MarkingTechnique, TechniqueConstraint, TechniquePricing, ProductTechniqueRule, BroderiePricing, SerigraphiePricing, TransferPricing, TechniqueMarquage

**Controllers** : TechniqueController, BatController, OrderBatController, DemandeDevisController

**Filament resources** : MarkingTechniqueResource, TechniqueMarquageResource, SerigraphiePricingResource, ProductResource/RelationManagers/TechniqueRulesRelationManager

**Livewire** : ProductConfigurator, BatApproval, QuotePage, QuoteCounter (+ vues blade)

**Services** : MarkingRecommendationService, BatService, QuotePdfService

**Console commands** : FixProductTechniques

**Jobs** : SendBatEmailJob, NotifyAdminBatDecisionJob

**Helpers/config** : BatHelper, config/bat.php

**Vues** : technique/, bat/ (+ 14 SVG zones), order-bat/, contact/devis, catalogue/devis, pdf/bat, pdf/quote, emails/bat-*, emails/quote-expiring, components/carousel-techniques, components/quote-validity-modal

**Seeders** : MarkingTechniqueSeeder, TechniqueMarquageSeeder, BroderiePricingSeeder, SerigraphiePricingSeeder, TransferPricingSeeder, TechniqueSeoSeeder

### Modifié — références retirées ou commentées avec `// TODO 2b: refactor`

**Routes** (`routes/web.php`) : retrait /mon-devis, /devis (GET+POST), /bat/*, /commande/bat/*, /devis/{ref}/*, /techniques-de-marquage, /mon-compte/devis, branche has_marking dans virement/confirmer, lookup MarkingTechnique dans le catch-all SEO

**Modèles** : champs BAT/markings/marking_* commentés dans Quote/QuoteItem/Order/OrderItem avec `// TODO 2c: drop column`. Product : compatible_techniques + relations techniqueRules / compatibleTechniques + hook booted() commentés.

**Controllers** : SitemapController (bloc Technique SEO retiré), HomeController (variable techniques → collection vide), AuthController (redirect mon-devis → dashboard), PaymentController (redirect devis → home), DatabaseSeeder (MarkingTechniqueSeeder retiré)

**Vues** : `app.blade.php` (livewire:quote-counter, lien Techniques de marquage, sous-menu Contact devis, footer techniques+devis), `home.blade.php` (carousel-techniques, boutons devis), `account/dashboard.blade.php` (carte Devis cachée)

### Stubs pour boot pendant transition

- `QuoteService.php` (917 → 130 lignes) : conserve constantes TVA_RATE, SHIPPING_ZONES, ZONE_COUNTRIES + méthodes statiques shipping + recalculate() simplifié + removeItem(). Toutes les méthodes liées au marquage lèvent une `RuntimeException` explicite.
- `FloatingQuote.php` (966 → 19 lignes) : composant Livewire stub minimal, retourne une vue vide.
- `floating-quote.blade.php` (830 → 4 lignes) : `<div class="hidden"></div>` en attendant la reconstruction du panier B2B en 2b.

### Tests passés (avant commit)

- ✅ `php artisan about` (Application Name = "Grossiste Textile", Laravel 11.48, PHP 8.3.28)
- ✅ `php artisan route:list` (91 routes, aucune erreur)
- ✅ Page d'accueil rendue (HTTP 200, 43kb)
- ✅ Filament admin login rendu (HTTP 200)

### Reste à faire

- **2b** : reconstruire le panier B2B (Cart/CartItem), supprimer Quote/QuoteItem, FloatingQuote/Checkout définitivement
- **2c** : migrations DROP COLUMN sur orders, order_items, quotes, quote_items, products
- **2d** : migrations DROP TABLE pour marking_techniques, technique_pricings, technique_constraints, product_technique_rules, serigraphie_pricings, transfer_pricings, techniques_marquage

## [Étape 1] Configuration de base — 2026-04-27

### Ajouté
- `.env` : nouveau projet (`APP_NAME="Grossiste Textile"`, `APP_URL=http://grossiste-textile.test`, `DB_DATABASE=grossiste_textile`, `MAIL_MAILER=log` pour dev)
- `CHANGELOG.md` (ce fichier)
- Base de données MySQL `grossiste_textile` (vide, créée localement)
- Credentials API TopTex + Roly + Imbretex copiées depuis le `.env` parent

### Non repris du parent
- Valento, Solo (pas dans le scope grossiste pour l'instant)
- Stripe, Systempay (paiement reporté à une phase ultérieure)
- SMTP OVH (`MAIL_MAILER=log` en dev — emails dans `storage/logs/laravel.log`)
- Adresses email service (devis/PAO/factures/commandes — concernent marquage-textile)

### TODO / À surveiller
- ⚠️ Vérifier avec TopTex / Roly / Imbretex si leur contrat autorise l'utilisation de l'API sur 2 sites distincts. Pas bloquant en dev local mais à valider avant mise en production publique.
- Mettre en place la connexion DB partagée pour clients + numérotation factures (série F unique) — phase ultérieure, pour l'instant base séparée.

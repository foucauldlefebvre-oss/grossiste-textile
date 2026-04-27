# Changelog — grossiste-textile.fr

Toutes les modifications notables apportées au fork depuis sa création.

Format : sections par étape de transformation depuis le code parent (marquage-textile.fr).

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

# TODO — grossiste-textile.fr

Notes et points d'attention identifiés pendant la transformation du fork.

## Étape 2b — refactor panier B2B (suite immédiate)

- [ ] Reconstruire le panier en `Cart` / `CartItem` (Q1 décidé en plan initial)
- [ ] Supprimer définitivement `Quote`, `QuoteItem`, `FloatingQuote`, `Checkout` (stubés en 2a)
- [ ] Reconstruire le sélecteur produit Livewire (`<livewire:product-configurator>` retiré en 2a — placeholder en place sur la fiche produit)
- [ ] Migrations DROP COLUMN sur `orders`, `order_items`, `quotes`, `quote_items`, `products` (BAT + markings + compatible_techniques) — étape 2c
- [ ] Migrations DROP TABLE pour `marking_techniques`, `technique_pricings`, `technique_constraints`, `product_technique_rules`, `serigraphie_pricings`, `transfer_pricings`, `techniques_marquage` — étape 2d
- [ ] Nettoyer ChatWidget (méthode `getFileFormats` référence `MarkingTechnique`)
- [ ] Nettoyer `GroupShopProduct` (référence `MarkingTechnique` dans relation `technique`)
- [ ] Nettoyer commandes Console : `GenerateSeoDescriptions`, `GenerateCategoryDescriptions` (référencent `compatible_techniques` + `MarkingTechnique`)
- [ ] Décider du sort des `group_shops` (boutiques de groupe) — pas pertinent grossiste a priori, à dégager

## Coefficients prix B2B grossiste

- [ ] Adapter les coefficients dans `pricing_rules` (actuellement copiés depuis B2C marquage, donc marges trop élevées)
- [ ] Lancer `php artisan products:apply-pricing` après modif coefficients pour recalculer `base_price` sur tous les produits
- [ ] Définir politique de prix grossiste (par tranche de quantité, par catégorie, etc.)

## Configuration grossiste métier

- [ ] **Franco de port** : 600€ HT (à configurer dans la logique livraison — pas de frais au-dessus de ce seuil)
- [ ] **Minimum de commande** : 100€ HT (à mettre dans la config + bloquer le checkout en dessous)
- [ ] **Inscription simplifiée** : pas de Kbis obligatoire (positionnement différenciant vs TopTex)
- [ ] **Masquage prix** : tarifs cachés pour les visiteurs non connectés → CTA "Créer un compte gratuit pour voir les prix"

## API fournisseurs — points à valider

- [ ] **TopTex** : vérifier avec le fournisseur si l'utilisation API sur 2 sites distincts (marquage-textile.fr + grossiste-textile.fr) est autorisée par le contrat
- [ ] **Roly (GorFactory)** : pareil
- [ ] **Imbretex** : pareil — et noter que ce fournisseur n'a pas de cron sur marquage-textile.fr (à investiguer)
- [ ] **Quotas / rate limits** : si trafic doublé entre les 2 sites, vérifier que les limites API sont respectées

## Crons stocks/prix

- [ ] **Activer les crons sur grossiste-textile.fr avant la mise en prod publique**
  - `import:toptex --stock-only` toutes les heures (décaler de 30 min vs marquage si on veut étaler la charge API)
  - `import:roly --update-stock-only` chaque jour à 02:00
  - `import:imbretex --stock-only` (à créer — pas existant côté marquage-textile, manque)
- [ ] **Imbretex** : pas de cron sur marquage-textile.fr → stocks pas rafraîchis automatiquement. À régler côté marquage aussi (note non urgente).

## SEO / branding (étape 6 future)

- [ ] Logo "Grossiste-Textile.fr" (placeholder texte pour l'instant)
- [ ] Couleurs de la charte
- [ ] Footer : retirer mentions marquage, adapter "Qui sommes-nous"
- [ ] Méta titres / descriptions par défaut (`config/app.php` + layout app.blade.php)
- [ ] Adresses email service (`MAIL_FROM_*`) — actuellement `contact@grossiste-textile.fr` placeholder
- [ ] Numérotation factures avec préfixe "G" (G2026-001, etc.)

## SEO — protection temporaire (avant publication)

- [ ] `<meta name="robots" content="noindex,nofollow">` sur toutes les pages (étape 8 plan initial)
- [ ] `robots.txt` en `Disallow: /`
- [ ] À retirer une fois le contenu réécrit (sinon Google = duplicate content avec marquage-textile.fr)

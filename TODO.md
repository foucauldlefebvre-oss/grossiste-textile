# TODO — grossiste-textile.fr

Notes et points d'attention identifiés pendant la transformation du fork.

## Étape 2b — refactor panier B2B (terminée 2026-04-28)

- [x] Reconstruire le panier en `Cart` / `CartItem`
- [x] Supprimer définitivement `Quote`, `QuoteItem`, `FloatingQuote`, `Checkout`
- [x] Reconstruire le sélecteur produit Livewire (`ProductSelector`)
- [x] Nettoyer `ChatWidget` (méthode `getFileFormats` neutralisée)
- [x] Nettoyer `GroupShopProduct` (relation `technique` retirée)

## Étape 2c — DROP COLUMN (terminée 2026-04-28)

- [x] DROP COLUMN `has_marking`, `status_bat`, `bat_status`, `bat_client_comment`, `bat_client_decided_at`, `bat_token`, `bat_done_at` sur `orders`
- [x] DROP COLUMN `marking_technique_id` (+FK), `marking_price_ht`, `visual_file`, `marking_zone`, `visual_colors`, `bat_pdf`, `bat_status` sur `order_items`
- [x] DROP COLUMN `compatible_techniques` sur `products`
- [x] DROP COLUMN `default_techniques` sur `categories` (orphelin trouvé pendant l'inventaire)
- [x] Cleanup code orphelin : Order/OrderItem/Product, ChatWidget::lookupOrder
- [ ] Pour `group_shop_products` (marking_*) : sera dégagé avec la table en 2c-bis/2d
- [ ] `account/order-show.blade.php` : `@if(false)` peuvent rester ou être retirés à la phase contenu

## Étape 2d — DROP TABLE (restant)

- [ ] DROP TABLE `quotes`, `quote_items`
- [ ] DROP TABLE `marking_techniques`, `technique_pricings`, `technique_constraints`, `product_technique_rules`
- [ ] DROP TABLE `serigraphie_pricings`, `transfer_pricings`, `techniques_marquage`

## Group Shop (à décider)

- [ ] Décider du sort des `group_shops` (boutiques de groupe — pas pertinent grossiste a priori, à dégager)
- [ ] Si dégage : DROP TABLE `group_shops`, `group_shop_products`, `group_shop_orders` + suppression `GroupShopController`, `GroupShopOrderForm`, modèles, vues

## Console commands à nettoyer (faible priorité)

- [ ] `GenerateSeoDescriptions.php` — référence `MarkingTechnique` + `compatible_techniques` (planera si lancée)
- [ ] `GenerateCategoryDescriptions.php` — pareil

## Tests automatisés (à prévoir)

- [ ] Feature tests : add to cart, view cart, checkout flow
- [ ] À mettre en place quand le projet sera stabilisé après les étapes contenu/CSS

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

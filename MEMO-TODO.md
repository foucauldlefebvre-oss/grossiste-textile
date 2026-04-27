# MEMO - Travail restant (03/04/2026)

## FILTRES & CONTRAINTES TECHNIQUES - TERMINÉ

Toutes les sous-catégories sont configurées (filtres + contraintes techniques de marquage).

### Catégories désactivées/redirigées
- [x] 81 Hauts de travail → produits redistribués (tuniques, polos pro, blouses, dossards)
- [x] 114 Paniers de rangement → redirect sacs polyester
- [x] 26 Sweats sans manches (0 produits)

### Catégories fusionnées (secondaire)
- [x] 95 Polos de sport ↔ 19 Polos sport (même produits visibles dans les deux)
- [x] 35 Vestes Teddy ↔ 225 Teddys (idem)

---

## IMAGES À CORRIGER
- [ ] Photos Kimood (casquettes, bobs, bonnets) : images Toptex mal cadrées (mannequin en pied au lieu du produit). ~8 truckers sans image couleur alternative
- [ ] Vérifier les autres marques Toptex pour le même problème

## PRODUITS
- [x] Fichier recategorisation-produits.xlsx : Solo (159), Valento (752), Westford Mill (133) traités et appliqués
- [x] Catalogue Valento 2026 importé (139 nouveaux, 745 mis à jour)
- [x] 35 produits Roly importés manuellement depuis Excel
- [x] Prix Roly corrigés (PRICE UNIT depuis Excel catalog)
- [x] 210 anciens produits PrestaShop désactivés (18 avec redirections)
- [x] Produits mal catégorisés corrigés (accessoires pro, sport, etc.)
- [ ] 6 refs Valento nouveautés non trouvées dans catalogue : SAVAATL, SAVADRE, COVANOR, COVACRA, CNVANEA, BOVARAG

## API & STOCKS
- [ ] API Roly (GorFactory) : credentials 401 — mot de passe expiré, à mettre à jour
- [x] Stock Valento : scheduler configuré (3h15 + 5h00), fonctionne en créneau 3h-6h
- [x] Stock Solo : scheduler configuré (horaire)
- [x] Stock Toptex : scheduler configuré (horaire)
- [x] Prix Valento : scheduler configuré (3h30)
- [x] Fix mémoire agrégation stock Valento (requête SQL au lieu de PHP)

## TECHNIQUE DE MARQUAGE
- [x] Transfert Sérigraphique ajouté (ID:11) — 1 couleur, 50 pièces min, qualité 5/5
- [ ] Grille tarifaire transfert sérigraphique à configurer
- [ ] Contenu SEO page transfert sérigraphique à rédiger

## BACK-OFFICE
- [x] Filtre catégorie : CheckboxList fonctionnel dans CategoryResource
- [ ] Ajouter champ image par couleur dans le repeater ProductResource (éditable)
- [ ] Performance : lent en local (WAMP), OK en prod avec Redis + OPcache

## DESIGN
- [x] Menu sidebar bordeaux avec hover accordion + scrollbar large
- [x] Cercles bicolores (2859 couleurs avec hex secondaire, gradient diagonal)
- [x] Triangle avertissement chaleur (doudounes) — amber
- [x] Triangle logo complexe (polaires) — orange
- [x] Triangle broderie préconisée (softshells) — bleu
- [x] Taille logo limitée A7 pour casquettes/couvre-chef/portefeuilles
- [x] Filtres cases à cocher groupées à droite
- [x] Filtres dynamiques par catégorie (composition, grammage, duo/trio, etc.)

## SYSTÈME DE FILTRES IMPLÉMENTÉS
Toggles disponibles : duo_trio, elasthanne, poche_toggle, capuche, sans_manches, multi_poche, jean, filet, visiere, patch, pompon, ourlet, bicolor, polaire_bonnet, tote_bag, shopping, sac_a_dos, pochon, jute, bio, made_in_france, matelasse, fin, manches_longues, genouillere, agroindustrie, medical, tunique, coiffe, gilet_serveur, securite, sabot, sandale, haute_visibilite, survetement, maillot_short, chaussette_sport, coiffe_sport, ballon, sweat, fermeture_zip, sac_bio, linge, bavette, court, veste_travail, blouse, manche_elastique

Selects : grammage (ranges personnalisées par cat), composition, material, cut (sexe), type_hv, type_chaussette, hauteur_chaussette, panneaux, volume

## AVERTISSEMENTS DEVIS (triangles)
- _heat_warning : doudounes (DTF, tissage, PVC, offset, sérigraphique) — triangle amber
- _logo_warning : polaires (si logo > 6 couleurs) — triangle orange + badge "Conseillé" sur broderie
- _broderie_warning : softshells (sur toutes techniques sauf broderie) — triangle bleu
- _max_logo_sizes : casquettes/couvre-chef/portefeuilles → A7 uniquement

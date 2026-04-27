# Changelog — grossiste-textile.fr

Toutes les modifications notables apportées au fork depuis sa création.

Format : sections par étape de transformation depuis le code parent (marquage-textile.fr).

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

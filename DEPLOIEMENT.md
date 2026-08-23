# 🚀 Déploiement de KABA sur hébergement mutualisé (cPanel)

Guide pas-à-pas pour mettre KABA en ligne sur un hébergeur type **o2switch, Hostinger, LWS, PlanetHoster…**
(Laravel 11 + Inertia/Vue 3 + MySQL).

---

## 0. Prérequis à vérifier chez l'hébergeur

| Élément | Requis | Comment vérifier |
|---|---|---|
| **PHP** | 8.2 ou + | cPanel → « Sélectionner une version de PHP » |
| Extensions PHP | `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `gd` | même écran, cocher si absent |
| **MySQL / MariaDB** | oui | cPanel → « Bases de données MySQL » |
| **Accès SSH** | recommandé (pas obligatoire) | cPanel → « Terminal » ou « Accès SSH » |
| **Composer** | si SSH dispo | tester `composer --version` en SSH |

> 💡 Sur **o2switch**, SSH + Composer sont disponibles → suivez le **Parcours A**.
> Sur un mutualisé **sans SSH**, suivez le **Parcours B** (tout se prépare en local).

---

## 1. Préparer le build en local (obligatoire dans tous les cas)

Depuis le dossier `kaba-app` sur votre PC :

```bash
# 1. Compiler les assets front-end pour la production
npm install
npm run build

# 2. IMPORTANT : supprimer le fichier « hot » (résidu du dev Vite)
#    Sinon le site cherchera le serveur de dev et n'aura pas de CSS.
#    Windows PowerShell :  Remove-Item public\hot -Force -ErrorAction SilentlyContinue
#    Bash :                rm -f public/hot
```

Le dossier `public/build/` doit maintenant contenir les fichiers compilés (`manifest.json`, `assets/…`).

---

## 2. Créer la base de données (cPanel)

1. cPanel → **Bases de données MySQL**.
2. Créez une base : ex. `moncompte_kaba`.
3. Créez un utilisateur MySQL + **mot de passe fort**.
4. **Ajoutez l'utilisateur à la base** avec **TOUS LES PRIVILÈGES**.
5. Notez : nom de base, utilisateur, mot de passe → iront dans le `.env`.

---

## 3. Envoyer les fichiers

### Parcours A — avec SSH (recommandé)

```bash
# En local : compresser le projet SANS node_modules ni vendor (trop lourds)
#   -> ils seront régénérés sur le serveur.
# Assurez-vous d'inclure public/build (déjà compilé à l'étape 1).
```

1. Uploadez le projet (via Git, `scp`, ou le Gestionnaire de fichiers) dans un dossier **au-dessus** de `public_html`, par ex. `~/kaba-app`.
2. En SSH, dans `~/kaba-app` :

```bash
composer install --optimize-autoloader --no-dev
```

### Parcours B — sans SSH (FTP / Gestionnaire de fichiers uniquement)

Comme Composer n'est pas disponible sur le serveur, on installe les dépendances **en local** puis on envoie tout :

```bash
# En local, dans kaba-app :
composer install --optimize-autoloader --no-dev
```

Puis uploadez **tout le dossier** `kaba-app` (y compris `vendor/` et `public/build/`, mais **sans** `node_modules/`) dans un dossier au-dessus de `public_html`, par ex. `kaba-app/`.

---

## 4. Faire pointer le domaine vers `public/`

Laravel doit être servi depuis son dossier **`public/`**, jamais depuis la racine (sécurité).

### Option 1 — Changer le « Document Root » (idéal, dispo sur o2switch)

cPanel → **Domaines** → votre domaine → **Document Root** →
mettez `/home/moncompte/kaba-app/public`. Terminé.

### Option 2 — Le domaine est bloqué sur `public_html`

1. Placez le projet dans `~/kaba-app` (au-dessus de `public_html`).
2. **Copiez le contenu** de `kaba-app/public/` **dans** `public_html/`.
3. Éditez `public_html/index.php` et corrigez les deux chemins :

```php
// AVANT
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

// APRÈS (on remonte vers le vrai dossier de l'app)
require __DIR__.'/../kaba-app/vendor/autoload.php';
$app = require_once __DIR__.'/../kaba-app/bootstrap/app.php';
```

> À chaque `npm run build`, recopiez `public/build/` dans `public_html/build/`.

---

## 5. Configurer l'environnement

1. Copiez `.env.production.example` en `.env` **dans le dossier de l'app** (`kaba-app/.env`).
2. Remplissez : `APP_URL`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, et le SMTP.
3. Générez la clé et préparez la base :

### Avec SSH

```bash
php artisan key:generate --force
php artisan migrate --force
php artisan db:seed --force        # (facultatif) données de démo
php artisan storage:link           # lien symbolique pour les photos uploadées
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Sans SSH

- **Clé** : générez-la en local avec `php artisan key:generate --show`, puis collez la valeur `base64:…` dans `APP_KEY` du `.env`.
- **Migrations** : si vous ne pouvez pas lancer artisan, importez la base via **phpMyAdmin** en exécutant un dump SQL (voir §8) ; sinon activez temporairement une route de migration protégée (à supprimer après).
- **storage:link** : sans SSH, créez manuellement le dossier `public_html/storage` pointant vers `kaba-app/storage/app/public` (le support de l'hébergeur peut créer le lien symbolique), **ou** passez `FILESYSTEM_DISK` en conséquence.

---

## 6. Droits sur les dossiers

Ces deux dossiers doivent être **inscriptibles** par le serveur web :

```bash
chmod -R 775 storage bootstrap/cache
```

(via cPanel Gestionnaire de fichiers : permissions `775` sur `storage/` et `bootstrap/cache/`, en récursif).

---

## 7. Vérifications post-déploiement ✅

- [ ] La page d'accueil s'affiche **avec le style** (si pas de CSS → fichier `public/hot` oublié, ou `build/` mal copié).
- [ ] `https://…/explorer` liste les livres.
- [ ] Créer un compte + se connecter fonctionne.
- [ ] Publier une annonce avec photo → la photo s'affiche (⇒ `storage:link` OK).
- [ ] La messagerie envoie/reçoit.
- [ ] Une URL inexistante affiche la **page 404 KABA** (pas l'erreur Apache).
- [ ] `APP_DEBUG=false` (aucune trace d'erreur détaillée visible publiquement).
- [ ] Le cadenas HTTPS est actif (installez un certificat **Let's Encrypt** gratuit via cPanel → SSL/TLS).

---

## 8. Mises à jour ultérieures

À chaque nouvelle version :

```bash
# En local
npm run build           # recompiler le front
rm -f public/hot

# Sur le serveur (SSH)
git pull                # ou ré-upload des fichiers modifiés
composer install --optimize-autoloader --no-dev
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan optimize:clear   # si comportement bizarre après déploiement
```

> ⚠️ Après **chaque** changement de `.env`, relancez `php artisan config:cache`
> (sinon les anciennes valeurs restent en cache).

---

## Aide-mémoire des pièges fréquents

| Symptôme | Cause | Solution |
|---|---|---|
| Page blanche / pas de CSS | fichier `public/hot` présent | le supprimer |
| `500` sans détail | `storage/` non inscriptible ou `APP_KEY` vide | `chmod 775 storage`, `key:generate` |
| Photos cassées | `storage:link` non fait | créer le lien symbolique |
| « 419 Page Expired » aux formulaires | `APP_URL` faux ou cookies | corriger `APP_URL`, `SESSION_SECURE_COOKIE=true` en https |
| Anciennes valeurs `.env` | config en cache | `php artisan config:cache` |
| Erreur FK à la migration | ordre des migrations | déjà vérifié ✔ (ne pas renommer les fichiers) |

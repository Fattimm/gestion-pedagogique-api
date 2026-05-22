# Gestion Pédagogique — Backend

API REST de gestion pédagogique pour l'école **Ecole 221**, développée avec Laravel 10.

![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-10.x-FF2D20?logo=laravel&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql&logoColor=white)

---

## Prérequis

- PHP 8.2 (Thread Safe, 64-bit) avec les extensions `sodium` et `grpc`
- MySQL
- Composer

---

## Installation

```bash
git clone <url-du-repo>
cd gestion-pedagogique-backend

composer install

cp .env.example .env
# Renseigner DB_*, FIREBASE_* dans .env

php artisan key:generate
php artisan migrate --seed
php artisan passport:install
php artisan serve
```

> Le fichier `storage/firebase/*.json` (credentials Firebase) ne doit pas être commité.
> L'application fonctionne sans lui si Firebase n'est pas configuré.

---

## Comptes de test

| Login | Rôle | Mot de passe |
|-------|------|--------------|
| `mdiallo` | MANAGER | `pedagogie@` |
| `fndiaye` | CM (Attaché) | `pedagogie@` |
| `ifall` | COACH (Prof) | `pedagogie@` |
| `csarr` | APPRENANT | `pedagogie@` |

---

## Documentation

La référence complète de l'API (endpoints, paramètres, rôles) est disponible dans [`docs/API.md`](docs/API.md).

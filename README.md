# Casa In Due 🏠💕

App web per la gestione condivisa di casa e spese per coppie.

![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?logo=laravel)
![Vue.js](https://img.shields.io/badge/Vue.js-3-4FC08D?logo=vue.js)
![Tailwind CSS](https://img.shields.io/badge/Tailwind-3-38B2AC?logo=tailwind-css)

## 📋 Caratteristiche

- **Gestione Spese**: Spese comuni e personali con categorie
- **Saldo Automatico**: Calcola chi deve quanto
- **Faccende Domestiche**: Organizza e assegna compiti
- **Documenti Condivisi**: Archivia PDF e immagini
- **Lista della Spesa**: Collaborativa con check
- **Dashboard**: Panoramica rapida
- **Mobile-First**: Design responsive grigio/rosa

## 🚀 Stack

- Laravel 11 + PHP 8.2+
- Vue 3 + Inertia.js + Vite
- Tailwind CSS
- SQLite (dev) / MySQL|PostgreSQL (prod)
- Pest (testing)
- Locale: it-IT, Timezone: Europe/Rome

## 📦 Setup Rapido

```bash
# 1. Dipendenze
composer install && npm install

# 2. Database
touch database/database.sqlite
php artisan migrate --seed

# 3. Storage
php artisan storage:link
mkdir -p storage/app/public/documents

# 4. Build & Run
npm run build
php artisan serve
```

Apri http://localhost:8000

**Login demo**: alice@example.com o bob@example.com / password

## 📖 Documentazione Completa

Consulta per implementazione dettagliata:

- **IMPLEMENTATION_GUIDE.md** - Migrazioni, modelli, services
- **IMPLEMENTATION_GUIDE_PART2.md** - Controller, Vue, tests

Include codice completo per 8 tabelle, 6 modelli, SettlementService, 5 controller, policy, factories, seeder, componenti Vue, tests.

## 🔧 Comandi Utili

```bash
# Crea utente
php artisan couple:user-create "Nome" "email@test.com" "pwd" --household="Casa"

# Test
php artisan test

# Fresh migrate (ATTENZIONE!)
php artisan migrate:fresh --seed

# Dev mode
npm run dev
```

## 🎨 Design

Palette: **Slate grays** + **Pink 500/600**
- Card: `bg-white rounded-2xl shadow-sm`
- Bottoni: `bg-pink-600 hover:bg-pink-700 rounded-xl`
- Mobile-first con breakpoints responsive

## 📱 Features

### Dashboard
- Saldo: chi deve quanto
- Contatori: spese, chores, shopping
- Ultimi documenti

### Spese
- Filtri (type, date, category)
- CRUD con auth
- Storico saldi

### Faccende
- Assegnazione + due date
- Toggle open/done

### Documenti
- Upload (PDF, immagini max 10MB)
- Download/Delete

### Shopping
- Add/check/delete item
- "Pulisci spuntati"

## 🔒 Sicurezza

- Laravel Breeze (no registrazione pubblica)
- Policy-based authorization
- CSRF, Rate limiting
- Upload validation

## 🚢 Deploy Produzione

```env
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=mysql
# ...configura DB
```

```bash
npm run build
php artisan optimize
# Setup web server document root: /public
```

## 📄 Licenza

MIT - Progetto educativo/dimostrativo

---

**Supporto**: Vedi guide IMPLEMENTATION_GUIDE.md e IMPLEMENTATION_GUIDE_PART2.md

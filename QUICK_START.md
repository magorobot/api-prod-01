# 🚀 QUICK START - Casa In Due

## Situazione Attuale

✅ Laravel 11 installato e configurato
✅ Breeze pacchetti npm installati (Vue, Inertia, Tailwind)
✅ Database SQLite creato
✅ .env configurato (it-IT, Europe/Rome)
✅ Guide complete create (IMPLEMENTATION_GUIDE.md + PART2)

## PROSSIMI PASSI - Esegui in Ordine

### 1. Installa Dipendenze Node (3-5 min)

```bash
npm install
```

### 2. Copia i File dalle Guide (10-15 min)

Apri `IMPLEMENTATION_GUIDE.md` e `IMPLEMENTATION_GUIDE_PART2.md` e copia il codice per:

**Da IMPLEMENTATION_GUIDE.md:**
- ✅ Migrazioni (8 file in `database/migrations/`)
- ✅ Modelli (6 file in `app/Models/`)
- ✅ SettlementService (`app/Services/SettlementService.php`)

**Da IMPLEMENTATION_GUIDE_PART2.md:**
- ✅ Controller (6 file in `app/Http/Controllers/`)
- ✅ Rotte (`routes/web.php` - sovrascrivi)
- ✅ Policy (4 file in `app/Policies/`)
- ✅ Comando (`app/Console/Commands/CreateCoupleUser.php`)
- ✅ Factory (5 file in `database/factories/`)
- ✅ Seeder (`database/seeders/DemoSeeder.php` + aggiorna `DatabaseSeeder.php`)
- ✅ Tailwind config (`tailwind.config.js`)
- ✅ Componenti Vue (in `resources/js/Components/`)
- ✅ Pagine Vue (in `resources/js/Pages/`)

### 3. Esegui Comandi Setup (2-3 min)

```bash
# Crea tutte le migrazioni vuote
php artisan make:migration create_households_table
php artisan make:migration add_household_id_to_users_table
php artisan make:migration create_expenses_table
php artisan make:migration create_settlements_table
php artisan make:migration create_settlement_expense_table
php artisan make:migration create_chores_table
php artisan make:migration create_documents_table
php artisan make:migration create_shopping_items_table

# Crea modelli
php artisan make:model Household
php artisan make:model Expense
php artisan make:model Settlement
php artisan make:model Chore
php artisan make:model Document
php artisan make:model ShoppingItem

# Crea controller
php artisan make:controller DashboardController
php artisan make:controller ExpenseController
php artisan make:controller SettlementController
php artisan make:controller ChoreController
php artisan make:controller DocumentController
php artisan make:controller ShoppingItemController

# Crea service directory e file
mkdir app\Services
type nul > app\Services\SettlementService.php

# Crea comando
php artisan make:command CreateCoupleUser

# Crea policy
php artisan make:policy ExpensePolicy --model=Expense
php artisan make:policy ChorePolicy --model=Chore
php artisan make:policy DocumentPolicy --model=Document
php artisan make:policy ShoppingItemPolicy --model=ShoppingItem

# Crea factory
php artisan make:factory HouseholdFactory
php artisan make:factory ExpenseFactory
php artisan make:factory ChoreFactory
php artisan make:factory DocumentFactory
php artisan make:factory ShoppingItemFactory

# Crea seeder
php artisan make:seeder DemoSeeder

# Crea directory Vue
mkdir resources\js\Components
mkdir resources\js\Pages\Expenses
mkdir resources\js\Pages\House
```

### 4. Popola i File Creati

Ora **copia il contenuto** dalle guide nei file appena creati.

**IMPORTANTE**: Le guide contengono il codice completo e funzionante. Copia esattamente come scritto.

### 5. Esegui Migrazioni e Seed (1 min)

```bash
# Esegui migrazioni
php artisan migrate

# Esegui seed per dati demo
php artisan db:seed --class=DemoSeeder

# Crea storage link
php artisan storage:link
```

### 6. Build Assets (2-3 min)

```bash
npm run build
```

### 7. Avvia Server

```bash
php artisan serve
```

Apri http://localhost:8000

### 8. Login Demo

- Email: **alice@example.com**
- Password: **password**

Oppure:

- Email: **bob@example.com**
- Password: **password**

## ✅ Verifica Funzionamento

Dopo il login dovresti vedere:

1. **Dashboard** con saldo rapido e contatori
2. **Menu navigazione** verso Spese, Casa (con Chores/Documents/Shopping)
3. **Dati demo** popolati (spese, faccende, documenti, shopping)

## 🐛 Troubleshooting Rapido

### Errore "SQLSTATE[HY000]: General error"
```bash
php artisan migrate:fresh
php artisan db:seed --class=DemoSeeder
```

### Errore "Mix manifest not found"
```bash
npm run build
```

### Assets non si caricano
```bash
php artisan optimize:clear
npm run build
```

### Errore policy o service non trovato
Verifica di aver:
1. Creato il file
2. Copiato il codice dalle guide
3. Rispettato namespace e nome classe

## 📋 Checklist Completa

- [ ] npm install completato
- [ ] Tutte le migrazioni create e popolate
- [ ] Tutti i modelli creati e popolati
- [ ] SettlementService creato
- [ ] Controller creati e popolati
- [ ] routes/web.php aggiornato
- [ ] Policy create e popolate
- [ ] CreateCoupleUser comando creato
- [ ] Factory create e popolate
- [ ] DemoSeeder creato e DatabaseSeeder aggiornato
- [ ] tailwind.config.js aggiornato
- [ ] Componenti Vue creati (Card, StatsTile, PrimaryButton minimo)
- [ ] Pagine Vue create (Dashboard, Expenses/Index minimo)
- [ ] php artisan migrate eseguito
- [ ] php artisan db:seed eseguito
- [ ] php artisan storage:link eseguito
- [ ] npm run build eseguito
- [ ] php artisan serve funzionante
- [ ] Login demo funzionante

## 🎯 Obiettivo

Applicazione completa e funzionante con:
- Autenticazione (Breeze)
- Gestione spese con saldo automatico
- Faccende domestiche
- Documenti condivisi
- Lista spesa collaborativa
- Dashboard con metriche
- UI mobile-first con palette rosa/grigio

## 📚 Risorse

- **IMPLEMENTATION_GUIDE.md** - Codice backend completo
- **IMPLEMENTATION_GUIDE_PART2.md** - Codice frontend e test completi
- **README.md** - Documentazione generale

## ⏱ Tempo Totale Stimato

- Setup file: 10-15 min
- Copia codice: 15-20 min (se fatto con calma)
- Test e verifica: 5 min

**Totale: 30-40 minuti** per applicazione completa funzionante!

---

**Buon lavoro!** Se incontri problemi, consulta le sezioni Troubleshooting nelle guide.

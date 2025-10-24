# Casa In Due - Guida Implementazione Completa

Questa guida contiene tutti i file e comandi necessari per completare l'applicazione "Casa In Due".

## 1. Setup Iniziale Completato

- Laravel 11 installato
- Breeze pacchetti npm configurati
- Database SQLite creato
- .env configurato con locale it-IT e timezone Europe/Rome

## 2. PROSSIMI PASSI - ESEGUI IN ORDINE

### Step 1: Installa dipendenze Node
```bash
npm install && npm run build
```

### Step 2: Esegui i comandi Laravel
```bash
# Crea migrazione households
php artisan make:migration create_households_table

# Modifica users per aggiungere household_id
php artisan make:migration add_household_id_to_users_table

# Crea altre tabelle
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
php artisan make:controller ExpenseController --resource
php artisan make:controller ChoreController --resource
php artisan make:controller DocumentController --resource
php artisan make:controller ShoppingItemController --resource
php artisan make:controller SettlementController

# Crea service
mkdir -p app/Services
touch app/Services/SettlementService.php

# Crea comando
php artisan make:command CreateCoupleUser

# Crea factory e seeder
php artisan make:factory HouseholdFactory
php artisan make:factory ExpenseFactory
php artisan make:factory ChoreFactory
php artisan make:factory DocumentFactory
php artisan make:factory ShoppingItemFactory
php artisan make:seeder DemoSeeder
```

### Step 3: Policy
```bash
php artisan make:policy ExpensePolicy --model=Expense
php artisan make:policy ChorePolicy --model=Chore
php artisan make:policy DocumentPolicy --model=Document
php artisan make:policy ShoppingItemPolicy --model=ShoppingItem
```

## 3. MIGRAZIONI - Codice Completo

### database/migrations/YYYY_MM_DD_create_households_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('households', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('households');
    }
};
```

### database/migrations/YYYY_MM_DD_add_household_id_to_users_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('household_id')->nullable()->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['household_id']);
            $table->dropColumn('household_id');
        });
    }
};
```

### database/migrations/YYYY_MM_DD_create_expenses_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['common', 'personal']);
            $table->decimal('amount', 10, 2)->unsigned();
            $table->string('description');
            $table->string('category')->nullable();
            $table->date('spent_at');
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();

            $table->index(['household_id', 'type', 'settled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
```

### database/migrations/YYYY_MM_DD_create_settlements_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('to_user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 10, 2)->unsigned();
            $table->text('note')->nullable();
            $table->date('settled_on');
            $table->timestamp('created_at');

            $table->index(['household_id', 'settled_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settlements');
    }
};
```

### database/migrations/YYYY_MM_DD_create_settlement_expense_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settlement_expense', function (Blueprint $table) {
            $table->foreignId('settlement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('expense_id')->constrained()->cascadeOnDelete();

            $table->primary(['settlement_id', 'expense_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settlement_expense');
    }
};
```

### database/migrations/YYYY_MM_DD_create_chores_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->date('due_date')->nullable();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['open', 'done'])->default('open');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['household_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chores');
    }
};
```

### database/migrations/YYYY_MM_DD_create_documents_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('file_path');
            $table->string('file_name');
            $table->string('mime');
            $table->unsignedBigInteger('size');
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at');

            $table->index(['household_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
```

### database/migrations/YYYY_MM_DD_create_shopping_items_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shopping_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('quantity')->nullable();
            $table->boolean('is_checked')->default(false);
            $table->foreignId('added_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at');

            $table->index(['household_id', 'is_checked']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shopping_items');
    }
};
```

## 4. MODELLI - Codice Completo

### app/Models/Household.php
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Household extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(Settlement::class);
    }

    public function chores(): HasMany
    {
        return $this->hasMany(Chore::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function shoppingItems(): HasMany
    {
        return $this->hasMany(ShoppingItem::class);
    }
}
```

### app/Models/User.php - Aggiungi al modello esistente
```php
// Aggiungi dopo l'existing code:

use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Aggiungi household_id a $fillable:
protected $fillable = [
    'name',
    'email',
    'password',
    'household_id',
];

// Aggiungi metodo:
public function household(): BelongsTo
{
    return $this->belongsTo(Household::class);
}
```

### app/Models/Expense.php
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'household_id',
        'user_id',
        'type',
        'amount',
        'description',
        'category',
        'spent_at',
        'settled_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'spent_at' => 'date',
        'settled_at' => 'datetime',
    ];

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function settlements(): BelongsToMany
    {
        return $this->belongsToMany(Settlement::class);
    }

    public function scopeUnsettled($query)
    {
        return $query->whereNull('settled_at');
    }

    public function scopeCommon($query)
    {
        return $query->where('type', 'common');
    }
}
```

### app/Models/Settlement.php
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Settlement extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $dates = ['created_at'];

    protected $fillable = [
        'household_id',
        'from_user_id',
        'to_user_id',
        'amount',
        'note',
        'settled_on',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'settled_on' => 'date',
        'created_at' => 'datetime',
    ];

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function expenses(): BelongsToMany
    {
        return $this->belongsToMany(Expense::class);
    }
}
```

### app/Models/Chore.php
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chore extends Model
{
    use HasFactory;

    protected $fillable = [
        'household_id',
        'title',
        'due_date',
        'assigned_user_id',
        'status',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }
}
```

### app/Models/Document.php
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $dates = ['created_at'];

    protected $fillable = [
        'household_id',
        'title',
        'file_path',
        'file_name',
        'mime',
        'size',
        'uploaded_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
```

### app/Models/ShoppingItem.php
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShoppingItem extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $dates = ['created_at'];

    protected $fillable = [
        'household_id',
        'name',
        'quantity',
        'is_checked',
        'added_by',
    ];

    protected $casts = [
        'is_checked' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function adder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function scopeUnchecked($query)
    {
        return $query->where('is_checked', false);
    }
}
```

## 5. SERVICE - SettlementService

### app/Services/SettlementService.php
```php
<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Settlement;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SettlementService
{
    public function calculateBalance(int $householdId): array
    {
        $users = User::where('household_id', $householdId)->get();

        if ($users->count() !== 2) {
            throw new \Exception('Il saldo funziona solo per 2 utenti');
        }

        $unsettledCommon = Expense::where('household_id', $householdId)
            ->common()
            ->unsettled()
            ->get();

        $totalCommon = $unsettledCommon->sum('amount');
        $quota = $totalCommon / 2;

        $balances = [];
        foreach ($users as $user) {
            $paid = $unsettledCommon->where('user_id', $user->id)->sum('amount');
            $balances[$user->id] = [
                'user' => $user,
                'paid' => $paid,
                'quota' => $quota,
                'delta' => $paid - $quota,
            ];
        }

        return [
            'balances' => $balances,
            'total_common' => $totalCommon,
            'unsettled_expenses' => $unsettledCommon,
        ];
    }

    public function createSettlement(int $householdId, ?string $note = null): Settlement
    {
        return DB::transaction(function () use ($householdId, $note) {
            $calculation = $this->calculateBalance($householdId);
            $balances = $calculation['balances'];

            $userA = array_values($balances)[0];
            $userB = array_values($balances)[1];

            if ($userA['delta'] === 0.0) {
                throw new \Exception('Non ci sono spese da saldare');
            }

            $fromUser = $userA['delta'] < 0 ? $userA['user'] : $userB['user'];
            $toUser = $userA['delta'] < 0 ? $userB['user'] : $userA['user'];
            $amount = abs($userA['delta']);

            $settlement = Settlement::create([
                'household_id' => $householdId,
                'from_user_id' => $fromUser->id,
                'to_user_id' => $toUser->id,
                'amount' => $amount,
                'note' => $note,
                'settled_on' => now()->toDateString(),
                'created_at' => now(),
            ]);

            $expenseIds = $calculation['unsettled_expenses']->pluck('id');

            $settlement->expenses()->attach($expenseIds);

            Expense::whereIn('id', $expenseIds)->update(['settled_at' => now()]);

            return $settlement;
        });
    }

    public function getSettlementMetrics(int $householdId): array
    {
        $calc = $this->calculateBalance($householdId);
        $balances = $calc['balances'];

        $userA = array_values($balances)[0];
        $userB = array_values($balances)[1];

        $owes = null;
        if ($userA['delta'] < 0) {
            $owes = [
                'from' => $userA['user']->name,
                'to' => $userB['user']->name,
                'amount' => abs($userA['delta']),
            ];
        } elseif ($userB['delta'] < 0) {
            $owes = [
                'from' => $userB['user']->name,
                'to' => $userA['user']->name,
                'amount' => abs($userB['delta']),
            ];
        }

        return [
            'total_unsettled_common' => $calc['total_common'],
            'quota_per_user' => $calc['total_common'] / 2,
            'owes' => $owes,
            'count_unsettled' => $calc['unsettled_expenses']->count(),
        ];
    }
}
```

## 6. IMPORTANTE: Comandi Finali

Dopo aver copiato tutti i file sopra, esegui:

```bash
# Esegui migrazioni
php artisan migrate

# Crea storage link
php artisan storage:link

# Crea directory documents
mkdir -p storage/app/public/documents

# Compila assets
npm run build

# Avvia server
php artisan serve
```

## CONTINUA NEL FILE: IMPLEMENTATION_GUIDE_PART2.md
Vedi il file part2 per controller, rotte, Vue components, factories, seeder, tests e README finale.

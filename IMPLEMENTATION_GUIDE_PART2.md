# Casa In Due - Part 2: Controller, Rotte, Vue, Factory & Tests

## 7. CONTROLLER

### app/Http/Controllers/DashboardController.php
```php
<?php

namespace App\Http\Controllers;

use App\Models\Chore;
use App\Models\Document;
use App\Models\ShoppingItem;
use App\Services\SettlementService;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(
        private SettlementService $settlementService
    ) {}

    public function index()
    {
        $householdId = auth()->user()->household_id;

        $metrics = $this->settlementService->getSettlementMetrics($householdId);

        $choreCount = Chore::where('household_id', $householdId)->open()->count();
        $shoppingCount = ShoppingItem::where('household_id', $householdId)->unchecked()->count();
        $recentDocuments = Document::where('household_id', $householdId)
            ->with('uploader')
            ->latest('created_at')
            ->limit(5)
            ->get();

        return Inertia::render('Dashboard', [
            'metrics' => $metrics,
            'chore_count' => $choreCount,
            'shopping_count' => $shoppingCount,
            'recent_documents' => $recentDocuments,
        ]);
    }
}
```

### app/Http/Controllers/ExpenseController.php
```php
<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $householdId = auth()->user()->household_id;

        $query = Expense::where('household_id', $householdId)
            ->with('user');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('from_date')) {
            $query->where('spent_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->where('spent_at', '<=', $request->to_date);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $expenses = $query->latest('spent_at')->paginate(20);

        return Inertia::render('Expenses/Index', [
            'expenses' => $expenses,
            'filters' => $request->only(['type', 'from_date', 'to_date', 'category']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:common,personal',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'spent_at' => 'required|date',
        ]);

        $expense = Expense::create([
            ...$validated,
            'household_id' => auth()->user()->household_id,
            'user_id' => auth()->id(),
        ]);

        return back()->with('success', 'Spesa aggiunta con successo');
    }

    public function update(Request $request, Expense $expense)
    {
        $this->authorize('update', $expense);

        $validated = $request->validate([
            'type' => 'required|in:common,personal',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'spent_at' => 'required|date',
        ]);

        $expense->update($validated);

        return back()->with('success', 'Spesa aggiornata');
    }

    public function destroy(Expense $expense)
    {
        $this->authorize('delete', $expense);

        $expense->delete();

        return back()->with('success', 'Spesa eliminata');
    }
}
```

### app/Http/Controllers/SettlementController.php
```php
<?php

namespace App\Http\Controllers;

use App\Models\Settlement;
use App\Services\SettlementService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettlementController extends Controller
{
    public function __construct(
        private SettlementService $settlementService
    ) {}

    public function index()
    {
        $householdId = auth()->user()->household_id;

        $settlements = Settlement::where('household_id', $householdId)
            ->with(['fromUser', 'toUser'])
            ->latest('settled_on')
            ->paginate(20);

        return Inertia::render('Expenses/Settlements', [
            'settlements' => $settlements,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'note' => 'nullable|string|max:500',
        ]);

        try {
            $settlement = $this->settlementService->createSettlement(
                auth()->user()->household_id,
                $validated['note'] ?? null
            );

            return back()->with('success', 'Saldo creato con successo');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
```

### app/Http/Controllers/ChoreController.php
```php
<?php

namespace App\Http\Controllers;

use App\Models\Chore;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ChoreController extends Controller
{
    public function index(Request $request)
    {
        $householdId = auth()->user()->household_id;

        $query = Chore::where('household_id', $householdId)
            ->with('assignedUser');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $chores = $query->latest('created_at')->get();

        $users = User::where('household_id', $householdId)->get();

        return Inertia::render('House/Chores', [
            'chores' => $chores,
            'users' => $users,
            'filters' => $request->only(['status']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'due_date' => 'nullable|date',
            'assigned_user_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        Chore::create([
            ...$validated,
            'household_id' => auth()->user()->household_id,
        ]);

        return back()->with('success', 'Faccenda aggiunta');
    }

    public function update(Request $request, Chore $chore)
    {
        $this->authorize('update', $chore);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'due_date' => 'nullable|date',
            'assigned_user_id' => 'nullable|exists:users,id',
            'status' => 'required|in:open,done',
            'notes' => 'nullable|string',
        ]);

        $chore->update($validated);

        return back()->with('success', 'Faccenda aggiornata');
    }

    public function destroy(Chore $chore)
    {
        $this->authorize('delete', $chore);

        $chore->delete();

        return back()->with('success', 'Faccenda eliminata');
    }

    public function toggle(Chore $chore)
    {
        $this->authorize('update', $chore);

        $chore->update([
            'status' => $chore->status === 'open' ? 'done' : 'open',
        ]);

        return back();
    }
}
```

### app/Http/Controllers/DocumentController.php
```php
<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DocumentController extends Controller
{
    public function index()
    {
        $householdId = auth()->user()->household_id;

        $documents = Document::where('household_id', $householdId)
            ->with('uploader')
            ->latest('created_at')
            ->get();

        return Inertia::render('House/Documents', [
            'documents' => $documents,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'file' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,heic,webp',
        ]);

        $file = $request->file('file');
        $path = $file->store('documents', 'public');

        Document::create([
            'household_id' => auth()->user()->household_id,
            'title' => $validated['title'],
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
            'uploaded_by' => auth()->id(),
            'created_at' => now(),
        ]);

        return back()->with('success', 'Documento caricato');
    }

    public function show(Document $document)
    {
        $this->authorize('view', $document);

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }

    public function destroy(Document $document)
    {
        $this->authorize('delete', $document);

        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return back()->with('success', 'Documento eliminato');
    }
}
```

### app/Http/Controllers/ShoppingItemController.php
```php
<?php

namespace App\Http\Controllers;

use App\Models\ShoppingItem;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShoppingItemController extends Controller
{
    public function index()
    {
        $householdId = auth()->user()->household_id;

        $items = ShoppingItem::where('household_id', $householdId)
            ->with('adder')
            ->latest('created_at')
            ->get();

        return Inertia::render('House/Shopping', [
            'items' => $items,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'nullable|string|max:100',
        ]);

        ShoppingItem::create([
            ...$validated,
            'household_id' => auth()->user()->household_id,
            'added_by' => auth()->id(),
            'created_at' => now(),
        ]);

        return back();
    }

    public function destroy(ShoppingItem $shoppingItem)
    {
        $this->authorize('delete', $shoppingItem);

        $shoppingItem->delete();

        return back();
    }

    public function toggle(ShoppingItem $shoppingItem)
    {
        $this->authorize('update', $shoppingItem);

        $shoppingItem->update([
            'is_checked' => !$shoppingItem->is_checked,
        ]);

        return back();
    }

    public function clearChecked()
    {
        ShoppingItem::where('household_id', auth()->user()->household_id)
            ->where('is_checked', true)
            ->delete();

        return back()->with('success', 'Articoli spuntati rimossi');
    }
}
```

## 8. ROTTE

### routes/web.php
```php
<?php

use App\Http\Controllers\ChoreController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\SettlementController;
use App\Http\Controllers\ShoppingItemController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Expenses
    Route::resource('expenses', ExpenseController::class)->except(['show', 'create', 'edit']);
    Route::post('expenses/settle', [SettlementController::class, 'store'])->name('expenses.settle');
    Route::get('settlements', [SettlementController::class, 'index'])->name('settlements.index');

    // Chores
    Route::resource('chores', ChoreController::class)->except(['show', 'create', 'edit']);
    Route::post('chores/{chore}/toggle', [ChoreController::class, 'toggle'])->name('chores.toggle');

    // Documents
    Route::resource('documents', DocumentController::class)->only(['index', 'store', 'show', 'destroy']);

    // Shopping
    Route::get('shopping', [ShoppingItemController::class, 'index'])->name('shopping.index');
    Route::post('shopping', [ShoppingItemController::class, 'store'])->name('shopping.store');
    Route::post('shopping/{shoppingItem}/toggle', [ShoppingItemController::class, 'toggle'])->name('shopping.toggle');
    Route::delete('shopping/{shoppingItem}', [ShoppingItemController::class, 'destroy'])->name('shopping.destroy');
    Route::delete('shopping-clear-checked', [ShoppingItemController::class, 'clearChecked'])->name('shopping.clear-checked');
});

require __DIR__.'/auth.php';
```

## 9. POLICIES

### app/Policies/ExpensePolicy.php
```php
<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;

class ExpensePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->household_id !== null;
    }

    public function view(User $user, Expense $expense): bool
    {
        return $user->household_id === $expense->household_id;
    }

    public function create(User $user): bool
    {
        return $user->household_id !== null;
    }

    public function update(User $user, Expense $expense): bool
    {
        return $user->household_id === $expense->household_id
            && $user->id === $expense->user_id;
    }

    public function delete(User $user, Expense $expense): bool
    {
        return $user->household_id === $expense->household_id
            && $user->id === $expense->user_id;
    }
}
```

### Crea le altre policy simili: ChorePolicy, DocumentPolicy, ShoppingItemPolicy
```php
// Tutte le policy hanno la stessa logica base:
// - viewAny: user ha household_id
// - view/create/update/delete: controllano household_id match
```

## 10. COMANDO ARTISAN

### app/Console/Commands/CreateCoupleUser.php
```php
<?php

namespace App\Console\Commands;

use App\Models\Household;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateCoupleUser extends Command
{
    protected $signature = 'couple:user-create {name} {email} {password} {--household=Casa}';
    protected $description = 'Crea un utente per la coppia';

    public function handle()
    {
        $householdName = $this->option('household');
        $household = Household::firstOrCreate(['name' => $householdName]);

        $user = User::create([
            'name' => $this->argument('name'),
            'email' => $this->argument('email'),
            'password' => Hash::make($this->argument('password')),
            'household_id' => $household->id,
        ]);

        $this->info("Utente {$user->name} creato nella household {$household->name}");

        return 0;
    }
}
```

## 11. FACTORIES

### database/factories/HouseholdFactory.php
```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class HouseholdFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Casa',
        ];
    }
}
```

### database/factories/ExpenseFactory.php
```php
<?php

namespace Database\Factories;

use App\Models\Household;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'household_id' => Household::factory(),
            'user_id' => User::factory(),
            'type' => fake()->randomElement(['common', 'personal']),
            'amount' => fake()->randomFloat(2, 5, 200),
            'description' => fake()->sentence(3),
            'category' => fake()->randomElement(['Spesa', 'Bollette', 'Trasporti', 'Svago', null]),
            'spent_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }

    public function common()
    {
        return $this->state(['type' => 'common']);
    }
}
```

### database/factories/ChoreFactory.php
```php
<?php

namespace Database\Factories;

use App\Models\Household;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChoreFactory extends Factory
{
    public function definition(): array
    {
        return [
            'household_id' => Household::factory(),
            'title' => fake()->randomElement([
                'Lavare i piatti',
                'Fare la lavatrice',
                'Pulire il bagno',
                'Passare l\'aspirapolvere',
                'Fare la spesa',
            ]),
            'due_date' => fake()->optional()->dateTimeBetween('now', '+7 days'),
            'assigned_user_id' => null,
            'status' => fake()->randomElement(['open', 'done']),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
```

### database/factories/ShoppingItemFactory.php
```php
<?php

namespace Database\Factories;

use App\Models\Household;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShoppingItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'household_id' => Household::factory(),
            'name' => fake()->randomElement([
                'Latte',
                'Pane',
                'Pasta',
                'Pomodori',
                'Mozzarella',
                'Caffè',
                'Acqua',
            ]),
            'quantity' => fake()->optional()->randomElement(['1kg', '2L', '500g', '6 pz']),
            'is_checked' => false,
            'added_by' => User::factory(),
        ];
    }
}
```

### database/factories/DocumentFactory.php
```php
<?php

namespace Database\Factories;

use App\Models\Household;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'household_id' => Household::factory(),
            'title' => fake()->words(3, true),
            'file_path' => 'documents/dummy.pdf',
            'file_name' => 'documento.pdf',
            'mime' => 'application/pdf',
            'size' => fake()->numberBetween(10000, 1000000),
            'uploaded_by' => User::factory(),
        ];
    }
}
```

## 12. SEEDER

### database/seeders/DemoSeeder.php
```php
<?php

namespace Database\Seeders;

use App\Models\Chore;
use App\Models\Document;
use App\Models\Expense;
use App\Models\Household;
use App\Models\ShoppingItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $household = Household::create(['name' => 'Casa']);

        $alice = User::create([
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'password' => Hash::make('password'),
            'household_id' => $household->id,
        ]);

        $bob = User::create([
            'name' => 'Bob',
            'email' => 'bob@example.com',
            'password' => Hash::make('password'),
            'household_id' => $household->id,
        ]);

        // 10 spese miste
        Expense::factory()->count(5)->common()->create([
            'household_id' => $household->id,
            'user_id' => $alice->id,
        ]);

        Expense::factory()->count(3)->common()->create([
            'household_id' => $household->id,
            'user_id' => $bob->id,
        ]);

        Expense::factory()->count(2)->create([
            'household_id' => $household->id,
            'user_id' => fake()->randomElement([$alice->id, $bob->id]),
            'type' => 'personal',
        ]);

        // 3 chores
        Chore::factory()->count(3)->create([
            'household_id' => $household->id,
            'assigned_user_id' => fake()->randomElement([$alice->id, $bob->id, null]),
        ]);

        // 6 shopping items
        ShoppingItem::factory()->count(6)->create([
            'household_id' => $household->id,
            'added_by' => fake()->randomElement([$alice->id, $bob->id]),
        ]);

        // 3 documenti (crea file dummy prima)
        $dummyPath = storage_path('app/public/documents/dummy.pdf');
        if (!file_exists(dirname($dummyPath))) {
            mkdir(dirname($dummyPath), 0755, true);
        }
        file_put_contents($dummyPath, 'Dummy PDF content');

        Document::factory()->count(3)->create([
            'household_id' => $household->id,
            'uploaded_by' => fake()->randomElement([$alice->id, $bob->id]),
        ]);

        $this->command->info('Demo data creati! Usa alice@example.com o bob@example.com con password: password');
    }
}
```

### database/seeders/DatabaseSeeder.php - Aggiungi:
```php
public function run(): void
{
    $this->call([
        DemoSeeder::class,
    ]);
}
```

## 13. CONFIGURAZIONE TAILWIND

### tailwind.config.js
```js
import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter var', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    50: '#fdf2f8',
                    100: '#fce7f3',
                    200: '#fbcfe8',
                    300: '#f9a8d4',
                    400: '#f472b6',
                    500: '#ec4899', // pink-500
                    600: '#db2777', // pink-600
                    700: '#be185d',
                    800: '#9d174d',
                    900: '#831843',
                },
            },
        },
    },

    plugins: [forms],
};
```

## 14. COMPONENTI VUE RIUTILIZZABILI

Crea directory: `resources/js/Components/`

### resources/js/Components/Card.vue
```vue
<template>
    <div class="bg-white rounded-2xl shadow-sm p-6">
        <slot />
    </div>
</template>
```

### resources/js/Components/StatsTile.vue
```vue
<template>
    <div class="bg-white rounded-xl shadow-sm p-4">
        <div class="text-sm text-slate-500 mb-1">{{ label }}</div>
        <div class="text-2xl font-semibold text-slate-900">{{ value }}</div>
    </div>
</template>

<script setup>
defineProps({
    label: String,
    value: [String, Number],
});
</script>
```

### resources/js/Components/PrimaryButton.vue
```vue
<template>
    <button
        type="button"
        class="bg-pink-600 hover:bg-pink-700 text-white rounded-xl px-4 py-2 font-medium transition-colors disabled:opacity-50"
        :disabled="disabled"
    >
        <slot />
    </button>
</template>

<script setup>
defineProps({
    disabled: Boolean,
});
</script>
```

## 15. PAGINE INERTIA

### resources/js/Pages/Dashboard.vue
```vue
<template>
    <AuthenticatedLayout>
        <Head title="Dashboard" />

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h1 class="text-3xl font-bold text-slate-900 mb-6">Dashboard</h1>

                <!-- Saldo Card -->
                <Card class="mb-6">
                    <h2 class="text-xl font-semibold text-slate-900 mb-4">Saldo Rapido</h2>
                    <div v-if="metrics.owes" class="mb-4">
                        <p class="text-lg text-slate-700">
                            {{ metrics.owes.from }} deve pagare
                            <span class="font-bold text-pink-600">€{{ metrics.owes.amount.toFixed(2) }}</span>
                            a {{ metrics.owes.to }}
                        </p>
                    </div>
                    <div v-else class="mb-4">
                        <p class="text-lg text-green-600">Tutto in pari!</p>
                    </div>
                    <PrimaryButton @click="settle" v-if="metrics.owes">
                        Saldare Adesso
                    </PrimaryButton>
                </Card>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <StatsTile label="Spese comuni da saldare" :value="`€${metrics.total_unsettled_common.toFixed(2)}`" />
                    <StatsTile label="Faccende aperte" :value="chore_count" />
                    <StatsTile label="Articoli da comprare" :value="shopping_count" />
                </div>

                <!-- Recent Documents -->
                <Card>
                    <h2 class="text-xl font-semibold text-slate-900 mb-4">Ultimi Documenti</h2>
                    <div v-if="recent_documents.length" class="space-y-2">
                        <div v-for="doc in recent_documents" :key="doc.id" class="flex items-center justify-between py-2">
                            <div>
                                <p class="font-medium text-slate-900">{{ doc.title }}</p>
                                <p class="text-sm text-slate-500">Caricato da {{ doc.uploader.name }}</p>
                            </div>
                            <Link :href="route('documents.show', doc.id)" class="text-pink-600 hover:text-pink-700">
                                Download
                            </Link>
                        </div>
                    </div>
                    <p v-else class="text-slate-500">Nessun documento</p>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import StatsTile from '@/Components/StatsTile.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

defineProps({
    metrics: Object,
    chore_count: Number,
    shopping_count: Number,
    recent_documents: Array,
});

const settle = () => {
    if (confirm('Confermi di voler creare un saldo?')) {
        router.post(route('expenses.settle'));
    }
};
</script>
```

### resources/js/Pages/Expenses/Index.vue
```vue
<template>
    <AuthenticatedLayout>
        <Head title="Spese" />

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold text-slate-900">Spese</h1>
                    <PrimaryButton @click="showAddModal = true">+ Aggiungi</PrimaryButton>
                </div>

                <!-- Filters -->
                <Card class="mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <select v-model="filters.type" class="rounded-lg border-slate-300">
                            <option value="">Tutte</option>
                            <option value="common">Comuni</option>
                            <option value="personal">Personali</option>
                        </select>
                        <input type="date" v-model="filters.from_date" class="rounded-lg border-slate-300" />
                        <input type="date" v-model="filters.to_date" class="rounded-lg border-slate-300" />
                        <button @click="applyFilters" class="bg-slate-200 hover:bg-slate-300 rounded-lg px-4 py-2">
                            Filtra
                        </button>
                    </div>
                </Card>

                <!-- Expenses List -->
                <div class="space-y-4">
                    <Card v-for="expense in expenses.data" :key="expense.id">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-semibold text-slate-900">{{ expense.description }}</p>
                                <p class="text-sm text-slate-500">
                                    {{ expense.user.name }} - {{ formatDate(expense.spent_at) }}
                                    <span class="ml-2 px-2 py-1 text-xs rounded"
                                          :class="expense.type === 'common' ? 'bg-pink-100 text-pink-700' : 'bg-slate-100 text-slate-700'">
                                        {{ expense.type === 'common' ? 'Comune' : 'Personale' }}
                                    </span>
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-xl font-bold text-slate-900">€{{ parseFloat(expense.amount).toFixed(2) }}</p>
                                <button @click="deleteExpense(expense.id)" class="text-sm text-red-600 hover:text-red-700 mt-1">
                                    Elimina
                                </button>
                            </div>
                        </div>
                    </Card>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    <!-- Add pagination links here -->
                </div>
            </div>
        </div>

        <!-- Add Modal - Implementa un modale semplice -->
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    expenses: Object,
    filters: Object,
});

const filters = ref({ ...props.filters });
const showAddModal = ref(false);

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('it-IT');
};

const applyFilters = () => {
    router.get(route('expenses.index'), filters.value);
};

const deleteExpense = (id) => {
    if (confirm('Eliminare questa spesa?')) {
        router.delete(route('expenses.destroy', id));
    }
};
</script>
```

## CONTINUA: Implementa le altre pagine Vue similmente (Chores, Documents, Shopping)

## 16. TESTS CON PEST

### tests/Feature/ExpenseTest.php
```php
<?php

use App\Models\Expense;
use App\Models\Household;
use App\Models\User;

test('user can create expense', function () {
    $household = Household::factory()->create();
    $user = User::factory()->create(['household_id' => $household->id]);

    $this->actingAs($user)
        ->post(route('expenses.store'), [
            'type' => 'common',
            'amount' => 50.00,
            'description' => 'Test expense',
            'spent_at' => now()->toDateString(),
        ])
        ->assertSessionHasNoErrors();

    expect(Expense::count())->toBe(1);
});

test('settlement calculates correctly', function () {
    $household = Household::factory()->create();
    $alice = User::factory()->create(['household_id' => $household->id]);
    $bob = User::factory()->create(['household_id' => $household->id]);

    // Alice pays 100
    Expense::factory()->common()->create([
        'household_id' => $household->id,
        'user_id' => $alice->id,
        'amount' => 100,
    ]);

    // Bob pays 50
    Expense::factory()->common()->create([
        'household_id' => $household->id,
        'user_id' => $bob->id,
        'amount' => 50,
    ]);

    // Total 150, quota 75 each
    // Alice paid 100 - 75 = +25 (should receive)
    // Bob paid 50 - 75 = -25 (should pay)

    $service = app(\App\Services\SettlementService::class);
    $metrics = $service->getSettlementMetrics($household->id);

    expect($metrics['total_unsettled_common'])->toBe(150.0);
    expect($metrics['owes']['amount'])->toBe(25.0);
    expect($metrics['owes']['from'])->toBe($bob->name);
    expect($metrics['owes']['to'])->toBe($alice->name);
});
```

## 17. README FINALE

Aggiorna il README.md con le istruzioni complete:

```markdown
# Casa In Due

App web per gestione casa e spese condivise per coppie.

## Stack Tecnologico

- Laravel 11
- Vue 3 + Inertia.js
- Tailwind CSS
- SQLite (dev)
- Pest (testing)

## Setup

1. Clona repository
2. Installa dipendenze:
```bash
composer install
npm install
```

3. Configura ambiente:
```bash
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
```

4. Esegui migrazioni e seed:
```bash
php artisan migrate --seed
```

5. Crea storage link:
```bash
php artisan storage:link
mkdir -p storage/app/public/documents
```

6. Compila assets:
```bash
npm run build
# oppure per development:
npm run dev
```

7. Avvia server:
```bash
php artisan serve
```

8. Accedi con:
- Email: alice@example.com o bob@example.com
- Password: password

## Funzionalità

- Gestione spese comuni e personali
- Sistema di saldo automatico
- Faccende domestiche
- Documenti condivisi
- Lista della spesa collaborativa
- Dashboard con metriche rapide

## Creare nuovi utenti

```bash
php artisan couple:user-create "Nome" "email@example.com" "password" --household="Casa"
```

## Test

```bash
php artisan test
```

## License

MIT
```

## FINE IMPLEMENTAZIONE

Hai ora tutti i file e le istruzioni per completare l'applicazione "Casa In Due"!

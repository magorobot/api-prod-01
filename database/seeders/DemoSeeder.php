<?php

namespace Database\Seeders;

use App\Models\Chore;
use App\Models\Expense;
use App\Models\Household;
use App\Models\ShoppingItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crea una household demo
        $household = Household::create([
            'name' => 'Casa Demo',
        ]);

        // Crea 2 utenti demo
        $user1 = User::create([
            'name' => 'Mario Rossi',
            'email' => 'mario@example.com',
            'password' => Hash::make('password'),
            'household_id' => $household->id,
            'email_verified_at' => now(),
        ]);

        $user2 = User::create([
            'name' => 'Giulia Bianchi',
            'email' => 'giulia@example.com',
            'password' => Hash::make('password'),
            'household_id' => $household->id,
            'email_verified_at' => now(),
        ]);

        // Crea alcune spese comuni
        Expense::create([
            'household_id' => $household->id,
            'user_id' => $user1->id,
            'type' => 'common',
            'amount' => 150.50,
            'description' => 'Spesa al supermercato',
            'category' => 'Alimentari',
            'spent_at' => now()->subDays(5),
        ]);

        Expense::create([
            'household_id' => $household->id,
            'user_id' => $user2->id,
            'type' => 'common',
            'amount' => 80.00,
            'description' => 'Bolletta luce',
            'category' => 'Utenze',
            'spent_at' => now()->subDays(3),
        ]);

        Expense::create([
            'household_id' => $household->id,
            'user_id' => $user1->id,
            'type' => 'common',
            'amount' => 65.75,
            'description' => 'Spesa settimanale',
            'category' => 'Alimentari',
            'spent_at' => now()->subDays(1),
        ]);

        // Crea alcune spese personali
        Expense::create([
            'household_id' => $household->id,
            'user_id' => $user1->id,
            'type' => 'personal',
            'amount' => 45.00,
            'description' => 'Palestra',
            'category' => 'Sport',
            'spent_at' => now()->subDays(7),
        ]);

        Expense::create([
            'household_id' => $household->id,
            'user_id' => $user2->id,
            'type' => 'personal',
            'amount' => 120.00,
            'description' => 'Parrucchiere',
            'category' => 'Cura personale',
            'spent_at' => now()->subDays(4),
        ]);

        // Crea alcune faccende
        Chore::create([
            'household_id' => $household->id,
            'title' => 'Pulire il bagno',
            'due_date' => now()->addDays(2),
            'assigned_user_id' => $user1->id,
            'status' => 'open',
            'notes' => 'Ricordati di comprare i detergenti',
        ]);

        Chore::create([
            'household_id' => $household->id,
            'title' => 'Fare la spesa',
            'due_date' => now()->addDays(1),
            'assigned_user_id' => $user2->id,
            'status' => 'open',
        ]);

        Chore::create([
            'household_id' => $household->id,
            'title' => 'Lavare i pavimenti',
            'due_date' => now()->subDays(1),
            'assigned_user_id' => $user1->id,
            'status' => 'done',
        ]);

        // Crea alcuni articoli nella lista della spesa
        ShoppingItem::create([
            'household_id' => $household->id,
            'name' => 'Latte',
            'quantity' => '2 litri',
            'is_checked' => false,
            'added_by' => $user1->id,
        ]);

        ShoppingItem::create([
            'household_id' => $household->id,
            'name' => 'Pane',
            'quantity' => '1 kg',
            'is_checked' => false,
            'added_by' => $user2->id,
        ]);

        ShoppingItem::create([
            'household_id' => $household->id,
            'name' => 'Pomodori',
            'quantity' => '500g',
            'is_checked' => true,
            'added_by' => $user1->id,
        ]);

        ShoppingItem::create([
            'household_id' => $household->id,
            'name' => 'Pasta',
            'quantity' => '3 pacchi',
            'is_checked' => false,
            'added_by' => $user2->id,
        ]);
    }
}

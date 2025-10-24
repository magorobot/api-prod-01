<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Household;
use App\Models\Settlement;
use Illuminate\Support\Facades\DB;

class SettlementService
{
    /**
     * Calcola il saldo tra i due utenti della casa.
     * Ritorna un array con 'from_user_id', 'to_user_id', 'amount'.
     */
    public function calculateBalance(Household $household): ?array
    {
        // Recupera tutti gli utenti della household
        $users = $household->users()->get();

        if ($users->count() !== 2) {
            return null; // Solo per household con esattamente 2 utenti
        }

        $user1 = $users[0];
        $user2 = $users[1];

        // Recupera le spese comuni non ancora saldate
        $commonExpenses = $household->expenses()
            ->common()
            ->unsettled()
            ->get();

        if ($commonExpenses->isEmpty()) {
            return null; // Nessuna spesa da saldare
        }

        // Calcola il totale delle spese comuni
        $totalAmount = $commonExpenses->sum('amount');

        // Calcola quanto ciascun utente ha pagato
        $user1Paid = $commonExpenses->where('user_id', $user1->id)->sum('amount');
        $user2Paid = $commonExpenses->where('user_id', $user2->id)->sum('amount');

        // Calcola la quota per persona (metà del totale)
        $sharePerPerson = $totalAmount / 2;

        // Calcola il delta per ogni utente
        $user1Delta = $user1Paid - $sharePerPerson;
        $user2Delta = $user2Paid - $sharePerPerson;

        // Se il delta è praticamente zero (meno di 1 centesimo), non serve saldo
        if (abs($user1Delta) < 0.01) {
            return null;
        }

        // Determina chi deve pagare chi
        if ($user1Delta > 0) {
            // User1 ha pagato di più, quindi User2 deve a User1
            return [
                'from_user_id' => $user2->id,
                'to_user_id' => $user1->id,
                'from_user' => $user2,
                'to_user' => $user1,
                'amount' => abs($user1Delta),
                'expense_ids' => $commonExpenses->pluck('id')->toArray(),
            ];
        } else {
            // User2 ha pagato di più, quindi User1 deve a User2
            return [
                'from_user_id' => $user1->id,
                'to_user_id' => $user2->id,
                'from_user' => $user1,
                'to_user' => $user2,
                'amount' => abs($user2Delta),
                'expense_ids' => $commonExpenses->pluck('id')->toArray(),
            ];
        }
    }

    /**
     * Crea un settlement e marca le spese come saldate.
     */
    public function createSettlement(Household $household, array $data): Settlement
    {
        return DB::transaction(function () use ($household, $data) {
            // Crea il settlement
            $settlement = Settlement::create([
                'household_id' => $household->id,
                'from_user_id' => $data['from_user_id'],
                'to_user_id' => $data['to_user_id'],
                'amount' => $data['amount'],
                'note' => $data['note'] ?? null,
                'settled_on' => $data['settled_on'] ?? now(),
            ]);

            // Collega le spese al settlement
            if (!empty($data['expense_ids'])) {
                $settlement->expenses()->attach($data['expense_ids']);

                // Marca le spese come saldate
                Expense::whereIn('id', $data['expense_ids'])
                    ->update(['settled_at' => now()]);
            }

            return $settlement;
        });
    }

    /**
     * Ottiene le metriche per il dashboard.
     */
    public function getSettlementMetrics(Household $household): array
    {
        $balance = $this->calculateBalance($household);

        $totalUnsettled = $household->expenses()
            ->common()
            ->unsettled()
            ->sum('amount');

        $recentSettlements = $household->settlements()
            ->with(['fromUser', 'toUser'])
            ->latest('settled_on')
            ->take(5)
            ->get();

        return [
            'balance' => $balance,
            'total_unsettled' => $totalUnsettled,
            'recent_settlements' => $recentSettlements,
        ];
    }
}

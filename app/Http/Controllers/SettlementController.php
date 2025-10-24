<?php

namespace App\Http\Controllers;

use App\Services\SettlementService;
use Illuminate\Http\Request;

class SettlementController extends Controller
{
    public function store(Request $request, SettlementService $settlementService)
    {
        $household = auth()->user()->household;

        $balance = $settlementService->calculateBalance($household);

        if (!$balance) {
            return redirect()->back()->with('error', 'Non ci sono spese da saldare al momento.');
        }

        $validated = $request->validate([
            'note' => 'nullable|string|max:255',
            'settled_on' => 'nullable|date',
        ]);

        // Usa la data odierna con timestamp attuale per garantire che appaia dopo le spese
        $settlementDate = $validated['settled_on'] ?? now();

        $settlementService->createSettlement($household, [
            'from_user_id' => $balance['from_user_id'],
            'to_user_id' => $balance['to_user_id'],
            'amount' => $balance['amount'],
            'expense_ids' => $balance['expense_ids'],
            'note' => $validated['note'] ?? null,
            'settled_on' => $settlementDate,
        ]);

        return redirect()->route('expenses.index')->with('success', 'Saldo creato con successo.');
    }
}

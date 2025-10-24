<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Services\SettlementService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;

class ExpenseController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request, SettlementService $settlementService)
    {
        $household = auth()->user()->household;

        // Recupera le spese (ordinate per data decrescente)
        $expenses = Expense::where('household_id', $household->id)
            ->with('user')
            ->orderBy('spent_at', 'desc')
            ->get();

        // Recupera i settlements (ordinati per data decrescente)
        $settlements = $household->settlements()
            ->with(['fromUser', 'toUser'])
            ->orderBy('settled_on', 'desc')
            ->get();

        // Calcola il saldo
        $balance = $settlementService->calculateBalance($household);

        // Calcola totale spese comuni non saldate
        $totalUnsettled = Expense::where('household_id', $household->id)
            ->common()
            ->unsettled()
            ->sum('amount');

        return Inertia::render('Expenses/Index', [
            'expenses' => $expenses,
            'settlements' => $settlements,
            'balance' => $balance,
            'totalUnsettled' => $totalUnsettled,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:common,personal',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'spent_at' => 'required|date',
        ]);

        // Usa sempre l'ora corrente per la data inserita
        $spentAt = \Carbon\Carbon::parse($validated['spent_at'])->setTime(
            now()->hour,
            now()->minute,
            now()->second
        );

        Expense::create([
            'household_id' => auth()->user()->household_id,
            'user_id' => auth()->id(),
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'spent_at' => $spentAt,
        ]);

        return redirect()->route('expenses.index')->with('success', 'Spesa aggiunta con successo.');
    }

    public function update(Request $request, Expense $expense)
    {
        $this->authorize('update', $expense);

        $validated = $request->validate([
            'type' => 'required|in:common,personal',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'spent_at' => 'required|date',
        ]);

        $expense->update($validated);

        return redirect()->route('expenses.index')->with('success', 'Spesa aggiornata con successo.');
    }

    public function destroy(Expense $expense)
    {
        $this->authorize('delete', $expense);

        $expense->delete();

        return redirect()->route('expenses.index')->with('success', 'Spesa eliminata con successo.');
    }
}

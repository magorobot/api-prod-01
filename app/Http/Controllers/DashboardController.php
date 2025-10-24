<?php

namespace App\Http\Controllers;

use App\Models\Chore;
use App\Models\Expense;
use App\Models\ShoppingItem;
use App\Services\SettlementService;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke(SettlementService $settlementService)
    {
        $household = auth()->user()->household;

        if (!$household) {
            return Inertia::render('Dashboard', [
                'message' => 'Non sei ancora stato assegnato a una casa.',
            ]);
        }

        // Metriche spese e saldi
        $settlementMetrics = $settlementService->getSettlementMetrics($household);

        // Spese personali del mese corrente
        $personalExpensesThisMonth = Expense::where('household_id', $household->id)
            ->where('user_id', auth()->id())
            ->where('type', 'personal')
            ->whereMonth('spent_at', now()->month)
            ->whereYear('spent_at', now()->year)
            ->sum('amount');

        // Faccende aperte
        $openChores = Chore::where('household_id', $household->id)
            ->open()
            ->with('assignedUser')
            ->latest('due_date')
            ->take(5)
            ->get();

        // Articoli nella lista della spesa non ancora comprati
        $uncheckedShoppingItems = ShoppingItem::where('household_id', $household->id)
            ->unchecked()
            ->count();

        return Inertia::render('Dashboard', [
            'balance' => $settlementMetrics['balance'],
            'totalUnsettled' => $settlementMetrics['total_unsettled'],
            'recentSettlements' => $settlementMetrics['recent_settlements'],
            'personalExpensesThisMonth' => $personalExpensesThisMonth,
            'openChores' => $openChores,
            'uncheckedShoppingItems' => $uncheckedShoppingItems,
        ]);
    }
}

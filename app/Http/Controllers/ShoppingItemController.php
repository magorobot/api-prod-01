<?php

namespace App\Http\Controllers;

use App\Models\ShoppingItem;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;

class ShoppingItemController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        $household = auth()->user()->household;

        $items = ShoppingItem::where('household_id', $household->id)
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
            'quantity' => 'nullable|string|max:50',
        ]);

        ShoppingItem::create([
            'household_id' => auth()->user()->household_id,
            'name' => $validated['name'],
            'quantity' => $validated['quantity'] ?? null,
            'is_checked' => false,
            'added_by' => auth()->id(),
        ]);

        return redirect()->route('shopping.index')->with('success', 'Articolo aggiunto con successo.');
    }

    public function update(Request $request, ShoppingItem $shoppingItem)
    {
        $this->authorize('update', $shoppingItem);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'nullable|string|max:50',
            'is_checked' => 'boolean',
        ]);

        $shoppingItem->update($validated);

        return redirect()->route('shopping.index')->with('success', 'Articolo aggiornato con successo.');
    }

    public function destroy(ShoppingItem $shoppingItem)
    {
        $this->authorize('delete', $shoppingItem);

        $shoppingItem->delete();

        return redirect()->route('shopping.index')->with('success', 'Articolo eliminato con successo.');
    }

    public function toggleCheck(ShoppingItem $shoppingItem)
    {
        $this->authorize('update', $shoppingItem);

        $shoppingItem->update([
            'is_checked' => !$shoppingItem->is_checked,
        ]);

        return redirect()->route('shopping.index')->with('success', 'Stato aggiornato con successo.');
    }
}

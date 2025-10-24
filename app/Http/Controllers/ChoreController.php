<?php

namespace App\Http\Controllers;

use App\Models\Chore;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;

class ChoreController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        $household = auth()->user()->household;

        $chores = Chore::where('household_id', $household->id)
            ->with('assignedUser')
            ->latest('due_date')
            ->get();

        $users = $household->users;

        return Inertia::render('House/Chores', [
            'chores' => $chores,
            'users' => $users,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'due_date' => 'required|date',
            'assigned_user_id' => ['nullable', function ($attribute, $value, $fail) {
                if ($value !== 'both' && $value !== null && !\App\Models\User::find($value)) {
                    $fail('L\'utente selezionato non è valido.');
                }
            }],
            'notes' => 'nullable|string|max:500',
        ]);

        Chore::create([
            'household_id' => auth()->user()->household_id,
            'title' => $validated['title'],
            'due_date' => $validated['due_date'],
            'assigned_user_id' => $validated['assigned_user_id'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => 'open',
        ]);

        return redirect()->route('chores.index')->with('success', 'Faccenda aggiunta con successo.');
    }

    public function update(Request $request, Chore $chore)
    {
        $this->authorize('update', $chore);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'due_date' => 'required|date',
            'assigned_user_id' => ['nullable', function ($attribute, $value, $fail) {
                if ($value !== 'both' && $value !== null && !\App\Models\User::find($value)) {
                    $fail('L\'utente selezionato non è valido.');
                }
            }],
            'status' => 'required|in:open,done',
            'notes' => 'nullable|string|max:500',
        ]);

        $chore->update($validated);

        return redirect()->route('chores.index')->with('success', 'Faccenda aggiornata con successo.');
    }

    public function destroy(Chore $chore)
    {
        $this->authorize('delete', $chore);

        $chore->delete();

        return redirect()->route('chores.index')->with('success', 'Faccenda eliminata con successo.');
    }

    public function toggleStatus(Chore $chore)
    {
        $this->authorize('update', $chore);

        $chore->update([
            'status' => $chore->status === 'open' ? 'done' : 'open',
        ]);

        return redirect()->route('chores.index')->with('success', 'Stato aggiornato con successo.');
    }
}

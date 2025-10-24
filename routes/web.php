<?php

use App\Http\Controllers\ChoreController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettlementController;
use App\Http\Controllers\ShoppingItemController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Expenses
    Route::resource('expenses', ExpenseController::class)->only(['index', 'store', 'update', 'destroy']);

    // Settlements
    Route::post('/settlements', [SettlementController::class, 'store'])->name('settlements.store');
    Route::post('/settlements/settle', [SettlementController::class, 'store'])->name('settlements.settle');

    // Chores
    Route::resource('chores', ChoreController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::patch('/chores/{chore}/toggle', [ChoreController::class, 'toggleStatus'])->name('chores.toggle');

    // Documents
    Route::resource('documents', DocumentController::class)->only(['index', 'store', 'destroy']);
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');

    // Shopping List
    Route::resource('shopping', ShoppingItemController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::patch('/shopping/{shoppingItem}/toggle', [ShoppingItemController::class, 'toggleCheck'])->name('shopping.toggle');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

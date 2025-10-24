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

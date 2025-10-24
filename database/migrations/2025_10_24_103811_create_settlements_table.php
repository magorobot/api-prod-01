<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('to_user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 10, 2)->unsigned();
            $table->text('note')->nullable();
            $table->timestamp('settled_on');
            $table->timestamp('created_at');

            $table->index(['household_id', 'settled_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settlements');
    }
};

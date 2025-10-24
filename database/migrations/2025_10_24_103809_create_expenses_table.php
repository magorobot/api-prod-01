<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['common', 'personal']);
            $table->decimal('amount', 10, 2)->unsigned();
            $table->string('description');
            $table->string('category')->nullable();
            $table->timestamp('spent_at');
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();

            $table->index(['household_id', 'type', 'settled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};

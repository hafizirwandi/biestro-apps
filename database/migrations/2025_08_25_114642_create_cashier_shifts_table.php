<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cashier_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('counter_id')->nullable()->constrained()->onDelete('cascade');
            // kalau ada tabel pos/mesin kasir

            $table->dateTime('opened_at');
            $table->decimal('opening_balance', 15, 2);

            $table->dateTime('closed_at')->nullable();
            $table->decimal('closing_balance', 15, 2)->nullable();
            $table->decimal('system_balance', 15, 2)->nullable();
            $table->decimal('difference', 15, 2)->nullable();

            $table->text('notes')->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashier_shifts');
    }
};

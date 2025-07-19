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
        Schema::create('daily_ticket_sequences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wahana_id');
            $table->date('date');
            $table->integer('last_number')->default(0);
            $table->timestamps();

            $table->unique(['wahana_id', 'date']); // kombinasi unik

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_ticket_sequences');
    }
};

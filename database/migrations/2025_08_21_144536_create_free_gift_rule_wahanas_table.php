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
        Schema::create('free_gift_rule_wahanas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('free_gift_rule_id')->constrained()->onDelete('cascade');
            $table->foreignId('wahana_id')->nullable()->constrained()->onDelete('set null');
            $table->unsignedInteger('qty')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('free_gift_rule_wahanas');
    }
};

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
        Schema::table('issued_tickets', function (Blueprint $table) {
            $table->foreignId('transaction_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('free_gift_rule_id')->nullable()->constrained()->onDelete('set null');
            $table->unsignedInteger('count_print')->default(0);
            $table->timestamp('last_printed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('issued_tickets', function (Blueprint $table) {
            $table->dropForeign(['free_gift_rule_id']);
            $table->dropColumn('free_gift_rule_id');

            $table->dropForeign(['transaction_id']);
            $table->dropColumn('transaction_id');

            $table->dropColumn('count_print');
            $table->dropColumn('last_printed_at');
        });
    }
};

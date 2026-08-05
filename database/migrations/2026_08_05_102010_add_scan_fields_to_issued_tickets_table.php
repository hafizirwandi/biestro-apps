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
            $table->foreignId('used_by')->nullable()->after('used_at')->constrained('users')->nullOnDelete();
            $table->foreignId('unflagged_by')->nullable()->after('used_by')->constrained('users')->nullOnDelete();
            $table->timestamp('unflagged_at')->nullable()->after('unflagged_by');
            $table->string('unflag_reason')->nullable()->after('unflagged_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('issued_tickets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('used_by');
            $table->dropConstrainedForeignId('unflagged_by');
            $table->dropColumn(['unflagged_at', 'unflag_reason']);
        });
    }
};

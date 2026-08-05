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
        Schema::create('playground_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wahana_id')->nullable()->constrained('wahanas')->nullOnDelete();
            $table->foreignId('issued_ticket_id')->nullable()->constrained('issued_tickets')->nullOnDelete();
            $table->string('child_name');
            $table->enum('gender', ['male', 'female']);
            $table->string('clothing_color');
            $table->unsignedInteger('duration_minutes');
            $table->dateTime('started_at');
            $table->dateTime('end_at');
            $table->enum('status', ['ongoing', 'time_up', 'picked_up'])->default('ongoing');
            $table->boolean('is_calling')->default(false);
            $table->unsignedInteger('call_count')->default(0);
            $table->timestamp('last_called_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('playground_sessions');
    }
};

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
        Schema::create('bets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('round_id')->constrained('game_rounds')->cascadeOnDelete();
            $table->string('game_key', 32);
            $table->string('bet_type', 24);
            $table->string('selection', 40);
            $table->unsignedInteger('amount');
            $table->decimal('odds', 8, 2);
            $table->unsignedInteger('payout')->default(0);
            $table->string('status', 16);
            $table->timestamps();

            $table->index(['round_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bets');
    }
};

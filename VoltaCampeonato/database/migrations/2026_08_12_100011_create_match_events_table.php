<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_match_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('player_id')->nullable();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // goal, yellow_card, red_card, substitution
            $table->integer('minute')->default(0);
            $table->string('description')->nullable();
            $table->json('extra_data')->nullable();
            $table->timestamps();

            $table->foreign('player_id')->references('id')->on('players')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_events');
    }
};

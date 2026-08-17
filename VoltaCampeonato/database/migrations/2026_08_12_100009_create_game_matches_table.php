<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('championship_id')->constrained()->cascadeOnDelete();
            $table->foreignId('phase_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('group_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('round_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('home_team_id')->nullable();
            $table->unsignedBigInteger('away_team_id')->nullable();
            $table->date('match_date')->nullable();
            $table->time('match_time')->nullable();
            $table->string('venue')->nullable();
            $table->string('referee')->nullable();
            $table->string('status')->default('scheduled'); // scheduled, in_progress, finished, suspended, cancelled
            $table->integer('home_score')->default(0);
            $table->integer('away_score')->default(0);
            $table->integer('bracket_position')->nullable();
            $table->unsignedBigInteger('next_match_id')->nullable();
            $table->timestamps();

            $table->foreign('home_team_id')->references('id')->on('teams')->nullOnDelete();
            $table->foreign('away_team_id')->references('id')->on('teams')->nullOnDelete();
            $table->foreign('next_match_id')->references('id')->on('game_matches')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_matches');
    }
};

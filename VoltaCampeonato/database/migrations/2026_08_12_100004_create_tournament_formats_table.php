<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tournament_formats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('championship_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('league'); // league, groups, knockout, groups_knockout, custom
            $table->json('configuration')->nullable();
            $table->string('status')->default('draft'); // draft, configured, generated, in_progress, finished
            $table->boolean('is_round_trip')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tournament_formats');
    }
};

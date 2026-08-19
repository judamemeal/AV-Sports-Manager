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
        // 1. Data Migration: Copy existing relationships
        $teams = DB::table('teams')->whereNotNull('championship_id')->get();
        
        foreach ($teams as $team) {
            DB::table('championship_team')->insert([
                'team_id' => $team->id,
                'championship_id' => $team->championship_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Schema Change: Drop the old foreign key and column
        Schema::table('teams', function (Blueprint $table) {
            $table->dropForeign(['championship_id']);
            $table->dropColumn('championship_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->foreignId('championship_id')->nullable()->constrained()->cascadeOnDelete();
        });

        $relations = DB::table('championship_team')->get();
        foreach ($relations as $rel) {
            DB::table('teams')
                ->where('id', $rel->team_id)
                ->update(['championship_id' => $rel->championship_id]);
        }
    }
};

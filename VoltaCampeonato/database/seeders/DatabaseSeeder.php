<?php

namespace Database\Seeders;

use App\Models\Championship;
use App\Models\Player;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@volta.edu',
            'password' => 'Vt9#qL2!xP7@rK4$zM', // Admin password has been updated.
            'role' => 'admin',
        ]);

        // Create regular user
        User::create([
            'name' => 'Espectador',
            'email' => 'user@volta.edu',
            'password' => 'password123', // Demo user password
            'role' => 'user',
        ]);

        // Create a sample championship
        $championship = Championship::create([
            'name' => 'Campeonato Intercursos de Fútbol 2026',
            'year' => 2026,
            'sport' => 'Fútbol',
            'category' => 'Bachillerato',
            'course_level' => 'Bachillerato',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-15',
            'description' => 'Campeonato anual de fútbol intercursos de la Unidad Educativa Volta.',
            'regulations' => 'Partidos de 2 tiempos de 25 minutos. Sistema de puntos: Victoria 3pts, Empate 1pt, Derrota 0pts.',
            'status' => 'active',
        ]);

        // Create teams with players
        $teamData = [
            ['name' => 'Los Titanes', 'course' => '3ro Bachillerato', 'parallel' => 'A', 'color' => '#e74c3c'],
            ['name' => 'Los Halcones', 'course' => '3ro Bachillerato', 'parallel' => 'B', 'color' => '#3498db'],
            ['name' => 'Los Leones', 'course' => '2do Bachillerato', 'parallel' => 'A', 'color' => '#f39c12'],
            ['name' => 'Los Dragones', 'course' => '2do Bachillerato', 'parallel' => 'B', 'color' => '#2ecc71'],
            ['name' => 'Los Guerreros', 'course' => '1ro Bachillerato', 'parallel' => 'A', 'color' => '#9b59b6'],
            ['name' => 'Los Pumas', 'course' => '1ro Bachillerato', 'parallel' => 'B', 'color' => '#1abc9c'],
            ['name' => 'Los Águilas', 'course' => '1ro Bachillerato', 'parallel' => 'C', 'color' => '#e67e22'],
            ['name' => 'Los Lobos', 'course' => '2do Bachillerato', 'parallel' => 'C', 'color' => '#34495e'],
        ];

        foreach ($teamData as $td) {
            $team = Team::create([
                'championship_id' => $championship->id,
                'name' => $td['name'],
                'course' => $td['course'],
                'parallel' => $td['parallel'],
                'category' => 'Bachillerato',
                'color' => $td['color'],
                'captain_name' => fake()->name(),
                'is_active' => true,
            ]);

            // Create 11 players per team
            $positions = [
                'goalkeeper' => 1,
                'defender' => 4,
                'midfielder' => 3,
                'forward' => 3,
            ];

            $jersey = 1;
            foreach ($positions as $position => $count) {
                for ($i = 0; $i < $count; $i++) {
                    Player::create([
                        'team_id' => $team->id,
                        'first_name' => fake()->firstName('male'),
                        'last_name' => fake()->lastName(),
                        'jersey_number' => $jersey++,
                        'position' => $position,
                        'course' => $td['course'],
                        'parallel' => $td['parallel'],
                        'birth_date' => fake()->dateTimeBetween('-18 years', '-14 years'),
                        'is_active' => true,
                    ]);
                }
            }
        }
    }
}

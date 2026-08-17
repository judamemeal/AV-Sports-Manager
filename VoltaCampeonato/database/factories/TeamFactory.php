<?php

namespace Database\Factories;

use App\Models\Championship;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeamFactory extends Factory
{
    protected $model = Team::class;

    public function definition(): array
    {
        $names = [
            'Los Titanes', 'Los Halcones', 'Los Leones', 'Los Dragones',
            'Los Guerreros', 'Los Pumas', 'Los Águilas', 'Los Lobos',
            'Los Tigres', 'Los Cóndores', 'Los Fénix', 'Los Toros',
            'Los Rayos', 'Los Relámpagos', 'Los Truenos', 'Los Vikingos',
        ];

        $courses = ['1ro Bachillerato', '2do Bachillerato', '3ro Bachillerato', '8vo Básica', '9no Básica', '10mo Básica'];
        $parallels = ['A', 'B', 'C', 'D'];
        $colors = ['#e74c3c', '#3498db', '#2ecc71', '#f39c12', '#9b59b6', '#1abc9c', '#e67e22', '#34495e'];

        return [
            'championship_id' => Championship::factory(),
            'name' => $this->faker->randomElement($names),
            'course' => $this->faker->randomElement($courses),
            'parallel' => $this->faker->randomElement($parallels),
            'category' => $this->faker->randomElement(['Básica', 'Bachillerato']),
            'color' => $this->faker->randomElement($colors),
            'captain_name' => $this->faker->name(),
            'is_active' => true,
        ];
    }
}

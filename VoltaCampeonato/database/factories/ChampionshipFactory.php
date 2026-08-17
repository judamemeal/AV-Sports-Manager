<?php

namespace Database\Factories;

use App\Models\Championship;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChampionshipFactory extends Factory
{
    protected $model = Championship::class;

    public function definition(): array
    {
        $sports = ['Fútbol', 'Básquetbol', 'Voleibol'];
        $categories = ['Básica', 'Bachillerato'];
        $year = 2026;

        return [
            'name' => 'Campeonato Intercursos de ' . $this->faker->randomElement($sports) . ' ' . $year,
            'year' => $year,
            'sport' => $this->faker->randomElement($sports),
            'category' => $this->faker->randomElement($categories),
            'course_level' => $this->faker->randomElement(['Secundaria', 'Preparatoria']),
            'start_date' => $this->faker->dateTimeBetween('2026-09-01', '2026-09-15'),
            'end_date' => $this->faker->dateTimeBetween('2026-11-01', '2026-12-15'),
            'description' => 'Campeonato deportivo de la Unidad Educativa.',
            'regulations' => 'Reglamento oficial del campeonato.',
            'status' => 'upcoming',
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    public function finished(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'finished',
        ]);
    }
}

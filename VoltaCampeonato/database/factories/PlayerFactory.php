<?php

namespace Database\Factories;

use App\Models\Player;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlayerFactory extends Factory
{
    protected $model = Player::class;

    public function definition(): array
    {
        $positions = ['goalkeeper', 'defender', 'midfielder', 'forward'];

        return [
            'team_id' => Team::factory(),
            'first_name' => $this->faker->firstName('male'),
            'last_name' => $this->faker->lastName(),
            'jersey_number' => $this->faker->numberBetween(1, 99),
            'position' => $this->faker->randomElement($positions),
            'course' => $this->faker->randomElement(['1ro Bachillerato', '2do Bachillerato', '3ro Bachillerato']),
            'parallel' => $this->faker->randomElement(['A', 'B', 'C']),
            'birth_date' => $this->faker->dateTimeBetween('-18 years', '-14 years'),
            'is_active' => true,
        ];
    }
}

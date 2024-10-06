<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'subject' => $this->faker->sentence,
            'details' => $this->faker->paragraph(2),
            'open' => $this->faker->dateTimeBetween('-1 week', 'now')->format('Y-m-d H:i:s'),
            'due' => $this->faker->dateTimeBetween('now', '+1 week')->format('Y-m-d H:i:s'),
            'response' => $this->faker->dateTime('now')->format('Y-m-d H:i:s'),
        ];
    }
}

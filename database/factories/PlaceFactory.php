<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\County;
use App\Models\Place;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Place>
 */
class PlaceFactory extends Factory
{
    public function definition()
    {
        return [
            'postal_code' => $this->faker->postcode(),
            'name' => $this->faker->city(),
            'county_id' => County::factory(),
        ];
    }
}

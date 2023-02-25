<?php

namespace QRFeedz\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use QRFeedz\Cube\Models\Country;
use QRFeedz\Cube\Models\Place;

class PlaceFactory extends Factory
{
    protected $model = Place::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'address' => fake()->streetAddress(),
            'postal_code' => fake()->postcode(),
            'locality' => fake()->city(),
            'country_id' => Country::all()->random(),
            'description' => fake()->sentence()
        ];
    }
}

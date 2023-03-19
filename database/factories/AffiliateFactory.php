<?php

namespace QRFeedz\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use QRFeedz\Cube\Models\Affiliate;
use QRFeedz\Cube\Models\Country;

class AffiliateFactory extends Factory
{
    protected $model = Affiliate::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'address' => $this->faker->streetAddress,
            'postal_code' => $this->faker->postcode,
            'locality' => $this->faker->city,
            'user_id' => 1, //User::factory(),
            'country_id' => 1, //Country::factory(),
        ];
    }
}

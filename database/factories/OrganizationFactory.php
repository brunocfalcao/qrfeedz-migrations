<?php

namespace QRFeedz\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use QRFeedz\Cube\Models\Country;
use QRFeedz\Cube\Models\Organization;

class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    public function definition(): array
    {
        // for vat()
        fake()->addProvider(new \Faker\Provider\es_ES\Payment(fake()));

        return [
            'name' => fake()->company(),
            'address' => fake()->streetAddress(),
            'postal_code' => fake()->postcode(),
            'locality' => fake()->city(),
            'vat_number' => fake()->vat(),
            'country_id' => Country::all()->random(),
        ];
    }
}

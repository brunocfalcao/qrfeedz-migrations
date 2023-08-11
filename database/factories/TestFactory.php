<?php

use Faker\Generator as Faker;

$factory->define(QRFeedz\Cube\Models\Affiliate::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'commission_percentage' => $faker->numberBetween(0, 100),
        'address' => $faker->address,
        'postal_code' => $faker->postcode,
        'locality' => $faker->city,
        'country_id' => function () {
            return factory(App\Country::class)->create()->id;
        },
    ];
});

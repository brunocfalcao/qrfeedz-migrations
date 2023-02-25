<?php

namespace QRFeedz\Database\Seeders;

use Illuminate\Database\Seeder;
use QRFeedz\Cube\Models\Organization;
use QRFeedz\Cube\Models\Place;

class SchemaTestSeeder extends Seeder
{
    public function run()
    {
        /**
         $faker = Faker\Factory::create();
         // generate data by calling methods

         echo $faker->name();
         // 'Vince Sporer'

         echo $faker->email();
         // 'walter.sophia@hotmail.com'

         echo $faker->text();
         // 'Numquam ut mollitia at consequuntur inventore dolorem.'
         */

        //$faker = new Faker\Generator();
        //$faker->addProvider(new Faker\Provider\en_US\Address($faker));

        /**
         * Organization -> place -> questionnaire -> question -> answer
         */

        /**
         * Start by creating a random number of organizations. Everything
         * starts at the topic, and then we will cascade for places,
         * questionnaires, questions and answers.
         */
        Organization::factory()->count(rand(20, 30))->create();

        foreach (Organization::all() as $organization) {
            Place::factory()->count(rand(1, 3))->make([
                'organization_id' => $organization->id,
            ])->each(function ($place) {
                dd($place);
            });
        }
    }
}

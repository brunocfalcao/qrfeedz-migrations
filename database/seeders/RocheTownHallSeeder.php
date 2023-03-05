<?php

namespace QRFeedz\Database\Seeders;

use Illuminate\Database\Seeder;
use QRFeedz\Cube\Models\Country;
use QRFeedz\Cube\Models\Organization;
use QRFeedz\Cube\Models\Place;
use QRFeedz\Cube\Models\Question;
use QRFeedz\Cube\Models\Widget;

class RocheTownHallSeeder extends Seeder
{
    public function run()
    {
        /**
         * Roche schema seeder. It will create a Town Hall feedback survey.
         * The Town Hall is the Pharma IT Town Hall, that happened 21st March
         * 2022, and it will have only 1 question:
         *
         * "How much did you like this town hall?"
         * [ Emoji slider rating ]
         *
         * In case the emoji is 1 or 2, then it opens a [ Textarea ] widget
         * to ask more details:
         * "Why did you don't like?"
         *
         * In case the emoji is 5, then it opens a [ Textarea ] widget
         * to ask more details:
         * "Why did you like it so much?"
         *
         * Organization will be Roche IT.
         */
        $organization = Organization::create([
            'name' => 'Roche IT',
            'address' => 'Wurmisweg',
            'postal_code' => '4303',
            'locality' => 'Kaiseraugst',
            'country_id' => Country::firstWhere('name', 'Switzerland')->id,
            'vat_number' => '507643121',
        ]);

        $place = Place::create([
            'name' => 'H4IT',
            'description' => 'Home4IT events, related with all Roche IT Organizations',
            'organization_id' => $organization->id,
        ]);
    }
}

<?php

namespace QRFeedz\Database\Seeders;

use Illuminate\Database\Seeder;
use QRFeedz\Cube\Models\Affiliate;

class FactoryTest extends Seeder
{
    public function run()
    {
        /**
         * This seeder is specific to create factory test data into the
         * database, in order to run our feature and unit tests.
         */
        $affiliate = Affiliate::factory()->create();
    }
}

<?php

namespace QRFeedz\Database\Seeders;

use Illuminate\Database\Seeder;
use QRFeedz\Cube\Models\Organization;

class SchemaTestSeeder extends Seeder
{
    public function run()
    {
        Organization::factory()->count(3)->create();
    }
}

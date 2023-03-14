<?php

namespace QRFeedz\Database\Seeders;

use Illuminate\Database\Seeder;
use QRFeedz\Cube\Models\Authorization;
use QRFeedz\Cube\Models\Client;
use QRFeedz\Cube\Models\Country;
use QRFeedz\Cube\Models\Locale;
use QRFeedz\Cube\Models\Question;
use QRFeedz\Cube\Models\Questionnaire;
use QRFeedz\Cube\Models\User;
use QRFeedz\Cube\Models\Widget;

class Test1 extends Seeder
{
    public function run()
    {
        /**
         * 3 Clients: McDonalds, Porto Novo and Roche.
         * 2 Users (std and admin) per client (@mcd.com, @portonovo.ch, @roche.ch).
         * 3 affiliates, 1 with Mcd and Porto Novo, 2nd with Roche,
         * 3rd with none. (affmcd@qrfeedz.ai, affpn@qrfeedz.ai, affrch@qrfeedz.ai)
         */
        $client = Client::create([
            'name' => 'Roche',
            'address' => 'Wurmisweg',
            'postal_code' => '4303',
            'locality' => 'Kaiseraugst',
            'default_locale' => 'en',
            'country_id' => Country::firstWhere('name', 'Switzerland')->id,
            'vat_number' => '507643121',
        ]);

        /**
         * Create a user that has admin access to the client, and create
         * a user that only has read-only access.
         */
        $userAdmin = User::create([
            'client_id' => $client->id,
            'name' => 'Roche Admin',
            'email' => 'admin@roche.com',
            'password' => bcrypt(env('ROCHE_TOWNHALL_ADMIN_PASSWORD')),
        ]);

        // Assign 'admin' authorization to the client $user.
        $authorizationAdmin = Authorization::firstWhere('name', 'admin');
        $client->authorizations()->save($authorizationAdmin, ['user_id' => $userAdmin->id]);

        // This is a very normal user, without any specific authorization.
        $userStandard = User::create([
            'client_id' => $client->id,
            'name' => 'Roche Standard',
            'email' => 'standard@roche.com',
            'password' => bcrypt(env('ROCHE_TOWNHALL_ADMIN_PASSWORD')),
        ]);

        /**
         * Simulating a Town Hall event, so we need a new questionnaire for that
         * event. First we create the only question, then the questionnaire,
         * then the widget to connect to the questionnaire.
         * In case the emoji range widget is very sad or sad we open a
         * textarea to ask for more details.
         *
         * We will have 4 languages:
         * English (default), Italian, French and German.
         *
         * At the end we will ask for a custom data field that is the
         * employee number (optional field).
         */

        // Start by creating the locale caption.
        Locale::create([
            'en' => 'How much did you like this Town Hall?',
            'pt' => 'Quanto gostou deste Town Hall?',
            'de' => 'Wie sehr hat Ihnen dieses Rathaus gefallen?',
            'it' => 'Quanto vi è piaciuto questo Town Hall?',
            'cn' => '你有多喜欢这个市政厅？',
            'client_id' => $client->id,
        ]);

        // Create the questionnaire.
        Questionnaire::create([
            'name' => 'Home4IT March Town Hall',
            'client_id' => $client->id,
            'description' => 'The Town Hall for the H4IT, March 2023',
        ]);
    }
}

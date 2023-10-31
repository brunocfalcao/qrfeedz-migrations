<?php

namespace QRFeedz\Database\Seeders;

use Illuminate\Database\Seeder;
use QRFeedz\Cube\Models\Authorization;
use QRFeedz\Cube\Models\Category;
use QRFeedz\Cube\Models\Client;
use QRFeedz\Cube\Models\ClientAuthorization;
use QRFeedz\Cube\Models\Country;
use QRFeedz\Cube\Models\Locale;
use QRFeedz\Cube\Models\QuestionInstance;
use QRFeedz\Cube\Models\Questionnaire;
use QRFeedz\Cube\Models\QuestionnaireAuthorization;
use QRFeedz\Cube\Models\User;

class CrocRock extends Seeder
{
    public function run()
    {
        // Create CrocRock client.
        $client = Client::create([
            'name' => 'Croc & Rock',
            'address' => '156 Rue de Frescaty, 57155 Marly, France',
            'postal_code' => '57155',
            'city' => 'Marly',
            'latitude' => 49.087548,
            'longitude' => 6.141728899999999,
            'country_id' => Country::firstWhere('name', 'France')->id,
            'locale_id' => Locale::firstWhere('canonical', 'fr')->id,
        ]);

        // This is the user connected to the afilliate.
        $affiliate = User::create([
            'name' => env('CROCROCK_AFFILIATE_NAME'),
            'email' => env('CROCROCK_AFFILIATE_EMAIL'),
            'password' => bcrypt(env('CROCROCK_AFFILIATE_PASSWORD')),
            'address' => 'Le chauffour 4',
            'postal_code' => '2364',
            'locality' => 'St-Brais',
            'is_affiliate' => true,
            'commission_percentage' => 50,
            'country_id' => Country::firstWhere('name', 'Switzerland')->id,
        ]);

        // Create client admin.
        $clientAdmin = User::create([
            'client_id' => $client->id,
            'name' => env('CROCROCK_CLIENT_ADMIN_NAME'),
            'email' => env('CROCROCK_CLIENT_ADMIN_EMAIL'),
            'password' => bcrypt(env('CROCROCK_CLIENT_ADMIN_PASSWORD')),
        ]);

        // Create questionaire admin.
        $questionnaireAdmin = User::create([
            'client_id' => $client->id,
            'name' => env('CROCROCK_QUESTIONNAIRE_ADMIN_NAME'),
            'email' => env('CROCROCK_QUESTIONNAIRE_ADMIN_EMAIL'),
            'password' => bcrypt(env('CROCROCK_QUESTIONNAIRE_ADMIN_PASSWORD')),
        ]);

        /**
         * Create user admin.
         */
        $admin = User::create([
            'name' => env('QRFEEDZ_ADMIN_NAME'),
            'email' => env('QRFEEDZ_ADMIN_EMAIL'),
            'password' => bcrypt(env('QRFEEDZ_ADMIN_PASSWORD')),
            'is_admin' => true,
        ]);

        // Associate the client with this affiliate.
        $client->affiliate()
               ->associate($affiliate)
               ->save();

        // Give client admin permissions.
        ClientAuthorization::create([
            'user_id' => $clientAdmin->id,
            'client_id' => $client->id,
            'authorization_id' => Authorization::firstWhere('canonical', 'client-admin')->id,
        ]);

        $questionnaire = Questionnaire::create([
            'name' => 'CrocRock 2024',
            'title' => 'Restaurant CrocRock',
            'location_id' => 1, // This is because the client creation triggered a location.
            'category_id' => Category::firstWhere('canonical', 'restaurant')->id,
            'starts_at' => now(),
        ]);

        $questionnaire->save();

        // Give questionnaire admin permissions.
        QuestionnaireAuthorization::create([
            'user_id' => $questionnaireAdmin->id,
            'questionnaire_id' => $questionnaire->id,
            'authorization_id' => Authorization::firstWhere('canonical', 'questionnaire-admin')->id,
        ]);

        /**
         * Create 2 questions:
         * - What's your overall satisfaction?
         * - Anything you would like to see improved?
         */
        $question1 = QuestionInstance::create([
            'questionnaire_id' => $questionnaire->id,
            'is_analytical' => true,
            'is_used_for_personal_data' => false,
            'is_required' => true,
        ]);

        $question2 = QuestionInstance::create([
            'questionnaire_id' => $questionnaire->id,
            'is_analytical' => true,
            'is_used_for_personal_data' => false,
            'is_required' => false,
        ]);
    }
}

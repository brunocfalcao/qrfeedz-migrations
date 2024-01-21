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
use QRFeedz\Cube\Models\Widget;
use QRFeedz\Cube\Models\WidgetInstance;

class Seed1 extends Seeder
{
    public function run()
    {
        // Create clients.
        $clientCroc = Client::create([
            'name' => 'Croc & Rock',
            'address' => '156 Rue de Frescaty, 57155 Marly, France',
            'postal_code' => '57155',
            'city' => 'Marly',
            'latitude' => 49.087548,
            'longitude' => 6.141728899999999,
            'country_id' => Country::firstWhere('name', 'France')->id,
            'locale_id' => Locale::firstWhere('canonical', 'fr')->id,
        ]);

        $clientHilton = Client::create([
            'name' => 'Hilton Diagonal Barcelona',
            'address' => 'Rua da Penha de França 138 porta 4',
            'postal_code' => '1170-207',
            'city' => 'Lisbon',
            'latitude' => 38.72523740,
            'longitude' => -9.12950620,
            'country_id' => Country::firstWhere('name', 'Portugal')->id,
            'locale_id' => Locale::firstWhere('canonical', 'pt')->id,
        ]);

        // Create users that will be affiliates.
        $affiliateCroc = User::create([
            'name' => env('CROCROCK_AFFILIATE_NAME'),
            'email' => env('CROCROCK_AFFILIATE_EMAIL'),
            'password' => bcrypt(env('CROCROCK_AFFILIATE_PASSWORD')),
            'address' => 'Paiva Couceiro',
            'postal_code' => '1200',
            'locality' => 'Lisbon',
            'is_affiliate' => true,
            'commission_percentage' => 50,
            'country_id' => Country::firstWhere('name', 'Portugal')->id,
        ]);

        $affiliateHilton = User::create([
            'name' => env('HILTON_AFFILIATE_NAME'),
            'email' => env('HILTON_AFFILIATE_EMAIL'),
            'password' => bcrypt(env('HILTON_AFFILIATE_PASSWORD')),
            'address' => 'Feichruttiweg 141',
            'postal_code' => '5263',
            'locality' => 'Etzgen',
            'is_affiliate' => true,
            'commission_percentage' => 20,
            'country_id' => Country::firstWhere('name', 'Switzerland')->id,
        ]);

        // Create client admins.
        $clientCrocAdmin = User::create([
            'client_id' => $clientCroc->id,
            'name' => env('CROCROCK_CLIENT_ADMIN_NAME'),
            'email' => env('CROCROCK_CLIENT_ADMIN_EMAIL'),
            'password' => bcrypt(env('CROCROCK_CLIENT_ADMIN_PASSWORD')),
        ]);

        $clientHiltonAdmin = User::create([
            'client_id' => $clientHilton->id,
            'name' => env('HILTON_CLIENT_ADMIN_NAME'),
            'email' => env('HILTON_CLIENT_ADMIN_EMAIL'),
            'password' => bcrypt(env('HILTON_CLIENT_ADMIN_PASSWORD')),
        ]);

        // Create questionaire admin.
        $questionnaireCrocAdmin = User::create([
            'client_id' => $clientCroc->id,
            'name' => env('CROCROCK_QUESTIONNAIRE_ADMIN_NAME'),
            'email' => env('CROCROCK_QUESTIONNAIRE_ADMIN_EMAIL'),
            'password' => bcrypt(env('CROCROCK_QUESTIONNAIRE_ADMIN_PASSWORD')),
        ]);

        // Create questionaire admin.
        $questionnaireHiltonAdmin = User::create([
            'client_id' => $clientHilton->id,
            'name' => env('HILTON_QUESTIONNAIRE_ADMIN_NAME'),
            'email' => env('HILTON_QUESTIONNAIRE_ADMIN_EMAIL'),
            'password' => bcrypt(env('HILTON_QUESTIONNAIRE_ADMIN_PASSWORD')),
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

        // Associate the clients with these affiliates.
        $clientCroc->affiliate()
               ->associate($affiliateCroc)
               ->save();

        // Associate the clients with these affiliates.
        $clientHilton->affiliate()
               ->associate($affiliateHilton)
               ->save();

        // Give client admin permissions.
        ClientAuthorization::create([
            'user_id' => $clientCrocAdmin->id,
            'client_id' => $clientCroc->id,
            'authorization_id' => Authorization::firstWhere('canonical', 'client-admin')->id,
        ]);

        ClientAuthorization::create([
            'user_id' => $clientHiltonAdmin->id,
            'client_id' => $clientHilton->id,
            'authorization_id' => Authorization::firstWhere('canonical', 'client-admin')->id,
        ]);

        return;

        $questionnaireCroc = Questionnaire::create([
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

        // Get locales.
        $en = Locale::firstWhere('canonical', 'en');
        $fr = Locale::firstWhere('canonical', 'fr');
        $pt = Locale::firstWhere('canonical', 'pt');

        // Add locales to both questions.
        $question1->captions()->attach($en->id, [
            'caption' => 'Overall, how did we perform?',
        ]);

        $question1->captions()->attach($fr->id, [
            'caption' => 'Tout etait bien?',
        ]);

        $question1->captions()->attach($pt->id, [
            'caption' => 'De modo geral, como o acolhemos?',
        ]);

        $question2->captions()->attach($en->id, [
            'caption' => 'Anything else to add?',
        ]);

        $question2->captions()->attach($fr->id, [
            'caption' => 'Quelque chose a dire?',
        ]);

        $question2->captions()->attach($pt->id, [
            'caption' => 'Algo mais a acrescentar?',
        ]);

        // Add widget instances to the question instance.
        $starsRating = WidgetInstance::create([
            'question_instance_id' => $question1->id,
            'widget_id' => Widget::firstWhere('canonical', 'stars-rating')->id,
        ]);

        $textarea = WidgetInstance::create([
            'question_instance_id' => $question2->id,
            'widget_id' => Widget::firstWhere('canonical', 'textarea')->id,
        ]);
    }
}

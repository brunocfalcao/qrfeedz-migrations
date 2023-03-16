<?php

namespace QRFeedz\Database\Seeders;

use Illuminate\Database\Seeder;
use QRFeedz\Cube\Models\Affiliate;
use QRFeedz\Cube\Models\Authorization;
use QRFeedz\Cube\Models\Client;
use QRFeedz\Cube\Models\Country;
use QRFeedz\Cube\Models\Locale;
use QRFeedz\Cube\Models\User;

class CrocRock extends Seeder
{
    public function run()
    {
        /**
         * The Croc & Rock restaurant example.
         *
         * A restaurant survey that will ask for the visitors for the
         * overall experience. It will ask only one question, and in
         * case it's very bad, or very good, it will ask for more
         * details. At the end will ask for the email (optional).
         *
         * Final page, will show a promotion for the next month, a
         * coupon that the person can use to come back. It will
         * need to ask for the email to send the coupon.
         *
         * Final page is the social sharing of the restaurant.
         *
         * Users: They are 3 partners. In this case two will have "admin"
         * profile, and the 3rd one will have a "non-admin" profile.
         *
         * There will also be one affiliate (Karine) that will be connected
         * to the client.
         */
        $affiliate = Affiliate::create([
            'name' => 'Karine Esnault',
            'address' => 'Le chauffour 4',
            'postal_code' => '2364',
            'locality' => 'St-Brais',
            'country_id' => Country::firstWhere('name', 'Switzerland')->id,
        ]);

        $client = Client::create([
            'name' => 'Croc & Rock',
            'address' => '27 avenue du XX ème corps',
            'postal_code' => '54000',
            'locality' => 'Nancy',
            'country_id' => Country::firstWhere('name', 'France')->id,
            'affiliate_id' => Affiliate::firstWhere('name', 'Karine Esnault')->id,
            'locale_id' => Locale::firstWhere('code', 'fr')->id,
        ]);

        $affiliate = User::create([
            'client_id' => $client->id,
            'is_affiliate' => true,
            'name' => env('CROCROCK_AFFILIATE_NAME'),
            'email' => env('CROCROCK_AFFILIATE_EMAIL'),
            'password' => bcrypt(env('CROCROCK_AFFILIATE_PASSWORD')),
        ]);

        $admin = User::create([
            'client_id' => $client->id,
            'name' => env('CROCROCK_ADMIN_NAME'),
            'email' => env('CROCROCK_ADMIN_EMAIL'),
            'password' => bcrypt(env('CROCROCK_ADMIN_PASSWORD')),
        ]);

        $user = User::create([
            'client_id' => $client->id,
            'name' => env('CROCROCK_USER_NAME'),
            'email' => env('CROCROCK_USER_EMAIL'),
            'password' => bcrypt(env('CROCROCK_USER_PASSWORD')),
        ]);

        /**
         * Give respective permissions to all the created users.
         *
         * The client that will have Karine ($affiliate) as affiliate.
         * The client that will have Peres as admin.
         * The client that will have Francois as questionnaire admin.
         */
        Authorization::firstWhere('code', 'affiliate')
                     ->clients()
                     ->attach(
                         $client->id,
                         ['user_id' => $affiliate->id] // Karine
                     );

        Authorization::firstWhere('code', 'admin')
                     ->clients()
                     ->attach(
                         $client->id,
                         ['user_id' => $admin->id] // Peres
                     );

        /**
         * Now it's time to configure the OpenAI prompt. On this case,
         * the restaurant is a start-up, so highly sensitive to feedback,
         * and it's interest to know mostly the food quality since that's
         * where they are betting to be different. They also want to
         * know if visitors left their emails so they can reach to them
         * with more information.
         */
    }
}

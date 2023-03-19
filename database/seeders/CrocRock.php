<?php

namespace QRFeedz\Database\Seeders;

use Illuminate\Database\Seeder;
use QRFeedz\Cube\Models\Affiliate;
use QRFeedz\Cube\Models\Authorization;
use QRFeedz\Cube\Models\Client;
use QRFeedz\Cube\Models\Country;
use QRFeedz\Cube\Models\Locale;
use QRFeedz\Cube\Models\OpenAIPrompt;
use QRFeedz\Cube\Models\Question;
use QRFeedz\Cube\Models\Questionnaire;
use QRFeedz\Cube\Models\User;
use QRFeedz\Cube\Models\Widget;

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
         * Users: They are 2 partners. In this case one will have "admin"
         * profile, and the 2nd one will have a "non-admin" profile.
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

        // Create CrocRock client.
        $client = Client::create([
            'name' => 'Croc & Rock',
            'address' => '27 avenue du XX ème corps',
            'postal_code' => '54000',
            'locality' => 'Nancy',
            'country_id' => Country::firstWhere('name', 'France')->id,
            'affiliate_id' => Affiliate::firstWhere('name', 'Karine Esnault')->id,
            'locale_id' => Locale::firstWhere('code', 'fr')->id,
        ]);

        // This is the user connected to the afilliate.
        $affiliateUser = User::create([
            'name' => env('CROCROCK_AFFILIATE_NAME'),
            'email' => env('CROCROCK_AFFILIATE_EMAIL'),
            'password' => bcrypt(env('CROCROCK_AFFILIATE_PASSWORD')),
        ]);

        // Associate Karine affiliate with the user Karine.
        $affiliate->user()->associate($affiliateUser)->save();

        // Create restaurant admin.
        $admin = User::create([
            'client_id' => $client->id,
            'name' => env('CROCROCK_ADMIN_NAME'),
            'email' => env('CROCROCK_ADMIN_EMAIL'),
            'password' => bcrypt(env('CROCROCK_ADMIN_PASSWORD')),
        ]);

        // Create restaurant standard user.
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
         * The 2nd user (non-admin) will not have direct permissions.
         */
        Authorization::firstWhere('code', 'affiliate')
                     ->clients()
                     ->attach(
                         $client->id,
                         ['user_id' => $affiliateUser->id] // Karine User
                     );

        Authorization::firstWhere('code', 'admin')
                     ->clients()
                     ->attach(
                         $client->id,
                         ['user_id' => $admin->id] // Peres User
                     );

        /**
         * Time to create the questionnaire. This will be a simple questionnaire
         * with just one question: How do you rate us?. It will have 3 main
         * locales: FR, EN, PT. In case the rating is <2 or =5 a textarea
         * should slide down to ask for more information (optional).
         *
         * 1 questionnaire, 3 locales
         * 1 widget - stars rating + textarea conditional
         * 2 conditionals ( <=2 and =5 ).
         *
         * After there will be a promo message (written in the 3 locales) and
         * a final page for the social sharing links.
         *
         * The promo message and the final social page are not questionnaire
         * pages, "per se", so they don't count for the questionnaire counter.
         */
        $questionnaire = Questionnaire::create([
            'name' => 'CrocRock 2024',
            'title' => 'Restaurant CrocRock',
            'starts_at' => now(),
        ]);

        $questionnaire->client()->associate($client);
        $questionnaire->save();

        /**
         * Now it's time to configure the OpenAI prompt. On this case,
         * the restaurant is a start-up, so highly sensitive to feedback,
         * and it's interest to know mostly the food quality since that's
         * where they are betting to be different. They also want to
         * know if visitors left their emails so they can reach to them
         * with more information.
         */
        $prompt = OpenAIPrompt::make([
            'prompt_i_am_a_business_of' => 'a restaurant in Nancy',
            'prompt_I_am_paying_attention_to' => 'my food quality, and if tourists like it or not',
            'balance_type' => 'balanced',
            'should_be_email_aware' => true,
        ]);

        $prompt->questionnaire()->associate($questionnaire);
        $prompt->save();

        /**
         * Next step is to assign widgets to the questionnaire.
         * When we scan the qr code we should see:
         * A page with a unique stars rating, with the conditionals.
         * Then a page telling about a promotion.
         * Then a page about social links sharing.
         */
        $question = Question::make([
            'is_required' => true,
        ]);

        $question->questionnaire()->associate($questionnaire);
        $question->save();

        /**
         * Lets create the locale captions.
         * We will have 2 languages: French and English.
         */
        $localeEN = Locale::firstWhere('code', 'en');
        $localeFR = Locale::firstWhere('code', 'fr');

        $question->captions()->attach($localeEN->id, ['caption' => 'How was your meal?']);
        $question->captions()->attach($localeFR->id, ['caption' => 'Comme avez vous passez?']);

        /**
         * Next is to add a stars rating widget to the question, and then
         * attach the respective caption locales to the widget instance.
         *
         * Remember that you have a QuestionsWidget model that will ease the
         * life of understanding what widgets belong to what question.
         */
        $widget = Widget::firstWhere('canonical', 'stars-rating');

        $question->widgets()->save($widget);
    }
}

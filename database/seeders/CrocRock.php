<?php

namespace QRFeedz\Database\Seeders;

use Illuminate\Database\Seeder;
use QRFeedz\Cube\Models\Affiliate;
use QRFeedz\Cube\Models\Authorization;
use QRFeedz\Cube\Models\Client;
use QRFeedz\Cube\Models\Country;
use QRFeedz\Cube\Models\Locale;
use QRFeedz\Cube\Models\OpenAIPrompt;
use QRFeedz\Cube\Models\PageType;
use QRFeedz\Cube\Models\Question;
use QRFeedz\Cube\Models\QuestionWidget;
use QRFeedz\Cube\Models\QuestionWidgetTypeConditional;
use QRFeedz\Cube\Models\QuestionWidgetType;
use QRFeedz\Cube\Models\Questionnaire;
use QRFeedz\Cube\Models\User;
use QRFeedz\Cube\Models\WidgetType;

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
            'locale_id' => Locale::firstWhere('canonical', 'fr')->id,
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
        Authorization::firstWhere('canonical', 'affiliate')
                     ->clients()
                     ->attach(
                         $client->id,
                         ['user_id' => $affiliateUser->id] // Karine User
                     );

        Authorization::firstWhere('canonical', 'admin')
                     ->clients()
                     ->attach(
                         $client->id,
                         ['user_id' => $admin->id] // Peres User
                     );

        /**
         * Time to create the questionnaire.
         *
         * 2 survey pages, with one question each:
         * 1 question - Overall rating.
         * 1 question - Anything specific to improve?
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
         * Lets create the pages:
         *
         * 1. Splash screen.
         * 2. Select language.
         * 3. 1 question - Overall rating.
         * 4. 1 question - Anything specific to improve?
         * 5. promo page.
         *
         * Pages are added to the via the PageTypeQuestionnaire pivot table.
         */

        $pageTypeIds = [
            PageType::firstWhere('canonical', 'splash-page-5-secs')->id,
            PageType::firstWhere('canonical', 'locale-select-page')->id,
            PageType::firstWhere('canonical', 'survey-page-default')->id,
            PageType::firstWhere('canonical', 'survey-page-default')->id,
            PageType::firstWhere('canonical', 'promo-page-default')->id
        ];

        foreach ($pageTypeIds as $pageTypeId) {
            $pageType = $questionnaire->pageTypes()->newPivot([
                'questionnaire_id' => $questionnaire->id,
                'page_type_id' => $pageTypeId
            ]);

            $pageType->save();
        }

        /**
         * Lets create the questions associated with each survey page.
         * Each question will have a localable in each of the available
         * languages. And will then have a widget(s) collection if needed.
         *
         * On this case, we want to create 1 question per survey page:
         *
         * 1st page:
         * - Overall, how do you rate your experience with us?
         *
         * 2nd page:
         * - Anything to let us know to help us improve?
         *
         */

        // Obtain the page type questionnaire instances (ordered by index).
        $pageTypes = $questionnaire->pageTypes;

        /**
         * The first 2 pages they don't need any work.
         * The next 2 pages they need to have questions, one question per page.
         * The last promo page doesn't need to have anything.
         */
        foreach ($pageTypes as $pageType) {
            $pivot = $pageType->pivot;

            if ($pageType->canonical == 'survey-page-default' && $pivot->index == 3) {
                /**
                 * Add the overall question stars rating.
                 * - Question
                 * - Locales
                 * - Widgets
                 * - Conditionals
                 */

                // Add question.
                $question = Question::create([
                    'page_type_questionnaire_id' => $pageType->pivot->id,
                    'is_analytical' => true,
                    'is_used_for_personal_data' => false,
                    'is_single_value' => true,
                    'is_required' => true
                ]);

                // Add locales.
                Locale::firstWhere('canonical', 'en')
                      ->questions()
                      ->attach(
                          $question->id,
                          ['caption' => 'How do you rate us, in overall?']
                      );

                Locale::firstWhere('canonical', 'fr')
                      ->questions()
                      ->attach(
                          $question->id,
                          ['caption' => 'Comment nous évaluez-vous, globalement ?']
                      );

                Locale::firstWhere('canonical', 'de')
                      ->questions()
                      ->attach(
                          $question->id,
                          ['caption' => 'Wie bewerten Sie uns insgesamt?']
                      );

                /**
                 * Add the widget type to the question, by then creating a
                 * QuestionWidgetType model instance (pivot).
                 */
                $questionWidgetType = new QuestionWidgetType();
                $questionWidgetType->question_id = $question->id;
                $questionWidgetType->widget_type_id = WidgetType::firstWhere('canonical', 'stars-rating')->id;
                $questionWidgetType->save();

                // Now, lets create the widget type conditionals.
                $questionWidgetTypeConditional = QuestionWidgetTypeConditional::create([
                    'question_widget_type_id' => $questionWidgetType->id,
                    'when' => ['value' => '<=2'],
                    'then' => ['action' => 'textarea.open']
                ]);

                $questionWidgetTypeConditional = QuestionWidgetTypeConditional::create([
                    'question_widget_type_id' => $questionWidgetType->id,
                    'when' => ['value' => '==5'],
                    'then' => ['action' => 'textarea.open']
                ]);
            }

            if ($pageType->canonical == 'survey-page-default' && $pivot->index == 4) {
                /**
                 * Add anything else to let us know.
                 * - Question
                 * - Locales
                 * - Widgets
                 * - Conditionals
                 */
                // Add question.
                $question = Question::create([
                    'page_type_questionnaire_id' => $pageType->pivot->id,
                    'is_analytical' => true,
                    'is_used_for_personal_data' => false,
                    'is_single_value' => true,
                    'is_required' => true
                ]);

                // Add locales.
                Locale::firstWhere('canonical', 'en')
                      ->questions()
                      ->attach(
                          $question->id,
                          ['caption' => 'Anything else to let us know?']
                      );

                Locale::firstWhere('canonical', 'fr')
                      ->questions()
                      ->attach(
                          $question->id,
                          ['caption' => 'Y a-t-il autre chose que vous souhaitez nous communiquer ?']
                      );

                Locale::firstWhere('canonical', 'de')
                      ->questions()
                      ->attach(
                          $question->id,
                          ['caption' => 'Gibt es sonst noch etwas, das Sie uns mitteilen möchten?']
                      );

                /**
                 * Add the widget type to the question, by then creating a
                 * QuestionWidgetType model instance (pivot).
                 */
                $questionWidgetType = new QuestionWidgetType();
                $questionWidgetType->question_id = $question->id;
                $questionWidgetType->widget_type_id = WidgetType::firstWhere('canonical', 'textarea')->id;
                $questionWidgetType->save();
            }
        };

        /**
         * Lets also add categories, groups and tags, to test the questionnaire.
         * Category: Restaurant
         * Tag:      Nancy (location)
         * Group:    Pioneer
         */
        Tag::create([
            'name' => 'Nancy',
            'description' => 'Nancy location'
        ]);
    }
}

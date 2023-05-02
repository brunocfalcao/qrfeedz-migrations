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
use QRFeedz\Cube\Models\Questionnaire;
use QRFeedz\Cube\Models\QuestionWidget;
use QRFeedz\Cube\Models\QuestionWidgetConditional;
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

                // Add widget.
                $question->widgetTypes()
                         ->attach(
                             WidgetType::firstWhere('canonical', 'stars-rating')->id
                         );

                // Add widget conditional.


                /*
                Authorization::firstWhere('canonical', 'affiliate')
                     ->clients()
                     ->attach(
                         $client->id,
                         ['user_id' => $affiliateUser->id] // Karine User
                     );
                */





                dd($question);
            }

            if ($pageType->canonical == 'survey-page-default' && $pivot->index == 4) {
                dd('4');
            }
        };

        dd('---');

        /**
         * Lets create que questions. There are 2 questions, one on each
         * page type questionnaire instance. Our approach is to have
         * one question per page.
         */
        $question = Question::make([
            'is_required' => true,
        ]);

        $question->pageTypeQuestionnaire()->associate($surveyPage1);
        $question->save();

        dd('good.');

        /**
         * Lets create the locale captions.
         * We will have 2 languages: French and English.
         */
        $localeEN = Locale::firstWhere('canonical', 'en');
        $localeFR = Locale::firstWhere('canonical', 'fr');

        $question->captions()->attach($localeEN->id, ['caption' => 'How was your meal?']);
        $question->captions()->attach($localeFR->id, ['caption' => 'Comme avez vous passez?']);

        /**
         * Next is to add a stars rating widget to the question, and then
         * attach the respective caption locales to the widget instance.
         *
         * Remember that you have a QuestionsWidget model that will ease the
         * life of understanding what widgets belong to what question.
         *
         * We also add 2 conditionals on the widget:
         * <=2 or =5 opens textarea.
         *
         * The textarea is not a widget, but a feature from almost all the
         * widgets that accept ratings in a certain way.
         *
         * Conditionals exist in certain options:
         * textarea = will slide down a textarea to request more details.
         * subtext = will show a message below the widget.
         */
        $widget = WidgetType::firstWhere('canonical', 'stars-rating');

        // We need to save the data using the Pivot model, and not the N-N.
        $questionWidget = new QuestionWidget();
        $questionWidget->question_id = $question->id;
        $questionWidget->widget_id = $widget->id;

        $questionWidget->save();

        /**
         * This question widget doesn't have a caption "per se", but the
         * conditionals have, specially the subtext conditional. Lets add
         * a new entry in the question_widget_conditionals on it.
         *
         * Also, add the textarea slide down.
         */
        QuestionWidgetConditional::make([
            'when' => 'value <=2 || value > 2',
            'then' => ['textarea-slidedown'],
        ])->questionWidget()
          ->associate($questionWidget)
          ->save();

        $subtext = QuestionWidgetConditional::make([
            'when' => 'value == 3',
            'then' => ['subtext-appear'],
        ]);

        $subtext->questionWidget()
                ->associate($questionWidget);
        $subtext->save();

        // The subtext needs to have respective locales (EN and FR).
        $subtext->captions()
                ->attach(
                    $localeEN->id,
                    ['caption' => 'Right in the middle!']
                );

        $subtext->captions()
                ->attach(
                    $localeFR->id,
                    ['caption' => 'Au millieux! Parfait!']
                );

        /**
         * Lets add a question, without caption, that just serves as a
         * placeholder for the promo-page.
         */
        $question = Question::make([
            'is_required' => false,
            'is_analytical' => false,
            'is_used_for_personal_data' => true, //because we retrieve emails
            'is_single_value' => true,
        ]);

        $question->page()->associate($pagePromo);
        $question->save();

        $widget = WidgetType::firstWhere('canonical', 'promo-coupon-page');

        $questionWidget = new QuestionWidget();
        $questionWidget->question_id = $question->id;
        $questionWidget->widget_id = $widget->id;

        $questionWidget->save();

        /**
         * Now it's time to add the locales for the promotion.
         * For the promo widget, the locales would need locale placeholders
         * and also the placeholder codes.
         * - promot-title ("Your coupon")
         * - promo-text ("10% off on your next visit")
         * - promo-subtext "Just bring the QR our paper you received by email"
         * - promo-email "Please enter your email"
         * - The input text is already part of the widget.
         * - Special button that sends an email with the promo code generation.
         * - The promo code generation is attached to the promotions that the
         *   client is creating for the questionnaires. The promotion is
         *   attached to a questionnaire, and can have an end date.
         */
        $questionWidget->captions()->attach(
            $localeEN->id,
            ['caption' => 'Get your promotion code!', 'placeholder' => 'promo-title']
        );

        $questionWidget->captions()->attach(
            $localeFR->id,
            ['caption' => 'Trouvez votre code promo!', 'placeholder' => 'promo-title']
        );

        $questionWidget->captions()->attach(
            $localeEN->id,
            ['caption' => '25% discount on your next visit', 'placeholder' => 'promo-text']
        );

        $questionWidget->captions()->attach(
            $localeFR->id,
            ['caption' => '25% rabais dans votre prochaine visite', 'placeholder' => 'promo-text']
        );

        $questionWidget->captions()->attach(
            $localeEN->id,
            ['caption' => 'Bring us the received promo code next time', 'placeholder' => 'promo-subtext']
        );

        $questionWidget->captions()->attach(
            $localeFR->id,
            ['caption' => 'Vieullez nous trouvez votre code la prochaine fois', 'placeholder' => 'promo-subtext']
        );

        $questionWidget->captions()->attach(
            $localeEN->id,
            ['caption' => 'Enter your email to receive the promotion', 'placeholder' => 'promo-email']
        );

        $questionWidget->captions()->attach(
            $localeFR->id,
            ['caption' => 'S il vous plait mettez votre email ici', 'placeholder' => 'promo-email']
        );
    }
}

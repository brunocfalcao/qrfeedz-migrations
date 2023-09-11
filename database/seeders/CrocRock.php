<?php

namespace QRFeedz\Database\Seeders;

use Illuminate\Database\Seeder;
use QRFeedz\Cube\Models\Authorization;
use QRFeedz\Cube\Models\Category;
use QRFeedz\Cube\Models\Client;
use QRFeedz\Cube\Models\Country;
use QRFeedz\Cube\Models\Locale;
use QRFeedz\Cube\Models\OpenAIPrompt;
use QRFeedz\Cube\Models\Page;
use QRFeedz\Cube\Models\PageInstance;
use QRFeedz\Cube\Models\Question;
use QRFeedz\Cube\Models\QuestionInstance;
use QRFeedz\Cube\Models\Questionnaire;
use QRFeedz\Cube\Models\User;
use QRFeedz\Cube\Models\Widget;
use QRFeedz\Cube\Models\WidgetInstance;

class CrocRock extends Seeder
{
    public function run()
    {
        // Create CrocRock client.
        $client = Client::create([
            'name' => 'Croc & Rock',
            'address' => '27 avenue du XX ème corps',
            'postal_code' => '54000',
            'locality' => 'Nancy',
            'country_id' => Country::firstWhere('name', 'France')->id,
            'locale_id' => Locale::firstWhere('canonical', 'fr')->id,
        ]);

        // This is the user connected to the afilliate. For testing purposes.
        $affiliate = User::create([
            'name' => env('CROCROCK_AFFILIATE_NAME'),
            'email' => env('CROCROCK_AFFILIATE_EMAIL'),
            'password' => bcrypt(env('CROCROCK_AFFILIATE_PASSWORD')),
            'address' => 'Le chauffour 4',
            'postal_code' => '2364',
            'locality' => 'St-Brais',
            'commission_percentage' => 50,
            'country_id' => Country::firstWhere('name', 'Switzerland')->id,
        ]);

        $client->affiliate()
               ->associate($affiliate)
               ->save();

        /**
         * Besides being an affiliate in the users/clients we also
         * need to add this affiliate to the authorization Morph table.
         */
        Authorization::firstWhere('canonical', 'affiliate')
            ->clients()
            ->attach(
                $client->id,
                ['user_id' => $affiliate->id] // Affiliate User
            );

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
         * The client that will have Peres as admin.
         * The 2nd user (non-admin) will not have direct permissions.
         */
        Authorization::firstWhere('canonical', 'client-admin')
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
            'location_id' => 1, // This is because the client creation triggered a location.
            'category_id' => Category::firstWhere('canonical', 'restaurant')->id,
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
        $pageIds = [
            Page::firstWhere('canonical', 'full-screen')->id,
            Page::firstWhere('canonical', 'full-screen')->id,
            Page::firstWhere('canonical', 'full-screen')->id,
            Page::firstWhere('canonical', 'full-screen')->id,
            Page::firstWhere('canonical', 'full-screen')->id,
        ];

        foreach ($pageIds as $pageId) {
            PageInstance::create([
                'page_id' => $pageId,
                'questionnaire_id' => $questionnaire->id,
            ]);
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
         */

        // Obtain the page type questionnaire instances (ordered by index).
        $pageInstances = $questionnaire->pageInstances;

        /**
         * The first 2 pages they don't need any work.
         * The next 2 pages they need to have questions, one question per page.
         * The last promo page doesn't need to have anything.
         */
        foreach ($pageInstances as $pageInstance) {

            /**
             * Splash page. No feedback is received. No locale used.
             */
            if ($pageInstance->id == 1) {
                $questionInstance = QuestionInstance::create([
                    'page_instance_id' => $pageInstance->id,
                    'is_analytical' => false,
                    'is_single_value' => false,
                    'is_used_for_personal_data' => false,
                ]);

                $widgetInstance = WidgetInstance::create([
                    'question_instance_id' => $questionInstance->id,
                    'widget_id' => Widget::firstWhere('canonical', 'splash-1')->id,
                ]);
            }

            /**
             * Locale selection. Will be kept forever in all pages.
             * The locale is a session variable that changes in case there
             * is a querystring parameter 'lang=en' as example. After that
             * it will keep the same locale on the same session.
             */
            if ($pageInstance->id == 2) {
                $questionInstance = QuestionInstance::create([
                    'page_instance_id' => $pageInstance->id,
                    'is_analytical' => false,
                    'is_single_value' => false,
                    'is_used_for_personal_data' => false,
                ]);

                $widgetInstance = WidgetInstance::create([
                    'question_instance_id' => $questionInstance->id,
                    'widget_id' => Widget::firstWhere('canonical', 'locale-selector-1')->id,
                ]);
            }

            /**
             * First survey page. A "how did it go?" 5 stars widget.
             * In case there is a  <=2 or a =5 then a "why?". The label on the
             * why is different so we need use it on the widget conditionals.
             *
             * On this case we also need to add question and widget locales,
             * and also widget conditional locales (en, fr, it).
             */
            if ($pageInstance->id == 3) {
                $questionInstance = QuestionInstance::create([
                    'page_instance_id' => $pageInstance->id,
                    'is_analytical' => true,
                    'is_single_value' => true,
                    'is_used_for_personal_data' => false,
                ]);

                $widgetInstance = WidgetInstance::create([
                    'question_instance_id' => $questionInstance->id,
                    'widget_id' => Widget::firstWhere('canonical', 'stars-rating')->id,
                ]);

                Locale::firstWhere('canonical', 'en')
                    ->questionInstances()
                    ->attach(
                        $questionInstance->id,
                        ['caption' => 'How do you rate us, in overall?']
                    );

                Locale::firstWhere('canonical', 'fr')
                    ->questionInstances()
                    ->attach(
                        $questionInstance->id,
                        ['caption' => 'Ca va etait?']
                    );

                Locale::firstWhere('canonical', 'it')
                    ->questionInstances()
                    ->attach(
                        $questionInstance->id,
                        ['caption' => 'Tuto va bienne?']
                    );

                // Now we need to load the widget instance conditionals.
                $widgetInstanceConditional = WidgetInstance::create([
                    'widget_instance_id' => $widgetInstance->id,
                    'widget_id' => Widget::firstWhere('canonical', 'textarea')->id,
                    'index' => null,
                    'when' => ['value' => '<=2'],
                    'then' => ['action' => 'slidedown'],
                ]);

                Locale::firstWhere('canonical', 'en')
                    ->widgetInstances()
                    ->attach(
                        $widgetInstanceConditional->id,
                        ['caption' => 'What went wrong?']
                    );

                Locale::firstWhere('canonical', 'it')
                    ->widgetInstances()
                    ->attach(
                        $widgetInstanceConditional->id,
                        ['caption' => 'Qui ha passato malle?']
                    );

                Locale::firstWhere('canonical', 'fr')
                    ->widgetInstances()
                    ->attach(
                        $widgetInstanceConditional->id,
                        ['caption' => 'Ce etait mal?']
                    );
            }

            /**
             * 2nd survey page. We ask "anything you would like to see
             * improved?" -- This is a textarea.
             */
            if ($pageInstance->id == 4) {
            }

            /**
             * This is a promotional page from the
             *
             * @var [type]
             */
            if ($pageInstance->id == 5) {
            }
        }
    }
}

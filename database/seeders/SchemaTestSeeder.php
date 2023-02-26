<?php

namespace QRFeedz\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use QRFeedz\Cube\Models\Category;
use QRFeedz\Cube\Models\Organization;
use QRFeedz\Cube\Models\Place;
use QRFeedz\Cube\Models\Question;
use QRFeedz\Cube\Models\Questionnaire;
use QRFeedz\Cube\Models\Widget;

class SchemaTestSeeder extends Seeder
{
    public function run()
    {
        /**
         $faker = Faker\Factory::create();
         // generate data by calling methods

         echo $faker->name();
         // 'Vince Sporer'

         echo $faker->email();
         // 'walter.sophia@hotmail.com'

         echo $faker->text();
         // 'Numquam ut mollitia at consequuntur inventore dolorem.'
         */

        //$faker = new Faker\Generator();
        //$faker->addProvider(new Faker\Provider\en_US\Address($faker));

        /**
         * Organization -> place -> questionnaire -> question -> answer
         */

        /**
         * Start by creating a random number of organizations. Everything
         * starts at the topic, and then we will cascade for places,
         * questionnaires, questions and answers.
         */
        Organization::factory()->count(rand(20, 30))->create();

        /**
         * Populate categories.
         */
        $categoryHotel = Category::firstWhere('name', 'Hotel');
        $categoryCantine = Category::firstWhere('name', 'Cantine');
        $categoryRestaurant = Category::firstWhere('name', 'Restaurant');

        foreach (Organization::all() as $organization) {
            /**
             * Then by creating random places that will store questionnares
             * and answers to questionnaires.
             */
            $places = Place::factory()->count(rand(1, 3))->create([
                'organization_id' => $organization->id,
                'address' => $organization->address,
                'postal_code' => $organization->postal_code,
                'locality' => $organization->locality,
                'country_id' => $organization->country_id,
            ]);
        }

        /**
         * Now, for each place we create one random questionnaire. The
         * questionnaire itself is like a "placeholder" for the remaining
         * data repository of questions, answers and widgets.
         */
        foreach (Place::all() as $place) {
            $questionnaire = Questionnaire::create([
                'description' => 'Questionnaire for ' . $place->name,
                'qrcode' => (string) Str::uuid(),
                'place_id' => $place->id
            ]);

            /**
             * Next we need to randomize the category between hotel,
             * restaurante, and cantine. For each type, there will be
             * a different questionnaire created. I want to have them
             * in different probabilities still. More restaurants than
             * hotels, more hotels than cantines.
             */
            $type = rand(1, 100);
            $category = null;
            $callable = null;

            if ($type < 25) {
                $category = $categoryHotel;
                $callable = 'createHotelQuestions';
            } elseif ($type < 90) {
                $category = $categoryRestaurant;
                $callable = 'createRestaurantQuestions';
            } else {
                $category = $categoryCantine;
                $callable = 'createCantineQuestions';
            }

            /**
             * Attach a related category. Then we will create the
             * questionnaire instance based on this category, for testing
             * purposes.
             */
            $place->categories()
                  ->save($category);

            /**
             * Time to create the questions collection for the respective
             * questionnaire type. For that we dynamically call the
             * protected method.
             */
            $this->$callable($questionnaire);
        }

        /**
         * Now it's time to create the questionnaire instances. So, we will
         * create 3 types of questionnaires, and then attach each of those
         * via the questions and widgets structure to the questionnaire id.
         *
         * We will use 3 different functions for this:
         * createRestaurantQuestions($questionnaire)
         * createHotelQuestions($questionnaire)
         * createCantineQuestions($questionnaire)
         */
    }

    protected function createRestaurantQuestions(Questionnaire $questionnaire)
    {
        /**
         * We add the following questions (2 per page):
         *
         * How much did you like what you eat?
         * [ stars - 1 to 5 ]
         *
         * How much did you like the cleaniness?
         * [ stars - 1 to 5 ]
         *
         * Did you pay a fair price for the service?
         * [ radio - yes  / no / don't know ]
         *
         * Anything else to tell us?
         * [ textarea ]
         */

        $question = Question::create([
            'questionnaire_id' => $questionnaire->id,
            'question' => 'How much did you like what you ate?',
            'page_num' => 1,
            'is_required' => true,
            'widget_group_uuid' => Widget::newestByCanonical('stars-rating')->group_uuid
        ]);

        Question::create([
            'questionnaire_id' => $questionnaire->id,
            'question' => 'How much did you like the cleaniness?',
            'page_num' => 1,
            'is_required' => true,
            'widget_group_uuid' => Widget::newestByCanonical('stars-rating')->group_uuid
        ]);

        Question::create([
            'questionnaire_id' => $questionnaire->id,
            'question' => 'Did you consider you paid a fair price?',
            'page_num' => 2,
            'is_required' => true,
            'widget_group_uuid' => Widget::newestByCanonical('radio-group')->group_uuid
        ]);

        Question::create([
            'questionnaire_id' => $questionnaire->id,
            'question' => 'Anything else to tell us?',
            'page_num' => 2,
            'widget_group_uuid' => Widget::newestByCanonical('textarea')->group_uuid
        ]);
    }

    protected function createCantineQuestions(Questionnaire $questionnaire)
    {
        info('creating cantine questions ...');
    }

    protected function createHotelQuestions(Questionnaire $questionnaire)
    {
        info('creating hotel questions ...');
    }
}

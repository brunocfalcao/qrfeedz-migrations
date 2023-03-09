<?php

namespace QRFeedz\Database\Seeders;

use Illuminate\Database\Seeder;
use QRFeedz\Cube\Models\Category;
use QRFeedz\Cube\Models\Client;
use QRFeedz\Cube\Models\Group;
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
         * Client -> questionnaire -> question -> answer
         *       <-> group
         */

        /**
         * Start by creating a random number of clients. Everything
         * starts at the topic, and then we will cascade for groups,
         * questionnaires, questions and answers.
         */
        Client::factory()->count(rand(20, 30))->create();

        /**
         * Populate categories.
         */
        $categoryHotel = Category::firstWhere('name', 'Hotel');
        $categoryCantine = Category::firstWhere('name', 'Cantine');
        $categoryRestaurant = Category::firstWhere('name', 'Restaurant');
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
        $this->question(
            questionnaire: $questionnaire,
            caption: 'How much did you like what you ate?',
            canonical: 'stars-rating'
        );

        $this->question(
            questionnaire: $questionnaire,
            caption: 'How much did you like the cleaniness?',
            canonical: 'stars-rating'
        );

        $this->question(
            questionnaire: $questionnaire,
            caption: 'Did you consider you paid a fair price?',
            canonical: 'radio-group',
            pageNum: 2
        );

        $this->question(
            questionnaire: $questionnaire,
            caption: 'Anything else to tell us?',
            canonical: 'textarea',
            pageNum: 2
        );
    }

    protected function createCantineQuestions(Questionnaire $questionnaire)
    {
        info('creating cantine questions ...');
    }

    protected function createHotelQuestions(Questionnaire $questionnaire)
    {
        info('creating hotel questions ...');
    }

    protected function question(Questionnaire $questionnaire, string $caption, string $canonical, $pageNum = 1, $isRequired = true, string $locale = 'en')
    {
        $question = Question::create([
            'questionnaire_id' => $questionnaire->id,
            'caption' => $caption,
            'page_num' => $pageNum,
            'is_required' => $isRequired,
            'widget_group_uuid' => Widget::newestByCanonical($canonical)->group_uuid,
        ]);
    }
}

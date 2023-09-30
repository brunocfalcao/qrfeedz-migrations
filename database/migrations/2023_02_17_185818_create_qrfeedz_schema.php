<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('locales', function (Blueprint $table) {
            $table->id();

            $table->string('canonical')
                ->unique()
                ->comment('Locale canonical, like pt, en, cn, etc');

            $table->string('name')
                ->comment('The described locale name');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('authorizations', function (Blueprint $table) {
            $table->id();

            $table->string('canonical')
                  ->unique()
                  ->comment('The authorization canonical name');

            $table->string('name')
                  ->comment('The authorization name');

            $table->text('description')
                  ->nullable()
                  ->comment('Details on the authorization type');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('authorizables', function (Blueprint $table) {
            $table->id();

            $table->morphs('model');
            $table->foreignId('authorization_id');
            $table->foreignId('user_id');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('countries', function (Blueprint $table) {
            $table->id();

            $table->string('code')
                  ->comment('Country code');

            $table->string('name')
                  ->comment('Country name');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('clients', function (Blueprint $table) {
            $table->id();

            $table->string('name')
                  ->comment('The client name');

            $table->foreignId('user_affiliate_id')
                  ->nullable()
                  ->comment('Related affiliate, in table users, if exists');

            $table->foreignId('locale_id')
                  ->comment('Related default locale. Cascades to questionnaire');

            $table->string('vat_number')
                  ->nullable()
                  ->comment('Client fiscal number');

            $table->text('address')
                ->nullable()
                ->comment('The client address');

            $table->string('postal_code')
                ->nullable()
                ->comment('The client postal code');

            $table->string('city')
                  ->nullable()
                  ->comment('The client city');

            $table->foreignId('country_id')
                  ->comment('Related country');

            $table->string('latitude')
                  ->nullable()
                  ->comment('Address latitude');

            $table->string('longitude')
                  ->nullable()
                  ->comment('Address longitude');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('users', function (Blueprint $table) {

            $table->string('name')
                  ->nullable()
                  ->change();

            $table->string('password')
                  ->nullable()
                  ->change();

            $table->foreignId('client_id')
                  ->nullable()
                  ->after('email');

            $table->foreignId('locale_id')
                  ->comment('The default locale of all notifications that are sent to this user')
                  ->after('client_id');

            $table->boolean('is_super_admin')
                  ->default(false)
                  ->after('locale_id')
                  ->comment('Has a super admin role?');

            $table->boolean('is_admin')
                  ->default(false)
                  ->after('is_super_admin')
                  ->comment('Has an admin role?');

            $table->unsignedInteger('commission_percentage')
                  ->default(0)
                  ->after('email')
                  ->comment('Comission in percentage (0 to 100), in case it is an affiliate');

            $table->text('address')
                ->nullable()
                ->after('commission_percentage')
                ->comment('User address');

            $table->string('postal_code')
                  ->nullable()
                  ->after('address')
                  ->comment('User postal code');

            $table->string('locality')
                  ->nullable()
                  ->after('postal_code')
                  ->comment('User locality');

            $table->foreignId('country_id')
                  ->nullable()
                  ->after('locality')
                  ->comment('User country');

            $table->dropColumn('email_verified_at');

            $table->softDeletes();
        });

        Schema::create('questionnaires', function (Blueprint $table) {
            $table->id();

            $table->char('uuid', 36)
                  ->unique()
                  ->nullable()
                  ->comment('This will be the unique questionnaire qr code that will be scanned by a client.');

            $table->foreignId('location_id')
                  ->nullable()
                  ->comment('Related location');

            $table->foreignId('locale_id')
                  ->nullable()
                  ->comment('The default locale for this questionnaire, in case a language is not selected');

            $table->foreignId('category_id')
                  ->comment('Related system assigned category (hotel, restaurant, product, etc)');

            $table->string('name')
                  ->nullable()
                  ->comment('Human name that the questionnaire is used for. E.g.: Terrace Summer 2021, used for the title html tag too');

            $table->string('title')
                  ->nullable()
                  ->comment('Used for the questionnaire title, and for the title html tag');

            $table->text('description')
                  ->nullable()
                  ->comment('In case we want to describe a specific qr code instance for whatever reason');

            $table->string('color_primary')
                  ->comment('Primary color, normally used for the background colors, widgets background buttons, etc');

            $table->string('color_secondary')
                  ->comment('Secondary color, normally used for the actionable buttons like "start questionnaire"');

            $table->string('file_logo')
                  ->nullable()
                  ->comment('Image logo, appears in the questionnaire headers, preferably SVG or PNG/transparent');

            $table->boolean('is_active')
                  ->default(true)
                  ->comment('Overrides the active dates. In case we want to immediate inactivate the questionnaire');

            $table->json('data')
                  ->nullable()
                  ->comment('Additional data that identifies or better details this questionnaire');

            $table->dateTime('starts_at')
                  ->nullable()
                  ->comment('When is the questionnaire active, and ready to receive data');

            $table->dateTime('ends_at')
                  ->nullable()
                  ->comment('When will the questionnaire stop receiving data');

            $table->timestamps();
            $table->softDeletes();
        });

        /**
         * The OpenAI prompt settings for the questionnaires. Each questionnaire
         * can have a specific prompt type for a better AI feedback conclusion
         * from the clients feedbacks. For example, a restaurant can be more
         * interested of specific type of feedback than others.
         *
         * The OpenAI configuration is based on the following parameters:
         *
         * - My questionnaire is about:
         *   (a restaurant, a town  hall event).
         *
         * - I am specially paying attention to:
         *   (food quality, feedback from employees, ...).
         *
         * - I want to have a (balanced/worst cases/best cases) feedback type
         *   Each will give a type of feedback conclusion focused on the
         *   worst cases for improvement, the best cases for continuation, or
         *   a balanced feedback conclusion (mix of both).
         *
         * - I want to know if I have new emails:
         *   (This is an automated configuration that will tell the
         *   questionnaire owner if on the previous feedback duration there
         *   were someone that left the email).
         *
         * In case there is no OpenAI prompt configuration, qrfeedz will use
         * a default prompt text, but it can give less quality results to the
         * questionnaire owner.
         */
        Schema::create('openai_prompts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('questionnaire_id')
                  ->comment('Related questionnaire');

            $table->text('prompt_i_am_a_business_of')
                  ->nullable()
                  ->comment('OpenAI specific prompt text about what the questionnaire/business is about');

            $table->text('prompt_i_am_paying_attention_to')
                  ->nullable()
                  ->comment('OpenAI specific prompt text');

            $table->string('balance_type')
                  ->default('balanced')
                  ->comment('balanced, worst-cases, best-cases. This will be auto-generated prompt text');

            $table->boolean('should_be_email_aware')
                  ->default(true)
                  ->comment('Notify if there were emails being received by visitors');

            $table->timestamps();
            $table->softDeletes();
        });

        /**
         * Categories can be a system grouped tag. Restaurant, Hotel, Product,
         * besides others. They are system-assigned and not by the user.
         */
        Schema::create('categories', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            $table->string('canonical')
                  ->unique()
                  ->comment('Category canonical');

            $table->text('description')
                  ->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        /**
         * Tags are joker attributes that are related with clients,
         * groups, questionnaires, etc. They can be created and used as
         * requested.
         */
        Schema::create('tags', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            $table->text('description')
                  ->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('client_tag', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_id');
            $table->foreignId('tag_id');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('taggables', function (Blueprint $table) {
            $table->id();

            $table->morphs('model');
            $table->foreignId('tag_id');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('localables', function (Blueprint $table) {
            $table->id();

            $table->morphs('model');
            $table->foreignId('locale_id');

            $table->string('caption')
                  ->comment('The sentence in the respective locale');

            $table->string('placeholder')
                  ->nullable()
                  ->comment('Used in case a widget has several placeholders of text.g.: "subtext" or "promo-coupon-header"');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('widgets', function (Blueprint $table) {
            $table->id();

            $table->string('name')
                  ->comment('E.g: Textbox, 1 to N, etc');

            $table->text('description')
                  ->nullable()
                  ->comment('Extended description, validation options, integration details, etc');

            $table->string('canonical')
                  ->unique()
                  ->comment('Widget canonical, easier to find when relating with question instances');

            $table->boolean('is_progressable')
                  ->default(true)
                  ->comment('If it is progressable, then it will be part of the questionnaire count (pages). If not then it is used for the last pages like direct message or social sharing');

            $table->boolean('is_full_page')
                  ->default(false)
                  ->comment('Full page widget means the widget will not have other widgets with it and occupies a full page, like direct visitor messaging or social sharing full screen pages. Also a full page widget will not have the placeholder for the question instance');

            $table->string('view_component_namespace')
                  ->comment('The view component namespace and path. All widgets are rendered via blade components');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('canonical')
                  ->unique();

            $table->text('description')
                  ->nullable();

            $table->string('view_component_namespace')
                  ->default('survey')
                  ->comment('The view component name that will encapsulate the widget(s) namespaces. Normally will the components inside components/pages/...');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('page_instances', function (Blueprint $table) {
            $table->id();

            $table->char('uuid', 36)
                  ->unique()
                  ->nullable();

            $table->string('name')
                  ->comment('What is this page instance about?');

            $table->foreignId('page_id')
                  ->comment('Related page type, to undertand what strucutre should be loaded. If null, then a view component override is needed');

            $table->foreignId('questionnaire_id')
                  ->comment('Related questionnaire id');

            $table->unsignedInteger('index')
                  ->comment('The page index in the respective related questionnaire, or to the group name');

            $table->string('group')
                  ->nullable()
                  ->comment('A group joins different pages to create sub-questionnaire pages, e.g.: when using group oneliners');

            $table->string('view_component_override')
                  ->nullable()
                  ->comment('If we have a specific view component, instead of using the ones from the page types');

            $table->json('data')
                  ->nullable()
                  ->comment('Any extra data we want to pass to the page instance');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('question_instances', function (Blueprint $table) {
            $table->id();

            $table->char('uuid', 36)
                  ->unique()
                  ->nullable();

            $table->foreignId('page_instance_id')
                  ->nullable()
                  ->comment('Related questionnaire page instance');

            $table->boolean('is_analytical')
                  ->default(true)
                  ->comment('If the question instance value will be used for reports. If it is not then it can be to display a message, or to capture custom information. E.g.: Input text to get a employee code');

            $table->boolean('is_used_for_personal_data')
                  ->default(false)
                  ->comment('Used to be only seen by GDPR profiles');

            $table->unsignedInteger('index')
                  ->comment('The question instance index in related page');

            $table->boolean('is_required')
                  ->default(false)
                  ->comment('If this question instance is required to be answered');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('responses', function (Blueprint $table) {
            $table->id();

            $table->string('session_instance_id')
                  ->comment('The visitor session instance id (aggregator) where this questionnaire was created');

            $table->foreignId('question_instance_id')
                  ->comment('Related question instance where this response was answered');

            $table->foreignId('widget_instance_id')
                  ->comment('Related widget instance where this response was answered');

            $table->json('value')
                  ->nullable()
                  ->comment('Value or values of the question instance');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('widget_instances', function (Blueprint $table) {
            $table->id();

            $table->char('uuid', 36)
                  ->unique()
                  ->nullable();

            $table->foreignId('question_instance_id')
                  ->nullable();

            $table->foreignId('widget_id')
                  ->nullable();

            $table->unsignedInteger('index')
                  ->nullable()
                  ->comment('The sequence of the widget instance in case it is a multi-widget instance question instance');

            $table->foreignId('widget_instance_id')
                  ->nullable()
                  ->comment('If it is a widget instance child (like a widget conditional)');

            /**
             * This is a javascript eval expression like:
             * 'widget.value <=2 or widget.value ==5'.
             * The value is the widget.value.
             * Each json entry is an OR clause.
             * Later there will be more types and options, like access
             * to other question instance values, widget values, etc.
             */
            $table->json('when')
                  ->nullable()
                  ->comment('Condition that will trigger the condition for the widget conditional to appear');

            /**
             * The available conditions at the moment are:
             * textarea-slidedown
             * subtext-appear
             * jump-to-page
             *
             * Then the respective value if needed. E.g.:
             * ["jump-to-page" => 2]
             * ["textarea-slidedown"]
             * ["subtext-appear" => WidgetPivot on the localables with
             *                      'variable_type' => 'subtext',
             *                      'variable_uuid' => uuid()]
             */
            $table->json('then')
                  ->nullable()
                  ->comment('Consequence of the conditional when it is triggered');

            $table->json('data')
                  ->nullable()
                  ->comment('The settings override for the widget instance data');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('locations', function (Blueprint $table) {
            $table->id();

            $table->string('name')
                  ->comment('Location name, like an Hotel name');

            $table->foreignId('client_id')
                  ->comment('Related client');

            $table->text('address')
                ->nullable()
                ->comment('The location address');

            $table->string('postal_code')
                ->nullable()
                ->comment('The location postal code');

            $table->string('city')
                  ->nullable()
                  ->comment('The location city');

            $table->foreignId('country_id')
                  ->comment('Related country');

            $table->string('latitude')
                  ->nullable()
                  ->comment('Location Address latitude');

            $table->string('longitude')
                  ->nullable()
                  ->comment('Location address longitude');

            $table->timestamps();
            $table->softDeletes();
        });

        /**
         * The foundation seeder populates de system tables with the
         * initial data that is needed to use qrfeedz.
         */
        Artisan::call('db:seed', [
            '--class' => 'QRFeedz\Database\Seeders\SchemaFoundationSeeder',
            '--force' => true,
            '--quiet' => 1,
        ]);
    }

    public function down()
    {
        //
    }
};

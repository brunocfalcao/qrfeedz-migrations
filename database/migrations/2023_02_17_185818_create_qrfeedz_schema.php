<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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

        Schema::create('affiliates', function (Blueprint $table) {
            $table->id();

            $table->string('name')
                  ->comment('Affiliate name');

            $table->unsignedInteger('commission_percentage')
                  ->default(50)
                  ->comment('Comission in percentage (0 to 100)');

            $table->text('address')
                ->nullable()
                ->comment('The affiliate address');

            $table->string('postal_code')
                  ->nullable()
                  ->comment('The affiliate postal code');

            $table->string('locality')
                  ->nullable()
                  ->comment('The affiliate locality');

            $table->foreignId('country_id')
                  ->comment('Affiliate country');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('clients', function (Blueprint $table) {
            $table->id();

            $table->string('name')
                  ->comment('The client name');

            $table->foreignId('affiliate_id')
                  ->nullable()
                  ->comment('Related affiliate, if exists');

            $table->foreignId('locale_id')
                  ->comment('Related default locale');

            $table->string('vat_number')
                  ->nullable()
                  ->comment('Client fiscal number');

            $table->text('address')
                ->nullable()
                ->comment('The client address');

            $table->string('postal_code')
                ->nullable()
                ->comment('The client postal code');

            $table->string('locality')
                  ->nullable()
                  ->comment('The client locality');

            $table->foreignId('country_id')
                  ->comment('Related country');

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

            $table->foreignId('affiliate_id')
                  ->nullable()
                  ->after('client_id')
                  ->comment('Related affiliate');

            $table->boolean('is_super_admin')
                  ->default(false)
                  ->after('affiliate_id')
                  ->comment('Has a super admin role?');

            $table->dropColumn('email_verified_at');

            $table->softDeletes();
        });

        Schema::create('groups', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_id')
                  ->nullable();

            $table->string('name')
                  ->comment('The group name, can be a specific location or a restaurant/hotel, etc');

            $table->text('description')
                  ->nullable()
                  ->comment('If necessary can have a bit more description context to understand what this group is');

            $table->json('data')
                  ->nullable()
                  ->comment('Additional data that identifies this group, like a brand, a restaurant, etc');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('questionnaires', function (Blueprint $table) {
            $table->id();

            $table->uuid()
                  ->unique()
                  ->nullable()
                  ->comment('This will be the unique questionnaire qr code that will be scanned by a client.');

            $table->foreignId('client_id')
                  ->nullable()
                  ->comment('Related client');

            $table->foreignId('locale_id')
                  ->nullable()
                  ->comment('The default locale for this questionnaire, in case a language is not selected');

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

            $table->dateTime('starts_at')
                  ->nullable()
                  ->comment('When is the questionnaire active, and ready to receive data');

            $table->dateTime('ends_at')
                  ->nullable()
                  ->comment('When will the questionnaire stop receiving data');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('group_questionnaire', function (Blueprint $table) {
            $table->id();

            $table->foreignId('group_id');
            $table->foreignId('questionnaire_id');

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

            $table->text('prompt_I_am_paying_attention_to')
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
         * Categories are joker attributes that are related to clients,
         * groups, questionnaires, etc. They can be created and used as
         * requested.
         */
        Schema::create('categories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_id')
                  ->nullable()
                  ->comment('Related client');

            $table->string('name');

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

        Schema::create('category_questionnaire', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id');
            $table->foreignId('questionnaire_id');

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
            $table->string('canonical');
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

            $table->foreignId('page_instance_id')
                  ->nullable()
                  ->comment('Related questionnaire page instance');

            $table->boolean('is_analytical')
                  ->default(true)
                  ->comment('If the question instance value will be used for reports. If it is not then it can be to display a message, or to capture custom information. E.g.: Input text to get a employee code');

            $table->boolean('is_used_for_personal_data')
                  ->default(false)
                  ->comment('Used to be only seen by GDPR profiles');

            $table->boolean('is_single_value')
                  ->default(true)
                  ->comment('Accepted values: single - Just returns one value (even from several widgets), multiple, returns all the values');

            $table->unsignedInteger('index')
                  ->default(1)
                  ->comment('The question instance index in related page');

            $table->boolean('is_required')
                  ->default(false)
                  ->comment('If this question instance is required to be answered');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('responses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('question_instance_id')
                  ->comment('Related question instance where this response was answered');

            $table->string('value')
                  ->nullable()
                  ->comment('The concluded response value');

            $table->json('values')
                  ->nullable()
                  ->comment('A possible subset of values that are part of a response, like a multiple checkbox widget');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('widget_instances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('question_instance_id');
            $table->foreignId('widget_id');

            $table->unsignedInteger('index')
                  ->default(1)
                  ->comment('The sequence of the widget instance in case it is a multi-widget instance question instance');

            $table->json('data')
                  ->nullable()
                  ->comment('The settings override for the widget instance data');

            $table->timestamps();
            $table->softDeletes();
        });

        /**
         * This table stores all the conditional types for a specific widget
         * instance vs the respective conditionals it will have.
         *
         * Examples:
         * If a stars rating <=2 then it slides down a textarea.
         * If an emoji rating = 3 then it shows sub-text "Right in the middle!".
         */
        Schema::create('widget_instance_conditionals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('widget_instance_id');

            /**
             * This is a javascript eval expression like:
             * 'widget.value <=2 or widget.value ==5'.
             * The value is the widget.value.
             * Each json entry is an OR clause.
             * Later there will be more types and options, like access
             * to other question instance values, widget values, etc.
             */
            $table->json('when')
                  ->comment('Conditional that will trigger the condition');

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
                  ->comment('Consequence of the conditional when it is triggered');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        //
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /**
         * Locales are used to define the caption language. By default, the
         * foundation seeder will render a group of locales. Later we can
         * add more if needed. The data model is dynamic enough.
         */
        Schema::create('locales', function (Blueprint $table) {
            $table->id();

            $table->string('code')
                  ->unique()
                  ->comment('Locale code, like pt, en, cn, etc');

            $table->string('name')
                  ->comment('The described locale name');

            $table->timestamps();
            $table->softDeletes();
        });

        /**
         * Authorizations are the heart of user security and data privacy.
         * They connect users with profiles, that allow them to "do"
         * things. By default there will be a group of pre-defined
         * authorizations. Authorizations are applied to 3 main
         * groups: clients, questionnaires or groups. Also
         * any authorization level will cascade to its
         * childs. Meaning, a "client"-admin will have
         * access to the child questionnaires and
         * child groups.
         *
         * Authorization types:
         * ADMIN - Admins all the information for the respective client. From
         * the own client, users, questionnaires, and groups.
         *
         * QUESTIONNAIRE-ADMIN - Admins all the information at the questionnaire
         * level, meaning manages questionnaires, and groups. Normally used
         * for users that can create new questionnaires under the same client.
         * The groups management are directly related with the questionnaire
         * admin role.
         *
         * GDPR - Special role that will allow the user to see personal data.
         * That is, if a question.is_used_for_personal_data = true, then
         * only users with this role will see that data value.
         *
         * AFFILIATE - Special role that is given to users that are considered
         * affiliates. An affiliate is someone that sells qrfeedz to others.
         * So, the affiliate will be like a "super-admin" (can create clients,
         * admin users and questionnaires) but with some limitations, like it
         * can't manage clients that are not related to its account.
         *
         * Authorizations are mostly used in the model policies where they
         * will reflect directly in the Nova admin, the backoffice and the
         * frontend, and also in the different data store actions.
         */
        Schema::create('authorizations', function (Blueprint $table) {
            $table->id();

            $table->string('name')
                  ->comment('The authorization name');

            $table->text('description')
                  ->nullable()
                  ->comment('Details on the authorization type');

            $table->timestamps();
            $table->softDeletes();
        });

        /**
         * A polymorphic many-to-many to relate clients, questionnaires
         * and groups, into authorization types, and respective users.
         */
        Schema::create('authorizables', function (Blueprint $table) {
            $table->id();

            $table->morphs('authorizable');
            $table->foreignId('authorization_id');
            $table->foreignId('user_id');

            $table->timestamps();
            $table->softDeletes();
        });

        /**
         * Countries are used in clients. Both respect the same values from
         * Laravel Nova, so we can use the country field type. Countries
         * are used for addresses fields (clients, groups, questionnaires).
         */
        Schema::create('countries', function (Blueprint $table) {
            $table->id();

            $table->string('code')
                  ->comment('Country code');

            $table->string('name')
                  ->comment('Country name');

            $table->timestamps();
            $table->softDeletes();
        });

        /**
         * Affiliates are sales persons that work for qrfeedz, and sell it
         * to clients. Each client created by an affiliate user will
         * automatically connect to the respective affiliate user.
         * Affiliate commissions will then be applied, on each revenue
         * cycle, giving a split % to the affiliate. Also the affiliate
         * can manage the client, create users for that client, and
         * create questionnaires. It's fully enpowered to start
         * new businesses for new clients.
         */
        Schema::create('affiliates', function (Blueprint $table) {
            $table->id();

            $table->string('name')
                  ->comment('Affiliate name');

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

        /**
         * Clients are the top most entity on the qrfeedz structure. A client
         * will cascade its data relations to questionnaires. A client can be
         * like a big company e.g.: Tavero, but can also be just
         * a single entity like a small restaurant. If the client
         * wants to group questionnaires it should use groups.
         */
        Schema::create('clients', function (Blueprint $table) {
            $table->id();

            $table->string('name')
                  ->comment('The client name');

            $table->text('address')
                  ->nullable()
                  ->comment('The client address');

            $table->string('postal_code')
                  ->nullable()
                  ->comment('The client postal code');

            $table->string('locality')
                  ->nullable()
                  ->comment('The client locality');

            $table->foreignId('affiliate_id')
                  ->nullable()
                  ->comment('Related affiliate, if exists');

            $table->foreignId('country_id')
                  ->comment('Client country');

            $table->foreignId('locale_id')
                  ->comment('The related default locale');

            $table->string('vat_number')
                  ->nullable()
                  ->comment('Client fiscal number');

            $table->timestamps();
            $table->softDeletes();
        });

        /**
         * The users table is extended to also have soft deletes to allow
         * users to be "deleted". Users are ALWAYS connected to clients
         * and their authorizations will cascade down to groups, and
         * questionnaires. As an example, if an user has "admin" at
         * a client level, then it will be admin in questionnaires
         * and it its groups. We always should apply a least-based
         * security (the user just have the minimum security level).
         */
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('client_id')
            ->after('id')
            ->nullable();

            $table->boolean('is_admin')
            ->after('client_id')
            ->default(false)
            ->comment('Super admin role');

            $table->boolean('is_affiliate')
            ->after('is_admin')
            ->default(false)
            ->comment('Affiliate super role');

            $table->string('phone_number')
            ->after('email')
            ->nullable();

            $table->dropColumn('email_verified_at');

            $table->softDeletes();
        });

        /**
         * Groups are, as the name says, abstract grouping entities to group
         * questionnaires. They are related to the client, and they have a
         * N-N relationship with questionnaires. Meaning we don't need to
         * create the same questionnaire if we want to relate it with
         * different groups. Take as example a qrcode for wine
         * bottles. The group will be the brand/model of the
         * wine bottle, the questionnaire will always be
         * the same (same id).
         */
        Schema::create('groups', function (Blueprint $table) {
            $table->id();

            $table->string('name')
            ->comment('The group name, can be a specific location or a restaurant/hotel, etc');

            $table->text('description')
            ->nullable()
            ->comment('If necessary can have a bit more description context to understand what this group is');

            $table->json('data')
            ->nullable()
            ->comment('Additional data that identifies this group, like a brand, a restaurant, etc');

            $table->foreignId('client_id')
            ->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        /**
         * Questionnaires are unique entry points for visitors to give their
         * feedback about something. A questionnaire is attached to a group,
         * and can have multiple questions versions attached to it.
         * Questionnaires can be enpowered with tags and categories
         * so it will be easier to see reports from another
         * perspective. The relationship between a group
         * and a questionnaire is 1-N meaning a group
         * can have multiple questionnaires attached
         * to it. The versioning of content is made
         * at the questions level.
         */
        Schema::create('questionnaires', function (Blueprint $table) {
            $table->id();

            $table->string('name')
                  ->nullable()
                  ->comment('Human name that the questionnaire is used for. E.g.: Terrace Summer 2021, used for the title html tag too');

            $table->text('description')
                  ->nullable()
                  ->comment('In case we want to describe a specific qr code instance for whatever reason');

            $table->foreignId('client_id')
                  ->nullable()
                  ->comment('Related client');

            $table->foreignId('group_id')
                  ->nullable()
                  ->comment('Related groups where the questionnaire will be used');

            $table->foreignId('locale_id')
                  ->comment('The default locale for this questionnaire');

            $table->string('image_filename')
                  ->nullable()
                  ->comment('Image logo, appears in the questionnaire header');

            $table->boolean('is_active')
                  ->default(true)
                  ->comment('Overrides the active dates. In case we want to immediate inactivate the questionnaire');

            $table->dateTime('starts_at')
                  ->nullable();

            $table->dateTime('ends_at')
                  ->nullable();

            $table->longText('logo_svg')
                  ->nullable()
                  ->comment('SVG code in case we want to have a header background with a pattern, image, etc. It will be all the css inside the bg-header { } class');

            $table->boolean('asks_for_email')
                  ->default(true)
                  ->comment('Will it ask for the visitors email at the end of the questionnaire');

            $table->uuid()
                  ->nullable()
                  ->comment('This will be the unique questionnaire qr code that will be scanned by a client.');

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
        Schema::create('openai_prompt_configurations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('questionnaire_id')
                  ->comment('Related questionnaire');

            $table->text('my_questionnaire_is_about')
                  ->nullable()
                  ->comment('OpenAI specific prompt text');

            $table->text('I_am_paying_attention_to')
                  ->nullable()
                  ->comment('OpenAI specific prompt text');

            $table->string('balance_type')
                  ->default('balanced')
                  ->comment('balanced, worst-cases, best-cases');

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

        Schema::create('taggables', function (Blueprint $table) {
            $table->id();

            $table->morphs('taggable');
            $table->foreignId('tag_id');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('localables', function (Blueprint $table) {
            $table->id();

            $table->morphs('localable');
            $table->foreignId('locale_id');

            $table->string('caption')
                  ->comment('The sentence in the respective locale');

            $table->string('variable')
                  ->nullable()
                  ->comment('Used from widgets in case we have several locales for the same widget id. If nullable then it is just used for default purposes (will be most of the time)');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('categorizables', function (Blueprint $table) {
            $table->id();

            $table->morphs('categorizable');
            $table->foreignId('category_id');

            $table->timestamps();
            $table->softDeletes();
        });

        /**
         * A widget is a view component that renders HTML where the visitor
         * will then give the answer to the question. Widgets are only
         * related with questions. See it as a widget library to create
         * questionnaires. The model between a questionnaire question
         * and a widget is the QuestionWidget model. It will related
         * both entities to create questions enriched with widgets.
         */
        Schema::create('widgets', function (Blueprint $table) {
            $table->id();

            $table->string('name')
                  ->comment('E.g: Textbox, 1 to N, etc');

            $table->text('description')
                  ->nullable()
                  ->comment('Extended description, validation options, integration details, etc');

            $table->string('canonical')
                  ->comment('Widget canonical, easier to find when relating with questions');

            $table->json('settings')
                  ->nullable()
                  ->comment('Widgets default settings, so it can be UI redered by default');

            $table->string('view_component_namespace')
                  ->comment('The view component namespace and path. All questions are rendered via blade components');

            $table->timestamps();
            $table->softDeletes();
        });

        /**
         * Questions are one of the topmost rich data entity. It will create
         * the foundation of questionnaires, by asking something to the
         * visitors. Questions are related with widgets (1 to many) and
         * they have special configurations for the answers types. Like
         * if the question is restricted GDPR, or if the question will
         * record a value that doesn't need to be reportable (used for
         * analytics).
         */
        Schema::create('questions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('questionnaire_id');

            $table->foreignId('locale_id')
                  ->nullable()
                  ->comment('The related caption locale values. If null then we dont render a label but only the widget');

            $table->boolean('is_analytical')
                  ->default(true)
                  ->comment('If the question value will be used for reports. If it is not then it can be to display a message, or to capture custom information. E.g.: Input text to get a employee code');

            $table->boolean('is_used_for_personal_data')
                  ->default(false)
                  ->comment('Used to be only seen by gdpr profiles');

            $table->boolean('is_single_value')
                  ->default(true)
                  ->comment('Accepted values: single - Just returns one value (even from several widgets), multiple, returns all the values');

            $table->unsignedInteger('page_index')
                  ->default(1)
                  ->comment('The questionnaire page number that this question will belong to. By default we just have one page, but we could have multiple too');

            $table->unsignedInteger('index')
                  ->default(1)
                  ->comment('The question index in the questionnaire. AKA sequence in the questionnaire. Can be automatically generated');

            $table->boolean('is_required')
                  ->default(false)
                  ->comment('If this question is required to be answered');

            $table->timestamps();
            $table->softDeletes();
        });

        /**
         * The response is the final data entity from the full qrfeedz
         * data chain. It stores the value that the visitor gave to a
         * specific question (composed of widgets).
         */
        Schema::create('responses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('question_id')
                  ->comment('Related question where this response was answered');

            $table->string('value')
                  ->nullable()
                  ->comment('The concluded response value');

            $table->timestamps();
            $table->softDeletes();
        });

        /**
         * This model relates questions with widgets in a N-N relationship.
         * It is not only a relation but a model itself, since its ID is
         * used for the polymorphic N-N with locales. Meaning a question
         * that has widgets will then have caption locales for the
         * question and related widgets.
         */
        Schema::create('question_widget', function (Blueprint $table) {
            $table->id();

            $table->foreignId('questionnaire_id');
            $table->foreignId('widget_id');

            $table->unsignedInteger('index')
                  ->default(1)
                  ->comment('The sequence of the widget in case it is a multi-widget question');

            $table->json('settings_data')
                  ->nullable()
                  ->comment('The settings override from the question-widget');

            $table->json('settings_conditionals')
                  ->nullable()
                  ->comment('The settings conditionals from the question-widget pair. Like if we want to extend a textarea if the value is < XX');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};

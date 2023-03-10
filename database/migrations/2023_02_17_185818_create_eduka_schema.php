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
         * The users table is extended to also have soft deletes to allow
         * users to be "deleted". Users are ALWAYS connected to clients
         * and their authorizations will cascade down to groups, and
         * questionnaires. Authorization cascading profiles:
         *
         * Two access types: READ and UPSERT.
         * The READ access is at it says: User can ONLY view data, and cannot
         * change, neither interact with anything. The data scope excepts the
         * emails of visitors. That one is specific for GDPR access.
         *
         * The UPSERT access is wider: It can give access to insert and to
         * update data. Some ground rules: If a questionnaire already has
         * questions on it, it cannot be updated on the questions configuration.
         * The user would need to create a new question version to attach it to
         * the questionnaire. The questionnaire instance will always display
         * the latest versions of each of the question, but for reporting will
         * always give the value of the respective version that the answer was
         * given to.
         *
         * Admin access specifically: DELETE and GPDR.
         *
         * The DELETE is very powerful, because it will actually be able to
         * delete groups, questionnaires, and clients. The delete is
         * always a soft delete still. The user will be able to delete a
         * questionnaire. If a questionnaire is deleted, all the data is
         * also deleted. The best is to disable, or close it with an
         * end date.
         */
        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::create('locales', function (Blueprint $table) {
            $table->id();

            $table->string('en')
                  ->nullable()
                  ->comment('The caption value on the respective locale');

            $table->string('fr')
                  ->nullable()
                  ->comment('The caption value on the respective locale');

            $table->string('it')
                  ->nullable()
                  ->comment('The caption value on the respective locale');

            $table->string('de')
                  ->nullable()
                  ->comment('The caption value on the respective locale');

            $table->string('pt')
                  ->nullable()
                  ->comment('The caption value on the respective locale');

            $table->string('cn')
                  ->nullable()
                  ->comment('The caption value on the respective locale');

            $table->foreignId('client_id')
                  ->comment('Related client id');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('authorizations', function (Blueprint $table) {
            $table->id();

            $table->string('name')
                  ->comment('The authorization type: READ, UPSERT, DELETE, GDPR, SYSADMIN');

            $table->timestamps();
            $table->softDeletes();
        });

        /**
         * Countries are used in clients. Both respect
         * the same values from Laravel Nova, so we can use the country
         * field type.
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
         * Clients are the top most entity on the qrfeedz structure.
         * A client will cascade its data relations to
         * questionnaires. A client can be like a big
         * company e.g.: Tavero, but can also be just
         * a single entity as a restaurant.
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

            $table->foreignId('country_id')
                  ->comment('Client country');

            $table->string('default_locale')
                  ->comment('The default locale: Can be one of the locale columns (en, de, it, pt, fr, cn)');

            $table->string('vat_number')
                  ->nullable()
                  ->comment('Client fiscal number');

            $table->timestamps();
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

            $table->string('default_locale')
                  ->default('en')
                  ->comment('The default localization for this questionnaire');

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
         * related with questions, and they can be versioned. A widget, if
         * not specified by the question, will always be rendered on the
         * latest version when the questionnaire is rendered to the
         * visitor. Still, the widget is related with the response
         * specifically (by the widget id and not by the group uuid).
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

            $table->uuid('group_uuid')
                  ->comment('Used to uniquely identify the widget group with the version.Automatically generated');

            $table->unsignedInteger('version')
                  ->default(1)
                  ->comment('We can have several versions of the same question widget, but we don\'t want to lose the connection to the previous version question instances.Automatically generated');

            $table->json('settings')
                  ->nullable()
                  ->comment('Widgets default settings. Can be overriden by the question_widget.settings column');

            $table->boolean('is_reportable')
                  ->default(true)
                  ->comment('If the widget will have data for reports. If it is not then it can be to display a message, or to capture custom information. E.g.: Input text to get a employee code');

            $table->string('view_component_namespace')
                  ->comment('The view component namespace and path. All questions are rendered via blade components');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['group_uuid', 'version']);
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('questionnaire_id');

            $table->foreignId('locale_id')
                  ->nullable()
                  ->comment('The related caption locale values. If null then we dont render a label but only the widget');

            $table->boolean('is_caption_visible')
                  ->default(true)
                  ->comment('In case we just want to show only widget caption(s) and not the question caption');

            $table->uuid('group_uuid')
                  ->nullable()
                  ->comment('A group uuid to group questions. Automatically generated');

            $table->unsignedInteger('version')
                  ->comment('We can have several versions of the same question. Still this needs to be used in edge cases');

            $table->unsignedInteger('index')
                  ->comment('The question index in the questionnaire. AKA sequence in the questionnaire. Can be automatically generated');

            $table->unsignedInteger('page_num')
                  ->default(1)
                  ->comment('The questionnaire page number that this question will belong to. By default we just have one page, but we could have multiple too');

            $table->uuid('widget_group_uuid')
                  ->nullable()
                  ->comment('Related widget group uuid. For new questionnaires, the widget is rendered in the last active version');

            $table->boolean('is_required')
                  ->default(false)
                  ->comment('If this question is required to be answered');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('widget_group_uuid')
                  ->references('group_uuid')
                  ->on('widgets');

            $table->index(['group_uuid', 'version']);
        });

        Schema::create('responses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('question_id')
                  ->comment('This is referenced to the exact question version that was answered for');

            $table->foreignId('widget_id')
                  ->comment('The relatable exact widget id that this question was answered for');

            $table->longText('data')
                  ->nullable()
                  ->comment('Answers need to be json-structured since it can be a complex answer type');

            $table->string('value')
                  ->nullable()
                  ->comment('This is the concluded value(s) from the data json structure');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('question_widget', function (Blueprint $table) {
            $table->id();

            $table->foreignId('questionnaire_id');
            $table->foreignId('widget_id');

            $table->foreignId('locale_id')
                  ->nullable()
                  ->comment('Related locale instance');

            $table->unsignedInteger('index')
                  ->default(1)
                  ->comment('The sequence of the index in the question, in case it is a multi-widget question');

            $table->json('settings_data')
                  ->nullable()
                  ->comment('The settings override from the question-widget pair. These are general settings');

            $table->json('settings_conditionals')
                  ->nullable()
                  ->comment('The settings conditionals from the question-widget pair. Like if we want to extend a textarea if the value is < XX');

            $table->json('settings_captions')
                  ->nullable()
                  ->comment('To be used in case this widget has multiple captions, like a yes-no one-liner radio button, for instance');

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

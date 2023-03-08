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
         * and their authorizations will cascade down to places, and
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
         * delete places, questionnaires, and clients. The delete is
         * always a soft delete still. The user will be able to delete a
         * questionnaire. If a questionnaire is deleted, all the data is
         * also deleted. The best is to disable, or close it with an
         * end date.
         */
        Schema::table('users', function (Blueprint $table) {
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
         * The authorization is given between a user, an authorization type
         * and a client/questionnaire or group/questionnaire. That's why we
         * need a many-to-many polymorphic relationship.
         */
        Schema::create('authorizables', function (Blueprint $table) {
            $table->id();

            $table->morphs('authorizable');
            $table->foreignId('authorization_id');

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
                  ->comment('The place name, can be a specific location or a restaurant/hotel, etc');

            $table->text('description')
                  ->nullable()
                  ->comment('If necessary can have a bit more description context to understand what this place is');

            $table->json('data')
                  ->nullable()
                  ->comment('Additional data that identifies this group, like a brand, a place, etc');

            $table->foreignId('client_id')
                  ->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        /**
         * Questionnaires are unique entry points for visitors to give their
         * feedback about something. A questionnaire is attached to a place,
         * and can have multiple questions versions attached to it.
         * Questionnaires can be enpowered with tags and categories
         * so it will be easier to see reports from another
         * perspective. The relationship between a place
         * and a questionnaire is 1-N meaning a place
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

            $table->foreignId('place_id')
                  ->comment('Related place where the questionnaire will be used');

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

            $table->string('theme_background_color')
                  ->default('#BABABA')
                  ->comment('That is the main questionnaire background color (not the form background!)');

            $table->string('theme_background_form_color')
                  ->default('#FFFFFF')
                  ->comment('That is the main questionnaire background color (not the form background!)');

            $table->string('theme_font_primary_color')
                  ->default('#000000')
                  ->comment('That is the main questionnaire font color, for the questions captions mostly, and marketing content');

            $table->string('theme_font_secondary_color')
                  ->default('#EEEEEE')
                  ->comment('That is the main questionnaire secondary color, for imput placeholders, and extra info content');

            $table->string('theme_font_warning_color')
                  ->default('#FC7736')
                  ->comment('That is when we want to alert the visitor because he/she made a mistake or forgot something');

            $table->longText('svg_header_css')
                  ->nullable()
                  ->comment('SVG code in case we want to have a header background with a pattern, image, etc. It will be all the css inside the bg-header { } class');

            $table->boolean('asks_for_email')
                  ->default(true)
                  ->comment('It will ask for an email at the end of the questionnaire');

            $table->uuid('qrcode')
                  ->comment('This will be the unique qr code that will be scanned by a client');

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
         * places, questionnaires, etc. They can be created and used as
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

            $table->boolean('is_reportable')
                  ->default(true)
                  ->comment('If the widget counts for reports or not. If it is not reportable means it is to display a message');

            $table->string('canonical')
                  ->comment('Widget canonical, easier to find when relating with questions');

            $table->uuid('group_uuid')
                  ->comment('Used to uniquely identify the widget with the version.Automatically generated');

            $table->unsignedInteger('version')
                  ->comment('We can have several versions of the same question widget, but we don\'t want to lose the connection to the previous version question instances.Automatically generated');

            $table->json('settings')
                  ->nullable()
                  ->comment('Widgets default settings. Can be overriden by the questions.settings_override column');

            $table->string('view_component_namespace')
                  ->comment('The view component namespace and path. All questions are rendered via blade components');

            $table->text('description')
                  ->nullable()
                  ->comment('Extended description, validation options, integration details, etc');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['group_uuid', 'version']);
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('questionnaire_id');

            $table->string('caption')
                  ->nullable()
                  ->comment('The question caption (appearing in the display) in the default questionnaires.default_locale. All other captions should be defined in the settings.locale (key=locale)');

            $table->boolean('is_caption_visible')
                  ->default(true)
                  ->comment('In case we just want to show only widget caption(s) and not the question caption');

            $table->json('caption_locales')
                  ->nullable()
                  ->comment('Additional captions in different locales. If a locale is missing it fallsback to the default questionnaire locale');

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
                  ->comment('Related widget group uuid. By default the widget will always be rendered in the latest version, still the answers are recorded in the exact widget id (not group uuid)');

            $table->boolean('is_required')
                  ->default(false)
                  ->comment('If this question is required to be answered');

            $table->json('settings_override')
                  ->nullable()
                  ->comment('These settings are copied from the widget, at the moment of the creation. Then we can override them to change the default configuration');

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

            $table->longText('data')
                  ->nullable()
                  ->comment('Answers need to be json-structured since it can be a complex answer type');

            $table->string('value')
                  ->nullable()
                  ->comment('This is the concluded value(s) from the data json structure');

            $table->foreignId('widget_id')
                  ->comment('The relatable exact widget id that this question was answered for');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('question_flows', function (Blueprint $table) {
            $table->id();

            $table->foreignId('question_id_parent');
            $table->foreignId('question_id_child');

            $table->json('conditions')
                  ->nullable()
                  ->comment('Conditions that will make the visitor progress to the child questionnaire. For the parent, it is a button=back click');

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

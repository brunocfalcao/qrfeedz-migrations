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
        Schema::table('users', function (Blueprint $table) {
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

        Schema::create('organizations', function (Blueprint $table) {
            $table->id();

            $table->string('name')
                  ->comment('The organization name');

            $table->text('address')
                  ->nullable()
                  ->comment('The organization address');

            $table->string('vat_number')
                  ->nullable()
                  ->comment('Organization fiscal number');

            $table->foreignId('country_id')
                  ->comment('Organization country');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('places', function (Blueprint $table) {
            $table->id();

            $table->string('name')
                  ->comment('The place name, can be a specific location or a restaurant/hotel, etc');

            $table->text('address')
                  ->nullable()
                  ->comment('The place address, but it might also be a specific zone inside a location');

            $table->foreignId('country_id')
                  ->comment('Place country. By default (observer) will show the related organization country');

            $table->text('description')
                  ->nullable()
                  ->comment('If necessary can have a bit more description context to understand what this place is');

            $table->foreignId('organization_id')
                  ->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('questionnaires', function (Blueprint $table) {
            $table->id();

            $table->text('description')
                  ->nullable()
                  ->comment('In case we want to describe a specific qr code instance for whatever reason');

            $table->uuid('uuid')
                  ->comment('This will be the unique qr code that will be scanned by a client');

            $table->boolean('is_inactive')
                  ->default(false)
                  ->comment('Overrides the active dates. In case we want to immediate inactivate the questionnaire');

            $table->dateTime('starts_at')
                  ->nullable();

            $table->dateTime('ends_at')
                  ->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            $table->text('description')
                  ->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

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

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('categorizables', function (Blueprint $table) {
            $table->id();

            $table->morphs('categorizables');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();

            $table->string('group_uuid')
                  ->comment('Represents grouped questions with the same uuid, since questions can have versions');

            $table->unsignedInteger('version')
                  ->comment('We can have several versions of the same question, but we don\'t want to lose the connection to the previous version question versions');

            $table->string('question')
                  ->comment('Question value to be presented to the visitor');

            $table->unsignedInteger('index')
                  ->comment('The question index in the questionnaire. AKA sequence in the questionnaire');

            $table->unsignedInteger('page_num')
                  ->default(1)
                  ->comment('The questionnaire page number that this question will belong to');

            $table->foreignId('widget_id')
                  ->nullable();

            $table->longText('settings')
                  ->nullable()
                  ->comment('These settings are copied from the widget, at the moment of the creation. Then we can override them to change the default configuration');

            $table->foreignId('questionnaire_id');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['group_uuid', 'version']);
        });

        Schema::create('widgets', function (Blueprint $table) {
            $table->id();

            $table->string('name')
                  ->comment('E.g: Textbox, 1 to N, etc');

            $table->uuid('group_uuid')
                  ->comment('Used to uniquely identify the widget with the version');

            $table->unsignedInteger('version')
                  ->comment('We can have several versions of the same question widget, but we don\'t want to lose the connection to the previous version question instances');

            $table->longText('settings')
                  ->nullable()
                  ->comment('Question additional configuration data to be sent to the UI');

            $table->string('view_component')
                  ->comment('The view component namespace and path. All questions are rendered via blade components');

            $table->text('description')
                  ->nullable()
                  ->comment('Extended description, validation options, integration details, etc');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['group_uuid', 'version']);
        });

        Schema::create('responses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('question_id')
                  ->comment('This is referenced to the exact question version that was answered for');

            $table->foreignId('place_id');

            $table->longText('data')
                  ->nullable()
                  ->comment('Answers need to be json-structured since it can be a complex answer type');

            $table->string('value')
                  ->nullable()
                  ->comment('This is the value that is used for reporting in a certain way. It is a human value computed from the json_value column');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('place_questionnaire', function (Blueprint $table) {
            $table->id();

            $table->foreignId('questionnaire_id');
            $table->foreignId('place_id');

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

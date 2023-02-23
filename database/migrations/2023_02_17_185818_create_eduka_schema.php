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

            $table->morph('taggable');
            $table->foreignId('organization_id');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('categorizables', function (Blueprint $table) {
            $table->id();

            $table->morph('categorizables');
            $table->foreignId('organization_id');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();

            $table->string('name')
                  ->comment('E.g: A Textbox');

            $table->string('canonical')
                  ->comment('E.g.: textbox, checkbox, etc');

            $table->longText('settings')
                  ->nullable()
                  ->comment('Question additional configuration data to be sent to the UI');

            $table->string('view_component')
                  ->comment('The view component namespace and path. All questions are rendered via blade components');

            $table->unsignedInteger('version')
                  ->comment('We can have several versions of the same question widget, but we don\'t want to lose the connection to the previous version question instances');

            $table->text('description')
                  ->nullable()
                  ->comment('Extended description, validation options, integration details, etc');

            $table->uuid('uuid')
                  ->nullable()
                  ->comment('The uuid is what is actually used to uniquely identify the component in the UI (still, can have several instances in the UI)');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('responses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('question_id');
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

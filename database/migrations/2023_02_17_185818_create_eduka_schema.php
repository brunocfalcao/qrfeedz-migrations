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
        Schema::create('application_log', function (Blueprint $table) {
            $table->id();

            $table->string('session_id')
                  ->nullable();

            $table->unsignedBigInteger('causable_id')
                  ->nullable();

            $table->string('causable_type')
                  ->comment('The causable can be a visitor id (if not contexted as user) or an user id')
                  ->nullable();

            $table->unsignedBigInteger('relatable_id')
                  ->nullable();

            $table->string('relatable_type')
                  ->comment('The relatable can be any model instance that we would like to relate')
                  ->nullable();

            $table->string('group')
                  ->nullable()
                  ->comment('A process label code that will allow to group loggings');

            $table->string('description')
                  ->nullable()
                  ->comment('A natural description of the activity');

            $table->longText('properties')
                  ->nullable();

            $table->timestamps();
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};

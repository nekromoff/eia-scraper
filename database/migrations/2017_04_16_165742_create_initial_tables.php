<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInitialTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id')->references('id')->on('companies');
            $table->unsignedInteger('institution_id')->references('id')->on('institutions');
            $table->unsignedInteger('stakeholder_id')->references('id')->on('stakeholders');
            $table->text('name');
            $table->text('url');
            $table->string('act');
            $table->string('type');
            $table->text('activity');
            $table->text('espoo')->nullable();
            $table->text('description')->nullable();
            $table->string('status');
            $table->string('hash')->index();
            $table->timestamps();
        });

        Schema::create('companies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('ico');
            $table->timestamps();
        });

        Schema::create('institutions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('stakeholders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('project_id')->references('id')->on('projects');
            $table->string('name');
            $table->string('mimefiletype');
            $table->string('url');
            $table->timestamps();
        });

        Schema::create('watchers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->index();
            $table->string('search');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('projects');
        Schema::drop('companies');
        Schema::drop('institutions');
        Schema::drop('stakeholders');
        Schema::drop('documents');
        Schema::drop('watchers');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectRelations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects_regions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('project_id')->references('id')->on('projects')->index();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('projects_districts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('project_id')->references('id')->on('projects')->index();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('projects_localities', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('project_id')->references('id')->on('projects')->index();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('projects_institutions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('project_id')->references('id')->on('projects')->index();
            $table->unsignedInteger('institution_id')->references('id')->on('institutions')->index();
            $table->enum('type', ['primary', 'secondary'])->index();
            $table->timestamps();
        });

        Schema::create('projects_stakeholders', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('project_id')->references('id')->on('projects')->index();
            $table->unsignedInteger('stakeholder_id')->references('id')->on('stakeholders')->index();
            $table->enum('type', ['primary', 'secondary'])->index();
            $table->timestamps();
        });

        Schema::create('projects_companies', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('project_id')->references('id')->on('projects')->index();
            $table->unsignedInteger('company_id')->references('id')->on('companies')->index();
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
        Schema::drop('projects_regions');
        Schema::drop('projects_districts');
        Schema::drop('projects_localities');
        Schema::drop('projects_institutions');
        Schema::drop('projects_stakeholders');
        Schema::drop('projects_companies');
    }
}

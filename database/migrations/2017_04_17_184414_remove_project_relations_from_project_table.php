<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveProjectRelationsFromProjectTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('company_id');
            $table->dropColumn('institution_id');
            $table->dropColumn('stakeholder_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->unsignedInteger('company_id')->after('id')->references('id')->on('companies');
            $table->unsignedInteger('institution_id')->after('company_id')->references('id')->on('institutions');
            $table->unsignedInteger('stakeholder_id')->after('institution_id')->references('id')->on('stakeholders');
        });
    }
}

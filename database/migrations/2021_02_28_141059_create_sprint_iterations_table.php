<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSprintIterationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sprint_iterations', function (Blueprint $table) {
            $table->id();
            $table->integer('project_id')->unsigned();
            $table->integer('sprint_duration')->unsigned()->nullable()->comment('Sprint duration measured in weeks been 2,3 or 4 the possible values');
            $table->integer('sprint_start_weekday')->unsigned()->nullable()->comment('Sprint starting week day 0 for Sunday, 1 for Monday, etc');
            $table->string('sprint_prefix')->nullable()->comment('Sprint prefix');
            $table->integer('iteration_status')->unsigned()->default(0);
            $table->date('next_iteration_start_date')->nullable()->default(null);
            $table->string('iteration_user_assembla_id')->nullable();
            $table->integer('iterations_count')->unsigned()->default(0)->comment('used to track the amount if iterations done');
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
        Schema::dropIfExists('sprint_iterations');
    }
}

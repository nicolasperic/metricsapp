<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('project_id');
            $table->timestamp('from_date');
            $table->timestamp('to_date');
            $table->unsignedInteger('range_type');
            $table->unsignedInteger('year')->default(0);
            $table->unsignedInteger('month')->default(0);
            $table->unsignedInteger('week')->nullable(true);
            $table->unsignedFloat('worked_hours')->nullable(true);
            $table->unsignedInteger('total_tasks')->nullable(true);
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
        Schema::table('project_stats', function (Blueprint $table) {
            //
        });
    }
}

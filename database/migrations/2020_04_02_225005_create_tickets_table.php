<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('project_id');
            $table->string('name');
            $table->unsignedInteger('number');
            $table->string('status');
            $table->boolean('state')->default(1);
            $table->string('ticket_assembla_id')->nullable();
            $table->boolean('is_story');
            $table->integer('story_points')->nullable();
            $table->string('type')->nullable();
            $table->unsignedFloat('total_invested_hours')->default(0);
            $table->unsignedFloat('worked_hours')->default(0);
            $table->datetime('started_at')->nullable();
            $table->datetime('completed_at')->nullable();
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
        Schema::dropIfExists('tickets');
    }
}

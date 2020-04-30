<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketTimesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ticket_times', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->float('hours');
            $table->dateTime('begin_at');
            $table->dateTime('end_at');
            $table->unsignedInteger('ticket_number');
            $table->string('ticket_assembla_id');
            $table->string('project_assembla_id');
            $table->string('user_assembla_id');
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
        Schema::dropIfExists('ticket_times');
    }
}

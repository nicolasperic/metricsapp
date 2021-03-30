<?php

use App\Project;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('wikiname')->nullable();
            $table->string('code');
            $table->string('project_assembla_id')->nullable();
            $table->boolean('status')->default(1);
            $table->boolean('shared')->default(0);
            $table->integer('estimate_type')->unsigned()->default(Project::ESTIMATE_POINTS);
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
        Schema::dropIfExists('projects');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRatingsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('slack_log', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('channel');
            $table->text('message');
            $table->tinyInteger('q');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('slack_log');
    }
}

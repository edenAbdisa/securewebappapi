<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeedbackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->text('file')->nullable(); 
            $table->string('comments', 255)->nullable();
            $table->string('status', 30)->nullable();            
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->bigInteger('address_id')->unsigned()->nullable();
            
            $table->bigInteger('feedback_types_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('address_id')->references('id')->on('addresses');
            
            $table->foreign('feedback_types_id')->references('id')->on('feedback_types');
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
        Schema::dropIfExists('feedbacks');
    }
}

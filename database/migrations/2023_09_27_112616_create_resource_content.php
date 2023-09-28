<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResourceContent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('resource_content', function (Blueprint $table) {
            $table->id('content_id'); // Primary key using an auto-increment integer field
            $table->string('category'); // VARCHAR field for question
            $table->string('question', 1024); // VARCHAR field for question
            $table->string('answer', 65535); // VARCHAR field for answer
            $table->string('answer_file'); // VARCHAR field for answer
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
        Schema::dropIfExists('resource_content');
    }
}

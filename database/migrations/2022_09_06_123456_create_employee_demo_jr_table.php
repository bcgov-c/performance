<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeDemoJrTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_demo_jr', function (Blueprint $table) {
            $table->id();
            $table->string('guid');
            $table->string('last_employee_status')->nullable();
            $table->string('current_employee_status')->nullable();
            $table->date('last_conversation_date')->nullable();
            $table->date('next_conversation_date')->nullable();
            $table->string('due_date_paused')->nullable();
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
        Schema::dropIfExists('employee_demo_jr');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobDataAuditTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('job_data_audit', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('job_sched_id')->constrained('job_sched_audit');
            $table->longText('old_values');
            $table->longText('new_values');
            $table->timestamps();
            $table->index('job_sched_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_data_audit');
    }
}

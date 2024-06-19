<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccessOrganizationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('access_organizations', function (Blueprint $table) {
            $table->id();
            $table->string('organization', 100);
            $table->char('allow_login', 1)->default('N');
            $table->char('allow_inapp_msg', 1)->default('N');
            $table->char('allow_email_msg', 1)->default('N');
            $table->bigInteger('created_by_id')->nullable();
            $table->bigInteger('updated_by_id')->nullable();
            $table->timestamps();

            $table->unique(['organization']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('access_organizations');
    }
}

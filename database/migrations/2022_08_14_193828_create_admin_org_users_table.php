<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminOrgUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_org_users', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('granted_to_id');
            $table->bigInteger('allowed_user_id');
            $table->integer('access_type')->nullable()->default(0);
            
            $table->bigInteger('admin_org_id')->nullable;
            $table->bigInteger('shared_profile_id')->nullable;

            $table->timestamps();

            $table->index(['granted_to_id', 'allowed_user_id', 'access_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_org_users');
    }
}

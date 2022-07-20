<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKeycloakFieldsInUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            //
            
            $table->string('identity_provider')->nullable()->after('email');
            $table->string('keycloak_id')->nullable()->after('identity_provider');
            $table->string('idir_email_addr', 100)->nullable()->after('keycloak_id');
            $table->string('source_type',5)->nullable()->after('password');
            $table->string('idir', 50)->nullable()->after('guid');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->dropColumn('identity_provider');
            $table->dropColumn('keycloak_id');
            $table->dropColumn('idir_email_addr');
            $table->dropColumn('source_type');
            $table->dropColumn('idir');
        });
    }
}

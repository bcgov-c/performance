<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateServiceRepresentativeRole extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('roles')->insert([
            'name' => 'Service Representative',
            'longname' => 'Service Representative',
            'created_at' => now(),
            'updated_at' => now()
        ]);


        DB::table('permissions')->insert([
            'name' => 'service representative',
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('role_has_permissions')->insert([
            'permission_id' => '7',
            'role_id' => '5'
        ]);



    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //Schema::dropIfExists('service_representative_role');
    }
}

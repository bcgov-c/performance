<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreatedByInEmployeeSharesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employee_shares', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->after('reason');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee_shares', function (Blueprint $table) {
            if(Schema::hasColumn('employee_shares', 'created_by')) {
                $table->dropColumn('created_by');
            }
            if(Schema::hasColumn('employee_shares', 'updated_by')) {
                $table->dropColumn('updated_by');
            }
        });
    }
}

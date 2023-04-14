<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminOrgTreeView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        DB::statement('DROP VIEW IF EXISTS admin_org_tree_view');
        
        \DB::statement("
            CREATE VIEW admin_org_tree_view
            AS
            SELECT
                vwao.user_id,
                vwao.version,
                vwao.inherited,
                vwao.orgid,
                vwedt.level,
                vwedt.name,
                vwedt.organization,
                vwedt.level1_program,
                vwedt.level2_division,
                vwedt.level3_branch,
                vwedt.level4,
                vwedt.level5,
                vwedt.organization_key,
                vwedt.level1_key,
                vwedt.level2_key,
                vwedt.level3_key,
                vwedt.level4_key,
                vwedt.level5_key
            FROM 
                admin_orgs 
                    AS vwao 
                INNER JOIN employee_demo_tree 
                    AS vwedt
                    ON vwedt.id = vwao.orgid
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::statement("
            DROP VIEW admin_org_tree_view
        ");
    }


}

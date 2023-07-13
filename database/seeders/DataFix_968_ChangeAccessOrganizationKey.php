<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AccessOrganization;
use App\Models\EmployeeDemoTree;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;



class DataFix_968_ChangeAccessOrganizationKey extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        Schema::table('access_organizations', function (Blueprint $table) {
            $table->unsignedBigInteger('orgid')->after('id');
            echo Carbon::now()->format('c').' - orgid added in access_organizations.'; echo "\r\n";
        });

        $nextOrgId = 1000001;
        $result = AccessOrganization::where('orgid', '>', 1000000)->select(\DB::raw('max(orgid) AS maxorgid'))->first();
        if($result->maxorgid){
            echo Carbon::now()->format('c').' - max(orgid) = '.$result->maxorgid; echo "\r\n";
            $nextOrgId = $result->maxorgid + 1;
        }

        $orgs = AccessOrganization::all();
        foreach($orgs AS $org){
            $tree = null;
            $tree = EmployeeDemoTree::where('level', 0)
                ->where('name', $org->organization)
                ->first();
            if($tree){
                $org->orgid = $tree->id;
                $org->save();
                echo Carbon::now()->format('c').' - '.$org->orgid.' assigned to '.$org->organization.'.'; echo "\r\n";
            } else {
                if(!$org->orgid){
                    $org->orgid = $nextOrgId;
                    $org->save();
                    $nextOrgId++;
                    echo Carbon::now()->format('c').' - '.$org->orgid.' assigned to '.$org->organization.'.'; echo "\r\n";
                }
            }
        }

        Schema::table('access_organizations', function (Blueprint $table) {
            $table->dropUnique(['organization']);
            echo Carbon::now()->format('c').' - organization changed from unique field.'; echo "\r\n";
            $table->string('organization')->nullable()->change();
            echo Carbon::now()->format('c').' - organization changed to nullable field.'; echo "\r\n";
            $table->unique(['orgid']);
            echo Carbon::now()->format('c').' - orgid changed to unique field.'; echo "\r\n";
        });

    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AccessOrganization;

class AccessOrganizationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $orgnizations = [
            [
                'name' => 'BC Public Service Agency',
            ],
            [
                'name' => 'Royal BC Museum',
            ],
            [
                'name' => 'Social Development and Poverty Reduction',
            ]
        ];

        foreach ($orgnizations as $organization) {
            AccessOrganization::updateOrCreate([
                'organization' => $organization['name'],
            ],[ 
                'allow_login' => 'Y',
                'allow_inapp_msg' => 'Y',
                'allow_email_msg' => 'Y',
            ] );
        }
    }
}

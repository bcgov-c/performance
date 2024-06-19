<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;


class DataFix_755_InvalidGUID extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::where('email','Hardeep.Chhabra@gov.bc.ca')->where('guid','B18144010ED141FE936EFF08985C6911')->update(['guid' => '15851751DD12418DB49A432C6B15821C']);
        User::where('email','Sonia.Nijjar@gov.bc.ca')->where('guid','74DA6363E9FE4D2396A6497E9B5D70D0')->update(['guid' => 'DBA24A36E1F8474AB74A5E24C4DD0E34']);
    }
}

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
        User::where('email','hardeep.chhabra@gov.bc.ca')->where('guid','B18144010ED141FE936EFF08985C6911')->update(['guid' => '15851751DD12418DB49A432C6B15821C']);
    }
}

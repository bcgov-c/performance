<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;


class DataFix_842_InvalidGUID extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::where('email','Cheri.Maisonneuve@gov.bc.ca')->where('guid','6278637AD6054D5DB829AC61F0EBD8D1')->update(['guid' => 'B77AFAA0F73B40B294B2F04BFE699BE7']);
        User::where('email','Gabrielle.Faludi@gov.bc.ca')->where('guid','388171C8EA1C4F8EA3D95BE96EA24CC3')->update(['guid' => 'D10BD88074B140E0B6282E284879A933']);
    }
}

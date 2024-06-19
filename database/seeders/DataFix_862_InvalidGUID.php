<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;


class DataFix_862_InvalidGUID extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::where('email','Angela.Sosnoski@gov.bc.ca')->where('guid','C1C338D34E9442AA9933CB3B58167301')->update(['guid' => '8355D37ADB8E4785B6F9CA02A5C80B56']);
        User::where('email','Michelle.Boyer@gov.bc.ca')->where('guid','8A3E1848707E4D6684E554D05AD03454')->update(['guid' => '5D7ED1C893C743D498A05B03D85C7748']);
        User::where('email','Joan.Kathol@gov.bc.ca')->where('guid','B19C0D24A71F4BD3890D0C044D4E8C14')->update(['guid' => 'DF1C81559D17432B9E326011D5D68CFD']);
        User::where('email','Chantal.Vercide@gov.bc.ca')->where('guid','A8211CC92AAB441FAF316AF3B57BE458')->update(['guid' => '08EAAB12F577457E9A53A29BE31CEA2B']);
    }
}

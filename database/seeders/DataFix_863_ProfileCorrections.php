<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Goal;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class DataFix_863_ProfileCorrections extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Disable older profile
        User::whereRaw("users.id IN (10328,11011,11397,11829,13359,17742,18630,19718,20199,21620,22001,22521,22965,23959,24123,24370,24499,25639,26078,26352,49574,26950,27006,50011,27734,27748,28030,28133,28368,28437,48240,28896,28925,29993,30007,30304,30349,30533,31051,31371,32259,32500,32778,32838,34002,34166,35184,35201,35602,48287,35786,35964,35983,36422,36429,36438,37000,37109,37172,37479,37684,38305,38658,38729,39088,39158,39840,39936,40105,40165,41733,41995,42061,42878,43567,43887,43905,43986,44210,44492,44513,44658,44748,44874,45131,45207,49167,48348,45801,45874,48870,50360,48153,46782,47291,47477,47535,47624,47742,49056,49075,51045)")
        ->whereRaw("NOT employee_id LIKE '%locked-by-863-%'")
        ->update([
            'guid' => DB::raw("CONCAT('locked-by-863-', guid)"),
            'employee_id' => DB::raw("CONCAT('locked-by-863-', employee_id)"),
            'email' => DB::raw("CONCAT('locked-by-863-', email)"),
            'acctlock' => DB::raw('1'),
        ]);

        //Update Goals
        Goal::whereRaw('id in (1715,1716,1717,1718,1719)')
        ->update([
            'user_id' => DB::raw("49775"),
        ]);
        Goal::whereRaw('id in (12616)')
        ->update([
            'user_id' => DB::raw("49005"),
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StoredDate;


class DataFix_746_ResetLastODSPull extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        StoredDate::where('id',1)->update(['value' => null]);
    }
}

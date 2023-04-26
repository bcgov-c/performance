<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Position;
use App\Models\PositionStaging;
use App\Models\JobSchedAudit;
use App\Models\JobDataAudit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class GetODSPositions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:GetODSPositions {--manual}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $start_time = Carbon::now()->format('c');
        $this->info( 'Positions Data pull from ODS, Started: '. $start_time);

        $job_name = 'command:GetODSPositions';
        $switch = strtolower(env('PRCS_PULL_ODS_POSITIONS'));
        $manualoverride = (strtolower($this->option('manual')) ? true : false);
        $status = (($switch == 'on' || $manualoverride) ? 'Initiated' : 'Disabled');
        $audit_id = JobSchedAudit::insertGetId(
            [
            'job_name' => $job_name,
            'start_time' => date('Y-m-d H:i:s', strtotime($start_time)),
            'status' => $status
            ]
        );

        if ($switch == 'on' || $manualoverride) {

            $top = 10000;
            $skip = 0;
            $positionData = Http::acceptJson()
            ->withHeaders(['Content-Type' => 'application/x-www-form-urlencoded'])
            ->withBasicAuth(env('ODS_DEMO_CLIENT_ID'),env('ODS_DEMO_CLIENT_SECRET'))
            ->withOptions(['query' => [
                '$top' => $top, 
                '$skip' => $skip, 
                // Filter below to shorten test run
                // '$filter' => "position_nbr lt '00001'",
                '$orderby' => 'position_nbr',
            ] ])
            ->get( env('ODS_POSITIONS_URI') . '?$top=' . $top . '&$skip=' . $skip );
            $data = $positionData['value'];
            $total = 0;

            PositionStaging::truncate();

            do {

                $total += count($data);
                $this->info( 'Staging => $top = ' . $top . ' : $skip = ' . $skip . ' : $data = ' . count($data) . ' : Count = ' . $total);

                foreach($data as $item){
                    PositionStaging::updateOrCreate([
                        'position_nbr' => $item['position_nbr']
                    ],[
                        'descr' => $item['descr'],
                        'descrshort' => $item['descrshort'],
                        'reports_to' => $item['reports_to']
                    ]);
                };

                $skip += $top;

                $positionData = Http::acceptJson()
                ->withHeaders(['Content-Type' => 'application/x-www-form-urlencoded'])
                ->withBasicAuth(env('ODS_DEMO_CLIENT_ID'),env('ODS_DEMO_CLIENT_SECRET'))
                ->withOptions(['query' => [
                    '$top' => $top, 
                    '$skip' => $skip, 
                    // Filter below to shorten test run
                    // '$filter' => "position_nbr lt '00001'",
                    '$orderby' => 'position_nbr'
                ] ])
                ->get( env('ODS_POSITIONS_URI') . '?$top=' . $top . '&$skip=' . $skip );
                $data = $positionData['value'];
        
            } while(count($data)!=0);

            $stagingCount = PositionStaging::count();

            $count_insert = 0;
            $count_update = 0;
            $count_delete = 0;

            if ($stagingCount > 0) {
                $insertRows = PositionStaging::select('position_nbr', 'descr', 'descrshort', 'reports_to')
                ->whereNotExists(function ($q) {
                    $q->select(\DB::raw(1))
                    ->from('positions')
                    ->whereRaw('positions_stg.position_nbr = positions.position_nbr');
                })
                ->orderBy('position_nbr')
                ->get();
                foreach($insertRows as $ins){
                    \DB::beginTransaction();
                    try {
                        Position::create([
                            'position_nbr' => $ins['position_nbr'],
                            'descr' => $ins['descr'],
                            'descrshort' => $ins['descrshort'],
                            'reports_to' => $ins['reports_to'],
                            'date_deleted' => NULL
                        ]);
                        // $old_values = [ 
                        //     'table' => 'positions'                        
                        // ];
                        // $new_values = [ 
                        //     'table' => 'positions', 
                        //     'position_nbr' => $ins->position_nbr, 
                        //     'descr' => $ins->descr, 
                        //     'descrshort' => $ins->descrshort, 
                        //     'reports_to' => $ins->reports_to, 
                        //     'date_deleted' => NULL
                        // ];
                        // $audit = new JobDataAudit;
                        // $audit->job_sched_id = $audit_id;
                        // $audit->old_values = json_encode($old_values);
                        // $audit->new_values = json_encode($new_values);
                        // $audit->save();
                        DB::commit();
                        $count_insert += 1;
                    } catch (Exception $e) {
                        echo 'Unable to insert for Posn # '.$ins->position_nbr.'.'; echo "\r\n";
                        \DB::rollback();
                    }
                }

                $updateRows = PositionStaging::select('position_nbr', 'descr', 'descrshort', 'reports_to')
                ->whereExists(function ($q) {
                    $q->select(\DB::raw(1))
                    ->from('positions')
                    ->whereRaw('positions_stg.position_nbr = positions.position_nbr AND (positions_stg.descr <> positions.descr OR positions_stg.descrshort <> positions.descrshort OR positions_stg.reports_to <> positions.reports_to OR NOT positions.date_deleted IS NULL)');
                })
                ->orderBy('position_nbr')
                ->get();
                foreach($updateRows as $upd){
                    \DB::beginTransaction();
                    try {
                        $row = DB::table('positions')
                        ->whereRaw("position_nbr = '".$upd->position_nbr."'")
                        ->first();
                        // $old_values = [ 
                        //     'table' => 'positions',                        
                        //     'position_nbr' => $row->position_nbr, 
                        //     'descr' => $row->descr, 
                        //     'descrshort' => $row->descrshort, 
                        //     'reports_to' => $row->reports_to, 
                        //     'date_deleted' => $row->date_deleted
                        // ];
                        // $new_values = [ 
                        //     'table' => 'positions', 
                        //     'position_nbr' => $upd->position_nbr, 
                        //     'descr' => $upd->descr, 
                        //     'descrshort' => $upd->descrshort, 
                        //     'reports_to' => $upd->reports_to, 
                        //     'date_deleted' => NULL
                        // ];
                        Position::whereRaw("position_nbr = '".$upd->position_nbr."'")
                        ->update([
                            'descr' => $upd->descr,
                            'descrshort' => $upd->descrshort,
                            'reports_to' => $upd->reports_to,
                            'date_deleted' => NULL
                        ]);
                        // $audit = new JobDataAudit;
                        // $audit->job_sched_id = $audit_id;
                        // $audit->old_values = json_encode($old_values);
                        // $audit->new_values = json_encode($new_values);
                        // $audit->save();
                        DB::commit();
                        $count_update += 1;
                    } catch (Exception $e) {
                        echo 'Unable to update for Posn # '.$upd->position_nbr.'.'; echo "\r\n";
                        DB::rollback();
                    }
                }

                $deletedRows = Position::select('position_nbr')
                ->whereNotExists(function ($q) {
                    $q->select(\DB::raw(1))
                    ->from('positions_stg')
                    ->whereRaw('positions_stg.position_nbr = positions.position_nbr');
                })
                ->whereRaw('date_deleted IS NULL')
                ->orderBy('position_nbr')
                ->get();
                foreach($deletedRows as $del){
                    $now = Carbon::now();
                    \DB::beginTransaction();
                    try {
                        $row = Position::whereRaw("position_nbr = '".$del->position_nbr."'")->first();
                        // $old_values = [ 
                        //     'table' => 'positions',                        
                        //     'position_nbr' => $row->position_nbr, 
                        //     'date_deleted' => $row->date_deleted
                        // ];
                        // $new_values = [ 
                        //     'table' => 'positions', 
                        //     'position_nbr' => $del->position_nbr, 
                        //     'date_deleted' => date('Y-m-d H:i:s', strtotime($now))
                        // ];
                        Position::whereRaw("position_nbr = '".$del->position_nbr."'")
                        ->update([
                            'date_deleted' => date('Y-m-d H:i:s', strtotime($now))
                        ]);
                        // $audit = new JobDataAudit;
                        // $audit->job_sched_id = $audit_id;
                        // $audit->old_values = json_encode($old_values);
                        // $audit->new_values = json_encode($new_values);
                        // $audit->save();
                        \DB::commit();
                        $count_delete += 1;
                    } catch (Exception $e) {
                        echo 'Unable to update (delete) for Posn # '.$del->position_nbr.'.'; echo "\r\n";
                        \DB::rollback();
                    }
                }
            }

            $end_time = Carbon::now();
            \DB::table('job_sched_audit')->updateOrInsert(
                [
                'id' => $audit_id
                ],
                [
                'job_name' => $job_name,
                'start_time' => date('Y-m-d H:i:s', strtotime($start_time)),
                'end_time' => date('Y-m-d H:i:s', strtotime($end_time)),
                'status' => 'Completed',
                'details' => 'Processed '.$total.' rows. Inserted '.$count_insert.' rows. Updated '.$count_update.' rows. Deleted '.$count_delete.' rows.',
                ]
            );

            $this->info( 'Positions Data pull from ODS, Completed: ' . $end_time);
        } else {
            $this->info( 'Process is currently disabled; or "PRCS_PULL_ODS_POSITIONS=on" is currently missing in the .env file.');
        }

    }
    
}

<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Models\JobSchedAudit;
use App\Models\JobDataAudit;
use App\Models\ShareProfile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class CleanShareProfile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:CleanShareProfile';

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
        $processname = 'CleanShareProfile';
        $DefaultCreatorName = 'System';
        $start_time = Carbon::now()->format('c');
        $this->info( $processname.', Started: '. $start_time);
        Log::info($start_time.' - '.$processname.' - Started.');
        $job_name = 'command:CleanShareProfile';
        $status = 'Initiated';
        $audit_id = JobSchedAudit::insertGetId(
          [
            'job_name' => $job_name,
            'start_time' => date('Y-m-d H:i:s', strtotime($start_time)),
            'status' => $status
          ]
        );
        //Process clean up
        $counter = 0;
                
        $share_list = DB::table('shared_profiles')->get();
        foreach ($share_list as $item) {
            $teammember_id = $item->shared_id;
            $supervisor_id = $item->shared_with;
            $user_reportings = User::select('reporting_to')->where('id','=', $teammember_id)->get();
            foreach($user_reportings as $reporting){
                if ($reporting->reporting_to == $supervisor_id) {
                    DB::beginTransaction();
                    try {
                            DB::table('shared_profiles')
                            ->where('shared_id', $teammember_id)    
                            ->where('shared_with', $supervisor_id)    
                            ->delete();

                            $old_values = [ 
                                'table' => 'shared_profiles',
                                'id' => $item->id,
                                'shared_id' => $item->shared_id, 
                                'shared_with' =>  $item->shared_with, 
                                'shared_item' =>  $item->shared_item, 
                                'comment' =>  $item->comment, 
                                'shared_by' =>  $item->shared_by, 
                            ];
                            $new_values = [];
                            $audit = new JobDataAudit;
                            $audit->job_sched_id = $audit_id;
                            $audit->old_values = json_encode($old_values);
                            $audit->new_values = json_encode($new_values);
                            $audit->save();
                            DB::commit();
                            $counter++;
                    } catch (Exception $e) {
                        echo 'Unable to clean shared profile ID '.$item->id.'.'; echo "\r\n";
                        DB::rollback();
                    }        
                }
            }
        
        }
        
        //echo 'Processed '.$counter.'.'; echo "\r\n";
        //$end_time = Carbon::now();
        //$this->info('CleanShareProfile, Completed: '.$end_time);
        //Log::info($end_time->format('c').' - '.$processname.' - Finished');
        
        echo 'Processed '.$counter.'.'; echo "\r\n";
        $end_time = Carbon::now();
        DB::table('job_sched_audit')->updateOrInsert(
            [
                'id' => $audit_id
            ],
            [
                'job_name' => $job_name,
                'start_time' => date('Y-m-d H:i:s',strtotime($start_time)),
                'end_time' => date('Y-m-d H:i:s',strtotime($end_time)),
                'status' => 'Completed',
                'details' => 'Processed '.$counter.' rows.',
            ]
        );
        $this->info('CleanShareProfile, Completed: '.$end_time);
        Log::info($end_time->format('c').' - '.$processname.' - Finished');
    } 
    
}

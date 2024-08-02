<?php

namespace App\Http\Controllers\Api;

use Exception;
use DateTimeZone;
use Carbon\Carbon;
use App\Models\Setting;
use Cron\CronExpression;
use App\Models\CampaignYear;
use Illuminate\Http\Request;
use App\Models\ScheduleJobAudit;
use Illuminate\Events\Dispatcher;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Console\Scheduling\Schedule;

class SystemStatusController extends Controller
{
    //
    public function queueStatus(Request $request) {

        // Run the command
        $cmd_result = shell_exec('ps -eF');

        $status = "@@@ Failure @@@ -- The background process queue is not functioning.";
        if (str_contains( strtolower($cmd_result), 'artisan queue:work')) {
            $status = "running";
        }

        // checking queue
        $queues = DB::table(config('queue.connections.database.table'))->get();

        $jobs = [];
        if($queues) {

            foreach($queues as $queue) {

                $t = Carbon::parse($queue->available_at);
                $t->setTimezone('America/Vancouver');

                // if queue up more than 5 minutes
                if ( (!($queue->reserved_at)) && Carbon::now()->diffInSeconds($t) > 300) {

                    $payload = json_decode($queue->payload, true);
                    array_push($jobs, "The background queue process ". $payload['displayName'] . " submitted on " . $t . " has been in the queue for more than 5 minutes.");
                    
                }
            }

        }

        $result = [
            'queue status' => $status,
            'now' => now()->format('Y-m-d H:i:s'),
            'jobs' => $jobs,
        ];

        if (str_contains($status, 'Failure')) {
            $result['server'] = shell_exec('cat /proc/sys/kernel/hostname');
        }

        return response()->json( $result, 200, [], JSON_PRETTY_PRINT);

    }

    public function databaseStatus(Request $request) 
    {

        $status = "running";
        $uptime = null;
        try {
            $setting = Setting::first();

            // Calculate UpTime
            $result = DB::select("SHOW GLOBAL STATUS LIKE 'Uptime'");
            $s = $result ? round($result[0]->Value) : null;
            $uptime = sprintf('%d day(s), %d hour(s), %d minute(s) and %d second(s)', $s/86400, round($s/3600) %24, round($s/60) %60, $s%60);

            if ($s < 300) {
                $status = "@@@ Failure @@@ -- The database was recently restarted, less than 5 minutes ago.";    
            }

        } catch (Exception $ex) {
            $status = "@@@ Failure @@@ -- " . $ex->getMessage();
        }       

        return response()->json([
            'database status' => $status,
            'up time' => $uptime,
            'now' => now()->format('Y-m-d H:i:s'),

        ], 200, [], JSON_PRETTY_PRINT);

    }

}

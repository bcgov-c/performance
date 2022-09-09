<?php

namespace App\Console\Commands;

use DateTime;
use DateTimeZone;
use Carbon\Carbon;
use App\Models\User;
use GuzzleHttp\Client;
use Microsoft\Graph\Graph;
use App\Models\Conversation;
use App\Models\NotificationLog;
use Illuminate\Console\Command;
use App\Models\DashboardNotification;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class NotificationProcess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:NotificationProcess';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trigger the event notification';
    
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

        $this->info( now() );
        $this->info("Notification -- Conversation Due (start)");
        $this->notifyConversationDue();
        $this->info( now() );
        $this->info("Notification -- Conversation Due (end)");

    }


    protected function notifyConversationDue() {

        $sent_count = 0;

        // Eligible Users (check against Allow Access Oragnizations)
        $users = User::join('employee_demo','employee_demo.guid','users.guid')
                        ->join('access_organizations','employee_demo.organization','access_organizations.organization')
                        ->where('access_organizations.allow_inapp_msg', 'Y')
                        ->whereNull('date_deleted')
                    ->groupBy('users.id')
                    ->select('users.*')
                    ->get();

        foreach ($users as $index => $user) {

            $due = Conversation::nextConversationDue( $user );

            $dueDate = \Carbon\Carbon::create($due);
            $now = Carbon::now();
            $dayDiff = $dueDate->diffInDays($now);

            $dueIndays = 0;
            $msg = '';
            if ($dayDiff > 7 and $dayDiff <= 30) {
                $msg = 'REMINDER - your next performance conversation is due in 1 month';
                $dueIndays = 30;
            }
            if ($dayDiff > 0 and $dayDiff <= 7) {
                $msg = 'REMINDER - your next performance conversation is due in 1 week';
                $dueIndays = 7;
            }
            if ($dayDiff <= 0) {  
                $msg = 'OVERDUE - your next performance conversation is past due';
                $dueIndays = 0;
            }

            if ($msg) {

                // check the notification sent or not 
                $log = NotificationLog::where('alert_type', 'N')
                                    ->where('alert_format', 'A')
                                    ->where('notify_user_id',  $user->id)
                                    ->where('notify_due_date', $dueDate->format('Y-m-d') )
                                    ->where('notify_for_days', $dueIndays)
                                    ->first();

                if (!$log) {
                    $this->info( $due . ' - ' . $user->id . ' - ' . $dueDate->format('Y-m-d') . ' ' . $dueIndays);
                    $sent_count += 1;


                    // DashBoard Message
                    DashboardNotification::create([
                        'user_id' => $user->id,
                        'notification_type' => '',        // Conversation Added
                        'comment' => $msg,
                        'related_id' => null,
                    ]);

                    // Write to Log table
                    $notification_log = NotificationLog::Create([  
                        'recipients' => ' ',        // Not in Use
                        'sender_id' => 0,           
                        'subject' => $msg,
                        'description' => '',
                        'alert_type' => 'N',
                        'alert_format' => 'A',
                        'notify_user_id' => $user->id,
                        'notify_due_date' => $dueDate->format('Y-m-d'),
                        'notify_for_days' => $dueIndays,
                        'template_id' => null,
                        'date_sent' => now(),
                    ]);

                }
            }

        }


        $this->info("Total selected users       : " . $users->count() );
        $this->info("Total notification created : " . $sent_count );


    }

}

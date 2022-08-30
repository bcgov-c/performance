<?php

namespace App\Console\Commands;

use DateTime;
use DateTimeZone;
use Carbon\Carbon;
use App\Models\User;
use GuzzleHttp\Client;
use Microsoft\Graph\Graph;
use App\Models\Conversation;
use App\Models\SharedProfile;
use App\Models\UserPreference;
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
        $this->info("Dashboard Notification -- Conversation Due (start)");
        $this->dashboardNotificationsConversationDue();
        $this->info( now() );
        $this->info("Dashboard Notification -- Conversation Due (end)");

        $this->info( now() );
        $this->info("Email Notification -- Conversation Due (start)");
        $this->sendEmployeeEmailNotificationsWhenConversationDue();
        $this->info( now() );
        $this->info("Email Notification -- Conversation Due (end)");

        $this->info( now() );
        $this->info("Email Notification -- Conversation Due (start)");
        $this->sendSupervisorEmailNotificationsWhenTeamConversationDue();
        $this->info( now() );
        $this->info("Email Notification -- Conversation Due (end)");

    }


    protected function dashboardNotificationsConversationDue() {

        $sent_count = 0;
        $skip_count = 0;

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

                } else {
                    $skip_count += 1;
                }
            }

        }

        $this->info("Total selected users              : " . $users->count() );
        $this->info("Total notification skipped (sent) : " . $skip_count );
        $this->info("Total notification created        : " . $sent_count );

    }


    protected function sendEmployeeEmailNotificationsWhenConversationDue() {

        $sent_count = 0;
        $skip_count = 0;

        // Eligible Users (check against Allow Access Oragnizations)
        $users = User::join('employee_demo','employee_demo.guid','users.guid')
                        ->join('access_organizations','employee_demo.organization','access_organizations.organization')
                        ->where('access_organizations.allow_email_msg', 'Y')
                        ->whereNull('employee_demo.date_deleted')
                    ->groupBy('users.id')
                    ->select('users.*')
//->where('users.id', 100013)                    
                    ->get();

        foreach ($users as $index => $user) {

            // User Prference 
            $pref = UserPreference::where('user_id', $user->id)->first();
            if (!$pref) {
                $pref = new UserPreference;
                $pref->user_id = $user->id;
            }
            
            $due = Conversation::nextConversationDue( $user );

            $dueDate = \Carbon\Carbon::create($due);
// Override for testing            
//$dateDate = $dueDate->subDays(70);
            $now = Carbon::now();
            $dayDiff = $dueDate->diffInDays($now);
//$this->info($user->name . ' - ' . $dateDate . ' - ' . $dayDiff);            

            $dueIndays = 0;
            $subject = '';
            $template = 'CONVERSATION_REMINDER';
            $bSend = false;
            $bind1 = $user->name;
            $bind2 = '';
            if ($dayDiff > 7 and $dayDiff <= 30) {
                $dueIndays = 30;
                $bind2 = '1 month';
                if ($pref->conversation_due_month == 'Y') {
                    // $subject = 'REMINDER - your next performance conversation is due in 1 month';
                    $bSend = true;
                }
            }
            if ($dayDiff > 0 and $dayDiff <= 7) {
                $dueIndays = 7;
                $bind2 = '1 week';
                if ($pref->conversation_due_week == 'Y') {
                    // $subject = 'REMINDER - your next performance conversation is due in 1 week';
                    $bSend = true;
                }
            }
            if ($dayDiff <= 0) {  
                $dueIndays = 0;
                $template = 'CONVERSATION_DUE';
                if ($pref->conversation_due_past == 'Y') {
                    // $subject = 'OVERDUE - your next performance conversation is past due';
                    $bSend = true;
                }
            }
      
            if ($bSend) {

                // check the notification sent or not 
                $log = NotificationLog::where('alert_type', 'N')
                                    ->where('alert_format', 'E')
                                    ->where('notify_user_id',  $user->id)
                                    ->whereNull('overdue_user_id')
                                    ->where('notify_due_date', $dueDate->format('Y-m-d') )
                                    ->where('notify_for_days', $dueIndays)
                                    ->first();

                // Send Email for team members
                if (!$log) {
                    $this->info( $due . ' - ' . $user->id . ' - ' . $dueDate->format('Y-m-d') . ' ' . $dueIndays);
                    $sent_count += 1;

                    $sendMail = new \App\MicrosoftGraph\SendMail();
                    $sendMail->toRecipients = [$user->id];
                    // $sendMail->ccRecipients = [$user->id];  // test
                    // $sendMail->bccRecipients = [$user->id]; // test 
                    $sendMail->sender_id = null;  // default sender is System
                    $sendMail->useQueue = false;
                    $sendMail->saveToLog = true;

                    $sendMail->alert_type = 'N';
                    $sendMail->alert_format = 'E';
                    $sendMail->notify_user_id = $user->id;
                    $sendMail->overdue_user_id = null; 
                    $sendMail->notify_due_date = $dueDate->format('Y-m-d');
                    $sendMail->notify_for_days = $dueIndays;

                    $sendMail->template = $template;
                    array_push($sendMail->bindvariables, $bind1 );
                    array_push($sendMail->bindvariables, $bind2 );
                    $response = $sendMail->sendMailWithGenericTemplate();    

                } else {
                    $skip_count += 1;
                }
            } 

        }

        $this->info("Total selected users              : " . $users->count() );
        $this->info("Total notification skipped (sent) : " . $skip_count );
        $this->info("Total notification created        : " . $sent_count );

    }


    protected function sendSupervisorEmailNotificationsWhenTeamConversationDue() {

        $sent_count = 0;
        $skip_count = 0;

        // Eligible Users (check against Allow Access Oragnizations)
        $users = User::join('employee_demo','employee_demo.guid','users.guid')
                        ->join('access_organizations','employee_demo.organization','access_organizations.organization')
                        ->where('access_organizations.allow_email_msg', 'Y')
                        ->whereNull('employee_demo.date_deleted')
                    ->groupBy('users.id')
                    ->select('users.*')
// ->where('users.id', 100013)        
//->where('users.id', 288693)        
                    ->get();

        foreach ($users as $index => $user) {

            // Look for direct report manager and Shared with
            $manager_ids = SharedProfile::where('shared_id', $user->id)
                                ->where('shared_item', 'like',  '%"2"%' ) 
                                ->orderBy('id')
                                ->pluck('shared_with');
            if ($user->reporting_to) {        
                $manager_ids->push($user->reporting_to);
            }

            // if no manager found, then next 
            if ($manager_ids->count() == 0) {
                    continue;
            }


            // process  each managers 
            foreach ($manager_ids as $manager_id) {

                // User Prference 
                $pref = UserPreference::where('user_id', $user->manager_id)->first();
                if (!$pref) {
                    $pref = new UserPreference;
                    $pref->user_id = $user->manager_id;
                }
                
                $due = Conversation::nextConversationDue( $user );

                $dueDate = \Carbon\Carbon::create($due);
// Override for testing            
// $dateDate = $dueDate->subDays(88);
                $now = Carbon::now();
                $dayDiff = $dueDate->diffInDays($now);
// $this->info($manager_id .  ' - ' . $user->name . ' - ' . $dateDate . ' - ' . $dayDiff);            

                $dueIndays = 0;
                $subject = '';
                $template = 'CONVERSATION_REMINDER';
                $bSend = false;
                $bind1 = $user->name;
                $bind2 = '';
                $bind3 = $user->name;

                if ($dayDiff > 7 and $dayDiff <= 30) {
                    $dueIndays = 30;
                    $bind2 = '1 month';
                    if ($pref->team_conversation_due_month == 'Y') {
                        // $subject = 'REMINDER - your next performance conversation is due in 1 month';
                        $bSend = true;
                    }
                }
                if ($dayDiff > 0 and $dayDiff <= 7) {
                    $dueIndays = 7;
                    $bind2 = '1 week';
                    if ($pref->team_conversation_due_week == 'Y') {
                        // $subject = 'REMINDER - your next performance conversation is due in 1 week';
                        $bSend = true;
                    }
                }
                if ($dayDiff <= 0) {  
                    $dueIndays = 0;
                    $template = 'CONVERSATION_DUE';
                    if ($pref->team_conversation_due_past == 'Y') {
                        // $subject = 'OVERDUE - your next performance conversation is past due';
                        $bSend = true;
                    }
                }
        
                if ($bSend) {

                    // check the notification sent or not 
                    $log = NotificationLog::where('alert_type', 'N')
                                        ->where('alert_format', 'E')
                                        ->where('notify_user_id',  $manager_id)
                                        ->where('overdue_user_id',  $user->id)
                                        ->where('notify_due_date', $dueDate->format('Y-m-d') )
                                        ->where('notify_for_days', $dueIndays)
                                        ->first();

                    // Send Email for team members
                    if (!$log) {
                        $this->info( $due . ' - ' . $user->id . ' - ' . $dueDate->format('Y-m-d') . ' ' . $dueIndays);
                        $sent_count += 1;

                        $sendMail = new \App\MicrosoftGraph\SendMail();
                        $sendMail->toRecipients = [$manager_id];
                        // $sendMail->ccRecipients = [$user->id];  // test
                        // $sendMail->bccRecipients = [$user->id]; // test 
                        $sendMail->sender_id = null;  // default sender is System
                        $sendMail->useQueue = false;
                        $sendMail->saveToLog = true;

                        $sendMail->alert_type = 'N';
                        $sendMail->alert_format = 'E';
                        $sendMail->notify_user_id = $manager_id;
                        $sendMail->overdue_user_id = $user->id; 
                        $sendMail->notify_due_date = $dueDate->format('Y-m-d');
                        $sendMail->notify_for_days = $dueIndays;

                        $sendMail->template = $template;
                        array_push($sendMail->bindvariables, $bind1 );
                        array_push($sendMail->bindvariables, $bind2 );
                        array_push($sendMail->bindvariables, $bind3 );
                        $response = $sendMail->sendMailWithGenericTemplate();    

                    } else {
                        $skip_count += 1;
                    }
                } 

           }

        }

        $this->info("Total selected users              : " . $users->count() );
        $this->info("Total notification skipped (sent) : " . $skip_count );
        $this->info("Total notification created        : " . $sent_count );


    }

}

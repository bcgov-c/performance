<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DateTime;
use DateTimeZone;
use Carbon\Carbon;
use App\Models\User;
use App\Models\JobSchedAudit;
use App\Models\SharedProfile;
use App\Models\UserPreference;
use App\Models\NotificationLog;
use App\Models\DashboardNotification;

class NotifyConversationDue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:notifyConversationDue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Conversation Due Notification';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->details = '';
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $start_time = Carbon::now();

        $this->task = JobSchedAudit::Create([
            'job_name' => $this->signature,
            'start_time' => date('Y-m-d H:i:s', strtotime($start_time)),
            'cutoff_time' => date('Y-m-d H:i:s', strtotime($start_time)),
            'status' => 'Initiated'
        ]);

        $this->logInfo( now() );
        $this->logInfo("(1) Dashboard Notification (In-App) -- Conversation Due (start)");
        $this->dashboardNotificationsConversationDue();
        $this->logInfo( now() );
        $this->logInfo("(1) Dashboard Notification (In-App) -- Conversation Due (end)");

        $this->logInfo( now() );
        $this->logInfo("(2) Supervisor Dashboard Notification (In-App) -- Conversation Due (start)");
        $this->supervisorDashboardNotificationsConversationDue();
        $this->logInfo( now() );
        $this->logInfo("(2) Supervisor Dashboard Notification (In-App) -- Conversation Due (end)");

        $this->logInfo( now() );
        $this->logInfo("(3) Email Notification -- Conversation Due (start)");
        $this->sendEmployeeEmailNotificationsWhenConversationDue();
        $this->logInfo( now() );
        $this->logInfo("(3) Email Notification -- Conversation Due (end)");

        $this->logInfo( now() );
        $this->logInfo("(4) Supervisor Email Notification -- Conversation Due (start)");
        $this->sendSupervisorEmailNotificationsWhenTeamConversationDue();
        $this->logInfo( now() );
        $this->logInfo("(4) Supervisor Email Notification -- Conversation Due (end)");

        $end_time = Carbon::now();
        $this->task->end_time = date('Y-m-d H:i:s', strtotime($end_time));
        $this->task->details = $this->details;
        $this->task->status = 'Completed';
        $this->task->save();

        return 0;
    }

    protected function dashboardNotificationsConversationDue() {

        $sent_count = 0;
        $skip_count = 0;
        $row_count = 0;

        // Eligible Users (check against Allow Access Oragnizations)
        // $sql = User::join('employee_demo','employee_demo.guid','users.guid')
        //     ->join('employee_demo_tree', 'employee_demo_tree.id', 'employee_demo.orgid')
        //     ->join('access_organizations','employee_demo_tree.organization_key','access_organizations.orgid')
        //     ->where('employee_demo.guid','<>','')
        //     ->where('users.due_date_paused', 'N')
        //     ->where('access_organizations.allow_inapp_msg', 'Y')
        //     ->whereNull('employee_demo.date_deleted')
        //     ->select('users.*')
        //     ->orderBy('users.guid')
        //     ->orderBy('users.id', 'desc');
        $sql = User::join(\DB::raw('employee_demo USE INDEX (idx_employee_demo_employeeid_orgid)'),'employee_demo.employee_id','users.employee_id')
            ->join('employee_demo_tree', 'employee_demo_tree.id', 'employee_demo.orgid')
            ->join('access_organizations','employee_demo_tree.organization_key','access_organizations.orgid')
            ->where('employee_demo.guid','<>','')
            ->where('users.due_date_paused', 'N')
            ->where('access_organizations.allow_inapp_msg', 'Y')
            ->whereNull('employee_demo.date_deleted')
            ->whereRaw('employee_demo.pdp_excluded = 0')
            ->select('users.*')
            ->orderBy('users.guid')
            ->orderBy('users.id', 'desc');

        $prev_guid = '';
        $sql->chunk(10000, function($chunk) use(&$sent_count, &$skip_count, &$row_count, &$prev_guid) {

            foreach ($chunk as $index => $user) {

                // Avoid deuplicate email send out 
                if ($user->guid == $prev_guid) {
                    continue;
                }
                $prev_guid = $user->guid;
                $row_count += 1;

                //$due = Conversation::nextConversationDue( $user );
                $due = $user->next_conversation_date;

                $dueDate = \Carbon\Carbon::create($due);
                $now = Carbon::now();
                $dayDiff = $now->diffInDays($dueDate, false);
    // Override for testing                        
    // $dayDiff = -1;

                $dueIndays = 0;
                $msg = '';
                if ($dayDiff >= 7 and $dayDiff < 30) {
                    $msg = 'REMINDER - your next conversation is due by ' . $due ;
                    $dueIndays = 30;
                }
                if ($dayDiff >= 0 and $dayDiff < 7) {
                    $msg = 'REMINDER - your next conversation is due by ' . $due ;
                    $dueIndays = 7;
                }
                if ($dayDiff < 0) {  
                    $msg = 'OVERDUE - your next conversation is due by ' .  $due ;
                    $dueIndays = 0;
                }

                // To avoid the past email sent out when the sysadmin turn on global flag under system access control
                if ( ($dayDiff < 0 && $dueDate < today()->subDays(6)) ||
                     ($dayDiff >= 0 && $dueDate < today()) ) {
                    $msg = '';
                }

                if ($msg) {

                    // check the notification sent or not 
                    $log = NotificationLog::where('alert_type', 'N')
                                        ->where('alert_format', 'A')
                                        ->where('notify_user_id',  $user->id)
                                        ->whereNull('overdue_user_id')
                                        ->where('notify_due_date', $dueDate->format('Y-m-d') )
                                        ->where('notify_for_days', $dueIndays)
                                        ->first();

                    if (!$log) {
                        $this->logInfo( $now->format('Y-m-d') . ' - A - ' . $user->id . ' (' . $user->employee_id . ')' . ' - ' . $dueDate->format('Y-m-d') . ' - (' . $dayDiff . ') - ' . $dueIndays);
                        $sent_count += 1;


                        // Use Class to create DashboardNotification
                        $notification = new \App\MicrosoftGraph\SendDashboardNotification();
                        $notification->user_id = $user->id;
                        $notification->notification_type = '';
                        $notification->comment = $msg;
                        $notification->related_id = null;
                    
                        $notification->notify_user_id = $user->id;
                        $notification->overdue_user_id = null; 
                        $notification->notify_due_date = $dueDate->format('Y-m-d');
                        $notification->notify_for_days = $dueIndays;

                        $notification->send(); 

                        // DashBoard Message
                        // DashboardNotification::create([
                        //     'user_id' => $user->id,
                        //     'notification_type' => '',        // Conversation Added
                        //     'comment' => $msg,
                        //     'related_id' => null,
                        // ]);

                        // Write to Log table
                        // $notification_log = NotificationLog::Create([  
                        //     'recipients' => ' ',        // Not in Use
                        //     'sender_id' => 0,           
                        //     'subject' => $msg,
                        //     'description' => '',
                        //     'alert_type' => 'N',
                        //     'alert_format' => 'A',
                        //     'notify_user_id' => $user->id,
                        //     'overdue_user_id' => null,
                        //     'notify_due_date' => $dueDate->format('Y-m-d'),
                        //     'notify_for_days' => $dueIndays,
                        //     'template_id' => null,
                        //     'date_sent' => now(),
                        // ]);

                    } else {
                        // $this->logInfo( $now->format('Y-m-d') . ' - A - ' . $user->id . ' - ' . $dueDate->format('Y-m-d') . ' - (' . $dayDiff . ') - ' . $dueIndays . '  ** SKIPPED ** (ALREADY SENT, LOG RECORD FOUND)' );
                        $skip_count += 1;
                    }

                } else {
                    // $this->logInfo( $now->format('Y-m-d') . ' - A - ' . $user->id . ' - ' . $dueDate->format('Y-m-d') . ' - (' . $dayDiff . ') - ' . $dueIndays . '   ** SKIPPED ** (NOT DUE YET)' );
                    $skip_count += 1;
                }

            }

        });

        $this->logInfo("Total eligible users            : " . $row_count );
        $this->logInfo("Total notification skipped      : " . $skip_count );
        $this->logInfo("Total notification created/Sent : " . $sent_count );

    }


    protected function supervisorDashboardNotificationsConversationDue() {

        $sent_count = 0;
        $skip_count = 0;
        $row_count = 0;

        // Eligible Users (check against Allow Access Oragnizations)
        // $sql = User::join('employee_demo','employee_demo.guid','users.guid')
        //     ->join('employee_demo_tree', 'employee_demo_tree.id', 'employee_demo.orgid')
        //     ->join('access_organizations','employee_demo_tree.organization_key','access_organizations.orgid')
        //     ->where('employee_demo.guid','<>','')
        //     ->where('users.due_date_paused', 'N')
        //     ->where('access_organizations.allow_inapp_msg', 'Y')
        //     ->whereNull('date_deleted')
        //     ->select('users.*')
        //     ->orderBy('users.guid')
        //     ->orderBy('users.id', 'desc');
        $sql = User::join(\DB::raw('employee_demo USE INDEX (idx_employee_demo_employeeid_orgid)'),'employee_demo.employee_id','users.employee_id')
            ->join('employee_demo_tree', 'employee_demo_tree.id', 'employee_demo.orgid')
            ->join('access_organizations','employee_demo_tree.organization_key','access_organizations.orgid')
            ->where('employee_demo.guid','<>','')
            ->whereRaw('employee_demo.pdp_excluded = 0')
            ->where('users.due_date_paused', 'N')
            ->where('access_organizations.allow_inapp_msg', 'Y')
            ->whereNull('date_deleted')
            ->select('users.*')
            ->orderBy('users.guid')
            ->orderBy('users.id', 'desc');

        $prev_guid = '';    
        $sql->chunk(10000, function($chunk) use(&$sent_count, &$skip_count, &$row_count, &$prev_guid) {

            foreach ($chunk as $index => $user) {

                // Avoid deuplicate email send out 
                if ($user->guid == $prev_guid) {
                    continue;
                }

                // $row_count += 1;

                // Look for direct report manager and Shared with
                // $manager_ids = SharedProfile::where('shared_id', $user->id)
                //                     ->where('shared_item', 'like',  '%"2"%' ) 
                //                     ->orderBy('id')
                //                     ->pluck('shared_with');
                // if ($user->reporting_to) {        
                //     $manager_ids->push($user->reporting_to);
                // }

                $manager_ids = $this->getSupervisorList($user); 

                // if no manager found, then next 
                if ($manager_ids->count() == 0) {
                        continue;
                }

                // process  each managers 
                foreach ($manager_ids as $manager_id) {

                    $row_count += 1;

                    // check whether the manager can recieve In-App Message
                    // $mgr = User::join('employee_demo','employee_demo.guid','users.guid')
                    //     ->join('employee_demo_tree', 'employee_demo_tree.id', 'employee_demo.orgid')
                    //     ->join('access_organizations','employee_demo_tree.organization_key','access_organizations.orgid')
                    //     ->where('access_organizations.allow_inapp_msg', 'Y')
                    //     ->whereNull('date_deleted')                                                
                    //     ->where('users.id',  $manager_id)
                    //     ->first();
                    $mgr = User::join(\DB::raw('employee_demo USE INDEX (idx_employee_demo_employeeid_orgid)'),'employee_demo.employee_id','users.employee_id')
                        ->join('employee_demo_tree', 'employee_demo_tree.id', 'employee_demo.orgid')
                        ->join('access_organizations','employee_demo_tree.organization_key','access_organizations.orgid')
                        ->where('access_organizations.allow_inapp_msg', 'Y')
                        ->whereNull('date_deleted')                                                
                        ->whereRaw('employee_demo.pdp_excluded = 0')                                            
                        ->where('users.id',  $manager_id)
                        ->first();

                    if (!$mgr) {
                        $this->logInfo( Carbon::now()->format('Y-m-d') . ' - E - ' .  $manager_id . ' (' . ($mgr ? $mgr->employee_id : '      ') . ') - '  .
                                $user->id . ' (' . $user->employee_id . ')' . '  ** SKIPPED ** (MANAGER PREFER NOT TO RECECIVED EMAIL OR ORG IS NOT ALLOW EMAIL)' );
                        $skip_count += 1;
                        continue;
                    }

                    // $due = Conversation::nextConversationDue( $user );
                    $due = $user->next_conversation_date;

                    $dueDate = \Carbon\Carbon::create($due);
                    $now = Carbon::now();
                    $dayDiff = $now->diffInDays($dueDate, false);
    // Override for testing                        
    //$dayDiff = 11;                

                    $dueIndays = 0;
                    $msg = '';
                    if ($dayDiff >= 7 and $dayDiff < 30) {
                        $msg = 'REMINDER - ' . $user->name . '\'s next conversation is due by ' . $due ;
                        $dueIndays = 30;
                    }
                    if ($dayDiff >= 0 and $dayDiff < 7) {
                        $msg = 'REMINDER - ' . $user->name . '\'s next conversation is due by ' . $due ;
                        $dueIndays = 7;
                    }
                    if ($dayDiff < 0) {  
                        $msg = 'OVERDUE - ' . $user->name . '\'s next conversation is due by ' . $due ;
                        $dueIndays = 0;
                    }

                    // To avoid the past email sent out when the sysadmin turn on global flag under system access control
                    if ( ($dayDiff < 0 && $dueDate < today()->subDays(6)) ||
                         ($dayDiff >= 0 && $dueDate < today()) ) {
                        $msg = '';
                    }

                    if ($msg) {

                        // check the notification sent or not 
                        $log = NotificationLog::where('alert_type', 'N')
                                            ->where('alert_format', 'A')
                                            ->where('notify_user_id',  $manager_id)
                                            ->where('overdue_user_id',  $user->id)
                                            ->where('notify_due_date', $dueDate->format('Y-m-d') )
                                            ->where('notify_for_days', $dueIndays)
                                            ->first();

                        if (!$log) {
                            $this->logInfo( $now->format('Y-m-d') . ' - A - ' .  $manager_id . ' (' . ($mgr ? $mgr->employee_id : '      ') . ') - '  .
                                $user->id . ' (' . $user->employee_id . ')' . ' - ' . $dueDate->format('Y-m-d') . ' - (' . $dayDiff . ') - ' . $dueIndays);
                            $sent_count += 1;
        
                            // Use Class to create DashboardNotification
                            $notification = new \App\MicrosoftGraph\SendDashboardNotification();
                            $notification->user_id = $manager_id;
                            $notification->notification_type = '';
                            $notification->comment = $msg;
                            $notification->related_id = null;

                            $notification->notify_user_id = $manager_id;
                            $notification->overdue_user_id = $user->id; 
                            $notification->notify_due_date = $dueDate->format('Y-m-d');
                            $notification->notify_for_days = $dueIndays;

                            $notification->send(); 

                            // DashBoard Message
                            // DashboardNotification::create([
                            //     'user_id' => $manager_id,
                            //     'notification_type' => '',        // Conversation Added
                            //     'comment' => $msg,
                            //     'related_id' => null,
                            // ]);
        
                            // Write to Log table
                            // $notification_log = NotificationLog::Create([  
                            //     'recipients' => ' ',        // Not in Use
                            //     'sender_id' => 0,           
                            //     'subject' => $msg,
                            //     'description' => '',
                            //     'alert_type' => 'N',
                            //     'alert_format' => 'A',
                            //     'notify_user_id' => $manager_id,
                            //     'overdue_user_id' => $user->id,
                            //     'notify_due_date' => $dueDate->format('Y-m-d'),
                            //     'notify_for_days' => $dueIndays,
                            //     'template_id' => null,
                            //     'date_sent' => now(),
                            // ]);
        
                        } else {
                            // $this->logInfo( $now->format('Y-m-d') . ' - A - ' .  $manager_id . ' - ' . $user->id . ' - ' . $dueDate->format('Y-m-d') . ' - (' . $dayDiff . ') - ' . $dueIndays . '  ** SKIPPED ** (ALREADY SENT, LOG RECORD FOUND)' );
                            $skip_count += 1;
                        }
                    } else {
                        // $this->logInfo( $now->format('Y-m-d') . ' - A - ' .  $manager_id . ' - ' . $user->id . ' - ' . $dueDate->format('Y-m-d') . ' - (' . $dayDiff . ') - ' . $dueIndays . '  ** SKIPPED ** (NOT DUE YET)');
                        $skip_count += 1;
                    }

                }

                $prev_guid = $user->guid;

            }

        });

        $this->logInfo("Total eligible managers         : " . $row_count );
        $this->logInfo("Total notification skipped      : " . $skip_count );
        $this->logInfo("Total notification created/Sent : " . $sent_count );

    }


    protected function sendEmployeeEmailNotificationsWhenConversationDue() {

        $sent_count = 0;
        $skip_count = 0;
        $row_count = 0;

        // Eligible Users (check against Allow Access Oragnizations)
        // $sql = User::join('employee_demo','employee_demo.guid','users.guid')
        //     ->join('employee_demo_tree', 'employee_demo_tree.id', 'employee_demo.orgid')
        //     ->join('access_organizations','employee_demo_tree.organization_key','access_organizations.orgid')
        //     ->where('employee_demo.guid','<>','')
        //     ->where('access_organizations.allow_email_msg', 'Y')
        //     ->whereNull('employee_demo.date_deleted')
        //     ->where('users.due_date_paused', 'N')
        //     ->select('users.*')
        //     ->orderBy('users.guid')
        //     ->orderBy('users.id', 'desc');
        $sql = User::join(\DB::raw('employee_demo USE INDEX (idx_employee_demo_employeeid_orgid)'),'employee_demo.employee_id','users.employee_id')
            ->join('employee_demo_tree', 'employee_demo_tree.id', 'employee_demo.orgid')
            ->join('access_organizations','employee_demo_tree.organization_key','access_organizations.orgid')
            ->where('employee_demo.guid','<>','')
            ->where('access_organizations.allow_email_msg', 'Y')
            ->whereNull('employee_demo.date_deleted')
            ->whereRaw('employee_demo.pdp_excluded = 0')
            ->where('users.due_date_paused', 'N')
            ->select('users.*')
            ->orderBy('users.guid')
            ->orderBy('users.id', 'desc');

        $prev_guid = '';
        $sql->chunk(10000, function($chunk) use(&$sent_count, &$skip_count, &$row_count, &$prev_guid) {

            foreach ($chunk as $index => $user) {

                // Avoid deuplicate email send out 
                if ($user->guid == $prev_guid) {
                    continue;
                }
                $prev_guid = $user->guid;
                $row_count += 1;

                // User Prference 
                $pref = UserPreference::where('user_id', $user->id)->first();
                if (!$pref) {
                    $pref = new UserPreference;
                    $pref->user_id = $user->id;
                }
                
                // $due = Conversation::nextConversationDue( $user );
                $due = $user->next_conversation_date;
        
                $dueDate = \Carbon\Carbon::create($due);
                $now = Carbon::now();
                $dayDiff = $now->diffInDays($dueDate, false);
    // Override for testing                        
    // $dayDiff = -1;                   


                $dueIndays = 0;
                $subject = '';
                $template = 'CONVERSATION_REMINDER';
                $bSend = false;
                $bind1 = $user->name;
                $bind2 = $dueDate->format('M d, Y');
                if ($dayDiff >= 7 and $dayDiff < 30) {
                    $dueIndays = 30;
                    if ($pref->conversation_due_month == 'Y') {
                        // $subject = 'REMINDER - your next performance conversation is due in 1 month';
                        $bSend = true;
                    }
                }
                if ($dayDiff >= 0 and $dayDiff < 7) {
                    $dueIndays = 7;
                    if ($pref->conversation_due_week == 'Y') {
                        // $subject = 'REMINDER - your next performance conversation is due in 1 week';
                        $bSend = true;
                    }
                }
                if ($dayDiff < 0) {  
                    $template = 'CONVERSATION_DUE';
                    if ($pref->conversation_due_past == 'Y') {
                        // $subject = 'OVERDUE - your next performance conversation is past due';
                        $bSend = true;
                    }
                }

                // To avoid the past email sent out when the sysadmin turn on global flag under system access control
                if ( ($dayDiff < 0 && $dueDate < today()->subDays(6)) ||
                     ($dayDiff >= 0 && $dueDate < today()) ) {                    
                    $bSend = false;
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
                        $this->logInfo( $now->format('Y-m-d') . ' - E - ' . $user->id . ' (' . $user->employee_id . ')' .' - ' . $dueDate->format('Y-m-d') . ' - (' . $dayDiff . ') - ' . $dueIndays);
                        $sent_count += 1;

                        $sendMail = new \App\MicrosoftGraph\SendMail();
                        $sendMail->toRecipients = [$user->id];
                        // $sendMail->ccRecipients = [$user->id];  // test
                        // $sendMail->bccRecipients = [$user->id]; // test 
                        $sendMail->sender_id = null;  // default sender is System
                        $sendMail->useQueue = true;
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
                        // $this->logInfo( $now->format('Y-m-d') . ' - E - ' . $user->id . ' - ' . $dueDate->format('Y-m-d') . ' - (' . $dayDiff . ') - ' . $dueIndays . ' ** SKIPPED ** (ALREADY SENT, LOG RECORD FOUND)' );
                        $skip_count += 1;
                    }
                } else {
                    // $this->logInfo( $now->format('Y-m-d') . ' - E - ' . $user->id . ' - ' . $dueDate->format('Y-m-d') . ' - (' . $dayDiff . ') - ' . $dueIndays . ' ** SKIPPED ** (NOT DUE YET)' );
                    $skip_count += 1;
                }

            }

        });

        $this->logInfo("Total eligible users            : " . $row_count );
        $this->logInfo("Total notification skipped      : " . $skip_count );
        $this->logInfo("Total notification created/Sent : " . $sent_count );

    }


    protected function sendSupervisorEmailNotificationsWhenTeamConversationDue() {

        $sent_count = 0;
        $skip_count = 0;
        $row_count = 0;

        // Eligible Users (check against Allow Access Oragnizations)
        // $sql = User::join('employee_demo','employee_demo.guid','users.guid')
        //     ->join('employee_demo_tree', 'employee_demo_tree.id', 'employee_demo.orgid')
        //     ->join('access_organizations','employee_demo_tree.organization_key','access_organizations.orgid')
        //     ->where('employee_demo.guid','<>','')
        //     ->where('access_organizations.allow_email_msg', 'Y')
        //     ->whereNull('employee_demo.date_deleted')
        //     ->where('users.due_date_paused', 'N')
        //     ->select('users.*')
        //     ->orderBy('users.guid')
        //     ->orderBy('users.id', 'desc');
        $sql = User::join(\DB::raw('employee_demo USE INDEX (idx_employee_demo_employeeid_orgid)'),'employee_demo.employee_id','users.employee_id')
            ->join('employee_demo_tree', 'employee_demo_tree.id', 'employee_demo.orgid')
            ->join('access_organizations','employee_demo_tree.organization_key','access_organizations.orgid')
            ->where('employee_demo.guid','<>','')
            ->where('access_organizations.allow_email_msg', 'Y')
            ->whereNull('employee_demo.date_deleted')
            ->whereRaw('employee_demo.pdp_excluded = 0')
            ->where('users.due_date_paused', 'N')
            ->select('users.*')
            ->orderBy('users.guid')
            ->orderBy('users.id', 'desc');

        $prev_guid = '';
        $sql->chunk(10000, function($chunk) use(&$sent_count, &$skip_count, &$row_count, &$prev_guid) {

            foreach ($chunk as $index => $user) {

                // Avoid deuplicate email send out 
                if ($user->guid == $prev_guid) {
                    continue;
                }

                // $row_count += 1;

                // Look for direct report manager and Shared with
                // $manager_ids = SharedProfile::where('shared_id', $user->id)
                //                     ->where('shared_item', 'like',  '%"2"%' ) 
                //                     ->orderBy('id')
                //                     ->pluck('shared_with');
                // if ($user->reporting_to) {        
                //     $manager_ids->push($user->reporting_to);
                // }

                $manager_ids = $this->getSupervisorList($user); 

                // if no manager found, then next 
                if ($manager_ids->count() == 0) {
                        continue;
                }

                // process  each managers 
                foreach ($manager_ids as $manager_id) {

                    $row_count += 1;

                    // check whether the manager can recieve In-App Message
                    // $mgr = User::join('employee_demo','employee_demo.guid','users.guid')
                    //     ->join('employee_demo_tree', 'employee_demo_tree.id', 'employee_demo.orgid')
                    //     ->join('access_organizations','employee_demo_tree.organization_key','access_organizations.orgid')
                    //     ->where('access_organizations.allow_email_msg', 'Y')
                    //     ->whereNull('date_deleted')                                                
                    //     ->where('users.id',  $manager_id)
                    //     ->first();
                    $mgr = User::join(\DB::raw('employee_demo USE INDEX (idx_employee_demo_employeeid_orgid)'),'employee_demo.employee_id','users.employee_id')
                        ->join('employee_demo_tree', 'employee_demo_tree.id', 'employee_demo.orgid')
                        ->join('access_organizations','employee_demo_tree.organization_key','access_organizations.orgid')
                        ->where('access_organizations.allow_email_msg', 'Y')
                        ->whereNull('date_deleted')                                                
                        ->whereRaw('employee_demo.pdp_excluded = 0')                                             
                        ->where('users.id',  $manager_id)
                        ->select('users.*')
                        ->first();

                    if (!$mgr) {
                        $this->logInfo( Carbon::now()->format('Y-m-d') . ' - E - ' .  $manager_id . ' (' . ($mgr ? $mgr->employee_id : '      ') . ') - '  .
                        $user->id . ' (' . $user->employee_id . ')' . ' - ' . '  ** SKIPPED ** (MANAGER PREFER NOT TO RECECIVED EMAIL OR ORG IS NOT ALLOW EMAIL)' );
                        $skip_count += 1;
                        continue;
                    }



                    // User Prference 
                    $pref = UserPreference::where('user_id', $mgr->id)->first();
                    if (!$pref) {
                        $pref = new UserPreference;
                        $pref->user_id = $mgr->id;
                    }

                    // $due = Conversation::nextConversationDue( $user );
                    $due = $user->next_conversation_date;

                    $dueDate = \Carbon\Carbon::create($due);
                    $now = Carbon::now();
                    $dayDiff = $now->diffInDays($dueDate, false);
    // Override for testing                        
    // $dayDiff = -1;                   


                    $dueIndays = 0;
                    $subject = '';
                    $template = 'SUPV_CONV_REMINDER';
                    $bSend = false;
                    // $bind1 = $user->reportingManager->name;
                    $bind1 = $mgr->name;
                    $bind2 = $user->name;
                    $bind3 = $dueDate->format('M d, Y');

                    if ($dayDiff >= 7 and $dayDiff < 30) {
                        $dueIndays = 30;
                        if ($pref->team_conversation_due_month == 'Y') {
                            // $subject = 'REMINDER - your next performance conversation is due in 1 month';
                            $bSend = true;
                        }
                    }
                    if ($dayDiff >= 0 and $dayDiff < 7) {
                        $dueIndays = 7;
                        if ($pref->team_conversation_due_week == 'Y') {
                            // $subject = 'REMINDER - your next performance conversation is due in 1 week';
                            $bSend = true;
                        }
                    }
                    if ($dayDiff < 0) {  
                        $dueIndays = 0;
                        $template = 'SUPV_CONV_DUE';
                        if ($pref->team_conversation_due_past == 'Y') {
                            // $subject = 'OVERDUE - your next performance conversation is past due';
                            $bSend = true;
                        }
                    }

                    // To avoid the past email sent out when the sysadmin turn on global flag under system access control
                    if ($bSend && ($dueDate < today()->subDays(6)))  {
                            $bSend = false;                        
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
                            $this->logInfo( $now->format('Y-m-d') . ' - E - ' .  $manager_id . ' (' . ($mgr ? $mgr->employee_id : '      ') . ') - '  .
                            $user->id . ' (' . $user->employee_id . ')' . ' - ' . $dueDate->format('Y-m-d') . ' - (' . $dayDiff . ') - ' . $dueIndays);
                            $sent_count += 1;

                            $sendMail = new \App\MicrosoftGraph\SendMail();
                            $sendMail->toRecipients = [$manager_id];
                            // $sendMail->ccRecipients = [$user->id];  // test
                            // $sendMail->bccRecipients = [$user->id]; // test 
                            $sendMail->sender_id = null;  // default sender is System
                            $sendMail->useQueue = true;
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
                            // $this->logInfo( Carbon::now()->format('Y-m-d') . ' - E - ' .  $manager_id . ' - ' . $user->id . ' - ' . $dueDate->format('Y-m-d') . ' - (' . $dayDiff . ') - ' . $dueIndays . '  ** SKIPPED ** (ALREADY SENT, LOG RECORD FOUND)' );
                            $skip_count += 1;
                        } 

                    } else {
                        // $this->logInfo( Carbon::now()->format('Y-m-d') . ' - E - ' .  $manager_id . ' - ' . $user->id . ' - ' . $dueDate->format('Y-m-d') . ' - (' . $dayDiff . ') - ' . $dueIndays . '  ** SKIPPED ** (NOT DUE YET)' );
                        $skip_count += 1; 
                    }
                }

                $prev_guid = $user->guid;

            }

        });

        $this->logInfo("Total eligible managers         : " . $row_count );
        $this->logInfo("Total notification skipped      : " . $skip_count );
        $this->logInfo("Total notification created/Sent : " . $sent_count );

    }

    protected function getSupervisorList ($current_user) {

        // Shared Profile
        $manager_ids = SharedProfile::where('shared_id', $current_user->id)
                        ->join('users','users.id','shared_profiles.shared_with')
                        ->join('employee_demo','employee_demo.employee_id', 'users.employee_id')
                        ->whereNull('employee_demo.date_deleted')
                        ->whereRaw('employee_demo.pdp_excluded = 0')
                        ->where('shared_item', 'like',  '%"2"%' ) 
                        ->orderBy('users.id')
                        ->pluck('shared_with');

        // Superviser
        $supervisorList = $current_user->supervisorList();
        $supervisorListCount = $current_user->supervisorListCount();
        $preferredSupervisor = $current_user->preferredSupervisor();

        if ($supervisorListCount <= 1) {
            if ($current_user->reportingManager) {
                $manager_ids->push( $current_user->reportingManager->id );
            }
        } else {
            if (!$preferredSupervisor) {
                if ($current_user->reportingManager) {
                    $manager_ids->push( $current_user->reportingManager->id );
                }
            } else {
                foreach ($supervisorList as $supv) {
                    if ($supv->employee_id == $preferredSupervisor->supv_empl_id) {
                        $manager_ids->push( $supv->id );
                        break;
                    }
                }
            }
        }

        return $manager_ids;

    }


    protected function logInfo($text) {

        $this->info( $text );
        $this->details .= $text . PHP_EOL;

        // write to log
        $this->task->details = $this->details;
        // $this->task->save();

    }

}

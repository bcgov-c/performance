<?php

namespace App\MicrosoftGraph;

use DateTime;
use DateInterval;
use DateTimeZone;
use App\Models\User;
use App\Models\NotificationLog;
use App\Models\DashboardNotification;
  

class SendDashboardNotification
{

    //public $toAddresses;
    public $user_id;                /* user who receive this notfication */
    public $notification_type;      /* type of the notitcation */
    public $comment;                /* Comment */
    public $related_id;             /* related itme ID */

    // Audit Log related
    public $saveToLog;              /* Boolean -- true or false */
    public $alertType;
    public $alertFormat;

    // Special fields for Conversation Due notification (use for avoid sent duplication)
    public $notify_user_id;
    public $overdue_user_id;
    public $notify_due_date;
    public $notify_for_days;
    
   
    public function __construct() 
    {
        //$this->toAddresses = [];
        $this->user_id = null;
        $this->notification_type = '';
        $this->comment = '';
        $this->related_id = null;

        $this->saveToLog = true;

        $this->alertType = 'N';  /* Notification */
        $this->alertFormat = 'A';   /* E = E-mail, A = In App */

    }

    public function send() 
    {

        // Eligible User's organization (check against Allow Access Oragnizations)
        // $user = User::join('employee_demo','employee_demo.guid','users.guid')
                // ->join('employee_demo_tree', 'employee_demo_tree.id', 'employee_demo.orgid')
        //                 ->join('access_organizations','employee_demo_tree.organization_key','access_organizations.orgid')
        //                 ->where('access_organizations.allow_inapp_msg', 'Y')
        //                 ->whereNull('date_deleted')                                                
        //                 ->where('users.id',  $this->user_id)
        //                 ->first();

        // if ($user) {

            // DashBoard Message
            DashboardNotification::create([
                'user_id' => $this->user_id,
                'notification_type' => $this->notification_type,       
                'comment' => $this->comment,
                'related_id' => $this->related_id,
            ]);

            if ($this->saveToLog)  {

                // Write to Log table
                $notification_log = NotificationLog::Create([  
                    'recipients' => ' ',        // Not in Use
                    'sender_id' => 0,           
                    'subject' =>  $this->comment,
                    'description' => '',
                    'alert_type' => 'N',
                    'alert_format' => 'A',
                    'notify_user_id' => $this->notify_user_id,
                    'overdue_user_id' => $this->overdue_user_id,
                    'notify_due_date' => $this->notify_due_date,
                    'notify_for_days' => $this->notify_for_days,
                    'template_id' => null,
                    'date_sent' => now(),
                ]);
            }
        // }

    }

}

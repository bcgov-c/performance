<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id', 
        'recipients',    /* has value assign only when this is send in the lower region with test account */
        'subject',
        'description',
        'alert_type',
        'alert_format',
        'sender_id',
        'sender_email',
        'notify_user_id',           // notify user id  (team member or Supervisor)
        'overdue_user_id',          // conversation overdue user (team member or Supervisor
        'notify_due_date',
        'notify_for_days',          // InApp -- notify for due in days e.g. conversation due date
        'template_id',
        'status',
        'date_sent',
        'use_queue',

    ];

    public const ALERT_FORMAT = 
    [
        "E" => "E-mail",
        "A" => "In app",
    ];

    public const ALERT_TYPE = 
    [
        "N" => "Notification",
    ];


    public function recipients() {
        return $this->hasMany('App\Models\NotificationLogRecipient', 'notification_log_id');
    }

    public function recipientNames() {

        $userIds = $this->recipients()->pluck('recipient_id')->toArray();
        $users = User::whereIn('id', $userIds)->pluck('name');
        return implode('; ', $users->toArray() );

    }

    public function notify_user() {

        return $this->belongsTo('App\Models\User','notify_user_id','id')->select('name', 'id', 'email');        

    }

    public function overdue_user() {

        return $this->belongsTo('App\Models\User','overdue_user_id','id')->select('name', 'id', 'email');        

    }


    public function sender() {

        return $this->belongsTo('App\Models\User')->select('name', 'id', 'email');        

    }

    public function template() {

        return $this->belongsTo('App\Models\GenericTemplate')->select('template', 'id');        
        
    }

    public function alert_type_name() {
        
        return array_key_exists($this->alert_type, self::ALERT_TYPE) ? self::ALERT_TYPE[$this->alert_type] : '';

    }

    public function alert_format_name() {
        
        return array_key_exists($this->alert_format, self::ALERT_FORMAT) ? self::ALERT_FORMAT[$this->alert_format] : '';

    }



}


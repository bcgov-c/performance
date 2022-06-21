<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Conversation extends Model
{
    use HasFactory, SoftDeletes;

    protected $with = ['topic', 'conversationParticipants', 'conversationParticipants.participant'];
    protected $appends = ['c_date', 'c_time', 'questions', 'date_time', 'is_current_user_participant', 'is_with_supervisor', 'last_sign_off_date', 'is_locked'];

    protected $casts = [
        'date' => 'datetime:Y-m-d',
        'time' => 'datetime:H:i:s',
        'sign_off_time' => 'datetime:Y-m-d',
        'supervisor_signoff_time' => 'datetime:Y-m-d',
        'initial_signoff' => 'datetime:Y-m-d',
        'unlock_until' => 'datetime:Y-m-d'
    ];

    public function topic()
    {
        return $this->belongsTo('App\Models\ConversationTopic', 'conversation_topic_id', 'id');
    }
    public function conversationParticipants()
    {
        return $this->hasMany('App\Models\ConversationParticipant');
    }

    public function getIsLockedAttribute() {
        if (!$this->initial_signoff) {
            return false;
        }
        return $this->initial_signoff->addDays(14)->isPast();
    }

    public function getInfoComment1Attribute() {
        if($this->attributes['info_comment1'] === null) return '';
        return $this->attributes['info_comment1'];
    }

    public function getInfoComment2Attribute() {
        if($this->attributes['info_comment2'] === null) return '';
        return $this->attributes['info_comment2'];
    }

    public function getInfoComment3Attribute() {
        if($this->attributes['info_comment3'] === null) return '';
        return $this->attributes['info_comment3'];
    }

    public function getInfoComment4Attribute() {
        if($this->attributes['info_comment4'] === null) return '';
        return $this->attributes['info_comment4'];
    }

    public function getInfoComment5Attribute() {
        if($this->attributes['info_comment5'] === null) return '';
        return $this->attributes['info_comment5'];
    }

    // If conversation is with
    public function getIsWithSupervisorAttribute() {
        return $this->isWithSupervisor();
    }

    private function isWithSupervisor($userID = null) {
        if ($userID === null) {
            $checkForOriginalUser = true;
            $authId = ($checkForOriginalUser && session()->has('original-auth-id')) ? session()->get('original-auth-id') : Auth::id();
        } else {
            $authId = $userID;
        }
        $user = User::find($authId);
        $reportingManager = $user ? $user->reportingManager()->first() : null;
        if (!$reportingManager) {
            return false;
        }
        foreach ($this->conversationParticipants->toArray() as $cp) {
            if ($cp['participant_id'] === $reportingManager->id)
                return true;
        }
        return false;
    }

    public function getCDateAttribute()
    {
        return $this->date->format('M d, Y');
    }

    public function getLastSignOffDateAttribute()
    {
        return $this->supervisor_signoff_time > $this->sign_off_time ? $this->sign_off_time : $this->supervisor_signoff_time;
    }

    public function getCTimeAttribute()
    {
        return $this->time->format('h:i A');
    }

    public function getDateTimeAttribute()
    {
        return Carbon::parse($this->date->format('M d, Y') .' '. $this->time->format('h:i A')); // $this->time->format('h:i A');
    }

    public function getQuestionsAttribute()
    {
        // return Config::get('global.conversation.topic.' . $this->conversation_topic_id . '.questions');
        return ConversationTopic::find($this->conversation_topic_id)->question_html;
    }

    // Should not be used.
    public static function hasNotDoneAtleastOnceIn4Months()
    {
        $latestPastConversation = self::latestPastConversation();
        if ($latestPastConversation) {
            return $latestPastConversation->date_time->addDays(122)->isPast();
        }
        return true;
    }

    // Should not be used.
    public static function hasNotYetScheduledConversation($user_id)
    {
        return !self::where('user_id', $user_id)->count() > 0;
    }

    public static function getLastConv($ignoreList = [], $user = null) {
        if ($user === null) 
            $user = Auth::user();
        $authId = $user->id;
        
        $lastConv = self::where(function ($query) use ($authId) {
            $query->where('user_id', $authId)->orWhereHas('conversationParticipants', function ($query) use ($authId) {
                return $query->where('participant_id', $authId);
            });
        })->whereNotNull('signoff_user_id')
        ->whereNotNull('supervisor_signoff_id')
        ->whereNotIn('id', $ignoreList)
        ->orderBy('sign_off_time', 'DESC')
        ->first();
        
        if ($lastConv && !$lastConv->isWithSupervisor($user->id)) {
            $ignoreList[] = $lastConv->id;
            $lastConv = self::getLastConv($ignoreList, $user);
        }
        return $lastConv;
    }

    public static function warningMessage() {
        $lastConv = self::getLastConv();
        
        if ($lastConv) {
            if ($lastConv->sign_off_time->addMonths(4)->lt(Carbon::now())) {
                return [
                    "You are required to complete a performance conversation every 4 months at minimum. You are overdue. Please complete a conversation as soon as possible.",
                    "danger"
                ];
            }
            $nextDueDate = $lastConv->sign_off_time->addMonths(4);
            $diff = Carbon::now()->diffInMonths($lastConv->sign_off_time->addMonths(4), false);
            return [
                // "Your last performance conversation was completed on ".$lastConv->sign_off_time->format('d-M-y').". 
                "Your next performance conversation is due by ". $lastConv->sign_off_time->addMonths(4)->format('d-M-y'),
                $diff < 0 ? "danger" : ($diff < 1 ? "warning" : "success")
            ];
        }
        $user = Auth::user();
        $nextDueDate = $user->joining_date ? $user->joining_date->addMonths(4) : '';
        $diff = Carbon::now()->diffInMonths($nextDueDate, false);
        /* dd([
            Carbon::now()->format('d-M-y'),
            $nextDueDate->format('d-M-y'),
            $diff
        ]); */
        return [
            "You must complete your first performance conversation by " . $nextDueDate->format('d-M-y'),
            $diff < 0 ? "danger" : ($diff < 1 ? "warning" : "success")
        ];
    }

    public static function nextConversationDue($user = null) {
        if ($user === null)
            $user = Auth::user();
        $lastConv = self::getLastConv([], $user);
        $nextConvDate =  ($lastConv) ? $lastConv->sign_off_time->addMonths(4)->format('d-M-y') : (
            $user->joining_date ? $user->joining_date->addMonths(4)->format('d-M-y') : ''
        );
        return $nextConvDate;
    }

    public static function latestPastConversation()
    {
        return self::whereNotNull('signoff_user_id')->orderBy('date', 'DESC')->first();
    }

    public function getIsCurrentUserParticipantAttribute()
    {
        foreach ($this->conversationParticipants->toArray() as $cp) {
            if ($cp['participant_id'] === Auth::id())
                return true;
        }
        return false;
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getIsUnlockAttribute() {
        if (!$this->unlock_until) {
            return false;
        }
        return !($this->unlock_until->isPast());
        
    }

    public function signoff_user() {
        return $this->belongsTo(User::class, 'signoff_user_id');
    }

    public function signoff_supervisor() {
        return $this->belongsTo(User::class, 'supervisor_signoff_id');
    }


}

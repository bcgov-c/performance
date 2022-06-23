<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Traits\HasRoles;
use App\Models\EmployeeDemo;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    // Use \Awobaz\Compoships\Compoships;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'azure_id',
        'samaccountname',
        'guid',
        'reporting_id',
        'employee_id',
        'empl_record',
        'excused_start_date',
        'excused_end_date',
        'excused_reason_id',
        'joining_date',
        'acctlock',
        'last_signon_at',
        'last_sync_at',
    ];

    public $timestamps = true;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'azure_id',
        'email_verified_at'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'joining_date' => 'datetime'
    ];

    protected $appends = [
        'is_goal_shared_with_auth_user',
        'is_conversation_shared_with_auth_user',
        'is_shared'
    ];

    public function goals() {
        return $this->hasMany('App\Models\Goal');
    }

    public function activeGoals()
    {
        return $this->hasMany('App\Models\Goal')->where('status', 'active');
    }

    public function goalCount() {
        return $this->goals()->count();
    }

    public function conversations() {
        return $this->hasMany('App\Models\Conversation');
    }

    public function upcomingConversation() {
        return $this->conversations()->whereNull('signoff_user_id')->orderBy('date', 'DESC');
    }

    public function getIsGoalSharedWithAuthUserAttribute() {
        if ($this->id === Auth::id()) {
            return true;
        }

        $shared = SharedProfile::
            where('shared_id', $this->id)
            ->where('shared_with', Auth::id())
            ->first();
        if (!$shared) {
            return false;
        }

        return in_array(1, $shared->shared_item);
    }

    public function getIsConversationSharedWithAuthUserAttribute() {
        if ($this->id === Auth::id()) {
            return true;
        }

        $shared = SharedProfile::
            where('shared_id', $this->id)
            ->where('shared_with', Auth::id())
            ->first();
        if (!$shared) {
            return false;
        }

        return in_array(2, $shared->shared_item);
    }

    public function getIsSharedAttribute() {
        return SharedProfile::where('shared_id', $this->id)->count() > 0;
    }

    public function latestConversation()
    {
        return $this->conversations()->whereNotNull('signoff_user_id')->orderBy('date', 'DESC');
    }

    public function sharedGoals()
    {
        return $this->belongsToMany('App\Models\Goal', 'goals_shared_with', 'user_id', 'goal_id')->where('status', 'active');
    }

    public function excuseReason()
    {
        return $this->belongsTo('App\Models\ExcusedReason','excused_reason_id','id')->select('name', 'id');
    }

    public function reportingManager() {
        return $this->belongsTo('App\Models\User', 'reporting_to');
    }

    public function reportingManagerRecursive() {
        return $this->reportingManager()->with('reportingManagerRecursive');
    }

    public function reportees() {
        return $this->hasMany('App\Models\User', 'reporting_to');
    }

    public function reporteesCount() {
        return $this->reportees()->count();
    }

    public function hasSupervisorRole() {
        return $this->reportees()->count() > 0;
    }

    public function canBeSeenBy($id) {
        if (!$this->reportingManager)
            return false;
        if ($this->reportingManager->id === $id)
            return true;
        return $this->reportingManager->canBeSeenBy($id);
    }

    public function hierarchyParentNames(&$supervisorList, $tillId) {
        if (!$this->reportingManager)
            return array_reverse($supervisorList);
        if ($this->reportingManager->id === $tillId)
            return array_reverse($supervisorList);
        array_push($supervisorList, $this->reportingManager->name);
        return $this->reportingManager->hierarchyParentNames($supervisorList, $tillId);
    }

    public function getReportingUserIds() {
        return $this->reportees()->pluck('id');
    }

    public function reportingTos() {
        return $this->hasMany('App\Models\UserReportingTo', 'user_id');
    }

    public function employees() {
        return $this->hasMany('App\Models\EmployeeDemo', 'employee_id', 'id');
    }
    
    public function users() {
        return $this->hasMany('App\Models\User');
    }
    
    public function usersUserIds() {
        return $this->users()->pluck('id');
    }

    public function getAllReporteesAttribute()
    {
        return collect($this->flat_reportees($this));
    }

    function flat_reportees($model) {
        $result = [];
        foreach ($model->reportees as $child) {
          $result[] = $child;
          if ($child->reportees) {
            $result = array_merge($result, $this->flat_reportees($child));
          }
        }
        return $result;
    }

    public function sharedWith()
    {
        return $this->hasMany('App\Models\SharedProfile','shared_id','id');
    }

}

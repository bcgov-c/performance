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
use App\Models\EmployeeDemoJunior;
use App\Models\PreferredSupervisor;
use App\Models\PrimaryJob;

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
        'excused_flag',
        'excused_reason_id',
        'joining_date',
        'acctlock',
        'last_signon_at',
        'last_sync_at',
        'excused_updated_by',
        'excused_updated_at',
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
        'is_shared',
        'allow_inapp_notification',
        'allow_email_notification',
    ];

    public const is_excused = [
        1 => 'Yes',
        0 => 'No',
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
        return $this->belongsTo('App\Models\User', 'reporting_to')
        ->join('employee_demo', 'employee_demo.employee_id', 'users.employee_id')->whereNull('employee_demo.date_deleted');
    }

    public function reportingManagerRecursive() {
        return $this->reportingManager()->with('reportingManagerRecursive');
    }

    public function reportees() {
        return $this->hasMany('App\Models\User', 'reporting_to');
    }
    
    public function avaliableReportees() {
        return $this->reportees()
                ->join('employee_demo', 'employee_demo.employee_id', '=', 'users.employee_id')
                ->whereNull('employee_demo.date_deleted');
    }

    public function reporteesCount() {
        return $this->reportees()->count();
    }
    
    public function avaliableReporteesCount() {
        return $this->avaliableReportees()->count();
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
    
    public function getAvaliableReportingUserIds() {
        return $this->avaliableReportees()->pluck('id');
    }

    public function reportingTos() {
        return $this->hasMany('App\Models\UserReportingTo', 'user_id');
    }

    public function employees() {
        return $this->hasMany('App\Models\EmployeeDemo', 'employee_id', 'id');
    }
    
    public function employee_demo() {
        return $this->hasOne('App\Models\EmployeeDemo', 'employee_id', 'employee_id');
    }
    
    public function employee_demo_jr() {
        $instance = $this->hasOne('App\Models\EmployeeDemoJunior', 'employee_id', 'employee_id');
        $instance->getQuery()->orderBy('id', 'desc')->first();
        return $instance;
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

    public function userPreference()
    {
        return $this->belongsTo('App\Models\UserPreference','id','user_id')->withDefault();
    }

    public function getAllowInappNotificationAttribute() {

        $organization = EmployeeDemo::join('employee_demo_tree', 'employee_demo_tree.id', 'employee_demo.orgid')
            ->join('access_organizations', 'employee_demo_tree.organization_key', 'access_organizations.orgid')
            ->where('access_organizations.allow_inapp_msg', 'Y')
            ->where('employee_demo.employee_id', $this->employee_id)
            ->first(); 

        return ($organization ? true : false);                            

    }

    public function getAllowEmailNotificationAttribute() {

        if (env('PRCS_EMAIL_NOTIFICATION', null) != 'on') {
            return false;
        }

        $organization = EmployeeDemo::join('employee_demo_tree', 'employee_demo_tree.id', 'employee_demo.orgid')
            ->join('access_organizations', 'employee_demo_tree.organization_key', 'access_organizations.orgid')
            ->where('access_organizations.allow_email_msg', 'Y')
            ->where('employee_demo.employee_id', $this->employee_id)
            ->select('employee_demo.guid', 'access_organizations.organization')
            ->first(); 

        return ($organization ? true : false);                            

    }

    public function supervisorList() {
        return EmployeeDemo::join('positions AS p', 'employee_demo.position_number', 'p.position_nbr')
        ->join('employee_demo AS e', 'p.reports_to', 'e.position_number')
        ->join('users AS v', 'e.employee_id', 'v.employee_id')
        ->distinct()
        ->select('e.position_number', 'v.employee_id', 'v.name', 'v.id')
        ->whereNull('e.date_deleted')
        ->where('employee_demo.employee_id', $this->employee_id)
        ->orderBy('e.position_number')
        ->orderBy('v.name')
        ->get();
    }

    public function preferredSupervisor() {
        if ($this->employee_demo && $this->employee_demo->position_number) {
            return PreferredSupervisor::join('users AS u', 'u.employee_id', 'preferred_supervisor.supv_empl_id')
            ->select('preferred_supervisor.supv_empl_id', 'u.name', 'u.id')
            ->where('preferred_supervisor.employee_id', '=', $this->employee_id)
            ->where('preferred_supervisor.position_nbr', '=', $this->employee_demo->position_number)
            ->first();
        } else {
            return null;
        }
    }

    public function supervisorListCount() {
        return $this->supervisorList()->count();
    }

    public function validPreferredSupervisor() {
        $row = EmployeeDemo::join('positions AS p', 'employee_demo.position_number', 'p.position_nbr')
        ->join('employee_demo AS e', 'p.reports_to', 'e.position_number')
        ->select('e.employee_id')
        ->whereNull('e.date_deleted')
        ->where('employee_demo.employee_id', $this->employee_id)
        ->whereRaw("EXISTS (SELECT 1 FROM preferred_supervisor AS ps WHERE ps.employee_id = employee_demo.employee_id AND ps.position_nbr = employee_demo.position_number AND ps.supv_empl_id = e.employee_id)")
        ->first();
        return $row ? $row->employee_id : '';
    }

    // public function primaryJob() {
    //     return PrimaryJob::from('primary_jobs AS pj')
    //         ->join('employee_demo AS ed', function($join){
    //             $join->on(function($on){
    //                 $on->whereRaw('pj.employee_id = ed.employee_id')
    //                     ->whereRaw('pj.empl_record = ed.empl_record')
    //                     ->whereNull('ed.date_deleted');
    //             });
    //         })
    //         ->join('employee_demo_tree AS edt', 'edt.id', 'ed.orgid')
    //         ->where('pj.employee_id', $this->employee_id)
    //         ->selectRaw("pj.employee_id, pj.empl_record, CONCAT(ed.jobcode_desc, ' - ', edt.organization) AS job")
    //         ->first();
    // }

    public function primaryJob() {
        return User::from('users AS pj')
            ->join('employee_demo AS ed', function($join){
                $join->on(function($on){
                    $on->whereRaw('pj.employee_id = ed.employee_id')
                        ->whereRaw('pj.empl_record = ed.empl_record')
                        ->whereNull('ed.date_deleted');
                });
            })
            ->join('employee_demo_tree AS edt', 'edt.id', 'ed.orgid')
            ->where('pj.employee_id', $this->employee_id)
            ->selectRaw("pj.employee_id, pj.empl_record, CONCAT(ed.jobcode_desc, ' - ', edt.organization) AS job")
            ->first();
    }

    public function jobList() {
        return EmployeeDemo::from('employee_demo AS ed1')
            ->whereRaw("EXISTS (SELECT 1 FROM employee_demo AS ed2 WHERE ed2.employee_id = ed1.employee_id AND ed2.empl_record <> ed1.empl_record AND ed2.date_deleted IS NULL)")
            ->join('employee_demo_tree AS edt', 'edt.id', 'ed1.orgid')
            ->whereNull('ed1.date_deleted')
            ->where('ed1.employee_id', $this->employee_id)
            ->selectRaw("ed1.employee_id, ed1.empl_record, CONCAT(ed1.jobcode_desc, ' - ', edt.organization) AS job")
            ->get();
    }

    public function jobCount() {
        return $this->jobList->count();
    }

}

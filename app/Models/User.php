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
use App\Models\UsersAnnex;
use App\Models\EmployeeDemoJunior;
use App\Models\PreferredSupervisor;
use App\Models\EmployeeSupervisor;
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
        ->join('employee_demo', 'employee_demo.employee_id', 'users.employee_id')
        ->whereNull('employee_demo.date_deleted')
        ->whereRaw('employee_demo.pdp_excluded = 0');
    }

    public function reportingManagerRecursive() {
        return $this->reportingManager()->with('reportingManagerRecursive');
    }

    public function reportees() {
        return $this->hasMany('App\Models\User', 'reporting_to');
    }
    
    public function avaliableReportees() {
        $reportee_emplids = [];
        $reportee_emplids = UsersAnnex::whereRaw("reporting_to_employee_id = '{$this->employee_id}'")
            ->join('users', function ($on) {
                return $on->on('users.employee_id', 'users_annex.employee_id')
                    ->on('users.empl_record', 'users_annex.empl_record');
            })
            ->selectRaw("CONCAT(users_annex.employee_id, '-', users_annex.empl_record) AS combo_id")
            ->pluck('combo_id');
        
        return User::join('employee_demo as ed', function ($on) {
                return $on->on('ed.employee_id', 'users.employee_id')
                    ->on('ed.empl_record', 'users.empl_record')
                    ->whereNull('ed.date_deleted')
                    ->whereRaw('ed.pdp_excluded = 0');
            })
            ->whereIn(\DB::raw("CONCAT(users.employee_id, '-', users.empl_record)"), $reportee_emplids)
            ->whereNull('ed.date_deleted')
            ->select('users.id', 'users.name'); // Explicitly select the id from users table
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

    public function EmployeeSupervisor() {
        return $this->hasOne('App\Models\EmployeeSupervisor', 'user_id')->first();
    }

    public function overrideReportees() {
        return $this->hasMany('App\Models\EmployeeSupervisor', 'supervisor_id');
    }

    public function overrideReporteeUsers() {
        return $this->hasMany('App\Models\EmployeeSupervisor', 'supervisor_id')
            ->join('users', 'users.id', 'supervisor_id')
            ->selectRaw("
                employee_supervisor.*,
                users.id users_id,
                users.name users_name,
                users.email users_email,
                users.employee_id users_employee_id,
                users.empl_record users_empl_record,
                users.reporting_to users_reporting_to,
                users.joining_date users_joining_date,
                users.acctlock users_acctlock,
                users.last_signon_at users_last_signon_at,
                users.last_sync_at users_last_sync_at,
                users.created_at users_created_at,
                users.updated_at users_updated_at,
                users.excused_reason_id users_excused_reason_id,
                users.next_conversation_date users_next_conversation_date,
                users.due_date_paused users_due_date_paused,
                users.excused_flag users_excused_flag,
                users.excused_updated_by users_excused_updated_by,
                users.excused_updated_at users_excused_updated_at
            ")
            ->whereNull('employee_supervisor.deleted_at');
    }

    public function EmployeeManager() {
        return $this->hasMany('App\Models\EmployeeManager', 'employee_id', 'employee_id');
    }

    public function EmployeeManagerUserProfile() {
        return $this->hasMany('App\Models\EmployeeManager', 'employee_id', 'employee_id')
            ->join('users', 'users.employee_id', 'employee_managers.employee_id')
            ->selectRaw("
                employee_managers.*,
                users.id users_id,
                users.name users_name,
                users.email users_email,
                users.employee_id users_employee_id,
                users.empl_record users_empl_record,
                users.reporting_to users_reporting_to,
                users.joining_date users_joining_date,
                users.acctlock users_acctlock,
                users.last_signon_at users_last_signon_at,
                users.last_sync_at users_last_sync_at,
                users.created_at users_created_at,
                users.updated_at users_updated_at,
                users.excused_reason_id users_excused_reason_id,
                users.next_conversation_date users_next_conversation_date,
                users.due_date_paused users_due_date_paused,
                users.excused_flag users_excused_flag,
                users.excused_updated_by users_excused_updated_by,
                users.excused_updated_at users_excused_updated_at
            ");
    }

    public function EmployeeManagerSupervisorProfile() {
        return $this->hasMany('App\Models\EmployeeManager', 'employee_id', 'employee_id')
            ->join('users', 'users.employee_id', 'employee_managers.supervisor_emplid')
            ->selectRaw("
                employee_managers.*,
                users.id users_id,
                users.name users_name,
                users.email users_email,
                users.employee_id users_employee_id,
                users.empl_record users_empl_record,
                users.reporting_to users_reporting_to,
                users.joining_date users_joining_date,
                users.acctlock users_acctlock,
                users.last_signon_at users_last_signon_at,
                users.last_sync_at users_last_sync_at,
                users.created_at users_created_at,
                users.updated_at users_updated_at,
                users.excused_reason_id users_excused_reason_id,
                users.next_conversation_date users_next_conversation_date,
                users.due_date_paused users_due_date_paused,
                users.excused_flag users_excused_flag,
                users.excused_updated_by users_excused_updated_by,
                users.excused_updated_at users_excused_updated_at
            ");
    }

    public function EmployeeManagerSupervisors() {
        return $this->hasMany('App\Models\EmployeeManager', 'supervisor_emplid', 'employee_id');
    }

    public function EmployeeManagerSupervisorsUserProfile() {
        return $this->hasMany('App\Models\EmployeeManager', 'supervisor_emplid', 'employee_id')
            ->join('users', 'users.employee_id', 'employee_managers.employee_id')
            ->selectRaw("
                employee_managers.*,
                users.id users_id,
                users.name users_name,
                users.email users_email,
                users.employee_id users_employee_id,
                users.empl_record users_empl_record,
                users.reporting_to users_reporting_to,
                users.joining_date users_joining_date,
                users.acctlock users_acctlock,
                users.last_signon_at users_last_signon_at,
                users.last_sync_at users_last_sync_at,
                users.created_at users_created_at,
                users.updated_at users_updated_at,
                users.excused_reason_id users_excused_reason_id,
                users.next_conversation_date users_next_conversation_date,
                users.due_date_paused users_due_date_paused,
                users.excused_flag users_excused_flag,
                users.excused_updated_by users_excused_updated_by,
                users.excused_updated_at users_excused_updated_at
            ");
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

        // $organization = EmployeeDemo::join('employee_demo_tree', 'employee_demo_tree.id', 'employee_demo.orgid')
        //     ->join('access_organizations', 'employee_demo_tree.organization_key', 'access_organizations.orgid')
        //     ->where('access_organizations.allow_email_msg', 'Y')
        //     ->where('employee_demo.employee_id', $this->employee_id)
        //     ->select('employee_demo.guid', 'access_organizations.organization')
        //     ->first(); 

        $organization = UsersAnnex::join(\DB::raw('access_organizations USE INDEX (access_organizations_orgid_unique)'), function ($on1) {
                return $on1->on('access_organizations.orgid', 'users_annex.organization_key')
                    ->whereRaw("access_organizations.allow_email_msg = 'Y'");
            })
            ->where('users_annex.employee_id', $this->employee_id)
            ->whereNull('users_annex.jr_excused_type')
            ->first();

        return ($organization ? true : false);                            

    }

    public function supervisorList() {
        return EmployeeDemo::withoutGlobalScopes()
        ->join('positions AS p', 'employee_demo.position_number', 'p.position_nbr')
        ->join('employee_demo AS e', function($join1){
            return $join1->on(function($on1){
                return $on1->where('p.reports_to', 'e.position_number')
                ->whereRaw('e.pdp_excluded = 0');
            });
        })
        ->join('users AS v', 'e.employee_id', 'v.employee_id')
        ->distinct()
        ->select('e.position_number', 'v.employee_id', 'v.name', 'v.id')
        ->whereNull('e.date_deleted')
        ->where('employee_demo.employee_id', $this->employee_id)
        ->whereRaw('employee_demo.pdp_excluded = 0')
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

    public function supervisorListPrimaryJob() {
        $pJob = EmployeeSupervisor::from('employee_supervisor')
            ->join('users AS u', 'u.id', 'employee_supervisor.supervisor_id')
            ->join('employee_demo AS ed', function($join1){
                return $join1->on(function($on1){
                    return $on1->where('ed.employee_id', 'u.employee_id')
                    ->whereRaw('ed.pdp_excluded = 0');
                });
            })
            ->selectRaw("
                ed.position_number,
                u.employee_id,
                u.name AS user_name,
                ed.employee_name AS name,
                employee_supervisor.supervisor_id
            ")
            ->whereRaw("employee_supervisor.user_id = {$this->id}")
            ->whereNull('ed.date_deleted')
            ->distinct()
            ->get();
        if($pJob->count() == 0){
            $pJob = \DB::table('employee_managers AS em')
                ->join('users AS authu', 'authu.employee_id', 'em.employee_id')
                ->join('employee_demo AS authed', function($join1) {
                    return $join1->on('authed.employee_id', 'authu.employee_id')
                        ->on('authed.empl_record', 'authu.empl_record');
                })
                ->join('users AS u', 'u.employee_id', 'em.supervisor_emplid')
                ->selectRaw("
                    em.supervisor_position_number AS position_number,
                    em.supervisor_emplid AS employee_id,
                    u.name AS user_name,
                    em.supervisor_name AS name,
                    u.id AS supervisor_id
                ")
                ->whereRaw("em.employee_id = '{$this->employee_id}'")
                ->whereRaw('authed.position_number = em.position_number')
                ->whereRaw('authed.pdp_excluded = 0')
                ->whereNull('authed.date_deleted')
                ->get();
        }
        return $pJob;
    }

    public function supervisorListPrimaryJobCount() {
        return $this->supervisorListPrimaryJob()->count();
    }

    public function validPreferredSupervisor() {
        $row = EmployeeDemo::withoutGlobalScopes()
            ->from('employee_demo AS ed')
            ->join('users AS u', function($join){
                return $join->on(function($on){
                    $on->whereRaw('u.employee_id = ed.employee_id')
                        ->whereRaw('u.empl_record = ed.empl_record')
                        ->whereNull('ed.date_deleted')
                        ->whereRaw('ed.pdp_excluded = 0');
                });
            })
            ->join('positions AS p', 'ed.position_number', 'p.position_nbr')
            ->join('employee_demo AS e', 'p.reports_to', 'e.position_number')
            ->select('e.employee_id')
            ->whereNull('e.date_deleted')
            ->where('ed.employee_id', $this->employee_id)
            ->whereRaw('ed.pdp_excluded = 0')
            ->whereRaw('e.pdp_excluded = 0')
            ->whereRaw("EXISTS (SELECT 1 FROM preferred_supervisor AS ps WHERE ps.employee_id = ed.employee_id AND ps.position_nbr = ed.position_number AND ps.supv_empl_id = e.employee_id)")
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
                        ->whereNull('ed.date_deleted')
                        ->whereRaw('ed.pdp_excluded = 0');
                });
            })
            ->join('employee_demo_tree AS edt', 'edt.id', 'ed.orgid')
            ->where('pj.employee_id', $this->employee_id)
            ->selectRaw("pj.employee_id, pj.empl_record, CONCAT(edt.deptid, ' ', ed.jobcode_desc) AS job")
            ->first();
    }

    public function jobList() {
        return EmployeeDemo::withoutGlobalScopes()
            ->from('employee_demo AS ed1')
            ->whereRaw("EXISTS (SELECT 1 FROM employee_demo AS ed2 WHERE ed2.employee_id = ed1.employee_id AND ed2.empl_record <> ed1.empl_record AND ed2.date_deleted IS NULL)")
            ->join('employee_demo_tree AS edt', 'edt.id', 'ed1.orgid')
            ->whereNull('ed1.date_deleted')
            ->whereRaw('ed1.pdp_excluded = 0')
            ->where('ed1.employee_id', $this->employee_id)
            ->selectRaw("ed1.employee_id, ed1.empl_record, CONCAT(edt.deptid, ' ', ed1.jobcode_desc) AS job")
            ->get();
    }

    public function jobCount() {
        return $this->jobList->count();
    }

}

<?php

namespace App\Models;

use App\Scopes\NonPDPExcludedScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\Builder;
use App\Models\User;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class EmployeeDemo extends Model implements Auditable
{
    public $table = 'employee_demo';

    use HasFactory;
    use AuditableTrait;

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $fillable = [
        'id',
        'guid',
        'employee_id',
        'empl_record',
        'employee_first_name',
        'employee_last_name',
        'employee_status',
        'employee_email',
        'classification',
        'deptid',
        'Jobcode',
        'Job_title',
        'position_number',
        'position_title',
        'position_start_date',
        'manager_id',
        'manager_first_name',
        'manager_last_name',
        'organization',
        'job_indicator',
        'tgb_reg_district',
        'supervisor_position_title',
        'supervisor_position_start_date',
        'supervisor_emplid',
        'supervisor_position_number',
        'supervisor_name',
        'supervisor_email',
        'stateprovince',
        'sal_admin_plan',
        'public_service_act',
        'postal',
        'phone',
        'office_stateprovince',
        'office_postal',
        'office_phone',
        'office_location_code',
        'office_country',
        'office_city',
        'office_address2',
        'office_address',
        'employee_name',
        'employee_middle_name',
        'level4',
        'level3_branch',
        'level2_division',
        'level1_program',
        'jobcode_desc',
        'job_function_employee_group',
        'employee_status_long',
        'country',
        'city',
        'can_noc_code',
        'appointment_status',
        'address2',
        'address1',
        'paygroup',
        'occupationalgroup',
        'jobcodedescgroup',
        'job_function',
        'idir',
        'hire_dt',
        'empl_ctg',
        'empl_class',
        'effseq',
        'effdt',
        'classification_group',
        'business_unit',
        'created_at',
        'updated_at',
        'date_posted',
        'date_deleted',
        'date_updated',
        'date_created',
        'orgid',
        'pdp_excluded',
    ];

    protected $auditExclude = [
        'orgid',
        'pdp_excluded',
    ];

    protected static function boot()
    {
      parent::boot();
  
      static::addGlobalScope(new NonPDPExcludedScope);
    }
  
    public function users() {
        return $this->hasOne(User::class, 'employee_id', 'employee_id');
    }


}

<?php

namespace App\Models;

use App\Scopes\NonPDPExcludedScope;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ODSEmployeeDemo extends Model
{
    public $table = 'ods_employee_demo';

    protected $primaryKey = ['employee_id','empl_record'];

    public $incrementing = false;

    protected $fillable = [
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
        'jobcode_desc',
        'position_number',
        'position_start_date',
        'manager_id',
        'manager_first_name',
        'manager_last_name',
        'date_posted',
        'date_deleted',
        'date_updated',
        'date_created',
        'orgid'
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

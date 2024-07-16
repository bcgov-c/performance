<?php

namespace App\Models;

use App\Scopes\NonPDPExcludedScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\Builder;
use App\Models\User;

class EmployeeDemo extends Model
{
    // Use \Awobaz\Compoships\Compoships;

    public $table = 'employee_demo';

    use HasFactory;

    //protected $primaryKey = ['guid'];
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

    // public function dr_count() {
    //     return $this->belongsToMany('App\Models\User', 'users', 'id', 'supervisor_emplid')->count();
    // }

//     protected function setKeysForSaveQuery(Builder $query)
//     {
//         $keys = $this->getKeyName();
//         if(!is_array($keys)){
//             return parent::setKeysForSaveQuery($query);
//         }
//
//         foreach($keys as $keyName){
//             $query->where($keyName, '=', $this->getKeyForSaveQuery($keyName));
//         }
//
//         return $query;
//     }
//
//     protected function getKeyForSaveQuery($keyName = null)
//     {
//         if(is_null($keyName)){
//             $keyName = $this->getKeyName();
//         }
//
//         if (isset($this->original[$keyName])) {
//             return $this->original[$keyName];
//         }
//
//         return $this->getAttribute($keyName);
//     }

}

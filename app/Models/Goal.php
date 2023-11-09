<?php

namespace App\Models;

use App\Scopes\NonLibraryScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Goal extends Model implements Auditable
{
  use AuditableTrait, SoftDeletes;
  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'title',
    'goal_type_id',
    'start_date',
    'target_date',
    'what',
    'why',
    'how',
    'measure_of_success',
    'status',
    'user_id',
    'created_by',
    'created_at',
    'updated_at',
    'is_library',
    'is_mandatory',
    'by_admin',
    'display_name'
  ];


  protected $appends = [
    "start_date_human",
    "target_date_human",
    'mandatory_status_descr'
  ];

  /**
   * The attributes that should be cast to native types.
   *
   * @var array
   */
  protected $casts = [
    'start_date' => 'datetime',
    'target_date' => 'datetime',
  ];


  public const MANDATORY_STATUS_LIST = 
  [
      "" => "Suggested",
      "0" => "Suggested",
      "1" => "Mandatory",

  ];

  /* public function newQuery($excludeDeleted = true, $excludeLibrary = true)
  {
    if ($excludeLibrary) {
      return parent::newQuery($excludeDeleted)
      ->where('is_library', '=', 0);
    }
    return parent::newQuery($excludeDeleted);
  } */

  protected static function boot()
  {
    parent::boot();

    static::addGlobalScope(new NonLibraryScope);
  }


  public function goalType() {
    return $this->belongsTo('App\Models\GoalType')->select('name', 'id');
  }

  public function user() {
    return $this->belongsTo('App\Models\User')->select('name', 'id', 'email', 'reporting_to');
  }

  public function originalCreatedBy() {
    return $this->belongsTo('App\Models\User', 'created_by')->select('name', 'id', 'email', 'reporting_to');
  }

  public function comments()
  {
    // TODO: Order of comments
    //return $this->hasMany('App\Models\GoalComment')->whereNull('parent_id')->withTrashed()->orderBy('created_at','ASC')->limit(10);

    return $this->hasMany('App\Models\GoalComment')->whereNull('parent_id')->withTrashed()->orderBy('created_at','ASC');
  }

  public function getStartDateHumanAttribute() {
    return ($this->start_date) ?  $this->start_date->format('M d, Y') : null;
  }

  public function getTargetDateHumanAttribute()
  {
    return ($this->target_date) ? $this->target_date->format('M d, Y') : null;
  }

  public function sharedWith()
  {
    return $this->belongsToMany('App\Models\User', 'goals_shared_with', 'goal_id', 'user_id')->withTimestamps();
  }

  // public function sharedWithThruAdmin()
  // {
  //     return $this->join('employee_shares', 'goals.user_id', '=', 'employee_shares.user_id')
  //     ->whereIn('employee_shares.shared_element_id', ['B', 'G'])
  //     ->belongsToMany('App\Models\User', 'goals_shared_with', 'goal_id', 'shared_with_id')->withTimestamps();
  // }

  public function tags()
  {
    return $this->belongsToMany(Tag::class, 'goal_tags')->withTimestamps();
  }

  public function getMandatoryStatusDescrAttribute()
  {
      //return $this->designation_name();
      return array_key_exists($this->is_mandatory, self::MANDATORY_STATUS_LIST) ? self::MANDATORY_STATUS_LIST[$this->is_mandatory] : '';
  }

  public function transformAudit(array $data): array
    {

        if(session()->has('user_is_switched')) {
          $original_auth_id = session()->get('existing_user_id');
        } else {
          $original_auth_id = session()->has('original-auth-id') ? session()->get('original-auth-id') : Auth::id();
        }

        $data['original_auth_id'] =  $original_auth_id;

        return $data;
    }

}

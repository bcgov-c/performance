<?php

namespace App\Models;

use App\Scopes\NonLibraryScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

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
    'is_mandatory'
  ];


  protected $appends = [
    "start_date_human",
    "target_date_human"
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
    return $this->hasMany('App\Models\GoalComment')->whereNull('parent_id')->withTrashed()->orderBy('created_at','ASC')->limit(10);
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

  public function tags()
  {
    return $this->belongsToMany(Tag::class, 'goal_tags')->withTimestamps();
  }
}

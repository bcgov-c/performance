<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Route;


class DashboardNotification extends Model
{
  use SoftDeletes;
  protected $table = 'dashboard_notifications';

  protected $fillable = [
      'id',
      'user_id',
      'notification_type',
      'comment',
      'related_id',
      'status',
      'created_at',
      'updated_at',
      'deleted_at'
  ];
  
  protected $appends = ['url'];

  public function relatedGoal() {
      return $this->belongsTo(Goal::class, 'related_id')->withoutGlobalScopes();
  }
 
  public function conversation() {
      return $this->belongsTo(Conversation::class, 'related_id');
  }

  public function sharedProfile() {
        return $this->belongsTo(SharedProfile::class, 'related_id');
  }

  public function getUrlAttribute() {

        $url = '';
        switch ($this->notification_type) {
          case 'GC':
          case 'GR':
          case 'GK':
          case 'GS':
              $url = route("goal.show", $this->relatedGoal->id);
              break;
          case 'GB':
              // $url = route("goal.library.detail", $this->relatedGoal->id);
              $url = route("goal.library");
              break;
          case 'CA':
          case 'CS':
              $url = route("conversation.upcoming");
              break;
          case 'SP':
              $url = route("dashboard");
            break;
        }
      
      return $url;
    
  }


}

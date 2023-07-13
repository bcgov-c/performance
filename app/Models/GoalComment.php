<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class GoalComment extends Model implements Auditable
{
    use SoftDeletes, AuditableTrait;
    

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function replies() {
        return $this->hasMany('App\Models\GoalComment', 'parent_id')->withTrashed();
    }

    public function canBeDeleted() {
        if (!session()->has('original-auth-id')) {
            return ($this->user_id === Auth::id());
        } else {
            return ($this->user_id === session()->get('original-auth-id'));
        }
        
    }

    public function canBeEdited() {
        if (!session()->has('original-auth-id')) {
            return (!$this->trashed()) && $this->user_id === Auth::id();
        } else {
            return (!$this->trashed()) && $this->user_id === session()->get('original-auth-id');
        }
        
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

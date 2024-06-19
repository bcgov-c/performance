<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Audit extends Model
{
    use HasFactory;

    public const EVENT_TYPES = 
    [
        "created" => "created",
        "updated" => "updated",
        "deleted" => "deleted",
        "restored" => "restored"    ,
    ];

    
    public function audit_user()
    {
        return $this->hasOne(User::class, 'id', 'user_id' );
    }

    public function original_user() 
    {
        return $this->hasOne(User::class, 'id', 'original_auth_id');
    }

    public function goal()
    {
        return $this->hasOne(Goal::class, 'id', 'auditable_id' );
    }

    public function conversation()
    {
        return $this->hasOne(Conversation::class, 'id', 'auditable_id' );
    }


    

}

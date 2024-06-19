<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminOrgUser extends Model
{
    public $table = 'admin_org_users';

    use HasFactory;

    protected $fillable = [
        'granted_to_id', 'allowed_user_id', 'access_type',
        'admin_org_id', 'shared_profile_id'
    ];

}

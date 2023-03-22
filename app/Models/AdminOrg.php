<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminOrg extends Model
{
    public $table = 'admin_orgs';

    use HasFactory;

    protected $fillable = [
        'user_id', 
        'version', 
        'orgid',
        'organization', 
        'level1_program', 
        'level2_division', 
        'level3_branch', 
        'level4',
        'inherited'
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class AuthOrg extends Model
{

    public $table = 'auth_orgs';
    public $timestamps = true;
    public $incrementing = true;

    use HasFactory;

    protected $primaryKey = ['id'];

    protected $fillable = [
        'id',
        'type',
        'auth_id',
        'orgid',
    ];

}

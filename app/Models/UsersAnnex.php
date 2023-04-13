<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;

class UsersAnnex extends Authenticatable
{

    public $table = 'users_annex';
    public $timestamps = true;
    public $incrementing = false;


}

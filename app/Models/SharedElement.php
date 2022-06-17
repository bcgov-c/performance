<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SharedElement extends Model
{
    protected $table = 'shared_elements';

    public $timestamps = true;

    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'description'
    ];
}

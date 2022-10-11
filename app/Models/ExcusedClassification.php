<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExcusedClassification extends Model
{
    protected $table = 'excused_classifications';

    public $incrementing = true;
    public $timestamps = true;

    protected $fillable = [
        'jobcode',
        'jobcode_desc',
    ];
}

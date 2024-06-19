<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class GoalType extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
    'name',
    'order',
    'description'
    ];

    protected static function boot()
    {
        parent::boot();
     
        // Order by name ASC
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('order', 'asc');
        });
    }
    
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrganizationStatistics extends Model
{
    public $table = 'organization_statistics';
    public $timestamps = true;
    public $incrementing = true;

    protected $fillable = [
        'id',
        'orgid',
        'userdemojrview_groupcount',
        'created_at',
        'updated_at',
    ];
}

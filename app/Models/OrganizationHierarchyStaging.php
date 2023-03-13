<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class OrganizationHierarchyStaging extends Model
{
    public $table = 'ods_dept_org_hierarchy_stg';
    public $timestamps = true;
    public $incrementing = false;

    use HasFactory;

    protected $primaryKey = ['OrgID'];

    protected $fillable = [
        'OrgID',
        'HierarchyLevel',
        'ParentOrgHierarchyKey',
        'OrgHierarchyKey',
        'DepartmentID',
        'BusinessName', 
        'date_deleted',
        'date_updated',
    ];

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class OrganizationHierarchy extends Model
{
    public $table = 'ods_dept_org_hierarchy';
    public $timestamps = true;
    public $incrementing = false;

    use HasFactory;

    protected $primaryKey = ['orgid'];

    protected $fillable = [
        'orgid',
        'hlevel',
        'pkey',
        'okey',
        'deptid',
        'name', 
        'ulevel',
        'organization', 
        'organization_label', 
        'organization_deptid', 
        'organization_key', 
        'level1', 
        'level1_label', 
        'level1_deptid', 
        'level1_key', 
        'level2', 
        'level2_label', 
        'level2_deptid', 
        'level2_key', 
        'level3', 
        'level3_label', 
        'level3_deptid', 
        'level3_key', 
        'level4', 
        'level4_label', 
        'level4_deptid', 
        'level4_key', 
        'level5', 
        'level5_label', 
        'level5_deptid', 
        'level5_key', 
        'level6', 
        'level6_label', 
        'level6_deptid', 
        'level6_key', 
        'search_key', 
        'org_path', 
        'date_deleted',
        'date_updated',
        'exception',
        'exception_reason',
        'unallocated',
        'duplicate',
        'depts',
    ];

}

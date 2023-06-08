<?php

namespace App\Models;

use Kalnoy\Nestedset\NodeTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeeDemoTreeTemp extends Model
{
    use HasFactory, NodeTrait;

    public $table = 'employee_demo_tree_temp';

    protected $fillable = [
        'id', 
        'name', 
        'deptid', 
        'status', 
        'level', 
        'headcount', 
        'groupcount', 
        'organization', 
        'level1_program', 
        'level2_division',
        'level3_branch', 
        'level4', 
        'level5', 
        'organization_key', 
        'level1_key', 
        'level2_key',
        'level3_key', 
        'level4_key', 
        'level5_key', 
        'organization_deptid', 
        'level1_deptid', 
        'level2_deptid',
        'level3_deptid', 
        'level4_deptid', 
        'level5_deptid', 
        'organization_orgid', 
        'level1_orgid', 
        'level2_orgid',
        'level3_orgid', 
        'level4_orgid', 
        'level5_orgid', 
        'parent_id', 
    ];

}

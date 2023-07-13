<?php

namespace App\Models;

use App\Models\User;
use App\Models\EmployeeDemo;
use Kalnoy\Nestedset\NodeTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrganizationTree extends Model
{
    use HasFactory, NodeTrait;

    protected $fillable = [
        'name', 'status', 'level', 'deptid', 'organization', 'level1_program', 'level2_division',
        'level3_branch', 'level4', 'parent_id', 'no_of_employee', 
    ];

    // public function subcategory() {

    //     return $this->hasMany('App\Models\OrganizationTree', 'parent_id', 'id');
    // }


    public function employees() {

        $demoWhere = EmployeeDemo::where('organization_trees.id', $this->id );
  
        $sql_level0 = clone $demoWhere; 
        $sql_level0->join('organization_trees', function($join) {
            $join->on('employee_demo.organization', '=', 'organization_trees.organization')
                ->where('organization_trees.level', '=', 0);
            });
        $sql_level1 = clone $demoWhere; 
        $sql_level1->join('organization_trees', function($join) {
            $join->on('employee_demo.organization', '=', 'organization_trees.organization')
                ->on('employee_demo.level1_program', '=', 'organization_trees.level1_program')
                ->where('organization_trees.level', '=', 1);
            });
        $sql_level2 = clone $demoWhere; 
        $sql_level2->join('organization_trees', function($join) {
            $join->on('employee_demo.organization', '=', 'organization_trees.organization')
                ->on('employee_demo.level1_program', '=', 'organization_trees.level1_program')
                ->on('employee_demo.level2_division', '=', 'organization_trees.level2_division')
                ->where('organization_trees.level', '=', 2);    
            });    
        $sql_level3 = clone $demoWhere; 
        $sql_level3->join('organization_trees', function($join) {
            $join->on('employee_demo.organization', '=', 'organization_trees.organization')
                ->on('employee_demo.level1_program', '=', 'organization_trees.level1_program')
                ->on('employee_demo.level2_division', '=', 'organization_trees.level2_division')
                ->on('employee_demo.level3_branch', '=', 'organization_trees.level3_branch')
                ->where('organization_trees.level', '=', 3);    
            });
        $sql_level4 = clone $demoWhere; 
        $sql_level4->join('organization_trees', function($join) {
            $join->on('employee_demo.organization', '=', 'organization_trees.organization')
                ->on('employee_demo.level1_program', '=', 'organization_trees.level1_program')
                ->on('employee_demo.level2_division', '=', 'organization_trees.level2_division')
                ->on('employee_demo.level3_branch', '=', 'organization_trees.level3_branch')
                ->on('employee_demo.level4', '=', 'organization_trees.level4')
                ->where('organization_trees.level', '=', 4);
            });

        return $sql_level4->select()
            ->union( $sql_level3->select())
            ->union( $sql_level2->select())
            ->union( $sql_level1->select())
            ->union( $sql_level0->select()) 
            ->get()->sortBy('employee_name');

    }

    public function users() {

        $demoWhere = EmployeeDemo::join('users','employee_demo.employee_id', 'users.employee_id')
            ->where('organization_trees.id', $this->id );
  
        $sql_level0 = clone $demoWhere; 
        $sql_level0->join('organization_trees', function($join) {
            $join->on('employee_demo.organization', '=', 'organization_trees.organization')
                ->where('organization_trees.level', '=', 0);
            });
        $sql_level1 = clone $demoWhere; 
        $sql_level1->join('organization_trees', function($join) {
            $join->on('employee_demo.organization', '=', 'organization_trees.organization')
                ->on('employee_demo.level1_program', '=', 'organization_trees.level1_program')
                ->where('organization_trees.level', '=', 1);
            });
        $sql_level2 = clone $demoWhere; 
        $sql_level2->join('organization_trees', function($join) {
            $join->on('employee_demo.organization', '=', 'organization_trees.organization')
                ->on('employee_demo.level1_program', '=', 'organization_trees.level1_program')
                ->on('employee_demo.level2_division', '=', 'organization_trees.level2_division')
                ->where('organization_trees.level', '=', 2);    
            });    
        $sql_level3 = clone $demoWhere; 
        $sql_level3->join('organization_trees', function($join) {
            $join->on('employee_demo.organization', '=', 'organization_trees.organization')
                ->on('employee_demo.level1_program', '=', 'organization_trees.level1_program')
                ->on('employee_demo.level2_division', '=', 'organization_trees.level2_division')
                ->on('employee_demo.level3_branch', '=', 'organization_trees.level3_branch')
                ->where('organization_trees.level', '=', 3);    
            });
        $sql_level4 = clone $demoWhere; 
        $sql_level4->join('organization_trees', function($join) {
            $join->on('employee_demo.organization', '=', 'organization_trees.organization')
                ->on('employee_demo.level1_program', '=', 'organization_trees.level1_program')
                ->on('employee_demo.level2_division', '=', 'organization_trees.level2_division')
                ->on('employee_demo.level3_branch', '=', 'organization_trees.level3_branch')
                ->on('employee_demo.level4', '=', 'organization_trees.level4')
                ->where('organization_trees.level', '=', 4);
            });

        return $sql_level4->select()
            ->union( $sql_level3->select())
            ->union( $sql_level2->select())
            ->union( $sql_level1->select())
            ->union( $sql_level0->select()) 
            ->get();


    }

}

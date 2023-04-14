<?php

namespace App\Http\Controllers\HRAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmployeeDemoTree;
use App\Models\OrganizationTree;
use App\Models\AdminOrgTreeView;
use App\Models\AdminOrgs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HRAdminSharedController extends Controller
{
    public function getOrganizationsV2(Request $request) {
        $authId = Auth::id();
        $orgs = AdminOrgTreeView::select('orgid', 'name')
            ->where('version', \DB::raw(2))
            ->where('inherited', \DB::raw(0))
            ->where('user_id', \DB::raw($authId))
            ->where('level', \DB::raw(0))
            ->when($request->q, function ($q) use($request) { return $q->whereRaw("name LIKE '%{$request->q}%'"); });
        $orgsInherited = EmployeeDemoTree::select('organization_key AS orgid', 'organization AS name')
            ->when($request->q, function ($q) use($request) { return $q->whereRaw("organization LIKE '%{$request->q}%'"); })
            ->whereRaw("EXISTS (SELECT DISTINCT 1 FROM admin_orgs WHERE (orgid = organization_key OR orgid = level1_key OR orgid = level2_key OR orgid = level3_key OR orgid = level4_key) AND version = 2 AND inherited = 1 AND user_id = {$userid})");
        $orgs = $orgs->union($orgsInherited)
            ->distinct()
            ->orderby('name', 'asc')
            ->limit(300)
            ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) { $formatted_orgs[] = ['id' => $org->orgid, 'text' => $org->name]; }
        return response()->json($formatted_orgs);
    } 

    public function getProgramsV2(Request $request) {
        $authId = Auth::id();
        $orgs = AdminOrgTreeView::select('orgid', 'name')
            ->where('version', \DB::raw(2))
            ->where('inherited', \DB::raw(0))
            ->where('user_id', \DB::raw($authId))
            ->where('level', \DB::raw(1))
            ->when($request->q, function ($q) use($request) { return $q->whereRaw("name LIKE '%{$request->q}%'"); })
            ->when($request->level0, function ($q) use($request) { return $q->where('organization_key', $request->level0); });
        $orgsInherited = EmployeeDemoTree::select('level1_key AS orgid', 'level1_program AS name')
            ->when($request->q, function ($q) use($request) { return $q->whereRaw("level1_program LIKE '%{$request->q}%'"); })
            ->when($request->level0, function ($q) use($request) { return $q->where('organization_key', $request->level0); })
            ->whereRaw("EXISTS (SELECT DISTINCT 1 FROM admin_orgs WHERE (orgid = organization_key OR orgid = level1_key OR orgid = level2_key OR orgid = level3_key OR orgid = level4_key) AND version = 2 AND inherited = 1 AND user_id = {$userid})");
        $orgs = $orgs->union($orgsInherited)
            ->distinct()
            ->orderby('name', 'asc')
            ->limit(300)
            ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) { $formatted_orgs[] = ['id' => $org->orgid, 'text' => $org->name]; }
        return response()->json($formatted_orgs);
    } 

    public function getDivisionsV2(Request $request) {
        $authId = Auth::id();
        $orgs = AdminOrgTreeView::select('orgid', 'name')
            ->where('version', \DB::raw(2))
            ->where('inherited', \DB::raw(0))
            ->where('user_id', \DB::raw($authId))
            ->where('level', \DB::raw(2))
            ->when($request->q, function ($q) use($request) { return $q->whereRaw("name LIKE '%{$request->q}%'"); })
            ->when($request->level0, function ($q) use($request) { return $q->where('organization_key', $request->level0); })
            ->when($request->level1, function ($q) use($request) { return $q->where('level1_key', $request->level1); });
        $orgsInherited = EmployeeDemoTree::select('level2_key AS orgid', 'level2_division AS name')
            ->when($request->q, function ($q) use($request) { return $q->whereRaw("level2_division LIKE '%{$request->q}%'"); })
            ->when($request->level0, function ($q) use($request) { return $q->where('organization_key', $request->level0); })
            ->when($request->level1, function ($q) use($request) { return $q->where('level1_key', $request->level1); })
            ->whereRaw("EXISTS (SELECT DISTINCT 1 FROM admin_orgs WHERE (orgid = organization_key OR orgid = level1_key OR orgid = level2_key OR orgid = level3_key OR orgid = level4_key) AND version = 2 AND inherited = 1 AND user_id = {$userid})");
        $orgs = $orgs->union($orgsInherited)
            ->distinct()
            ->orderby('name', 'asc')
            ->limit(300)
            ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) { $formatted_orgs[] = ['id' => $org->orgid, 'text' => $org->name]; }
        return response()->json($formatted_orgs);
    } 

    public function getBranchesV2(Request $request) {
        $authid = Auth::id();
        $orgs = AdminOrgTreeView::select('orgid', 'name')
            ->where('version', \DB::raw(2))
            ->where('inherited', \DB::raw(0))
            ->where('user_id', \DB::raw($authid))
            ->where('level', \DB::raw(3))
            ->when($request->q, function ($q) use($request) { return $q->whereRaw("name LIKE '%{$request->q}%'"); })
            ->when($request->level0, function ($q) use($request) { return $q->where('organization_key', $request->level0); })
            ->when($request->level1, function ($q) use($request) { return $q->where('level1_key', $request->level1); })
            ->when($request->level2, function ($q) use($request) { return $q->where('level2_key', $request->level2); });
        $orgsInherited = EmployeeDemoTree::select('level3_key AS orgid', 'level3_branch AS name')
            ->when($request->q, function ($q) use($request) { return $q->whereRaw("level3_branch LIKE '%{$request->q}%'"); })
            ->when($request->level0, function ($q) use($request) { return $q->where('organization_key', $request->level0); })
            ->when($request->level1, function ($q) use($request) { return $q->where('level1_key', $request->level1); })
            ->when($request->level2, function ($q) use($request) { return $q->where('level2_key', $request->level2); })
            ->whereRaw("EXISTS (SELECT DISTINCT 1 FROM admin_orgs WHERE (orgid = organization_key OR orgid = level1_key OR orgid = level2_key OR orgid = level3_key OR orgid = level4_key) AND version = 2 AND inherited = 1 AND user_id = {$userid})");
        $orgs = $orgs->union($orgsInherited)
            ->distinct()
            ->orderby('name', 'asc')
            ->limit(300)
            ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) { $formatted_orgs[] = ['id' => $org->orgid, 'text' => $org->name]; }
        return response()->json($formatted_orgs);
    } 

    public function getLevel4V2(Request $request) {
        $authid = Auth::id();
        $orgs = AdminOrgTreeView::select('orgid', 'name')
            ->where('version', \DB::raw(2))
            ->where('inherited', \DB::raw(0))
            ->where('user_id', \DB::raw($authid))
            ->where('level', \DB::raw(4))
            ->when($request->q, function ($q) use($request) { return $q->whereRaw("name LIKE '%{$request->q}%'"); })
            ->when($request->level0, function ($q) use($request) { return $q->where('organization_key', $request->level0); })
            ->when($request->level1, function ($q) use($request) { return $q->where('level1_key', $request->level1); })
            ->when($request->level2, function ($q) use($request) { return $q->where('level2_key', $request->level2); })
            ->when($request->level3, function ($q) use($request) { return $q->where('level3_key', $request->level3); });
        $orgsInherited = EmployeeDemoTree::select('level4_key AS orgid', 'level4 AS name')
            ->when($request->q, function ($q) use($request) { return $q->whereRaw("level4 LIKE '%{$request->q}%'"); })
            ->when($request->level0, function ($q) use($request) { return $q->where('organization_key', $request->level0); })
            ->when($request->level1, function ($q) use($request) { return $q->where('level1_key', $request->level1); })
            ->when($request->level2, function ($q) use($request) { return $q->where('level2_key', $request->level2); })
            ->when($request->level3, function ($q) use($request) { return $q->where('level3_key', $request->level3); })
            ->whereRaw("EXISTS (SELECT DISTINCT 1 FROM admin_orgs WHERE (orgid = organization_key OR orgid = level1_key OR orgid = level2_key OR orgid = level3_key OR orgid = level4_key) AND version = 2 AND inherited = 1 AND user_id = {$userid})");
        $orgs = $orgs->union($orgsInherited)
            ->distinct()
            ->orderby('name', 'asc')
            ->limit(300)
            ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) { $formatted_orgs[] = ['id' => $org->orgid, 'text' => $org->name]; }
        return response()->json($formatted_orgs);
    } 

    public function egetOrganizationsV2(Request $request) {
        $orgs = EmployeeDemoTree::join('admin_orgs', 'admin_orgs.orgid', 'employee_demo_tree.id')
        ->whereRaw('admin_orgs.user_id = '.Auth::id())
        ->orderby('employee_demo_tree.name', 'asc')
        ->select('employee_demo_tree.id', 'employee_demo_tree.name')
        ->where('employee_demo_tree.level', 0)
        ->when($request->q, function ($q) use($request) { return $q->whereRaw("employee_demo_tree.name LIKE '%{$request->q}%'"); })
        ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) { $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name]; }
        return response()->json($formatted_orgs);
    } 

    public function egetProgramsV2(Request $request) {
        $orgs = EmployeeDemoTree::join('admin_orgs', 'admin_orgs.orgid', 'employee_demo_tree.id')
        ->whereRaw('admin_orgs.user_id = '.Auth::id())
        ->orderby('employee_demo_tree.name', 'asc')
        ->select('employee_demo_tree.id', 'employee_demo_tree.name')
        ->where('employee_demo_tree.level', 1)
        ->when($request->q, function ($q) use($request) { return $q->whereRaw("employee_demo_tree.name LIKE '%{$request->q}%'"); })
        ->when($request->elevel0, function ($q) use($request) { return $q->where('employee_demo_tree.organization_key', $request->elevel0); })
        ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) { $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name]; }
        return response()->json($formatted_orgs);
    } 

    public function egetDivisionsV2(Request $request) {
        $orgs = EmployeeDemoTree::join('admin_orgs', 'admin_orgs.orgid', 'employee_demo_tree.id')
        ->whereRaw('admin_orgs.user_id = '.Auth::id())
        ->orderby('employee_demo_tree.name', 'asc')
        ->select('employee_demo_tree.id', 'employee_demo_tree.name')
        ->where('employee_demo_tree.level', 2)
        ->when($request->q, function ($q) use($request) { return $q->whereRaw("employee_demo_tree.name LIKE '%{$request->q}%'"); })
        ->when($request->elevel0, function ($q) use($request) { return $q->where('employee_demo_tree.organization_key', $request->elevel0); })
        ->when($request->elevel1, function ($q) use($request) { return $q->where('employee_demo_tree.level1_key', $request->elevel1); })
        ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) { $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name]; }
        return response()->json($formatted_orgs);
    } 

    public function egetBranchesV2(Request $request) {
        $orgs = EmployeeDemoTree::join('admin_orgs', 'admin_orgs.orgid', 'employee_demo_tree.id')
        ->whereRaw('admin_orgs.user_id = '.Auth::id())
        ->orderby('employee_demo_tree.name', 'asc')
        ->select('employee_demo_tree.id', 'employee_demo_tree.name')
        ->where('employee_demo_tree.level', 3)
        ->when($request->q, function ($q) use($request) { return $q->whereRaw("employee_demo_tree.name LIKE '%{$request->q}%'"); })
        ->when($request->elevel0, function ($q) use($request) { return $q->where('employee_demo_tree.organization_key', $request->elevel0); })
        ->when($request->elevel1, function ($q) use($request) { return $q->where('employee_demo_tree.level1_key', $request->elevel1); })
        ->when($request->elevel2, function ($q) use($request) { return $q->where('employee_demo_tree.level2_key', $request->elevel2); })
        ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) { $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name]; }
        return response()->json($formatted_orgs);
    } 

    public function egetLevel4V2(Request $request) {
        $orgs = EmployeeDemoTree::join('admin_orgs', 'admin_orgs.orgid', 'employee_demo_tree.id')
        ->whereRaw('admin_orgs.user_id = '.Auth::id())
        ->orderby('employee_demo_tree.name', 'asc')
        ->select('employee_demo_tree.id', 'employee_demo_tree.name')
        ->where('employee_demo_tree.level', 4)
        ->when($request->q, function ($q) use($request) { return $q->whereRaw("employee_demo_tree.name LIKE '%{$request->q}%'"); })
        ->when($request->elevel0, function ($q) use($request) { return $q->where('employee_demo_tree.organization_key', $request->elevel0); })
        ->when($request->elevel1, function ($q) use($request) { return $q->where('employee_demo_tree.level1_key', $request->elevel1); })
        ->when($request->elevel2, function ($q) use($request) { return $q->where('employee_demo_tree.level2_key', $request->elevel2); })
        ->when($request->elevel3, function ($q) use($request) { return $q->where('employee_demo_tree.level3_key', $request->elevel3); })
        ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) { $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name]; }
        return response()->json($formatted_orgs);
    } 

    public function agetOrganizationsV2(Request $request) {
        $orgs = EmployeeDemoTree::join('admin_orgs', 'admin_orgs.orgid', 'employee_demo_tree.id')
        ->whereRaw('admin_orgs.user_id = '.Auth::id())
        ->orderby('employee_demo_tree.name', 'asc')
        ->select('employee_demo_tree.id', 'employee_demo_tree.name')
        ->where('employee_demo_tree.level', 0)
        ->when($request->q, function ($q) use($request) { return $q->whereRaw("employee_demo_tree.name LIKE '%{$request->q}%'"); })
        ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) { $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name]; }
        return response()->json($formatted_orgs);
    } 

    public function agetProgramsV2(Request $request) {
        $orgs = EmployeeDemoTree::join('admin_orgs', 'admin_orgs.orgid', 'employee_demo_tree.id')
        ->whereRaw('admin_orgs.user_id = '.Auth::id())
        ->orderby('employee_demo_tree.name', 'asc')
        ->select('employee_demo_tree.id', 'employee_demo_tree.name')
        ->where('employee_demo_tree.level', 1)
        ->when($request->q, function ($q) use($request) { return $q->whereRaw("employee_demo_tree.name LIKE '%{$request->q}%'"); })
        ->when($request->alevel0, function ($q) use($request) { return $q->where('employee_demo_tree.organization_key', $request->alevel0); })
        ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) { $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name]; }
        return response()->json($formatted_orgs);
    } 

    public function agetDivisionsV2(Request $request) {
        $orgs = EmployeeDemoTree::join('admin_orgs', 'admin_orgs.orgid', 'employee_demo_tree.id')
        ->whereRaw('admin_orgs.user_id = '.Auth::id())
        ->orderby('employee_demo_tree.name', 'asc')
        ->select('employee_demo_tree.id', 'employee_demo_tree.name')
        ->where('employee_demo_tree.level', 2)
        ->when($request->q, function ($q) use($request) { return $q->whereRaw("employee_demo_tree.name LIKE '%{$request->q}%'"); })
        ->when($request->alevel0, function ($q) use($request) { return $q->where('employee_demo_tree.organization_key', $request->alevel0); })
        ->when($request->alevel1, function ($q) use($request) { return $q->where('employee_demo_tree.level1_key', $request->alevel1); })
        ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) { $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name]; }
        return response()->json($formatted_orgs);
    } 

    public function agetBranchesV2(Request $request) {
        $orgs = EmployeeDemoTree::join('admin_orgs', 'admin_orgs.orgid', 'employee_demo_tree.id')
        ->whereRaw('admin_orgs.user_id = '.Auth::id())
        ->orderby('employee_demo_tree.name', 'asc')
        ->select('employee_demo_tree.id', 'employee_demo_tree.name')
        ->where('employee_demo_tree.level', 3)
        ->when($request->q, function ($q) use($request) { return $q->whereRaw("employee_demo_tree.name LIKE '%{$request->q}%'"); })
        ->when($request->alevel0, function ($q) use($request) { return $q->where('employee_demo_tree.organization_key', $request->alevel0); })
        ->when($request->alevel1, function ($q) use($request) { return $q->where('employee_demo_tree.level1_key', $request->alevel1); })
        ->when($request->alevel2, function ($q) use($request) { return $q->where('employee_demo_tree.level2_key', $request->alevel2); })
        ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) { $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name]; }
        return response()->json($formatted_orgs);
    } 

    public function agetLevel4V2(Request $request) {
        $orgs = EmployeeDemoTree::join('admin_orgs', 'admin_orgs.orgid', 'employee_demo_tree.id')
        ->whereRaw('admin_orgs.user_id = '.Auth::id())
        ->orderby('employee_demo_tree.name', 'asc')
        ->select('employee_demo_tree.id', 'employee_demo_tree.name')
        ->where('employee_demo_tree.level', 4)
        ->when($request->q, function ($q) use($request) { return $q->whereRaw("employee_demo_tree.name LIKE '%{$request->q}%'"); })
        ->when($request->alevel0, function ($q) use($request) { return $q->where('employee_demo_tree.organization_key', $request->alevel0); })
        ->when($request->alevel1, function ($q) use($request) { return $q->where('employee_demo_tree.level1_key', $request->alevel1); })
        ->when($request->alevel2, function ($q) use($request) { return $q->where('employee_demo_tree.level2_key', $request->alevel2); })
        ->when($request->alevel3, function ($q) use($request) { return $q->where('employee_demo_tree.level3_key', $request->alevel3); })
        ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) { $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name]; }
        return response()->json($formatted_orgs);
    } 


    


    public function getOrganizations(Request $request) {
        $orgs = OrganizationTree::join('admin_orgs', function ($j1) {
            $j1->on(function ($j1a) {
                $j1a->whereRAW('admin_orgs.organization = organization_trees.organization OR ((admin_orgs.organization = "" OR admin_orgs.organization IS NULL) AND (organization_trees.organization = "" OR organization_trees.organization IS NULL))');
            } )
            ->on(function ($j2a) {
                $j2a->whereRAW('admin_orgs.level1_program = organization_trees.level1_program OR ((admin_orgs.level1_program = "" OR admin_orgs.level1_program IS NULL) AND (organization_trees.level1_program = "" OR organization_trees.level1_program IS NULL))');
            } )
            ->on(function ($j3a) {
                $j3a->whereRAW('admin_orgs.level2_division = organization_trees.level2_division OR ((admin_orgs.level2_division = "" OR admin_orgs.level2_division IS NULL) AND (organization_trees.level2_division = "" OR organization_trees.level2_division IS NULL))');
            } )
            ->on(function ($j4a) {
                $j4a->whereRAW('admin_orgs.level3_branch = organization_trees.level3_branch OR ((admin_orgs.level3_branch = "" OR admin_orgs.level3_branch IS NULL) AND (organization_trees.level3_branch = "" OR organization_trees.level3_branch IS NULL))');
            } )
            ->on(function ($j5a) {
                $j5a->whereRAW('admin_orgs.level4 = organization_trees.level4 OR ((admin_orgs.level4 = "" OR admin_orgs.level4 IS NULL) AND (organization_trees.level4 = "" OR organization_trees.level4 IS NULL))');
            } );
        } )
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->orderby('organization_trees.name','asc')->select('organization_trees.id','organization_trees.name')
        ->where('organization_trees.level',0)
        ->when( $request->q , function ($q) use($request) {
            return $q->whereRaw("LOWER(name) LIKE '%" . strtolower($request->q) . "%'");
        })
        ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) {
            $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }
        return response()->json($formatted_orgs);
    } 

    public function getPrograms(Request $request) {
        $level0 = $request->level0 ? OrganizationTree::join('admin_orgs', function ($j1) {
            $j1->on(function ($j1a) {
                $j1a->whereRAW('admin_orgs.organization = organization_trees.organization OR ((admin_orgs.organization = "" OR admin_orgs.organization IS NULL) AND (organization_trees.organization = "" OR organization_trees.organization IS NULL))');
            } )
            ->on(function ($j2a) {
                $j2a->whereRAW('admin_orgs.level1_program = organization_trees.level1_program OR ((admin_orgs.level1_program = "" OR admin_orgs.level1_program IS NULL) AND (organization_trees.level1_program = "" OR organization_trees.level1_program IS NULL))');
            } )
            ->on(function ($j3a) {
                $j3a->whereRAW('admin_orgs.level2_division = organization_trees.level2_division OR ((admin_orgs.level2_division = "" OR admin_orgs.level2_division IS NULL) AND (organization_trees.level2_division = "" OR organization_trees.level2_division IS NULL))');
            } )
            ->on(function ($j4a) {
                $j4a->whereRAW('admin_orgs.level3_branch = organization_trees.level3_branch OR ((admin_orgs.level3_branch = "" OR admin_orgs.level3_branch IS NULL) AND (organization_trees.level3_branch = "" OR organization_trees.level3_branch IS NULL))');
            } )
            ->on(function ($j5a) {
                $j5a->whereRAW('admin_orgs.level4 = organization_trees.level4 OR ((admin_orgs.level4 = "" OR admin_orgs.level4 IS NULL) AND (organization_trees.level4 = "" OR organization_trees.level4 IS NULL))');
            } );
        } )
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id',$request->level0)->first() : null;
        $orgs = OrganizationTree::join('admin_orgs', function ($j1) {
            $j1->on(function ($j1a) {
                $j1a->whereRAW('admin_orgs.organization = organization_trees.organization OR ((admin_orgs.organization = "" OR admin_orgs.organization IS NULL) AND (organization_trees.organization = "" OR organization_trees.organization IS NULL))');
            } )
            ->on(function ($j2a) {
                $j2a->whereRAW('admin_orgs.level1_program = organization_trees.level1_program OR ((admin_orgs.level1_program = "" OR admin_orgs.level1_program IS NULL) AND (organization_trees.level1_program = "" OR organization_trees.level1_program IS NULL))');
            } )
            ->on(function ($j3a) {
                $j3a->whereRAW('admin_orgs.level2_division = organization_trees.level2_division OR ((admin_orgs.level2_division = "" OR admin_orgs.level2_division IS NULL) AND (organization_trees.level2_division = "" OR organization_trees.level2_division IS NULL))');
            } )
            ->on(function ($j4a) {
                $j4a->whereRAW('admin_orgs.level3_branch = organization_trees.level3_branch OR ((admin_orgs.level3_branch = "" OR admin_orgs.level3_branch IS NULL) AND (organization_trees.level3_branch = "" OR organization_trees.level3_branch IS NULL))');
            } )
            ->on(function ($j5a) {
                $j5a->whereRAW('admin_orgs.level4 = organization_trees.level4 OR ((admin_orgs.level4 = "" OR admin_orgs.level4 IS NULL) AND (organization_trees.level4 = "" OR organization_trees.level4 IS NULL))');
            } );
        } )
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->orderby('organization_trees.name','asc')->select(DB::raw('min(organization_trees.id) as id'),'organization_trees.name')
        ->where('organization_trees.level',1)
        ->when( $request->q , function ($q) use($request) {
            return $q->whereRaw("LOWER(organization_trees.name) LIKE '%" . strtolower($request->q) . "%'");
            })
        ->when( $level0 , function ($q) use($level0) {
            return $q->where('organization_trees.organization', $level0->name );
        })
        ->groupBy('organization_trees.name')
        ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) {
            $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }
        return response()->json($formatted_orgs);
    } 

    public function getDivisions(Request $request) {
        $level0 = $request->level0 ? OrganizationTree::join('admin_orgs', function ($j1) {
            $j1->on(function ($j1a) {
                $j1a->whereRAW('admin_orgs.organization = organization_trees.organization OR ((admin_orgs.organization = "" OR admin_orgs.organization IS NULL) AND (organization_trees.organization = "" OR organization_trees.organization IS NULL))');
            } )
            ->on(function ($j2a) {
                $j2a->whereRAW('admin_orgs.level1_program = organization_trees.level1_program OR ((admin_orgs.level1_program = "" OR admin_orgs.level1_program IS NULL) AND (organization_trees.level1_program = "" OR organization_trees.level1_program IS NULL))');
            } )
            ->on(function ($j3a) {
                $j3a->whereRAW('admin_orgs.level2_division = organization_trees.level2_division OR ((admin_orgs.level2_division = "" OR admin_orgs.level2_division IS NULL) AND (organization_trees.level2_division = "" OR organization_trees.level2_division IS NULL))');
            } )
            ->on(function ($j4a) {
                $j4a->whereRAW('admin_orgs.level3_branch = organization_trees.level3_branch OR ((admin_orgs.level3_branch = "" OR admin_orgs.level3_branch IS NULL) AND (organization_trees.level3_branch = "" OR organization_trees.level3_branch IS NULL))');
            } )
            ->on(function ($j5a) {
                $j5a->whereRAW('admin_orgs.level4 = organization_trees.level4 OR ((admin_orgs.level4 = "" OR admin_orgs.level4 IS NULL) AND (organization_trees.level4 = "" OR organization_trees.level4 IS NULL))');
            } );
        } )
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->level0)->first() : null;
        $level1 = $request->level1 ? OrganizationTree::join('admin_orgs', function ($j1) {
            $j1->on(function ($j1a) {
                $j1a->whereRAW('admin_orgs.organization = organization_trees.organization OR ((admin_orgs.organization = "" OR admin_orgs.organization IS NULL) AND (organization_trees.organization = "" OR organization_trees.organization IS NULL))');
            } )
            ->on(function ($j2a) {
                $j2a->whereRAW('admin_orgs.level1_program = organization_trees.level1_program OR ((admin_orgs.level1_program = "" OR admin_orgs.level1_program IS NULL) AND (organization_trees.level1_program = "" OR organization_trees.level1_program IS NULL))');
            } )
            ->on(function ($j3a) {
                $j3a->whereRAW('admin_orgs.level2_division = organization_trees.level2_division OR ((admin_orgs.level2_division = "" OR admin_orgs.level2_division IS NULL) AND (organization_trees.level2_division = "" OR organization_trees.level2_division IS NULL))');
            } )
            ->on(function ($j4a) {
                $j4a->whereRAW('admin_orgs.level3_branch = organization_trees.level3_branch OR ((admin_orgs.level3_branch = "" OR admin_orgs.level3_branch IS NULL) AND (organization_trees.level3_branch = "" OR organization_trees.level3_branch IS NULL))');
            } )
            ->on(function ($j5a) {
                $j5a->whereRAW('admin_orgs.level4 = organization_trees.level4 OR ((admin_orgs.level4 = "" OR admin_orgs.level4 IS NULL) AND (organization_trees.level4 = "" OR organization_trees.level4 IS NULL))');
            } );
        } )
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->level1)->first() : null;
        $orgs = OrganizationTree::join('admin_orgs', function ($j1) {
            $j1->on(function ($j1a) {
                $j1a->whereRAW('admin_orgs.organization = organization_trees.organization OR ((admin_orgs.organization = "" OR admin_orgs.organization IS NULL) AND (organization_trees.organization = "" OR organization_trees.organization IS NULL))');
            } )
            ->on(function ($j2a) {
                $j2a->whereRAW('admin_orgs.level1_program = organization_trees.level1_program OR ((admin_orgs.level1_program = "" OR admin_orgs.level1_program IS NULL) AND (organization_trees.level1_program = "" OR organization_trees.level1_program IS NULL))');
            } )
            ->on(function ($j3a) {
                $j3a->whereRAW('admin_orgs.level2_division = organization_trees.level2_division OR ((admin_orgs.level2_division = "" OR admin_orgs.level2_division IS NULL) AND (organization_trees.level2_division = "" OR organization_trees.level2_division IS NULL))');
            } )
            ->on(function ($j4a) {
                $j4a->whereRAW('admin_orgs.level3_branch = organization_trees.level3_branch OR ((admin_orgs.level3_branch = "" OR admin_orgs.level3_branch IS NULL) AND (organization_trees.level3_branch = "" OR organization_trees.level3_branch IS NULL))');
            } )
            ->on(function ($j5a) {
                $j5a->whereRAW('admin_orgs.level4 = organization_trees.level4 OR ((admin_orgs.level4 = "" OR admin_orgs.level4 IS NULL) AND (organization_trees.level4 = "" OR organization_trees.level4 IS NULL))');
            } );
        } )
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->orderby('organization_trees.name','asc')->select(DB::raw('min(organization_trees.id) as id'),'organization_trees.name')
            ->where('organization_trees.level',2)
            ->when( $request->q , function ($q) use($request) {
                return $q->whereRaw("LOWER(organization_trees.name) LIKE '%" . strtolower($request->q) . "%'");
                })
            ->when( $level0 , function ($q) use($level0) {
                return $q->where('organization_trees.organization', $level0->name) ;
            })
            ->when( $level1 , function ($q) use($level1) {
                return $q->where('organization_trees.level1_program', $level1->name );
            })
            ->groupBy('organization_trees.name')
            ->limit(300)
            ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) {
            $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }
        return response()->json($formatted_orgs);
    } 

    public function getBranches(Request $request) {
        $level0 = $request->level0 ? OrganizationTree::join('admin_orgs', function ($j1) {
            $j1->on(function ($j1a) {
                $j1a->whereRAW('admin_orgs.organization = organization_trees.organization OR ((admin_orgs.organization = "" OR admin_orgs.organization IS NULL) AND (organization_trees.organization = "" OR organization_trees.organization IS NULL))');
            } )
            ->on(function ($j2a) {
                $j2a->whereRAW('admin_orgs.level1_program = organization_trees.level1_program OR ((admin_orgs.level1_program = "" OR admin_orgs.level1_program IS NULL) AND (organization_trees.level1_program = "" OR organization_trees.level1_program IS NULL))');
            } )
            ->on(function ($j3a) {
                $j3a->whereRAW('admin_orgs.level2_division = organization_trees.level2_division OR ((admin_orgs.level2_division = "" OR admin_orgs.level2_division IS NULL) AND (organization_trees.level2_division = "" OR organization_trees.level2_division IS NULL))');
            } )
            ->on(function ($j4a) {
                $j4a->whereRAW('admin_orgs.level3_branch = organization_trees.level3_branch OR ((admin_orgs.level3_branch = "" OR admin_orgs.level3_branch IS NULL) AND (organization_trees.level3_branch = "" OR organization_trees.level3_branch IS NULL))');
            } )
            ->on(function ($j5a) {
                $j5a->whereRAW('admin_orgs.level4 = organization_trees.level4 OR ((admin_orgs.level4 = "" OR admin_orgs.level4 IS NULL) AND (organization_trees.level4 = "" OR organization_trees.level4 IS NULL))');
            } );
        } )
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->level0)->first() : null;
        $level1 = $request->level1 ? OrganizationTree::join('admin_orgs', function ($j1) {
            $j1->on(function ($j1a) {
                $j1a->whereRAW('admin_orgs.organization = organization_trees.organization OR ((admin_orgs.organization = "" OR admin_orgs.organization IS NULL) AND (organization_trees.organization = "" OR organization_trees.organization IS NULL))');
            } )
            ->on(function ($j2a) {
                $j2a->whereRAW('admin_orgs.level1_program = organization_trees.level1_program OR ((admin_orgs.level1_program = "" OR admin_orgs.level1_program IS NULL) AND (organization_trees.level1_program = "" OR organization_trees.level1_program IS NULL))');
            } )
            ->on(function ($j3a) {
                $j3a->whereRAW('admin_orgs.level2_division = organization_trees.level2_division OR ((admin_orgs.level2_division = "" OR admin_orgs.level2_division IS NULL) AND (organization_trees.level2_division = "" OR organization_trees.level2_division IS NULL))');
            } )
            ->on(function ($j4a) {
                $j4a->whereRAW('admin_orgs.level3_branch = organization_trees.level3_branch OR ((admin_orgs.level3_branch = "" OR admin_orgs.level3_branch IS NULL) AND (organization_trees.level3_branch = "" OR organization_trees.level3_branch IS NULL))');
            } )
            ->on(function ($j5a) {
                $j5a->whereRAW('admin_orgs.level4 = organization_trees.level4 OR ((admin_orgs.level4 = "" OR admin_orgs.level4 IS NULL) AND (organization_trees.level4 = "" OR organization_trees.level4 IS NULL))');
            } );
        } )
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->level1)->first() : null;
        $level2 = $request->level2 ? OrganizationTree::join('admin_orgs', function ($j1) {
            $j1->on(function ($j1a) {
                $j1a->whereRAW('admin_orgs.organization = organization_trees.organization OR ((admin_orgs.organization = "" OR admin_orgs.organization IS NULL) AND (organization_trees.organization = "" OR organization_trees.organization IS NULL))');
            } )
            ->on(function ($j2a) {
                $j2a->whereRAW('admin_orgs.level1_program = organization_trees.level1_program OR ((admin_orgs.level1_program = "" OR admin_orgs.level1_program IS NULL) AND (organization_trees.level1_program = "" OR organization_trees.level1_program IS NULL))');
            } )
            ->on(function ($j3a) {
                $j3a->whereRAW('admin_orgs.level2_division = organization_trees.level2_division OR ((admin_orgs.level2_division = "" OR admin_orgs.level2_division IS NULL) AND (organization_trees.level2_division = "" OR organization_trees.level2_division IS NULL))');
            } )
            ->on(function ($j4a) {
                $j4a->whereRAW('admin_orgs.level3_branch = organization_trees.level3_branch OR ((admin_orgs.level3_branch = "" OR admin_orgs.level3_branch IS NULL) AND (organization_trees.level3_branch = "" OR organization_trees.level3_branch IS NULL))');
            } )
            ->on(function ($j5a) {
                $j5a->whereRAW('admin_orgs.level4 = organization_trees.level4 OR ((admin_orgs.level4 = "" OR admin_orgs.level4 IS NULL) AND (organization_trees.level4 = "" OR organization_trees.level4 IS NULL))');
            } );
        } )
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->level2)->first() : null;
        $orgs = OrganizationTree::join('admin_orgs', function ($j1) {
            $j1->on(function ($j1a) {
                $j1a->whereRAW('admin_orgs.organization = organization_trees.organization OR ((admin_orgs.organization = "" OR admin_orgs.organization IS NULL) AND (organization_trees.organization = "" OR organization_trees.organization IS NULL))');
            } )
            ->on(function ($j2a) {
                $j2a->whereRAW('admin_orgs.level1_program = organization_trees.level1_program OR ((admin_orgs.level1_program = "" OR admin_orgs.level1_program IS NULL) AND (organization_trees.level1_program = "" OR organization_trees.level1_program IS NULL))');
            } )
            ->on(function ($j3a) {
                $j3a->whereRAW('admin_orgs.level2_division = organization_trees.level2_division OR ((admin_orgs.level2_division = "" OR admin_orgs.level2_division IS NULL) AND (organization_trees.level2_division = "" OR organization_trees.level2_division IS NULL))');
            } )
            ->on(function ($j4a) {
                $j4a->whereRAW('admin_orgs.level3_branch = organization_trees.level3_branch OR ((admin_orgs.level3_branch = "" OR admin_orgs.level3_branch IS NULL) AND (organization_trees.level3_branch = "" OR organization_trees.level3_branch IS NULL))');
            } )
            ->on(function ($j5a) {
                $j5a->whereRAW('admin_orgs.level4 = organization_trees.level4 OR ((admin_orgs.level4 = "" OR admin_orgs.level4 IS NULL) AND (organization_trees.level4 = "" OR organization_trees.level4 IS NULL))');
            } );
        } )
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->orderby('organization_trees.name','asc')->select(DB::raw('min(organization_trees.id) as id'),'organization_trees.name')
            ->where('organization_trees.level',3)
            ->when( $request->q , function ($q) use($request) {
                return $q->whereRaw("LOWER(organization_trees.name) LIKE '%" . strtolower($request->q) . "%'");
                })
            ->when( $level0 , function ($q) use($level0) {
                return $q->where('organization_trees.organization', $level0->name) ;
            })
            ->when( $level1 , function ($q) use($level1) {
                return $q->where('organization_trees.level1_program', $level1->name );
            })
            ->when( $level2 , function ($q) use($level2) {
                return $q->where('organization_trees.level2_division', $level2->name );
            })
            ->groupBy('organization_trees.name')
            ->limit(300)
            ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) {
            $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }
        return response()->json($formatted_orgs);
    } 

    public function getLevel4(Request $request) {
        $level0 = $request->level0 ? OrganizationTree::join('admin_orgs', function ($j1) {
            $j1->on(function ($j1a) {
                $j1a->whereRAW('admin_orgs.organization = organization_trees.organization OR ((admin_orgs.organization = "" OR admin_orgs.organization IS NULL) AND (organization_trees.organization = "" OR organization_trees.organization IS NULL))');
            } )
            ->on(function ($j2a) {
                $j2a->whereRAW('admin_orgs.level1_program = organization_trees.level1_program OR ((admin_orgs.level1_program = "" OR admin_orgs.level1_program IS NULL) AND (organization_trees.level1_program = "" OR organization_trees.level1_program IS NULL))');
            } )
            ->on(function ($j3a) {
                $j3a->whereRAW('admin_orgs.level2_division = organization_trees.level2_division OR ((admin_orgs.level2_division = "" OR admin_orgs.level2_division IS NULL) AND (organization_trees.level2_division = "" OR organization_trees.level2_division IS NULL))');
            } )
            ->on(function ($j4a) {
                $j4a->whereRAW('admin_orgs.level3_branch = organization_trees.level3_branch OR ((admin_orgs.level3_branch = "" OR admin_orgs.level3_branch IS NULL) AND (organization_trees.level3_branch = "" OR organization_trees.level3_branch IS NULL))');
            } )
            ->on(function ($j5a) {
                $j5a->whereRAW('admin_orgs.level4 = organization_trees.level4 OR ((admin_orgs.level4 = "" OR admin_orgs.level4 IS NULL) AND (organization_trees.level4 = "" OR organization_trees.level4 IS NULL))');
            } );
        } )
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->level0)->first() : null;
        $level1 = $request->level1 ? OrganizationTree::join('admin_orgs', function ($j1) {
            $j1->on(function ($j1a) {
                $j1a->whereRAW('admin_orgs.organization = organization_trees.organization OR ((admin_orgs.organization = "" OR admin_orgs.organization IS NULL) AND (organization_trees.organization = "" OR organization_trees.organization IS NULL))');
            } )
            ->on(function ($j2a) {
                $j2a->whereRAW('admin_orgs.level1_program = organization_trees.level1_program OR ((admin_orgs.level1_program = "" OR admin_orgs.level1_program IS NULL) AND (organization_trees.level1_program = "" OR organization_trees.level1_program IS NULL))');
            } )
            ->on(function ($j3a) {
                $j3a->whereRAW('admin_orgs.level2_division = organization_trees.level2_division OR ((admin_orgs.level2_division = "" OR admin_orgs.level2_division IS NULL) AND (organization_trees.level2_division = "" OR organization_trees.level2_division IS NULL))');
            } )
            ->on(function ($j4a) {
                $j4a->whereRAW('admin_orgs.level3_branch = organization_trees.level3_branch OR ((admin_orgs.level3_branch = "" OR admin_orgs.level3_branch IS NULL) AND (organization_trees.level3_branch = "" OR organization_trees.level3_branch IS NULL))');
            } )
            ->on(function ($j5a) {
                $j5a->whereRAW('admin_orgs.level4 = organization_trees.level4 OR ((admin_orgs.level4 = "" OR admin_orgs.level4 IS NULL) AND (organization_trees.level4 = "" OR organization_trees.level4 IS NULL))');
            } );
        } )
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->level1)->first() : null;
        $level2 = $request->level2 ? OrganizationTree::join('admin_orgs', function ($j1) {
            $j1->on(function ($j1a) {
                $j1a->whereRAW('admin_orgs.organization = organization_trees.organization OR ((admin_orgs.organization = "" OR admin_orgs.organization IS NULL) AND (organization_trees.organization = "" OR organization_trees.organization IS NULL))');
            } )
            ->on(function ($j2a) {
                $j2a->whereRAW('admin_orgs.level1_program = organization_trees.level1_program OR ((admin_orgs.level1_program = "" OR admin_orgs.level1_program IS NULL) AND (organization_trees.level1_program = "" OR organization_trees.level1_program IS NULL))');
            } )
            ->on(function ($j3a) {
                $j3a->whereRAW('admin_orgs.level2_division = organization_trees.level2_division OR ((admin_orgs.level2_division = "" OR admin_orgs.level2_division IS NULL) AND (organization_trees.level2_division = "" OR organization_trees.level2_division IS NULL))');
            } )
            ->on(function ($j4a) {
                $j4a->whereRAW('admin_orgs.level3_branch = organization_trees.level3_branch OR ((admin_orgs.level3_branch = "" OR admin_orgs.level3_branch IS NULL) AND (organization_trees.level3_branch = "" OR organization_trees.level3_branch IS NULL))');
            } )
            ->on(function ($j5a) {
                $j5a->whereRAW('admin_orgs.level4 = organization_trees.level4 OR ((admin_orgs.level4 = "" OR admin_orgs.level4 IS NULL) AND (organization_trees.level4 = "" OR organization_trees.level4 IS NULL))');
            } );
        } )
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->level2)->first() : null;
        $level3 = $request->level3 ? OrganizationTree::join('admin_orgs', function ($j1) {
            $j1->on(function ($j1a) {
                $j1a->whereRAW('admin_orgs.organization = organization_trees.organization OR ((admin_orgs.organization = "" OR admin_orgs.organization IS NULL) AND (organization_trees.organization = "" OR organization_trees.organization IS NULL))');
            } )
            ->on(function ($j2a) {
                $j2a->whereRAW('admin_orgs.level1_program = organization_trees.level1_program OR ((admin_orgs.level1_program = "" OR admin_orgs.level1_program IS NULL) AND (organization_trees.level1_program = "" OR organization_trees.level1_program IS NULL))');
            } )
            ->on(function ($j3a) {
                $j3a->whereRAW('admin_orgs.level2_division = organization_trees.level2_division OR ((admin_orgs.level2_division = "" OR admin_orgs.level2_division IS NULL) AND (organization_trees.level2_division = "" OR organization_trees.level2_division IS NULL))');
            } )
            ->on(function ($j4a) {
                $j4a->whereRAW('admin_orgs.level3_branch = organization_trees.level3_branch OR ((admin_orgs.level3_branch = "" OR admin_orgs.level3_branch IS NULL) AND (organization_trees.level3_branch = "" OR organization_trees.level3_branch IS NULL))');
            } )
            ->on(function ($j5a) {
                $j5a->whereRAW('admin_orgs.level4 = organization_trees.level4 OR ((admin_orgs.level4 = "" OR admin_orgs.level4 IS NULL) AND (organization_trees.level4 = "" OR organization_trees.level4 IS NULL))');
            } );
        } )
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->level3)->first() : null;
        $orgs = OrganizationTree::join('admin_orgs', function ($j1) {
            $j1->on(function ($j1a) {
                $j1a->whereRAW('admin_orgs.organization = organization_trees.organization OR ((admin_orgs.organization = "" OR admin_orgs.organization IS NULL) AND (organization_trees.organization = "" OR organization_trees.organization IS NULL))');
            } )
            ->on(function ($j2a) {
                $j2a->whereRAW('admin_orgs.level1_program = organization_trees.level1_program OR ((admin_orgs.level1_program = "" OR admin_orgs.level1_program IS NULL) AND (organization_trees.level1_program = "" OR organization_trees.level1_program IS NULL))');
            } )
            ->on(function ($j3a) {
                $j3a->whereRAW('admin_orgs.level2_division = organization_trees.level2_division OR ((admin_orgs.level2_division = "" OR admin_orgs.level2_division IS NULL) AND (organization_trees.level2_division = "" OR organization_trees.level2_division IS NULL))');
            } )
            ->on(function ($j4a) {
                $j4a->whereRAW('admin_orgs.level3_branch = organization_trees.level3_branch OR ((admin_orgs.level3_branch = "" OR admin_orgs.level3_branch IS NULL) AND (organization_trees.level3_branch = "" OR organization_trees.level3_branch IS NULL))');
            } )
            ->on(function ($j5a) {
                $j5a->whereRAW('admin_orgs.level4 = organization_trees.level4 OR ((admin_orgs.level4 = "" OR admin_orgs.level4 IS NULL) AND (organization_trees.level4 = "" OR organization_trees.level4 IS NULL))');
            } );
        } )
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->orderby('organization_trees.name','asc')->select(DB::raw('min(organization_trees.id) as id'),'organization_trees.name')
            ->where('organization_trees.level',4)
            ->when( $request->q , function ($q) use($request) {
                return $q->whereRaw("LOWER(organization_trees.name) LIKE '%" . strtolower($request->q) . "%'");
                })
            ->when( $level0 , function ($q) use($level0) {
                return $q->where('organization_trees.organization', $level0->name) ;
            })
            ->when( $level1 , function ($q) use($level1) {
                return $q->where('organization_trees.level1_program', $level1->name );
            })
            ->when( $level2 , function ($q) use($level2) {
                return $q->where('organization_trees.level2_division', $level2->name );
            })
            ->when( $level3 , function ($q) use($level3) {
                return $q->where('organization_trees.level3_branch', $level3->name );
            })
            ->groupBy('organization_trees.name')
            ->limit(300)
            ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) {
            $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }
        return response()->json($formatted_orgs);
    } 

    public function egetOrganizations(Request $request) {
        $orgs = OrganizationTree::join('admin_orgs', function ($j1) {
            $j1->on(function ($j1a) {
                $j1a->whereRAW('admin_orgs.organization = organization_trees.organization OR ((admin_orgs.organization = "" OR admin_orgs.organization IS NULL) AND (organization_trees.organization = "" OR organization_trees.organization IS NULL))');
            } )
            ->on(function ($j2a) {
                $j2a->whereRAW('admin_orgs.level1_program = organization_trees.level1_program OR ((admin_orgs.level1_program = "" OR admin_orgs.level1_program IS NULL) AND (organization_trees.level1_program = "" OR organization_trees.level1_program IS NULL))');
            } )
            ->on(function ($j3a) {
                $j3a->whereRAW('admin_orgs.level2_division = organization_trees.level2_division OR ((admin_orgs.level2_division = "" OR admin_orgs.level2_division IS NULL) AND (organization_trees.level2_division = "" OR organization_trees.level2_division IS NULL))');
            } )
            ->on(function ($j4a) {
                $j4a->whereRAW('admin_orgs.level3_branch = organization_trees.level3_branch OR ((admin_orgs.level3_branch = "" OR admin_orgs.level3_branch IS NULL) AND (organization_trees.level3_branch = "" OR organization_trees.level3_branch IS NULL))');
            } )
            ->on(function ($j5a) {
                $j5a->whereRAW('admin_orgs.level4 = organization_trees.level4 OR ((admin_orgs.level4 = "" OR admin_orgs.level4 IS NULL) AND (organization_trees.level4 = "" OR organization_trees.level4 IS NULL))');
            } );
        } )
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->orderby('organization_trees.name','asc')->select('organization_trees.id','organization_trees.name')
        ->where('organization_trees.level',0)
        ->when( $request->q , function ($q) use($request) {
            return $q->whereRaw("LOWER(name) LIKE '%" . strtolower($request->q) . "%'");
        })
        ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) {
            $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }
        return response()->json($formatted_orgs);
    } 

    public function egetPrograms(Request $request) {
        $level0 = $request->elevel0 ? OrganizationTree::join('admin_orgs', function ($j1) {
            $j1->on(function ($j1a) {
                $j1a->whereRAW('admin_orgs.organization = organization_trees.organization OR ((admin_orgs.organization = "" OR admin_orgs.organization IS NULL) AND (organization_trees.organization = "" OR organization_trees.organization IS NULL))');
            } )
            ->on(function ($j2a) {
                $j2a->whereRAW('admin_orgs.level1_program = organization_trees.level1_program OR ((admin_orgs.level1_program = "" OR admin_orgs.level1_program IS NULL) AND (organization_trees.level1_program = "" OR organization_trees.level1_program IS NULL))');
            } )
            ->on(function ($j3a) {
                $j3a->whereRAW('admin_orgs.level2_division = organization_trees.level2_division OR ((admin_orgs.level2_division = "" OR admin_orgs.level2_division IS NULL) AND (organization_trees.level2_division = "" OR organization_trees.level2_division IS NULL))');
            } )
            ->on(function ($j4a) {
                $j4a->whereRAW('admin_orgs.level3_branch = organization_trees.level3_branch OR ((admin_orgs.level3_branch = "" OR admin_orgs.level3_branch IS NULL) AND (organization_trees.level3_branch = "" OR organization_trees.level3_branch IS NULL))');
            } )
            ->on(function ($j5a) {
                $j5a->whereRAW('admin_orgs.level4 = organization_trees.level4 OR ((admin_orgs.level4 = "" OR admin_orgs.level4 IS NULL) AND (organization_trees.level4 = "" OR organization_trees.level4 IS NULL))');
            } );
        } )
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id',$request->elevel0)->first() : null;
        $orgs = OrganizationTree::join('admin_orgs', function ($j1) {
            $j1->on(function ($j1a) {
                $j1a->whereRAW('admin_orgs.organization = organization_trees.organization OR ((admin_orgs.organization = "" OR admin_orgs.organization IS NULL) AND (organization_trees.organization = "" OR organization_trees.organization IS NULL))');
            } )
            ->on(function ($j2a) {
                $j2a->whereRAW('admin_orgs.level1_program = organization_trees.level1_program OR ((admin_orgs.level1_program = "" OR admin_orgs.level1_program IS NULL) AND (organization_trees.level1_program = "" OR organization_trees.level1_program IS NULL))');
            } )
            ->on(function ($j3a) {
                $j3a->whereRAW('admin_orgs.level2_division = organization_trees.level2_division OR ((admin_orgs.level2_division = "" OR admin_orgs.level2_division IS NULL) AND (organization_trees.level2_division = "" OR organization_trees.level2_division IS NULL))');
            } )
            ->on(function ($j4a) {
                $j4a->whereRAW('admin_orgs.level3_branch = organization_trees.level3_branch OR ((admin_orgs.level3_branch = "" OR admin_orgs.level3_branch IS NULL) AND (organization_trees.level3_branch = "" OR organization_trees.level3_branch IS NULL))');
            } )
            ->on(function ($j5a) {
                $j5a->whereRAW('admin_orgs.level4 = organization_trees.level4 OR ((admin_orgs.level4 = "" OR admin_orgs.level4 IS NULL) AND (organization_trees.level4 = "" OR organization_trees.level4 IS NULL))');
            } );
        } )
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->orderby('organization_trees.name','asc')->select(DB::raw('min(organization_trees.id) as id'),'organization_trees.name')
        ->where('organization_trees.level',1)
        ->when( $request->q , function ($q) use($request) {
            return $q->whereRaw("LOWER(organization_trees.name) LIKE '%" . strtolower($request->q) . "%'");
            })
        ->when( $level0 , function ($q) use($level0) {
            return $q->where('organization_trees.organization', $level0->name );
        })
        ->groupBy('organization_trees.name')
        ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) {
            $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }
        return response()->json($formatted_orgs);
    } 

    public function egetDivisions(Request $request) {
        $level0 = $request->elevel0 ? OrganizationTree::join('admin_orgs', function ($j1) {
            $j1->on(function ($j1a) {
                $j1a->whereRAW('admin_orgs.organization = organization_trees.organization OR ((admin_orgs.organization = "" OR admin_orgs.organization IS NULL) AND (organization_trees.organization = "" OR organization_trees.organization IS NULL))');
            } )
            ->on(function ($j2a) {
                $j2a->whereRAW('admin_orgs.level1_program = organization_trees.level1_program OR ((admin_orgs.level1_program = "" OR admin_orgs.level1_program IS NULL) AND (organization_trees.level1_program = "" OR organization_trees.level1_program IS NULL))');
            } )
            ->on(function ($j3a) {
                $j3a->whereRAW('admin_orgs.level2_division = organization_trees.level2_division OR ((admin_orgs.level2_division = "" OR admin_orgs.level2_division IS NULL) AND (organization_trees.level2_division = "" OR organization_trees.level2_division IS NULL))');
            } )
            ->on(function ($j4a) {
                $j4a->whereRAW('admin_orgs.level3_branch = organization_trees.level3_branch OR ((admin_orgs.level3_branch = "" OR admin_orgs.level3_branch IS NULL) AND (organization_trees.level3_branch = "" OR organization_trees.level3_branch IS NULL))');
            } )
            ->on(function ($j5a) {
                $j5a->whereRAW('admin_orgs.level4 = organization_trees.level4 OR ((admin_orgs.level4 = "" OR admin_orgs.level4 IS NULL) AND (organization_trees.level4 = "" OR organization_trees.level4 IS NULL))');
            } );
        } )
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->elevel0)->first() : null;
        $level1 = $request->elevel1 ? OrganizationTree::join('admin_orgs', function ($j1) {
            $j1->on(function ($j1a) {
                $j1a->whereRAW('admin_orgs.organization = organization_trees.organization OR ((admin_orgs.organization = "" OR admin_orgs.organization IS NULL) AND (organization_trees.organization = "" OR organization_trees.organization IS NULL))');
            } )
            ->on(function ($j2a) {
                $j2a->whereRAW('admin_orgs.level1_program = organization_trees.level1_program OR ((admin_orgs.level1_program = "" OR admin_orgs.level1_program IS NULL) AND (organization_trees.level1_program = "" OR organization_trees.level1_program IS NULL))');
            } )
            ->on(function ($j3a) {
                $j3a->whereRAW('admin_orgs.level2_division = organization_trees.level2_division OR ((admin_orgs.level2_division = "" OR admin_orgs.level2_division IS NULL) AND (organization_trees.level2_division = "" OR organization_trees.level2_division IS NULL))');
            } )
            ->on(function ($j4a) {
                $j4a->whereRAW('admin_orgs.level3_branch = organization_trees.level3_branch OR ((admin_orgs.level3_branch = "" OR admin_orgs.level3_branch IS NULL) AND (organization_trees.level3_branch = "" OR organization_trees.level3_branch IS NULL))');
            } )
            ->on(function ($j5a) {
                $j5a->whereRAW('admin_orgs.level4 = organization_trees.level4 OR ((admin_orgs.level4 = "" OR admin_orgs.level4 IS NULL) AND (organization_trees.level4 = "" OR organization_trees.level4 IS NULL))');
            } );
        } )
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->elevel1)->first() : null;
        $orgs = OrganizationTree::join('admin_orgs', function ($j1) {
            $j1->on(function ($j1a) {
                $j1a->whereRAW('admin_orgs.organization = organization_trees.organization OR ((admin_orgs.organization = "" OR admin_orgs.organization IS NULL) AND (organization_trees.organization = "" OR organization_trees.organization IS NULL))');
            } )
            ->on(function ($j2a) {
                $j2a->whereRAW('admin_orgs.level1_program = organization_trees.level1_program OR ((admin_orgs.level1_program = "" OR admin_orgs.level1_program IS NULL) AND (organization_trees.level1_program = "" OR organization_trees.level1_program IS NULL))');
            } )
            ->on(function ($j3a) {
                $j3a->whereRAW('admin_orgs.level2_division = organization_trees.level2_division OR ((admin_orgs.level2_division = "" OR admin_orgs.level2_division IS NULL) AND (organization_trees.level2_division = "" OR organization_trees.level2_division IS NULL))');
            } )
            ->on(function ($j4a) {
                $j4a->whereRAW('admin_orgs.level3_branch = organization_trees.level3_branch OR ((admin_orgs.level3_branch = "" OR admin_orgs.level3_branch IS NULL) AND (organization_trees.level3_branch = "" OR organization_trees.level3_branch IS NULL))');
            } )
            ->on(function ($j5a) {
                $j5a->whereRAW('admin_orgs.level4 = organization_trees.level4 OR ((admin_orgs.level4 = "" OR admin_orgs.level4 IS NULL) AND (organization_trees.level4 = "" OR organization_trees.level4 IS NULL))');
            } );
        } )
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->orderby('organization_trees.name','asc')->select(DB::raw('min(organization_trees.id) as id'),'organization_trees.name')
            ->where('organization_trees.level',2)
            ->when( $request->q , function ($q) use($request) {
                return $q->whereRaw("LOWER(organization_trees.name) LIKE '%" . strtolower($request->q) . "%'");
                })
            ->when( $level0 , function ($q) use($level0) {
                return $q->where('organization_trees.organization', $level0->name) ;
            })
            ->when( $level1 , function ($q) use($level1) {
                return $q->where('organization_trees.level1_program', $level1->name );
            })
            ->groupBy('organization_trees.name')
            ->limit(300)
            ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) {
            $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }
        return response()->json($formatted_orgs);
    } 

    public function egetBranches(Request $request) {
        $level0 = $request->elevel0 ? OrganizationTree::join('admin_orgs', function ($j1) {
            $j1->on(function ($j1a) {
                $j1a->whereRAW('admin_orgs.organization = organization_trees.organization OR ((admin_orgs.organization = "" OR admin_orgs.organization IS NULL) AND (organization_trees.organization = "" OR organization_trees.organization IS NULL))');
            } )
            ->on(function ($j2a) {
                $j2a->whereRAW('admin_orgs.level1_program = organization_trees.level1_program OR ((admin_orgs.level1_program = "" OR admin_orgs.level1_program IS NULL) AND (organization_trees.level1_program = "" OR organization_trees.level1_program IS NULL))');
            } )
            ->on(function ($j3a) {
                $j3a->whereRAW('admin_orgs.level2_division = organization_trees.level2_division OR ((admin_orgs.level2_division = "" OR admin_orgs.level2_division IS NULL) AND (organization_trees.level2_division = "" OR organization_trees.level2_division IS NULL))');
            } )
            ->on(function ($j4a) {
                $j4a->whereRAW('admin_orgs.level3_branch = organization_trees.level3_branch OR ((admin_orgs.level3_branch = "" OR admin_orgs.level3_branch IS NULL) AND (organization_trees.level3_branch = "" OR organization_trees.level3_branch IS NULL))');
            } )
            ->on(function ($j5a) {
                $j5a->whereRAW('admin_orgs.level4 = organization_trees.level4 OR ((admin_orgs.level4 = "" OR admin_orgs.level4 IS NULL) AND (organization_trees.level4 = "" OR organization_trees.level4 IS NULL))');
            } );
        } )
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->elevel0)->first() : null;
        $level1 = $request->elevel1 ? OrganizationTree::join('admin_orgs', function ($j1) {
            $j1->on(function ($j1a) {
                $j1a->whereRAW('admin_orgs.organization = organization_trees.organization OR ((admin_orgs.organization = "" OR admin_orgs.organization IS NULL) AND (organization_trees.organization = "" OR organization_trees.organization IS NULL))');
            } )
            ->on(function ($j2a) {
                $j2a->whereRAW('admin_orgs.level1_program = organization_trees.level1_program OR ((admin_orgs.level1_program = "" OR admin_orgs.level1_program IS NULL) AND (organization_trees.level1_program = "" OR organization_trees.level1_program IS NULL))');
            } )
            ->on(function ($j3a) {
                $j3a->whereRAW('admin_orgs.level2_division = organization_trees.level2_division OR ((admin_orgs.level2_division = "" OR admin_orgs.level2_division IS NULL) AND (organization_trees.level2_division = "" OR organization_trees.level2_division IS NULL))');
            } )
            ->on(function ($j4a) {
                $j4a->whereRAW('admin_orgs.level3_branch = organization_trees.level3_branch OR ((admin_orgs.level3_branch = "" OR admin_orgs.level3_branch IS NULL) AND (organization_trees.level3_branch = "" OR organization_trees.level3_branch IS NULL))');
            } )
            ->on(function ($j5a) {
                $j5a->whereRAW('admin_orgs.level4 = organization_trees.level4 OR ((admin_orgs.level4 = "" OR admin_orgs.level4 IS NULL) AND (organization_trees.level4 = "" OR organization_trees.level4 IS NULL))');
            } );
        } )
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->elevel1)->first() : null;
        $level2 = $request->elevel2 ? OrganizationTree::join('admin_orgs', function ($j1) {
            $j1->on(function ($j1a) {
                $j1a->whereRAW('admin_orgs.organization = organization_trees.organization OR ((admin_orgs.organization = "" OR admin_orgs.organization IS NULL) AND (organization_trees.organization = "" OR organization_trees.organization IS NULL))');
            } )
            ->on(function ($j2a) {
                $j2a->whereRAW('admin_orgs.level1_program = organization_trees.level1_program OR ((admin_orgs.level1_program = "" OR admin_orgs.level1_program IS NULL) AND (organization_trees.level1_program = "" OR organization_trees.level1_program IS NULL))');
            } )
            ->on(function ($j3a) {
                $j3a->whereRAW('admin_orgs.level2_division = organization_trees.level2_division OR ((admin_orgs.level2_division = "" OR admin_orgs.level2_division IS NULL) AND (organization_trees.level2_division = "" OR organization_trees.level2_division IS NULL))');
            } )
            ->on(function ($j4a) {
                $j4a->whereRAW('admin_orgs.level3_branch = organization_trees.level3_branch OR ((admin_orgs.level3_branch = "" OR admin_orgs.level3_branch IS NULL) AND (organization_trees.level3_branch = "" OR organization_trees.level3_branch IS NULL))');
            } )
            ->on(function ($j5a) {
                $j5a->whereRAW('admin_orgs.level4 = organization_trees.level4 OR ((admin_orgs.level4 = "" OR admin_orgs.level4 IS NULL) AND (organization_trees.level4 = "" OR organization_trees.level4 IS NULL))');
            } );
        } )
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->elevel2)->first() : null;
        $orgs = OrganizationTree::join('admin_orgs', function ($j1) {
            $j1->on(function ($j1a) {
                $j1a->whereRAW('admin_orgs.organization = organization_trees.organization OR ((admin_orgs.organization = "" OR admin_orgs.organization IS NULL) AND (organization_trees.organization = "" OR organization_trees.organization IS NULL))');
            } )
            ->on(function ($j2a) {
                $j2a->whereRAW('admin_orgs.level1_program = organization_trees.level1_program OR ((admin_orgs.level1_program = "" OR admin_orgs.level1_program IS NULL) AND (organization_trees.level1_program = "" OR organization_trees.level1_program IS NULL))');
            } )
            ->on(function ($j3a) {
                $j3a->whereRAW('admin_orgs.level2_division = organization_trees.level2_division OR ((admin_orgs.level2_division = "" OR admin_orgs.level2_division IS NULL) AND (organization_trees.level2_division = "" OR organization_trees.level2_division IS NULL))');
            } )
            ->on(function ($j4a) {
                $j4a->whereRAW('admin_orgs.level3_branch = organization_trees.level3_branch OR ((admin_orgs.level3_branch = "" OR admin_orgs.level3_branch IS NULL) AND (organization_trees.level3_branch = "" OR organization_trees.level3_branch IS NULL))');
            } )
            ->on(function ($j5a) {
                $j5a->whereRAW('admin_orgs.level4 = organization_trees.level4 OR ((admin_orgs.level4 = "" OR admin_orgs.level4 IS NULL) AND (organization_trees.level4 = "" OR organization_trees.level4 IS NULL))');
            } );
        } )
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->orderby('organization_trees.name','asc')->select(DB::raw('min(organization_trees.id) as id'),'organization_trees.name')
            ->where('organization_trees.level',3)
            ->when( $request->q , function ($q) use($request) {
                return $q->whereRaw("LOWER(organization_trees.name) LIKE '%" . strtolower($request->q) . "%'");
                })
            ->when( $level0 , function ($q) use($level0) {
                return $q->where('organization_trees.organization', $level0->name) ;
            })
            ->when( $level1 , function ($q) use($level1) {
                return $q->where('organization_trees.level1_program', $level1->name );
            })
            ->when( $level2 , function ($q) use($level2) {
                return $q->where('organization_trees.level2_division', $level2->name );
            })
            ->groupBy('organization_trees.name')
            ->limit(300)
            ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) {
            $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }
        return response()->json($formatted_orgs);
    } 

    public function egetLevel4(Request $request) {
        $level0 = $request->elevel0 ? OrganizationTree::join('admin_orgs', function ($j1) {
            $j1->on(function ($j1a) {
                $j1a->whereRAW('admin_orgs.organization = organization_trees.organization OR ((admin_orgs.organization = "" OR admin_orgs.organization IS NULL) AND (organization_trees.organization = "" OR organization_trees.organization IS NULL))');
            } )
            ->on(function ($j2a) {
                $j2a->whereRAW('admin_orgs.level1_program = organization_trees.level1_program OR ((admin_orgs.level1_program = "" OR admin_orgs.level1_program IS NULL) AND (organization_trees.level1_program = "" OR organization_trees.level1_program IS NULL))');
            } )
            ->on(function ($j3a) {
                $j3a->whereRAW('admin_orgs.level2_division = organization_trees.level2_division OR ((admin_orgs.level2_division = "" OR admin_orgs.level2_division IS NULL) AND (organization_trees.level2_division = "" OR organization_trees.level2_division IS NULL))');
            } )
            ->on(function ($j4a) {
                $j4a->whereRAW('admin_orgs.level3_branch = organization_trees.level3_branch OR ((admin_orgs.level3_branch = "" OR admin_orgs.level3_branch IS NULL) AND (organization_trees.level3_branch = "" OR organization_trees.level3_branch IS NULL))');
            } )
            ->on(function ($j5a) {
                $j5a->whereRAW('admin_orgs.level4 = organization_trees.level4 OR ((admin_orgs.level4 = "" OR admin_orgs.level4 IS NULL) AND (organization_trees.level4 = "" OR organization_trees.level4 IS NULL))');
            } );
        } )
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->elevel0)->first() : null;
        $level1 = $request->elevel1 ? OrganizationTree::join('admin_orgs', function ($j1) {
            $j1->on(function ($j1a) {
                $j1a->whereRAW('admin_orgs.organization = organization_trees.organization OR ((admin_orgs.organization = "" OR admin_orgs.organization IS NULL) AND (organization_trees.organization = "" OR organization_trees.organization IS NULL))');
            } )
            ->on(function ($j2a) {
                $j2a->whereRAW('admin_orgs.level1_program = organization_trees.level1_program OR ((admin_orgs.level1_program = "" OR admin_orgs.level1_program IS NULL) AND (organization_trees.level1_program = "" OR organization_trees.level1_program IS NULL))');
            } )
            ->on(function ($j3a) {
                $j3a->whereRAW('admin_orgs.level2_division = organization_trees.level2_division OR ((admin_orgs.level2_division = "" OR admin_orgs.level2_division IS NULL) AND (organization_trees.level2_division = "" OR organization_trees.level2_division IS NULL))');
            } )
            ->on(function ($j4a) {
                $j4a->whereRAW('admin_orgs.level3_branch = organization_trees.level3_branch OR ((admin_orgs.level3_branch = "" OR admin_orgs.level3_branch IS NULL) AND (organization_trees.level3_branch = "" OR organization_trees.level3_branch IS NULL))');
            } )
            ->on(function ($j5a) {
                $j5a->whereRAW('admin_orgs.level4 = organization_trees.level4 OR ((admin_orgs.level4 = "" OR admin_orgs.level4 IS NULL) AND (organization_trees.level4 = "" OR organization_trees.level4 IS NULL))');
            } );
        } )
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->elevel1)->first() : null;
        $level2 = $request->elevel2 ? OrganizationTree::join('admin_orgs', function ($j1) {
            $j1->on(function ($j1a) {
                $j1a->whereRAW('admin_orgs.organization = organization_trees.organization OR ((admin_orgs.organization = "" OR admin_orgs.organization IS NULL) AND (organization_trees.organization = "" OR organization_trees.organization IS NULL))');
            } )
            ->on(function ($j2a) {
                $j2a->whereRAW('admin_orgs.level1_program = organization_trees.level1_program OR ((admin_orgs.level1_program = "" OR admin_orgs.level1_program IS NULL) AND (organization_trees.level1_program = "" OR organization_trees.level1_program IS NULL))');
            } )
            ->on(function ($j3a) {
                $j3a->whereRAW('admin_orgs.level2_division = organization_trees.level2_division OR ((admin_orgs.level2_division = "" OR admin_orgs.level2_division IS NULL) AND (organization_trees.level2_division = "" OR organization_trees.level2_division IS NULL))');
            } )
            ->on(function ($j4a) {
                $j4a->whereRAW('admin_orgs.level3_branch = organization_trees.level3_branch OR ((admin_orgs.level3_branch = "" OR admin_orgs.level3_branch IS NULL) AND (organization_trees.level3_branch = "" OR organization_trees.level3_branch IS NULL))');
            } )
            ->on(function ($j5a) {
                $j5a->whereRAW('admin_orgs.level4 = organization_trees.level4 OR ((admin_orgs.level4 = "" OR admin_orgs.level4 IS NULL) AND (organization_trees.level4 = "" OR organization_trees.level4 IS NULL))');
            } );
        } )
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->elevel2)->first() : null;
        $level3 = $request->elevel3 ? OrganizationTree::join('admin_orgs', function ($j1) {
            $j1->on(function ($j1a) {
                $j1a->whereRAW('admin_orgs.organization = organization_trees.organization OR ((admin_orgs.organization = "" OR admin_orgs.organization IS NULL) AND (organization_trees.organization = "" OR organization_trees.organization IS NULL))');
            } )
            ->on(function ($j2a) {
                $j2a->whereRAW('admin_orgs.level1_program = organization_trees.level1_program OR ((admin_orgs.level1_program = "" OR admin_orgs.level1_program IS NULL) AND (organization_trees.level1_program = "" OR organization_trees.level1_program IS NULL))');
            } )
            ->on(function ($j3a) {
                $j3a->whereRAW('admin_orgs.level2_division = organization_trees.level2_division OR ((admin_orgs.level2_division = "" OR admin_orgs.level2_division IS NULL) AND (organization_trees.level2_division = "" OR organization_trees.level2_division IS NULL))');
            } )
            ->on(function ($j4a) {
                $j4a->whereRAW('admin_orgs.level3_branch = organization_trees.level3_branch OR ((admin_orgs.level3_branch = "" OR admin_orgs.level3_branch IS NULL) AND (organization_trees.level3_branch = "" OR organization_trees.level3_branch IS NULL))');
            } )
            ->on(function ($j5a) {
                $j5a->whereRAW('admin_orgs.level4 = organization_trees.level4 OR ((admin_orgs.level4 = "" OR admin_orgs.level4 IS NULL) AND (organization_trees.level4 = "" OR organization_trees.level4 IS NULL))');
            } );
        } )
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->elevel3)->first() : null;
        $orgs = OrganizationTree::join('admin_orgs', function ($j1) {
            $j1->on(function ($j1a) {
                $j1a->whereRAW('admin_orgs.organization = organization_trees.organization OR ((admin_orgs.organization = "" OR admin_orgs.organization IS NULL) AND (organization_trees.organization = "" OR organization_trees.organization IS NULL))');
            } )
            ->on(function ($j2a) {
                $j2a->whereRAW('admin_orgs.level1_program = organization_trees.level1_program OR ((admin_orgs.level1_program = "" OR admin_orgs.level1_program IS NULL) AND (organization_trees.level1_program = "" OR organization_trees.level1_program IS NULL))');
            } )
            ->on(function ($j3a) {
                $j3a->whereRAW('admin_orgs.level2_division = organization_trees.level2_division OR ((admin_orgs.level2_division = "" OR admin_orgs.level2_division IS NULL) AND (organization_trees.level2_division = "" OR organization_trees.level2_division IS NULL))');
            } )
            ->on(function ($j4a) {
                $j4a->whereRAW('admin_orgs.level3_branch = organization_trees.level3_branch OR ((admin_orgs.level3_branch = "" OR admin_orgs.level3_branch IS NULL) AND (organization_trees.level3_branch = "" OR organization_trees.level3_branch IS NULL))');
            } )
            ->on(function ($j5a) {
                $j5a->whereRAW('admin_orgs.level4 = organization_trees.level4 OR ((admin_orgs.level4 = "" OR admin_orgs.level4 IS NULL) AND (organization_trees.level4 = "" OR organization_trees.level4 IS NULL))');
            } );
        } )
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->orderby('organization_trees.name','asc')->select(DB::raw('min(organization_trees.id) as id'),'organization_trees.name')
            ->where('organization_trees.level',4)
            ->when( $request->q , function ($q) use($request) {
                return $q->whereRaw("LOWER(organization_trees.name) LIKE '%" . strtolower($request->q) . "%'");
                })
            ->when( $level0 , function ($q) use($level0) {
                return $q->where('organization_trees.organization', $level0->name) ;
            })
            ->when( $level1 , function ($q) use($level1) {
                return $q->where('organization_trees.level1_program', $level1->name );
            })
            ->when( $level2 , function ($q) use($level2) {
                return $q->where('organization_trees.level2_division', $level2->name );
            })
            ->when( $level3 , function ($q) use($level3) {
                return $q->where('organization_trees.level3_branch', $level3->name );
            })
            ->groupBy('organization_trees.name')
            ->limit(300)
            ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) {
            $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }
        return response()->json($formatted_orgs);
    } 

    public function agetOrganizations(Request $request) {
        $orgs = OrganizationTree::join('admin_orgs', function ($j1) {
            $j1->on(function ($j1a) {
                $j1a->whereRAW('admin_orgs.organization = organization_trees.organization OR ((admin_orgs.organization = "" OR admin_orgs.organization IS NULL) AND (organization_trees.organization = "" OR organization_trees.organization IS NULL))');
            } )
            ->on(function ($j2a) {
                $j2a->whereRAW('admin_orgs.level1_program = organization_trees.level1_program OR ((admin_orgs.level1_program = "" OR admin_orgs.level1_program IS NULL) AND (organization_trees.level1_program = "" OR organization_trees.level1_program IS NULL))');
            } )
            ->on(function ($j3a) {
                $j3a->whereRAW('admin_orgs.level2_division = organization_trees.level2_division OR ((admin_orgs.level2_division = "" OR admin_orgs.level2_division IS NULL) AND (organization_trees.level2_division = "" OR organization_trees.level2_division IS NULL))');
            } )
            ->on(function ($j4a) {
                $j4a->whereRAW('admin_orgs.level3_branch = organization_trees.level3_branch OR ((admin_orgs.level3_branch = "" OR admin_orgs.level3_branch IS NULL) AND (organization_trees.level3_branch = "" OR organization_trees.level3_branch IS NULL))');
            } )
            ->on(function ($j5a) {
                $j5a->whereRAW('admin_orgs.level4 = organization_trees.level4 OR ((admin_orgs.level4 = "" OR admin_orgs.level4 IS NULL) AND (organization_trees.level4 = "" OR organization_trees.level4 IS NULL))');
            } );
        } )
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->orderby('organization_trees.name','asc')->select('organization_trees.id','organization_trees.name')
        ->where('organization_trees.level',0)
        ->when( $request->q , function ($q) use($request) {
            return $q->whereRaw("LOWER(name) LIKE '%" . strtolower($request->q) . "%'");
        })
        ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) {
            $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }
        return response()->json($formatted_orgs);
    } 

    public function agetPrograms(Request $request) {
        $level0 = $request->alevel0 ? OrganizationTree::join('admin_orgs', function ($j1) {
            $j1->on(function ($j1a) {
                $j1a->whereRAW('admin_orgs.organization = organization_trees.organization OR ((admin_orgs.organization = "" OR admin_orgs.organization IS NULL) AND (organization_trees.organization = "" OR organization_trees.organization IS NULL))');
            } )
            ->on(function ($j2a) {
                $j2a->whereRAW('admin_orgs.level1_program = organization_trees.level1_program OR ((admin_orgs.level1_program = "" OR admin_orgs.level1_program IS NULL) AND (organization_trees.level1_program = "" OR organization_trees.level1_program IS NULL))');
            } )
            ->on(function ($j3a) {
                $j3a->whereRAW('admin_orgs.level2_division = organization_trees.level2_division OR ((admin_orgs.level2_division = "" OR admin_orgs.level2_division IS NULL) AND (organization_trees.level2_division = "" OR organization_trees.level2_division IS NULL))');
            } )
            ->on(function ($j4a) {
                $j4a->whereRAW('admin_orgs.level3_branch = organization_trees.level3_branch OR ((admin_orgs.level3_branch = "" OR admin_orgs.level3_branch IS NULL) AND (organization_trees.level3_branch = "" OR organization_trees.level3_branch IS NULL))');
            } )
            ->on(function ($j5a) {
                $j5a->whereRAW('admin_orgs.level4 = organization_trees.level4 OR ((admin_orgs.level4 = "" OR admin_orgs.level4 IS NULL) AND (organization_trees.level4 = "" OR organization_trees.level4 IS NULL))');
            } );
        } )
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id',$request->alevel0)->first() : null;
        $orgs = OrganizationTree::join('admin_orgs', function ($j1) {
            $j1->on(function ($j1a) {
                $j1a->whereRAW('admin_orgs.organization = organization_trees.organization OR ((admin_orgs.organization = "" OR admin_orgs.organization IS NULL) AND (organization_trees.organization = "" OR organization_trees.organization IS NULL))');
            } )
            ->on(function ($j2a) {
                $j2a->whereRAW('admin_orgs.level1_program = organization_trees.level1_program OR ((admin_orgs.level1_program = "" OR admin_orgs.level1_program IS NULL) AND (organization_trees.level1_program = "" OR organization_trees.level1_program IS NULL))');
            } )
            ->on(function ($j3a) {
                $j3a->whereRAW('admin_orgs.level2_division = organization_trees.level2_division OR ((admin_orgs.level2_division = "" OR admin_orgs.level2_division IS NULL) AND (organization_trees.level2_division = "" OR organization_trees.level2_division IS NULL))');
            } )
            ->on(function ($j4a) {
                $j4a->whereRAW('admin_orgs.level3_branch = organization_trees.level3_branch OR ((admin_orgs.level3_branch = "" OR admin_orgs.level3_branch IS NULL) AND (organization_trees.level3_branch = "" OR organization_trees.level3_branch IS NULL))');
            } )
            ->on(function ($j5a) {
                $j5a->whereRAW('admin_orgs.level4 = organization_trees.level4 OR ((admin_orgs.level4 = "" OR admin_orgs.level4 IS NULL) AND (organization_trees.level4 = "" OR organization_trees.level4 IS NULL))');
            } );
        } )
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->orderby('organization_trees.name','asc')->select(DB::raw('min(organization_trees.id) as id'),'organization_trees.name')
        ->where('organization_trees.level',1)
        ->when( $request->q , function ($q) use($request) {
            return $q->whereRaw("LOWER(organization_trees.name) LIKE '%" . strtolower($request->q) . "%'");
            })
        ->when( $level0 , function ($q) use($level0) {
            return $q->where('organization_trees.organization', $level0->name );
        })
        ->groupBy('organization_trees.name')
        ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) {
            $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }
        return response()->json($formatted_orgs);
    } 

    public function agetDivisions(Request $request) {
        $level0 = $request->alevel0 ? OrganizationTree::join('admin_orgs', function ($j1) {
            $j1->on(function ($j1a) {
                $j1a->whereRAW('admin_orgs.organization = organization_trees.organization OR ((admin_orgs.organization = "" OR admin_orgs.organization IS NULL) AND (organization_trees.organization = "" OR organization_trees.organization IS NULL))');
            } )
            ->on(function ($j2a) {
                $j2a->whereRAW('admin_orgs.level1_program = organization_trees.level1_program OR ((admin_orgs.level1_program = "" OR admin_orgs.level1_program IS NULL) AND (organization_trees.level1_program = "" OR organization_trees.level1_program IS NULL))');
            } )
            ->on(function ($j3a) {
                $j3a->whereRAW('admin_orgs.level2_division = organization_trees.level2_division OR ((admin_orgs.level2_division = "" OR admin_orgs.level2_division IS NULL) AND (organization_trees.level2_division = "" OR organization_trees.level2_division IS NULL))');
            } )
            ->on(function ($j4a) {
                $j4a->whereRAW('admin_orgs.level3_branch = organization_trees.level3_branch OR ((admin_orgs.level3_branch = "" OR admin_orgs.level3_branch IS NULL) AND (organization_trees.level3_branch = "" OR organization_trees.level3_branch IS NULL))');
            } )
            ->on(function ($j5a) {
                $j5a->whereRAW('admin_orgs.level4 = organization_trees.level4 OR ((admin_orgs.level4 = "" OR admin_orgs.level4 IS NULL) AND (organization_trees.level4 = "" OR organization_trees.level4 IS NULL))');
            } );
        } )
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->alevel0)->first() : null;
        $level1 = $request->alevel1 ? OrganizationTree::join('admin_orgs', function ($j1) {
            $j1->on(function ($j1a) {
                $j1a->whereRAW('admin_orgs.organization = organization_trees.organization OR ((admin_orgs.organization = "" OR admin_orgs.organization IS NULL) AND (organization_trees.organization = "" OR organization_trees.organization IS NULL))');
            } )
            ->on(function ($j2a) {
                $j2a->whereRAW('admin_orgs.level1_program = organization_trees.level1_program OR ((admin_orgs.level1_program = "" OR admin_orgs.level1_program IS NULL) AND (organization_trees.level1_program = "" OR organization_trees.level1_program IS NULL))');
            } )
            ->on(function ($j3a) {
                $j3a->whereRAW('admin_orgs.level2_division = organization_trees.level2_division OR ((admin_orgs.level2_division = "" OR admin_orgs.level2_division IS NULL) AND (organization_trees.level2_division = "" OR organization_trees.level2_division IS NULL))');
            } )
            ->on(function ($j4a) {
                $j4a->whereRAW('admin_orgs.level3_branch = organization_trees.level3_branch OR ((admin_orgs.level3_branch = "" OR admin_orgs.level3_branch IS NULL) AND (organization_trees.level3_branch = "" OR organization_trees.level3_branch IS NULL))');
            } )
            ->on(function ($j5a) {
                $j5a->whereRAW('admin_orgs.level4 = organization_trees.level4 OR ((admin_orgs.level4 = "" OR admin_orgs.level4 IS NULL) AND (organization_trees.level4 = "" OR organization_trees.level4 IS NULL))');
            } );
        } )
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->alevel1)->first() : null;
        $orgs = OrganizationTree::join('admin_orgs', function ($j1) {
            $j1->on(function ($j1a) {
                $j1a->whereRAW('admin_orgs.organization = organization_trees.organization OR ((admin_orgs.organization = "" OR admin_orgs.organization IS NULL) AND (organization_trees.organization = "" OR organization_trees.organization IS NULL))');
            } )
            ->on(function ($j2a) {
                $j2a->whereRAW('admin_orgs.level1_program = organization_trees.level1_program OR ((admin_orgs.level1_program = "" OR admin_orgs.level1_program IS NULL) AND (organization_trees.level1_program = "" OR organization_trees.level1_program IS NULL))');
            } )
            ->on(function ($j3a) {
                $j3a->whereRAW('admin_orgs.level2_division = organization_trees.level2_division OR ((admin_orgs.level2_division = "" OR admin_orgs.level2_division IS NULL) AND (organization_trees.level2_division = "" OR organization_trees.level2_division IS NULL))');
            } )
            ->on(function ($j4a) {
                $j4a->whereRAW('admin_orgs.level3_branch = organization_trees.level3_branch OR ((admin_orgs.level3_branch = "" OR admin_orgs.level3_branch IS NULL) AND (organization_trees.level3_branch = "" OR organization_trees.level3_branch IS NULL))');
            } )
            ->on(function ($j5a) {
                $j5a->whereRAW('admin_orgs.level4 = organization_trees.level4 OR ((admin_orgs.level4 = "" OR admin_orgs.level4 IS NULL) AND (organization_trees.level4 = "" OR organization_trees.level4 IS NULL))');
            } );
        } )
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->orderby('organization_trees.name','asc')->select(DB::raw('min(organization_trees.id) as id'),'organization_trees.name')
            ->where('organization_trees.level',2)
            ->when( $request->q , function ($q) use($request) {
                return $q->whereRaw("LOWER(organization_trees.name) LIKE '%" . strtolower($request->q) . "%'");
                })
            ->when( $level0 , function ($q) use($level0) {
                return $q->where('organization_trees.organization', $level0->name) ;
            })
            ->when( $level1 , function ($q) use($level1) {
                return $q->where('organization_trees.level1_program', $level1->name );
            })
            ->groupBy('organization_trees.name')
            ->limit(300)
            ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) {
            $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }
        return response()->json($formatted_orgs);
    } 

    public function agetBranches(Request $request) {
        $level0 = $request->alevel0 ? OrganizationTree::join('admin_orgs', function ($j1) {
            $j1->on(function ($j1a) {
                $j1a->whereRAW('admin_orgs.organization = organization_trees.organization OR ((admin_orgs.organization = "" OR admin_orgs.organization IS NULL) AND (organization_trees.organization = "" OR organization_trees.organization IS NULL))');
            } )
            ->on(function ($j2a) {
                $j2a->whereRAW('admin_orgs.level1_program = organization_trees.level1_program OR ((admin_orgs.level1_program = "" OR admin_orgs.level1_program IS NULL) AND (organization_trees.level1_program = "" OR organization_trees.level1_program IS NULL))');
            } )
            ->on(function ($j3a) {
                $j3a->whereRAW('admin_orgs.level2_division = organization_trees.level2_division OR ((admin_orgs.level2_division = "" OR admin_orgs.level2_division IS NULL) AND (organization_trees.level2_division = "" OR organization_trees.level2_division IS NULL))');
            } )
            ->on(function ($j4a) {
                $j4a->whereRAW('admin_orgs.level3_branch = organization_trees.level3_branch OR ((admin_orgs.level3_branch = "" OR admin_orgs.level3_branch IS NULL) AND (organization_trees.level3_branch = "" OR organization_trees.level3_branch IS NULL))');
            } )
            ->on(function ($j5a) {
                $j5a->whereRAW('admin_orgs.level4 = organization_trees.level4 OR ((admin_orgs.level4 = "" OR admin_orgs.level4 IS NULL) AND (organization_trees.level4 = "" OR organization_trees.level4 IS NULL))');
            } );
        } )
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->alevel0)->first() : null;
        $level1 = $request->alevel1 ? OrganizationTree::join('admin_orgs', function ($j1) {
            $j1->on(function ($j1a) {
                $j1a->whereRAW('admin_orgs.organization = organization_trees.organization OR ((admin_orgs.organization = "" OR admin_orgs.organization IS NULL) AND (organization_trees.organization = "" OR organization_trees.organization IS NULL))');
            } )
            ->on(function ($j2a) {
                $j2a->whereRAW('admin_orgs.level1_program = organization_trees.level1_program OR ((admin_orgs.level1_program = "" OR admin_orgs.level1_program IS NULL) AND (organization_trees.level1_program = "" OR organization_trees.level1_program IS NULL))');
            } )
            ->on(function ($j3a) {
                $j3a->whereRAW('admin_orgs.level2_division = organization_trees.level2_division OR ((admin_orgs.level2_division = "" OR admin_orgs.level2_division IS NULL) AND (organization_trees.level2_division = "" OR organization_trees.level2_division IS NULL))');
            } )
            ->on(function ($j4a) {
                $j4a->whereRAW('admin_orgs.level3_branch = organization_trees.level3_branch OR ((admin_orgs.level3_branch = "" OR admin_orgs.level3_branch IS NULL) AND (organization_trees.level3_branch = "" OR organization_trees.level3_branch IS NULL))');
            } )
            ->on(function ($j5a) {
                $j5a->whereRAW('admin_orgs.level4 = organization_trees.level4 OR ((admin_orgs.level4 = "" OR admin_orgs.level4 IS NULL) AND (organization_trees.level4 = "" OR organization_trees.level4 IS NULL))');
            } );
        } )
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->alevel1)->first() : null;
        $level2 = $request->alevel2 ? OrganizationTree::join('admin_orgs', function ($j1) {
            $j1->on(function ($j1a) {
                $j1a->whereRAW('admin_orgs.organization = organization_trees.organization OR ((admin_orgs.organization = "" OR admin_orgs.organization IS NULL) AND (organization_trees.organization = "" OR organization_trees.organization IS NULL))');
            } )
            ->on(function ($j2a) {
                $j2a->whereRAW('admin_orgs.level1_program = organization_trees.level1_program OR ((admin_orgs.level1_program = "" OR admin_orgs.level1_program IS NULL) AND (organization_trees.level1_program = "" OR organization_trees.level1_program IS NULL))');
            } )
            ->on(function ($j3a) {
                $j3a->whereRAW('admin_orgs.level2_division = organization_trees.level2_division OR ((admin_orgs.level2_division = "" OR admin_orgs.level2_division IS NULL) AND (organization_trees.level2_division = "" OR organization_trees.level2_division IS NULL))');
            } )
            ->on(function ($j4a) {
                $j4a->whereRAW('admin_orgs.level3_branch = organization_trees.level3_branch OR ((admin_orgs.level3_branch = "" OR admin_orgs.level3_branch IS NULL) AND (organization_trees.level3_branch = "" OR organization_trees.level3_branch IS NULL))');
            } )
            ->on(function ($j5a) {
                $j5a->whereRAW('admin_orgs.level4 = organization_trees.level4 OR ((admin_orgs.level4 = "" OR admin_orgs.level4 IS NULL) AND (organization_trees.level4 = "" OR organization_trees.level4 IS NULL))');
            } );
        } )
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->alevel2)->first() : null;
        $orgs = OrganizationTree::join('admin_orgs', function ($j1) {
            $j1->on(function ($j1a) {
                $j1a->whereRAW('admin_orgs.organization = organization_trees.organization OR ((admin_orgs.organization = "" OR admin_orgs.organization IS NULL) AND (organization_trees.organization = "" OR organization_trees.organization IS NULL))');
            } )
            ->on(function ($j2a) {
                $j2a->whereRAW('admin_orgs.level1_program = organization_trees.level1_program OR ((admin_orgs.level1_program = "" OR admin_orgs.level1_program IS NULL) AND (organization_trees.level1_program = "" OR organization_trees.level1_program IS NULL))');
            } )
            ->on(function ($j3a) {
                $j3a->whereRAW('admin_orgs.level2_division = organization_trees.level2_division OR ((admin_orgs.level2_division = "" OR admin_orgs.level2_division IS NULL) AND (organization_trees.level2_division = "" OR organization_trees.level2_division IS NULL))');
            } )
            ->on(function ($j4a) {
                $j4a->whereRAW('admin_orgs.level3_branch = organization_trees.level3_branch OR ((admin_orgs.level3_branch = "" OR admin_orgs.level3_branch IS NULL) AND (organization_trees.level3_branch = "" OR organization_trees.level3_branch IS NULL))');
            } )
            ->on(function ($j5a) {
                $j5a->whereRAW('admin_orgs.level4 = organization_trees.level4 OR ((admin_orgs.level4 = "" OR admin_orgs.level4 IS NULL) AND (organization_trees.level4 = "" OR organization_trees.level4 IS NULL))');
            } );
        } )
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->orderby('organization_trees.name','asc')->select(DB::raw('min(organization_trees.id) as id'),'organization_trees.name')
            ->where('organization_trees.level',3)
            ->when( $request->q , function ($q) use($request) {
                return $q->whereRaw("LOWER(organization_trees.name) LIKE '%" . strtolower($request->q) . "%'");
                })
            ->when( $level0 , function ($q) use($level0) {
                return $q->where('organization_trees.organization', $level0->name) ;
            })
            ->when( $level1 , function ($q) use($level1) {
                return $q->where('organization_trees.level1_program', $level1->name );
            })
            ->when( $level2 , function ($q) use($level2) {
                return $q->where('organization_trees.level2_division', $level2->name );
            })
            ->groupBy('organization_trees.name')
            ->limit(300)
            ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) {
            $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }
        return response()->json($formatted_orgs);
    } 

    public function agetLevel4(Request $request) {
        $level0 = $request->alevel0 ? OrganizationTree::join('admin_orgs', function ($j1) {
            $j1->on(function ($j1a) {
                $j1a->whereRAW('admin_orgs.organization = organization_trees.organization OR ((admin_orgs.organization = "" OR admin_orgs.organization IS NULL) AND (organization_trees.organization = "" OR organization_trees.organization IS NULL))');
            } )
            ->on(function ($j2a) {
                $j2a->whereRAW('admin_orgs.level1_program = organization_trees.level1_program OR ((admin_orgs.level1_program = "" OR admin_orgs.level1_program IS NULL) AND (organization_trees.level1_program = "" OR organization_trees.level1_program IS NULL))');
            } )
            ->on(function ($j3a) {
                $j3a->whereRAW('admin_orgs.level2_division = organization_trees.level2_division OR ((admin_orgs.level2_division = "" OR admin_orgs.level2_division IS NULL) AND (organization_trees.level2_division = "" OR organization_trees.level2_division IS NULL))');
            } )
            ->on(function ($j4a) {
                $j4a->whereRAW('admin_orgs.level3_branch = organization_trees.level3_branch OR ((admin_orgs.level3_branch = "" OR admin_orgs.level3_branch IS NULL) AND (organization_trees.level3_branch = "" OR organization_trees.level3_branch IS NULL))');
            } )
            ->on(function ($j5a) {
                $j5a->whereRAW('admin_orgs.level4 = organization_trees.level4 OR ((admin_orgs.level4 = "" OR admin_orgs.level4 IS NULL) AND (organization_trees.level4 = "" OR organization_trees.level4 IS NULL))');
            } );
        } )
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->alevel0)->first() : null;
        $level1 = $request->alevel1 ? OrganizationTree::join('admin_orgs', function ($j1) {
            $j1->on(function ($j1a) {
                $j1a->whereRAW('admin_orgs.organization = organization_trees.organization OR ((admin_orgs.organization = "" OR admin_orgs.organization IS NULL) AND (organization_trees.organization = "" OR organization_trees.organization IS NULL))');
            } )
            ->on(function ($j2a) {
                $j2a->whereRAW('admin_orgs.level1_program = organization_trees.level1_program OR ((admin_orgs.level1_program = "" OR admin_orgs.level1_program IS NULL) AND (organization_trees.level1_program = "" OR organization_trees.level1_program IS NULL))');
            } )
            ->on(function ($j3a) {
                $j3a->whereRAW('admin_orgs.level2_division = organization_trees.level2_division OR ((admin_orgs.level2_division = "" OR admin_orgs.level2_division IS NULL) AND (organization_trees.level2_division = "" OR organization_trees.level2_division IS NULL))');
            } )
            ->on(function ($j4a) {
                $j4a->whereRAW('admin_orgs.level3_branch = organization_trees.level3_branch OR ((admin_orgs.level3_branch = "" OR admin_orgs.level3_branch IS NULL) AND (organization_trees.level3_branch = "" OR organization_trees.level3_branch IS NULL))');
            } )
            ->on(function ($j5a) {
                $j5a->whereRAW('admin_orgs.level4 = organization_trees.level4 OR ((admin_orgs.level4 = "" OR admin_orgs.level4 IS NULL) AND (organization_trees.level4 = "" OR organization_trees.level4 IS NULL))');
            } );
        } )
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->alevel1)->first() : null;
        $level2 = $request->alevel2 ? OrganizationTree::join('admin_orgs', function ($j1) {
            $j1->on(function ($j1a) {
                $j1a->whereRAW('admin_orgs.organization = organization_trees.organization OR ((admin_orgs.organization = "" OR admin_orgs.organization IS NULL) AND (organization_trees.organization = "" OR organization_trees.organization IS NULL))');
            } )
            ->on(function ($j2a) {
                $j2a->whereRAW('admin_orgs.level1_program = organization_trees.level1_program OR ((admin_orgs.level1_program = "" OR admin_orgs.level1_program IS NULL) AND (organization_trees.level1_program = "" OR organization_trees.level1_program IS NULL))');
            } )
            ->on(function ($j3a) {
                $j3a->whereRAW('admin_orgs.level2_division = organization_trees.level2_division OR ((admin_orgs.level2_division = "" OR admin_orgs.level2_division IS NULL) AND (organization_trees.level2_division = "" OR organization_trees.level2_division IS NULL))');
            } )
            ->on(function ($j4a) {
                $j4a->whereRAW('admin_orgs.level3_branch = organization_trees.level3_branch OR ((admin_orgs.level3_branch = "" OR admin_orgs.level3_branch IS NULL) AND (organization_trees.level3_branch = "" OR organization_trees.level3_branch IS NULL))');
            } )
            ->on(function ($j5a) {
                $j5a->whereRAW('admin_orgs.level4 = organization_trees.level4 OR ((admin_orgs.level4 = "" OR admin_orgs.level4 IS NULL) AND (organization_trees.level4 = "" OR organization_trees.level4 IS NULL))');
            } );
        } )
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->alevel2)->first() : null;
        $level3 = $request->alevel3 ? OrganizationTree::join('admin_orgs', function ($j1) {
            $j1->on(function ($j1a) {
                $j1a->whereRAW('admin_orgs.organization = organization_trees.organization OR ((admin_orgs.organization = "" OR admin_orgs.organization IS NULL) AND (organization_trees.organization = "" OR organization_trees.organization IS NULL))');
            } )
            ->on(function ($j2a) {
                $j2a->whereRAW('admin_orgs.level1_program = organization_trees.level1_program OR ((admin_orgs.level1_program = "" OR admin_orgs.level1_program IS NULL) AND (organization_trees.level1_program = "" OR organization_trees.level1_program IS NULL))');
            } )
            ->on(function ($j3a) {
                $j3a->whereRAW('admin_orgs.level2_division = organization_trees.level2_division OR ((admin_orgs.level2_division = "" OR admin_orgs.level2_division IS NULL) AND (organization_trees.level2_division = "" OR organization_trees.level2_division IS NULL))');
            } )
            ->on(function ($j4a) {
                $j4a->whereRAW('admin_orgs.level3_branch = organization_trees.level3_branch OR ((admin_orgs.level3_branch = "" OR admin_orgs.level3_branch IS NULL) AND (organization_trees.level3_branch = "" OR organization_trees.level3_branch IS NULL))');
            } )
            ->on(function ($j5a) {
                $j5a->whereRAW('admin_orgs.level4 = organization_trees.level4 OR ((admin_orgs.level4 = "" OR admin_orgs.level4 IS NULL) AND (organization_trees.level4 = "" OR organization_trees.level4 IS NULL))');
            } );
        } )
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->alevel3)->first() : null;
        $orgs = OrganizationTree::join('admin_orgs', function ($j1) {
            $j1->on(function ($j1a) {
                $j1a->whereRAW('admin_orgs.organization = organization_trees.organization OR ((admin_orgs.organization = "" OR admin_orgs.organization IS NULL) AND (organization_trees.organization = "" OR organization_trees.organization IS NULL))');
            } )
            ->on(function ($j2a) {
                $j2a->whereRAW('admin_orgs.level1_program = organization_trees.level1_program OR ((admin_orgs.level1_program = "" OR admin_orgs.level1_program IS NULL) AND (organization_trees.level1_program = "" OR organization_trees.level1_program IS NULL))');
            } )
            ->on(function ($j3a) {
                $j3a->whereRAW('admin_orgs.level2_division = organization_trees.level2_division OR ((admin_orgs.level2_division = "" OR admin_orgs.level2_division IS NULL) AND (organization_trees.level2_division = "" OR organization_trees.level2_division IS NULL))');
            } )
            ->on(function ($j4a) {
                $j4a->whereRAW('admin_orgs.level3_branch = organization_trees.level3_branch OR ((admin_orgs.level3_branch = "" OR admin_orgs.level3_branch IS NULL) AND (organization_trees.level3_branch = "" OR organization_trees.level3_branch IS NULL))');
            } )
            ->on(function ($j5a) {
                $j5a->whereRAW('admin_orgs.level4 = organization_trees.level4 OR ((admin_orgs.level4 = "" OR admin_orgs.level4 IS NULL) AND (organization_trees.level4 = "" OR organization_trees.level4 IS NULL))');
            } );
        } )
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->orderby('organization_trees.name','asc')->select(DB::raw('min(organization_trees.id) as id'),'organization_trees.name')
            ->where('organization_trees.level',4)
            ->when( $request->q , function ($q) use($request) {
                return $q->whereRaw("LOWER(organization_trees.name) LIKE '%" . strtolower($request->q) . "%'");
                })
            ->when( $level0 , function ($q) use($level0) {
                return $q->where('organization_trees.organization', $level0->name) ;
            })
            ->when( $level1 , function ($q) use($level1) {
                return $q->where('organization_trees.level1_program', $level1->name );
            })
            ->when( $level2 , function ($q) use($level2) {
                return $q->where('organization_trees.level2_division', $level2->name );
            })
            ->when( $level3 , function ($q) use($level3) {
                return $q->where('organization_trees.level3_branch', $level3->name );
            })
            ->groupBy('organization_trees.name')
            ->limit(300)
            ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) {
            $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }
        return response()->json($formatted_orgs);
    } 
}

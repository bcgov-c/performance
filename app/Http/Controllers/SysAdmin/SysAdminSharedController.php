<?php

namespace App\Http\Controllers\SysAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrganizationTree;
use App\Models\EmployeeDemoTree;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SysAdminSharedController extends Controller
{
    public function getOrganizationList(Request $request, $index, $level) 
    {
        switch ($index) {
            case 2:
                $option = 'e';
                break;
            case 3:
                $option = 'a';
                break;
            default:
                $option = '';
                break;
        } 

        return response()->json(EmployeeDemoTree::where('employee_demo_tree.level', \DB::raw($level))
            ->when($request->q, function ($q) use($request) { return $q->whereRaw("employee_demo_tree.name LIKE '%{$request->q}%'"); })
            ->when($level > 0 && "{$request->{$option.'level0'}}", function ($q) use($request, $option) { return $q->whereRaw('employee_demo_tree.organization_key = '."{$request->{$option.'level0'}}"); })
            ->when($level > 1 && "{$request->{$option.'level1'}}", function ($q) use($request, $option) { return $q->whereRaw('employee_demo_tree.level1_key = '."{$request->{$option.'level1'}}"); })
            ->when($level > 2 && "{$request->{$option.'level2'}}", function ($q) use($request, $option) { return $q->whereRaw('employee_demo_tree.level2_key = '."{$request->{$option.'level2'}}"); })
            ->when($level > 3 && "{$request->{$option.'level3'}}", function ($q) use($request, $option) { return $q->whereRaw('employee_demo_tree.level3_key = '."{$request->{$option.'level3'}}"); })
            ->when($level > 4 && "{$request->{$option.'level4'}}", function ($q) use($request, $option) { return $q->whereRaw('employee_demo_tree.level4_key = '."{$request->{$option.'level4'}}"); })
            ->select('employee_demo_tree.id AS id', 'employee_demo_tree.name AS text')
            ->orderBy('employee_demo_tree.name', 'ASC')
            ->limit(300)
            ->get('id', 'text')
            ->toArray());
    } 

    public function getOrganizationsV2(Request $request) {
        $orgs = EmployeeDemoTree::from('employee_demo_tree AS t')
            ->orderBy('t.name', 'asc')
            ->select('t.id', 't.name')
            ->where('t.level', 0)
            ->when($request->q, function ($q) use($request) { return $q->whereRaw("t.name LIKE '%{$request->q}%'"); })
            ->limit(300)
            ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) { $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name]; }
        return response()->json($formatted_orgs);
    } 

    public function getProgramsV2(Request $request) {
        $orgs = EmployeeDemoTree::from('employee_demo_tree AS t')
            ->orderBy('t.name', 'asc')
            ->select('t.id', 't.name')
            ->where('t.level', 1)
            ->when($request->q, function ($q) use($request) { return $q->whereRaw("t.name LIKE '%{$request->q}%'"); })
            ->when($request->level0, function ($q) use($request) { return $q->where('t.organization_key', $request->level0); })
            ->limit(300)
            ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) { $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name]; }
        return response()->json($formatted_orgs);
    } 

    public function getDivisionsV2(Request $request) {
        $orgs = EmployeeDemoTree::from('employee_demo_tree AS t')
            ->orderBy('t.name', 'asc')
            ->select('t.id', 't.name')
            ->where('t.level', 2)
            ->when($request->q, function ($q) use($request) { return $q->whereRaw("t.name LIKE '%{$request->q}%'"); })
            ->when($request->level0, function ($q) use($request) { return $q->where('t.organization_key', $request->level0); })
            ->when($request->level1, function ($q) use($request) { return $q->where('t.level1_key', $request->level1); })
            ->limit(300)
            ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) { $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name]; }
        return response()->json($formatted_orgs);
    } 

    public function getBranchesV2(Request $request) {
        $orgs = EmployeeDemoTree::from('employee_demo_tree AS t')
            ->orderBy('t.name', 'asc')
            ->select('t.id', 't.name')
            ->where('t.level', 3)
            ->when($request->q, function ($q) use($request) { return $q->whereRaw("t.name LIKE '%{$request->q}%'"); })
            ->when($request->level0, function ($q) use($request) { return $q->where('t.organization_key', $request->level0); })
            ->when($request->level1, function ($q) use($request) { return $q->where('t.level1_key', $request->level1); })
            ->when($request->level2, function ($q) use($request) { return $q->where('t.level2_key', $request->level2); })
            ->limit(300)
            ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) { $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name]; }
        return response()->json($formatted_orgs);
    } 

    public function getLevel4V2(Request $request) {
        $orgs = EmployeeDemoTree::from('employee_demo_tree AS t')
            ->orderBy('t.name', 'asc')
            ->select('t.id', 't.name')
            ->where('t.level', 4)
            ->when($request->q, function ($q) use($request) { return $q->whereRaw("t.name LIKE '%{$request->q}%'"); })
            ->when($request->level0, function ($q) use($request) { return $q->where('t.organization_key', $request->level0); })
            ->when($request->level1, function ($q) use($request) { return $q->where('t.level1_key', $request->level1); })
            ->when($request->level2, function ($q) use($request) { return $q->where('t.level2_key', $request->level2); })
            ->when($request->level3, function ($q) use($request) { return $q->where('t.level3_key', $request->level3); })
            ->limit(300)
            ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) { $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name ]; }
        return response()->json($formatted_orgs);
    } 

    public function egetOrganizationsV2(Request $request) {
        $orgs = EmployeeDemoTree::orderBy('name', 'asc')
            ->select('id', 'name')
            ->where('level', 0)
            ->when($request->q, function ($q) use($request) { return $q->whereRaw("name LIKE '%{$request->q}%'"); })
            ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) { $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name]; }
        return response()->json($formatted_orgs);
    } 

    public function egetProgramsV2(Request $request) {
        $orgs = EmployeeDemoTree::orderBy('name', 'asc')
            ->select('id', 'name')
            ->where('level', 1)
            ->when($request->q, function ($q) use($request) { return $q->whereRaw("name LIKE '%{$request->q}%'"); })
            ->when($request->elevel0, function ($q) use($request) { return $q->where('organization_key', $request->elevel0); })
            ->groupBy('name')
            ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) { $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name]; }
        return response()->json($formatted_orgs);
    } 

    public function egetDivisionsV2(Request $request) {
        $orgs = EmployeeDemoTree::orderBy('name', 'asc')
            ->select('id', 'name')
            ->where('level', 2)
            ->when($request->q, function ($q) use($request) { return $q->whereRaw("name LIKE '%{$request->q}%'"); })
            ->when($request->elevel0, function ($q) use($request) { return $q->where('organization_key', $request->elevel0); })
            ->when($request->elevel1, function ($q) use($request) { return $q->where('level1_key', $request->elevel1); })
            ->groupBy('name')
            ->limit(300)
            ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) { $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name]; }
        return response()->json($formatted_orgs);
    } 

    public function egetBranchesV2(Request $request) {
        $orgs = EmployeeDemoTree::orderBy('name','asc')
            ->select('id', 'name')
            ->where('level', 3)
            ->when($request->q, function ($q) use($request) { return $q->whereRaw("name LIKE '%{$request->q}%'"); })
            ->when($request->elevel0, function ($q) use($request) { return $q->where('organization_key', $request->elevel0); })
            ->when($request->elevel1, function ($q) use($request) { return $q->where('level1_key', $request->elevel1); })
            ->when($request->elevel2, function ($q) use($request) { return $q->where('level2_key', $request->elevel2); })
            ->groupBy('name')
            ->limit(300)
            ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) { $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name]; }
        return response()->json($formatted_orgs);
    } 

    public function egetLevel4V2(Request $request) {
        $orgs = EmployeeDemoTree::orderBy('name', 'asc')
            ->select('id', 'name')
            ->where('level', 4)
            ->when($request->q, function ($q) use($request) { return $q->whereRaw("name LIKE '%{$request->q}%'"); })
            ->when($request->elevel0, function ($q) use($request) { return $q->where('organization_key', $request->elevel0); })
            ->when($request->elevel1, function ($q) use($request) { return $q->where('level1_key', $request->elevel1); })
            ->when($request->elevel2, function ($q) use($request) { return $q->where('level2_key', $request->elevel2); })
            ->when($request->elevel3, function ($q) use($request) { return $q->where('level3_key', $request->elevel3); })
            ->groupBy('name')
            ->limit(300)
            ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) { $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name]; }
        return response()->json($formatted_orgs);
    } 

    public function agetOrganizationsV2(Request $request) {
        $orgs = EmployeeDemoTree::orderBy('name', 'asc')
            ->select('id', 'name')
            ->where('level', 0)
            ->when($request->q, function ($q) use($request) { return $q->whereRaw("name LIKE '%{$request->q}%'"); })
            ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) { $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name]; }
        return response()->json($formatted_orgs);
    } 

    public function agetProgramsV2(Request $request) {
        $orgs = EmployeeDemoTree::orderby('name', 'asc')
            ->select('id', 'name')
            ->where('level', 1)
            ->when($request->q, function ($q) use($request) { return $q->whereRaw("name LIKE '%{$request->q}%'"); })
            ->when($request->alevel0, function ($q) use($request) { return $q->where('organization_key', $request->alevel0); })
            ->groupBy('name')
            ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) { $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name]; }
        return response()->json($formatted_orgs);
    } 

    public function agetDivisionsV2(Request $request) {
        $orgs = EmployeeDemoTree::orderby('name', 'asc')
            ->select('id', 'name')
            ->where('level', 2)
            ->when($request->q, function ($q) use($request) { return $q->whereRaw("name LIKE '%{$request->q}%'"); })
            ->when($request->alevel0, function ($q) use($request) { return $q->where('organization_key', $request->alevel0); })
            ->when($request->alevel1, function ($q) use($request) { return $q->where('level1_key', $request->alevel1); })
            ->groupBy('name')
            ->limit(300)
            ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) { $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name]; }
        return response()->json($formatted_orgs);
    } 

    public function agetBranchesV2(Request $request) {
        $orgs = EmployeeDemoTree::orderby('name', 'asc')
            ->select('id', 'name')
            ->where('level', 3)
            ->when($request->q, function ($q) use($request) { return $q->whereRaw("name LIKE '%{$request->q}%'"); })
            ->when($request->alevel0, function ($q) use($request) { return $q->where('organization_key', $request->alevel0); })
            ->when($request->alevel1, function ($q) use($request) { return $q->where('level1_key', $request->alevel1); })
            ->when($request->alevel2, function ($q) use($request) { return $q->where('level2_key', $request->alevel2); })
            ->groupBy('name')
            ->limit(300)
            ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) { $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name]; }
        return response()->json($formatted_orgs);
    } 

    public function agetLevel4V2(Request $request) {
        $orgs = EmployeeDemoTree::orderby('name', 'asc')
            ->select('id', 'name')
            ->where('level', 4)
            ->when($request->q, function ($q) use($request) { return $q->whereRaw("name LIKE '%{$request->q}%'"); })
            ->when($request->alevel0, function ($q) use($request) { return $q->where('organization_key', $request->alevel0); })
            ->when($request->alevel1, function ($q) use($request) { return $q->where('level1_key', $request->alevel1); })
            ->when($request->alevel2, function ($q) use($request) { return $q->where('level2_key', $request->alevel2); })
            ->when($request->alevel3, function ($q) use($request) { return $q->where('level3_key', $request->alevel3); })
            ->groupBy('name')
            ->limit(300)
            ->get();
        $formatted_orgs = [];
        foreach ($orgs as $org) { $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name]; }
        return response()->json($formatted_orgs);
    } 





    public function getOrganizations(Request $request) {
        $orgs = OrganizationTree::
        orderby('organization_trees.name','asc')->select('organization_trees.id','organization_trees.name')
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
        $level0 = $request->level0 ? OrganizationTree::
        where('organization_trees.id',$request->level0)->first() : null;
        $orgs = OrganizationTree::
        orderby('organization_trees.name','asc')->select(DB::raw('min(organization_trees.id) as id'),'organization_trees.name')
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
        $level0 = $request->level0 ? OrganizationTree::
            where('organization_trees.id', $request->level0)->first() : null;
        $level1 = $request->level1 ? OrganizationTree::
        where('organization_trees.id', $request->level1)->first() : null;
        $orgs = OrganizationTree::
        orderby('organization_trees.name','asc')->select(DB::raw('min(organization_trees.id) as id'),'organization_trees.name')
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
        $level0 = $request->level0 ? OrganizationTree::
            where('organization_trees.id', $request->level0)->first() : null;
        $level1 = $request->level1 ? OrganizationTree::
            where('organization_trees.id', $request->level1)->first() : null;
        $level2 = $request->level2 ? OrganizationTree::
            where('organization_trees.id', $request->level2)->first() : null;
        $orgs = OrganizationTree::
        orderby('organization_trees.name','asc')->select(DB::raw('min(organization_trees.id) as id'),'organization_trees.name')
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
        $level0 = $request->level0 ? OrganizationTree::
            where('organization_trees.id', $request->level0)->first() : null;
        $level1 = $request->level1 ? OrganizationTree::
            where('organization_trees.id', $request->level1)->first() : null;
        $level2 = $request->level2 ? OrganizationTree::
            where('organization_trees.id', $request->level2)->first() : null;
        $level3 = $request->level3 ? OrganizationTree::
            where('organization_trees.id', $request->level3)->first() : null;
        $orgs = OrganizationTree::
            orderby('organization_trees.name','asc')->select(DB::raw('min(organization_trees.id) as id'),'organization_trees.name')
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
        $eorgs = OrganizationTree::
        orderby('organization_trees.name','asc')->select('organization_trees.id','organization_trees.name')
            ->where('organization_trees.level',0)
            ->when( $request->q , function ($q) use($request) {
                return $q->whereRaw("LOWER(name) LIKE '%" . strtolower($request->q) . "%'");
            })
            ->get();
        $eformatted_orgs = [];
        foreach ($eorgs as $org) {
            $eformatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }
        return response()->json($eformatted_orgs);
    } 

    public function egetPrograms(Request $request) {
        $elevel0 = $request->elevel0 ? OrganizationTree::
        where('organization_trees.id',$request->elevel0)->first() : null;
        $eorgs = OrganizationTree::
        orderby('organization_trees.name','asc')->select(DB::raw('min(organization_trees.id) as id'),'organization_trees.name')
            ->where('organization_trees.level',1)
            ->when( $request->q , function ($q) use($request) {
                return $q->whereRaw("LOWER(organization_trees.name) LIKE '%" . strtolower($request->q) . "%'");
                })
            ->when( $elevel0 , function ($q) use($elevel0) {
                return $q->where('organization_trees.organization', $elevel0->name );
            })
            ->groupBy('organization_trees.name')
            ->get();
        $eformatted_orgs = [];
        foreach ($eorgs as $org) {
            $eformatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }
        return response()->json($eformatted_orgs);
    } 

    public function egetBranches(Request $request) {
        $elevel0 = $request->elevel0 ? OrganizationTree::
            where('organization_trees.id', $request->elevel0)->first() : null;
        $elevel1 = $request->elevel1 ? OrganizationTree::
            where('organization_trees.id', $request->elevel1)->first() : null;
        $elevel2 = $request->elevel2 ? OrganizationTree::
            where('organization_trees.id', $request->elevel2)->first() : null;
        $eorgs = OrganizationTree::
            orderby('organization_trees.name','asc')->select(DB::raw('min(organization_trees.id) as id'),'organization_trees.name')
            ->where('organization_trees.level',3)
            ->when( $request->q , function ($q) use($request) {
                return $q->whereRaw("LOWER(organization_trees.name) LIKE '%" . strtolower($request->q) . "%'");
                })
            ->when( $elevel0 , function ($q) use($elevel0) {
                return $q->where('organization_trees.organization', $elevel0->name) ;
            })
            ->when( $elevel1 , function ($q) use($elevel1) {
                return $q->where('organization_trees.level1_program', $elevel1->name );
            })
            ->when( $elevel2 , function ($q) use($elevel2) {
                return $q->where('organization_trees.level2_division', $elevel2->name );
            })
            ->groupBy('organization_trees.name')
            ->limit(300)
            ->get();
        $eformatted_orgs = [];
        foreach ($eorgs as $org) {
            $eformatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }
        return response()->json($eformatted_orgs);
    } 

    public function egetDivisions(Request $request) {

        $elevel0 = $request->elevel0 ? OrganizationTree::
            where('organization_trees.id', $request->elevel0)->first() : null;
        $elevel1 = $request->elevel1 ? OrganizationTree::
            where('organization_trees.id', $request->elevel1)->first() : null;
        $eorgs = OrganizationTree::
        orderby('organization_trees.name','asc')->select(DB::raw('min(organization_trees.id) as id'),'organization_trees.name')
            ->where('organization_trees.level',2)
            ->when( $request->q , function ($q) use($request) {
                return $q->whereRaw("LOWER(organization_trees.name) LIKE '%" . strtolower($request->q) . "%'");
                })
            ->when( $elevel0 , function ($q) use($elevel0) {
                return $q->where('organization_trees.organization', $elevel0->name) ;
            })
            ->when( $elevel1 , function ($q) use($elevel1) {
                return $q->where('organization_trees.level1_program', $elevel1->name );
            })
            ->groupBy('organization_trees.name')
            ->limit(300)
            ->get();
        $eformatted_orgs = [];
        foreach ($eorgs as $org) {
            $eformatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }
        return response()->json($eformatted_orgs);
    } 

    public function egetLevel4(Request $request) {
        $elevel0 = $request->elevel0 ? OrganizationTree::
            where('organization_trees.id', $request->elevel0)->first() : null;
        $elevel1 = $request->elevel1 ? OrganizationTree::
            where('organization_trees.id', $request->elevel1)->first() : null;
        $elevel2 = $request->elevel2 ? OrganizationTree::
            where('organization_trees.id', $request->elevel2)->first() : null;
        $elevel3 = $request->elevel3 ? OrganizationTree::
            where('organization_trees.id', $request->elevel3)->first() : null;
        $eorgs = OrganizationTree::
            orderby('organization_trees.name','asc')->select(DB::raw('min(organization_trees.id) as id'),'organization_trees.name')
            ->where('organization_trees.level',4)
            ->when( $request->q , function ($q) use($request) {
                return $q->whereRaw("LOWER(organization_trees.name) LIKE '%" . strtolower($request->q) . "%'");
                })
            ->when( $elevel0 , function ($q) use($elevel0) {
                return $q->where('organization_trees.organization', $elevel0->name) ;
            })
            ->when( $elevel1 , function ($q) use($elevel1) {
                return $q->where('organization_trees.level1_program', $elevel1->name );
            })
            ->when( $elevel2 , function ($q) use($elevel2) {
                return $q->where('organization_trees.level2_division', $elevel2->name );
            })
            ->when( $elevel3 , function ($q) use($elevel3) {
                return $q->where('organization_trees.level3_branch', $elevel3->name );
            })
            ->groupBy('organization_trees.name')
            ->limit(300)
            ->get();
        $eformatted_orgs = [];
        foreach ($eorgs as $org) {
            $eformatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }
        return response()->json($eformatted_orgs);
    } 

    public function agetOrganizations(Request $request) {
        $orgs = OrganizationTree::
        orderby('organization_trees.name','asc')->select('organization_trees.id','organization_trees.name')
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
        $level0 = $request->alevel0 ? OrganizationTree::
        where('organization_trees.id',$request->alevel0)->first() : null;
        $orgs = OrganizationTree::
        orderby('organization_trees.name','asc')->select(DB::raw('min(organization_trees.id) as id'),'organization_trees.name')
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
        $level0 = $request->alevel0 ? OrganizationTree::
            where('organization_trees.id', $request->alevel0)->first() : null;
        $level1 = $request->alevel1 ? OrganizationTree::
        where('organization_trees.id', $request->alevel1)->first() : null;
        $orgs = OrganizationTree::
        orderby('organization_trees.name','asc')->select(DB::raw('min(organization_trees.id) as id'),'organization_trees.name')
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
        $level0 = $request->alevel0 ? OrganizationTree::
            where('organization_trees.id', $request->alevel0)->first() : null;
        $level1 = $request->alevel1 ? OrganizationTree::
            where('organization_trees.id', $request->alevel1)->first() : null;
        $level2 = $request->alevel2 ? OrganizationTree::
            where('organization_trees.id', $request->alevel2)->first() : null;
        $orgs = OrganizationTree::
        orderby('organization_trees.name','asc')->select(DB::raw('min(organization_trees.id) as id'),'organization_trees.name')
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
        $level0 = $request->alevel0 ? OrganizationTree::
            where('organization_trees.id', $request->alevel0)->first() : null;
        $level1 = $request->alevel1 ? OrganizationTree::
            where('organization_trees.id', $request->alevel1)->first() : null;
        $level2 = $request->alevel2 ? OrganizationTree::
            where('organization_trees.id', $request->alevel2)->first() : null;
        $level3 = $request->alevel3 ? OrganizationTree::
            where('organization_trees.id', $request->alevel3)->first() : null;
        $orgs = OrganizationTree::
            orderby('organization_trees.name','asc')->select(DB::raw('min(organization_trees.id) as id'),'organization_trees.name')
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

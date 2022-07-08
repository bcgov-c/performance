<?php

namespace App\Http\Controllers\SysAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrganizationTree;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SysAdminSharedController extends Controller
{
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
}

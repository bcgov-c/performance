<?php

namespace App\Http\Controllers\HRAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrganizationTree;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HRAdminSharedController extends Controller
{
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
        ->where('id', $request->level0)->first() : null;
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
        ->where('id', $request->elevel0)->first() : null;
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
        ->where('id', $request->alevel0)->first() : null;
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

<?php

namespace App\Http\Controllers\HRAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Conversation;
use App\Models\EmployeeDemo;
use App\Models\OrganizationTree;
use Yajra\Datatables\Datatables;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;




class MyOrganizationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $errors = session('errors');

        if ($errors) {
            $old = session()->getOldInput();
            $request->dd_level0 = isset($old['dd_level0']) ? $old['dd_level0'] : null;
            $request->dd_level1 = isset($old['dd_level1']) ? $old['dd_level1'] : null;
            $request->dd_level2 = isset($old['dd_level2']) ? $old['dd_level2'] : null;
            $request->dd_level3 = isset($old['dd_level3']) ? $old['dd_level3'] : null;
            $request->dd_level4 = isset($old['dd_level4']) ? $old['dd_level4'] : null;
            $request->criteria = isset($old['criteria']) ? $old['criteria'] : null;
            $request->search_text = isset($old['search_text']) ? $old['search_text'] : null;
        } 

        if ($request->btn_search) {
            session()->put('_old_input', [
                'dd_level0' => $request->dd_level0,
                'dd_level1' => $request->dd_level1,
                'dd_level2' => $request->dd_level2,
                'dd_level3' => $request->dd_level3,
                'dd_level4' => $request->dd_level4,
                'criteria' => $request->criteria,
                'search_text' => $request->search_text,
            ]);
        }

        $level0 = $request->dd_level0 ? OrganizationTree::where('id', $request->dd_level0)->first() : null;
        $level1 = $request->dd_level1 ? OrganizationTree::where('id', $request->dd_level1)->first() : null;
        $level2 = $request->dd_level2 ? OrganizationTree::where('id', $request->dd_level2)->first() : null;
        $level3 = $request->dd_level3 ? OrganizationTree::where('id', $request->dd_level3)->first() : null;
        $level4 = $request->dd_level4 ? OrganizationTree::where('id', $request->dd_level4)->first() : null;

        $request->session()->flash('level0', $level0);
        $request->session()->flash('level1', $level1);
        $request->session()->flash('level2', $level2);
        $request->session()->flash('level3', $level3);
        $request->session()->flash('level4', $level4);

        $criteriaList = $this->search_criteria_list();

        return view('hradmin.myorg.myorganization', compact ('request', 'criteriaList'));
    }

    public function getList(Request $request)
    {
        if ($request->ajax()) 
        {
            $authId = Auth::id();
            $level0 = $request->dd_level0 ? OrganizationTree::where('id', $request->dd_level0)->first() : null;
            $level1 = $request->dd_level1 ? OrganizationTree::where('id', $request->dd_level1)->first() : null;
            $level2 = $request->dd_level2 ? OrganizationTree::where('id', $request->dd_level2)->first() : null;
            $level3 = $request->dd_level3 ? OrganizationTree::where('id', $request->dd_level3)->first() : null;
            $level4 = $request->dd_level4 ? OrganizationTree::where('id', $request->dd_level4)->first() : null;
            $query = User::withoutGlobalScopes()
            ->join('employee_demo', 'users.guid', '=', 'employee_demo.guid')
            ->join('admin_orgs', function($join) {
                $join->on('admin_orgs.organization', '=', 'employee_demo.organization')
                ->on('admin_orgs.level1_program', '=', 'employee_demo.level1_program')
                ->on('admin_orgs.level2_division', '=', 'employee_demo.level2_division')
                ->on('admin_orgs.level3_branch', '=', 'employee_demo.level3_branch')
                ->on('admin_orgs.level4', '=', 'employee_demo.level4');
            })
            ->where('admin_orgs.user_id', '=', $authId)
            ->when($level0, function($q) use($level0) {return $q->where('employee_demo.organization', $level0->name);})
            ->when($level1, function($q) use($level1) {return $q->where('employee_demo.level1_program', $level1->name);})
            ->when($level2, function($q) use($level2) {return $q->where('employee_demo.level2_division', $level2->name);})
            ->when($level3, function($q) use($level3) {return $q->where('employee_demo.level3_branch', $level3->name);})
            ->when($level4, function($q) use($level4) {return $q->where('employee_demo.level4', $level4->name);})
            ->when($request->criteria == 'id' && $request->search_text, function($q) use($request){return $q->where('employee_demo.employee_id', 'like', "%" . $request->search_text . "%");})
            ->when($request->criteria == 'name' && $request->search_text, function($q) use($request){return $q->where('employee_demo.employee_name', 'like', "%" . $request->search_text . "%");})
            ->when($request->criteria == 'job' && $request->search_text, function($q) use($request){return $q->where('employee_demo.jobcode_desc', 'like', "%" . $request->search_text . "%");})
            ->when($request->criteria == 'dpt' && $request->search_text, function($q) use($request){return $q->where('employee_demo.deptid', 'like', "%" . $request->search_text . "%");})
            ->when([$request->criteria == 'all' && $request->search_text, $request->search_text], function($q) use ($request) 
            {
                return $q->where(function ($query2) use ($request) 
                {
                    $query2->where('employee_demo.employee_id', 'like', "%" . $request->search_text . "%")
                    ->orWhere('employee_demo.employee_name', 'like', "%" . $request->search_text . "%")
                    ->orWhere('employee_demo.jobcode_desc', 'like', "%" . $request->search_text . "%")
                    ->orWhere('employee_demo.deptid', 'like', "%" . $request->search_text . "%");
                });
            })
            ->select
            (
                'employee_demo.employee_id',
                'employee_demo.employee_name', 
                'employee_demo.jobcode_desc',
                'employee_demo.organization',
                'employee_demo.level1_program',
                'employee_demo.level2_division',
                'employee_demo.level3_branch',
                'employee_demo.level4',
                'employee_demo.deptid',
                'users.excused_start_date',
                'users.id'
            );
            return Datatables::of($query)->addIndexColumn()
            ->addColumn('activeGoals', function($row) {
                $countActiveGoals = $row->activeGoals()->count() . ' Goals';
                return $countActiveGoals;
            })
            ->addColumn('nextConversationDue', function ($row) {
                $nextConversation = Conversation::nextConversationDue(User::find($row["id"]));
                return $nextConversation;
            })
            ->addColumn('excused', function ($row) {
                $yesOrNo = ($row->excused_start_date !== null) ? 'Yes' : 'No';
                return $yesOrNo;
            })
            ->addColumn('shared', function ($row) {
                $yesOrNo = $row->is_shared ? "Yes" : "No";
                return $yesOrNo;
            })
            ->addColumn('reportees', function($row) {
                $countReportees = $row->reportees()->count() ?? '0';
                return $countReportees;
            })
            ->make(true);
        }
    }

    public function getOrganizations(Request $request) {

        $orgs = OrganizationTree::join('admin_orgs', function($join) {
            $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
            ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
            ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
            ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('organization_trees.level4', '=', 'admin_orgs.level4');
        })
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

        $level0 = $request->level0 ? OrganizationTree::join('admin_orgs', function($join) {
            $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
            ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
            ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
            ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('organization_trees.level4', '=', 'admin_orgs.level4');
        })
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id',$request->level0)->first() : null;

        $orgs = OrganizationTree::join('admin_orgs', function($join) {
            $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
            ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
            ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
            ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('organization_trees.level4', '=', 'admin_orgs.level4');
        })
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

        $level0 = $request->level0 ? OrganizationTree::join('admin_orgs', function($join) {
            $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
            ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
            ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
            ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('organization_trees.level4', '=', 'admin_orgs.level4');
        })
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->level0)->first() : null;
        $level1 = $request->level1 ? OrganizationTree::join('admin_orgs', function($join) {
            $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
            ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
            ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
            ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('organization_trees.level4', '=', 'admin_orgs.level4');
        })
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->level1)->first() : null;

        $orgs = OrganizationTree::join('admin_orgs', function($join) {
            $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
            ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
            ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
            ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('organization_trees.level4', '=', 'admin_orgs.level4');
        })
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

        $level0 = $request->level0 ? OrganizationTree::join('admin_orgs', function($join) {
            $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
            ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
            ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
            ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('organization_trees.level4', '=', 'admin_orgs.level4');
        })
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('id', $request->level0)->first() : null;
        $level1 = $request->level1 ? OrganizationTree::join('admin_orgs', function($join) {
            $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
            ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
            ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
            ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('organization_trees.level4', '=', 'admin_orgs.level4');
        })
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->level1)->first() : null;
        $level2 = $request->level2 ? OrganizationTree::join('admin_orgs', function($join) {
            $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
            ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
            ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
            ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('organization_trees.level4', '=', 'admin_orgs.level4');
        })
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->level2)->first() : null;

        $orgs = OrganizationTree::join('admin_orgs', function($join) {
            $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
            ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
            ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
            ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('organization_trees.level4', '=', 'admin_orgs.level4');
        })
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
        $level0 = $request->level0 ? OrganizationTree::join('admin_orgs', function($join) {
            $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
            ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
            ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
            ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('organization_trees.level4', '=', 'admin_orgs.level4');
        })
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->level0)->first() : null;
        $level1 = $request->level1 ? OrganizationTree::join('admin_orgs', function($join) {
            $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
            ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
            ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
            ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('organization_trees.level4', '=', 'admin_orgs.level4');
        })
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->level1)->first() : null;
        $level2 = $request->level2 ? OrganizationTree::join('admin_orgs', function($join) {
            $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
            ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
            ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
            ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('organization_trees.level4', '=', 'admin_orgs.level4');
        })
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->level2)->first() : null;
        $level3 = $request->level3 ? OrganizationTree::join('admin_orgs', function($join) {
            $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
            ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
            ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
            ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('organization_trees.level4', '=', 'admin_orgs.level4');
        })
        ->where('admin_orgs.user_id', '=', Auth::id())
        ->where('organization_trees.id', $request->level3)->first() : null;

        $orgs = OrganizationTree::join('admin_orgs', function($join) {
            $join->on('organization_trees.organization', '=', 'admin_orgs.organization')
            ->on('organization_trees.level1_program', '=', 'admin_orgs.level1_program')
            ->on('organization_trees.level2_division', '=', 'admin_orgs.level2_division')
            ->on('organization_trees.level3_branch', '=', 'admin_orgs.level3_branch')
            ->on('organization_trees.level4', '=', 'admin_orgs.level4');
        })
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

    protected function search_criteria_list() {
        return [
            'all' => 'All',
            'emp' => 'Employee ID', 
            'name'=> 'Employee Name',
            'job' => 'Classification', 
            'dpt' => 'Department ID'
        ];
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}

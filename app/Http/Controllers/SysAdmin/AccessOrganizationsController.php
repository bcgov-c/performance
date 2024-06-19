<?php

namespace App\Http\Controllers\SysAdmin;

use App\Models\EmployeeDemo;
use App\Models\EmployeeDemoTree;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\Datatables\Datatables;
use App\Models\AccessOrganization;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AccessOrganizationsController extends Controller
{
    //
    public function index(Request $request) {

        if($request->ajax()) {
             $access_orgs = AccessOrganization::from('access_organizations')
                ->leftjoin('employee_demo_tree', 'access_organizations.orgid', 'employee_demo_tree.id')
                ->leftjoin('organization_statistics', 'organization_statistics.orgid', 'access_organizations.orgid')
                ->where('access_organizations.orgid', '<', 1000000)
                ->when($request->organization, function($query) use($request) {
                    return $query->where('employee_demo_tree.name', 'like', '%'.$request->organization.'%');
                })
                ->when($request->allow_login, function($query) use($request) {
                    return $query->where('allow_login', $request->allow_login);
                })
                ->when($request->allow_inapp_msg, function($query) use($request) {
                    return $query->where('allow_inapp_msg', $request->allow_inapp_msg);
                })
                ->when($request->allow_email_msg, function($query) use($request) {
                    return $query->where('allow_email_msg', $request->allow_email_msg);
                })
                ->with('created_by', 'updated_by')
                ->selectRaw("
                    access_organizations.id AS id,
                    access_organizations.orgid,
                    employee_demo_tree.organization AS organization,
                    allow_login,
                    allow_inapp_msg,
                    allow_email_msg,
                    conversation_batch,
                    created_by_id,
                    updated_by_id,
                    access_organizations.created_at, 
                    access_organizations.updated_at,
                    organization_statistics.userdemojrview_groupcount AS active_employee_ids_count
                ");
            return Datatables::of($access_orgs)
                ->addIndexColumn()
                ->addColumn('action', function ($org) {
                    return '<a class="btn btn-info btn-sm edit-org" data-id="'. $org->id .'" >Change</a>' ;
                })
                ->addColumn('select_users', static function ($org) {
                    return '<input pid="1335" type="checkbox" id="userCheck'. 
                        $org->id .'" name="userCheck[]" value="'. $org->id .'" class="dt-body-center">';
                })
                ->editColumn('created_at', function ($user) {
                    return $user->created_at->format('Y-m-d H:m:s'); // human readable format
                })
                ->editColumn('updated_at', function ($user) {
                    return $user->updated_at->format('Y-m-d H:m:s'); // human readable format
                })
                ->rawColumns(['action', 'select_users', 'created_at', 'updated_at'])
                ->make(true);
        }

        $this->createNewAccessOrgsFromEmployeeDemo();

        $matched_emp_ids = AccessOrganization::where('orgid', '<', 1000000)->pluck('id');
        $old_selected_emp_ids = [];

        return view('sysadmin.system-security.access-orgs.index',compact('request','matched_emp_ids','old_selected_emp_ids') );


    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        if ($request->ajax()) {
            $access_org = AccessOrganization::where('id', $id)->first();
            return response()->json($access_org);
        }
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
        if ($request->ajax()) {

            $validator = Validator::make(request()->all(), [
                    'allow_login'  => ['required', Rule::in(['Y', 'N']) ],
                    'allow_inapp_msg'  => ['required', Rule::in(['Y', 'N']) ],
                    'allow_email_msg'  => ['required', Rule::in(['Y', 'N']) ],
            ]);

            //run validation which will redirect on failure
            $validated = $validator->validate();

            $access_org = AccessOrganization::where('id', $id)->first();
            $access_org->fill( $validated );
            $access_org->updated_by_id = Auth::ID();

            $access_org->save();

            return response()->json($access_org);
        }
    }

    public function toggleAllowLogin(Request $request) 
    {

        if ($request->ajax()) {

            $access_orgs = AccessOrganization::whereIn('id', $request->selected_orgs)->get();

            // return implode(', ', $access_orgs->pluck('id')->toArray() );

            foreach($access_orgs as $org) {
                $org->allow_login = $request->allow_login;
                $org->updated_by_id = Auth::ID();

                $org->save();
            }

            // return response()->json( $access_orgs->pluck('id') );
            return response()->noContent();
        }

    }

    public function reset(Request $request) 
    {

        if ($request->ajax()) {

            $access_orgs = AccessOrganization::whereIn('id', $request->selected_orgs)->get();

            // return implode(', ', $access_orgs->pluck('id')->toArray() );

            foreach($access_orgs as $org) {
                $org->allow_login = 'N';
                $org->allow_inapp_msg = 'N';
                $org->allow_email_msg = 'N';
                $org->updated_by_id = Auth::ID();

                $org->save();
            }

            // return response()->json( $access_orgs->pluck('id') );
            return response()->noContent();
        }

    }


    protected function createNewAccessOrgsFromEmployeeDemo() 
    {
        $org_names = EmployeeDemoTree::where('level', 0)
            ->select('id', 'name')
            ->orderBy('name')->get();

        foreach ($org_names as $org_name) {
            
            $access_org = AccessOrganization::where('orgid', $org_name->id)->first();

            if ( !$access_org ) {

                AccessOrganization::create([
                    'orgid' => $org_name->id,
                    'organization' => $org_name->name,
                    'created_by_id' => Auth::Id(),
                    'updated_by_id' => Auth::Id(),
                ]);
                
            }

        }

    }

}

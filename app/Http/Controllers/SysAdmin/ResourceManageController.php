<?php

namespace App\Http\Controllers\SysAdmin;

use App\Models\User;
use App\Models\EmployeeDemo;
use Illuminate\Http\Request;
use App\Models\NotificationLog;
use App\Models\OrganizationTree;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;


class ResourceManageController extends Controller
{
    //
    public function index(Request $request)
    {
        //
        $resources = DB::table('resource_content')
                    ->select('content_id', 'category', 'question', 'answer', 'answer_file')
                    ->orderBy('category')
                    ->orderBy('content_id', 'desc')
                    ->paginate(25);

        // load the view and pass the sharks
        return view('sysadmin.resource-manage.index', compact('resources'));

    }

    public function create()
    {
        $categories = DB::table('resource_content')
                    ->select('category')
                    ->distinct()
                    ->get();
        return view('sysadmin.resource-manage.create', compact('categories'));
    }

    public function show($id)
    {
        $resource = DB::table('resource_content')
                    ->select('content_id', 'category', 'question', 'answer', 'answer_file')
                    ->where('content_id', $id)
                    ->first();

        // show the view and pass the campaign year to it
        return view('sysadmin.resource-manage.show', compact('resource'));
    }

    public function edit($id, Request $request)
    {
        //
        $resource = DB::table('resource_content')
                    ->select('content_id', 'category', 'question', 'answer', 'answer_file')
                    ->where('content_id', $id)
                    ->first();

        // show the view and pass the campaign year to it
        return view('sysadmin.resource-manage.edit', compact('resource'));
         
    }

    public function store(Request $request, $id) {
        if($id) {
            DB::table('resource_content')
                ->where('content_id', $id)
                ->update([
                    'category' => $request->category,
                    'question' => $request->question,
                    'answer' => $request->answer,
                    // Add other fields to update as needed
                ]);

        }
        return redirect()->route('sysadmin.resource-manage')
            ->with('success','Resource updated successfully');
        
    }

    public function new(Request $request) {
        if ($request->category && $request->question && $request->question) {
            DB::table('resource_content')->insert([
                'category' => $request->category,
                'question' => $request->question,
                'answer' => $request->answer,
                // Add other fields to insert as needed
            ]);
            return redirect()->route('sysadmin.resource-manage')->with('success','Resource added successfully');
        } else {
            return redirect()->route('resource-manage.create')->with('error','Please make sure all information are filled');
        }
    }

}

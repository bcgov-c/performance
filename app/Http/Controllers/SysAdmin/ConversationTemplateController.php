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


class ConversationTemplateController extends Controller
{
    //
    public function index(Request $request)
    {
        //
        $resources = DB::table('conversation_topics')
                    ->orderBy('sort')
                    ->orderBy('sort', 'desc')->get();

        // load the view and pass the sharks
        return view('sysadmin.conversation-template.index', compact('resources'));

    }

    public function show($id)
    {
        $resource = DB::table('conversation_topics')
                    ->where('id', $id)
                    ->first();

        // show the view and pass the campaign year to it
        return view('sysadmin.conversation-template.show', compact('resource'));
    }

    public function edit($id, Request $request)
    {
        //
        $resource = DB::table('conversation_topics')
                    ->where('id', $id)
                    ->first();

        // show the view and pass the campaign year to it
        return view('sysadmin.conversation-template.edit', compact('resource'));
         
    }

    public function store(Request $request, $id) {
        if($id) {
            DB::table('conversation_topics')
                ->where('id', $id)
                ->update([
                    'name' => $request->name,
                    'when_to_use' => $request->when_to_use,
                    'question_html' => $request->question_html,
                    'preparing_for_conversation' => $request->preparing_for_conversation,
                ]);

        }
        return redirect()->route('sysadmin.conversation-template')
            ->with('success','Resource updated successfully');
        
    }
}

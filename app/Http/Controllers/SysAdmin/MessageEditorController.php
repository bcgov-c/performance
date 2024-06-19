<?php

namespace App\Http\Controllers\SysAdmin;



use App\Models\DashboardMessage;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;


class MessageEditorController extends Controller
{
    public function index(Request $request) 
    {
        $messages = DashboardMessage::all();
        foreach($messages as $message) {};

        return view('sysadmin.messageeditor.index', compact('message', 'request') );
    
    }

    public function update(Request $request) 
    {
        $result = DashboardMessage::where('id', '=', 1)->update([
            'message' => $request->message,
            'status' => $request->status ? 1 : 0
        ]);

        return redirect()->route(request()->segment(1).'.messageeditor')
            ->with('success', 'Message updated successfully.');
    
    }

}

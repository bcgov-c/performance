<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\UserPreference;
use Illuminate\Support\Facades\Auth;

class UserPreferenceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $pref = UserPreference::where('user_id', Auth::id() )->first();

        if (!$pref) {
            $pref = new UserPreference;
            $pref->user_id = Auth::id();
        }

        $user = User::where('id', Auth::id() )->first();
        $isSupervisor =  $user->hasRole('Supervisor');

        return view('user-preference.index', compact('pref', 'isSupervisor') );
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
        $pref = UserPreference::where('user_id', Auth::id() )->first();

        if (!$pref) {
            $pref = new UserPreference;
            $pref->user_id = Auth::id();
            $pref->created_by_id = Auth::id();
        }
        
        $pref->goal_comment_flag = $request->has('goal_comment_flag') ? $request->goal_comment_flag : 'N';
        $pref->goal_bank_flag = $request->has('goal_bank_flag') ? $request->goal_bank_flag : 'N';
        $pref->share_profile_flag = $request->has('share_profile_flag') ? $request->share_profile_flag : 'N';

        $pref->conversation_setup_flag = 'Y';   // always assign to 'Y'
        $pref->conversation_signoff_flag = 'Y';   // always assign to 'Y'
        $pref->conversation_disagree_flag = 'Y';   // always assign to 'Y'

        $pref->conversation_due_month = $request->has('conversation_due_month') ? $request->conversation_due_month : 'N';
        $pref->conversation_due_week = $request->has('conversation_due_week') ? $request->conversation_due_week : 'N';
        $pref->conversation_due_past = 'Y';   // always assign to 'Y'

        $pref->team_conversation_due_month = $request->has('team_conversation_due_month') ? $request->team_conversation_due_month : 'N';
        $pref->team_conversation_due_week = $request->has('team_conversation_due_week') ? $request->team_conversation_due_week : 'N';
        $pref->team_conversation_due_past = 'Y';   // always assign to 'Y'

        $pref->updated_by_id = Auth::id();
        $pref->save();

        return redirect()->route('dashboard')
                            ->with('success', 'update user preference successful.');

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

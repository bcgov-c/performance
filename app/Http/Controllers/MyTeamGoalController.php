<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use App\Models\User;
use App\Models\EmployeeShare;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyTeamGoalController extends Controller {

    public function shareMyGoals() {
        $goals = Goal::where('user_id', Auth::id())
            ->where('status', 'active')
            ->with('user')
            ->with('sharedWith')
            ->with('goalType')->get();
        $myTeamController = new MyTeamController();

        $employees = $myTeamController->myEmployeesAjax();

        // $adminShared=EmployeeShare::select('shared_with_id')
        // ->where('user_id', '=', Auth::id())
        // ->whereIn('shared_element_id', ['B', 'G'])
        // ->pluck('shared_with_id');
        // $adminemps = User::select('users.*')
        // ->whereIn('users.id', $adminShared)->get();
        // $employees = $employees->merge($adminemps);

        return view('my-team.goals.index', compact('goals', 'employees'));
    }

    public function teamGoalBank() {
        $myTeamController = new MyTeamController();
        return $myTeamController->showSugggestedGoals('my-team.goals.bank');
    }


    public function updateItemsToShare(Request $request) {
        $myTeamController = new MyTeamController();
        return $myTeamController->updateItemsToShare($request);
    }
}
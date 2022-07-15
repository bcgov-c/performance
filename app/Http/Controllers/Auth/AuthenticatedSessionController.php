<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\SharedProfile;
use App\Models\DashboardMessage;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {

        $request->authenticate();

        $request->session()->regenerate();

        // Assign Role "employee"
        $user = Auth::user();
        if (!($user->hasRole('employee'))) {
            $user->assignRole('employee');
        }

        // Save the last signon success time
        $user->last_signon_at = now();
        $user->save();

        // Grant or Remove 'Supervisor' Role based on ODS demo database
        $this->assignSupervisorRole($user);

        $dashboardmessage = DashboardMessage::get();
        foreach ($dashboardmessage as $message) {}

        if ($message->status) {
            return redirect()->intended(RouteServiceProvider::HOME)->with('showMessage', 1);
            // return '<a href="#' + row.id + '" onclick="showModal(' + row.id + ')" class="button edit-button">' + row.goal_type_name + '</a>';
        } else {
            return redirect()->intended(RouteServiceProvider::HOME);
        }
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    protected function assignSupervisorRole(User $user)
    {

        $role = 'Supervisor';

        $isManager = false;
        $hasSharedProfile = false;

        // To determine the login user whether is manager or not 
        $mgr = User::where('reporting_to', $user->id)->first();
        if ($mgr) {
            $isManager = true;
        } else {
            $isManager = false;
        }

        // To determine the login user whether has shared profile
        $sp = SharedProfile::where('shared_with', $user->id )->first();
        if ($sp) {
            $hasSharedProfile = true;
        } else {
            $hasSharedProfile = false;
        }

        // Assign/Rovoke Role when is manager or has shared Profile
        if ($user->hasRole($role)) {
            if (!($isManager or $hasSharedProfile)) {
                $user->removeRole($role);
            }
        } else {
            if ($isManager or $hasSharedProfile) {
                $user->assignRole($role);
            }
        }
    }
}

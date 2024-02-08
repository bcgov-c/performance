<?php

namespace App\Http\Controllers\Auth;

use Carbon\Carbon;
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

        // additional checking
        $result  = $this->canLoginIn($request, Auth::user());

        if (!$result) {
            $request->session()->invalidate();
            return redirect('/login')
                    ->with('error-psft', 'You do not have active PeopleSoft HCM account or not authority to access.');
        }

        $request->session()->regenerate();

        // Assign Role "employee"
        $user = Auth::user();
        if (!($user->hasRole('employee'))) {
            $user->assignRole('employee');
        }

        // Save the last signon success time
        $user->last_signon_at = now();
        $user->save();

        // Write to access log
        \App\Models\AccessLog::create([
            'user_id' => $user->id,
            'login_at' => Carbon::now(), 
            'login_ip' => $request->getClientIp(),
            'login_method' => 'Laravel UI',
       ]);


        // Grant or Remove 'Supervisor' Role based on ODS demo database
        $this->assignSupervisorRole($user);

        $dashboardmessage = DashboardMessage::get();
        foreach ($dashboardmessage as $message) {}

        if ($message->status) {
            // console.log('Showing Popup');
            return redirect()->intended(RouteServiceProvider::HOME)->with('displayModalMessage', 1);
        } else {
            // console.log('Not showing Popup');
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

    protected function canLoginIn($request, $auth_user)
    {

        // dd([$keycloak_user, $keycloak_user->user['idir_user_guid'], $keycloak_user->user['idir_username'] ]);
        $guid = $auth_user->guid;

        if (empty( $guid)) {
            if (str_contains( $auth_user->email, '@example.com')) {
                return true;
            } else {
                return false;
            }
        }

        // Step 1: find the Authenicated User by GUID 
        // $isUser = User::where('source_type', 'HCM')
        //                 ->where('guid', $guid)
        //                 ->where('acctlock', 0)->first();
        $isUser = User::join('employee_demo','employee_demo.guid','users.guid')
            ->join('employee_demo_tree', 'employee_demo.orgid', 'employee_demo_tree.id')
            ->join('access_organizations','employee_demo_tree.organization_key','access_organizations.orgid')
            ->where('access_organizations.allow_login', 'Y')
            ->whereNull('employee_demo.date_deleted')
            ->whereRaw('employee_demo.pdp_excluded = 0')
            ->where('users.guid', $guid)
            ->where('users.acctlock', 0)
            ->select('users.*')
            ->first();

        // User was found, then update the signin information
        if ($isUser || str_contains( $auth_user->email, '@example.com') ) {
            return true;
        } else {
            return false;
        }
        
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

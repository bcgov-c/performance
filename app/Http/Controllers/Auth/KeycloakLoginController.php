<?php

namespace App\Http\Controllers\Auth;

use Auth;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\SharedProfile;
use App\Models\DashboardMessage;
use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;

class KeycloakLoginController extends Controller
{
    //
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();

    }

    public function handleProviderCallback(Request $request, $provider) 
    {

        try {
            $keycloak_user = Socialite::driver($provider)->user();

            // To Retrieve "samaccountname" and "GUID" from "id_token"
            $idToken = $keycloak_user->accessTokenResponseBody['id_token'];
            $parsedToken = $this->parseToken($idToken);

            // Check where the authenicated user has record in PeopleSoft via  ODS's Employee table
            $identity_provider = property_exists($parsedToken, 'identity_provider') ? $parsedToken->identity_provider : null;
            // $guid = property_exists($parsedToken, 'idir_user_guid') ? $parsedToken->idir_user_guid : null;

            // find the Authenicated User by GUID 
            // $isUser = User::where('guid', $guid)->where('acctlock',0)->first();

            $isUser = $this->getUserByGuidOrIDir($request, $keycloak_user, $identity_provider);

            if ($isUser) {

                // cache the token information in session
                session([
                    'accessToken' => $keycloak_user->token,
                    'refreshToken' => $keycloak_user->refreshToken,
                    'tokenExpiresIn' => $keycloak_user->expiresIn,
                ]);

                Auth::loginUsingId($isUser->id);
                $request->session()->regenerate();

                $dashboardmessage = DashboardMessage::get();
                foreach ($dashboardmessage as $message) {}

                if ($message->status) {
                    // console.log('Showing Popup');
                    return redirect('/dashboard')->with('displayModalMessage', 1);
                } else {
                    // console.log('Not showing Popup');
                    return redirect('/dashboard');
                }
                // return redirect('/');

            } else {

                return redirect('/login')
                    ->with('error-psft', 'You do not have active PeopleSoft HCM account or not authority to access.');

            }

        } catch (Exception $e) {

            return redirect('/login')
                //  ->with('error', 'Error requesting access token')
                //  ->with('errorDetail', $e->getMessage());
                ->with('error-psft', $e->getMessage() );

            // return redirect('/login')->with(['error'=>['OOPS! An error occurred while trying to login with idir account. Please try again.']]);
            // abort(403, 'Error! ' . $request->error .  ' [' . $request->error_description . ']');   
        }

    }

    
    public function destroy(Request $request)
    {

        
        // Update logout time in Access Log
        $login_method = empty(session('accessToken')) ? 'Laravel UI' : 'Keycloak';
        $accessLog = \App\Models\AccessLog::where('user_id', Auth::Id() )
                                        ->whereNull('logout_at')
                                        ->where('login_method', $login_method)
                                        ->orderBy('login_at', 'desc')
                                        ->first();   
        if ($accessLog) {
            $accessLog->logout_at = Carbon::now(); 
            $accessLog->save();
        }

        // Determine whether signon Azure or local database
        if (empty(session('accessToken'))) {
            $back_url = ('/login');

        } else {
            $back = urlencode(url('/login'));
            $back_url = env('KEYCLOAK_BASE_URL').'/realms/'.env('KEYCLOAK_REALM').'/protocol/openid-connect/logout?redirect_uri='.$back; // Redirect to Keycloak
        }

        // clean up token information 
        session()->forget('accessToken');
        session()->forget('refreshToken');
        session()->forget('tokenExpires');

        // same as AuthenticatedSessionController::logout();
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect($back_url);

    }


    private function parseToken ($token) {
        $base64Data = explode(".", $token)[1];
        return json_decode(base64_decode($base64Data));
    }

    protected function getUserByGuidOrIDir($request, $keycloak_user, $identity_provider)
    {

        // dd([$keycloak_user, $keycloak_user->user['idir_user_guid'], $keycloak_user->user['idir_username'] ]);
        $guid = $keycloak_user->user['idir_user_guid'];
        $idir  = $keycloak_user->user['idir_username'];

        // Step 1: find the Authenicated User by GUID 
        // $isUser = User::where('source_type', 'HCM')
        //                 ->where('guid', $guid)
        //                 ->where('acctlock', 0)->first();
        $isUser = User::join('employee_demo','employee_demo.guid','users.guid')
            ->join('employee_demo_tree', 'employee_demo_tree.id', 'employee_demo.orgid')
            ->join('access_organizations','employee_demo_tree.organization_key','access_organizations.orgid')
            ->where('access_organizations.allow_login', 'Y')
            ->whereNull('employee_demo.date_deleted')
            ->whereRaw('employee_demo.pdp_excluded = 0')
            ->where('users.guid', $guid)
            ->where('users.acctlock', 0)
            ->select('users.*')
            ->first();
 
        // User was found, then update the signin information
        if ($isUser) {

            if (!($isUser->hasRole('employee'))) {
                $isUser->assignRole('employee');
            }

            if ($isUser->keycloak_id != $keycloak_user->getId()) {
                // Assign values
                $isUser->identity_provider = $identity_provider;
                $isUser->keycloak_id = $keycloak_user->getId();
                $isUser->idir_email_addr = $keycloak_user->getEmail();
            }

            $isUser->last_signon_at = now();
            $isUser->save();

            // Grant or Remove 'Supervisor' Role based on ODS demo database
            $this->assignSupervisorRole( $isUser );

            // Insert record into Access Log 
            \App\Models\AccessLog::create([
                'user_id' => $isUser->id,
                'login_at' => Carbon::now(), 
                'login_ip' => $request->getClientIp(),
                'login_method' => 'Keycloak',
                'identity_provider' => $identity_provider,
           ]);

            return $isUser;

        } else {
            return null;
        }
        
    }

    private function assignSupervisorRole(User $user)
    {

        $role = 'Supervisor';

        $isManager = false;
        $hasSharedProfile = false;

        // To determine the login user whether is manager or not 
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

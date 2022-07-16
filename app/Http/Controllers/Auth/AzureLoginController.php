<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Auth\SocialiteBaseController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Models\DashboardMessage;

class AzureLoginController extends SocialiteBaseController {

    protected $col;
    public function __construct()
    {
        parent::__construct('azure');
        $this->col = $this->provider . '_id';
    }

    public function login()
    {
        return Socialite::driver($this->provider)
            ->scopes(['openid', 'email', 'profile'])
            ->redirect();
    }

    public function handleCallback() {
        try {

            // $token = Socialite::with($this->provider)->getAccessTokenResponse($request->code);
            $user = Socialite::with($this->provider)->user();
            
            $idToken = $user->accessTokenResponseBody['id_token'];
            $parsedToken = $this->parseToken($idToken);
            
            $isUser = User::where($this->col, $user->id)->first();
            if ($isUser) {
                Auth::login($isUser);

                $dashboardmessage = DashboardMessage::get();
                foreach ($dashboardmessage as $message) {}

                if ($message->status) {
                    // console.log('Showing Popup');
                    return redirect('/')->with('displayModalMessage', 1);
                } else {
                    // console.log('Not showing Popup');
                    return redirect('/');
                }
            } else {
                $createUser = User::create([
                    'name' => $user->name,
                    'email' => $user->email,
                    $this->col => $user->id,
                    'samaccountname' => $parsedToken->samaccountname,
                    'guid' => $parsedToken->bcgovGUID,
                    'password' => ''
                ]);

                Auth::login($createUser);

                if ($message->status) {
                    // console.log('Showing Popup');
                    return redirect('/')->with('displayModalMessage', 1);
                } else {
                    // console.log('Not showing Popup');
                    return redirect('/');
                }

            }
        } catch (Exception $exception) {
            abort(500);
        }
    }

    private function parseToken ($token) {
        $base64Data = explode(".", $token)[1];
        return json_decode(base64_decode($base64Data));
    }
}
<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\AzureLoginController;
use App\Http\Controllers\Auth\MicrosoftGraphLoginController;
/* use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController; */

use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use App\MicrosoftGraph\SendMail;


Route::get('/login/microsoft', [AzureLoginController::class, 'login'])->name('ms-login');

// Route::get('/login/microsoft/callback', [AzureLoginController::class, 'handleCallback'])->name('ms-callback');

// MS Graph API Authenication -- composer require league/oauth2-client  microsoft/microsoft-graph
Route::get('/login/microsoft/callback', [MicrosoftGraphLoginController::class, 'callback']);
Route::get('/login/graph', [MicrosoftGraphLoginController::class, 'signin'])
                 ->middleware('guest')
                 ->name('login');
Route::post('/logout', [MicrosoftGraphLoginController::class, 'destroy'])
                ->middleware('auth')
                ->name('logout');
// Test function                 
Route::get('/graph/sendmail', function() {

    $sendMail = new SendMail();
    $response = $sendMail->send(['james.poon@telus.com', 'myphd2@gmail.com'],   "test email from BC Govt",
         "this is for testing purpose");
    return view('dashboard');

})->middleware('auth')->name('sendmail');

/* 
Route::get('/register', [RegisteredUserController::class, 'create'])
                ->middleware('guest')
                ->name('register');

Route::post('/register', [RegisteredUserController::class, 'store'])
                ->middleware('guest'); 
*/

Route::get('/login', [AuthenticatedSessionController::class, 'create'])
                ->middleware('guest')
                ->name('login');

Route::post('/login', [AuthenticatedSessionController::class, 'store'])
                ->middleware('guest');
/* 
Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])
                ->middleware('guest')
                ->name('password.request');

Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
                ->middleware('guest')
                ->name('password.email');

Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])
                ->middleware('guest')
                ->name('password.reset');

Route::post('/reset-password', [NewPasswordController::class, 'store'])
                ->middleware('guest')
                ->name('password.update');

Route::get('/verify-email', [EmailVerificationPromptController::class, '__invoke'])
                ->middleware('auth')
                ->name('verification.notice');

Route::get('/verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
                ->middleware(['auth', 'signed', 'throttle:6,1'])
                ->name('verification.verify');

Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
                ->middleware(['auth', 'throttle:6,1'])
                ->name('verification.send');

Route::get('/confirm-password', [ConfirmablePasswordController::class, 'show'])
                ->middleware('auth')
                ->name('password.confirm');

Route::post('/confirm-password', [ConfirmablePasswordController::class, 'store'])
                ->middleware('auth');
 */
/*
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
                ->middleware('auth')
                ->name('logout');
*/
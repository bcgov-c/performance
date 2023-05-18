<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    $user = $request->user();

    // Check if the user has the necessary roles
    if ($user->hasRole(['role0', 'role1','role2', 'role3','role4', 'role5'])) {
        return $user;
    } else {
        return response()->json(['error' => 'User does not have the right roles'], 403);
    }
});
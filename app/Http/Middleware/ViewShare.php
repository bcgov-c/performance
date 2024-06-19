<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class ViewShare
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if(session()->has('view-profile-as')) {
            $viewingProfileAs = $request->session()->get('view-profile-as');
            // $actualAuthId = session()->has('original-auth-id') ? session()->get('original-auth-id') : Auth::id();
            // 
            // dd($viewingProfileAs);
            $employee = User::find($viewingProfileAs);
            
            // $listOfEmployee = $employee->reportingManager->reportees()->get();
            $listOfEmployee = $employee->reportingManager ? $employee->reportingManager->reportees()->get() : [];
            View::share('viewingProfileAs', $employee);
            View::share('listOfEmployee', $listOfEmployee);            
            View::share('disableEdit', true);
        } else {
            View::share('disableEdit', false);
        }
        return $next($request);
    }
}

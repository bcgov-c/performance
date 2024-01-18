<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Goal;
use App\Models\SharedProfile;
use App\Models\UserDemoJrView;
use App\Models\Position;
use App\Models\EmployeeDemo;

class LoadTestController extends Controller
{
    public function loadTest()
    {
        return view('load-test');
    }
    
    public function simulateLoad(Request $request)
    {
        $numberOfUsers = $request->input('number_of_users', 1); // Default to 100 users

        $limit = $request->input('limit', 100); // Default to 100 users

        $startTime = microtime(true);

        // Run a loop to simulate user creation
        for ($i = 0; $i < $numberOfUsers; $i++) {
            $users = User::where('excused_flag', '<>', '1')
                    ->take($limit)
                    ->get();
        }

        $endTime = microtime(true);

        $queryTime = $endTime - $startTime;

        // Add feedback about query time, CPU usage, etc.
        $feedback = [
            'query_time' => $queryTime,
            // Add more metrics as needed
        ];

        return response()->json($feedback);
    }
}

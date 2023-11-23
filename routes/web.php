<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserPreferenceController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
    Route::get('/', function () {
        // return view('welcome');
        return redirect('/login');
    })->middleware('cache.headers:no_cache,private,max_age=300;etag');

    Route::middleware(['ViewShare'])->group(function () {
        Route::match(['get', 'post', 'delete', 'put'], '/dashboard', [DashboardController::class, 'index'])->middleware(['auth'])->name('dashboard');
        Route::get('/dashboardmessage/{id}',[DashboardController::class, 'show'])->name('dashboardmessage.show');
        Route::delete('/dashboard/{id}',[DashboardController::class, 'destroy'])->name('dashboard.destroy');
        Route::delete('/dashboarddeleteall',[DashboardController::class, 'destroyall'])->name('dashboard.destroyall');
        Route::post('/dashboardupdatestatus',[DashboardController::class, 'updatestatus'])->name('dashboard.updatestatus');
        Route::post('/dashboardUpdateSupervisor',[DashboardController::class, 'updateSupervisor'])->name('dashboard.updateSupervisor');
        Route::get('/dashboardUpdateJob',[DashboardController::class, 'updateJob']);
        Route::post('/dashboardUpdateJob',[DashboardController::class, 'updateJob'])->name('dashboard.updateJob');
        // Route::get('/dashboardresetstatus', [DashboardController::class, 'index'])->middleware(['auth'])->name('dashboard.notifications');
        Route::post('/dashboardresetstatus',[DashboardController::class, 'resetstatus'])->name('dashboard.resetstatus');
        Route::get('/dashboardmessage-badgecount',[DashboardController::class, 'badgeCount'])->name('dashboard.badgecount');
        Route::middleware(['auth'])->group(function () {
            require __DIR__ . '/goal.php';
            require __DIR__ . '/conversation.php';
            require __DIR__ . '/resource.php';
            require __DIR__ . '/my-team.php';
            require __DIR__ . '/hradmin.php';
            require __DIR__ . '/sysadmin.php';
        });
        Route::get('/dashboard/check-api',[DashboardController::class, 'checkApi'])->middleware(['auth'])->name('dashboard.check-api');
    });

    Route::get('/my-performance', function () {
        return view('my-performance');
    })->middleware(['auth'])->name('my-performance');

    

    require __DIR__.'/auth.php';
    Route::get('dashboard/revert-identity', [DashboardController::class, 'revertIdentity'])->name('dashboard.revert-identity');

    Route::middleware(['auth'])->group(function() {
        Route::resource('user-preference', UserPreferenceController::class)->only(['index', 'store']);
    });
    
    Route::get('check-session-expiration', [DashboardController::class, 'checkExpiration'])->name('check-session-expiration');
    Route::view('session-expired', 'session-expired')->name('session-expired');
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SysadminController;
use App\Http\Controllers\SysAdmin\EmployeeListController;
use App\Http\Controllers\SysAdmin\SysAdminSharedController;


Route::group(['middleware' => ['role:Service Representative']], function () 
{
    Route::group(['middleware' => ['auth']], function() {    
        //Shared functions v3.0
        Route::get('/sysadmin/org-list/{index}/{level}', [SysAdminSharedController::class,'getOrganizationList']);
        Route::get('/sysadmin/switch-identity', [SysadminController::class, 'switchIdentity'])->name('sysadmin.switch-identity');
        Route::get('/sysadmin/identity-list', [SysadminController::class, 'identityList'])->name('sysadmin.identity-list');    
        Route::get('/sysadmin/switch-identity-action', [SysadminController::class, 'switchIdentityAction'])->name('sysadmin.switch-identity-action');
        //Employee List
        Route::get('/sysadmin/employeelists', [EmployeeListController::class, 'currentList'])->name('sysadmin.employeelists');
        Route::get('/sysadmin/employeelists/currentlist', [EmployeeListController::class, 'currentList'])->name('sysadmin.employeelists.currentlist');
        Route::get('/sysadmin/employeelists/getcurrentlist', [EmployeeListController::class, 'getCurrentList'])->name('sysadmin.employeelists.getcurrentlist');
        Route::get('/sysadmin/employeelists/pastlist', [EmployeeListController::class, 'pastList'])->name('sysadmin.employeelists.pastlist');
        Route::get('/sysadmin/employeelists/getpastlist', [EmployeeListController::class, 'getPastList'])->name('sysadmin.employeelists.getpastlist');
        Route::get('/sysadmin/employeelists/export-current/{param?}', [EmployeeListController::class, 'exportCurrent'])->name('sysadmin.employeelists.export-current');
        Route::get('/sysadmin/employeelists/export-past/{param?}', [EmployeeListController::class, 'exportPast'])->name('sysadmin.employeelists.export-past');
    });
});


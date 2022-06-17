<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MyOrgController;
use App\Http\Controllers\HRadminController;
use App\Http\Controllers\GenericTemplateController;
use App\Http\Controllers\HRAdmin\NotificationController;
use App\Http\Controllers\HRAdmin\MyOrganizationController;
use App\Http\Controllers\HRAdmin\StatisticsReportController;
use App\Http\Controllers\HRAdmin\GoalBankController;
use App\Http\Controllers\HRAdmin\EmployeeSharesController;


Route::group(['middleware' => ['role:HR Admin']], function () 
{

    Route::get('/hradmin/org-organizations', [HRadminController::class,'getOrganizations']);
    Route::get('/hradmin/org-programs', [HRadminController::class,'getPrograms']);
    Route::get('/hradmin/org-divisions', [HRadminController::class,'getDivisions']);
    Route::get('/hradmin/org-branches', [HRadminController::class,'getBranches']);
    Route::get('/hradmin/org-level4', [HRadminController::class,'getLevel4']);


    //Goal Bank
    Route::group(['middleware' => ['auth']], function() {    
        Route::get('hradmin/myorg', [MyOrganizationController::class, 'index'])->name('hradmin.myorg');
        Route::get('hradmin/myorg/myorganization', [MyOrganizationController::class, 'getList'])->name('hradmin.myorg.myorganization');
        Route::post('hradmin/myorg/myorganization', [MyOrganizationController::class, 'index'])->name('hradmin.myorg.myorganization');

        Route::get('/hradmin/myorg/org-tree', [MyOrganizationController::class,'loadOrganizationTree']);
        Route::get('/hradmin/myorg/org-organizations', [MyOrganizationController::class,'getOrganizations']);
        Route::get('/hradmin/myorg/org-programs', [MyOrganizationController::class,'getPrograms']);
        Route::get('/hradmin/myorg/org-divisions', [MyOrganizationController::class,'getDivisions']);
        Route::get('/hradmin/myorg/org-branches', [MyOrganizationController::class,'getBranches']);
        Route::get('/hradmin/myorg/org-level4', [MyOrganizationController::class,'getLevel4']);
    });


    //Goal Bank
    Route::group(['middleware' => ['auth']], function() {    
        Route::get('/hradmin/goalbank', [GoalBankController::class, 'createindex'])->name('hradmin.goalbank');
        Route::get('/hradmin/goalbank/creategoal', [GoalBankController::class, 'createindex'])->name('hradmin.goalbank.createindex');
        Route::post('/hradmin/goalbank/creategoal', [GoalBankController::class, 'createindex'])->name('hradmin.goalbank.search');
        Route::get('/hradmin/goalbank/editpage/{id}', [GoalBankController::class, 'editpage'])->name('hradmin.goalbank.editpage');
        Route::post('/hradmin/goalbank/editpage/{id}', [GoalBankController::class, 'editpage'])->name('hradmin.goalbank.editpagepost');
        Route::get('/hradmin/goalbank/editone/{id}', [GoalBankController::class, 'editone'])->name('hradmin.goalbank.editone');
        Route::post('/hradmin/goalbank/editone/{id}', [GoalBankController::class, 'editone'])->name('hradmin.goalbank.editonepost');
        Route::get('/hradmin/goalbank/deletegoal/{id}', [GoalBankController::class, 'deletegoal'])->name('hradmin.goalbank.deletegoalget');
        Route::get('/hradmin/goalbank/deleteorg/{id}', [GoalBankController::class, 'deleteorg'])->name('hradmin.goalbank.deleteorgget');
        Route::post('/hradmin/goalbank/deleteorg/{id}', [GoalBankController::class, 'deleteorg'])->name('hradmin.goalbank.deleteorg');
        Route::get('/hradmin/goalbank/deleteindividual/{id}', [GoalBankController::class, 'deleteindividual'])->name('hradmin.goalbank.deleteindividualget');
        Route::post('/hradmin/goalbank/deleteindividual/{id}', [GoalBankController::class, 'deleteindividual'])->name('hradmin.goalbank.deleteindividual');
        Route::post('/hradmin/goalbank/deletegoal/{id}', [GoalBankController::class, 'deletegoal'])->name('hradmin.goalbank.deletegoal');
        Route::get('/hradmin/goalbank/updategoal', [GoalBankController::class, 'updategoal'])->name('hradmin.goalbank.updategoalget');
        Route::post('/hradmin/goalbank/updategoal', [GoalBankController::class, 'updategoal'])->name('hradmin.goalbank.updategoal');
        Route::get('/hradmin/goalbank/updategoalone', [GoalBankController::class, 'updategoalone'])->name('hradmin.goalbank.updategoaloneget');
        Route::post('/hradmin/goalbank/updategoalone', [GoalBankController::class, 'updategoalone'])->name('hradmin.goalbank.updategoalone');
        Route::get('/hradmin/goalbank/addnewgoal', [GoalBankController::class, 'addnewgoal'])->name('hradmin.goalbank.addnewgoalget');
        Route::post('/hradmin/goalbank/addnewgoal', [GoalBankController::class, 'addnewgoal'])->name('hradmin.goalbank.addnewgoal');
        Route::get('/hradmin/goalbank/savenewgoal', [GoalBankController::class, 'savenewgoal'])->name('hradmin.goalbank.savenewgoalget');
        Route::post('/hradmin/goalbank/savenewgoal', [GoalBankController::class, 'savenewgoal'])->name('hradmin.goalbank.savenewgoal');
        
        Route::get('/hradmin/goalbank/org-tree', [GoalBankController::class,'loadOrganizationTree']);
        Route::get('/hradmin/goalbank/org-organizations', [GoalBankController::class,'getOrganizations']);
        Route::get('/hradmin/goalbank/org-programs', [GoalBankController::class,'getPrograms']);
        Route::get('/hradmin/goalbank/org-divisions', [GoalBankController::class,'getDivisions']);
        Route::get('/hradmin/goalbank/org-branches', [GoalBankController::class,'getBranches']);
        Route::get('/hradmin/goalbank/org-level4', [GoalBankController::class,'getLevel4']);
        Route::get('/hradmin/goalbank/employees/{id}', [GoalBankController::class,'getEmployees']);
        Route::get('/hradmin/goalbank/employee-list', [GoalBankController::class, 'getDatatableEmployees'])->name('hradmin.goalbank.employee.list');

        Route::get('/hradmin/goalbank/aorg-tree', [GoalBankController::class,'aloadOrganizationTree']);
        Route::get('/hradmin/goalbank/aorg-organizations', [GoalBankController::class,'agetOrganizations']);
        Route::get('/hradmin/goalbank/aorg-programs', [GoalBankController::class,'agetPrograms']);
        Route::get('/hradmin/goalbank/aorg-divisions', [GoalBankController::class,'agetDivisions']);
        Route::get('/hradmin/goalbank/aorg-branches', [GoalBankController::class,'agetBranches']);
        Route::get('/hradmin/goalbank/aorg-level4', [GoalBankController::class,'agetLevel4']);
        Route::get('/hradmin/goalbank/aemployees/{id}', [GoalBankController::class,'agetEmployees']);
        Route::get('/hradmin/goalbank/aemployee-list', [GoalBankController::class, 'agetDatatableEmployees'])->name('hradmin.goalbank.aemployee.list');

        Route::get('/hradmin/goalbank/eorg-tree', [GoalBankController::class,'eloadOrganizationTree']);
        Route::get('/hradmin/goalbank/eorg-organizations', [GoalBankController::class,'egetOrganizations']);
        Route::get('/hradmin/goalbank/eorg-programs', [GoalBankController::class,'egetPrograms']);
        Route::get('/hradmin/goalbank/eorg-divisions', [GoalBankController::class,'egetDivisions']);
        Route::get('/hradmin/goalbank/eorg-branches', [GoalBankController::class,'egetBranches']);
        Route::get('/hradmin/goalbank/eorg-level4', [GoalBankController::class,'egetLevel4']);
        Route::get('/hradmin/goalbank/eemployees/{id}', [GoalBankController::class,'egetEmployees']);
        Route::get('/hradmin/goalbank/eemployee-list', [GoalBankController::class, 'egetDatatableEmployees'])->name('hradmin.goalbank.eemployee.list');

        Route::get('/hradmin/goalbank/managegoals', [GoalBankController::class, 'manageindex'])->name('hradmin.goalbank.manageindex');
        Route::get('/hradmin/goalbank/managegetlist', [GoalBankController::class, 'managegetList'])->name('hradmin.goalbank.managegetlist');
        Route::get('/hradmin/goalbank/getgoalorgs/{goal_id}', [GoalBankController::class, 'getgoalorgs'])->name('hradmin.goalbank.getgoalorgs');
        Route::get('/hradmin/goalbank/getgoalinds/{goal_id}', [GoalBankController::class, 'getgoalinds'])->name('hradmin.goalbank.getgoalinds');
    });


    //Shared Employees
    Route::group(['middleware' => ['auth']], function() {    
        // Route::get('/hradmin/employeeshares/addindex', [EmployeeSharesController::class, 'addindex'])->name('hradmin.employeeshares.addindex');
        // Route::post('/hradmin/employeeshares/addindex', [EmployeeSharesController::class, 'addindex'])->name('hradmin.employeeshares.addindexpost');
        Route::post('/hradmin/employeeshares/saveall', [EmployeeSharesController::class, 'saveall'])->name('hradmin.employeeshares.saveall');

        Route::get('/hradmin/employeeshares/manageindex', [EmployeeSharesController::class, 'manageindex'])->name('hradmin.employeeshares.manageindex');
        Route::post('/hradmin/employeeshares/manageindex', [EmployeeSharesController::class, 'manageindex'])->name('hradmin.employeeshares.manageindexpost');
        Route::get('/hradmin/employeeshares/manageindexlist', [EmployeeSharesController::class, 'manageindexlist'])->name('hradmin.employeeshares.manageindexlist');

        Route::get('/hradmin/employeeshares/org-tree', [EmployeeSharesController::class,'loadOrganizationTree']);
        Route::get('/hradmin/employeeshares/org-organizations', [EmployeeSharesController::class,'getOrganizations']);
        Route::get('/hradmin/employeeshares/org-programs', [EmployeeSharesController::class,'getPrograms']);
        Route::get('/hradmin/employeeshares/org-divisions', [EmployeeSharesController::class,'getDivisions']);
        Route::get('/hradmin/employeeshares/org-branches', [EmployeeSharesController::class,'getBranches']);
        Route::get('/hradmin/employeeshares/org-level4', [EmployeeSharesController::class,'getLevel4']);
        Route::get('/hradmin/employeeshares/employee-list', [EmployeeSharesController::class, 'getDatatableEmployees'])->name('hradmin.employeeshares.employee.list');
        Route::get('/hradmin/employeeshares/employees/{id}', [EmployeeSharesController::class,'getEmployees']);

        Route::get('/hradmin/employeeshares/eorg-tree', [EmployeeSharesController::class,'eloadOrganizationTree']);
        Route::get('/hradmin/employeeshares/eorg-organizations', [EmployeeSharesController::class,'egetOrganizations']);
        Route::get('/hradmin/employeeshares/eorg-programs', [EmployeeSharesController::class,'egetPrograms']);
        Route::get('/hradmin/employeeshares/eorg-divisions', [EmployeeSharesController::class,'egetDivisions']);
        Route::get('/hradmin/employeeshares/eorg-branches', [EmployeeSharesController::class,'egetBranches']);
        Route::get('/hradmin/employeeshares/eorg-level4', [EmployeeSharesController::class,'egetLevel4']);
        Route::get('/hradmin/employeeshares/eemployee-list', [EmployeeSharesController::class, 'egetDatatableEmployees'])->name('hradmin.employeeshares.eemployee.list');

        Route::get('/hradmin/employeeshares/addnew', [EmployeeSharesController::class, 'addnew'])->name('hradmin.employeeshares.addnew');

});
  
    Route::get('/hradmin/notifications', [NotificationController::class, 'index'])->name('hradmin.notifications');
    Route::get('/hradmin/notifications/detail/{notification_id}', [NotificationController::class, 'show']);
    Route::get('/hradmin/notifications/notify', [NotificationController::class, 'notify'])->name('hradmin.notifications.notify');
    Route::post('/hradmin/notifications/notify', [NotificationController::class, 'notify'])->name('hradmin.notifications.search');
    Route::post('/hradmin/notifications/notify-send', [NotificationController::class, 'send'])->name('hradmin.notifications.send');
    Route::get('/hradmin/notifications/users', [NotificationController::class, 'getUsers'])->name('hradmin.notifications.users.list');
    Route::resource('/hradmin/notifications/generic-template', GenericTemplateController::class)->except(['destroy']);
        
    Route::get('hradmin/statistics/goalsummary', [StatisticsReportController::class, 'goalsummary'])->name('hradmin.statistics.goalsummary');
    Route::get('hradmin/statistics/goalsummary-export', [StatisticsReportController::class, 'goalSummaryExport'])->name('hradmin.statistics.goalsummary.export');
    Route::get('hradmin/statistics/conversationsummary', [StatisticsReportController::class, 'conversationsummary'])->name('hradmin.statistics.conversationsummary');
    Route::get('hradmin/statistics/conversationsummary-export', [StatisticsReportController::class, 'conversationSummaryExport'])->name('hradmin.statistics.conversationsummary.export');
    Route::get('hradmin/statistics/sharedsummary', [StatisticsReportController::class, 'sharedsummary'])->name('hradmin.statistics.sharedsummary');
    Route::get('hradmin/statistics/sharedsummary-export', [StatisticsReportController::class, 'sharedSummaryExport'])->name('hradmin.statistics.sharedsummary.export');
    Route::get('hradmin/statistics/excusedsummary', [StatisticsReportController::class, 'excusedsummary'])->name('hradmin.statistics.excusedsummary');
    Route::get('hradmin/statistics/excusedsummary-export', [StatisticsReportController::class, 'excusedSummaryExport'])->name('hradmin.statistics.excusedsummary.export');


    Route::get('hradmin/level0', 'App\Http\Controllers\HRadminController@getOrgLevel0')->name('hradmin.level0');
    Route::get('hradmin/level1/{id0}', 'App\Http\Controllers\HRadminController@getOrgLevel1')->name('hradmin.level1');
    Route::get('hradmin/level2/{id0}/{id1}', 'App\Http\Controllers\HRadminController@getOrgLevel2')->name('hradmin.level2');
    Route::get('hradmin/level3/{id0}/{id1}/{id2}', 'App\Http\Controllers\HRadminController@getOrgLevel3')->name('hradmin.level3');
    Route::get('hradmin/level4/{id0}/{id1}/{id2}/{id3}', 'App\Http\Controllers\HRadminController@getOrgLevel4')->name('hradmin.level4');


});

Route::group(['middleware' => ['auth']], function () 
{
    Route::get('/hradmin/notifications/org-tree', [NotificationController::class,'loadOrganizationTree']);
    Route::get('/hradmin/notifications/org-organizations', [NotificationController::class,'getOrganizations']);
    Route::get('/hradmin/notifications/org-programs', [NotificationController::class,'getPrograms']);
    Route::get('/hradmin/notifications/org-divisions', [NotificationController::class,'getDivisions']);
    Route::get('/hradmin/notifications/org-branches', [NotificationController::class,'getBranches']);
    Route::get('/hradmin/notifications/org-level4', [NotificationController::class,'getLevel4']);
    Route::get('/hradmin/notifications/job-titles', [NotificationController::class,'getJobTitles']);
    Route::get('/hradmin/notifications/employees/{id}', [NotificationController::class,'getEmployees']);
    Route::get('/hradmin/notifications/employee-list', [NotificationController::class, 'getDatatableEmployees'])->name('hradmin.notifications.employee.list');

    Route::get('graph-users', [GenericTemplateController::class,'getUsers']);
});
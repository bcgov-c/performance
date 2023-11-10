<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MyOrgController;
use App\Http\Controllers\HRadminController;
use App\Http\Controllers\GenericTemplateController;
use App\Http\Controllers\HRAdmin\ExcuseEmployeesController;
use App\Http\Controllers\HRAdmin\NotificationController;
use App\Http\Controllers\HRAdmin\MyOrganizationController;
use App\Http\Controllers\HRAdmin\StatisticsReportController;
use App\Http\Controllers\HRAdmin\GoalBankController;
use App\Http\Controllers\HRAdmin\EmployeeSharesController;
use App\Http\Controllers\HRAdmin\HRAdminSharedController;


Route::group(['middleware' => ['role:HR Admin']], function () 
{
    //Shared functions v3.0
    Route::get('/hradmin/org-list/{index}/{level}', [HRAdminSharedController::class,'getOrganizationList']);

    //Shared functions v2.0
    Route::get('/hradmin/org-organizations2', [HRAdminSharedController::class,'getOrganizationsV2']);
    Route::get('/hradmin/org-programs2', [HRAdminSharedController::class,'getProgramsV2']);
    Route::get('/hradmin/org-divisions2', [HRAdminSharedController::class,'getDivisionsV2']);
    Route::get('/hradmin/org-branches2', [HRAdminSharedController::class,'getBranchesV2']);
    Route::get('/hradmin/org-level42', [HRAdminSharedController::class,'getLevel4V2']);
    Route::get('/hradmin/eorg-organizations2', [HRAdminSharedController::class,'egetOrganizationsV2']);
    Route::get('/hradmin/eorg-programs2', [HRAdminSharedController::class,'egetProgramsV2']);
    Route::get('/hradmin/eorg-divisions2', [HRAdminSharedController::class,'egetDivisionsV2']);
    Route::get('/hradmin/eorg-branches2', [HRAdminSharedController::class,'egetBranchesV2']);
    Route::get('/hradmin/eorg-level42', [HRAdminSharedController::class,'egetLevel4V2']);
    Route::get('/hradmin/aorg-organizations2', [HRAdminSharedController::class,'agetOrganizationsV2']);
    Route::get('/hradmin/aorg-programs2', [HRAdminSharedController::class,'agetProgramsV2']);
    Route::get('/hradmin/aorg-divisions2', [HRAdminSharedController::class,'agetDivisionsV2']);
    Route::get('/hradmin/aorg-branches2', [HRAdminSharedController::class,'agetBranchesV2']);
    Route::get('/hradmin/aorg-level42', [HRAdminSharedController::class,'agetLevel4V2']);
    
    //Shared functions
    Route::get('/hradmin/org-organizations', [HRAdminSharedController::class,'getOrganizations']);
    Route::get('/hradmin/org-programs', [HRAdminSharedController::class,'getPrograms']);
    Route::get('/hradmin/org-divisions', [HRAdminSharedController::class,'getDivisions']);
    Route::get('/hradmin/org-branches', [HRAdminSharedController::class,'getBranches']);
    Route::get('/hradmin/org-level4', [HRAdminSharedController::class,'getLevel4']);
    Route::get('/hradmin/eorg-organizations', [HRAdminSharedController::class,'egetOrganizations']);
    Route::get('/hradmin/eorg-programs', [HRAdminSharedController::class,'egetPrograms']);
    Route::get('/hradmin/eorg-divisions', [HRAdminSharedController::class,'egetDivisions']);
    Route::get('/hradmin/eorg-branches', [HRAdminSharedController::class,'egetBranches']);
    Route::get('/hradmin/eorg-level4', [HRAdminSharedController::class,'egetLevel4']);
    Route::get('/hradmin/aorg-organizations', [HRAdminSharedController::class,'agetOrganizations']);
    Route::get('/hradmin/aorg-programs', [HRAdminSharedController::class,'agetPrograms']);
    Route::get('/hradmin/aorg-divisions', [HRAdminSharedController::class,'agetDivisions']);
    Route::get('/hradmin/aorg-branches', [HRAdminSharedController::class,'agetBranches']);
    Route::get('/hradmin/aorg-level4', [HRAdminSharedController::class,'agetLevel4']);

    //My Organization
    Route::group(['middleware' => ['auth']], function() {    
        Route::get('/hradmin/myorg', [MyOrganizationController::class, 'index'])->name('hradmin.myorg');
        Route::get('/hradmin/myorg/myorganization', [MyOrganizationController::class, 'getList'])->name('hradmin.myorg.myorganization');
        Route::post('/hradmin/myorg/myorganization', [MyOrganizationController::class, 'index'])->name('hradmin.myorg.myorganization');
        Route::get('/hradmin/myorg/reporteeslist/{id}/{posn}', [MyOrganizationController::class, 'reporteesList'])->name('hradmin.myorg.reporteeslist');
    });

    //Goal Bank
    Route::group(['middleware' => ['auth']], function() {    
        Route::get('/hradmin/goalbank', [GoalBankController::class, 'createindex'])->name('hradmin.goalbank');
        Route::get('/hradmin/goalbank/creategoal', [GoalBankController::class, 'createindex'])->name('hradmin.goalbank.createindex');
        Route::post('/hradmin/goalbank/creategoal', [GoalBankController::class, 'createindex'])->name('hradmin.goalbank.search');
        Route::get('/hradmin/goalbank/editpage/{id}', [GoalBankController::class, 'editpage'])->name('hradmin.goalbank.editpage');
        Route::post('/hradmin/goalbank/editpage/{id}', [GoalBankController::class, 'editpage'])->name('hradmin.goalbank.editpagepost');
        Route::get('/hradmin/goalbank/editone/{id}', [GoalBankController::class, 'editone'])->name('hradmin.goalbank.editone');
        Route::get('/hradmin/goalbank/editdetails/{id}', [GoalBankController::class, 'editdetails'])->name('hradmin.goalbank.editdetails');
        Route::post('/hradmin/goalbank/editdetails/{id}', [GoalBankController::class, 'editdetails'])->name('hradmin.goalbank.editdetailspost');
        Route::post('/hradmin/goalbank/editone/{id}', [GoalBankController::class, 'editone'])->name('hradmin.goalbank.editonepost');
        Route::get('/hradmin/goalbank/deletegoal/{id}', [GoalBankController::class, 'deletegoal'])->name('hradmin.goalbank.deletegoalget');
        Route::get('/hradmin/goalbank/deleteorg/{id}', [GoalBankController::class, 'deleteorg'])->name('hradmin.goalbank.deleteorgget');
        Route::post('/hradmin/goalbank/deleteorg/{id}', [GoalBankController::class, 'deleteorg'])->name('hradmin.goalbank.deleteorg');
        Route::get('/hradmin/goalbank/deleteindividual/{id}', [GoalBankController::class, 'deleteindividual'])->name('hradmin.goalbank.deleteindividualget');
        Route::post('/hradmin/goalbank/deleteindividual/{id}', [GoalBankController::class, 'deleteindividual'])->name('hradmin.goalbank.deleteindividual');
        Route::post('/hradmin/goalbank/deletegoal/{id}', [GoalBankController::class, 'deletegoal'])->name('hradmin.goalbank.deletegoal');
        Route::get('/hradmin/goalbank/updategoal', [GoalBankController::class, 'updategoal'])->name('hradmin.goalbank.updategoalget');
        Route::post('/hradmin/goalbank/updategoal', [GoalBankController::class, 'updategoal'])->name('hradmin.goalbank.updategoal');
        Route::get('/hradmin/goalbank/updategoalone/{id}', [GoalBankController::class, 'updategoalone'])->name('hradmin.goalbank.updategoaloneget');
        Route::post('/hradmin/goalbank/updategoalone/{id}', [GoalBankController::class, 'updategoalone'])->name('hradmin.goalbank.updategoalone');
        Route::get('/hradmin/goalbank/updategoaldetails/{id}', [GoalBankController::class, 'updategoaldetails'])->name('hradmin.goalbank.updategoaldetailsget');
        Route::post('/hradmin/goalbank/updategoaldetails/{id}', [GoalBankController::class, 'updategoaldetails'])->name('hradmin.goalbank.updategoaldetails');
        Route::get('/hradmin/goalbank/addnewgoal', [GoalBankController::class, 'addnewgoal'])->name('hradmin.goalbank.addnewgoalget');
        Route::post('/hradmin/goalbank/addnewgoal', [GoalBankController::class, 'addnewgoal'])->name('hradmin.goalbank.addnewgoal');
        Route::get('/hradmin/goalbank/savenewgoal', [GoalBankController::class, 'savenewgoal'])->name('hradmin.goalbank.savenewgoalget');
        Route::post('/hradmin/goalbank/savenewgoal', [GoalBankController::class, 'savenewgoal'])->name('hradmin.goalbank.savenewgoal');
        
        Route::get('/hradmin/goalbank/org-tree/{index}', [GoalBankController::class,'loadOrganizationTree']);
        Route::get('/hradmin/goalbank/employees/{id}/{option?}', [GoalBankController::class,'getEmployees'])->name('hradmin.goalbank.getEmployees');
        Route::get('/hradmin/goalbank/employee-list/{option?}', [GoalBankController::class, 'getDatatableEmployees'])->name('hradmin.goalbank.employee.list');

        Route::get('/hradmin/goalbank/managegoals', [GoalBankController::class, 'manageindex'])->name('hradmin.goalbank.manageindex');
        Route::get('/hradmin/goalbank/managegetlist', [GoalBankController::class, 'managegetList'])->name('hradmin.goalbank.managegetlist');
        Route::get('/hradmin/goalbank/getgoalorgs/{goal_id}', [GoalBankController::class, 'getgoalorgs'])->name('hradmin.goalbank.getgoalorgs');
        Route::get('/hradmin/goalbank/getgoalinds/{goal_id}', [GoalBankController::class, 'getgoalinds'])->name('hradmin.goalbank.getgoalinds');

        Route::get('/hradmin/goalbank/getfilteredlist', [GoalBankController::class, 'getFilteredList'])->name('hradmin.goalbank.getfilteredlist');
    });


    //Shared Employees
    Route::group(['middleware' => ['auth']], function() {    
        Route::get('/hradmin/employeeshares', [EmployeeSharesController::class, 'addnew'])->name('hradmin.employeeshares');
        Route::get('/hradmin/employeeshares/addnew', [EmployeeSharesController::class, 'addnew'])->name('hradmin.employeeshares.addnew');
        Route::post('/hradmin/employeeshares/saveall', [EmployeeSharesController::class, 'saveall'])->name('hradmin.employeeshares.saveall');

        Route::get('/hradmin/employeeshares/manageindex', [EmployeeSharesController::class, 'manageindex'])->name('hradmin.employeeshares.manageindex');
        Route::post('/hradmin/employeeshares/manageindex', [EmployeeSharesController::class, 'manageindex'])->name('hradmin.employeeshares.manageindexpost');
        Route::get('/hradmin/employeeshares/manageindexlist', [EmployeeSharesController::class, 'manageindexlist'])->name('hradmin.employeeshares.manageindexlist');

        Route::get('/hradmin/employeeshares/deleteshare/{id}', [EmployeeSharesController::class, 'deleteshare'])->name('hradmin.employeeshares.deleteshareget');
        Route::delete('/hradmin/employeeshares/deleteshare/{id}', [EmployeeSharesController::class, 'deleteshare'])->name('hradmin.employeeshares.deleteshare');
        Route::get('hradmin/employeeshares/manageindexviewshares/{id}', [EmployeeSharesController::class, 'manageindexviewshares']);
        Route::get('/hradmin/employeeshares/deleteitem/{id}/{part?}', [EmployeeSharesController::class, 'deleteitem'])->name('hradmin.employeeshares.deleteitemget');
        Route::delete('/hradmin/employeeshares/deleteitem/{id}/{part?}', [EmployeeSharesController::class, 'deleteitem'])->name('hradmin.employeeshares.deleteitem');
        Route::get('/hradmin/employeeshares/deletemultishare/{ids}', [EmployeeSharesController::class, 'deleteMultiShare'])->name('hradmin.employeeshares.deletemultishareget');
        Route::delete('/hradmin/employeeshares/deletemultishare/{ids}', [EmployeeSharesController::class, 'deleteMultiShare'])->name('hradmin.employeeshares.deletemultishare');
        Route::get('/hradmin/employeeshares/org-tree/{index}', [EmployeeSharesController::class,'loadOrganizationTree']);
        Route::get('/hradmin/employeeshares/employee-list/{index}', [EmployeeSharesController::class, 'getDatatableEmployees']);
        Route::get('/hradmin/employeeshares/employees/{id}/{index}', [EmployeeSharesController::class,'getEmployees']);
        Route::get('/hradmin/employeeshares/removeallshare/{id}', [EmployeeSharesController::class, 'removeAllShare'])->name('hradmin.employeeshares.removeallshareget');
        Route::delete('/hradmin/employeeshares/removeallshare/{id}', [EmployeeSharesController::class, 'removeAllShare'])->name('hradmin.employeeshares.removeallshare');

        Route::get('/hradmin/employeeshares/getfilteredlist', [EmployeeSharesController::class, 'getFilteredList'])->name('hradmin.employeeshares.getfilteredlist');

        Route::get('/hradmin/profile-shared-with/{user_id}', [EmployeeSharesController::class, 'getProfileSharedWith'])->name('hradmin.employeeshares.profile-shared-with');
        Route::post('/hradmin/profile-shared-with/{shared_profile_id}', [EmployeeSharesController::class, 'updateProfileSharedWith'])->name('hradmin.employeeshares.profile-shared-with.update');

        Route::post('/hradmin/employeeshares/share-profile', [EmployeeSharesController::class, 'shareProfile'])->name('hradmin.employeeshares.share-profile'); 
        // Route::get('/hradmin/employeeshares/{user_id}', [EmployeeSharesController::class, 'getProfileSharedWith'])->name('hradmin.employeeshares.profile-shared-with'); 
        // Route::post('/hradmin/employeeshares/{shared_profile_id}', [EmployeeSharesController::class, 'updateProfileSharedWith'])->name('hradmin.employeeshares.profile-shared-with.update'); 
    });

    //Excuse Employees
    Route::group(['middleware' => ['auth']], function() {    
        Route::get('/hradmin/excuseemployees', [ExcuseEmployeesController::class, 'addindex'])->name('hradmin.excuseemployees');
        Route::get('/hradmin/excuseemployees/addindex', [ExcuseEmployeesController::class, 'addindex'])->name('hradmin.excuseemployees.addindex');
        Route::post('/hradmin/excuseemployees/saveexcuse', [ExcuseEmployeesController::class, 'saveexcuse'])->name('hradmin.excuseemployees.saveexcuse');
        Route::post('/hradmin/excuseemployees/addindex', [ExcuseEmployeesController::class, 'addindex'])->name('hradmin.excuseemployees.search');
        Route::get('/hradmin/excuseemployees/employee-list', [ExcuseEmployeesController::class, 'getDatatableEmployees'])->name('hradmin.excuseemployees.employee.list');

        Route::get('/hradmin/excuseemployees/managehistory', [ExcuseEmployeesController::class, 'managehistory'])->name('hradmin.excuseemployees.managehistory');
        Route::get('/hradmin/excuseemployees/managehistorylist', [ExcuseEmployeesController::class, 'managehistorylist'])->name('hradmin.excuseemployees.managehistorylist');

        Route::get('/hradmin/excuseemployees/manageindexupdate', [ExcuseEmployeesController::class, 'manageindexupdate'])->name('hradmin.excuseemployees.manageindexupdate');
        Route::post('/hradmin/excuseemployees/manageindexupdate', [ExcuseEmployeesController::class, 'manageindexupdate']);
        Route::get('/hradmin/excuseemployees/manageindexclear/{id}', [ExcuseEmployeesController::class, 'manageindexclear']);

        Route::get('/hradmin/excuseemployees/org-tree/{index}', [ExcuseEmployeesController::class,'loadOrganizationTree']);
        Route::get('/hradmin/excuseemployees/employees/{id}', [ExcuseEmployeesController::class,'getEmployees']);

        Route::get('/hradmin/excuseemployees/getfilteredlist', [ExcuseEmployeesController::class, 'getFilteredList'])->name('hradmin.excuseemployees.getfilteredlist');
    });

  
    Route::get('/hradmin/notifications', [NotificationController::class, 'index'])->name('hradmin.notifications');
    Route::get('/hradmin/notifications/detail/{notification_id}', [NotificationController::class, 'show']);
    Route::get('/hradmin/notifications/notify', [NotificationController::class, 'notify'])->name('hradmin.notifications.notify');
    Route::post('/hradmin/notifications/notify', [NotificationController::class, 'notify'])->name('hradmin.notifications.search');
    Route::post('/hradmin/notifications/notify-send', [NotificationController::class, 'send'])->name('hradmin.notifications.send');
    Route::get('/hradmin/notifications/users', [NotificationController::class, 'getUsers'])->name('hradmin.notifications.users.list');
    Route::resource('/hradmin/notifications/generic-template', GenericTemplateController::class)->except(['destroy']);

    // Statictics and Reporting 
    Route::get('/hradmin/statistics', [StatisticsReportController::class, 'goalsummary'])->name('hradmin.statistics');
    Route::get('/hradmin/statistics/goalsummary', [StatisticsReportController::class, 'goalsummary'])->name('hradmin.statistics.goalsummary');
    Route::get('/hradmin/statistics/goalsummary-export', [StatisticsReportController::class, 'goalSummaryExport'])->name('hradmin.statistics.goalsummary.export');
    Route::get('/hradmin/statistics/goalsummary-tag-export', [StatisticsReportController::class, 'goalSummaryTagExport'])->name('hradmin.statistics.goalsummary.tag.export');
    Route::get('/hradmin/statistics/conversationsummary', [StatisticsReportController::class, 'conversationsummary'])->name('hradmin.statistics.conversationsummary');
    Route::get('/hradmin/statistics/conversationstatus', [StatisticsReportController::class, 'conversationstatus'])->name('hradmin.statistics.conversationstatus');
    Route::get('/hradmin/statistics/conversationsummary-export', [StatisticsReportController::class, 'conversationSummaryExport'])->name('hradmin.statistics.conversationsummary.export');
    Route::get('/hradmin/statistics/conversationstatus-export', [StatisticsReportController::class, 'conversationStatusExport'])->name('hradmin.statistics.conversationstatus.export');
    Route::get('/hradmin/statistics/sharedsummary', [StatisticsReportController::class, 'sharedsummary'])->name('hradmin.statistics.sharedsummary');
    Route::get('/hradmin/statistics/sharedsummary-export', [StatisticsReportController::class, 'sharedSummaryExport'])->name('hradmin.statistics.sharedsummary.export');
    Route::get('/hradmin/statistics/excusedsummary', [StatisticsReportController::class, 'excusedsummary'])->name('hradmin.statistics.excusedsummary');
    Route::get('/hradmin/statistics/excusedsummary-export', [StatisticsReportController::class, 'excusedSummaryExport'])->name('hradmin.statistics.excusedsummary.export');
    Route::get('/hradmin/statistics/org-organizations', [StatisticsReportController::class,'getOrganizations']);
    Route::get('/hradmin/statistics/org-programs', [StatisticsReportController::class,'getPrograms']);
    Route::get('/hradmin/statistics/org-divisions', [StatisticsReportController::class,'getDivisions']);
    Route::get('/hradmin/statistics/org-branches', [StatisticsReportController::class,'getBranches']);
    Route::get('/hradmin/statistics/org-level4', [StatisticsReportController::class,'getLevel4']);

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

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SysadminController;
use App\Http\Controllers\SysAdmin\EmployeeListController;
use App\Http\Controllers\GenericTemplateController;
use App\Http\Controllers\SysAdmin\GoalBankController;
use App\Http\Controllers\SysAdmin\NotificationController;
use App\Http\Controllers\SysAdmin\EmployeeSharesController;
use App\Http\Controllers\SysAdmin\ExcuseEmployeesController;
use App\Http\Controllers\SysAdmin\SysadminStatisticsReportController;
use App\Http\Controllers\SysAdmin\AccessPermissionsController;
use App\Http\Controllers\SysAdmin\UnlockConversationController;


Route::group(['middleware' => ['role:Sys Admin']], function () {

    //Employee List
    Route::group(['middleware' => ['auth']], function() 
    {    
        Route::get('sysadmin/employeelists', [EmployeeListController::class, 'currentList'])->name('sysadmin.employeelists');
        Route::get('sysadmin/employeelists/currentlist', [EmployeeListController::class, 'currentList'])->name('sysadmin.employeelists.currentlist');
        Route::get('sysadmin/employeelists/getcurrentlist', [EmployeeListController::class, 'getCurrentList'])->name('sysadmin.employeelists.getcurrentlist');
        Route::get('sysadmin/employeelists/pastlist', [EmployeeListController::class, 'pastList'])->name('sysadmin.employeelists.pastlist');
        Route::get('sysadmin/employeelists/getpastlist', [EmployeeListController::class, 'getPastList'])->name('sysadmin.employeelists.getpastlist');
        // Route::post('sysadmin/employees/currentemployees', [CurrentEmployeesController::class, 'index'])->name('sysadmin.employees.currentemployees');
        Route::get('/sysadmin/employeelists/org-organizations', [EmployeeListController::class,'getOrganizations']);
        Route::get('/sysadmin/employeelists/org-programs', [EmployeeListController::class,'getPrograms']);
        Route::get('/sysadmin/employeelists/org-divisions', [EmployeeListController::class,'getDivisions']);
        Route::get('/sysadmin/employeelists/org-branches', [EmployeeListController::class,'getBranches']);
    Route::get('/sysadmin/employeelists/org-level4', [EmployeeListController::class,'getLevel4']);
    });

  
    Route::get('sysadmin/get-identities', [SysadminController::class, 'getIdentities'])->name('sysadmin.get-identities');
    

    //Unlock
    Route::group(['middleware' => ['auth']], function() 
    {    
        Route::get('/sysadmin/unlock', [UnlockConversationController::class, 'index'])->name('sysadmin.unlock');
        Route::get('/sysadmin/unlock/unlockconversation', [UnlockConversationController::class, 'index'])->name('sysadmin.unlock.unlockconversation');
        Route::post('/sysadmin/unlock/unlockconversation', [UnlockConversationController::class, 'index'])->name('sysadmin.unlock.unlockconversation.search');
        Route::get('/sysadmin/unlock/locked-conversation-list', [UnlockConversationController::class, 'getDatatableConversations'])->name('sysadmin.unlock.lockedconversation.list');
        Route::put('/sysadmin/unlock/unlockconversation/{id}', [UnlockConversationController::class, 'update'])->name('sysadmin.unlock.unlockconversation.store');
        Route::get('/sysadmin/unlock/manageunlocked', [UnlockConversationController::class, 'indexManageUnlocked'])->name('sysadmin.unlock.manageunlocked');
        Route::post('/sysadmin/unlock/manageunlocked', [UnlockConversationController::class, 'indexManageUnlocked'])->name('sysadmin.unlock.manageunlocked.search');
        Route::get('/sysadmin/unlock/unlocked-conversation-list', [UnlockConversationController::class, 'getDatatableManagedUnlocked'])->name('sysadmin.unlock.unlockconversation.list');
    });


    Route::get('sysadmin/statistics/goalsummary', [SysadminStatisticsReportController::class, 'goalsummary'])->name('sysadmin.statistics.goalsummary');
    Route::get('sysadmin/statistics/goalsummary-export', [SysadminStatisticsReportController::class, 'goalSummaryExport'])->name('sysadmin.statistics.goalsummary.export');
    Route::get('sysadmin/statistics/conversationsummary', [SysadminStatisticsReportController::class, 'conversationsummary'])->name('sysadmin.statistics.conversationsummary');
    Route::get('sysadmin/statistics/conversationsummary-export', [SysadminStatisticsReportController::class, 'conversationSummaryExport'])->name('sysadmin.statistics.conversationsummary.export');
    Route::get('sysadmin/statistics/sharedsummary', [SysadminStatisticsReportController::class, 'sharedsummary'])->name('sysadmin.statistics.sharedsummary');
    Route::get('sysadmin/statistics/sharedsummary-export', [SysadminStatisticsReportController::class, 'sharedSummaryExport'])->name('sysadmin.statistics.sharedsummary.export');
    Route::get('sysadmin/statistics/excusedsummary', [SysadminStatisticsReportController::class, 'excusedsummary'])->name('sysadmin.statistics.excusedsummary');
    Route::get('sysadmin/statistics/excusedsummary-export', [SysadminStatisticsReportController::class, 'excusedSummaryExport'])->name('sysadmin.statistics.excusedsummary.export');


    //Goal Bank
    Route::group(['middleware' => ['auth']], function() {    
        Route::get('/sysadmin/goalbank', [GoalBankController::class, 'createindex'])->name('sysadmin.goalbank');
        Route::get('/sysadmin/goalbank/creategoal', [GoalBankController::class, 'createindex'])->name('sysadmin.goalbank.createindex');
        Route::post('/sysadmin/goalbank/creategoal', [GoalBankController::class, 'createindex'])->name('sysadmin.goalbank.search');
        Route::get('/sysadmin/goalbank/editpage/{id}', [GoalBankController::class, 'editpage'])->name('sysadmin.goalbank.editpage');
        Route::post('/sysadmin/goalbank/editpage/{id}', [GoalBankController::class, 'editpage'])->name('sysadmin.goalbank.editpagepost');
        Route::get('/sysadmin/goalbank/editone/{id}', [GoalBankController::class, 'editone'])->name('sysadmin.goalbank.editone');
        Route::post('/sysadmin/goalbank/editone/{id}', [GoalBankController::class, 'editone'])->name('sysadmin.goalbank.editonepost');
        Route::get('/sysadmin/goalbank/deleteorg/{id}', [GoalBankController::class, 'deleteorg'])->name('sysadmin.goalbank.deleteorgget');
        Route::post('/sysadmin/goalbank/deleteorg/{id}', [GoalBankController::class, 'deleteorg'])->name('sysadmin.goalbank.deleteorg');
        Route::get('/sysadmin/goalbank/deleteindividual/{id}', [GoalBankController::class, 'deleteindividual'])->name('sysadmin.goalbank.deleteindividualget');
        Route::post('/sysadmin/goalbank/deleteindividual/{id}', [GoalBankController::class, 'deleteindividual'])->name('sysadmin.goalbank.deleteindividual');
        Route::get('/sysadmin/goalbank/deletegoal/{id}', [GoalBankController::class, 'deletegoal'])->name('sysadmin.goalbank.deletegoalget');
        Route::post('/sysadmin/goalbank/deletegoal/{id}', [GoalBankController::class, 'deletegoal'])->name('sysadmin.goalbank.deletegoal');
        Route::get('/sysadmin/goalbank/updategoal', [GoalBankController::class, 'updategoal'])->name('sysadmin.goalbank.updategoalget');
        Route::post('/sysadmin/goalbank/updategoal', [GoalBankController::class, 'updategoal'])->name('sysadmin.goalbank.updategoal');
        Route::get('/sysadmin/goalbank/updategoalone', [GoalBankController::class, 'updategoalone'])->name('sysadmin.goalbank.updategoaloneget');
        Route::post('/sysadmin/goalbank/updategoalone', [GoalBankController::class, 'updategoalone'])->name('sysadmin.goalbank.updategoalone');
        Route::get('/sysadmin/goalbank/addnewgoal', [GoalBankController::class, 'addnewgoal'])->name('sysadmin.goalbank.addnewgoalget');
        Route::post('/sysadmin/goalbank/addnewgoal', [GoalBankController::class, 'addnewgoal'])->name('sysadmin.goalbank.addnewgoal');
        Route::get('/sysadmin/goalbank/savenewgoal', [GoalBankController::class, 'savenewgoal'])->name('sysadmin.goalbank.savenewgoalget');
        Route::post('/sysadmin/goalbank/savenewgoal', [GoalBankController::class, 'savenewgoal'])->name('sysadmin.goalbank.savenewgoal');

        Route::get('/sysadmin/goalbank/org-tree', [GoalBankController::class,'loadOrganizationTree']);
        Route::get('/sysadmin/goalbank/org-organizations', [GoalBankController::class,'getOrganizations'])->name('sysadmin.goalbank.org-organizations');
        Route::get('/sysadmin/goalbank/org-programs', [GoalBankController::class,'getPrograms'])->name('sysadmin.goalbank.org-programs');
        Route::get('/sysadmin/goalbank/org-divisions', [GoalBankController::class,'getDivisions'])->name('sysadmin.goalbank.org-divisions');
        Route::get('/sysadmin/goalbank/org-branches', [GoalBankController::class,'getBranches'])->name('sysadmin.goalbank.org-branches');
        Route::get('/sysadmin/goalbank/org-level4', [GoalBankController::class,'getLevel4'])->name('sysadmin.goalbank.org-level4');
        Route::get('/sysadmin/goalbank/employees/{id}', [GoalBankController::class,'getEmployees']);
        Route::get('/sysadmin/goalbank/employee-list', [GoalBankController::class, 'getDatatableEmployees'])->name('sysadmin.goalbank.employee.list');

        Route::get('/sysadmin/goalbank/aorg-tree', [GoalBankController::class,'aloadOrganizationTree']);
        Route::get('/sysadmin/goalbank/aorg-organizations', [GoalBankController::class,'agetOrganizations']);
        Route::get('/sysadmin/goalbank/aorg-programs', [GoalBankController::class,'agetPrograms']);
        Route::get('/sysadmin/goalbank/aorg-divisions', [GoalBankController::class,'agetDivisions']);
        Route::get('/sysadmin/goalbank/aorg-branches', [GoalBankController::class,'agetBranches']);
        Route::get('/sysadmin/goalbank/aorg-level4', [GoalBankController::class,'agetLevel4']);
        Route::get('/sysadmin/goalbank/aemployees/{id}', [GoalBankController::class,'agetEmployees']);
        Route::get('/sysadmin/goalbank/aemployee-list', [GoalBankController::class, 'agetDatatableEmployees'])->name('sysadmin.goalbank.aemployee.list');

        Route::get('/sysadmin/goalbank/eorg-tree', [GoalBankController::class,'eloadOrganizationTree']);
        Route::get('/sysadmin/goalbank/eorg-organizations', [GoalBankController::class,'egetOrganizations']);
        Route::get('/sysadmin/goalbank/eorg-programs', [GoalBankController::class,'egetPrograms']);
        Route::get('/sysadmin/goalbank/eorg-divisions', [GoalBankController::class,'egetDivisions']);
        Route::get('/sysadmin/goalbank/eorg-branches', [GoalBankController::class,'egetBranches']);
        Route::get('/sysadmin/goalbank/eorg-level4', [GoalBankController::class,'egetLevel4']);
        Route::get('/sysadmin/goalbank/eemployees/{id}', [GoalBankController::class,'egetEmployees']);
        Route::get('/sysadmin/goalbank/eemployee-list', [GoalBankController::class, 'egetDatatableEmployees'])->name('sysadmin.goalbank.eemployee.list');

        Route::get('/sysadmin/goalbank/managegoals', [GoalBankController::class, 'manageindex'])->name('sysadmin.goalbank.manageindex');
        Route::get('/sysadmin/goalbank/managegetlist', [GoalBankController::class, 'managegetList'])->name('sysadmin.goalbank.managegetlist');
        Route::get('/sysadmin/goalbank/getgoalorgs/{goal_id}', [GoalBankController::class, 'getgoalorgs'])->name('sysadmin.goalbank.getgoalorgs');
        Route::get('/sysadmin/goalbank/getgoalinds/{goal_id}', [GoalBankController::class, 'getgoalinds'])->name('sysadmin.goalbank.getgoalinds');
    });


    //Excuse Employees
    Route::group(['middleware' => ['auth']], function() {    
        Route::get('/sysadmin/excuseemployees', [ExcuseEmployeesController::class, 'addindex'])->name('sysadmin.excuseemployees');
        Route::get('/sysadmin/excuseemployees/addindex', [ExcuseEmployeesController::class, 'addindex'])->name('sysadmin.excuseemployees.addindex');
        Route::post('/sysadmin/excuseemployees/saveexcuse', [ExcuseEmployeesController::class, 'saveexcuse'])->name('sysadmin.excuseemployees.saveexcuse');
        Route::post('/sysadmin/excuseemployees/addindex', [ExcuseEmployeesController::class, 'addindex'])->name('sysadmin.excuseemployees.search');
        Route::get('/sysadmin/excuseemployees/employee-list', [ExcuseEmployeesController::class, 'getDatatableEmployees'])->name('sysadmin.excuseemployees.employee.list');

        Route::get('/sysadmin/excuseemployees/manageindex', [ExcuseEmployeesController::class, 'manageindex'])->name('sysadmin.excuseemployees.manageindex');
        Route::get('/sysadmin/excuseemployees/manageindexlist', [ExcuseEmployeesController::class, 'manageindexlist'])->name('sysadmin.excuseemployees.manageindexlist');
        Route::get('/sysadmin/excuseemployees/manageindexedit/{id}', [ExcuseEmployeesController::class, 'manageindexedit'])->name('sysadmin.excuseemployees.manageindexedit');
        Route::post('/sysadmin/excuseemployees/manageindex/{id}', [ExcuseEmployeesController::class, 'manageindex']);
        Route::get('/sysadmin/excuseemployees/manageindexclear/{id}', [ExcuseEmployeesController::class, 'manageindexclear']);

        Route::get('/sysadmin/excuseemployees/org-tree', [ExcuseEmployeesController::class,'loadOrganizationTree']);
        Route::get('/sysadmin/excuseemployees/org-organizations', [ExcuseEmployeesController::class,'getOrganizations']);
        Route::get('/sysadmin/excuseemployees/org-programs', [ExcuseEmployeesController::class,'getPrograms']);
        Route::get('/sysadmin/excuseemployees/org-divisions', [ExcuseEmployeesController::class,'getDivisions']);
        Route::get('/sysadmin/excuseemployees/org-branches', [ExcuseEmployeesController::class,'getBranches']);
        Route::get('/sysadmin/excuseemployees/org-level4', [ExcuseEmployeesController::class,'getLevel4']);
        Route::get('/sysadmin/excuseemployees/employees/{id}', [ExcuseEmployeesController::class,'getEmployees']);
    });


    //Notifications
    Route::group(['middleware' => ['auth']], function() {    
        Route::get('/sysadmin/notifications', [NotificationController::class, 'index'])->name('sysadmin.notifications');
        Route::get('/sysadmin/notifications/detail/{notification_id}', [NotificationController::class, 'show']);
        Route::get('/sysadmin/notifications/notify', [NotificationController::class, 'notify'])->name('sysadmin.notifications.notify');
        Route::post('/sysadmin/notifications/notify', [NotificationController::class, 'notify'])->name('sysadmin.notifications.search');
        Route::post('/sysadmin/notifications/notify-send', [NotificationController::class, 'send'])->name('sysadmin.notifications.send');
        Route::get('/sysadmin/notifications/users', [NotificationController::class, 'getUsers'])->name('sysadmin.notifications.users.list');
        Route::resource('/sysadmin/notifications/generic-template', GenericTemplateController::class)->except(['destroy']);
        //Route::get('graph-users', [GenericTemplateController::class,'getUsers']);
        
        Route::get('/sysadmin/notifications/org-tree', [NotificationController::class,'loadOrganizationTree']);
        Route::get('/sysadmin/notifications/org-organizations', [NotificationController::class,'getOrganizations']);
        Route::get('/sysadmin/notifications/org-programs', [NotificationController::class,'getPrograms']);
        Route::get('/sysadmin/notifications/org-divisions', [NotificationController::class,'getDivisions']);
        Route::get('/sysadmin/notifications/org-branches', [NotificationController::class,'getBranches']);
        Route::get('/sysadmin/notifications/org-level4', [NotificationController::class,'getLevel4']);
        Route::get('/sysadmin/notifications/job-titles', [NotificationController::class,'getJobTitles']);
        Route::get('/sysadmin/notifications/employees/{id}', [NotificationController::class,'getEmployees']);
        Route::get('/sysadmin/notifications/employee-list', [NotificationController::class, 'getDatatableEmployees'])->name('sysadmin.notifications.employee.list');
    });


    //Shared Employees
    Route::group(['middleware' => ['auth']], function() {    
        Route::get('/sysadmin/employeeshares', [EmployeeSharesController::class, 'addnew'])->name('sysadmin.employeeshares');
        Route::get('/sysadmin/employeeshares/addnew', [EmployeeSharesController::class, 'addnew'])->name('sysadmin.employeeshares.addnew');
        Route::post('/sysadmin/employeeshares/saveall', [EmployeeSharesController::class, 'saveall'])->name('sysadmin.employeeshares.saveall');

        Route::get('/sysadmin/employeeshares/manageindex', [EmployeeSharesController::class, 'manageindex'])->name('sysadmin.employeeshares.manageindex');
        Route::delete('/sysadmin/employeeshares/manageindex', [EmployeeSharesController::class, 'manageindex'])->name('sysadmin.employeeshares.manageindexdelete');
        Route::get('/sysadmin/employeeshares/manageindexlist', [EmployeeSharesController::class, 'manageindexlist'])->name('sysadmin.employeeshares.manageindexlist');
        Route::get('/sysadmin/employeeshares/deleteshare/{id}', [EmployeeSharesController::class, 'deleteshare'])->name('sysadmin.employeeshares.deleteshareget');
        Route::delete('/sysadmin/employeeshares/deleteshare/{id}', [EmployeeSharesController::class, 'deleteshare'])->name('sysadmin.employeeshares.deleteshare');
        Route::get('sysadmin/employeeshares/manageindexviewshares/{id}', [EmployeeSharesController::class, 'manageindexviewshares']);
        Route::get('/sysadmin/employeeshares/deleteitem/{id}/{part?}', [EmployeeSharesController::class, 'deleteitem'])->name('sysadmin.employeeshares.deleteitemget');
        Route::delete('/sysadmin/employeeshares/deleteitem/{id}/{part?}', [EmployeeSharesController::class, 'deleteitem'])->name('sysadmin.employeeshares.deleteitem');

        Route::get('/sysadmin/employeeshares/org-tree', [EmployeeSharesController::class,'loadOrganizationTree'])->name('sysadmin.employeeshares.org-tree');
        Route::get('/sysadmin/employeeshares/org-organizations', [EmployeeSharesController::class,'getOrganizations'])->name('sysadmin.employeeshares.org-organizations');
        Route::get('/sysadmin/employeeshares/org-programs', [EmployeeSharesController::class,'getPrograms'])->name('sysadmin.employeeshares.org-programs');
        Route::get('/sysadmin/employeeshares/org-divisions', [EmployeeSharesController::class,'getDivisions'])->name('sysadmin.employeeshares.org-divisions');
        Route::get('/sysadmin/employeeshares/org-branches', [EmployeeSharesController::class,'getBranches'])->name('sysadmin.employeeshares.org-branches');
        Route::get('/sysadmin/employeeshares/org-level4', [EmployeeSharesController::class,'getLevel4'])->name('sysadmin.employeeshares.org-level4');
        Route::get('/sysadmin/employeeshares/employee-list', [EmployeeSharesController::class, 'getDatatableEmployees'])->name('sysadmin.employeeshares.employee.list');
        Route::get('/sysadmin/employeeshares/employees/{id}', [EmployeeSharesController::class,'getEmployees']);

        Route::get('/sysadmin/employeeshares/eorg-tree', [EmployeeSharesController::class,'eloadOrganizationTree']);
        Route::get('/sysadmin/employeeshares/eorg-organizations', [EmployeeSharesController::class,'egetOrganizations']);
        Route::get('/sysadmin/employeeshares/eorg-programs', [EmployeeSharesController::class,'egetPrograms']);
        Route::get('/sysadmin/employeeshares/eorg-divisions', [EmployeeSharesController::class,'egetDivisions']);
        Route::get('/sysadmin/employeeshares/eorg-branches', [EmployeeSharesController::class,'egetBranches']);
        Route::get('/sysadmin/employeeshares/eorg-level4', [EmployeeSharesController::class,'egetLevel4']);
        Route::get('/sysadmin/employeeshares/eemployee-list', [EmployeeSharesController::class, 'egetDatatableEmployees'])->name('sysadmin.employeeshares.eemployee.list');
        Route::get('/sysadmin/employeeshares/eemployees/{id}', [EmployeeSharesController::class,'egetEmployees']);
    });


    //Access and Permissions
    Route::group(['middleware' => ['auth']], function() {    
        Route::get('/sysadmin/accesspermissions', [AccessPermissionsController::class, 'index'])->name('sysadmin.accesspermissions');
        Route::get('/sysadmin/accesspermissions/detail/{notification_id}', [AccessPermissionsController::class, 'show']);
        Route::get('/sysadmin/accesspermissions/index', [AccessPermissionsController::class, 'index'])->name('sysadmin.accesspermissions.index');
        Route::post('/sysadmin/accesspermissions/index', [AccessPermissionsController::class, 'index'])->name('sysadmin.accesspermissions.search');
        Route::post('/sysadmin/accesspermissions/saveaccess', [AccessPermissionsController::class, 'saveAccess'])->name('sysadmin.accesspermissions.saveaccess');
        Route::get('/sysadmin/accesspermissions/users', [AccessPermissionsController::class, 'getUsers'])->name('sysadmin.accesspermissions.users.list');
        
        Route::get('/sysadmin/accesspermissions/org-tree', [AccessPermissionsController::class,'loadOrganizationTree']);
        Route::get('/sysadmin/accesspermissions/org-organizations', [AccessPermissionsController::class,'getOrganizations']);
        Route::get('/sysadmin/accesspermissions/org-programs', [AccessPermissionsController::class,'getPrograms']);
        Route::get('/sysadmin/accesspermissions/org-divisions', [AccessPermissionsController::class,'getDivisions']);
        Route::get('/sysadmin/accesspermissions/org-branches', [AccessPermissionsController::class,'getBranches']);
        Route::get('/sysadmin/accesspermissions/org-level4', [AccessPermissionsController::class,'getLevel4']);
        Route::get('/sysadmin/accesspermissions/eorg-tree', [AccessPermissionsController::class,'eloadOrganizationTree']);
        Route::get('/sysadmin/accesspermissions/eorg-organizations', [AccessPermissionsController::class,'geteOrganizations']);
        Route::get('/sysadmin/accesspermissions/eorg-programs', [AccessPermissionsController::class,'getePrograms']);
        Route::get('/sysadmin/accesspermissions/eorg-divisions', [AccessPermissionsController::class,'geteDivisions']);
        Route::get('/sysadmin/accesspermissions/eorg-branches', [AccessPermissionsController::class,'geteBranches']);
        Route::get('/sysadmin/accesspermissions/eorg-level4', [AccessPermissionsController::class,'geteLevel4']);
        Route::get('/sysadmin/accesspermissions/job-titles', [AccessPermissionsController::class,'getJobTitles']);
        Route::get('/sysadmin/accesspermissions/employees/{id}', [AccessPermissionsController::class,'getEmployees']);
        Route::get('/sysadmin/accesspermissions/employee-list', [AccessPermissionsController::class, 'getDatatableEmployees'])->name('sysadmin.accesspermissions.employee.list');
        Route::get('sysadmin/accesspermissions/manageexistingaccess', [AccessPermissionsController::class, 'manageindex'])->name('sysadmin.accesspermissions.manageindex');
        Route::put('sysadmin/accesspermissions/manageexistingaccessupdate', [AccessPermissionsController::class, 'manageUpdate']);
        Route::get('sysadmin/accesspermissions/manageexistingaccessdelete/{model_id}', [AccessPermissionsController::class, 'manageDestroy']);
        Route::delete('sysadmin/accesspermissions/manageexistingaccessdelete/{model_id}', [AccessPermissionsController::class, 'manageDestroy'])->name('sysadmin.accesspermissions.manageexistingaccessdelete');
        Route::get('sysadmin/accesspermissions/get_access_entry/{role_id}/{model_id}', [AccessPermissionsController::class, 'get_access_entry']);
        Route::get('sysadmin/accesspermissions/manageexistingaccesslist', [AccessPermissionsController::class, 'getList'])->name('sysadmin.accesspermissions.manageexistingaccesslist');
        Route::get('sysadmin/accesspermissions/manageexistingaccessadmin/{user_id}', [AccessPermissionsController::class, 'getAdminOrgs'])->name('sysadmin.accesspermissions.manageexistingaccessadmin');
        Route::get('sysadmin/accesspermissions/accessedit/{id}', [AccessPermissionsController::class, 'manageEdit'])->name('sysadmin.accesspermissions.accessedit');
        Route::post('sysadmin/accesspermissions/accessupdate/{id}', [AccessPermissionsController::class, 'manageUpdate']);
    });


    Route::get('/sysadmin/org-organizations', [SysadminController::class,'getOrganizations']);
    Route::get('/sysadmin/org-programs', [SysadminController::class,'getPrograms']);
    Route::get('/sysadmin/org-divisions', [SysadminController::class,'getDivisions']);
    Route::get('/sysadmin/org-branches', [SysadminController::class,'getBranches']);
    Route::get('/sysadmin/org-level4', [SysadminController::class,'getLevel4']);

    
    Route::get('sysadmin/level0', 'App\Http\Controllers\SysadminController@getOrgLevel0')->name('sysadmin.level0');
    Route::get('sysadmin/level1/{id0}', 'App\Http\Controllers\SysadminController@getOrgLevel1')->name('sysadmin.level1');
    Route::get('sysadmin/level2/{id0}/{id1}', 'App\Http\Controllers\SysadminController@getOrgLevel2')->name('sysadmin.level2');
    Route::get('sysadmin/level3/{id0}/{id1}/{id2}', 'App\Http\Controllers\SysadminController@getOrgLevel3')->name('sysadmin.level3');
    Route::get('sysadmin/level4/{id0}/{id1}/{id2}/{id3}', 'App\Http\Controllers\SysadminController@getOrgLevel4')->name('sysadmin.level4');
        

    Route::get('sysadmin/switch-identity', [SysadminController::class, 'switchIdentity'])->name('sysadmin.switch-identity');
    Route::get('sysadmin/identity-list', [SysadminController::class, 'identityList'])->name('sysadmin.identity-list');    
    Route::get('sysadmin/switch-identity-action', [SysadminController::class, 'switchIdentityAction'])->name('sysadmin.switch-identity-action');
});

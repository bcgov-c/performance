<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SysadminController;
use App\Http\Controllers\SysAdmin\AuditingController;
use App\Http\Controllers\GenericTemplateController;
use App\Http\Controllers\SysAdmin\GoalBankController;
use App\Http\Controllers\SysAdmin\AccessLogController;
use App\Http\Controllers\SysAdmin\EmployeeListController;
use App\Http\Controllers\SysAdmin\NotificationController;
use App\Http\Controllers\SysAdmin\JobSchedAuditController;
use App\Http\Controllers\SysAdmin\MessageEditorController;
use App\Http\Controllers\SysAdmin\EmployeeSharesController;
use App\Http\Controllers\SysAdmin\SysAdminSharedController;
use App\Http\Controllers\SysAdmin\ExcuseEmployeesController;
use App\Http\Controllers\SysAdmin\AccessPermissionsController;
use App\Http\Controllers\SysAdmin\UnlockConversationController;
use App\Http\Controllers\SysAdmin\AccessOrganizationsController;
use App\Http\Controllers\SysAdmin\SysadminStatisticsReportController;
use App\Http\Controllers\SysAdmin\SupervisorOverridesController;
use App\Http\Controllers\SysAdmin\ResourceManageController;
use App\Http\Controllers\SysAdmin\ConversationTemplateController;
use App\Http\Controllers\SysAdmin\QueueStatusController;
// use Illuminate\Http\Request;


Route::group(['middleware' => ['role:Sys Admin']], function () 
{
    // //Shared functions v3.0
    // Route::get('/sysadmin/org-list/{index}/{level}', [SysAdminSharedController::class,'getOrganizationList']);

    //Shared functions v2.0
    Route::get('/sysadmin/org-organizations2', [SysAdminSharedController::class,'getOrganizationsV2']);
    Route::get('/sysadmin/org-programs2', [SysAdminSharedController::class,'getProgramsV2']);
    Route::get('/sysadmin/org-divisions2', [SysAdminSharedController::class,'getDivisionsV2']);
    Route::get('/sysadmin/org-branches2', [SysAdminSharedController::class,'getBranchesV2']);
    Route::get('/sysadmin/org-level42', [SysAdminSharedController::class,'getLevel4V2']);
    Route::get('/sysadmin/eorg-organizations2', [SysAdminSharedController::class,'egetOrganizationsV2']);
    Route::get('/sysadmin/eorg-programs2', [SysAdminSharedController::class,'egetProgramsV2']);
    Route::get('/sysadmin/eorg-divisions2', [SysAdminSharedController::class,'egetDivisionsV2']);
    Route::get('/sysadmin/eorg-branches2', [SysAdminSharedController::class,'egetBranchesV2']);
    Route::get('/sysadmin/eorg-level42', [SysAdminSharedController::class,'egetLevel4V2']);
    Route::get('/sysadmin/aorg-organizations2', [SysAdminSharedController::class,'agetOrganizationsV2']);
    Route::get('/sysadmin/aorg-programs2', [SysAdminSharedController::class,'agetProgramsV2']);
    Route::get('/sysadmin/aorg-divisions2', [SysAdminSharedController::class,'agetDivisionsV2']);
    Route::get('/sysadmin/aorg-branches2', [SysAdminSharedController::class,'agetBranchesV2']);
    Route::get('/sysadmin/aorg-level42', [SysAdminSharedController::class,'agetLevel4V2']);
        
    //Shared functions
    Route::get('/sysadmin/org-organizations', [SysAdminSharedController::class,'getOrganizations']);
    Route::get('/sysadmin/org-programs', [SysAdminSharedController::class,'getPrograms']);
    Route::get('/sysadmin/org-divisions', [SysAdminSharedController::class,'getDivisions']);
    Route::get('/sysadmin/org-branches', [SysAdminSharedController::class,'getBranches']);
    Route::get('/sysadmin/org-level4', [SysAdminSharedController::class,'getLevel4']);
    Route::get('/sysadmin/eorg-organizations', [SysAdminSharedController::class,'egetOrganizations']);
    Route::get('/sysadmin/eorg-programs', [SysAdminSharedController::class,'egetPrograms']);
    Route::get('/sysadmin/eorg-divisions', [SysAdminSharedController::class,'egetDivisions']);
    Route::get('/sysadmin/eorg-branches', [SysAdminSharedController::class,'egetBranches']);
    Route::get('/sysadmin/eorg-level4', [SysAdminSharedController::class,'egetLevel4']);
    Route::get('/sysadmin/aorg-organizations', [SysAdminSharedController::class,'agetOrganizations']);
    Route::get('/sysadmin/aorg-programs', [SysAdminSharedController::class,'agetPrograms']);
    Route::get('/sysadmin/aorg-divisions', [SysAdminSharedController::class,'agetDivisions']);
    Route::get('/sysadmin/aorg-branches', [SysAdminSharedController::class,'agetBranches']);
    Route::get('/sysadmin/aorg-level4', [SysAdminSharedController::class,'agetLevel4']);

    
  
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

    // Statistics and Reporting
    Route::get('/sysadmin/statistics/goalsummary', [SysadminStatisticsReportController::class, 'goalsummary'])->name('sysadmin.statistics.goalsummary');
    Route::get('/sysadmin/statistics/goalsummary-export', [SysadminStatisticsReportController::class, 'goalSummaryExport'])->name('sysadmin.statistics.goalsummary.export');
    Route::get('/sysadmin/statistics/goalsummary-tag-export', [SysadminStatisticsReportController::class, 'goalSummaryTagExport'])->name('sysadmin.statistics.goalsummary.tag.export');
    Route::get('/sysadmin/statistics/conversationsummary', [SysadminStatisticsReportController::class, 'conversationsummary'])->name('sysadmin.statistics.conversationsummary');
    Route::get('/sysadmin/statistics/conversationstatus', [SysadminStatisticsReportController::class, 'conversationstatus'])->name('sysadmin.statistics.conversationstatus');
    Route::get('/sysadmin/statistics/conversationsummary-export', [SysadminStatisticsReportController::class, 'conversationSummaryExport'])->name('sysadmin.statistics.conversationsummary.export');
    Route::get('/sysadmin/statistics/conversationstatus-export', [SysadminStatisticsReportController::class, 'conversationStatusExport'])->name('sysadmin.statistics.conversationstatus.export');
    Route::get('/sysadmin/statistics/sharedsummary', [SysadminStatisticsReportController::class, 'sharedsummary'])->name('sysadmin.statistics.sharedsummary');
    Route::get('/sysadmin/statistics/sharedsummary-export', [SysadminStatisticsReportController::class, 'sharedSummaryExport'])->name('sysadmin.statistics.sharedsummary.export');
    Route::get('/sysadmin/statistics/excusedsummary', [SysadminStatisticsReportController::class, 'excusedsummary'])->name('sysadmin.statistics.excusedsummary');
    Route::get('/sysadmin/statistics/excusedsummary-export', [SysadminStatisticsReportController::class, 'excusedSummaryExport'])->name('sysadmin.statistics.excusedsummary.export');
    Route::get('/sysadmin/statistics/org-organizations', [SysadminStatisticsReportController::class,'getOrganizations']);
    Route::get('/sysadmin/statistics/org-programs', [SysadminStatisticsReportController::class,'getPrograms']);
    Route::get('/sysadmin/statistics/org-divisions', [SysadminStatisticsReportController::class,'getDivisions']);
    Route::get('/sysadmin/statistics/org-branches', [SysadminStatisticsReportController::class,'getBranches']);
    Route::get('/sysadmin/statistics/org-level4', [SysadminStatisticsReportController::class,'getLevel4']);
    
    Route::get('/sysadmin/statistics/filereports', [SysadminStatisticsReportController::class, 'filereports'])->name('sysadmin.statistics.filereports');
    Route::get('/sysadmin/statistics/filereports-export', [SysadminStatisticsReportController::class, 'filereportsExport'])->name('sysadmin.statistics.filereports.export');

    //Goal Bank
    Route::group(['middleware' => ['auth']], function() {    
        Route::get('/sysadmin/goalbank', [GoalBankController::class, 'createindex'])->name('sysadmin.goalbank');
        Route::get('/sysadmin/goalbank/creategoal', [GoalBankController::class, 'createindex'])->name('sysadmin.goalbank.createindex');
        Route::post('/sysadmin/goalbank/creategoal', [GoalBankController::class, 'createindex'])->name('sysadmin.goalbank.search');
        Route::get('/sysadmin/goalbank/editpage/{id}', [GoalBankController::class, 'editpage'])->name('sysadmin.goalbank.editpage');
        Route::post('/sysadmin/goalbank/editpage/{id}', [GoalBankController::class, 'editpage'])->name('sysadmin.goalbank.editpagepost');
        Route::get('/sysadmin/goalbank/editone/{id}', [GoalBankController::class, 'editone'])->name('sysadmin.goalbank.editone');
        Route::post('/sysadmin/goalbank/editone/{id}', [GoalBankController::class, 'editone'])->name('sysadmin.goalbank.editonepost');
        Route::get('/sysadmin/goalbank/editdetails/{id}', [GoalBankController::class, 'editdetails'])->name('sysadmin.goalbank.editdetails');
        Route::post('/sysadmin/goalbank/editdetails/{id}', [GoalBankController::class, 'editdetails'])->name('sysadmin.goalbank.editdetailspost');
        Route::get('/sysadmin/goalbank/deleteorg/{id}', [GoalBankController::class, 'deleteorg'])->name('sysadmin.goalbank.deleteorgget');
        Route::post('/sysadmin/goalbank/deleteorg/{id}', [GoalBankController::class, 'deleteorg'])->name('sysadmin.goalbank.deleteorg');
        Route::get('/sysadmin/goalbank/deleteindividual/{id}', [GoalBankController::class, 'deleteindividual'])->name('sysadmin.goalbank.deleteindividualget');
        Route::post('/sysadmin/goalbank/deleteindividual/{id}', [GoalBankController::class, 'deleteindividual'])->name('sysadmin.goalbank.deleteindividual');
        Route::get('/sysadmin/goalbank/deletegoal/{id}', [GoalBankController::class, 'deletegoal'])->name('sysadmin.goalbank.deletegoalget');
        Route::post('/sysadmin/goalbank/deletegoal/{id}', [GoalBankController::class, 'deletegoal'])->name('sysadmin.goalbank.deletegoal');
        Route::get('/sysadmin/goalbank/updategoal/{id}', [GoalBankController::class, 'updategoal'])->name('sysadmin.goalbank.updategoalget');
        Route::post('/sysadmin/goalbank/updategoal/{id}', [GoalBankController::class, 'updategoal'])->name('sysadmin.goalbank.updategoal');
        Route::get('/sysadmin/goalbank/updategoalone/{id}', [GoalBankController::class, 'updategoalone'])->name('sysadmin.goalbank.updategoaloneget');
        Route::post('/sysadmin/goalbank/updategoalone/{id}', [GoalBankController::class, 'updategoalone'])->name('sysadmin.goalbank.updategoalone');
        Route::get('/sysadmin/goalbank/updategoaldetails/{id}', [GoalBankController::class, 'updategoaldetails'])->name('sysadmin.goalbank.updategoaldetailsget');
        Route::post('/sysadmin/goalbank/updategoaldetails/{id}', [GoalBankController::class, 'updategoaldetails'])->name('sysadmin.goalbank.updategoaldetails');
        Route::get('/sysadmin/goalbank/addnewgoal', [GoalBankController::class, 'addnewgoal'])->name('sysadmin.goalbank.addnewgoalget');
        Route::post('/sysadmin/goalbank/addnewgoal', [GoalBankController::class, 'addnewgoal'])->name('sysadmin.goalbank.addnewgoal');
        Route::get('/sysadmin/goalbank/savenewgoal', [GoalBankController::class, 'savenewgoal'])->name('sysadmin.goalbank.savenewgoalget');
        Route::post('/sysadmin/goalbank/savenewgoal', [GoalBankController::class, 'savenewgoal'])->name('sysadmin.goalbank.savenewgoal');

        Route::get('/sysadmin/goalbank/org-tree/{index}', [GoalBankController::class,'loadOrganizationTree']);
        Route::get('/sysadmin/goalbank/employees/{id}/{option?}', [GoalBankController::class,'getEmployees'])->name('sysadmin.goalbank.getEmployees');
        Route::get('/sysadmin/goalbank/employee-list/{option?}', [GoalBankController::class, 'getDatatableEmployees'])->name('sysadmin.goalbank.employee.list');

        Route::get('/sysadmin/goalbank/managegoals', [GoalBankController::class, 'manageindex'])->name('sysadmin.goalbank.manageindex');
        Route::get('/sysadmin/goalbank/managegetlist', [GoalBankController::class, 'managegetList'])->name('sysadmin.goalbank.managegetlist');
        Route::get('/sysadmin/goalbank/getgoalorgs/{goal_id}', [GoalBankController::class, 'getgoalorgs'])->name('sysadmin.goalbank.getgoalorgs');
        Route::get('/sysadmin/goalbank/getgoalinds/{goal_id}', [GoalBankController::class, 'getgoalinds'])->name('sysadmin.goalbank.getgoalinds');

        Route::get('/sysadmin/goalbank/getfilteredlist', [GoalBankController::class, 'getFilteredList'])->name('sysadmin.goalbank.getfilteredlist');
    });

    //Excuse Employees
    Route::group(['middleware' => ['auth']], function() {    
        Route::get('/sysadmin/excuseemployees', [ExcuseEmployeesController::class, 'addindex'])->name('sysadmin.excuseemployees');
        Route::get('/sysadmin/excuseemployees/addindex', [ExcuseEmployeesController::class, 'addindex'])->name('sysadmin.excuseemployees.addindex');
        Route::post('/sysadmin/excuseemployees/addindex', [ExcuseEmployeesController::class, 'addindex'])->name('sysadmin.excuseemployees.search');
        Route::get('/sysadmin/excuseemployees/saveexcuse', [ExcuseEmployeesController::class, 'saveexcuse'])->name('sysadmin.excuseemployees.saveexcuse');
        Route::post('/sysadmin/excuseemployees/saveexcuse', [ExcuseEmployeesController::class, 'saveexcuse']);
        Route::get('/sysadmin/excuseemployees/employee-list', [ExcuseEmployeesController::class, 'getDatatableEmployees'])->name('sysadmin.excuseemployees.employee.list');

        Route::get('/sysadmin/excuseemployees/managehistory', [ExcuseEmployeesController::class, 'managehistory'])->name('sysadmin.excuseemployees.managehistory');
        Route::get('/sysadmin/excuseemployees/managehistorylist', [ExcuseEmployeesController::class, 'managehistorylist'])->name('sysadmin.excuseemployees.managehistorylist');

        Route::get('/sysadmin/excuseemployees/manageindexupdate', [ExcuseEmployeesController::class, 'manageindexupdate'])->name('sysadmin.excuseemployees.manageindexupdateget');
        Route::post('/sysadmin/excuseemployees/manageindexupdate', [ExcuseEmployeesController::class, 'manageindexupdate'])->name('sysadmin.excuseemployees.manageindexupdate');

        Route::get('/sysadmin/excuseemployees/org-tree/{index}', [ExcuseEmployeesController::class,'loadOrganizationTree']);
        Route::get('/sysadmin/excuseemployees/employees/{id}', [ExcuseEmployeesController::class,'getEmployees']);

        Route::get('/sysadmin/excuseemployees/getfilteredlist', [ExcuseEmployeesController::class, 'getFilteredList'])->name('sysadmin.excuseemployees.getfilteredlist');
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

    //ResourceManage
    Route::group(['middleware' => ['auth']], function() {
        Route::get('/sysadmin/resource-manage', [ResourceManageController::class, 'index'])->name('sysadmin.resource-manage');
        Route::get('/sysadmin/resource-manage/create', [ResourceManageController::class, 'create'])->name('resource-manage.create');
        Route::get('/sysadmin/resource-manage/show/{id}', [ResourceManageController::class, 'show'])->name('resource-manage.show');
        Route::get('/sysadmin/resource-manage/edit/{id}', [ResourceManageController::class, 'edit'])->name('resource-manage.edit');
        Route::post('/sysadmin/resource-manage/store/{id}', [ResourceManageController::class, 'store'])->name('resource-manage.store');
        Route::post('/sysadmin/resource-manage/new', [ResourceManageController::class, 'new'])->name('resource-manage.new');
    });

    //ConversationTemplate
    Route::group(['middleware' => ['auth']], function() {
        Route::get('/sysadmin/conversation-template', [ConversationTemplateController::class, 'index'])->name('sysadmin.conversation-template');
        Route::get('/sysadmin/conversation-template/show/{id}', [ConversationTemplateController::class, 'show'])->name('conversation-template.show');
        Route::get('/sysadmin/conversation-template/edit/{id}', [ConversationTemplateController::class, 'edit'])->name('conversation-template.edit');
        Route::post('/sysadmin/conversation-template/store/{id}', [ConversationTemplateController::class, 'store'])->name('conversation-template.store');
    });

    //  Job Schedule Audit 
    Route::middleware(['auth'])->prefix('sysadmin')->name('sysadmin.')->group(function() {    
        Route::resource('/auditing', AuditingController::class)->only(['index']);
    });


    //  Job Schedule Audit 
    Route::middleware(['auth'])->prefix('sysadmin')->name('sysadmin.')->group(function() {    
        Route::resource('/job-schedule-audit', JobSchedAuditController::class)->only(['index','show']);
    });

    // System Security -- Access Log 
    Route::get('/sysadmin/system-security/access-logs', [AccessLogController::class, 'index'])->name('sysadmin.system_security.access_logs');
    Route::get('/sysadmin/system-security/access-logs-export', [AccessLogController::class, 'export'])->name('sysadmin.system_security.access_logs_export');

    // System Security -- Access Organizations
    Route::resource('/sysadmin/system-security/access-orgs', AccessOrganizationsController::class)->except(['create', 'store', 'show', 'destroy']);
    Route::post('/sysadmin/system-security/access-orgs-toggle-allow-login', [AccessOrganizationsController::class,'toggleAllowLogin'])->name('access-orgs-toggle-allow-login');
    Route::post('/sysadmin/system-security/access-orgs-reset', [AccessOrganizationsController::class,'reset'])->name('access-orgs-reset');

    // System Security -- Access Organizations
    Route::resource('/sysadmin/system-security/access-orgs', AccessOrganizationsController::class)->except(['create', 'store', 'show', 'destroy']);
    Route::post('/sysadmin/system-security/access-orgs-toggle-allow-login', [AccessOrganizationsController::class,'toggleAllowLogin'])->name('access-orgs-toggle-allow-login');
    Route::post('/sysadmin/system-security/access-orgs-reset', [AccessOrganizationsController::class,'reset'])->name('access-orgs-reset');

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
        Route::get('/sysadmin/employeeshares/deletemultishare/{ids}', [EmployeeSharesController::class, 'deleteMultiShare'])->name('sysadmin.employeeshares.deletemultishareget');
        Route::delete('/sysadmin/employeeshares/deletemultishare/{ids}', [EmployeeSharesController::class, 'deleteMultiShare'])->name('sysadmin.employeeshares.deletemultishare');
        Route::get('sysadmin/employeeshares/manageindexviewshares/{id}', [EmployeeSharesController::class, 'manageindexviewshares']);
        Route::get('/sysadmin/employeeshares/deleteitem/{id}/{part?}', [EmployeeSharesController::class, 'deleteitem'])->name('sysadmin.employeeshares.deleteitemget');
        Route::delete('/sysadmin/employeeshares/deleteitem/{id}/{part?}', [EmployeeSharesController::class, 'deleteitem'])->name('sysadmin.employeeshares.deleteitem');
        Route::get('/sysadmin/employeeshares/removeallshare/{id}', [EmployeeSharesController::class, 'removeAllShare'])->name('sysadmin.employeeshares.removeallshareget');
        Route::delete('/sysadmin/employeeshares/removeallshare/{id}', [EmployeeSharesController::class, 'removeAllShare'])->name('sysadmin.employeeshares.removeallshare');

        Route::get('/sysadmin/employeeshares/org-tree/{index}', [EmployeeSharesController::class,'loadOrganizationTree']);
        Route::get('/sysadmin/employeeshares/employee-list/{index}', [EmployeeSharesController::class, 'getDatatableEmployees']);
        Route::get('/sysadmin/employeeshares/employees/{id}/{index}', [EmployeeSharesController::class,'getEmployees']);

        Route::get('/sysadmin/employeeshares/getfilteredlist', [EmployeeSharesController::class, 'getFilteredList'])->name('sysadmin.employeeshares.getfilteredlist');

        Route::get('/sysadmin/profile-shared-with/{user_id}', [EmployeeSharesController::class, 'getProfileSharedWith'])->name('sysadmin.employeeshares.profile-shared-with');
        Route::post('/sysadmin/profile-shared-with/{shared_profile_id}', [EmployeeSharesController::class, 'updateProfileSharedWith'])->name('sysadmin.employeeshares.profile-shared-with.update');

        Route::post('/sysadmin/employeeshares/share-profile', [EmployeeSharesController::class, 'shareProfile'])->name('sysadmin.employeeshares.share-profile'); 
        // Route::get('/sysadmin/employeeshares/{user_id}', [EmployeeSharesController::class, 'getProfileSharedWith'])->name('sysadmin.employeeshares.profile-shared-with'); 
        // Route::post('/sysadmin/employeeshares/{shared_profile_id}', [EmployeeSharesController::class, 'updateProfileSharedWith'])->name('sysadmin.employeeshares.profile-shared-with.update'); 

    });

    //Access and Permissions
    Route::group(['middleware' => ['auth']], function() {    
        Route::get('/sysadmin/accesspermissions', [AccessPermissionsController::class, 'index'])->name('sysadmin.accesspermissions');
        Route::get('/sysadmin/accesspermissions/detail/{notification_id}', [AccessPermissionsController::class, 'show']);
        Route::get('/sysadmin/accesspermissions/index', [AccessPermissionsController::class, 'index'])->name('sysadmin.accesspermissions.index');
        Route::post('/sysadmin/accesspermissions/index', [AccessPermissionsController::class, 'index'])->name('sysadmin.accesspermissions.search');
        Route::post('/sysadmin/accesspermissions/saveaccess', [AccessPermissionsController::class, 'saveAccess'])->name('sysadmin.accesspermissions.saveaccess');
        Route::get('/sysadmin/accesspermissions/users', [AccessPermissionsController::class, 'getUsers'])->name('sysadmin.accesspermissions.users.list');
        
        Route::get('/sysadmin/accesspermissions/org-tree/{index}', [AccessPermissionsController::class,'loadOrganizationTree']);
        Route::get('/sysadmin/accesspermissions/job-titles', [AccessPermissionsController::class,'getJobTitles']);
        Route::get('/sysadmin/accesspermissions/employees/{id}', [AccessPermissionsController::class,'getEmployees']);
        Route::get('/sysadmin/accesspermissions/employee-list', [AccessPermissionsController::class, 'getDatatableEmployees'])->name('sysadmin.accesspermissions.employee.list');
        Route::get('/sysadmin/accesspermissions/manageexistingaccess', [AccessPermissionsController::class, 'manageindex'])->name('sysadmin.accesspermissions.manageindex');
        Route::put('/sysadmin/accesspermissions/manageexistingaccessupdate', [AccessPermissionsController::class, 'manageUpdate']);
        Route::get('/sysadmin/accesspermissions/manageexistingaccessdelete/{model_id}/{role_id}', [AccessPermissionsController::class, 'manageDestroy']);
        Route::delete('/sysadmin/accesspermissions/manageexistingaccessdelete/{model_id}/{role_id}', [AccessPermissionsController::class, 'manageDestroy'])->name('sysadmin.accesspermissions.manageexistingaccessdelete');
        Route::get('/sysadmin/accesspermissions/get_access_entry/{role_id}/{model_id}', [AccessPermissionsController::class, 'get_access_entry']);
        Route::get('/sysadmin/accesspermissions/manageexistingaccesslist', [AccessPermissionsController::class, 'getList'])->name('sysadmin.accesspermissions.manageexistingaccesslist');
        Route::get('/sysadmin/accesspermissions/manageexistingaccessadmin/{user_id}', [AccessPermissionsController::class, 'getAdminOrgs'])->name('sysadmin.accesspermissions.manageexistingaccessadmin');
        Route::get('/sysadmin/accesspermissions/accessedit/{id}', [AccessPermissionsController::class, 'manageEdit'])->name('sysadmin.accesspermissions.accessedit');
        Route::post('/sysadmin/accesspermissions/accessupdate/{id}', [AccessPermissionsController::class, 'manageUpdate']);
        Route::get('/sysadmin/accesspermissions/deleteitem/{item_id}', [AccessPermissionsController::class, 'deleteitem'])->name('sysadmin.accesspermissions.deleteitemget');
        Route::delete('/sysadmin/accesspermissions/deleteitem/{item_id}', [AccessPermissionsController::class, 'deleteitem'])->name('sysadmin.accesspermissions.deleteitem');
        Route::get('/sysadmin/accesspermissions/deletemultiorgs/{item_ids}', [AccessPermissionsController::class, 'deleteMultiOrgs'])->name('sysadmin.accesspermissions.deletemultiorgsget');
        Route::delete('/sysadmin/accesspermissions/deletemultiorgs/{item_ids}', [AccessPermissionsController::class, 'deleteMultiOrgs'])->name('sysadmin.accesspermissions.deletemultiorgs');

        Route::get('/sysadmin/accesspermissions/getfilteredlist', [AccessPermissionsController::class, 'getFilteredList'])->name('sysadmin.accesspermissions.getfilteredlist');
    });

    //Welcome Message Editor
    Route::group(['middleware' => ['auth']], function() {    
        Route::get('/sysadmin/messageeditor', [MessageEditorController::class, 'index'])->name('sysadmin.messageeditor');
        Route::get('/sysadmin/messageeditor/index', [MessageEditorController::class, 'index'])->name('sysadmin.messageeditor.index');
        Route::post('/sysadmin/messageeditor/update', [MessageEditorController::class, 'update'])->name('sysadmin.messageeditor.update');
    });

    //Supervisor Overrides
    Route::group(['middleware' => ['auth']], function() {    
        Route::get('/sysadmin/supervisoroverrides', [SupervisorOverridesController::class, 'addnew'])->name('sysadmin.supervisoroverrides');
        Route::get('/sysadmin/supervisoroverrides/addnew', [SupervisorOverridesController::class, 'addnew'])->name('sysadmin.supervisoroverrides.addnew');
        Route::post('/sysadmin/supervisoroverrides/saveall', [SupervisorOverridesController::class, 'saveall'])->name('sysadmin.supervisoroverrides.saveall');

        Route::get('/sysadmin/supervisoroverrides/manageindex', [SupervisorOverridesController::class, 'manageindex'])->name('sysadmin.supervisoroverrides.manageindex');
        Route::delete('/sysadmin/supervisoroverrides/manageindex', [SupervisorOverridesController::class, 'manageindex'])->name('sysadmin.supervisoroverrides.manageindexdelete');
        Route::get('/sysadmin/supervisoroverrides/manageindexlist', [SupervisorOverridesController::class, 'manageindexlist'])->name('sysadmin.supervisoroverrides.manageindexlist');
        Route::get('/sysadmin/supervisoroverrides/deleteoverride/{id}', [SupervisorOverridesController::class, 'deleteOverride'])->name('sysadmin.supervisoroverrides.deleteoverrideget');
        Route::delete('/sysadmin/supervisoroverrides/deleteoverride/{id}', [SupervisorOverridesController::class, 'deleteOverride'])->name('sysadmin.supervisoroverrides.deleteoverride');
        Route::get('/sysadmin/supervisoroverrides/deletemultioverride/{ids}', [SupervisorOverridesController::class, 'deleteMultiOverride'])->name('sysadmin.supervisoroverrides.deletemultioverrideget');
        Route::delete('/sysadmin/supervisoroverrides/deletemultioverride/{ids}', [SupervisorOverridesController::class, 'deleteMultiOverride'])->name('sysadmin.supervisoroverrides.deletemultioverride');
        Route::get('sysadmin/supervisoroverrides/manageindexviewshares/{id}', [SupervisorOverridesController::class, 'manageindexviewshares']);

        Route::get('/sysadmin/supervisoroverrides/org-tree/{index}', [SupervisorOverridesController::class,'loadOrganizationTree']);
        Route::get('/sysadmin/supervisoroverrides/employee-list/{index}', [SupervisorOverridesController::class, 'getDatatableEmployees']);
        Route::get('/sysadmin/supervisoroverrides/employees/{id}/{index}', [SupervisorOverridesController::class,'getEmployees']);
    });


    //system queue monitor
    Route::group(['middleware' => ['auth']], function() {    
        Route::get('/sysadmin/queue/status', [QueueStatusController::class, 'show']);
        Route::get('/sysadmin/queue/processes', [QueueStatusController::class, 'processes']);
        Route::get('/sysadmin/queue/fixmodle', [QueueStatusController::class, 'fixModle']);
    });


    Route::get('/sysadmin/level0', 'App\Http\Controllers\SysadminController@getOrgLevel0')->name('sysadmin.level0');
    Route::get('/sysadmin/level1/{id0}', 'App\Http\Controllers\SysadminController@getOrgLevel1')->name('sysadmin.level1');
    Route::get('/sysadmin/level2/{id0}/{id1}', 'App\Http\Controllers\SysadminController@getOrgLevel2')->name('sysadmin.level2');
    Route::get('/sysadmin/level3/{id0}/{id1}/{id2}', 'App\Http\Controllers\SysadminController@getOrgLevel3')->name('sysadmin.level3');
    Route::get('/sysadmin/level4/{id0}/{id1}/{id2}/{id3}', 'App\Http\Controllers\SysadminController@getOrgLevel4')->name('sysadmin.level4');
    

    Route::get('/sysadmin/tags', [SysadminController::class, 'tags'])->name('sysadmin.tags');
    Route::get('/sysadmin/tag-detail/{id}', [SysadminController::class, 'tagDetail'])->name('sysadmin.tag-detail');
    Route::put('/sysadmin/tag-update/{id}', [SysadminController::class, 'tagUpdate'])->name('sysadmin.tag-update');
    Route::get('/sysadmin/tag-delete/{id}', [SysadminController::class, 'tagDelete'])->name('sysadmin.tag-delete');
    Route::get('/sysadmin/tag-new', [SysadminController::class, 'tagNew'])->name('sysadmin.tag-new');
    Route::put('/sysadmin/tag-insert', [SysadminController::class, 'tagInsert'])->name('sysadmin.tag-insert');

});


Route::group(['middleware' => ['role:Sys Admin|Service Representative']], function () {
    //Shared functions v3.0
    Route::get('/sysadmin/org-list/{index}/{level}', [SysAdminSharedController::class,'getOrganizationList']);
    //Identity Switch
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
    Route::get('/sysadmin/employeelists/reporteeslist/{id}/{posn}', [EmployeeListController::class, 'reporteesList'])->name('sysadmin.employeelists.reporteeslist');
});



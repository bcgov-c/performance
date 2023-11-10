<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\MyTeamController;
use App\Http\Controllers\MyTeamGoalController;
use App\Http\Controllers\MyTeamConversationController;
use App\Http\Controllers\MyTeamStatisticsReportController;

Route::group(['middleware' => ['role:Supervisor']], function () {
    Route::get( 'my-team/my-employees', [MyTeamController::class, 'myEmployees'])->name('my-team.my-employee');
    Route::get( 'my-team/my-employees-table', [MyTeamController::class, 'myEmployeesTable'])->name('my-team.my-employee-table');
    Route::get( 'my-team/shared-employees-table', [MyTeamController::class, 'sharedEmployeesTable'])->name('my-team.shared-employee-table');

    Route::get('my-team/suggested-goals', [MyTeamController::class, 'showSugggestedGoals'])->name('my-team.suggested-goals');
    Route::get('my-team/suggested-goal/{id}', [GoalController::class, 'getSuggestedGoal'])->name('my-team.get-suggested-goal');
    Route::post('my-team/suggested-goal/{id}', [GoalController::class, 'updateSuggestedGoal'])->name('my-team.update-suggested-goal');

    Route::get( 'my-team/performance-statistics', [MyTeamController::class, 'performanceStatistics'])->name('my-team.performance-statistics');
    Route::post('my-team/sync', [MyTeamController::class, 'syncGoals'])->name('my-team.sync-goals');
    Route::post('my-team/add-goal-to-library', [MyTeamController::class, 'addGoalToLibrary'])->name('my-team.add-goal-to-library');
    

    Route::post('my-team/share-profile', [MyTeamController::class, 'shareProfile'])->name('my-team.share-profile');
    Route::get('profile-shared-with/{user_id}', [MyTeamController::class, 'getProfileSharedWith'])->name('my-team.profile-shared-with');
    Route::post('profile-shared-with/{shared_profile_id}', [MyTeamController::class, 'updateProfileSharedWith'])->name('my-team.profile-shared-with.update');
    Route::get('my-team/direct-report/{id}', [MyTeamController::class, 'viewDirectReport'])->name('my-team.view-profile-as.direct-report');
    Route::get('employee-excused/{user_id}', [MyTeamController::class, 'getProfileExcused'])->name('my-team.employee-excused');
    Route::post('employee-excused', [MyTeamController::class, 'updateExcuseDetails'])->name('excused.updateExcuseDetails');
    // Route::get('excused-reasons', [MyTeamController::class, 'getExcusedReason'])->name('ereasons');


    Route::prefix('my-team')->name('my-team.')->group(function() {
        Route::get('/conversations', [MyTeamConversationController::class, 'templates'])->name('conversations');
        Route::get('/conversations/upcoming', [MyTeamConversationController::class, 'index'])->name('conversations.upcoming');
        Route::post('/conversations/upcoming', [MyTeamConversationController::class, 'index'])->name('conversations.upcoming.filter');
        Route::get('/conversations/past', [MyTeamConversationController::class, 'index'])->name('conversations.past');
        Route::post('/conversations/past', [MyTeamConversationController::class, 'index'])->name('conversations.past.filter');
    });

    Route::prefix('my-team/team-goals')->name('my-team.')->group(function() {
        Route::get('/share-my-goals', [MyTeamGoalController::class, 'shareMyGoals'])->name('share-my-goals');
        Route::get('/team-goal-bank', [MyTeamGoalController::class, 'teamGoalBank'])->name('team-goal-bank');
        Route::post('/sync-goal-sharing', [MyTeamGoalController::class, 'updateItemsToShare'])->name('sync-goals-sharing');
    });


  // Statictics and Reporting 
  Route::get('/my-team/statistics', [MyTeamStatisticsReportController::class, 'goalsummary'])->name('my-team.statistics');
  Route::get('/my-team/statistics/goalsummary', [MyTeamStatisticsReportController::class, 'goalsummary'])->name('my-team.statistics.goalsummary');
  Route::get('/my-team/statistics/goalsummary-export', [MyTeamStatisticsReportController::class, 'goalSummaryExport'])->name('my-team.statistics.goalsummary.export');
  Route::get('/my-team/statistics/goalsummary-tag-export', [MyTeamStatisticsReportController::class, 'goalSummaryTagExport'])->name('my-team.statistics.goalsummary.tag.export');
  Route::get('/my-team/statistics/conversationsummary', [MyTeamStatisticsReportController::class, 'conversationsummary'])->name('my-team.statistics.conversationsummary');
  Route::get('/my-team/statistics/conversationsummary-export', [MyTeamStatisticsReportController::class, 'conversationSummaryExport'])->name('my-team.statistics.conversationsummary.export');
  Route::get('/my-team/statistics/sharedsummary', [MyTeamStatisticsReportController::class, 'sharedsummary'])->name('my-team.statistics.sharedsummary');
  Route::get('/my-team/statistics/sharedsummary-export', [MyTeamStatisticsReportController::class, 'sharedSummaryExport'])->name('my-team.statistics.sharedsummary.export');
  Route::get('/my-team/statistics/excusedsummary', [MyTeamStatisticsReportController::class, 'excusedsummary'])->name('my-team.statistics.excusedsummary');
  Route::get('/my-team/statistics/excusedsummary-export', [MyTeamStatisticsReportController::class, 'excusedSummaryExport'])->name('my-team.statistics.excusedsummary.export');
  Route::get('/my-team/statistics/org-organizations', [MyTeamStatisticsReportController::class,'getOrganizations']);
  Route::get('/my-team/statistics/org-programs', [MyTeamStatisticsReportController::class,'getPrograms']);
  Route::get('/my-team/statistics/org-divisions', [MyTeamStatisticsReportController::class,'getDivisions']);
  Route::get('/my-team/statistics/org-branches', [MyTeamStatisticsReportController::class,'getBranches']);
  Route::get('/my-team/statistics/org-level4', [MyTeamStatisticsReportController::class,'getLevel4']);


});

Route::group(['middleware' => ['ViewAsPermission']], function () {
    Route::get('my-team/view-as/{id}/{landingPage?}', [MyTeamController::class, 'viewProfileAs'])->name('my-team.view-profile-as');
    Route::get('my-team/return-to-my-view', [MyTeamController::class, 'returnToMyProfile'])->name('my-team.return-to-my-view');

});

Route::group(['middleware' => ['role:Sys Admin|HR Admin|Supervisor']], function () {
    Route::get('users', [MyTeamController::class, 'userList'])->name('users-list');
    Route::get('user-options', [MyTeamController::class, 'userOptions'])->name('user-options');
});


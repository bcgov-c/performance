<?php

use App\Http\Controllers\GoalCommentController;
use App\Http\Controllers\GoalController;
use Illuminate\Support\Facades\Route;


Route::get('goal/get_allusers', [GoalController::class, 'getAllUsers'])->name('goal.get-all-users');
Route::get('goal/get_alluser_options', [GoalController::class, 'getAllUsersOptions'])->name('goal.get-all-user-options');
Route::get('goal/current', [GoalController::class, 'index'])->name('goal.current');
Route::get('goal/past', [GoalController::class, 'index'])->name('goal.past');
Route::get('goal/share', [GoalController::class, 'index'])->name('goal.share');
Route::get('goal/goalbank', [GoalController::class, 'goalBank'])->name('goal.library');
Route::post('goal/goalbank', [GoalController::class, 'saveFromLibrary'])->name('goal.library');
Route::get('goal/goalbank/{id}', [GoalController::class, 'showForLibrary'])->name('goal.library.detail');
Route::get('goal/supervisor/{id}', [GoalController::class, 'getSupervisorGoals'])->name('goal.supervisor');
Route::post('goal/supervisor/{id}/copy', [GoalController::class, 'copyGoal'])->name('goal.supervisor.copy');
Route::post('goal/goalbank/copy-multiple', [GoalController::class, 'saveFromLibraryMultiple'])->name('goal.library.save-multiple');
Route::post('goal/goalbank/hide-multiple', [GoalController::class, 'hideFromLibraryMultiple'])->name('goal.library.hide-multiple');
Route::post('goal/goalbank/show-multiple', [GoalController::class, 'showFromLibraryMultiple'])->name('goal.library.show-multiple');
Route::delete('goal/comment/{id}', [GoalCommentController::class, 'delete'])->name('goal.comment.delete');
Route::put('goal/comment/{id}', [GoalCommentController::class, 'edit'])->name('goal.comment.edit');

Route::post('goal/sync', [GoalController::class, 'syncGoals'])->name('goal.sync-goals');

Route::get('goal', function () {
    return redirect()->route('goal.current');
})->name('goal.index');

Route::resource('goal', GoalController::class)->except(['index']);

// Route::get('goal/{goal}/comment', [GoalController::class, 'getComments'])->name('get-comments');
Route::post('goal/{goal}/comment', [GoalController::class, 'addComment'])->name('goal.add-comment');
Route::get('goal/{goal}/status/{status}', [GoalController::class, 'updateStatus'])->name('goal.update-status');

// Link Employee to Supervisor goals
Route::post('link-goal', [GoalController::class, 'linkGoal'])->name('goal.link');

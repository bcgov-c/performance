<?php

use App\Http\Controllers\GoalCommentController;
use App\Http\Controllers\GoalController;
use Illuminate\Support\Facades\Route;

Route::get('goal/current', [GoalController::class, 'index'])->name('goal.current');
Route::get('goal/past', [GoalController::class, 'index'])->name('goal.past');
Route::get('goal/supervisor', [GoalController::class, 'index'])->name('goal.my-supervisor');
Route::get('goal/library', [GoalController::class, 'goalBank'])->name('goal.library');
Route::post('goal/library', [GoalController::class, 'saveFromLibrary'])->name('goal.library');
Route::get('goal/library/{id}', [GoalController::class, 'showForLibrary'])->name('goal.library.detail');
Route::get('goal/supervisor/{id}', [GoalController::class, 'getSupervisorGoals'])->name('goal.supervisor');
Route::post('goal/supervisor/{id}/copy', [GoalController::class, 'copyGoal'])->name('goal.supervisor.copy');
Route::post('goal/library/copy-multiple', [GoalController::class, 'saveFromLibraryMultiple'])->name('goal.library.save-multiple');
Route::delete('goal/comment/{id}', [GoalCommentController::class, 'delete'])->name('goal.comment.delete');
Route::put('goal/comment/{id}', [GoalCommentController::class, 'edit'])->name('goal.comment.edit');

Route::get('goal', function () {
    return redirect()->route('goal.current');
})->name('goal.index');

Route::resource('goal', GoalController::class)->except([
'index'
]);

// Route::get('goal/{goal}/comment', [GoalController::class, 'getComments'])->name('get-comments');
Route::post('goal/{goal}/comment', [GoalController::class, 'addComment'])->name('goal.add-comment');
Route::get('goal/{goal}/status/{status}', [GoalController::class, 'updateStatus'])->name('goal.update-status');

// Link Employee to Supervisor goals
Route::post('link-goal', [GoalController::class, 'linkGoal'])->name('goal.link');

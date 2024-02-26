<?php

use App\Http\Controllers\ConversationController;
use App\Http\Controllers\ParticipantController;
use Illuminate\Support\Facades\Route;
// Conversations
Route::get('conversation/upcoming', [ConversationController::class, 'index'])->name('conversation.upcoming');
Route::get('conversation/past', [ConversationController::class, 'index'])->name('conversation.past');
Route::post('conversation/upcoming', [ConversationController::class, 'index'])->name('conversation.upcoming.filter');
Route::post('conversation/past', [ConversationController::class, 'index'])->name('conversation.past.filter');
Route::get('conversation/templates', [ConversationController::class, 'templates'])->name('conversation.templates');
Route::get('conversation/templates/{id}', [ConversationController::class, 'templateDetail'])->name('conversation.template.detail');
Route::get('conversation/{conversation}', [ConversationController::class, 'show'])->name('conversation.show');
Route::post('conversation/sign-off/{conversation}', [ConversationController::class, 'signOff'])->name('conversation.signoff');
Route::post('conversation/unsign-off/{conversation}', [ConversationController::class, 'unsignOff'])->name('conversation.unsignoff');
Route::post('conversation', [ConversationController::class, 'store'])->name('conversation.store');
Route::put('conversation/{conversation}', [ConversationController::class, 'update'])->name('conversation.update');
Route::delete('conversation/{conversation}', [ConversationController::class, 'destroy'])->name('conversation.destroy');
Route::post('conversation-info-comment', [ConversationInfoCommentController::class, 'store'])->name('conversation-info-comment.store');
Route::get('conversation/agreement/{conversation}', [ConversationController::class, 'agreement'])->name('conversation.agreement');
Route::get('conversation/disagreement/{conversation}', [ConversationController::class, 'disagreement'])->name('conversation.disagreement');

Route::get('participant', [ParticipantController::class, 'index'])->name('participant.index');
Route::get('conversation-template/{id}', [ConversationController::class, 'conversationTemplate'])->name('conversation-template.detail');

Route::get('conversation/sendnotification/{id}', [ConversationController::class, 'sendNotification'])->name('conversation.sendnotification');



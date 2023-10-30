<?php

use App\Http\Controllers\ResourceController;
use Illuminate\Support\Facades\Route;

Route::get('resources', [ResourceController::class, 'userguide'])->name('resource.user-guide');
Route::get('resources/video-tutorials', [ResourceController::class, 'videotutorials'])->name('resource.video-tutorials');
Route::get('resources/goal-setting', [ResourceController::class, 'goalsetting'])->name('resource.goal-setting');
Route::get('resources/conversations', [ResourceController::class, 'conversations'])->name('resource.conversations');
Route::get('resources/contact', [ResourceController::class, 'contact'])->name('resource.contact');
Route::get('resources/faq', [ResourceController::class, 'faq'])->name('resource.faq');
Route::get('resources/hr-admin', [ResourceController::class, 'hradmin'])->name('resource.hr-admin');
<?php

use App\Http\Controllers\ResourceController;
use Illuminate\Support\Facades\Route;

Route::get('resource/user-guide', [ResourceController::class, 'userguide'])->name('resource.user-guide');
Route::get('resource/goal-setting', [ResourceController::class, 'goalsetting'])->name('resource.goal-setting');
Route::get('resource/conversations', [ResourceController::class, 'conversations'])->name('resource.conversations');
Route::get('resource/contact', [ResourceController::class, 'contact'])->name('resource.contact');

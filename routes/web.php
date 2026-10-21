<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;

Route::get('/', [TaskController::class, 'index'])->name('tasks.index');

Route::resource('tasks', TaskController::class)->except(['index']);

Route::patch('/tasks/{task}/complete', [TaskController::class, 'complete'])->name('tasks.complete');


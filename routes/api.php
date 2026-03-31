<?php

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

/*
 * IMPORTANT: /tasks/report must be registered BEFORE /tasks/{id}
 * otherwise Laravel matches "report" as an ID parameter.
 */

Route::get('/debug-env', function () {
    return response()->json([
        'DB_CONNECTION' => env('DB_CONNECTION'),
        'DB_HOST'       => env('DB_HOST'),
        'DB_PORT'       => env('DB_PORT'),
        'DB_DATABASE'   => env('DB_DATABASE'),
        'DB_USERNAME'   => env('DB_USERNAME'),
    ]);
});

Route::get('/tasks/report',         [TaskController::class, 'report']);
Route::get('/tasks',                [TaskController::class, 'index']);
Route::post('/tasks',               [TaskController::class, 'store']);
Route::patch('/tasks/{id}/status',  [TaskController::class, 'updateStatus']);
Route::delete('/tasks/{id}',        [TaskController::class, 'destroy']);
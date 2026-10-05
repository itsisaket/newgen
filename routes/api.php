<?php

use App\Http\Controllers\Api\SyncController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::get('me', fn (\Illuminate\Http\Request $request) => $request->user()->load('roles', 'areaAssignments'));
    Route::get('sync/pull', [SyncController::class, 'pull']);
    Route::post('sync/farm-activities', [SyncController::class, 'farmActivities']);
});

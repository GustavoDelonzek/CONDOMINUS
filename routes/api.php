<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::prefix('v1')->group(function () {
    Route::get('/', function () {
        return response()->json([
            'message' => 'ok'
        ]);
    });

    Route::post('login', [AuthController::class, 'login']);

    Route::prefix('')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);

    })->middleware(['auth:api']);
});

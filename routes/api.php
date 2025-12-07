<?php

use App\Http\Controllers\AdminCompanyController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\ResponseMiddleware;
use App\Http\Controllers\CondominiumController;

Route::prefix('v1')->middleware([
    ResponseMiddleware::class,
])->group(function () {
    Route::get('/', function () {
        return response()->json([
            'message' => 'ok'
        ]);
    });

    Route::post('login', [AuthController::class, 'login']);

    Route::prefix('')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('user/create', [AuthController::class, 'createUser']);

        // Admin companies
        Route::apiResource('admin-companies', AdminCompanyController::class)->except(['destroy']);

        //Condominiums
        Route::apiResource('condominiums', CondominiumController::class)->except(['destroy']);
    })->middleware(['auth:api']);
});

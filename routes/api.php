<?php

use App\Http\Controllers\AdminCompanyController;
use App\Http\Controllers\BlockController;
use App\Http\Controllers\UnitController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\ResponseMiddleware;
use App\Http\Controllers\CondominiumController;
use App\Http\Controllers\CommonAreaController;
use App\Http\Middleware\CheckMembership;

Route::prefix('v1')->middleware([
    ResponseMiddleware::class,
])->group(function () {
    Route::get('/', function () {
        return response()->json([
            'message' => 'ok'
        ]);
    });

    Route::post('login', [AuthController::class, 'login']);

    Route::middleware(['auth:api'])->group(function () {

        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('user/create', [AuthController::class, 'createUser']);

        Route::apiResource('admin-companies', AdminCompanyController::class)->except(['destroy']);

        Route::apiResource('condominiums', CondominiumController::class)->except(['destroy']);

        Route::middleware(CheckMembership::class)->group(function () {
            Route::apiResource('blocks', BlockController::class);

            Route::apiResource('units', UnitController::class);

            Route::apiResource('common-areas', CommonAreaController::class);
        });


    });
});

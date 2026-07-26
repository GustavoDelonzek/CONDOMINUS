<?php

use App\Http\Controllers\AdminCompanyController;
use App\Http\Controllers\BlockController;
use App\Http\Controllers\UnitController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\ResponseMiddleware;
use App\Http\Controllers\CondominiumController;
use App\Http\Controllers\CommonAreaController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\OccurrenceController;
use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\WhatsAppInstanceController;
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
    Route::post('whatsapp/webhook/{instance}', [WhatsAppController::class, 'handleWebhook']);

    Route::middleware(['auth:api'])->group(function () {

        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::post('user/create', [AuthController::class, 'createUser']);

        Route::apiResource('condominiums', CondominiumController::class)->except(['destroy']);

        Route::middleware(CheckMembership::class)->group(function () {
            Route::apiResource('blocks', BlockController::class);

            Route::apiResource('units', UnitController::class);

            Route::apiResource('common-areas', CommonAreaController::class);

            Route::get('reservations', [ReservationController::class, 'index']);
            Route::patch('reservations/{reservation}', [ReservationController::class, 'updateStatus']);

            Route::get('occurrences', [OccurrenceController::class, 'index']);
            Route::patch('occurrences/{occurrence}', [OccurrenceController::class, 'updateStatus']);

            Route::get('whatsapp/instance', [WhatsAppInstanceController::class, 'show']);
            Route::post('whatsapp/instance', [WhatsAppInstanceController::class, 'connect']);
            Route::get('whatsapp/messages', [WhatsAppInstanceController::class, 'messages']);
        });


    });
});

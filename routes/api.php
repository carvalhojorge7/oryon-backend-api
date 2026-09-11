<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

### Rotas Publicas de Autenticacao ###
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
});

### Rotas Protegidas por JWT ###
Route::middleware('auth:api')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });
});

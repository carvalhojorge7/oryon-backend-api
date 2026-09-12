<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\EmployeeController;
use Illuminate\Support\Facades\Route;

### Rotas Publicas de Autenticacao ###
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
});

### Rotas Protegidas por JWT ###
Route::middleware('auth:api')->group(function () {
    ### Modulo de Autenticacao ###
    Route::prefix('auth')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });

    ### Modulo de Departamentos e Transferencia ###
    Route::post('departments/{id}/transfer-employees', [DepartmentController::class, 'transferEmployees']);
    Route::apiResource('departments', DepartmentController::class);

    ### Modulo de Colaboradores ###
    Route::apiResource('employees', EmployeeController::class);
});

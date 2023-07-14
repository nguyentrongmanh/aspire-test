<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\RepaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/auth/login', [AuthController::class, 'login'])->name('login');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('loans', LoanController::class)->only(['index', 'store', 'show']);
    Route::post('/loans/{loan}/approve', [LoanController::class, 'approve'])->name('loans.approve');

    Route::apiResource('repayments', RepaymentController::class)->only(['update']);
});

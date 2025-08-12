<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SobrietyDateController;
use App\Http\Controllers\Api\SupportFormController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Device Date API Routes
Route::post('/sobriety-date', [SobrietyDateController::class, 'create']);
Route::put('/sobriety-date/{device_id}', [SobrietyDateController::class, 'update']);
Route::get('/sobriety-date/{device_id}', [SobrietyDateController::class, 'fetch']);

// Support Form API Routes
Route::post('/support-form', [SupportFormController::class, 'submit']);
Route::get('/support-form/stats', [SupportFormController::class, 'stats']); // Optional admin endpoint

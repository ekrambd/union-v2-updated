<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
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

Route::post('/user/user-password-update', [ApiController::class, 'userPasswordUpdate']);
Route::get('/user/offers', [ApiController::class, 'offers']);
Route::get('/user/suggestions', [ApiController::class, 'suggestions']);
Route::get('/user/app-banner-images', [ApiController::class, 'appBannerImages']);
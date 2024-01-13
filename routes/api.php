<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/import-data', [\App\Http\Controllers\HotelController::class, 'addDataToRecombee']);
Route::get('/store', [\App\Http\Controllers\HotelController::class, 'store']);
Route::get('/empty_users', [\App\Http\Controllers\HotelController::class, 'emptyUsersTable']);
Route::get('/empty_items', [\App\Http\Controllers\HotelController::class, 'emptyItemsTable']);
Route::get('/test', function () {
    return 'testtt';
});
Route::get('/get-popular-hotels', [\App\Http\Controllers\HotelController::class, 'getPopularHotels']);
Route::get('/get-hotels-by-city', [\App\Http\Controllers\HotelController::class, 'getHotelsByCity']);
Route::post('/get-recommandations', [\App\Http\Controllers\HotelController::class, 'getRecommandations']);
Route::post('/set-view', [\App\Http\Controllers\HotelController::class, 'setHotelAsViewed']);
Route::post('/login', [\App\Http\Controllers\HotelController::class, 'login']);
Route::apiResources(
    [
        'hotel' => App\Http\Controllers\HotelController::class,
    ]
);

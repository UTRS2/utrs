<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
// Add the route for the ACC API to direct to the ApiController, storeAcc function
Route::post('/acc', 'ApiController@storeAcc')->name('api.acc.store');
// add route for transfer confirmation to direct to the ApiController, storeTransfer function
Route::post('/transfer', 'ApiController@storeTransfer')->name('api.acc.transfer');

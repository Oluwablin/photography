<?php

use Illuminate\Http\Request;
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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'v1'], function ($router) {

    /** TESTING */
    Route::get('/test', function() {
        return 'Hello test';
    });

    /** Cache */
    Route::get('/clear-cache', function() {
        Artisan::call('config:cache');
        Artisan::call('cache:clear');
        Artisan::call('route:clear');

        return "Cache is cleared";
    });

    /**
     * THIS SHOULD ALWAYS BE THE ENDING OF THIS PAGE
     */
    Route::fallback(function () {
        return response()->json([
            'error' => true,
            'message' => 'Route don\'t exist',
            'data' => null
        ], 404);
    });

});

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

    /*** AUTHENTICATION ***/
    Route::group(['prefix' => 'auth'], function ($router) {
        //REGISTER USERS
        Route::post('create', 'v1\Auth\RegisterController@register');

        //Login User
        Route::post('login', 'v1\Auth\LoginController@login');

    });

    //AUTHENTICATED USERS
    Route::middleware('jwt.auth')->group(function () {
        Route::post('logout', 'v1\Auth\LoginController@logout');
        Route::get('me', 'v1\Auth\LoginController@me');

        Route::group(['prefix' => 'product'], function () {
            Route::get('fetch/all', 							'v1\ProductController@index');
            Route::get('fetch/one/{product}', 					'v1\ProductController@show');
            Route::post('add/new', 						'v1\ProductController@store');
            Route::put('update/{product}', 						'v1\ProductController@update');
            Route::delete('delete/{product}', 					'v1\ProductController@destroy');
        });

        Route::group(['prefix' => 'product_request'], function () {
            Route::get('fetch/all', 							'v1\ProductRequestController@index');
            Route::get('fetch/one/{productRequest}', 					'v1\ProductRequestController@show');
            Route::post('add/new', 						'v1\ProductRequestController@store');
            Route::put('update/{productRequest}', 						'v1\ProductRequestController@update');
            Route::delete('delete/{productRequest}', 					'v1\ProductRequestController@destroy');
        });

        Route::group(['prefix' => 'photo'], function () {
            Route::get('fetch/all', 							'v1\PhotoController@index');
            Route::get('fetch/one/{photo}', 					'v1\PhotoController@show');
            Route::post('add/new', 						'v1\PhotoController@store');
            Route::put('update/{photo}', 						'v1\PhotoController@update');
            Route::delete('delete/{photo}', 					'v1\PhotoController@destroy');
        });

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

<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\JwtMiddleware;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/login', 'App\Http\Controllers\Api\AuthController@login')->name('login');

Route::group(['middleware' => [JwtMiddleware::class]], function(){
    Route::resource('users', 'App\Http\Controllers\Api\UserController');
    Route::patch('/users/{user}/restore', 'App\Http\Controllers\Api\UserController@restore');
    Route::delete('/users/{user}/force-delete', 'App\Http\Controllers\Api\UserController@forceDelete');
    Route::resource('companies', 'App\Http\Controllers\Api\CompanyController');
    Route::get('/me','App\Http\Controllers\Api\UserController@me')->name('me');
    Route::put('/update-profile','App\Http\Controllers\Api\UserController@updateProfile')->name('update-profile');
    Route::post('/logout', 'App\Http\Controllers\Api\AuthController@logout')->name('logout');
});
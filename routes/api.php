<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\JwtMiddleware;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/login', 'App\Http\Controllers\Api\AuthController@login')->name('login');

Route::group(['middleware' => [JwtMiddleware::class]], function(){
    Route::post('/user/register', 'App\Http\Controllers\Api\UserController@store')->name('user.register')->middleware('role:super_admin');
    Route::post('/logout', 'App\Http\Controllers\Api\AuthController@logout')->name('logout');
});
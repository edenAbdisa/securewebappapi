<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Controllers;
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
Route::group(['middleware' => ['auth:api','scope:user,admin']], function () { 
    Route::delete('/user/logout', 'UserController@logout');
    Route::get('/user', 'UserController@index');
    Route::get('/users', 'UserController@index');
    Route::post('/user/logout', 'UserController@logout');
    Route::post('/user/search', 'UserController@search');
    Route::put('/user/{id}', 'UserController@update');

    Route::get('/feedback', 'FeedbackController@index');
    Route::post('/feedback', 'FeedbackController@store');
    Route::put('/feedback/{id}', 'FeedbackController@update');
    Route::delete('/feedback/{id}', 'FeedbackController@destroy');

    Route::get('/feedbacktype', 'FeedbackTypeController@index');
    Route::post('/feedbacktype', 'FeedbackTypeController@store');
    Route::put('/feedbacktype/{id}', 'FeedbackTypeController@update');
    Route::delete('/feedbacktype/{id}', 'FeedbackTypeController@destroy');

});
Route::post('/user/login', 'UserController@login');
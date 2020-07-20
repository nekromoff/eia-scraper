<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', ['uses' => 'EIAController@index', 'as' => 'index']);
Route::get('projekt', ['uses' => 'EIAController@about', 'as' => 'about']);
Route::get('odhlas/{email}/{hash}/{watcherid?}', ['uses' => 'EIAController@unsubscribe', 'as' => 'unsubscribe']);
Route::post('sleduj', ['uses' => 'EIAController@storeForm', 'as' => 'store']);

Route::get('cron/get', ['uses' => 'EIAController@get', 'as' => 'get']);
Route::get('cron/update', ['uses' => 'EIAController@updateFiles', 'as' => 'update']);

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

// Index APIs - filterable, selectable
Route::get('/constructors', 'ConstructorsController@index')->name('constructors.index');
Route::get('/circuits', 'CircuitsController@index')->name('circuits.index');

// Fetch Driver & Constructor Details - Telemetry API
Route::get('/drivers/data', 'DriverController@seasonData')->name('telemetry.season');
Route::post('/report/submit', 'ReportsController@bulkCreate')->name('steward.upload');

// Fetch User Info - Discord Bot
Route::get('/users/details/{query}/{discord_id}', 'BotController@fetchdetails')->name('bot.discord');
Route::get('/users/driver/{discord_id}', 'BotController@fetchDriverId')->name('bot.driverid');
Route::get('/drivers', "BotController@fetchDrivers")->name("bot.drivers");

// Upload Race Results
Route::post('/results/race', 'ResultsController@saveRaceResults')->name('result.upload');

Route::get('/fetch/drivers/{race}', 'ReportsController@driversdata');

// Signups
Route::get('/signups', 'SignupsController@getSignupsApi')->name('signups.index');
Route::get('/signups/{season_id}', 'SignupsController@getSignupsBySeasonApi')->name('signups.show');

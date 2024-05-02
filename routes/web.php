<?php

use App\Http\Controllers\Auth\SteamLoginController;
use kanalumaddela\LaravelSteamLogin\Facades\SteamLogin;

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

// Public Routes
Route::get('/', 'WebsiteController@loadhomepage')->name('home');
Route::get('joinus', 'WebsiteController@loadjoinus')->name('joinus');
Route::get('teamsanddrivers', 'WebsiteController@loadteamsanddrivers')->name('teams');
// Route::get('standings', 'WebsiteController@loadstandings');
Route::get('aboutus', 'WebsiteController@loadaboutus')->name('aboutus');
Route::get('ourteam', 'WebsiteController@loadourteam')->name('ourteam');
Route::get('faq', 'WebsiteController@loadfaq')->name('faq');

// League Rules
Route::get('f1leaguerules', 'WebsiteController@f1leaguerules')->name('rules.pcf1');
Route::get('f1XBOXleaguerules', 'WebsiteController@f1XBOXleaguerules')->name('rules.xboxf1');
Route::get('accleaguerules', 'WebsiteController@accleaguerules')->name('rules.acc');
Route::get('login', 'WebsiteController@loadlogin')->name('login');

/* Exposing Image Manipulation API for Public
Route::get('/public/image/race', 'ImageController@pubRace');
Route::get('/public/image/quali', 'ImageController@pubQuali');
*/

// Farewell Routes
Route::put('/impel', 'WebsiteController@impelS');
Route::delete('/impel', 'WebsiteController@impelA');

Route::get('/recotap', 'WebsiteController@recotapB');
Route::post('/recotap', 'WebsiteController@recotapG');
//

// League Result Data
Route::get('/{code}/{tier}/{season}/standings', 'StandingsController@fetchStandings')->name('standings')
->where(['tier' => '^[-+]?\d*\.?\d*$', 'season' => '^[-+]?\d*\.?\d*$']);
Route::get('/{code}/{tier}/{season}/races', 'StandingsController@fetchRaces')->name('allraces')
->where(['tier' => '^[-+]?\d*\.?\d*$', 'season' => '^[-+]?\d*\.?\d*$']);
Route::get('/{code}/{tier}/{season}/race/{round}', 'ResultsController@fetchRaceResults')->name('raceresults')
->where(['tier' => '^[-+]?\d*\.?\d*$', 'season' => '^[-+]?\d*\.?\d*$', 'round' => '^[-+]?\d*\.?\d*$']);

// User Authenticated Routes
Auth::routes();
Route::group(['middleware' => 'auth'], function () {
     Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

     // User Profile Routes
     Route::get('/user/profile/', 'UserPanel@viewprofile')->name('user.home');
     Route::post('/user/profile/save/{user}', 'HomeController@savedetails')->name('user.saveprofile');
     Route::group(['middleware' => 'profile'], function () {
          Route::post('/user/profile/setsteam/{user}', 'UserPanel@SetSteam');
          SteamLogin::routes(['controller' => SteamLoginController::class]);

          // Driver Report Routes
          // Route::get('/home/report/create','ReportsController@reportDriver')->name('report.create');
          Route::post('/home/report/submit', 'ReportsController@create')->name('report.submit');

          // Route::get('/home/report/list','ReportsController@listDriverReports')->name('report.list');
          // Route::get('/home/report/view/{report}','ReportsController@details')->where('report', '^[-+]?\d*\.?\d*$')->name('report.view');

          // Route::get('/home/report/edit/{report}','ReportsController@details')->where('report', '^[-+]?\d*\.?\d*$')->name('report.edit');
          Route::put('/home/report/edit/{report}', 'ReportsController@update')->where('report', '^[-+]?\d*\.?\d*$')->name('report.editsubmit');
          Route::delete('/home/report/delete/{report}', 'ReportsController@delete')->where('report', '^[-+]?\d*\.?\d*$')->name('report.delete');

          // Signup Routes
          Route::get('/signup', 'SignupsController@view')->middleware('signup')->name('driver.signup');
          Route::post('/signup/store', 'SignupsController@store')->name('driver.postsignup');
          Route::post('/signup/update/{signup}', 'SignupsController@update')->name('driver.editsignup');

          // Profile Routes
          Route::get('/user/profile/view/{user}', 'HomeController@viewprofile')->name('user.profile');
     });
});

// Routes Handling the Discord Login
Route::get('login/discord', 'Auth\LoginController@redirectToProvider')->name('login.discord');
Route::get('login/discord/callback', 'Auth\LoginController@handleProviderCallback')->name('login.discordcallback');

// Admin Panel
Route::group(['middleware' => 'can:admin|coordinator'], function () {
     Route::get('/home/admin/users', 'DriverController@viewusers')->name('coordinator.driverlist');
     Route::get('/home/admin/user-allot/{id}', 'DriverController@allotuser')->name('driver.allotpage');
     Route::post('/home/admin/user-allot/submit', 'DriverController@saveallotment')->name('driver.allot');

     Route::get('/result/upload', 'ResultsController@uploadResults')->name('race.upload');
     Route::get('/result/{race}', 'ResultsController@fetchResultsByRaceId')->name('raceresults.id');
     Route::post('/results/race', 'ResultsController@saveRaceResults')->name('result.upload');
});

// League Sign Up
Route::group(['middleware' => 'can:admin|signup'], function () {
     Route::get('/home/admin/view-signups', 'SignupsController@viewsignups')->name('coordinator.signup');
});

Route::middleware('auth:api')->get('/drivers/data', 'DriverController@driverdata')->name('telemetry.drivers');

// Stewards Reports
Route::group(['middleware' => 'can:admin|steward'], function () {
     // Route::get('/home/admin/report','DriverController@viewreports')->name('steward.list');
     // Route::get('home/admin/report/{report}/details','DriverController@reportdetails')->name('steward.view');
     Route::post('/home/admin/verdict/{report}/save', 'DriverController@saveverdict')->name('steward.save');

     Route::post('/home/steward/verdict/{report}/revert', 'ReportsController@revertVerdict')->name('steward.revert');
     Route::post('/home/steward/verdict/{report}/apply', 'ReportsController@applyVerdict')->name('steward.apply');

     Route::get('/home/steward/verdict/publish', 'ReportsController@applyReports')->name('steward.control');
     Route::post('/home/steward/verdict/publish', 'ReportsController@publishReports')->name('steward.publish');
     // Route::put('/position', 'ResultsController@updatePosition')->name('result.verdict');
});

// League Results Parsing
Route::group(['middleware' => 'can:admin|coordinator', 'prefix' => 'parse'], function () {
     // F1 Results
     Route::get('/f1/quali', 'ImageController@qualiIndex')->name('f1.imagequaliupload');
     Route::get('/f1/race', 'ImageController@raceIndex')->name('f1.imageraceupload');
     Route::post('/f1/race', 'ImageController@ocrRace')->name('f1.parseupload');

     // ACC Results
     Route::get('/acc/upload', 'AccController@raceUpload')->name('acc.raceupload');
     Route::post('/acc/upload', 'AccController@parseJson')->name('acc.parseupload');

     // AC Results
     Route::get('/ac/upload', 'AcController@raceUpload')->name('ac.raceupload');
     Route::post('/ac/upload', 'AcController@parseCsv')->name('ac.parseupload');
});

<?php

use App\Http\Middleware\ForceAssemblaKeys;
use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});


Route::group(['middleware' => ForceAssemblaKeys::class], function () {
    Route::get('/reports', 'ReportsController@index')->name('reports.index')->middleware('auth');
    Route::get('/reports/weekly', 'ReportsController@weekly')->name('reports.weekly')->middleware('auth');
    Route::post('/reports/weeklyStore', 'ReportsController@weeklyStore')->middleware('auth');
    Route::get('/reports/{id}', 'ReportsController@show')->name('reports.show')->middleware('auth');
    Route::post('/reports/hoursByUs', 'ReportsController@generateHoursByUsReport')->middleware('auth');
    Route::post('/reports/hoursByUser', 'ReportsController@generateHoursByUserReport')->middleware('auth');
    Route::post('/reports/generateSprintsReport', 'ReportsController@generateSprintsReport')->middleware('auth');
    Route::get('/tasks-timer', 'TasksTimerController@index')->name('taskstimer.index')->middleware('auth');
    Route::post('/tasks-timer', 'TasksTimerController@store')->middleware('auth');
    Route::get('/projects', 'ProjectsController@index')->name('projects.index')->middleware('auth');
    Route::get('/projects/importProjects', 'ProjectsController@importProjects')->name('projects.import')->middleware('auth');
    Route::get('/projects/{id}', 'ProjectsController@show')->name('projects.show')->middleware('auth');
    Route::post('/projects/starred/{id}', 'ProjectsController@starred')->name('projects.starred')->middleware('auth');
    Route::get('/sprints', 'SprintsController@index')->name('sprints.index')->middleware('auth');
    Route::get('/sprints/current', 'SprintsController@current')->name('sprints.current')->middleware('auth');
    Route::get('/sprints/importSprints/{projectId}', 'SprintsController@importSprints')->name('sprints.import')->middleware('auth');
    Route::get('/sprints/syncSprints/{projectId}', 'SprintsController@syncSprints')->name('sprints.sync')->middleware('auth');
    Route::get('/sprints/syncAllCurrentSprints', 'SprintsController@syncAllCurrentSprints')->name('sprints.sync-all-current-sprints')->middleware('auth');
    Route::get('/sprints/{id}', 'SprintsController@show')->name('sprints.show')->middleware('auth');
    Route::get('/tickets/importTickets/{sprintId}', 'TicketsController@importTickets')->name('tickets.import')->middleware('auth');
    Route::get('/users/importUsers/{userId}', 'UsersController@importUsers')->name('users.import')->middleware('auth');
    Route::get('/notifications', 'UsersController@notifications')->name('notifications')->middleware('auth');

    Route::get('/home', 'HomeController@index')->name('home');
});

Route::get('/settings', 'SettingsController@index')->name('settings.index')->middleware('auth');
Route::post('/settings', 'SettingsController@store')->middleware('auth');

Auth::routes();






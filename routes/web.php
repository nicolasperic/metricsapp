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
    Route::get('/spaces', 'ProjectsController@index')->name('projects.index')->middleware('auth');
    Route::get('/spaces/settingsPane/{wikiname}', 'ProjectsController@settingsPane')->name('projects.settingsPane')->middleware('auth');
    Route::get('/spaces/projectPane/{wikiname}', 'ProjectsController@projectPane')->name('projects.projectPane')->middleware('auth');
    Route::get('/spaces/syncProjects', 'Assembla\SyncSpacesController@sync')->name('projects.sync')->middleware('auth');
    Route::get('/spaces/{wikiname}', 'ProjectsController@show')->name('projects.show')->middleware('auth');
    Route::post('/spaces/storePivotAttribute/{wikiname}', 'ProjectsController@storePivotAttribute')->name('projects.storePivotAttribute')->middleware('auth');
    Route::post('/spaces/storeAttribute/{wikiname}', 'ProjectsController@storeAttribute')->name('projects.storeAttribute')->middleware('auth');
    Route::post('/iteration/storeAttribute/{wikiname}', 'SprintIterationsController@storeAttribute')->name('iterations.storeAttribute')->middleware('auth');
    Route::post('/iteration/start/{wikiname}', 'SprintIterationsController@start')->name('iterations.start')->middleware('auth');
    Route::post('/iteration/stop/{wikiname}', 'SprintIterationsController@stop')->name('iterations.stop')->middleware('auth');
    Route::post('/iteration/startDate/{wikiname}', 'SprintIterationsController@startDate')->name('iterations.startDate')->middleware('auth');
    Route::post('/iteration/modalContent/{wikiname}', 'SprintIterationsController@sprintModalDynamicContent')->name('iterations.modalContent')->middleware('auth');
    Route::get('/milestones', 'SprintsController@index')->name('sprints.index')->middleware('auth');
    Route::get('/milestones/current', 'SprintsController@current')->name('sprints.current')->middleware('auth');
    Route::get('/sprints/syncSprints/{projectId}', 'SprintsController@syncSprints')->name('sprints.sync')->middleware('auth');
    Route::get('/sprints/syncAllCurrentSprints', 'SprintsController@syncAllCurrentSprints')->name('sprints.sync-all-current-sprints')->middleware('auth');
    Route::get('/spaces/{wikiname}/milestones/{id}', 'SprintsController@show')->name('sprints.show')->middleware('auth');
    Route::get('/tickets/syncTickets/{sprintId}', 'TicketsController@syncTickets')->name('tickets.sync')->middleware('auth');
    Route::get('/users/syncUsers/{userId}', 'UsersController@syncUsers')->name('users.sync')->middleware('auth');
    Route::get('/notifications', 'UsersController@notifications')->name('notifications')->middleware('auth');
    Route::post('/notifications/markNotificationsAsRead', 'UsersController@markNotificationsAsRead')->name('notifications.markasread')->middleware('auth');

    Route::get('/home', 'HomeController@index')->name('home');
});

Route::get('/settings', 'SettingsController@index')->name('settings.index')->middleware('auth');
Route::post('/settings', 'SettingsController@store')->name('settings.post')->middleware('auth');

Auth::routes();






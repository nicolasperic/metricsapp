<?php

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

Route::get('/tasks-timer', 'TasksTimerController@index')->name('taskstimer.index')->middleware('auth');
Route::post('/tasks-timer', 'TasksTimerController@store')->middleware('auth');
Route::get('/projects', 'ProjectsController@index')->name('projects.index')->middleware('auth');
Route::get('/projects/importProjects', 'ProjectsController@importProjects')->name('projects.import')->middleware('auth');
Route::get('/projects/{id}', 'ProjectsController@show')->name('projects.show')->middleware('auth');
Route::get('/sprints', 'SprintsController@index')->name('sprints.index')->middleware('auth');
Route::get('/sprints/importSprints/{projectId}', 'SprintsController@importSprints')->name('sprints.import')->middleware('auth');
Route::get('/sprints/{id}', 'SprintsController@show')->name('sprints.show')->middleware('auth');
Route::get('/tickets/importTickets/{sprintId}', 'TicketsController@importTickets')->name('tickets.import')->middleware('auth');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

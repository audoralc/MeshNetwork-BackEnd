<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
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

// DashboardController
Route::get('db/Joins/{spaceId}/{year}', 'DashboardController@Joins');
Route::get('db/appearances/{spaceId}', 'DashboardController@Appearances');

// AuthController
Route::get('authorize', 'AuthController@checkAuth');  // sign up
Route::post('signUp', 'AuthController@signUp');  // sign up
Route::post('login', 'AuthController@signIn');  // login
Route::get('users', 'AuthController@getUsers');  // admin get users
Route::get('ban/{id}', 'AuthController@ban'); // ban user.id

// UserController
Route::get('deleteuser/{id}', 'UserController@delete'); // Admin delete user
Route::post('updateUser', 'UserController@updateUser'); // logged in user profile update
Route::get('skills', 'UserController@getSkills'); // to populate tags in sign up form
Route::get('skills/all', 'UserController@allSkills'); // to populate tags in sign up form
Route::post('searchname', 'UserController@searchName'); // search by name/spaceID
Route::get('search', 'UserController@search'); // search by skill/SpaceID
Route::get('showuser', 'UserController@showUser'); // show logged in user
Route::get('user/{id}', 'UserController@user'); // get user.id
Route::get('users/space/{spaceID}', 'UserController@usersFromSpace'); // get users from spaceID
Route::get('organizers/all', 'UserController@Organizers'); // to populate tags in sign up form

// RoleController
Route::post('newrole', 'RoleController@store');
Route::get('getroles', 'RoleController@get');
Route::post('showrole', 'RoleController@show');
Route::get('deleterole/{id}', 'RoleController@delete');

// WorkspaceController
Route::post('newspace', 'WorkspaceController@store');
Route::get('spacestatus/{spaceID}/{status}', 'WorkspaceController@approve');
Route::post('spaceupdate', 'WorkspaceController@update');
Route::get('workspaces', 'WorkspaceController@get');
Route::get('workspace/{spaceID}', 'WorkspaceController@show');
Route::get('workevents/{spaceID}', 'WorkspaceController@events');
Route::get('workbookables/{spaceID}', 'WorkspaceController@bookables');

// EventController
Route::post('sponser','EventController@makeSponser');
Route::get('sponsors','EventController@Sponsers');
Route::get('events','EventController@get');
Route::get('upcoming','EventController@upcoming');
Route::post('event','EventController@store');
Route::post('eventUpdate','EventController@update');
Route::get('event/{eventID}','EventController@show');
Route::post('searchEvent','EventController@search');
Route::post('optEvent, ','EventController@opt');
Route::get('deleteEvent/{id}','EventController@delete');
Route::get('getCalendar','EventController@getCalendar');
Route::get('event/join/{eventID}','EventController@storeCalendar');
Route::get('deleteCalendar/{id}','EventController@deleteCalendar');

//AppeanceController
Route::post('newAppearance','AppearanceController@store');
Route::get('appearances','AppearanceController@get');
Route::get('countAppearances/{sort}/{eventID?}/{spaceID?}','AppearanceController@getCount');
Route::get('appearance/{userID}','AppearanceController@show');
Route::get('storeInvite','AppearanceController@stroreInvite');
Route::get('getInvite','AppearanceController@getInvite');

// BookableController
Route::post('newBookable','BookableController@store');
Route::get('bookables','BookableController@get');
Route::get('bookables/{spaceID}','BookableController@getSpace');
Route::get('countBookings/{sort}/{eventID?}/{spaceID?}','BookableController@getCount');
Route::get('bookable/{userID}','BookableController@show');
Route::get('getBookings','BookableController@getBookings');
Route::get('deleteBookable/{id}','BookableController@delete');
Route::get('deleteBookings/{id}','BookableController@deleteBooking');

// KioskController
Route::get('kiosk/{spaceID}','KioskController@get');

Route::any('{path?}', 'MainController@index')->where("path", ".+");

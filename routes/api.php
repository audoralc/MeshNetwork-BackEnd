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

// AuthController
Route::post('signUp', 'AuthController@signUp');   // sign up
Route::post('login', 'AuthController@signIn');  // login
Route::post('getusers', 'AuthController@getUsers');  // admin get users 
Route::get('ban/{id}', 'AuthController@ban'); // ban user.id

// UserController
Route::get('deleteuser/{id}', 'UserController@delete'); // Admin delete user 
Route::post('updateUser', 'UserController@updateUser'); // logged in user profile update   
Route::get('skills', 'UserController@getSkills'); // to populate tags in sign up form
Route::post('searchname', 'UserController@searchName'); // search by name/spaceID
Route::post('search', 'UserController@search'); // search by skill/SpaceID
Route::get('showuser', 'UserController@showUser'); // show logged in user

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
Route::get('events','EventController@get');
Route::post('newEvent','EventController@store');
Route::post('eventUpdate','EventController@update');
Route::get('showEvent/{eventID}','EventController@show');
Route::post('searchEvent','EventController@search');
Route::post('optEvent, ','EventController@opt');
Route::get('deleteEvent/{id}','EventController@delete');
Route::get('getCalendar','EventController@getCalendar');
Route::get('storeCalendar/{eventID}','EventController@storeCalendar');
Route::get('deleteCalendar/{id}','EventController@deleteCalendar');

//AppeanceController
Route::post('newAppearance','AppearanceController@store');
Route::get('appearances','AppearanceController@get');
Route::get('countAppearances/{sort}/{eventID?}/{spaceID?}','AppearanceController@getCount');
Route::get('appearance/{userID}','AppearanceController@show');
Route::get('storeInvite','AppearanceController@stroreInvite');
Route::get('getInvite','AppearanceController@getInvite');

// test
Route::get('test', function() {
  return Response::json('wtf');
}); // show logged in user

// MainController
Route::any('{path?}', 'MainController@index')->where("path", ".+");

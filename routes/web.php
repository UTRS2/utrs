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

Route::get('/', 'Controller@home');
Route::get('/appeal/account', 'AppealController@accountappeal');
Route::post('/appeal/account', 'AppealController@appealsubmit');
Route::get('/appeal/ip', 'AppealController@ipappeal');
Route::post('/appeal/ip', 'AppealController@appealsubmit');
Route::get('/appeal/{id}', 'AppealController@appeal')->middleware('auth');
Route::get('/review', 'AppealController@appeallist');
Route::post('/appeal/checkuser/{id}', 'AppealController@checkuser');
Route::post('/appeal/comment/{id}', 'AppealController@comment');
Route::get('/appeal/respond/{id}', 'AppealController@respond');
Route::get('/appeal/reserve/{id}', 'AppealController@reserve');
Route::get('/appeal/release/{id}', 'AppealController@release');
Route::get('/appeal/open/{id}', 'AppealController@open');
Route::get('/appeal/close/{id}/{type}', 'AppealController@close');
Route::get('/appeal/checkuserreview/{id}', 'AppealController@checkuserreview');
Route::get('/appeal/privacy/{id}', 'AppealController@privacy');
Route::get('/appeal/admin/{id}', 'AppealController@admin');
Route::get('/appeal/invalidate/{id}', 'AppealController@invalidate');
Route::get('/appeal/template/{id}', 'AppealController@viewtemplates');
Route::get('/appeal/template/{id}/{template}', 'AppealController@respond');
Route::get('/appeal/custom/{id}', 'AppealController@respondCustom');
Route::post('/appeal/custom/{id}', 'AppealController@respondCustomSubmit');
Route::get('/publicappeal', 'AppealController@publicappeal');
Route::post('/publicappeal/comment', 'AppealController@publicComment');
Route::get('/appeal/privacy/{id}/{action}', 'AppealController@privacyhandle');
Route::get('/fixappeal/{hash}', 'AppealModifyController@changeip');
Route::post('/fixip/{id}', 'AppealModifyController@changeipsubmit');

Route::get('/admin/users', 'Admin\UserController@index')->name('admin.users.list');
Route::get('/admin/users/{user}', 'Admin\UserController@show')->name('admin.users.view');
Route::post('/admin/users/{user}', 'Admin\UserController@update')->name('admin.users.update');

Route::get('/admin/bans', 'AdminController@listbans');
Route::get('/admin/sitenotices', 'AdminController@listsitenotices');
Route::get('/admin/templates', 'AdminController@listtemplates');
Route::post('admin/templates/create', 'AdminController@makeTemplate');
Route::post('admin/templates/{template}', 'AdminController@saveTemplate');
Route::get('admin/templates/create', 'AdminController@showNewTemplate');
Route::get('admin/templates/{template}', 'AdminController@modifyTemplate');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/verifyaccount','AdminController@verifyAccount');
Route::get('/verify/{code}','AdminController@verify');
Route::get('/pending','HomeController@pending');
Route::get('/logout', 'HomeController@crashandburn');

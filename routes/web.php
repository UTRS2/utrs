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

Route::get('/appeal/{appeal}/verify/{token}', 'AppealController@showVerifyOwnershipForm')
    ->name('appeal.verifyownership');
Route::post('/appeal/{appeal}/verify', 'AppealController@verifyAccountOwnership')
    ->name('appeal.verifyownership.submit');

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

Route::get('/appeal/template/{appeal}', 'AppealController@viewtemplates')->name('appeal.template');
Route::post('/appeal/template/{appeal}/{template}', 'AppealController@respond')->name('appeal.template.submit');

Route::get('/appeal/custom/{appeal}', 'AppealController@respondCustom')->name('appeal.customresponse');
Route::post('/appeal/custom/{appeal}', 'AppealController@respondCustomSubmit')->name('appeal.customresponse.submit');

Route::get('/publicappeal', 'AppealController@publicappeal');
Route::post('/publicappeal/comment', 'AppealController@publicComment');
Route::get('/appeal/privacy/{id}/{action}', 'AppealController@privacyhandle');
Route::get('/fixappeal/{hash}', 'AppealModifyController@changeip');
Route::post('/fixip/{id}', 'AppealModifyController@changeipsubmit');

Route::get('/admin/users', 'AdminController@listusers');
Route::get('/admin/bans', 'AdminController@listbans');
Route::get('/admin/sitenotices', 'AdminController@listsitenotices');
Route::get('/admin/templates', 'AdminController@listtemplates');

Route::get('admin/templates/create', 'AdminController@showNewTemplate');
Route::post('admin/templates/create', 'AdminController@makeTemplate');
Route::get('admin/templates/{template}', 'AdminController@editTemplate')->name('admin.templates.edit');
Route::post('admin/templates/{template}', 'AdminController@updateTemplate')->name('admin.templates.update');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/verifyaccount','AdminController@verifyAccount');
Route::get('/verify/{code}','AdminController@verify');
Route::get('/pending','HomeController@pending');
Route::get('/logout', 'HomeController@crashandburn');

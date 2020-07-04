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

Route::view('/', 'home')->name('home');
Route::redirect('/home', '/');

Route::prefix('/public')->middleware('guest')->group(function () {
    Route::view('/appeal/ip', 'appeals.public.makeappeal.ip')->name('public.appeal.create.ip');
    Route::view('/appeal/account', 'appeals.public.makeappeal.account')->name('public.appeal.create.account');

    Route::post('/appeal/store', 'Appeal\PublicAppealController@store')->name('public.appeal.store');

    Route::get('/appeal/view', 'Appeal\PublicAppealController@view')->name('public.appeal.view');
    Route::post('/appeal/comment', 'Appeal\PublicAppealController@addComment')->name('public.appeal.comment');

    Route::get('/appeal/modify/{hash}', 'Appeal\PublicAppealModifyController@showForm')->name('public.appeal.modify');
    Route::post('/appeal/modify', 'Appeal\PublicAppealModifyController@submit')->name('public.appeal.modify.submit');

    Route::get('/appeal/verify/{appeal}/{token}', 'Appeal\PublicAppealController@showVerifyOwnershipForm')->name('public.appeal.verifyownership');
    Route::post('/appeal/verify/{appeal}', 'Appeal\PublicAppealController@verifyAccountOwnership')->name('public.appeal.verifyownership.submit');
});

Route::get('/appeal/{id}', 'AppealController@appeal')
    ->name('appeal.view')
    ->middleware('auth');

Route::get('/review', 'AppealController@appeallist')->name('appeal.list');
Route::get('/locate', 'AppealController@search')->name('appeal.search');

Route::post('/appeal/checkuser/{id}', 'AppealController@checkuser');
Route::post('/appeal/comment/{id}', 'AppealController@comment');
Route::get('/appeal/respond/{id}', 'AppealController@respond');
Route::get('/appeal/reserve/{id}', 'AppealController@reserve');
Route::post('/appeal/release/{id}', 'AppealController@release')->name('appeal.action.release');
Route::get('/appeal/open/{id}', 'AppealController@open');
Route::get('/appeal/findagain/{appeal}', 'AppealController@findagain');
Route::get('/appeal/close/{id}/{type}', 'AppealController@close');
Route::post('/appeal/checkuserreview/{appeal}', 'AppealController@checkuserreview')->name('appeal.action.checkuser');
Route::get('/appeal/privacy/{id}', 'AppealController@privacy');
Route::post('/appeal/tooladmin/{appeal}', 'AppealController@admin')->name('appeal.action.tooladmin');
Route::get('/appeal/invalidate/{id}', 'AppealController@invalidate');

Route::get('/appeal/template/{appeal}', 'AppealController@viewtemplates')->name('appeal.template');
Route::post('/appeal/template/{appeal}/{template}', 'AppealController@respond')->name('appeal.template.submit');

Route::get('/appeal/custom/{appeal}', 'AppealController@respondCustom')->name('appeal.customresponse');
Route::post('/appeal/custom/{appeal}', 'AppealController@respondCustomSubmit')->name('appeal.customresponse.submit');

Route::get('/publicappeal', 'Appeal\PublicAppealController@redirectLegacy');

Route::get('/appeal/privacy/{id}/{action}', 'AppealController@privacyhandle');

Route::get('/admin/users', 'Admin\UserController@index')->name('admin.users.list');
Route::get('/admin/users/{user}', 'Admin\UserController@show')->name('admin.users.view');
Route::post('/admin/users/{user}', 'Admin\UserController@update')->name('admin.users.update');

Route::get('/admin/bans', 'AdminController@listbans');
Route::get('/admin/sitenotices', 'AdminController@listsitenotices');
Route::get('/admin/templates', 'AdminController@listtemplates');

Route::get('admin/templates/create', 'AdminController@showNewTemplate');
Route::post('admin/templates/create', 'AdminController@makeTemplate');
Route::get('admin/templates/{template}', 'AdminController@editTemplate')->name('admin.templates.edit');
Route::post('admin/templates/{template}', 'AdminController@updateTemplate')->name('admin.templates.update');

Route::get('/oauth', 'Auth\\OauthLoginController@login')->name('login');
Route::get('/oauth/callback', 'Auth\\OauthLoginController@callback');
Route::get('/logout', 'Auth\\OauthLoginController@logout')->name('logout');

Route::get('/verifyaccount','AdminController@verifyAccount');
Route::get('/verify/{code}','AdminController@verify');
Route::get('/pending','HomeController@pending');

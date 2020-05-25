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

Route::get('/appeal/{id}', 'AppealController@appeal')->middleware('auth');

Route::get('/review', 'AppealController@appeallist')->name('appeal.list');
Route::get('/locate', 'AppealController@search')->name('appeal.search');

Route::post('/appeal/checkuser/{id}', 'AppealController@checkuser');
Route::post('/appeal/comment/{id}', 'AppealController@comment');
Route::get('/appeal/respond/{id}', 'AppealController@respond');
Route::get('/appeal/reserve/{id}', 'AppealController@reserve');
Route::get('/appeal/release/{id}', 'AppealController@release');
Route::get('/appeal/open/{id}', 'AppealController@open');
Route::get('/appeal/findagain/{id}', 'AppealController@findagain');
Route::get('/appeal/close/{id}/{type}', 'AppealController@close');
Route::get('/appeal/checkuserreview/{id}', 'AppealController@checkuserreview');
Route::get('/appeal/privacy/{id}', 'AppealController@privacy');
Route::get('/appeal/admin/{id}', 'AppealController@admin');
Route::get('/appeal/invalidate/{id}', 'AppealController@invalidate');
Route::get('/appeal/template/{id}', 'AppealController@viewtemplates');
Route::get('/appeal/template/{id}/{template}', 'AppealController@respond');
Route::get('/appeal/custom/{id}', 'AppealController@respondCustom');
Route::post('/appeal/custom/{id}', 'AppealController@respondCustomSubmit');
Route::get('/appeal/privacy/{id}/{action}', 'AppealController@privacyhandle');

Route::get('/admin/users', 'AdminController@listusers');
Route::get('/admin/bans', 'AdminController@listbans');
Route::get('/admin/sitenotices', 'AdminController@listsitenotices');
Route::get('/admin/templates', 'AdminController@listtemplates');
Route::post('admin/templates/create', 'AdminController@makeTemplate');
Route::post('admin/templates/{id}', 'AdminController@saveTemplate');
Route::get('admin/templates/create', 'AdminController@showNewTemplate');
Route::get('admin/templates/{id}', 'AdminController@modifyTemplate');

Auth::routes();

Route::get('/verifyaccount','AdminController@verifyAccount');
Route::get('/verify/{code}','AdminController@verify');
Route::get('/pending','HomeController@pending');
Route::get('/logout', 'HomeController@crashandburn');

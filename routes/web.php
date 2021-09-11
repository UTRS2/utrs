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
    Route::view('/appeal/ip', 'appeals.public.makeappeal.ip')
        ->name('public.appeal.create.ip')
        ->middleware('torblock');

    Route::view('/appeal/account', 'appeals.public.makeappeal.account')
        ->name('public.appeal.create.account')
        ->middleware('torblock');

    Route::post('/appeal/store', 'Appeal\PublicAppealController@store')
        ->name('public.appeal.store')
        ->middleware('torblock');

    Route::get('/appeal/view', 'Appeal\PublicAppealController@view')->name('public.appeal.view');
    Route::post('/appeal/comment', 'Appeal\PublicAppealController@addComment')->name('public.appeal.comment');

    Route::get('/appeal/modify/{hash}', 'Appeal\PublicAppealModifyController@showForm')->name('public.appeal.modify');
    Route::post('/appeal/modify', 'Appeal\PublicAppealModifyController@submit')->name('public.appeal.modify.submit');

    Route::get('/appeal/verify/{appeal}/{token}', 'Appeal\PublicAppealController@showVerifyOwnershipForm')->name('public.appeal.verifyownership');
    Route::post('/appeal/verify/{appeal}', 'Appeal\PublicAppealController@verifyAccountOwnership')->name('public.appeal.verifyownership.submit');
});

Route::get('/appeal/{id}', 'AppealController@appeal')->name('appeal.view');

Route::get('/review', 'AppealController@appeallist')->name('appeal.list');

Route::get('/search/quick', 'Appeal\AppealQuickSearchController@search')->name('appeal.search.quick');
Route::get('/search', 'Appeal\AppealAdvancedSearchController@search')->name('appeal.search.advanced');

Route::post('/appeal/checkuser/{appeal}', 'AppealController@checkuser')->name('appeal.action.viewcheckuser');
Route::post('/appeal/comment/{appeal}', 'AppealController@comment')->name('appeal.action.comment');

Route::post('/appeal/reserve/{appeal}', 'Appeal\AppealActionController@reserve')->name('appeal.action.reserve');
Route::post('/appeal/release/{appeal}', 'Appeal\AppealActionController@release')->name('appeal.action.release');

Route::post('/appeal/open/{appeal}', 'Appeal\AppealActionController@reOpen')->name('appeal.action.reopen');
Route::post('/appeal/findagain/{appeal}', 'Appeal\AppealActionController@reverifyBlockDetails')->name('appeal.action.findagain');
Route::post('/appeal/close/{appeal}/{type}', 'Appeal\AppealActionController@close')->name('appeal.action.close');
Route::post('/appeal/checkuserreview/{appeal}', 'Appeal\AppealActionController@sendToCheckUserReview')->name('appeal.action.requestcheckuser');
Route::post('/appeal/tooladmin/{appeal}', 'Appeal\AppealActionController@sendToTooladminReview')->name('appeal.action.tooladmin');
Route::post('/appeal/invalidate/{appeal}', 'Appeal\AppealActionController@invalidate')->name('appeal.action.invalidate');

Route::get('/appeal/template/{appeal}', 'AppealController@viewtemplates')->name('appeal.template');
Route::post('/appeal/template/{appeal}/{template}', 'AppealController@respond')->name('appeal.template.submit');

Route::get('/appeal/custom/{appeal}', 'AppealController@respondCustom')->name('appeal.customresponse');
Route::post('/appeal/custom/{appeal}', 'AppealController@respondCustomSubmit')->name('appeal.customresponse.submit');

Route::get('/publicappeal', 'Appeal\PublicAppealController@redirectLegacy');

Route::get('/admin/users', 'Admin\UserController@index')->name('admin.users.list');
Route::get('/admin/users/{user}', 'Admin\UserController@show')->name('admin.users.view');
Route::post('/admin/users/{user}', 'Admin\UserController@update')->name('admin.users.update');

Route::get('/admin/bans', 'Admin\BanController@index')->name('admin.bans.list');
Route::get('/admin/bans/create', 'Admin\BanController@new')->name('admin.bans.new');
Route::post('/admin/bans/create', 'Admin\BanController@create')->name('admin.bans.create');
Route::get('/admin/bans/{ban}', 'Admin\BanController@show')->name('admin.bans.view');
Route::post('/admin/bans/{ban}', 'Admin\BanController@update')->name('admin.bans.update');

Route::get('/admin/sitenotices', 'AdminController@listsitenotices')->name('admin.sitenotices.list');

Route::get('/admin/templates', 'AdminController@listtemplates')->name('admin.templates.list');
Route::get('admin/templates/create', 'AdminController@showNewTemplate');
Route::post('admin/templates/create', 'AdminController@makeTemplate');
Route::get('admin/templates/{template}', 'AdminController@editTemplate')->name('admin.templates.edit');
Route::post('admin/templates/{template}', 'AdminController@updateTemplate')->name('admin.templates.update');

Route::get('/wikis/list', 'WikiController@index')->name('wiki.list');

Route::get('/oauth', 'Auth\\OauthLoginController@login')->name('login');
Route::get('/oauth/callback', 'Auth\\OauthLoginController@callback');
Route::get('/logout', 'Auth\\OauthLoginController@logout')->name('logout');

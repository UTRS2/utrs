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
Route::middleware('set.locale')->group(function () {

    Route::view('/', 'home')->name('home');
    Route::redirect('/home', '/');
    Route::get('/changelang/{lang}', 'LanguageController@change');

    Route::prefix('/public')->middleware('guest')->group(function () {
        Route::get('/appeal/ip', 'Appeal\PublicAppealCreateController@showIpForm')
            ->name('public.appeal.create.ip')
            ->middleware('torblock');

        Route::get('/appeal/account', 'Appeal\PublicAppealCreateController@showAccountForm')
            ->name('public.appeal.create.account')
            ->middleware('torblock');

        Route::post('/appeal/store', 'Appeal\PublicAppealController@store')
            ->name('public.appeal.store')
            ->middleware('torblock');

        Route::post('/appeal/view', 'Appeal\PublicAppealController@view')->name('public.appeal.view');
        Route::post('/appealmap/view', 'Appeal\PublicAppealController@appealmap')->name('public.appeal.map');
        Route::post('/appeal/comment', 'Appeal\PublicAppealController@addComment')->name('public.appeal.comment');

        Route::post('/appeal/modify', 'Appeal\PublicAppealModifyController@showForm')->name('public.appeal.modify');
        Route::post('/appeal/modify/submit', 'Appeal\PublicAppealModifyController@submit')->name('public.appeal.modify.submit');

        Route::get('/appeal/verify/{appeal}/{token}', 'Appeal\PublicAppealController@showVerifyOwnershipForm')->name('public.appeal.verifyownership');
        Route::post('/appeal/verify/{appeal}', 'Appeal\PublicAppealController@verifyAccountOwnership')->name('public.appeal.verifyownership.submit');

        Route::post('/appeal/checkstatus', 'Appeal\PublicAppealController@checkStatus')->name('public.appeal.checkstatus');

        Route::post('/appeal/submitproxyquestion', 'Appeal\PublicAppealController@submitProxyReason')->name('public.appeal.proxyreason');

        Route::post('/appeal/recoverkey', 'AppealKeyController@sendAppealKeyReminder')->name('appealkey.reset');
        Route::get('/emailban/{method}/{token}', 'Appeal\PublicEmailBanController@showForm')->name('email.ban');
        Route::post('/emailban/{method}/{token}', 'Appeal\PublicEmailBanController@submit')->name('email.ban.submit');
    });

    Route::get('/appeal/map/{id}', 'AppealController@map')->name('appeal.map');
    Route::get('/appeal/{id}', 'AppealController@appeal')->name('appeal.view');

    Route::get('/review', 'AppealController@appeallist')->name('appeal.list');
    Route::get('/devreview', 'AppealController@devappeallist')->name('appeal.list.dev');

    Route::get('/emailpreview', 'EmailPreviewController@preview')->name('appeal.emailpreview');
    Route::get('/emailpreview/subject/{email}', 'EmailPreviewController@getSubjectLine')->name('appeal.emailpreview.subject');
    Route::get('/emailpreview/raw/{email}', 'EmailPreviewController@getRaw')->name('appeal.emailpreview.raw');
    Route::get('/emailpreview/{email}/{id}', 'EmailPreviewController@previewEmailByID')->name('appeal.emailpreview.viewbyid');
    Route::get('/emailpreview/{email}', 'EmailPreviewController@previewEmail')->name('appeal.emailpreview.view');

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
    Route::post('/appeal/transfer/{appeal}', 'Appeal\AppealActionController@transfer')->name('appeal.transfer');

    Route::get('/appeal/template/{appeal}', 'AppealController@viewtemplates')->name('appeal.template');
    Route::post('/appeal/template/{appeal}/{template}', 'AppealController@respond')->name('appeal.template.submit');

    Route::get('/appeal/custom/{appeal}', 'AppealController@respondCustom')->name('appeal.customresponse');
    Route::post('/appeal/custom/{appeal}', 'AppealController@respond')->name('appeal.customresponse.submit');

    Route::get('/appeal/acc/{appeal}', 'AppealController@sendToACC')->name('appeal.sendtoacc');

    Route::get('/publicappeal', 'Appeal\PublicAppealController@redirectLegacy');

    Route::get('/admin/users', 'Admin\UserController@index')->name('admin.users.list');
    Route::get('/admin/users/{user}', 'Admin\UserController@show')->name('admin.users.view');
    Route::post('/admin/users/{user}', 'Admin\UserController@update')->name('admin.users.update');
    Route::get('/admin/users/{user}/confirmemail/{token}', 'Admin\UserController@confirmEmail')->name('admin.users.confirmemail');

    Route::get('/admin/bans', 'Admin\BanController@index')->name('admin.bans.list');
    Route::get('/admin/bans/create', 'Admin\BanController@new')->name('admin.bans.new');
    Route::post('/admin/bans/create', 'Admin\BanController@create')->name('admin.bans.create');
    Route::get('/admin/bans/{ban}', 'Admin\BanController@show')->name('admin.bans.view');
    Route::post('/admin/bans/{ban}', 'Admin\BanController@update')->name('admin.bans.update');
    Route::get('/admin/emailban/{code}', 'Admin\BanController@banEmail')->name('admin.bans.emailban');
    Route::post('/admin/emailban/{code}', 'Admin\BanController@banEmailSubmit')->name('admin.bans.emailban.submit');
    Route::get('/admin/emailban', 'Admin\EmailBanController@index')->name('admin.emailban.list');
    Route::post('/admin/emailban/appeal/{emailid}', 'Admin\EmailBanController@appeal')->name('admin.emailban.appealban');
    Route::post('/admin/emailban/account/{emailid}', 'Admin\EmailBanController@account')->name('admin.emailban.accountban');
    Route::get('/admin/emailban/appealreason/{emailid}', 'Admin\EmailBanController@appealreason')->name('admin.emailban.appealreason');
    Route::get('/admin/emailban/accountreason/{emailid}', 'Admin\EmailBanController@accountreason')->name('admin.emailban.accountreason');


    Route::get('/admin/templates', 'Admin\TemplateController@index')->name('admin.templates.list');
    Route::get('/admin/templates/create', 'Admin\TemplateController@new');
    Route::post('/admin/templates/create', 'Admin\TemplateController@create');
    Route::get('/admin/templates/{template}', 'Admin\TemplateController@show')->name('admin.templates.edit');
    Route::post('/admin/templates/{template}', 'Admin\TemplateController@update')->name('admin.templates.update');

    Route::get('/admin/logs/{include}', 'Admin\LogListController@index')->name('admin.logs.list2');
    Route::get('/admin/logs', 'Admin\LogListController@index')->name('admin.logs.list');

    Route::get('/wikis/list', 'WikiController@index')->name('wiki.list');

    Route::get('/oauth', 'Auth\\OauthLoginController@login')->name('login');
    Route::get('/oauth/callback', 'Auth\\OauthLoginController@callback');
    Route::get('/logout', 'Auth\\OauthLoginController@logout')->name('logout');

    Route::get('/statistics/{name}/{wiki}/{length}', 'StatsController@display_appeals_chart')->name('stats.named');
    Route::get('/statistics', 'StatsController@display_appeals_chart')->name('stats.overall');

    Route::get('/translate/activate/{appeal}/{logid}', 'TranslateController@activate')->name('translate.activate');

    Route::get('/admin/apikeys', 'ApiController@apiList')->name('apikey.list');
    Route::post('/admin/apikeys/create', 'ApiController@create')->name('apikey.create');
    Route::post('/admin/apikeys/revoke/{apikey}', 'ApiController@revoke')->name('apikey.revoke');
    Route::post('/admin/apikeys/activate/{apikey}', 'ApiController@activate')->name('apikey.activate');
    Route::post('/admin/apikeys/regenerate/{apikey}', 'ApiController@regenerate')->name('apikey.regenerate');

    Route::get('/test', function () {
        $email = new App\Mail\Acc('https://accounts.wmflabs.org/randomkey', 'joe@null');
        return $email;
    });
});
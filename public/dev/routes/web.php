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


Auth::routes(['verify' => true]);

Route::middleware(['guest'])->group(function() {
    Route::prefix('account')->group(function () {
        Route::get('/menu', 'AccountController@menu')->name('account.menu');
        Route::get('/request', 'AccountController@request')->name('account.request');
        Route::post('/request', 'AccountController@exec')->name('account.exec');
        Route::get('/done', 'AccountController@done')->name('account.done');
        Route::get('/download', 'AgreementController@agreementDownload')->name('account.download');

    });

});

// 認証完了後のルーティング
Route::middleware(['verified'])->group(function() {
    // ホーム＆お知らせ
    Route::get('/', 'DashboardController@index')->name('dashboard.index');
    Route::get('/dashboard/detail/{id}', 'DashboardController@detail')->name('dashboard.detail');
    // 採用管理
    Route::prefix('apply')->group(function () {
        Route::get('/', 'ApplyController@index')->name('apply.index');
        Route::get('/regist/{id}', 'ApplyController@regist')->name('apply.regist'); // id=medicines.id
        Route::get('/allow/{id}', 'ApplyController@allow')->name('apply.allow'); // id=price_adoption.id
        Route::get('/registArrow/{id}', 'ApplyController@registArrow')->name('apply.registArrow'); // id=price_adoption.id
        Route::get('/confirm/{id}', 'ApplyController@confirm')->name('apply.confirm'); // id=price_adoption.id
        Route::get('/apploval/{id}', 'ApplyController@approval')->name('apply.approval'); // id=price_adoption.id
        Route::get('/adopt/{id}', 'ApplyController@adopt')->name('apply.adopt'); // id=price_adoption.id
        Route::get('/adopt2/{id}', 'ApplyController@adopt2')->name('apply.adopt2'); // id=price_adoption.id
        Route::get('/withdraw/{id}', 'ApplyController@withdraw')->name('apply.withdraw'); // id=price_adoption.id
        Route::get('/remand/{id}', 'ApplyController@remand')->name('apply.remand'); // id=price_adoption.id
        Route::get('/reject/{id}', 'ApplyController@reject')->name('apply.reject'); // id=price_adoption.id
        Route::get('/detail/{id}', 'ApplyController@detail')->name('apply.detail'); // id=price_adoption.id
        Route::post('/edit/{id}', 'ApplyController@edit')->name('apply.edit'); // id=price_adoption.id
        Route::get('/add', 'ApplyController@add')->name('apply.add');
        Route::post('/addexec', 'ApplyController@addexec')->name('apply.addexec');
        Route::get('/reentry/{id}', 'ApplyController@reentry')->name('apply.reentry');
        Route::get('/download', 'ApplyController@download')->name('apply.download');
        Route::get('/download2', 'ApplyController@download2')->name('apply.download2');
        Route::post('/ajaxStatusChangeBunkaren', 'ApplyController@ajaxStatusChangeBunkaren')->name('apply.ajaxStatusChangeBunkaren');
    });
    // 請求管理
    Route::prefix('claim')->group(function () {
        Route::get('/', 'ClaimController@index')->name('claim.index');
        Route::get('/regist', 'ClaimController@regist')->name('claim.regist');
        Route::post('/regexec', 'ClaimController@regexec')->name('claim.regexec');
        Route::get('/regexec_retry/{id}', 'ClaimController@regexec_retry')->name('claim.regexec_retry');
        Route::get('/recept/{id}', 'ClaimController@recept')->name('claim.recept');
        Route::get('/supply/{id}', 'ClaimController@supply')->name('claim.supply');
        Route::get('/trader/{id}', 'ClaimController@trader')->name('claim.trader');
        Route::get('/detail/{id}', 'ClaimController@detail')->name('claim.detail');
        Route::post('/import', 'ClaimController@import')->name('claim.import');
        Route::get('/confirm/{id}', 'ClaimController@confirm')->name('claim.confirm');
        Route::get('/payconf/{id}', 'ClaimController@payconf')->name('claim.payconf');
        Route::get('/complete/{id}', 'ClaimController@complete')->name('claim.complete');
        Route::get('/withdraw/{id}', 'ClaimController@withdraw')->name('claim.withdraw');
        Route::get('/remand/{id}', 'ClaimController@remand')->name('claim.remand');
        // 業者のからみで却下を追加
        Route::get('/reject/{id}', 'ClaimController@reject')->name('claim.reject');
        // 業者アップロード、ダウンロード、アップロード削除
        // 業者別
        Route::post('/sendh_upload', 'ClaimController@sendh_upload')->name('claim.sendh_upload');
        Route::post('/receiveh_upload', 'ClaimController@receiveh_upload')->name('claim.receiveh_upload');
        Route::get('/sendh_download/{id}', 'ClaimController@sendh_download')->name('claim.sendh_download');
        Route::get('/receiveh_download/{id}', 'ClaimController@receiveh_download')->name('claim.receiveh_download');
        Route::post('/sendh_delete', 'ClaimController@sendh_delete')->name('claim.sendh_delete');
        Route::post('/receiveh_delete', 'ClaimController@receiveh_delete')->name('claim.receiveh_delete');
        // 明細別
        Route::post('/send_upload', 'ClaimController@send_upload')->name('claim.send_upload');
        Route::post('/receive_upload', 'ClaimController@receive_upload')->name('claim.receive_upload');
        Route::get('/send_download/{id}', 'ClaimController@send_download')->name('claim.send_download');
        Route::get('/receive_download/{id}', 'ClaimController@receive_download')->name('claim.receive_download');
        Route::post('/send_delete', 'ClaimController@send_delete')->name('claim.send_delete');
        Route::post('/receive_delete', 'ClaimController@receive_delete')->name('claim.receive_delete');

        Route::post('/edit/{id}', 'ClaimController@edit')->name('claim.edit');
        Route::get('/comment/{id}', 'ClaimController@comment')->name('claim.comment');
        Route::post('/comment/regist', 'ClaimController@registComment')->name('claim.comment.regist');
        Route::get('/download/{id}', 'ClaimController@download')->name('claim.download');
        Route::get('/error_download/{id}', 'ClaimController@error_download')->name('claim.error_download');
        Route::get('/download_detail', 'ClaimController@download_detail')->name('claim.download_detail');
        //SQL ダウンロード
        Route::get('/sql_download2', 'ClaimController@sql_download2')->name('claim.sql_download2'); //請求一覧SQL
        Route::get('/sql_download3/{id}', 'ClaimController@sql_download3')->name('claim.sql_download3'); //請求一覧業者別ヘッダーSQL
        Route::get('/sql_download4/{id}', 'ClaimController@sql_download4')->name('claim.sql_download4'); //請求一覧業者別明細SQL
        Route::get('/sql_download5/{id}', 'ClaimController@sql_download5')->name('claim.sql_download5'); //請求一覧明細別ヘッダーSQL
        Route::get('/sql_download6/{id}', 'ClaimController@sql_download6')->name('claim.sql_download6'); //請求一覧明細別明細SQL

        // 請求データPDF出力
        Route::prefix('export')->group(function () {
        	Route::get('/', 'ClaimController@search')->name('claim.search');
        	Route::post('/', 'ClaimController@export')->name('claim.export');
        	// SQLダウンロード
        	Route::post('/sql_download7', 'ClaimController@export_sql_download7')->name('claim.export_sql_download7');  //請求PDF出力用SQL
        });
        Route::prefix('regist')->group(function () {
            Route::get('/detail', 'ClaimController@regist_detail')->name('claim.regist_detail');
            Route::get('/list', 'ClaimController@regist_list')->name('claim.regist_list');
            // SQLダウンロード
            Route::get('/sql_download', 'ClaimController@regist_sql_download')->name('claim.regist_sql_download'); //請求登録履歴一覧SQL
        });
    });
    // 採択管理
// この機能はapplyに吸収されたので閉鎖
//     Route::prefix('adoption')->group(function () {
//         Route::get('/', 'AdoptionController@index')->name('adoption.index');
//         Route::get('/detail/{id}', 'AdoptionController@detail')->name('adoption.detail');
//         Route::post('/edit/{id}', 'AdoptionController@edit')->name('adoption.edit');
//         Route::get('/delete/{id}', 'AdoptionController@delete')->name('adoption.delete');
//         Route::get('/download', 'AdoptionController@download')->name('adoption.download');
//     });
    // ユーザ管理
    Route::prefix('user')->group(function () {
        // グループに所属しているユーザ管理
        Route::prefix('group')->group(function () {
            Route::get('/', 'UserController@groupUsers')->name('user.group.index');
            Route::post('/', 'UserController@userRoleUpdate')->name('user.group.edit');
        });
        Route::get('/', 'UserController@index')->name('user.index');
        Route::get('/detail/{id}', 'UserController@detail')->name('user.detail');
        Route::post('/edit/{id}', 'UserController@edit')->name('user.edit');
        Route::get('/delete/{id}', 'UserController@delete')->name('user.delete');
        Route::get('/reset/{id}', 'UserController@reset')->name('user.reset');
        Route::get('/export', 'UserController@export')->name('user.export');
    });
    // ユーザグループ管理
    Route::prefix('usergroup')->group(function () {
        Route::get('/', 'UserGroupController@index')->name('usergroup.index');
        Route::get('/add', 'UserGroupController@add')->name('usergroup.add');
        Route::post('/add', 'UserGroupController@register')->name('usergroup.register');
        Route::get('/detail/{id}', 'UserGroupController@detail')->name('usergroup.detail');
        Route::post('/edit/{id}', 'UserGroupController@edit')->name('usergroup.edit');
        Route::get('/delete/{id}', 'UserGroupController@delete')->name('usergroup.delete');
        Route::get('/relation', 'UserGroupController@relation')->name('usergroup.relation_index');
        Route::get('/trader/search', 'UserGroupController@search');
        Route::get('/hospital/search', 'UserGroupController@searchHospital');
    });

    // お知らせ管理
    Route::prefix('info')->group(function () {
        Route::get('/', 'InfoController@index')->name('info.index');
        Route::get('/add', 'InfoController@add')->name('info.add');
        Route::post('/add', 'InfoController@regist')->name('info.regist');
        Route::post('/edit/{id}', 'InfoController@edit')->name('info.edit');
        Route::get('/detail/{id}', 'InfoController@detail')->name('info.detail');
        Route::get('/delete/{id}', 'InfoController@delete')->name('info.delete');
    });
    // 薬品管理
    Route::prefix('medicine')->group(function () {
        Route::get('/', 'MedicineController@index')->name('medicine.index');
        Route::get('/detail/{id}', 'MedicineController@detail')->name('medicine.detail');
        Route::post('/edit/{id}', 'MedicineController@edit')->name('medicine.edit');
        Route::get('/regist', 'MedicineController@regist')->name('medicine.regist');
        Route::post('/regexec', 'MedicineController@regexec')->name('medicine.regexec');
    });
    // アカウント管理
    Route::prefix('account')->group(function () {
        // 新規利用申請一覧
        Route::prefix('group')->group(function () {
            Route::get('/', 'AccountController@groups')->name('account.group.index');
            Route::get('/detail/{id}', 'AccountController@groupDetail')->name('account.group.detail');
            Route::post('/permission/{id}', 'AccountController@groupPermission')->name('account.group.permission');
            Route::get('/start/{id}', 'AccountController@groupStart')->name('account.group.start');
            Route::post('/start/exec/{id}', 'AccountController@groupStartExec')->name('account.group.start.exec');
            Route::post('/updatePrimaryUserGroupId/{id}', 'AccountController@updatePrimaryUserGroupId')->name('account.group.updatePrimaryUserGroupId');
        });
        // 既存グループ利用申請一覧
        Route::prefix('use')->group(function () {
            Route::get('/', 'AccountController@uses')->name('account.use.index');
            Route::get('/detail/{id}', 'AccountController@useDetail')->name('account.use.detail');
            Route::post('/start/{id}', 'AccountController@useStart')->name('account.use.start');
        });
        Route::prefix('token')->group(function () {
            Route::get('/', 'AccountController@token')->name('account.token.index');
            Route::post('/remake', 'AccountController@remakeToken')->name('account.token.remakeToken');
        });
    });
    // ロール管理
    Route::prefix('role')->group(function () {
        Route::get('/', 'RoleController@index')->name('role.index');
        Route::get('/add', 'RoleController@add')->name('role.add');
        Route::get('/search', 'RoleController@search');
        Route::post('/add', 'RoleController@regist')->name('role.regist');
        Route::get('/detail/{id}', 'RoleController@detail')->name('role.detail');
        Route::post('/edit/{id}', 'RoleController@edit')->name('role.edit');
        Route::get('/delete/{id}', 'RoleController@delete')->name('role.delete');
        Route::get('/copy/{id}', 'RoleController@copy')->name('role.copy');
        Route::post('/copy', 'RoleController@copyRegist')->name('role.copy.regist');
    });
    // 権限管理
    Route::prefix('privilege')->group(function () {
        Route::get('/', 'PrivilegeController@index')->name('privilege.index');
        Route::get('/download', 'PrivilegeController@download')->name('privilege.download');
        Route::get('/add', 'PrivilegeController@add')->name('privilege.add');
        Route::post('/add', 'PrivilegeController@regist')->name('privilege.regist');
        Route::get('/detail/{id}', 'PrivilegeController@detail')->name('privilege.detail');
        Route::post('/edit/{id}', 'PrivilegeController@edit')->name('privilege.edit');
        Route::get('/delete/{id}', 'PrivilegeController@delete')->name('privilege.delete');
    });
    // 規約管理
    Route::prefix('agreement')->group(function () {
        Route::get('/', 'AgreementController@index')->name('agreement.index');
        Route::get('/add', 'AgreementController@add')->name('agreement.add');
        Route::post('/add', 'AgreementController@regist')->name('agreement.regist');
        Route::get('/detail/{id}', 'AgreementController@detail')->name('agreement.detail');
        Route::post('/edit/{id}', 'AgreementController@edit')->name('agreement.edit');
        Route::get('/delete/{id}', 'AgreementController@delete')->name('agreement.delete');
        Route::get('/download/{id}', 'AgreementController@download')->name('agreement.download');
    });
});

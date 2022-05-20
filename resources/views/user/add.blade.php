@extends('layouts.app')

@php($title = 'ユーザー登録')

@php($category = 'user')

@php($breadcrumbs = ['HOME'=>'/','ユーザー登録'=>'javascript:void(0);'])

@section('content')
@if ($errors->any())
入力にエラーがあります<br>
{{ $errors->first('email') }}
@endif
{{ $viewdata->get('errorMessage') }}
{{ $viewdata->get('message') }}
<div class="card card-primary">
    <div class="card-body">
        <form class="form-horizontal h-adr" id="user-regist" action="{{ route('user.register') }}" method="post" accept-charset="utf-8"><input class="p-country-name" type="hidden" value="Japan">
            @csrf
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="name"> 名前<span class="badge badge-danger ml-2">必須</span></label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div><input class="form-control validate" id="name" type="text" name="name" placeholder="" data-validate="empty" value="{{ old('name') }}">
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="email"> メールアドレス<span class="badge badge-danger ml-2">必須</span></label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div><input class="form-control validate" id="email" type="text" name="email" placeholder="" data-validate="empty" value="{{ old('email') }}">
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="sub_id"> サブID</label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div><input class="form-control validate" id="sub_id" type="text" name="sub_id" placeholder="" value="{{ old('sub_id') }}">
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="password"> パスワード<span class="badge badge-danger ml-2">必須</span></label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div><input class="form-control validate" id="password" type="password" name="password" placeholder="" data-validate="empty" value="{{ old('password') }}">
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="password2"> パスワード確認<span class="badge badge-danger ml-2">必須</span></label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div><input class="form-control validate" id="password_confirmation" type="password" name="password_confirmation" placeholder="" data-validate="empty" value="{{ old('password_confirmation') }}">
                </div>
            </div>
            <!-- 施設は出さない
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="facility"> 施設<span class="badge badge-danger ml-2">必須</span></label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div><select class="form-control" type="text" data-search-item="facility_id" id="facility_id" name="facility_id">
                        <option value="">選択してください</option>
                        <option value="1">A施設</option>
                        <option value="2">B施設</option>
                        <option value="3">A本部</option>
                        <option value="4">文化連</option>
                    </select>
                </div>
            </div>
            -->
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="received"> 受信メール<span class="badge badge-success ml-2">任意</span></label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <div class="d-flex mr30 align-items-center block">
                        <div class="custom-control custom-checkbox custom-control-inline"><input class="custom-control-input validate input-checkbox" id="is_adoption_mail" name="is_adoption_mail" data-search-item="received" data-search-type="checkbox" value="1" type="checkbox" {{ checked(old('is_adoption_mail'), '1') }}><label class="custom-control-label" for="is_adoption_mail">採用承認</label></div>
                        <div class="custom-control custom-checkbox custom-control-inline"><input class="custom-control-input validate input-checkbox" id="is_claim_mail" name="is_claim_mail" data-search-item="received" data-search-type="checkbox" value="1" type="checkbox" {{ checked(old('is_claim_mail'), '1') }}><label class="custom-control-label" for="is_claim_mail">請求登録</label></div>
                    </div>
                </div>
            </div>
            <!--
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="received"> Googleアカウント連携<span class="badge badge-success ml-2">任意</span></label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <div class="d-flex mr30 align-items-center block">
                        <div class="custom-control custom-checkbox custom-control-inline"><input class="custom-control-input validate input-checkbox" id="is_google_account" name="is_google_account" data-search-item="received" data-search-type="checkbox" value="1" type="checkbox" {{ checked(old('is_google_account'), '1') }}><label class="custom-control-label" for="is_google_account">連携する</label></div>
                    </div>
                </div>
            </div>
            -->
        </form>
    </div>
    <div class="d-flex justify-content-around pt15 pb15 card-footer"><a class="btn btn-default btn-lg" href="/user"><i class="fas fa-chevron-left mr10"></i>ユーザー検索へ戻る</a>
        <div class="block"><button class="mr30 btn btn-primary btn-lg" type="button" data-toggle="modal" data-target="#modal-action" data-form="#form-facility-edit">
                <div class="fas fa-pen mr10"></div>登録する
            </button></div>
    </div>
</div>
<div class="fade modal" id="modal-action" role="dialog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ユーザー登録</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="tac text">このユーザーを登録してよろしいでしょうか？</p>
            </div>
            <div class="modal-footer"><button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
                <button class="btn btn-primary" type="submit" data-form="#user-regist">
                    <div class="fas fa-pen mr10"></div>登録する
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

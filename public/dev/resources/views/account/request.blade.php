@extends('layouts.out')
@php($title = '利用申請')
@php($category = 'account_apply')

@section('content')
{{ $viewdata->get('errorMessage') }}
{{ $viewdata->get('message') }}
<div class="card card-primary account-apply">
    <style>
        .card-primary{
            width: 680px;
            margin: 40px auto 0;
            border: #bbb solid 1px;
        }
        .form-horizontal{
            padding: 40px 20px 10px;
            width: 100%;
            margin: 0 auto;
        }
    </style>
    <div class="d-flex justify-content-between align-items-center card-header">
    <h3><i class="fas fa-user"></i> @if($viewdata->get('condition') == 1) 既存グループの利用申請 @elseif($viewdata->get('condition') == 2) 個人利用申請 @else 新規利用申請 @endif</h3>
    </div>
    <div class="pt-0 card-body">
        <form class="form-horizontal" action="{{ route('account.request') }}" method="post" id="account-request" accept-charset="utf-8">
            @csrf

            <input type="hidden" name="condition" value="{{$viewdata->get('condition')}}">
            <div class="form-group validateGroup row mb-3">
                <label class="col-sm-4 col-form-label" for="email">メールアドレス<span class="badge badge-danger ml-2">必須</span></label>
                <div class="col-sm-8">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <div class="input-group">
                        <input class="form-control validate" type="text" name="email" id="email" data-validate="empty email nospace hankaku" placeholder="" value="{{ old('email') }}">
                    </div>
                </div>
            </div>
            <div class="form-group validateGroup row mb-3">
                <label class="col-sm-4 col-form-label" for="group_key">グループキー @if($viewdata->get('condition') == 1 || $viewdata->get('condition') == 2)<span class="badge badge-danger ml-2">必須</span>@endif</label>
                <div class="col-sm-8">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <div class="input-group">
                        @if($viewdata->get('condition') == 1)
                        <input class="form-control validate" type="text" name="group_key" id="group_key" data-validate="empty nospace hankaku" placeholder="" value="{{ old('group_key') }}">
                        @elseif ($viewdata->get('condition') == 2)
                        <input class="form-control validate" type="text" name="group_key" id="group_key" data-validate="empty nospace hankaku" placeholder="" value="Independent" @if($viewdata->get('condition') == 2) readonly="readonly" @endif>
                        @elseif ($viewdata->get('condition') == 9)
                        <input class="form-control" type="text" name="group_key" id="group_key" placeholder="" value="{{ old('group_key') }}">
                        @endif
                    </div>
                </div>
            </div>
            <div class="form-group validateGroup row mb-3">
                <label class="col-sm-4 col-form-label" for="user_group_name">グループ名 @if($viewdata->get('condition') == 9)<span class="badge badge-danger ml-2">必須</span>@endif</label>
                <div class="col-sm-8">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <div class="input-group">
                        <input class="form-control @if($viewdata->get('condition') == 9) validate @endif" type="text" name="user_group_name" id="user_group_name" data-validate="empty nospace" placeholder="" value="{{ old('user_group_name') }}">
                    </div>
                </div>
            </div>
            <div class="form-group validateGroup row mb-3">
                <label class="col-sm-4 col-form-label" for="user_name">ユーザー名<span class="badge badge-danger ml-2">必須</span></label>
                <div class="col-sm-8">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <div class="input-group">
                        <input class="form-control validate" type="text" name="name" id="name" data-validate="empty nospace" placeholder="" value="{{ old('name') }}">
                    </div>
                </div>
            </div>
            <div class="form-group validateGroup row mb-3">
                <label class="col-sm-4 col-form-label" for="sub_id">サブID</label>
                <div class="col-sm-8">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <div class="input-group">
                        <input class="form-control" type="text" name="sub_id" id="sub_id" data-validate="empty nospace hankaku" placeholder="" value="{{ old('sub_id') }}">
                    </div>
                </div>
            </div>
            <div class="form-group validateGroup row mb-3">
                <label class="col-sm-4 col-form-label" for="remarks">通信欄</label>
                <div class="col-sm-8">
                    <div class="input-group">
                        <textarea class="form-control" name='remarks' placeholder="" rows="2">{{ old('remarks') }}</textarea>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <a target="_blank" href="{{ route( 'account.download') }}">利用規約ファイル[PDF]</a><br>
                    <div class="input-group">
                        <textarea>{{ $viewdata->get('agreement') }}</textarea>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-12">
                    <div class="d-flex mr30 align-items-center block">
                      <div class="custom-control custom-checkbox custom-control-inline">
                          <div class="error-tip">
                              <div class="error-tip-inner"></div>
                          </div>
                        <input class="custom-control-input validate input-checkbox" id="agree" name="agree" data-validate="empty" value="1" type="checkbox">
                        <label class="custom-control-label" for="agree">「厚生連共同購入ポータル規約」に同意します。
                        </label>
                      </div>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-12 text-center"><!--a class="btn btn-primary px-4 mb-3" data-submit="true" data-form="form-login" href="./dashboard.html">申請</a-->
                    <button class="mr30 btn btn-primary btn-lg" type="button"  data-toggle="modal" data-target="#modal-action">
                        <div class="fas fa-pen mr10"></div>申請する
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="fade modal" id="modal-action" role="dialog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">利用申請</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="tac text">利用を申請します。よろしいでしょうか？</p>
            </div>
            <div class="modal-footer"><button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button><button class="btn btn-primary" type="submit" data-form="#account-request">
                    <div class="fas fa-pen mr10"></div>申請する
                </button></div>
        </div>
    </div>
</div>

@endsection

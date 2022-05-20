@extends('layouts.app')

@php($title = '利用申請開始')

@php($category = 'system')

@php($breadcrumbs = ['HOME'=>'/', '利用申請一覧' => route('account.group.index'), '利用申請開始'=>'javascript:void(0);'])

@section('content')
@if ($errors->any())
入力にエラーがあります<br>
@foreach ($errors->all() as $message)
{{ $message }}
@endforeach
@endif
{{ $viewdata->get('errorMessage') }}
{{ $viewdata->get('message') }}
<div class="card card-primary">
    <form class="form-horizontal" action="{{ route('account.group.start.exec', ['id '=> $viewdata->get('detail')->id]) }}?page={{ $viewdata->get('detail')->page }}" method="post" id="account-start" accept-charset="utf-8">
        @csrf

    <div class="card-body">
        <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="name"> 名前</label>
            <div class="col-sm-4">
                <p>{{ $viewdata->get('detail')->name }}</p>
            </div>
        </div>
        <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="email"> メールアドレス</label>
            <div class="col-sm-4">
                <p>{{ $viewdata->get('detail')->email }}</p>
            </div>
        </div>
        <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="sub_id"> サブID</label>
            <div class="col-sm-4">
                <div class="error-tip">
                    <div class="error-tip-inner"></div>
                </div>
                {{ $viewdata->get('detail')->sub_id }}
            </div>
        </div>
        <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="group_key"> グループキー</label>
            <div class="col-sm-4">
                <p>{{ $viewdata->get('detail')->group_key }}</p>
            </div>
        </div>
        <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="user_group_name"> ユーザグループ名</label>
            <div class="col-sm-4">
                <p>{{ $viewdata->get('detail')->user_group_name }}</p>
            </div>
        </div>
        <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="remarks"> 通信欄</label>
            <div class="col-sm-4">
                <div class="error-tip">
                    <div class="error-tip-inner"></div>
                </div>
                <textarea class="form-control" name='remarks' placeholder="通信欄" rows="2">{{ old('remarks', $viewdata->get('detail')->remarks) }}</textarea>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-around pt15 pb15 card-footer"><a class="btn btn-default btn-lg" href="{{ route('account.group.index') }}?page={{ $viewdata->get('detail')->page }}"><i class="fas fa-chevron-left mr10"></i>利用申請一覧へ戻る</a>
        <div class="block">
            <button class="mr30 btn btn-primary btn-lg" type="button" data-toggle="modal" data-target="#modal-action">
                <div class="fas fa-pen mr10"></div>開始する
            </button>
        </div>
    </div>
</div>
<div class="fade modal" id="modal-action" role="dialog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">利用申請開始</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="tac text">この利用申請開始の更新してよろしいでしょうか？</p>
            </div>
            <div class="modal-footer"><button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
                <button class="btn btn-primary" type="submit" data-form="#account-start">
                    <div class="fas fa-pen mr10"></div>開始する
                </button>
            </div>
        </div>
    </div>
</div>
</form>
@endsection

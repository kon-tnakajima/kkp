@extends('layouts.app')

@php($title = '権限登録')

@php($category = 'role')

@php($breadcrumbs = ['HOME'=>'/', '権限一覧' => route('privilege.index'), '権限登録'=>'javascript:void(0);'])

@section('content')
@if ($errors->any())
入力にエラーがあります<br>
{{ $errors->first('email') }}
@endif
{{ $viewdata->get('errorMessage') }}
{{ $viewdata->get('message') }}
<div class="card card-primary">
    <div id="overlay">
        <div class="cv-spinner">
            <span class="spinner"></span>
        </div>
    </div>
    <div class="card-body">
        <form class="form-horizontal h-adr" id="privilege-regist" action="{{ route('privilege.regist') }}" method="post" accept-charset="utf-8">
            @csrf
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="name"> 名称<span class="badge badge-danger ml-2">必須</span></label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div><input class="form-control validate" id="name" type="text" name="name" placeholder="" data-validate="empty" value="{{ old('name') }}">
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="key_code"> KEYコード<span class="badge badge-danger ml-2">必須</span></label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <input class="form-control validate" id="key_code" type="text" name="key_code" placeholder="" data-validate="empty" value="{{ old('key_code') }}">
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="privilege_type"> 種別<span class="badge badge-danger ml-2">必須</span></label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <select class="form-control validate" id="privilege_type" name="privilege_type" data-validate="empty">
                        <option value="">選択してください</option>
@foreach($viewdata->get('types') as $type)
                            <option value="{{ $type['id'] }}" {{ selected(old('privilege_type'), $type['id']) }}>{{ $type['name'] }}</option>
@endforeach
                    </select>
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="description"> 説明</label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <textarea class="form-control" name='description' placeholder="" rows="3">{{ old('description') }}</textarea>
                </div>
            </div>
        </form>
    </div>
    <div class="d-flex justify-content-around pt15 pb15 card-footer"><a class="btn btn-default btn-lg" href="{{ route('privilege.index') }}@if(!empty($viewdata->get('page')))?page={{ $viewdata->get('page') }}@endif"><i class="fas fa-chevron-left mr10"></i>権限一覧へ戻る</a>
        <div class="block">
            <button class="mr30 btn btn-primary btn-lg" type="button" data-toggle="modal" data-target="#modal-action">
                <div class="fas fa-pen mr10"></div>更新する
            </button>
        </div>
    </div>
</div>
<div class="fade modal" id="modal-action" role="dialog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">権限登録</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="tac text">この権限を更新してよろしいでしょうか？</p>
            </div>
            <div class="modal-footer"><button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
                <button class="btn btn-primary" type="submit" data-form="#privilege-regist">
                    <div class="fas fa-pen mr10"></div>更新する
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

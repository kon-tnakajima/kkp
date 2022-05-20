@extends('layouts.app')

@php($title = 'ロール登録')

@php($category = 'role')

@php($breadcrumbs = ['HOME'=>'/', 'ロール一覧' => route('role.index'), 'ロール登録'=>'javascript:void(0);'])

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
        <form class="form-horizontal h-adr" id="role-regist" action="{{ route('role.regist') }}" method="post" accept-charset="utf-8">
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
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="group_type"> 種別<span class="badge badge-danger ml-2">必須</span></label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <select class="form-control validate" id="group_type" name="group_type" data-validate="empty">
                        <option value="">選択してください</option>
@foreach($viewdata->get('types') as $type)
                            <option value="{{ $type->name }}" {{ selected(old('group_type'), $type->name) }}>{{ $type->name }}</option>
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
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="privileges"> 権限</label>
            <div class="col-sm-4">
                    <br>
                    権限登録一覧<br>
                    <select class="form-control validate" type="text" name="add_privieges[]" id="add_privieges" multiple size=10>
                    </select>
                </div>
                <div class="col-sm-1">
                    <br><br><br><br><br>
                    <input type="button" id="right" name="right" value="≫" /><br><br>
                    <input type="button" id="left" name="left" value="≪" />
                </div>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <input type="text" type="search" data-search-item="priviege_name" id="priviege_name"><button id="search_priviege">検索</button><br>
                    権限未登録一覧<br>
                    <select class="form-control validate" type="text" name="privieges[]" id="privieges" multiple size=10>
@foreach($viewdata->get('privieges') as $priviege)
                            <option value="{{ $priviege['key_code'] }}">{{ $priviege['key_code'] }}</option>
@endforeach
                    </select>
                </div>
            </div>
        </form>
    </div>
    <div class="d-flex justify-content-around pt15 pb15 card-footer"><a class="btn btn-default btn-lg" href="{{ route('role.index') }}@if(!empty($viewdata->get('page')))?page={{ $viewdata->get('page') }}@endif"><i class="fas fa-chevron-left mr10"></i>ロール一覧へ戻る</a>
        <div class="block">
            <button class="mr30 btn btn-primary btn-lg" type="button" data-toggle="modal" data-target="#modal-action">
                <div class="fas fa-pen mr10"></div>登録する
            </button>
        </div>
    </div>
</div>
<div class="fade modal" id="modal-action" role="dialog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ロール登録</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="tac text">このロールを登録してよろしいでしょうか？</p>
            </div>
            <div class="modal-footer"><button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
                <button class="btn btn-primary" type="submit" data-form="#role-regist">
                    <div class="fas fa-pen mr10"></div>登録する
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

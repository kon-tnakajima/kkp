@extends('layouts.app')

@php($title = 'グループ登録')

@php($category = 'group')

@php($breadcrumbs = ['HOME'=>'/', 'ユーザグループ一覧' => route('usergroup.index'), 'グループ登録'=>'javascript:void(0);'])

@section('content')
@if ($errors->any())
入力にエラーがあります<br>
{{ $errors->first('email') }}
@endif
{{ $viewdata->get('errorMessage') }}
{{ $viewdata->get('message') }}
<div class="card card-primary">
    <div class="card-body">
        <form class="form-horizontal h-adr" id="usergroup-regist" action="{{ route('usergroup.register') }}" method="post" accept-charset="utf-8">
            @csrf
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="name"> 名称<span class="badge badge-danger ml-2">必須</span></label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div><input class="form-control validate" id="name" type="text" name="name" placeholder="" data-validate="empty" value="{{ old('name') }}">
                </div>
            </div>



            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="formal_name"> 正式名称<span class="badge badge-danger ml-2">必須</span></label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <input class="form-control validate" id="formal_name" type="text" name="formal_name" placeholder="" data-validate="empty" value="{{ old('formal_name') }}">
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="group_type"> 種別<span class="badge badge-danger ml-2">必須</span></label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <select class="form-control validate" id="group_type" name="group_type" data-validate="empty">
                        <option value="">選択してください</option>
@foreach($viewdata->get('group_types') as $group)
                            <option value="{{ $group->name }}" {{ selected(old('group_type'), $group->name) }}>{{ $group->name }}</option>
@endforeach
                    </select>
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="zip"> 郵便番号</label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <input class="form-control" id="zip" type="text" name="zip" placeholder="" value="{{ old('zip') }}">
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="address1"> 住所1</label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <input class="form-control" id="address1" type="text" name="address1" placeholder="" value="{{ old('address1') }}">
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="address2"> 住所2</label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <input class="form-control" id="address2" type="text" name="address2" placeholder="" value="{{ old('address2') }}">
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="tel"> 電話番号</label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <input class="form-control" id="tel" type="text" name="tel" placeholder="" value="{{ old('tel') }}">
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="fax"> FAX</label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <input class="form-control" id="fax" type="text" name="fax" placeholder="" value="{{ old('fax') }}">
                </div>
            </div>
        </form>
    </div>
    <div class="d-flex justify-content-around pt15 pb15 card-footer"><a class="btn btn-default btn-lg" href="{{ route('usergroup.index') }}"><i class="fas fa-chevron-left mr10"></i>グループ一覧へ戻る</a>
        <div class="block">
            <button class="mr30 btn btn-primary btn-lg" type="button" data-toggle="modal" data-target="#modal-action" data-form="#form-facility-edit">
                <div class="fas fa-pen mr10"></div>登録する
            </button>
        </div>
    </div>
</div>
<div class="fade modal" id="modal-action" role="dialog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">グループ登録</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="tac text">このグループを登録してよろしいでしょうか？</p>
            </div>
            <div class="modal-footer"><button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
                <button class="btn btn-primary" type="submit" data-form="#usergroup-regist">
                    <div class="fas fa-pen mr10"></div>登録する
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

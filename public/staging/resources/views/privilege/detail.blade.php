@extends('layouts.app')

@php($title = '権限編集')

@php($category = 'role')

@php($breadcrumbs = ['HOME'=>'/', '権限一覧' => route('privilege.index'), '権限編集'=>'javascript:void(0);'])

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
        <form class="form-horizontal h-adr" id="privilege-edit" action="{{ route('privilege.edit', ['id' => $viewdata->get('detail')->id]) }}" method="post" accept-charset="utf-8">
            @csrf
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="name"> 名称</label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <input id="name" type="text" name="name" placeholder="" value="{{ old('name', $viewdata->get('detail')->name) }}">
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="key_code"> KEYコード</label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <input id="key_code" type="text" name="key_code" placeholder="" value="{{ old('key_code', $viewdata->get('detail')->key_code) }}" disabled="disabled">
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="privilege_type_name"> 種別</label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <input id="privilege_type_name" type="text" name="privilege_type_name" placeholder="" value="{{ old('privilege_type_name', $viewdata->get('privilegeTypeName')) }}" disabled="disabled">
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="description"> 説明</label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <textarea class="form-control" name='description' placeholder="" rows="3">{{ old('description', $viewdata->get('detail')->description) }}</textarea>
                </div>
            </div>
        </form>
    </div>
    <div class="d-flex justify-content-around pt15 pb15 card-footer"><a class="btn btn-default btn-lg" href="{{ route('privilege.index') }}?page={{ $viewdata->get('detail')->page }}"><i class="fas fa-chevron-left mr10"></i>権限一覧へ戻る</a>
        <div class="block">
            <button class="mr30 btn btn-primary btn-lg" type="button" data-toggle="modal" data-target="#modal-action">
                <div class="fas fa-pen mr10"></div>更新する
            </button>
            <button class="btn btn-danger btn-lg" type="button" data-toggle="modal" data-target="#modal-delete">
                <div class="fas fa-ban mr10"></div>削除する
            </button>
        </div>
    </div>
</div>

<div class="fade modal" id="modal-delete" role="dialog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">権限の削除</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="tac text">この権限を削除してよろしいでしょうか？</p>
            </div>
            <div class="modal-footer"><button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
                <button class="btn btn-primary" type="button" onClick="location.href='{{ route('privilege.delete', ['id' => $viewdata->get('detail')->id]) }}'">
                    <div class="fas fa-pen mr10"></div>削除する
                </button>
            </div>
        </div>
    </div>
</div>

<div class="fade modal" id="modal-action" role="dialog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">権限編集</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="tac text">この権限を更新してよろしいでしょうか？</p>
            </div>
            <div class="modal-footer"><button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
                <button class="btn btn-primary" type="submit" data-form="#privilege-edit">
                    <div class="fas fa-pen mr10"></div>更新する
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

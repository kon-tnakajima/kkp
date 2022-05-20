@extends('layouts.app')

@php($title = '施設グループ詳細')

@php($category = 'facility_group')

@php($breadcrumbs = ['HOME'=>'/','施設グループ一覧'=>'/facility/group/','施設グループ詳細'=>'javascript:void(0);'])

@section('content')
@if ($errors->any())
入力にエラーがあります<br>
@endif
@if ( is_null($errorMessage) === false )
{{ $errorMessage }}
@endif
@if ( is_null($message) === false )
{{ $message }}
@endif
<div class="card card-primary facility_group_detail">
    <form class="form-horizontal" action="{{ route('facility.group.edit', ['id '=> $detail->id]) }}" method="post" id="facility-group-edit" accept-charset="utf-8">
        @csrf
        <div class="card-body">
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">施設グループID</label>
                <div class="col-sm-6">
                    <p class="form-control-static">{{ $detail->id }}</p>
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 col-form-label" for="code">コード<span class="badge badge-danger ml-2">必須</span></label>
                <div class="col-sm-2">
                    <div class="error-tip">
                        <div class="error-tip-inner">{{ $errors->first('code') }}</div>
                    </div>
                    <input class="form-control validate code" type="text" name="code" id="code" placeholder="" data-validate="empty" value="{{ $detail->code }}">
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="name">名前<span class="badge badge-danger ml-2">必須</span></label>
                <div class="col-sm-2">
                    <div class="error-tip">
                        <div class="error-tip-inner">{{ $errors->first('name') }}</div>
                    </div>
                    <input class="form-control validate name" type="text" name="name" id="name" placeholder="" data-validate="empty" value="{{ $detail->name }}">
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-around pt15 pb15 mt15">
            <a class="btn btn-default btn-lg" href="/facility/group"><i class="fas fa-chevron-left mr10"></i>施設グループ一覧へ戻る</a>
            <div class="block">
                <button class="mr30 btn btn-primary btn-lg" type="button" data-toggle="modal" data-target="#modal-action">
                    <div class="fas fa-pen mr10"></div>編集する
                </button>
                <button class="btn btn-danger btn-lg" type="button" data-toggle="modal" data-target="#modal-delete">
                    <div class="fas fa-ban mr10"></div>削除する
                </button>
            </div>
        </div>
        <div class="fade modal" id="modal-action" role="dialog" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">施設グループ更新</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <p class="tac text">この施設グループを更新してよろしいでしょうか？</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
                        <button class="btn btn-primary" type="submit" data-form="#facility-group-edit">
                            <div class="fas fa-pen mr10"></div>更新する
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@endsection

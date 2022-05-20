@extends('layouts.app')

@php($title = 'お知らせ更新')

@php($category = 'information')

@php($breadcrumbs = ['HOME'=>'/','お知らせ更新'=>'javascript:void(0);'])

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
    <div class="card-body">
        <form class="form-horizontal h-adr" action="{{ route('info.edit', ['id' => $viewdata->get('detail')->id]) }}" method="post" id="information-edit" accept-charset="utf-8">
            @csrf
            <div class="form-group validateGroup row">
                <label class="col-sm-2 col-form-label" for="facility_group">タイトル<span class="badge badge-danger ml-1">必須</span></label>
                <div class="col-sm-3">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <input class="form-control validate facility_name_short" type="text" name="title" id="facility_name_short" placeholder="" data-validate="empty" value="{{ old('title', $viewdata->get('detail')->title) }}">
                </div>
            </div>
            <div class="form-group validateGroup row">
                <label class="col-sm-2 col-form-label" for="valuation_id">本文<span class="badge badge-danger ml-1">必須</span></label>
                <div class="col-sm-3">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <textarea class="form-control w600 validate facility_name_short" type="text" name="contents" id="facility_name_short" placeholder="" data-validate="empty" style="height: 400px;">{{ old('contents', $viewdata->get('detail')->contents) }}</textarea>
                </div>
            </div>

            <div class="form-group validateGroup row">
                <label class="col-sm-2 col-form-label" for="facility_fax">カテゴリ<span class="badge badge-danger ml-1">必須</span></label>
                <div class="col-sm-3">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <select class="form-control validate actor_id" id="category" name="category" data-validate="empty">
                        <option value="">選択してください</option>
                        @foreach($viewdata->get('categories') as $id => $category)
                            <option value="{{ $id }}" {{ selected(old('category', $viewdata->get('detail')->category), $id) }}>{{ $category }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>
    </div>
    <div class="d-flex justify-content-around pt15 pb15 card-footer"><a class="btn btn-default btn-lg" href="{{ route('info.index') }}"><i class="fas fa-chevron-left mr10"></i>お知らせ検索へ戻る</a>
        <div class="block"><button class="mr30 btn btn-primary btn-lg" type="button" data-toggle="modal" data-target="#modal-action" data-form="#information-edit">
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
                <h5 class="modal-title">お知らせの削除</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="tac text">このお知らせを削除してよろしいでしょうか？</p>
            </div>
            <div class="modal-footer"><button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
                <button class="btn btn-primary" type="button" onClick="location.href='{{ route('info.delete', ['id' => $viewdata->get('detail')->id]) }}'">
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
                <h5 class="modal-title">お知らせ更新</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="tac text">このお知らせを更新してよろしいでしょうか？</p>
            </div>
            <div class="modal-footer"><button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
                <button class="btn btn-primary" type="submit" data-form="#information-edit">
                    <div class="fas fa-pen mr10"></div>更新する
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

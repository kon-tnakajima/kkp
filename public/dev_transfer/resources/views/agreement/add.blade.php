@extends('layouts.app')

@php($title = '規約登録')

@php($category = 'system')

@php($breadcrumbs = ['HOME'=>'/','規約登録'=>'javascript:void(0);'])

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
        <form class="form-horizontal h-adr" action="{{ route('agreement.add') }}" method="post" id="agreement-add" accept-charset="utf-8" enctype="multipart/form-data">
            @csrf

            <div class="form-group validateGroup row">
                <label class="col-sm-2 col-form-label" for="body">本文<span class="badge badge-danger ml-1">必須</span></label>
                <div class="col-sm-3">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <textarea class="form-control validate body" name='body'  data-validate="empty" placeholder="" rows="10">{{ old('body') }}</textarea>
                </div>
            </div>
            <div class="form-group validateGroup row">
                <label class="col-sm-2 col-form-label" for="from_date">開始日<span class="badge badge-danger ml-2">必須</span></label>
                <div class="col-sm-3">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <div class="d-flex w200">
                        <input class="form-control validate w150" data-view="days" data-min-view="days" data-validate="empty" data-date-format="yyyy/mm/dd" type="text" readonly="readonly" data-datepicker="true" id="from_date" name="from_date" data-validate="empty" value="{{ old('from_date') }}">
                        <div class="input-group-append">
                            <span class="input-group-text">
                                <i class="fa fa-calendar"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group validateGroup row">
                <label class="col-sm-2 col-form-label" for="to_date">ファイル<span class="badge badge-danger ml-2">必須</span></label>
                <div class="col-sm-3">
                    <div class="error-tip">
                        <div class="error-tip-inner">{{ $errors->first('file') }}</div>
                    </div>
                    <input class="form-control validate w400 h40" type="file" data-validate="empty" id="file" name="file">
                </div>
            </div>
        </form>
    </div>
    <div class="d-flex justify-content-around pt15 pb15 card-footer"><a class="btn btn-default btn-lg" href="{{ route('agreement.index') }}@if(!empty($viewdata->get('page')))?page={{ $viewdata->get('page') }}@endif"><i class="fas fa-chevron-left mr10"></i>規約検索へ戻る</a>
        <div class="block"><button class="mr30 btn btn-primary btn-lg" type="button" data-toggle="modal" data-target="#modal-action" data-form="#information-add">
                <div class="fas fa-pen mr10"></div>登録する
            </button></div>
    </div>
</div>
<div class="fade modal" id="modal-action" role="dialog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">規約登録</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="tac text">この規約を登録してよろしいでしょうか？</p>
            </div>
            <div class="modal-footer"><button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
                <button class="btn btn-primary" type="submit" data-form="#agreement-add">
                    <div class="fas fa-pen mr10"></div>登録する
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

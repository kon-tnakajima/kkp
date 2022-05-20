@extends('layouts.app')

@php($title = '規約更新')

@php($category = 'system')

@php($breadcrumbs = ['HOME'=>'/','規約更新'=>'javascript:void(0);'])

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
        <form class="form-horizontal h-adr" action="{{ route('agreement.edit', ['id' => $viewdata->get('detail')->id, 'page' => $viewdata->get('detail')->page]) }}" method="post" id="agreement-edit" accept-charset="utf-8" enctype="multipart/form-data">
            @csrf

            <div class="form-group validateGroup row">
                <label class="col-sm-2 col-form-label" for="body">本文<span class="badge badge-danger ml-1">必須</span></label>
                <div class="col-sm-3">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <textarea class="form-control validate body" name='body' data-validate="empty" placeholder="" rows="10">{{ old('body', $viewdata->get('detail')->body) }}</textarea>
                </div>
            </div>
            <div class="form-group validateGroup row">
                <label class="col-sm-2 col-form-label" for="from_date">開始日</label>
                <div class="col-sm-3">
                @if (isBunkaren())
                    <div class="error-tip">
                        <div class="error-tip-inner">{{ $errors->first('from_date') }}</div>
                    </div>
                    <div class="d-flex w200">
                        <input class="form-control validate w150" data-view="days" data-min-view="days" data-date-format="yyyy/mm/dd" type="text" readonly="readonly" data-datepicker="true" id="from_date" name="from_date"  data-validate="empty date" value="{{ old('from_date', $viewdata->get('detail')->from_date) }}">
                        <div class="input-group-append">
                            <span class="input-group-text">
                                <i class="fa fa-calendar"></i>
                            </span>
                        </div>
                    </div>
                @else
                    <p class="form-control-static">{{ $viewdata->get('detail')->from_date }}</p>
                @endif
                </div>
            </div>
            <div class="form-group validateGroup row">
                <label class="col-sm-2 col-form-label" for="to_date">終了日</label>
                <div class="col-sm-3">
                @if (isBunkaren())
                    <div class="error-tip">
                        <div class="error-tip-inner">{{ $errors->first('to_date') }}</div>
                    </div>
                    <div class="d-flex w200">
                        <input class="form-control validate w150" data-view="days" data-min-view="days" data-date-format="yyyy/mm/dd" type="text" readonly="readonly" data-datepicker="true" id="to_date" name="to_date" data-validate="empty date" value="{{ old('to_date', $viewdata->get('detail')->to_date) }}">
                        <div class="input-group-append">
                            <span class="input-group-text">
                                <i class="fa fa-calendar"></i>
                            </span>
                        </div>
                    </div>
                @else
                    <p class="form-control-static">{{ $viewdata->get('detail')->to_date }}</p>
                @endif
                </div>
            </div>
            <div class="form-group validateGroup row">
                <label class="col-sm-2 col-form-label" for="to_date">ファイル</label>
                <div class="col-sm-3">
                    <div class="error-tip">
                        <div class="error-tip-inner">{{ $errors->first('file') }}</div>
                    </div>
                    <input class="form-control validate w400 h40" type="file" id="file" name="file">
                </div>
            </div>
        </form>
    </div>
    <div class="d-flex justify-content-around pt15 pb15 card-footer"><a class="btn btn-default btn-lg" href="{{ route('agreement.index') }}?page={{ $viewdata->get('detail')->page }}"><i class="fas fa-chevron-left mr10"></i>規約検索へ戻る</a>
        <div class="block"><button class="mr30 btn btn-primary btn-lg" type="button" data-toggle="modal" data-target="#modal-action" data-form="#agreement-edit">
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
                <h5 class="modal-title">規約の削除</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="tac text">この規約を削除してよろしいでしょうか？</p>
            </div>
            <div class="modal-footer"><button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
                <button class="btn btn-primary" type="button" onClick="location.href='{{ route('agreement.delete', ['id' => $viewdata->get('detail')->id, 'page' => $viewdata->get('detail')->page]) }}'">
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
                <h5 class="modal-title">規約更新</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="tac text">この規約を更新してよろしいでしょうか？</p>
            </div>
            <div class="modal-footer"><button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
                <button class="btn btn-primary" type="submit" data-form="#agreement-edit">
                    <div class="fas fa-pen mr10"></div>更新する
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

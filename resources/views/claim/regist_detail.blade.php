@extends('layouts.app')

@php($title = '請求明細アップロード')

@php($category = 'claim')

@php($breadcrumbs = ['HOME'=>'./',$title=>'javascript:void(0);'])

@section('content')
@if ( !empty($viewdata->get('errorMessage')) )
<div class="alert alert-danger" role="alert">
{{ $viewdata->get('errorMessage') }}
</div>
@endif
@if ( !empty($viewdata->get('message')) )
<div class="alert alert-success" role="alert">
{{ $viewdata->get('message') }}
</div>
@endif
@if (!is_null($viewdata->get('list')))
<div class="card card-primary {{ $category }}">
@if (isBunkaren() )
    <div class="pt-0 card-body">
        <form class="form-horizontal" method="post" id="claim" action="{{ route('claim.import') }}" enctype="multipart/form-data">
            @csrf

            <div class="row mt30">
                <div class="form-group validateGroup col-10 d-flex">
                    <div class="d-flex mr30 align-items-center block">
                        <div class="error-tip">
                            <div class="error-tip-inner">{{ $errors->first('file') }}</div>
                        </div>
                        <input class="form-control validate w400 h40" type="file" data-validate="empty" id="file" name="file"><span class="badge badge-danger ml-2">必須</span>
                    </div>
                    <div class="d-flex mr30 align-items-center block">
	                    <button class="w100p mr30 btn btn-primary btn-lg" type="button" data-toggle="modal" data-target="#modal-action">
    	                    <div class="fas fa-edit mr10"></div>登録する
        	            </button>
	                    <div class="fade modal" id="modal-action" role="dialog" tabindex="-1" aria-hidden="true">
	                        <div class="modal-dialog" role="document">
	                            <div class="modal-content">
	                                <div class="modal-header">
	                                    <h5 class="modal-title">{{$title}}</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	                                </div>
	                                <div class="modal-body">
	                                    <p class="tac text">登録してよろしいでしょうか？</p>
	                                </div>
	                                <div class="modal-footer">
	                                    <button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
	                                    <button class="btn2 btn-primary" type="submit" data-form="#claim">
	                                        <div class="fas fa-pen mr10"></div>登録する
	                                    </button>
	                                </div>
	                            </div>
	                        </div>
	                    </div>

                    </div>
                </div>
            </div>
        </form>
     </div>
@endif
    </div>
</div>

<div class="fade modal" id="modal-action" role="dialog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form class="form-horizontal" method="post" id="file_upload" name="file_upload" action="{{ route('claim.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{$title}}</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row col-12">
                        <p class="tac text">アップロードするファイルを選択してください</p>
                        <div class="form-group validateGroup d-flex">
                            <div class="d-flex mr30 align-items-center block">
                                <div class="error-tip">
                                    <div class="error-tip-inner">{{ $errors->first('file') }}</div>
                                </div>
                                <input class="form-control validate w400 h40" type="file" data-validate="empty" id="file" name="file"><span class="badge badge-danger ml-2">必須</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
                    <button class="btn2 btn-primary" type="submit" data-form="#file_upload" not-close-window="true">
                        <div class="fas fa-pen mr10"></div>アップロードする
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endif
@endsection
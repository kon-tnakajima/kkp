@extends('layouts.app')

@php($title = '採用申請登録')

@php($category = 'apply')

@php($breadcrumbs = ['HOME'=>'/','採用申請登録'=>'javascript:void(0);'])

@section('content')
@if ($errors->any())
入力にエラーがあります<br>
@endif
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
<div class="card card-primary apply_registration">
    <div class="card-body">
        <form class="form-horizontal" action="{{ route('apply.addexec') }}" method="post"  accept-charset="utf-8" id="apply-action">
            @csrf
            <input type="hidden" name="page" id="page" value="{{ $viewdata->get('page') }}">
            <!-- <div class="form-group validateGroup row"><label class="col-sm-3 col-form-label" for="jan_code">JANコード<span class="badge badge-danger ml-2">必須</span></label>
                <div class="col-sm-6">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div><input class="form-control validate jan_code col-sm-4" type="text" name="jan_code" id="jan_code" placeholder="" data-validate="empty" value="{{ old('jan_code') }}">
                </div>
            </div> -->
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">申請施設</label>
                <div class="col-sm-3">
                    @if(!empty($viewdata->get('applygroups')))

                    <select class="form-control validate" name="apply_group" id="apply_group" data-validate="empty">
@foreach($viewdata->get('applygroups') as $group)
                            <option value="{{ $group->user_group_id }}" @if ($viewdata->get('primary_user_group_id') == $group->user_group_id) selected @endif >{{ $group->name }}</option>
@endforeach
                        </select>
                    @endif
                </div>
            </div>

            <div class="form-group validateGroup row"><label class="col-sm-3 col-form-label" for="sales_packaging_code">GS1販売<span class="badge badge-danger ml-2">必須</span></label>
                <div class="col-sm-6">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div><input class="form-control validate sales_packaging_code col-sm-5" type="text" name="sales_packaging_code" id="sales_packaging_code" placeholder="不明の場合は「不明」と入力して下さい" data-validate="empty gs1" value="{{ old('sales_packaging_code') }}">
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 col-form-label" for="unit">メーカー名<span class="badge badge-danger ml-2">必須</span></label>
                <div class="col-sm-6">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div><input class="form-control validate unit col-sm-5" type="text" name="maker_name" id="maker_name" placeholder="" data-validate="empty" value="{{ old('maker_name') }}">
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 col-form-label" for="name">商品名<span class="badge badge-danger ml-2">必須</span></label>
                <div class="col-sm-6">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div><input class="form-control validate name col-sm-5" type="text" name="name" id="name" placeholder="" data-validate="empty" value="{{ old('name') }}">
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 col-form-label" for="standard_unit">規格<span class="badge badge-danger ml-2">必須</span></label>
                <div class="col-sm-6">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div><input class="form-control validate standard_unit col-sm-5" type="text" name="standard_unit" id="standard_unit" placeholder="" data-validate="empty" value="{{ old('standard_unit') }}">
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 col-form-label" for="pack_unit_price">包装薬価<span class="badge badge-success ml-2">任意</span></label>
                <div class="col-sm-6">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div><input class="form-control validate pack_unit_price col-sm-5" type="text" name="pack_unit_price" id="pack_unit_price" placeholder="" data-validate="" value="{{ old('pack_unit_price') }}">
                </div>
            </div>
            <!--
            <div class="form-group validateGroup row"><label class="col-sm-3 col-form-label" for="pack_unit_price">オーナー区分（薬品・試薬）<span class="badge badge-success ml-2">任意</span></label>
                <div class="col-sm-6">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <select class="form-control" type="text" name="owner_classification" id="owner_classification">
                        <option value="" @if( old('owner_classification') == '' ) selected @endif>全て</option>
                        @foreach(App\Model\Medicine::OWNER_CLASSIFICATION_STR as $key => $value)
                        <option value={{ $key }} @if( old('owner_classification') == $key ) selected @endif>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
			-->
            <div class="form-group validateGroup row"><label class="col-sm-3 col-form-label" for="comment">コメント<span class="badge badge-success ml-2">任意</span></label>
                <div class="col-sm-6">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div><textarea class="form-control validate" name="comment" id="comment" rows="10" placeholder="" data-validate="">{{ old('comment') }}</textarea>
                </div>
            </div>
        </form>
    </div>
    <div class="d-flex justify-content-around pt15 pb15 card-footer"><a class="btn btn-default btn-lg" href="{{ route('apply.index') }}?page={{ $viewdata->get('page') }}"><i class="fas fa-chevron-left mr10"></i>採用申請一覧へ戻る</a>
        <div class="block"><button class="mr30 btn btn-primary btn-lg" type="button"  data-toggle="modal" data-target="#modal-action" data-form="#form-apply-edit">
                <div class="fas fa-pen mr10"></div>採用申請する
            </button></div>
    </div>
</div>
<div class="fade modal" id="modal-action" role="dialog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">採用申請</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="tac text">この薬品を採用申請してよろしいでしょうか？</p>
            </div>
            <div class="modal-footer"><button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button><button class="btn btn-primary" type="submit" data-form="#apply-action">
                    <div class="fas fa-pen mr10"></div>申請する
                </button></div>
        </div>
    </div>
</div>
@endsection

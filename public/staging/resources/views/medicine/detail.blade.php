@extends('layouts.app')

@php($title = '標準薬品詳細')

@php($category = 'medicine')

@php($breadcrumbs = ['HOME'=>'/','標準薬品検索'=>'/medicine/','標準薬品詳細'=>'javascript:void(0);'])

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
<div class="card card-primary apply_detail">
    <form class="form-horizontal" action="{{ route('medicine.edit', ['id '=> $detail->id]) }}" method="post" id="medicine-edit" accept-charset="utf-8">
        @csrf
        <div class="card-body">
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">薬品コード</label>
                <div class="col-sm-4">
                    <p class="form-control-static">{{ $detail->kon_medicine_id }}</p>
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">文化連コード</label>
                <div class="col-sm-4">
                    <input class="form-control validate" type="text" name="bunkaren_code" id="bunkaren_code" placeholder="" data-validate="empty" value="{{ $detail->bunkaren_code }}">
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">12桁コード</label>
                <div class="col-sm-4">
                    <input class="form-control validate" type="text" name="code" id="code" placeholder="" data-validate="empty" value="{{ $detail->code }}">
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">個別医薬品コード</label>
                <div class="col-sm-4">
                    <input class="form-control validate" type="text" name="medicine_code" id="medicine_code" placeholder="" data-validate="empty" value="{{ $detail->medicine_code }}">
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">レセプト電算処理コード1</label>
                <div class="col-sm-4">
                    <input class="form-control validate" type="text" name="receipt_computerized_processing1" id="receipt_computerized_processing1" placeholder="" data-validate="empty" value="{{ $detail->receipt_computerized_processing1 }}">
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">レセプト電算処理コード2</label>
                <div class="col-sm-4">
                    <input class="form-control validate" type="text" name="receipt_computerized_processing2" id="receipt_computerized_processing2" placeholder="" data-validate="empty" value="{{ $detail->receipt_computerized_processing2 }}">
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">一般名</label>
                <div class="col-sm-4">
                    <input class="form-control validate" type="text" name="popular_name" id="popular_name" placeholder="" data-validate="empty" value="{{ $detail->popular_name }}">
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">商品名</label>
                <div class="col-sm-4">
                    <input class="form-control validate" type="text" name="name" id="name" placeholder="" data-validate="empty" value="{{ $detail->name }}">
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">商品名読み</label>
                <div class="col-sm-4">
                    <input class="form-control validate" type="text" name="phonetic" id="phonetic" placeholder="" data-validate="empty" value="{{ $detail->phonetic }}">
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">規格単位</label>
                <div class="col-sm-4">
                    <input class="form-control validate" type="text" name="standard_unit" id="standard_unit" placeholder="" data-validate="empty" value="{{ $detail->standard_unit }}">
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">包装形態</label>
                <div class="col-sm-4">
                    <input class="form-control validate" type="text" name="package_presentation" id="package_presentation" placeholder="" data-validate="empty" value="{{ $detail->package_presentation }}">
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">単位</label>
                <div class="col-sm-4">
                    <input class="form-control validate" type="text" name="unit" id="unit" placeholder="" data-validate="empty" value="{{ $detail->unit }}">
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">薬価換算数</label>
                <div class="col-sm-4">
                    <input class="form-control validate" type="text" name="drug_price_equivalent" id="drug_price_equivalent" placeholder="" data-validate="empty" value="{{ $detail->drug_price_equivalent }}">
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">製造元コード</label>
                <div class="col-sm-3">
                    <select class="form-control" name="maker_id" id="maker_id">
                        <option value="" ></option>
@foreach($makers as $maker)
                        <option value="{{ $maker->id }}" @if( $maker->id == $detail->maker_id ) selected @endif >{{ $maker->name }}</option>
@endforeach
                    </select>
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">販売元コード</label>
                <div class="col-sm-3">
                    <select class="form-control" name="selling_agency_code" id="selling_agency_code">
                        <option value="" ></option>
@foreach($makers as $maker)
                        <option value="{{ $maker->id }}" @if( $maker->id == $detail->selling_agency_code ) selected @endif >{{ $maker->name }}</option>
@endforeach
                    </select>
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">薬効コード</label>
                <div class="col-sm-3">
                    <select class="form-control" name="medicine_effet_id" id="medicine_effet_id">
                        <option value="" ></option>
@foreach($medicine_effects as $medicine_effet)
                        <option value="{{ $medicine_effet->id }}" @if( $medicine_effet->id == $detail->medicine_effet_id ) selected @endif >{{ $medicine_effet->name }}</option>
@endforeach
                    </select>
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">剤型区分</label>
                <div class="col-sm-3">
                    <select class="form-control" name="dosage_type_division" id="dosage_type_division">
                        <option value=""></option>
                        @foreach(App\Helpers\Apply\getDosage_type_division() as $key => $value)
                            <option value="{{$key}}" @if( $key == $detail->dosage_type_division ) selected @endif>{{$value}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">経過措置期限</label>
                <div class="col-sm-4">
                    <input class="form-control validate" type="text" name="transitional_deadline" id="transitional_deadline" placeholder="" data-validate="empty" value="{{ $detail->transitional_deadline }}">
                </div>
            </div>

            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">劇毒区分1</label>
                <div class="col-sm-4">
                    <select class="form-control" name="danger_poison_category1" id="danger_poison_category1">
                        <option value="" ></option>
@foreach($medicine_effects as $medicine_effet)
                        <option value="{{ $medicine_effet->id }}" @if( $medicine_effet->id == $detail->danger_poison_category1 ) selected @endif >{{ $medicine_effet->name }}</option>
@endforeach
                    </select>
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">劇毒区分2</label>
                <div class="col-sm-4">
                    <select class="form-control" name="danger_poison_category2" id="danger_poison_category2">
                        <option value="" ></option>
@foreach($medicine_effects as $medicine_effet)
                        <option value="{{ $medicine_effet->id }}" @if( $medicine_effet->id == $detail->danger_poison_category2 ) selected @endif >{{ $medicine_effet->name }}</option>
@endforeach
                    </select>
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">劇毒区分3</label>
                <div class="col-sm-4">
                    <select class="form-control" name="danger_poison_category3" id="danger_poison_category3">
                        <option value="" ></option>
@foreach($medicine_effects as $medicine_effet)
                        <option value="{{ $medicine_effet->id }}" @if( $medicine_effet->id == $detail->danger_poison_category3 ) selected @endif >{{ $medicine_effet->name }}</option>
@endforeach
                    </select>
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">劇毒区分4</label>
                <div class="col-sm-4">
                    <select class="form-control" name="danger_poison_category4" id="danger_poison_category4">
                        <option value="" ></option>
@foreach($medicine_effects as $medicine_effet)
                        <option value="{{ $medicine_effet->id }}" @if( $medicine_effet->id == $detail->danger_poison_category4 ) selected @endif >{{ $medicine_effet->name }}</option>
@endforeach
                    </select>
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">劇毒区分5</label>
                <div class="col-sm-4">
                    <select class="form-control" name="danger_poison_category5" id="danger_poison_category5">
                        <option value="" ></option>
@foreach($medicine_effects as $medicine_effet)
                        <option value="{{ $medicine_effet->id }}" @if( $medicine_effet->id == $detail->danger_poison_category5 ) selected @endif >{{ $medicine_effet->name }}</option>
@endforeach
                    </select>
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">処方箋医薬品区分</label>
                <div class="col-sm-3">
                    <select class="form-control" name="prescription_drug_category" id="prescription_drug_category">
                        <option value="" ></option>
@foreach($medicine_effects as $medicine_effet)
                        <option value="{{ $medicine_effet->id }}" @if( $medicine_effet->id == $detail->prescription_drug_category ) selected @endif >{{ $medicine_effet->name }}</option>
@endforeach
                    </select>
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">生物由来品区分</label>
                <div class="col-sm-3">
                    <select class="form-control" name="biological_product_classification" id="biological_product_classification">
                            <option value="" ></option>
@foreach($medicine_effects as $medicine_effet)
                        <option value="{{ $medicine_effet->id }}" @if( $medicine_effet->id == $detail->biological_product_classification ) selected @endif >{{ $medicine_effet->name }}</option>
@endforeach
                    </select>
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">後発品フラグ</label>
                <div class="col-sm-3">
                    <div class="custom-control custom-checkbox custom-control-inline"><input class="custom-control-input validate" id="generic_product_flag" name="generic_product_flag" value="1" data-validate="checkboxempty" type="checkbox" @if( $detail->generic_product_flag ) checked @endif><label class="custom-control-label" for="generic_product_flag">後発品</label></div>
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">製造中止日</label>
                <div class="col-sm-4">
                    <input class="form-control validate" type="text" name="production_stop_date" id="production_stop_date" placeholder="" data-validate="empty" value="{{ $detail->production_stop_date }}">
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">販売中止日</label>
                <div class="col-sm-4">
                    <input class="form-control validate" type="text" name="discontinuation_date" id="discontinuation_date" placeholder="" data-validate="empty" value="{{ $detail->discontinuation_date }}">
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">病院コード</label>
                <div class="col-sm-4">
                    <input class="form-control validate" type="text" name="hospital_code" id="hospital_code" placeholder="" data-validate="empty" value="{{ $detail->hospital_code }}">
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">オーナー区分</label>
                <div class="col-sm-3">
                    <select class="form-control" name="owner_classification" id="owner_classification">
                        <option value="" ></option>
@foreach($medicine_effects as $medicine_effet)
                        <option value="{{ $medicine_effet->id }}" @if( $medicine_effet->id == $detail->owner_classification ) selected @endif >{{ $medicine_effet->name }}</option>
@endforeach
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-1">
                </div>
                <div class="mt20 mb20 block-table-control col-10 d-flex">
                    <h4 class="mt10 mb10">包装単位</h4>
                </div>
                <div class="col-1">
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">JANコード</label>
                <div class="col-sm-2">
                    <input class="form-control validate" type="text" name="jan_code" id="jan_code" placeholder="" data-validate="empty" value="{{ $detail->packUnit->jan_code }}">
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">hotコード</label>
                <div class="col-sm-4">
                    <input class="form-control validate" type="text" name="hot_code" id="hot_code" placeholder="" data-validate="empty" value="{{ $detail->packUnit->hot_code }}">
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">表示用包装単位</label>
                <div class="col-sm-4">
                    <input class="form-control validate" type="text" name="display_pack_unit" id="display_pack_unit" placeholder="" data-validate="empty" value="{{ $detail->packUnit->display_pack_unit }}">
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">包装数量</label>
                <div class="col-sm-4">
                    <input class="form-control validate" type="text" name="pack_count" id="pack_count" placeholder="" data-validate="empty" value="{{ $detail->packUnit->pack_count }}">
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">包装単位</label>
                <div class="col-sm-4">
                    <input class="form-control validate" type="text" name="pack_unit" id="pack_unit" placeholder="" data-validate="empty" value="{{ $detail->packUnit->pack_unit }}">
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">総包装数量</label>
                <div class="col-sm-4">
                    <input class="form-control validate" type="text" name="total_pack_count" id="total_pack_count" placeholder="" data-validate="empty" value="{{ $detail->packUnit->total_pack_count }}">
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">総包装単位</label>
                <div class="col-sm-4">
                    <input class="form-control validate" type="text" name="total_pack_unit" id="total_pack_unit" placeholder="" data-validate="empty" value="{{ $detail->packUnit->total_pack_unit }}">
                </div>
            </div>
            <div class="row">
                <div class="col-1">
                </div>
                <div class="mt20 mb20 block-table-control col-10 d-flex">
                    <h4 class="mt10 mb10">薬価</h4>
                </div>
                <div class="col-1">
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">適用開始日</label>
                <div class="col-sm-4">
                    <input class="form-control validate" type="text" name="start_date" id="start_date" placeholder="" data-validate="empty" value="{{ $detail->medicinePrice->start_date }}">
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">適用終了日</label>
                <div class="col-sm-4">
                    <input class="form-control validate" type="text" name="end_date" id="end_date" placeholder="" data-validate="empty" value="{{ $detail->medicinePrice->end_date }}">
                </div>
            </div>
            <div class="form-group row"><label class="col-sm-3 form-control-label" for="medicine_price"> 薬価（円）</label>
                <div class="col-sm-4">
                    <input class="form-control validate" type="text" name="price" id="price" placeholder="" data-validate="empty" value="{{ $detail->medicinePrice->price }}">
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-around pt15 pb15 mt15">
            <a class="btn btn-default btn-lg" href="{{ route('medicine.index') }}"><i class="fas fa-chevron-left mr10"></i>標準薬品一覧へ戻る</a>
            <div class="block">
                <button class="mr30 btn btn-primary btn-lg" type="button" data-toggle="modal" data-target="#modal-action">
                    <div class="fas fa-pen mr10"></div>登録する
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
                        <h5 class="modal-title">標準薬品登録</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <p class="tac text">この薬品を登録してよろしいでしょうか？</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
                        <button class="btn-primary" type="submit" data-form="#medicine-regist">
                            <div class="fas fa-pen mr10"></div>登録する
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@endsection

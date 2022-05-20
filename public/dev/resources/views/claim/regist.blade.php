@extends('layouts.app')

@php($title = '請求登録')

@php($category = 'claim')

@php($breadcrumbs = ['HOME'=>'/',$title=>'javascript:void(0);'])

@section('content')

<?php
$kengen = $viewdata->get('kengen');
$regist_kengen = $kengen["請求登録_請求登録"]->privilege_value == "true";
$purchase_waku_kengen = $kengen["請求登録_仕入価枠表示"]->privilege_value == "true";
$sales_waku_kengen = $kengen["請求登録_納入価枠表示"]->privilege_value == "true";
?>

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

<style type="text/css">
.form-control[readonly]{
  background-color:#fff;
  cursor: default;
}

label {
  margin: 0;
}

input,
select:not(.section) {
  width: 300px !important;
  overflow: hidden;
}

input#file {
  width: 800px !important;
}

input:focus,
select:focus {
  border: 2px solid green !important;
  /* background-color:#fff8dc !important; */
  outline: none
}

input,
select:not(.section) {
  /* border: 1px solid green !important; */
  background-color: #fff8dc !important;
  /* font-weight: 600 !important; */
}
input.require,
select.require {
  background-color: #ffdcdc !important;
  /* font-weight: 600 !important; */
}

</style>

<div class="card card-primary {{ $category }}">
    <div class="pt-0 card-body">
        <form class="form-horizontal" method="post" id="claim" action="{{ route('claim.regexec') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb30">
                <span>月次実績ファイルを添付し、下記項目をご記入ください。<br>ファイル取違防止のため、可能な範囲で金額をご記入ください。</span>
            </div>

            <table>
                <thead>

                    <!-- 施設 -->
                    
                    @if( count($viewdata->get('user_groups')) > 1)
                    @component('claim.components.regist_item', ['label' => '施設', 'property' => 'facility', 'require' => true, 'errors' => $errors])
                        <select class="form-control validate require" name="facility" id="facility">
                        @foreach($viewdata->get('user_groups') as $user_group)
                            <option value="{{$user_group->user_group_id}}" @if( $user_group->user_group_id == $viewdata->get('select_facility') ) selected @endif >{{$user_group->user_group_name}}</option>
                        @endforeach 
                        </select>
                    @endcomponent
                    @elseif( count($viewdata->get('user_groups')) == 1)
                    @component('claim.components.regist_item', ['label' => '施設', 'property' => 'facility', 'require' => false, 'errors' => $errors])
                        <p class="form-control-static">{{ $viewdata->get('user_groups')[0]->user_group_name }} </p>
                        @include('layouts.input_hidden', ['parent' => '[claim.regist]', 'name' => 'facility','id' => 'facility', 'value' => $viewdata->get('user_groups')[0]->user_group_id])
                    @endcomponent
                    @endif
                    
                    <!-- 添付 -->
                    @component('claim.components.regist_item', ['label' => '添付', 'property' => 'file', 'require' => true, 'errors' => $errors])
                        <label class="file-select">
                            <input class="form-control validate h40 require" type="file" data-validate="empty" id="file" name="file">
                        </label>
                    @endcomponent

                    <!-- 取引年月 -->
                    @component('claim.components.regist_item', ['label' => '取引年月', 'property' => 'claim_month', 'require' => true, 'errors' => $errors])
                        <input class="form-control validate require" data-view="months" data-min-view="months" data-date-format="yyyy/mm" type="text" readonly="readonly" data-datepicker="true" id="claim_month" name="claim_month" data-validate="empty" value="{{ $viewdata->get('claim_month') }}">
                        <div class="input-group-append">
                            <span class="input-group-text">
                                <i class="fa fa-calendar"></i>
                            </span>
                        </div>
                    @endcomponent

                    <!-- ファイル種別 -->
                    @component('claim.components.regist_item', ['label' => 'ファイル種別', 'property' => 'supply_division', 'require' => true, 'errors' => $errors])
                        <select class="form-control validate require" name="supply_division" id="supply_division">
                            <option value="0">選択してください</option>
@foreach($viewdata->get('data_kbnList') as $data_kbn)
                            <option value="{{$data_kbn->id}}" @if( $data_kbn->id == $viewdata->get('select_data_type')) selected @endif >{{$data_kbn->data_type_name}}</option>
@endforeach
                        </select>
                    @endcomponent

                    <!-- ファイル薬価金額合計 -->
                    @component('claim.components.regist_item', ['label' => 'ファイル薬価金額合計', 'property' => 'medicine_price_total', 'require' => false, 'errors' => $errors])
                        <input class="form-control validate medicine_price_total" type="text" name="medicine_price_total" id="medicine_price_total" placeholder="" data-validate=" zero" value="{{$viewdata->get('medicine_price_total')}}">
                    @endcomponent

                    <!-- ファイル購入金額合計 -->
@if ($sales_waku_kengen)
                    @component('claim.components.regist_item', ['label' => 'ファイル購入金額合計', 'property' => 'sales_price_total', 'require' => false, 'errors' => $errors])
                        <input class="form-control validate sales_price_total" type="text" name="sales_price_total" id="sales_price_total" placeholder="" data-validate=" zero" value="{{$viewdata->get('sales_price_total')}}">
                    @endcomponent
@endif

                    <!-- ファイル仕入金額合計 -->
@if ($purchase_waku_kengen)
                    @component('claim.components.regist_item', ['label' => 'ファイル仕入金額合計', 'property' => 'purchase_price_total', 'require' => false, 'errors' => $errors])
                        <input class="form-control validate purchase_price_total" type="text" name="purchase_price_total" id="purchase_price_total" placeholder="" data-validate=" zero" value="{{$viewdata->get('purchase_price_total')}}">
                    @endcomponent
@endif
                </thead>
@if ($regist_kengen)
                <tfoot>
                   <tr>
                       <td colspan="3" style="text-align: center;">
                            <button class="btn btn-primary btn-lg mt10" type="button" data-toggle="modal" data-target="#modal-action">
                                <div class="fas fa-edit mr10"></div>登録する
                            </button>
                        </td>
                   </tr>
                </tfoot>
@endif
            </table>

            <div class="row">
                <div class="col-2">
                    <div class="fade modal" id="modal-action" role="dialog" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">{{$title}}</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                </div>
                                <div class="modal-body">
                                    <p class="tac text">このファイルを登録してよろしいでしょうか？</p>
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
        </form>
    </div>
</div>

@endsection

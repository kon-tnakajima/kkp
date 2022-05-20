@extends('layouts.app')

@php($title = '請求データ出力')

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

<?php
/*
 * 権限の取得
 */
$kengen               = $viewdata->get('kengen');
$admin_kengen         = $kengen["一覧SQLダウンロード"]->privilege_value == "true";
$sales_waku_kengen    = $kengen["請求照会_納入価枠表示"]->privilege_value == "true";
?>

<style type="text/css">
.form-control[readonly]{
  background-color:#fff;
  cursor: default;
}

</style>
@if (!is_null($viewdata->get('list')))
<div class="card card-primary {{ $category }}">
    <div class="pt-0 card-body">
        <!-- form class="form-horizontal" method="post" id="export" action="{{ route('claim.export') }}" accept-charset="utf-8" -->
        <form class="form-horizontal" method="post" id="export" accept-charset="utf-8">
            @csrf
            <div class="row mt30">
                <div class="col-8 tal">
                    <a class="anchor anchor-clear-search mr40" href="javascript:void(0);"><i class="fas fa-eraser mr10"></i>出力条件をクリア</a>
                </div>
            </div>

            <div class="row mt10">
                <p class="ml20 mr10 fwb text">出力帳票<span class="badge badge-danger ml-2">必須</span></p>
                <select class="w300 form-control validate" type="text" name="output_type" id="output_type" required="required" style="width:10%;" data-search-item="output_type">
                    <option value="" selected>選択してください</option>
                    <option value="1">施設・供給区分・業者別合計表</option>
                    <option value="2">施設・供給区分・メーカー別合計表</option>
                </select>
            </div>
<br>
            <div class="row mt10">
                <p class="ml20 mr10 fwb text">施設</p>
                <select class="form-control" type="text" style="width:10%;" multiple size=15 name="user_groups[]" data-search-item="user_group">
@foreach($viewdata->get('user_groups') as $group)
                        <option value="{{$group->user_group_id}}">{{$group->user_group_name}}</option>
@endforeach
                </select>

                <p class="ml20 mr10 fwb text">年月</p>
                <select class="form-control" type="text" style="width:10%;" multiple size=15 name="past_dates[]" data-search-item="past_date">
@foreach($viewdata->get('past_dates') as $month)
                        <option value="{{$month}}">{{$month}}</option>
@endforeach
                </select>

                <p class="ml20 mr10 fwb text">供給区分</p>
                <select class="form-control" type="text" style="width:10%;" multiple size=15 name="supplies[]" data-search-item="supply">
@foreach($viewdata->get('supplies') as $supply)
                        <option value="{{$supply}}">{{$supply}}</option>
@endforeach
                </select>

                <div class="trader_box row ml10">
                    <p class="ml20 mr10 fwb text">業者</p>
                    <select class="form-control" type="text" style="width:70%;" id="trader" multiple size=15 name="traders[]" data-search-item="trader">
@foreach($viewdata->get('traders') as $trader)
                        <option value="{{$trader->trader_user_group_id}}">{{$trader->trader_group_name}}</option>
@endforeach
                    </select>
                </div>

                <div class="maker_box row ml10">
                    <p class="maker ml20 mr10 fwb text">メーカー</p>
                    <select class="form-control" type="text" style="width:70%;" id="maker" multiple size=15 name="makers[]" data-search-item="maker">
@foreach($viewdata->get('makers') as $maker)
                        <option value="{{$maker}}">{{$maker}}</option>
@endforeach
                    </select>
                </div>

                
            </div>
            <div class="row mt10">
                <div class="col-12 tar">
                    <!-- button class="btn-primary" type="submit" data-form="#export" -->
                    @if ($sales_waku_kengen)
                    <button class="btn-primary" type="submit" id ="export_button" name="{{route('claim.export')}}">
                        <i class="fas fa-file mr10"></i>PDF出力する
                    </button>
                    @endif
                    <!-- SQLダウンロード開発時用  -->
                    @if ($admin_kengen)
                    <button class="btn-primary" type="submit" id ="sql_download" name="{{route('claim.export_sql_download7')}}">
                        <div class="col-12 tar"></div>SQLダウンロード
                    </button>
                    @endif
                    <!--
                    <a
                     class="w200 btn btn-primary"
                     href="{{ route('claim.export_sql_download7') }}"
                     download="請求データPDF出力SQL.txt"
                    >
                    <div class="col-12 tar"></div>SQLダウンロード
                    </a>
                    -->
                </div>
            </div>
        </form>
    </div>
</div>
@endif
<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
<script>
  $("#export_button").click(function(){
    //alert('test');
    var url = $('#export_button').attr('name');
    $('#export').attr('action', url);
    $('#export').submit();

  });
  $("#sql_download").click(function(){
     //alert('test');
     var url = $('#sql_download').attr('name');
     $('#export').attr('action', url);
     $('#export').submit();

  });
</script>
@endsection
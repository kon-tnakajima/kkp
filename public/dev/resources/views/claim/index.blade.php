@extends('layouts.app')

@php($title = '請求照会')

@php($category = 'claim')

@php($breadcrumbs = ['HOME'=>'/',$title=>'javascript:void(0);'])

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
$kengen          = $viewdata->get('kengen');
$purchase_kengen = $kengen["請求登録_仕入価枠表示"]->privilege_value == "true";
$sales_kengen    = $kengen["請求登録_納入価枠表示"]->privilege_value == "true";
$admin_kengen    = $kengen["一覧SQLダウンロード"]->privilege_value == "true";
?>

<style type="text/css">
    .form-control[readonly] {
        background-color: #fff;
        cursor: default;
    }

    .input-item {
        display: -webkit-box;
        display: -ms-flexbox;
        display: -webkit-flex;
        display: flex;
        -webkit-box-orient: horizontal;
        -webkit-box-direction: normal;
        -ms-flex-direction: row;
        flex-direction: row;
        -webkit-box-align: start;
        -ms-flex-align: start;
        align-items: flex-start;
        -webkit-box-pack: start;
        -ms-flex-pack: start;
        justify-content: flex-start;
        margin-top: 0.3em;
    }

    .input-item>p {
        white-space: nowrap;
        font-weight: bold;
        margin-right: 0.3em;
    }

    .input-item>p:after {
        content: ""
    }

    .check-box-container {
        width: 100%;
        display: -webkit-box;
        display: -ms-flexbox;
        display: -webkit-flex;
        display: flex;
        -webkit-box-orient: horizontal;
        -webkit-box-direction: normal;
        -ms-flex-direction: row;
        flex-direction: row;
        -webkit-box-align: center;
        -ms-flex-align: center;
        align-items: center;
        -webkit-box-pack: start;
        -ms-flex-pack: start;
        justify-content: flex-start;
        -ms-flex-wrap: wrap;
        flex-wrap: wrap;
    }

    table.search-collapse {
        width: auto;
    }

    table.search-collapse tr td {
        padding-top: 0.3em;
        padding-right: 0.3em;
    }

    .check-box-container label.generic {
        position: relative;
        margin: 0;
    }

    .check-box-container label.generic span.text {
        position: relative;
        cursor: pointer;
        white-space: nowrap;
        z-index: 2;
    }

    .check-box-container label.generic input~span.deco {
        display: block;
        font-size: inherit;
        z-index: 1;
    }

    .check-box-container label.generic.normal {
        display: -webkit-box;
        display: -ms-flexbox;
        display: -webkit-flex;
        display: flex;
        -webkit-box-orient: horizontal;
        -webkit-box-direction: normal;
        -ms-flex-direction: row;
        flex-direction: row;
        -webkit-box-align: center;
        -ms-flex-align: center;
        align-items: center;
        -webkit-box-pack: start;
        -ms-flex-pack: start;
        justify-content: flex-start;
        cursor: pointer;
    }

    .check-box-container label.generic.normal input,
    .check-box-container label.generic.normal select {
        flex: 1;
    }

    .check-box-container label.generic.normal:hover,
    .check-box-container label.generic.normal:hover input~span {
        background-color: lightyellow;
    }

    .check-box-container label.generic.normal input~span.text {
        width: 5em;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .check-box-container label.generic.normal input~span.deco {
        display: -webkit-box;
        display: -ms-flexbox;
        display: -webkit-flex;
        display: flex;
        -webkit-box-orient: horizontal;
        -webkit-box-direction: normal;
        -ms-flex-direction: row;
        flex-direction: row;
        -webkit-box-align: center;
        -ms-flex-align: center;
        align-items: center;
        -webkit-box-pack: center;
        -ms-flex-pack: center;
        justify-content: center;
        width: 1em;
        height: 1em;
        min-width: 1em;
        min-height: 1em;
        margin-right: 0.2em;
        border: 1px solid rgb(103, 136, 152);
    }

    .check-box-container label.generic.normal input:checked~span.deco {
        background-color: rgb(32, 168, 216);
        color: white;
        border: none;
    }

    .check-box-container label.generic.normal input:active~span.deco,
    .check-box-container label.generic.normal input:active:checked~span.deco {
        background-color: rgb(182, 228, 244);
        border: none;
    }

    .check-box-container label.generic.normal input:focus~span.deco {
        outline: 3px solid rgb(182, 228, 244);
    }

    .check-box-container label.generic.normal input:checked~span.deco:before {
        content: "✔";
        font-size: 10px;
    }

    .sqlm {
        margin: 5px;
    }

    .target-claim {
        background-color: #ffd2d2
    }

    .page-item {
    cursor: pointer;
    }

    .btn-search-count {
    cursor: pointer;
    }

    table#claims th {
    font-weight: 400 ;
    }

    .text-right {
    text-align: right;
    }

    .text-left {
    text-align: left !important;

}
</style>
@include('components.search_hospital_group_css')
@if (!is_null($viewdata->get('list')))
<div class="card card-primary {{ $category }}">
    <div class="pt-0 card-body">
        <form class="form-horizontal" method="get" id="apply" accept-charset="utf-8">
            <!-- ガイド文追加start -->
            <div>
    @if (!empty($viewdata->get('list')->where('flg_in_charge_of_transact_header',1)->first()))
                <p style="font-weight: bold;">赤色の行について、【業者別】をクリックして、金額を確認してください。</p>
    @endif
            </div>
            <!-- ガイド文追加end -->
            <input type="hidden" data-search-item="is_search" value="1">
            <div class="row">
                <div class="col-8 tal">
                    <a class="anchor anchor-clear-search mr40" href="javascript:void(0);"><i class="fas fa-eraser mr10"></i>検索条件をクリア</a>
                </div>
            </div>

            <?php
    // 常に表示する施設グループの数
    $hospitalGroupsDefaultCount = 1;

    // 施設グループを畳んでいるかどうかをセッションから取得
    $is_condition_hospital = $viewdata->get('conditions')['is_condition_hospital'];

    // ユーザグループのグループごとにまとめて表示するためにデータ構築（1次配列からネストされた連想配列へ）
    $hospitalGroups = array();
    foreach($viewdata->get('user_groups') as $group) {
        $group_name = $group->ken_name;
        if (!array_key_exists($group_name, $hospitalGroups)) {
            $hospitalGroups[$group_name] = array();
        }
        array_push($hospitalGroups[$group_name], $group);
    }
    ?>
    @if (count($viewdata->get('user_groups')) > 1 )
            @include('components.search_hospital_group_script')
            <div class="input-item flex-row-left-top">
                <p class="flex-row-left-center">施設</p>
                <div class="flex-column-left-top">
                    <div id="selected-hospital-container" class="flex-row-left-center flex-wrap">
        @if (count($hospitalGroups) > $hospitalGroupsDefaultCount)
                    <a
                        class="mr2 anchor anchor-accordion anchor-search-condition {{ $is_condition_hospital ? 'open' : '' }} flex-row-left-center"
                        href="#collapseHospital" data-toggle="collapse"
                        aria-expanded="{{ $is_condition_hospital }}"
                        aria-controls="collapseHospital"
                        data-open-text="more"
                        data-close-text="close"
                        onClick="const val = $('#collapseHospital').hasClass('show'); $('#is_condition_hospital').val(!val);"
                    >more</a>
        @endif
    <?php
        foreach($viewdata->get('user_groups') as $group) {
            if(in_array($group->user_group_id, $viewdata->get('conditions')['user_group'])) {
    ?>
                    <div class="flex-row-left-center" onclick="onClickSelectedHospital(this)">{{ $group->user_group_name }}</div>
    <?php
            }
        }
    ?>
                    </div>
                    @include('components.search_hospital_group', ['hospitalGroups' => $hospitalGroups, 'isDefault' => true, 'hospitalGroupsDefaultCount' => $hospitalGroupsDefaultCount])
                    <input type="hidden" id="is_condition_hospital" name="is_condition_hospital" data-search-item="is_condition_hospital" value="{{ $is_condition_hospital }}">
                    <div id="collapseHospital" class="collapse @if ($is_condition_hospital) show @endif">
                        @include('components.search_hospital_group', ['hospitalGroups' => $hospitalGroups, 'isDefault' => false, 'hospitalGroupsDefaultCount' => $hospitalGroupsDefaultCount])
                    </div>
                </div>
            </div>
    @endif

            <div class="row mt10">
                <div class="col-5 d-flex">
                    <div class="d-flex mr10 align-items-center block">
                        <p class="mr10 fwb text">取引年月</p>
                        <div class="d-flex w200">
                            <input
                                class="form-control w200"
                                type="search"
                                data-view="months"
                                data-min-view="months"
                                data-date-format="yyyy/mm"
                                data-datepicker="true"
                                readonly="readonly"
                                data-search-item="claim_month_from"
                                value="{{$viewdata->get('conditions')['claim_month_from']}}"
                                placeholder=""
                            >
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <i class="fa fa-calendar"></i>
                                </span>
                            </div>
                        </div>
                        <div>　～</div>
                        <div class="d-flex w200">
                            <input
                                class="form-control w200"
                                type="search"
                                data-view="months"
                                data-min-view="months"
                                data-date-format="yyyy/mm"
                                data-datepicker="true"
                                readonly="readonly"
                                data-search-item="claim_month_to"
                                value="{{$viewdata->get('conditions')['claim_month_to']}}"
                                placeholder=""
                            >
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <i class="fa fa-calendar"></i>
                                </span>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="col-6 d-flex">
                    <div class="d-flex mr30 align-items-center block">
                        <p class="w200 mr10 fwb text">供給区分</p>
                        <select class="form-control" type="text" data-search-item="supply_division">
                            <option
                                value=""
    @if($viewdata->get('conditions')['supply_division'] == "")
                                selected
    @endif                  >
                                全て
                            </option>
    @foreach($viewdata->get('data_kbnList') as $data_kbn)
                            <option
                                value="{{$data_kbn->id}}"
        @if( $data_kbn->id == $viewdata->get('conditions')['supply_division'] )
                                selected
        @endif
                            >
                                {{ $data_kbn->supply_division_name }}
                            </option>
    @endforeach
                        </select>
                    </div>
                </div>
            </div>


            <div class="row mt10">
                <div class="col-12 tar"><button class="w200 btn btn-primary btn-table-search" type="button"><i class="fas fa-search mr10"></i>検索する</button></div>
            </div>
        </form>
        <div class="mt30 pt15 block block-table-control">
            <div class="row">
            <p class="col-12 fwb text">取引年月・施設・供給区分別 取引金額一覧</p>
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <p class="fs16 text">
    @if ($viewdata->get('page_count') > $viewdata->get('list')->total())
                        {{ $viewdata->get('list')->total() }}
    @elseif($viewdata->get('pager')->current == $viewdata->get('pager')->last)
                        {{$viewdata->get('page_count') * ($viewdata->get('pager')->current-1) + 1}}～{{$viewdata->get('list')->total()}}
    @elseif($viewdata->get('pager')->current < $viewdata->get('pager')->last && $viewdata->get('pager')->current > 0)
                        {{$viewdata->get('page_count') * ($viewdata->get('pager')->current-1) + 1}}～{{$viewdata->get('page_count') * $viewdata->get('pager')->current}}
    @else
                        0
    @endif 
                        件 / 全{{ $viewdata->get('list')->total() }}件
                    </p>
                    <div class="d-flex align-items-center block">
                        <div class="btn-group btn-group-toggle" data-toggle="buttons">
                            <label class="btn btn-default btn-search-count @if( $viewdata->get('page_count') == '20' ) active @endif" data-search-count="20">20件表示</label>
                            <label class="btn btn-default btn-search-count @if( $viewdata->get('page_count') == '50' ) active @endif" data-search-count="50">50件表示</label>
                            <label class="btn btn-default btn-search-count @if( $viewdata->get('page_count') == '100' ) active @endif" data-search-count="100">100件表示</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt10">
            <div class="col-12">
                <table class="table table-primary table-small" id="claims">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>取引年月</th>
                            <th>施設</th>
                            <th>供給区分</th>
    @if($purchase_kengen)
                            <th>仕入金額合計</th>
    @endif
    @if($sales_kengen)
                            <th>納入金額合計</th>
    @endif
                            <th>登録日時</th>
                            <th title="登録済みの業者別請求のうち、業者が金額を承認した件数です">業者承認済み件数</th>
                            <th title="登録済みの業者別請求のうち、施設が金額を承認した件数です">施設承認済み件数</th>
                        </tr>
                    </thead>
                    <tbody>
    @foreach($viewdata->get('list') as $transactHeader)
                        <tr>
                            <td class="@if ( $transactHeader->flg_in_charge_of_transact_header ) target-claim @endif">{{ $loop->iteration + ( $viewdata->get('page_count') * ($viewdata->get('pager')->current -1) ) }} <a title="業者別の内訳を確認できます" class="ml10" href="{{ $transactHeader->url }}?page={{ $viewdata->get('pager')->current }}">業者別</a> </td>
                            <td class="@if ( $transactHeader->flg_in_charge_of_transact_header ) target-claim @endif">{{ date('Y/m',  strtotime($transactHeader->claim_month)) }}</td>
                            <td class="@if ( $transactHeader->flg_in_charge_of_transact_header ) target-claim @endif">{{ $transactHeader->facility_name }}</td>
                            <td class="@if ( $transactHeader->flg_in_charge_of_transact_header ) target-claim @endif">{{ $transactHeader->supply_division_name }}</td>
        @if($purchase_kengen)
                            <td class="@if ( $transactHeader->flg_in_charge_of_transact_header ) target-claim @endif text-right">{{ number_format($transactHeader->purchase_price_total,0) }}</td>
        @endif
        @if($sales_kengen)
                            <td class="@if ( $transactHeader->flg_in_charge_of_transact_header ) target-claim @endif text-right">{{ number_format($transactHeader->sales_price_total,0) }}</td>
        @endif
                            <td class="@if ( $transactHeader->flg_in_charge_of_transact_header ) target-claim @endif">{{ $transactHeader->created_at }}</td>
                            <td title="登録済みの業者別請求のうち、業者が金額を承認した件数です" class="@if ( $transactHeader->flg_in_charge_of_transact_header ) target-claim @endif">{{ $transactHeader->trader_confirmed_count }}／{{ $transactHeader->all_confirmed_count }}</td>
                            <td title="登録済みの業者別請求のうち、施設が金額を承認した件数です" class="@if ( $transactHeader->flg_in_charge_of_transact_header ) target-claim @endif">{{ $transactHeader->facility_confirmed_count }}／{{ $transactHeader->all_confirmed_count }}</td>
                        </tr>
    @endforeach
                    </tbody>

                </table>
            </div>
        </div>
    </div>

    @if ($viewdata->get('pager')->max > 1)
    <div class="row mt15">
        <div class="col-12">
            <nav aria-label="page">
                <ul class="pagination d-fex justify-content-center">
                    <li class="page-item"><a class="page-link" data-page="1"><span aria-hidden="true">&laquo;</span></a></li>
                    <li class="page-item @if ($viewdata->get('pager')->current==1)disabled @endif">
                        <a class="page-link" data-page="{{ $viewdata->get('pager')->current - 1}}"><span aria-hidden="true">&lt;</span></a>
                    </li>
        @for ($i = $viewdata->get('pager')->first; $i <= $viewdata->get('pager')->last; $i++)
                        <li class="page-item @if($viewdata->get('pager')->current == $i) active @endif">
                            <a class="page-link" data-page="{{ $i }}">{{ $i }}</a>
                        </li>
        @endfor
                        <li class="page-item @if ($viewdata->get('pager')->current==$viewdata->get('pager')->last)disabled @endif">
                            <a class="page-link" data-page="{{ $viewdata->get('pager')->current + 1}}"><span aria-hidden="true">&gt;</span></a>
                        </li>
                        <li class="page-item"><a class="page-link" data-page="{{ $viewdata->get('pager')->last }}"><span aria-hidden="true">&raquo;</span></a></li>
                </ul>
            </nav>
        </div>
    </div>
    @endif

@endif
    @if ($admin_kengen)
    <div class="row mt15 sqlm">
        <div class="col-12 text-right">
            <!-- SQLダウンロード開発時用  -->
            <a class="w200 btn btn-primary" href="{{ route('claim.sql_download2') }}?page={{ $viewdata->get('pager')->current }}" download="請求一覧SQL.txt">
                <div class="col-12 tar"></div>SQLダウンロード
            </a>
        </div>
    </div>
    @endif
</div>
</div>

@endsection
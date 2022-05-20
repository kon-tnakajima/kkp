@extends('layouts.app')

@php($title = '請求登録履歴')

@php($category = 'claim')

@php($breadcrumbs = ['HOME'=>'/',$title=>'javascript:void(0);'])

@section('content')

<?php
$kengen          = $viewdata->get('kengen');
$purchase_kengen = $kengen['請求登録_仕入価枠表示']->privilege_value == 'true';
$sales_kengen    = $kengen['請求登録_納入価枠表示']->privilege_value == 'true';
$admin_kengen    = $kengen["一覧SQLダウンロード"]->privilege_value == "true";
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

.input-item > p {
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

label.generic {
    position: relative;
    margin: 0;
}

label.generic span.text {
    position: relative;
    cursor: pointer;
    white-space: nowrap;
    z-index: 2;
}

label.generic input ~ span.deco {
    display: block;
    font-size: inherit;
    z-index: 1;
}

label.generic.normal {
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

label.generic.normal input, label.generic.normal select {
    flex: 1;
}

.check-box-container label.generic.normal:hover,
.check-box-container label.generic.normal:hover input ~ span {
    background-color: lightyellow;
}

.check-box-container label.generic.normal input ~ span.text {
    width: 5em;
    overflow: hidden;
    text-overflow: ellipsis;
}

label.generic.normal input ~ span.deco {
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

label.generic.normal input:checked ~ span.deco {
    background-color: rgb(32, 168, 216);
    color: white;
    border: none;
}

label.generic.normal input:active ~ span.deco, label.generic.normal input:active:checked
    ~ span.deco {
    background-color: rgb(182, 228, 244);
    border: none;
}

label.generic.normal input:focus ~ span.deco {
    outline: 3px solid rgb(182, 228, 244);
}

label.generic.normal input:checked ~ span.deco:before {
    content: "✔";
    font-size: 10px;
}

.sqlm{
  margin:5px;
}

.stma{
  margin-top:0.3em;
  margin-bottom:0.3em;
  margin-left:1em;
  margin-right:0.5em;
}

.target-claim{background-color:#ffd2d2}

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
    text-align: right !important;
    }
.text-left {
    text-align: left !important;
}

</style>
@if (!is_null($viewdata->get('list')))
<div class="card card-primary {{ $category }}">
    <div class="pt-0 card-body">
        <form class="form-horizontal" method="get" id="claim" >
            @csrf
            <!-- ガイド文追加start -->
            <div class="mt10 mb10">
    @if (!empty($viewdata->get('user_guide')))
                    <p style="font-weight: bold;">赤色の行について、下記のタスクを実行してください。</p>
    @endif
    @foreach ($viewdata->get('user_guide') as $user_guide)
                    <span>{{$user_guide->user_guide_text}}<br></span>
    @endforeach
            </div>
            <!-- ガイド文追加end -->
    <input type="hidden" data-search-item="is_search" value="1">
    @if (count($viewdata->get('user_groups')) > 1 )
            <input type="hidden" data-search-item="is_regist_search" value="1">
            <div class="row mt30">
                <div class="input-item stma">
                <p class="mr10 fwb text">施設</p>
                    <div class="check-box-container">
        @foreach ($viewdata->get('user_groups') as $group)
                    <label class="generic normal">
                        <input
                            class="custom-control-input validate input-checkbox"
                            id="hospital_{{ $group->id }}"
                            name="user_group[]"
                            data-search-item="user_group[]"
                            data-search-type="checkbox"
                            value="{{ $group->id }}"
                            type="checkbox"
            @if (in_array($group->id, $viewdata->get('conditions')['user_group']))
                            checked
            @endif
                            >
                        <span class="deco"></span>
                        <span class="text" title="{{ $group->name }}">{{ $group->name }}</span>
                    </label>
        @endforeach
                    </div>
                </div>
            </div>
    @endif
            
            <div class="row mt30">
                <div class="col-5 d-flex">
                    <div class="d-flex mr10 align-items-center block">
                        <p class="mr10 fwb text">取引年月</p>
                        <div class="d-flex w200">
                            <input class="form-control w200" type="search" data-view="months" data-min-view="months" data-date-format="yyyy/mm" data-datepicker="true" readonly="readonly" data-search-item="claim_month_from" value="{{$viewdata->get('conditions')['claim_month_from']}}" placeholder="">
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <i class="fa fa-calendar"></i>
                                </span>
                            </div>
                        </div>
                        <div>　～</div>
                        <div class="d-flex w200">
                            <input class="form-control w200" type="search" data-view="months" data-min-view="months" data-date-format="yyyy/mm" data-datepicker="true" readonly="readonly" data-search-item="claim_month_to" value="{{$viewdata->get('conditions')['claim_month_to']}}" placeholder="">
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
                        <p class="w200 mr10 fwb text">ファイル種別</p>
                        <select class="form-control" type="text" data-search-item="supply_division">
                            <option value="" @if($viewdata->get('conditions')['supply_division'] == "") selected @endif>全て</option>
    @foreach($viewdata->get('data_kbnList') as $data_kbn)
                            <option
                                value="{{$data_kbn->id}}"
        @if( $data_kbn->id == $viewdata->get('conditions')['supply_division'] ) 
                                selected
        @endif
                            >
                                {{ $data_kbn->data_type_name }}
                            </option>
    @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="row mt30">
             <div class="input-item stma">
                <p class="mr10 fwb text">ステータス</p>
                <div class="check-box-container">
                    <label class="generic normal">
                        <input
                            class="custom-control-input validate input-checkbox"
                            id="status"
                            name="status"
                            data-search-item="status"
                            data-search-type="checkbox"
                            value="status"
                            type="checkbox"
    @if(!empty($viewdata->get('conditions')['status'])))
                             checked
    @endif
                        >
                        <span class="deco"></span>
                        <span class="text_status" title="status">削除を含む</span>
                    </label>
                </div>
                </div>
            </div>

            <div class="row mt10">
                <div class="col-12 tar"><button class="w200 btn btn-primary btn-table-search" type="button"><i class="fas fa-search mr10"></i>検索する</button></div>
            </div>

        </form>
        <div class="mt30 pt15 block block-table-control">
            <div class="row">
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
                            <th>添付ファイル</th>
                            <th>取引年月</th>
                            <th>施設</th>
                            <th>登録者</th>
                            <th>登録日</th>
                            <th>ファイル種別</th>
                            <th>薬価金額</th>
    @if($purchase_kengen)
                            <th>仕入金額</th>
    @endif
    @if($sales_kengen)
                            <th>納入金額</th>
    @endif
                            <th>ステータス</th>
                            <th>タスク</th>
                        </tr>
                    </thead>
                    <tbody>
    @foreach($viewdata->get('list') as $claim)
                        <tr>
                            <td class="tal @if ( $claim->flg_in_charge_of_transact_file_storage ) target-claim @endif">{{ $loop->iteration + ( $viewdata->get('page_count') * ($viewdata->get('pager')->current -1) ) }}</td>
                            <td class="@if ( $claim->flg_in_charge_of_transact_file_storage ) target-claim @endif text-left">@if(!empty($claim->file_name))<a target="_blank" href="{{ route( 'claim.download',[ 'id' => $claim->id ] ) }}"> {{ $claim->file_name }}</a>@endif</td>
                            <td class="@if ( $claim->flg_in_charge_of_transact_file_storage ) target-claim @endif">{{ date('Y/m',  strtotime($claim->claim_month)) }}</td>
                            <td class="@if ( $claim->flg_in_charge_of_transact_file_storage ) target-claim @endif text-left">{{ $claim->user_group_name }}</td>
                            <td class="@if ( $claim->flg_in_charge_of_transact_file_storage ) target-claim @endif text-left">{{ $claim->upload_user_name }}</td>
                            <td class="@if ( $claim->flg_in_charge_of_transact_file_storage ) target-claim @endif">{{ $claim->created_at }}</td>
                            <td class="@if ( $claim->flg_in_charge_of_transact_file_storage ) target-claim @endif text-left">{{ $claim->data_type_name }}</td>
                            <td class="@if ( $claim->flg_in_charge_of_transact_file_storage ) target-claim @endif text-right">{{ number_format($claim->medicine_price_total) }}</td>
        @if($purchase_kengen)
                            <td class="@if ( $claim->flg_in_charge_of_transact_file_storage ) target-claim @endif text-right">{{ number_format($claim->purchase_price_total) }}</td>
        @endif
        @if($sales_kengen)
                            <td class="@if ( $claim->flg_in_charge_of_transact_file_storage ) target-claim @endif text-right">{{ number_format($claim->sales_price_total) }}</td>
        @endif
                            <td class="@if ( $claim->flg_in_charge_of_transact_file_storage ) target-claim @endif">
                                <span class="badge badge-{{ App\Helpers\Claim\getStatusBadgeClass($claim->claim_task_status)}}">{{ $claim->status_name }}</span>
                            </td>
                            <td class="@if ( $claim->flg_in_charge_of_transact_file_storage ) target-claim @endif">
                                <input type="hidden" name="page" id="page" value="{{ $viewdata->get('pager')->current }}">
        @if (!is_null($claim->button['url']))
                                <button class="btn {{ $claim->button['style'] }} btn-sm claim" type="button" data-toggle="modal" data-target="#modal-action{{ $claim->medicine_id }}p{{ $claim->id }}">{{ $claim->button['label'] }}</button>
                                <div class="fade modal" id="modal-action{{ $claim->medicine_id }}p{{ $claim->id }}" role="dialog" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">{{$title}}</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            </div>
                                            <div class="modal-body">
                                                <p class="tac text">この請求内容を{{ $claim->button['label'] }}してよろしいでしょうか？</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
                                                <form action="{{ $claim->button['url'] }}" name="claim-action{{ $claim->medicine_id }}p{{ $claim->id }}" id="claim-action{{ $claim->medicine_id }}p{{ $claim->id }}">
                                                    <button class="btn2 {{ $claim->button['style'] }} " type="submit" data-form="#claim-action{{ $claim->medicine_id }}p{{ $claim->id }}">
                                                        <div class="fas fa-pen mr10"></div>{{ $claim->button['label'] }}
                                                    </button>
                                                    <input type="hidden" name="page" id="page" value="{{ $viewdata->get('pager')->current }}">
                                                    <input type="hidden" name="status_forward" id="status_forward" value="{{ $claim->status_forward }}">
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

        @endif
        @if (!is_null($claim->buttonReject['url']))
                                <button class="btn btn-secondary btn-sm claim" style="color:#fff;background-color:#6c757d;" type="button" data-toggle="modal" data-target="#modal-action2{{ $claim->medicine_id }}p{{ $claim->id }}">{{ $claim->buttonReject['label'] }}</button>
                                <div class="fade modal" id="modal-action2{{ $claim->medicine_id }}p{{ $claim->id }}" role="dialog" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">{{$title}}</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            </div>
                                            <div class="modal-body">
                                                <p class="tac text">この請求内容を{{ $claim->buttonReject['label'] }}してよろしいでしょうか？</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
                                                <form action="{{ $claim->buttonReject['url'] }}" name="claim-action2{{ $claim->medicine_id }}p{{ $claim->id }}" id="claim-action2{{ $claim->medicine_id }}p{{ $claim->id }}">
                                                    <button class="btn2 btn-secondary btn-sm claim " style="color:#fff;background-color:#6c757d;" type="submit" data-form="#claim-action2{{ $claim->medicine_id }}p{{ $claim->id }}">
                                                        <div class="fas fa-pen mr10"></div>{{ $claim->buttonReject['label'] }}
                                                    </button>
                                                    <input type="hidden" name="page" id="page" value="{{ $viewdata->get('pager')->current }}">
                                                    <input type="hidden" name="status_reject" id="status_reject" value="{{ $claim->status_reject }}">
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
        @endif
                            </td>
                        </tr>
    @endforeach
                    </tbody>
                </table>
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
                        <li class="page-item @if($viewdata->get('pager')->current == $i) active @endif"><a class="page-link" data-page="{{$i}}">{{$i}}</a></li>
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
    </div>
    @if ($admin_kengen)
    <div class="row mt15 sqlm">
        <div class="col-12 text-right">
        <!-- SQLダウンロード開発時用  -->
            <a
                class="w200 btn btn-primary"
                href="{{ route('claim.regist_sql_download') }}?page={{ $viewdata->get('pager')->current }}"
                download="請求登録履歴一覧SQL.txt"
            >
                <div class="col12 tar"></div>SQLダウンロード
            </a>
        </div>
   </div>
    @endif
</div>

@endif

@endsection

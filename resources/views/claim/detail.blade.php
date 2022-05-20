@extends('layouts.app')

@php($title = '明細別')

@php($category = 'claim')

@php($breadcrumbs = ['HOME'=>'/','請求照会'=>'/claim?page='.$page,'業者別'=>'/claim/trader/'.$transact_header->transact_header_id ,$title=>'javascript:void(0);'])
@section('content')
@if ($errors->any())
入力にエラーがあります<br>
@endif
@if ( !empty($errorMessage) )
<div class="alert alert-danger" role="alert">
{{ $errorMessage }}
</div>
@endif
@if ( !empty($message) )
<div class="alert alert-success" role="alert">
{{ $message }}
</div>
@endif

<?php
// $kengen = $viewdata->get('kengen');
$purchase_kengen = $kengen["請求登録_仕入価枠表示"]->privilege_value == "true";
$sales_kengen    = $kengen["請求登録_納入価枠表示"]->privilege_value == "true";
$admin_kengen    = $kengen["一覧SQLダウンロード"]->privilege_value == "true";
?>

<style type="text/css">
    table {
        background-color: #390;
        color: #fff;
        border-color: #ccc #fff #fff;
        border-bottom: #000;
        border-left: 1px solid #ccc;
    }

    td {
        background-color: #ffffff;
        color: #000;
        border-color: #ccc #fff #fff;
        border-bottom: 1px solid #ccc;
        border-left: 1px solid #ccc;
        border-right: 1px solid #ccc;
        font-size: 12px;

    }

    th {
        background-color: #390;
        color: #fff;
        border-color: #ccc #fff #fff;
        border-bottom: #000;
        border-left: 1px solid #ccc;
        text-align: center;
        /* font-size: 14px; */

    }

    table-x-scroll2 {
        overflow-x: scroll;
    }

    .card-box {
        position: relative;
        display: -webkit-inline-box;
        display: -ms-inline-flexbox;
        display: -webkit-inline-flex;
        display: inline-flex;
        -webkit-box-orient: vertical;
        -webkit-box-direction: normal;
        -ms-flex-direction: column;
        flex-direction: column;
        -webkit-box-align: stretch;
        -ms-flex-align: stretch;
        align-items: stretch;
        -webkit-box-pack: start;
        -ms-flex-pack: start;
        justify-content: flex-start;
        box-sizing: border-box;
        padding: 0 0.5em;
        border: 1px solid #390;
    }

    .card-box.nest {
        padding-bottom: 0.5em;
    }

    .card-box .btn {
        margin: 0.5em;
        margin-right: 0;
    }

    .card-box-title-1 {
        background-color: #390;
        position: absolute;
        left: -1em;
        top: -1px;
        margin: 0;
        color: white;
        padding: 4px;
        border: 1px solid white;
        transform: translateY(-50%);
    }

    .card-box.type-1 {
        margin-top: calc(0.75em + 1em);
        margin-left: 1em;
        padding-top: calc(0.75em + 0.2em);
        width: calc(100% - 1em);
    }

    .card-box.type-1:first-child {
        margin-top: 1em;
    }

    .sqlm {
        margin: 0px 5px 0px 5px;
    }

    .w150 {
        width: 150px !important;
    }

    .target-claim {
        background-color: #ffd2d2
    }

    .target-claim_facility {
        background-color: #ffd2d2
    }

    .target-claim_trader {
        background-color: #CEECF5
    }

    table#statement th {
    font-weight: 400 ;
    }
</style>

<div class="card card-primary {{ $category }}">
    <form class="form-horizontal" method="post" id="claim-edit" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="flg" id="flg" value="{{ $transact_header->flg }}">
        <input type="hidden" name="page" id="page" value="{{ $transact_header->page }}">
        <input type="hidden" name="id" id="id" value="{{ $transact_header->id }}">
        <input type="hidden" name="head_id" id="head_id" value="{{ $transact_header->transact_header_id }}">
        <div class="card-body">
            <div class="block">
                <div class="row">
                    <div class="col-12">
                    <!-- ガイド文追加start -->
@if ($transact_header->flg_in_charge_of_transact_confirmation_trader > 0)
                    <div class="mb20">
                    <p style="font-weight: bold;">下記の業者タスクを実行してください</p>
                    <p>{{ $transact_header->user_guide_text_trader }}</p>
                    </div>
@endif
                    <!-- ガイド文追加end -->
                    <div>取引年月・施設・供給区分・業者で集計した取引金額</div>
                        <table class="table table-primary table-small" id="statement">
                            <thead>
                                <tr>
                                    <th>取引<br>年月</th>
                                    <th>施設</th>
                                    <th>業者</th>
                                    <th>供給<br>区分</th>
@if($purchase_kengen)
                                    <th>仕入<br>金額合計</th>
@endif
@if($sales_kengen)
                                    <th>納入<br>金額合計</th>
@endif
@if($purchase_kengen)
                                    <th>業者<br>ステータス</th>
                                    <th>業者<br>タスク</th>
                                    <th>業者請求書</th>
                                    <th>押印済み<br>業者請求書</th>
                                    <th>業者－文化連<br>連絡事項</th>
@endif
@if($sales_kengen)
                                    <th>施設<br>ステータス</th>
                                    <!-- <th>施設コメント</th> -->
@endif
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="@if ( $transact_header->flg_in_charge_of_transact_confirmation_trader ) target-claim_trader @endif">{{ date('Y/m',  strtotime($transact_header->claim_month)) }}</td>
                                    <td class="@if ( $transact_header->flg_in_charge_of_transact_confirmation_trader ) target-claim_trader @endif">{{ $transact_header->facility_name }}</td>
                                    <td class="@if ( $transact_header->flg_in_charge_of_transact_confirmation_trader ) target-claim_trader @endif">{{ $transact_header->trader_name }}</td>
                                    <td class="@if ( $transact_header->flg_in_charge_of_transact_confirmation_trader ) target-claim_trader @endif">{{ $transact_header->supply_division_name }}</td>
@if($purchase_kengen)
                                    <td class="@if ( $transact_header->flg_in_charge_of_transact_confirmation_trader ) target-claim_trader @endif">{{ number_format($transact_header->purchase_price_total,0) }}</td>
@endif
@if($sales_kengen)
                                    <td class="@if ( $transact_header->flg_in_charge_of_transact_confirmation_trader ) target-claim_trader @endif">{{ number_format($transact_header->sales_price_total,0) }}</td>
@endif
@if($purchase_kengen)
                                    <td class="@if ( $transact_header->flg_in_charge_of_transact_confirmation_trader ) target-claim_trader @endif"><span class="badge badge-{{ App\Helpers\Claim\getStatusBadgeClass($transact_header->trader_confirmation_status)}}">{{ $transact_header->trader_confirmation_status_name }}</span></td>
                                    <td class="@if ( $transact_header->flg_in_charge_of_transact_confirmation_trader ) target-claim_trader @endif">
                                        <div class="validateGroup col-10 d-flex">
    @if ($transact_header->trader_status_remand > 0 && $transact_header->trader_status_remand != 30 && $transact_header->trader_status_remand != 40)
        @if ( !is_null($transact_header->buttonRemandTrader['url']))
                                            <button class="ml20 btn btn-secondary claim" type="button" data-toggle="modal" data-target="#modal-actionRemand-trader{{ $transact_header->id }}">{{ $transact_header->buttonRemandTrader['label'] }}</button>
        @endif
    @endif
    @if ($transact_header->trader_status_withdraw > 0 && $transact_header->trader_status_withdraw != 30 && $transact_header->trader_status_withdraw != 40)
        @if ( !is_null($transact_header->buttonWithdrawTrader['url']))
                                            <button class="ml40 btn {{$transact_header->buttonWithdrawTrader['style']}} claim" type="button" data-toggle="modal" data-target="#modal-actionWithdraw-trader{{ $transact_header->id }}">{{ $transact_header->buttonWithdrawTrader['label'] }}</button>
        @endif
    @endif
    @if ($transact_header->trader_status_foward > 0 && $transact_header->trader_status_foward != 40 && $transact_header->trader_status_foward != 50)
        @if ( !is_null($transact_header->buttonTrader['url']))
                                            <button class="ml40 btn {{$transact_header->buttonTrader['style']}} claim" type="button" data-toggle="modal" data-target="#modal-action-trader{{ $transact_header->id }}">{{ $transact_header->buttonTrader['label'] }}</button>
        @endif
    @endif
                                        </div>
                                    </td>
                                    <td class="@if ( $transact_header->flg_in_charge_of_transact_confirmation_trader ) target-claim_trader @endif">
                                        <!-- 業者請求書 -->
    @if( $transact_header->trader_status_remand == 30)
                                        <button class="btn btn-primary delete" type="button" data-toggle="modal" data-target="#modal-action-send-delete">
                                            <div class="fas"></div>削除
                                        </button>
                                        <input type="hidden" name="delete_control" id="delete_control" value="1">
                                        <input type="hidden" name="claim_control" id="claim_control" value="2">
                                        <input type="hidden" name="trader_status_remand" id="trader_status_remand" value="{{ $transact_header->trader_status_remand }}">
    @elseif($transact_header->trader_status_withdraw == 30)
                                        <button class="btn btn-primary delete" type="button" data-toggle="modal" data-target="#modal-action-send-delete">
                                            <div class="fas"></div>削除
                                        </button>
                                        <input type="hidden" name="delete_control" id="delete_control" value="2">
                                        <input type="hidden" name="claim_control" id="claim_control" value="2">
                                        <input type="hidden" name="trader_status_withdraw" id="trader_status_withdraw" value="{{ $transact_header->trader_status_withdraw }}">
    @endif
    @if($transact_header->trader_status_foward == 40)
                                        <div class="d-flex align-items-center block">
                                            <div class="error-tip">
                                                <div class="error-tip-inner">{{ $errors->first('file') }}</div>
                                            </div>
                                            <input class="form-control w250 h40" type="file" data-validate="empty" id="send_file" name="send_file">
                                            <button class="btn btn-primary upload" type="button" data-toggle="modal" data-target="#modal-action-send-upload">
                                                <div class="fas"></div>登録
                                            </button>
                                        </div>
                                        <input type="hidden" name="claim_control" id="claim_control" value="2">
                                        <input type="hidden" name="trader_status_foward" id="trader_status_foward" value="{{ $transact_header->trader_status_foward }}">
    @endif
                                        <a target="_blank" href="{{ route( 'claim.send_download',[ 'id' => $transact_header->id ] ) }}"> {{ $transact_header->attachment_send_file_name }}</a>
                                    </td>
                                    <td class="@if ( $transact_header->flg_in_charge_of_transact_confirmation_trader ) target-claim_trader @endif">
                                        <!-- 押印済み業者請求書 -->
    @if( $transact_header->trader_status_remand == 40)
                                        <button class="btn btn-primary delete" type="button" data-toggle="modal" data-target="#modal-action-receive-delete">
                                            <div class="fas"></div>削除
                                        </button>
                                        <input type="hidden" name="delete_control" id="delete_control" value="1">
                                        <input type="hidden" name="claim_control" id="claim_control" value="2">
                                        <input type="hidden" name="trader_status_remand" id="trader_status_remand" value="{{ $transact_header->trader_status_remand }}">
    @elseif($transact_header->trader_status_withdraw == 40)
                                        <button class="btn btn-primary delete" type="button" data-toggle="modal" data-target="#modal-action-receive-delete">
                                            <div class="fas"></div>削除
                                        </button>
                                        <input type="hidden" name="delete_control" id="delete_control" value="2">
                                        <input type="hidden" name="claim_control" id="claim_control" value="2">
                                        <input type="hidden" name="trader_status_withdraw" id="trader_status_withdraw" value="{{ $transact_header->trader_status_withdraw }}">
    @endif
    @if($transact_header->trader_status_foward == 50)
                                        <div class="d-flex align-items-center block">
                                            <div class="error-tip">
                                                <div class="error-tip-inner">{{ $errors->first('file') }}</div>
                                            </div>
                                            <input class="form-control w250 h40" type="file" data-validate="empty" id="receive_file" name="receive_file">
                                            <button class="btn btn-primary upload" type="button" data-toggle="modal" data-target="#modal-action-receive-upload">
                                                <div class="fas"></div>登録
                                            </button>
                                        </div>
                                        <input type="hidden" name="claim_control" id="claim_control" value="2">
                                        <input type="hidden" name="trader_status_foward" id="trader_status_foward" value="{{ $transact_header->trader_status_foward }}">
    @endif
                                        <a target="_blank" href="{{ route( 'claim.receive_download',[ 'id' => $transact_header->id ] ) }}"> {{ $transact_header->attachment_receive_file_name }}</a>
                                    </td>
                                    <td class="@if ( $transact_header->flg_in_charge_of_transact_confirmation_trader ) target-claim_trader @endif"><a class="comment" href="javascript:void(0);" data-tc-id="{{ $transact_header->id }}" data-toggle="modal" data-target="#modal-comment">未読{{ $transact_header->unread_comments_count }}件/既読{{ $transact_header->read_comments_count }}件</a></td>
@endif
@if($sales_kengen)
                                    <td class="@if ( $transact_header->flg_in_charge_of_transact_confirmation_trader ) target-claim_trader @endif"><span class="badge badge-{{ App\Helpers\Claim\getStatusBadgeClass($transact_header->facility_confirmation_status)}}">{{ $transact_header->facility_confirmation_status_name }}</span></td>
@endif

                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

@if(!is_array($list) && count($list) > 0)
            <div class="mt30 pt15 block block-table-control">
                <div class="row">
                    <div class="col-12 d-flex justify-content-between align-items-center">
                        <p class="fs16 text">
    @if ($page_count > $list->total())
                            {{ $list->total() }}
    @elseif($pager->current == $pager->last)
                            {{$page_count * ($pager->current-1) + 1}}～{{$list->total()}}
    @elseif($pager->current < $pager->last && $pager->current > 0)
                            {{$page_count * ($pager->current-1) + 1}}～{{$page_count * $pager->current}}
    @else
                            0
    @endif 
                        件 / 全{{ $list->total() }}件
                    </p>
                        <div class="d-flex align-items-center block">
                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                <label class="btn btn-default btn-search-count @if( $page_count == '20' ) active @endif" data-search-count="20">20件表示</label>
                                <label class="btn btn-default btn-search-count @if( $page_count == '50' ) active @endif" data-search-count="50">50件表示</label>
                                <label class="btn btn-default btn-search-count @if( $page_count == '100' ) active @endif" data-search-count="100">100件表示</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt10">
                    <div class="ml15 table-x-scroll2">
                        <table id="statement">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th width="110">取引年月日</th>
                                    <th width="110">業者</th>
                                    <th width="180">販売メーカー</th>
                                    <th width="350">商品名</th>
                                    <th width="350">規格</th>
                                    <th width="110">JANCD</th -->
                                    <th width="50">数量</th>
    @if($purchase_kengen)
                                    <th width="110">仕入単価</th>
                                    <th width="110">仕入金額</th>
    @endif
    @if($sales_kengen)
                                    <th width="110">納入単価</th>
                                    <th width="110">納入金額</th>
    @endif
                                </tr>
                            </thead>
                            <tbody>
    @foreach($list as $transactDetail)
                                <tr>
                                    <td style="text-align: center;">{{ $loop->iteration + ( $page_count * ($pager->current -1) ) }}</td>
                                    <td>{{ $transactDetail->claim_date }}</td>
                                    <td>{{ $transactDetail->trader_name }}</td>
                                    <td>{{ $transactDetail->maker_name }}</td>
                                    <td>{{ $transactDetail->item_name }}</td>
                                    <td>{{ $transactDetail->standard }}</td>
                                    <td>{{ $transactDetail->jan_code }}</td>
                                    <td style="text-align: right;">{{ $transactDetail->quantity }}</td>
        @if($purchase_kengen)
                                    <td style="text-align: right;">{{ number_format($transactDetail->buy_unit_price) }}</td>
                                    <td style="text-align: right;">{{ number_format($transactDetail->buy_price) }}</td>
        @endif
        @if($sales_kengen)
                                    <td style="text-align: right;">{{ number_format($transactDetail->sales_unit_price) }}</td>
                                    <td style="text-align: right;">{{ number_format($transactDetail->sales_price) }}</td>
        @endif
                                </tr>
    @endforeach
                            </tbody>
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th class="no">取引年月日</th>
                                    <th>業者</th>
                                    <th>販売メーカー</th>
                                    <th>商品名</th>
                                    <th>規格</th>
                                    <th>JANCD</th>
                                    <th>数量</th>
    @if($purchase_kengen)
                                    <th>仕入単価</th>
                                    <th>仕入金額</th>
    @endif
    @if($sales_kengen)
                                    <th>納入単価</th>
                                    <th>納入金額</th>
    @endif
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
    @if ($pager->max > 1)
                <div class="row mt15">
                    <div class="col-12">
                        <nav aria-label="page">
                            <ul class="pagination d-fex justify-content-center">
                                <li class="page-item"><a class="page-link" data-page="1"><span aria-hidden="true">&laquo;</span></a></li>
                                <li class="page-item @if ($pager->current==1)disabled @endif">
                                    <a class="page-link" data-page="{{ $pager->current - 1}}"><span aria-hidden="true">&lt;</span></a>
                                </li>
        @for ($i = $pager->first; $i <= $pager->last; $i++)
                                    <li class="page-item @if($pager->current == $i) active @endif"><a class="page-link" data-page="{{$i}}">{{$i}}</a></li>
        @endfor
                                    <li class="page-item @if ($pager->current==$pager->last)disabled @endif">
                                        <a class="page-link" data-page="{{ $pager->current + 1}}"><span aria-hidden="true">&gt;</span></a>
                                    </li>
                                    <li class="page-item"><a class="page-link" data-page="{{ $pager->last }}"><span aria-hidden="true">&raquo;</span></a></li>
                            </ul>
                        </nav>
                    </div>
                </div>
    @endif
            </div>
@endif

        </div>
        <div class="card-footer d-flex justify-content-around pt15 pb15 mt15">

            <a class="btn btn-default btn-lg" href="{{ route('claim.trader', ['id '=> $transact_header_id]) }}"><i class="fas fa-chevron-left mr10"></i>前の画面へ戻る</a>
            <div class="block">
@if (!empty($transact_header->edit))
                <button class="mr30 btn btn-primary btn-lg" type="button" data-toggle="modal" data-target="#modal-action">
                    <div class="fas fa-pen mr10"></div>更新する
                </button>
@endif
                <a class="w200 btn btn-primary" href="{{ route('claim.download_detail') }}?page={{ $pager->current }}&id={{ $transact_header->id }}" download="請求明細_{{ date('Ymd') }}.csv">
                    <div class="fas fa-pen mr10"></div>CSVダウンロード
                </a>
                <!-- SQLダウンロード開発時用  -->
                @if ($admin_kengen)
                <a class="w200 btn btn-primary sqlm" href="{{ route('claim.sql_download5', $transact_header->id ) }}?page={{ $pager->current }}" download="明細別ヘッダーSQL.txt">
                    <div class="col-12 tar"></div>ヘッダーSQLダウンロード
                </a>
                <a class="w200 btn btn-primary sqlm" href="{{ route('claim.sql_download6', $transact_header->id ) }}?page={{ $pager->current }}" download="明細別明細SQL.txt">
                    <div class="col-12 tar"></div>明細SQLダウンロード
                </a>
                @endif
            </div>
        </div>
        <div class="fade modal" id="modal-action" role="dialog" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">請求登録</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <p class="tac text">この請求内容を更新してよろしいでしょうか？</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
                        <button class="btn2 btn-primary" type="submit" id="edit_btn" name="{{route('claim.edit', ['id '=> $transact_header->id])}}">
                            <div class="fas fa-pen mr10"></div>更新する
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- ファイルアップロード  -->
        <div class="fade modal" id="modal-action-send-upload" role="dialog" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">請求登録</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <p class="tac text">このファイルを登録してよろしいでしょうか？</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
                        <button class="btn2 btn-primary" type="submit" id="send_upload_btn" name="{{route('claim.send_upload')}}">
                            <div class="fas fa-pen mr10"></div>登録する
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="fade modal" id="modal-action-receive-upload" role="dialog" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">請求登録</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <p class="tac text">このファイルを登録してよろしいでしょうか？</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
                        <button class="btn2 btn-primary" type="submit" id="receive_upload_btn" name="{{route('claim.receive_upload')}}">
                            <div class="fas fa-pen mr10"></div>登録する
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- ファイル削除  -->
        <div class="fade modal" id="modal-action-send-delete" role="dialog" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">請求登録</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <p class="tac text">このファイルを削除してよろしいでしょうか？</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
                        <button class="btn2 btn-primary" type="submit" id="send_delete_btn" name="{{route('claim.send_delete')}}">
                            <div class="fas fa-pen mr10"></div>削除する
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="fade modal" id="modal-action-receive-delete" role="dialog" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">請求登録</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <p class="tac text">このファイルを削除してよろしいでしょうか？</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
                        <button class="btn2 btn-primary" type="submit" id="receive_delete_btn" name="{{route('claim.receive_delete')}}">
                            <div class="fas fa-pen mr10"></div>削除する
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

@if (!is_null($transact_header->buttonTrader['url']))
    <div class="fade modal" id="modal-action-trader{{ $transact_header->id }}" role="dialog" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">請求登録</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <p class="tac text">この請求内容を{{ $transact_header->buttonTrader['label'] }}してよろしいでしょうか？</p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
                    <form action="{{ $transact_header->buttonTrader['url'] }}" name="claim-action-trader{{ $transact_header->id }}" id="claim-action-trader{{ $transact_header->id }}">
                        <button class="btn2 {{$transact_header->buttonTrader['style']}}" type="submit" id="claim_action_btn" name="claim-action-trader{{ $transact_header->id }}">
                            <div class="fas fa-pen mr10"></div>{{ $transact_header->buttonTrader['label'] }}
                        </button>
                        <input type="hidden" name="id" id="id" value="{{ $transact_header->id }}">
                        <input type="hidden" name="head_id" id="head_id" value="{{ $transact_header_id }}">
                        <input type="hidden" name="claim_control" id="claim_control" value="2">
                        <input type="hidden" name="trader_status_foward" id="trader_status_foward" value="{{ $transact_header->trader_status_foward }}">
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif
@if (!is_null($transact_header->buttonWithdrawTrader['url']))
    <div class="fade modal" id="modal-actionWithdraw-trader{{ $transact_header->id }}" role="dialog" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">請求登録</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <p class="tac text">この請求内容を{{ $transact_header->buttonWithdrawTrader['label'] }}してよろしいでしょうか？</p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
                    <form action="{{ $transact_header->buttonWithdrawTrader['url'] }}" name="claim-actionWithdraw-trader{{ $transact_header->id }}" id="claim-actionWithdraw-trader{{ $transact_header->id }}">
                        <button class="btn2 {{$transact_header->buttonWithdrawTrader['style']}}" type="submit" id="claim_withdraw_btn" name="claim-actionWithdraw-trader{{ $transact_header->id }}">
                            <div class="fas fa-pen mr10"></div>{{ $transact_header->buttonWithdrawTrader['label'] }}
                        </button>
                        <input type="hidden" name="id" id="id" value="{{ $transact_header->id }}">
                        <input type="hidden" name="head_id" id="head_id" value="{{ $transact_header_id }}">
                        <input type="hidden" name="claim_control" id="claim_control" value="2">
                        <input type="hidden" name="trader_status_withdraw" id="trader_status_withdraw" value="{{ $transact_header->trader_status_withdraw }}">
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif

@if (!is_null($transact_header->buttonRemandTrader['url']))
    <div class="fade modal" id="modal-actionRemand-trader{{ $transact_header->id }}" role="dialog" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">請求登録</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <p class="tac text">この請求内容を{{ $transact_header->buttonRemandTrader['label'] }}してよろしいでしょうか？</p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
                    <form action="{{ $transact_header->buttonRemandTrader['url'] }}" name="claim-action-remand-trader{{ $transact_header->id }}" id="claim-action-remand-trader{{ $transact_header->id }}">
                        <!--
                        <button class="btn-primary" type="submit" data-form-method="get" data-form="#claim-action-remand-trader{{ $transact_header->id }}">
                        -->
                        <button class="btn-primary" type="submit" id="claim_remand_btn" name="claim-action-remand-trader{{ $transact_header->id }}">
                            <div class="fas fa-pen mr10"></div>{{ $transact_header->buttonRemandTrader['label'] }}
                        </button>
                        <!--
                        <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
                        <script>
                            jQuery(function()
                            {
                                if($('#comment').length){
                                    $('#comment').change(function() {
                                        $('#comment2').val($(this).val());
                                    });
                                }
                            })
                        </script>
                        <input type="hidden" name="comment2" id="comment2" value="">
                        <input class="form-control validate" type="hidden" name="page" id="page" value="">
                        -->
                        <input type="hidden" name="head_id" id="head_id" value="{{ $transact_header_id }}">
                        <input type="hidden" name="claim_control" id="claim_control" value="2">
                        <input type="hidden" name="trader_status_remand" id="trader_status_remand" value="{{ $transact_header->trader_status_remand }}">
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif
</div>
<div class="fade modal" id="modal-comment" role="dialog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">コメント</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="tac text mb20">
                    <button id="reload" name="reload" class="btn btn-primary col-4" type="button">
                        <i class="fas fa-redo mr10"></i>更新する
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <form class="w100p tac" action="{{ route('claim.comment.regist') }}" name="claim-comment" id="claim-comment">
                    @csrf
                    <textarea class="col-7 mr10" name="comment" id="comment" rows="2"></textarea>
                    <input type="hidden" name="facility_id" id="facility_id" value="{{ Auth::user()->primary_user_group_id }}">
                    <input type="hidden" name="user_id" id="user_id" value="{{ Auth::user()->id }}">
                    <input type="hidden" name="tc_id" id="tc_id" value="">
                    <button class="btn-primary col-4 btn_comment" type="submit">
                        <div class="fas fa-pen mr10"></div>コメントする
                    </button>
                </form>
            </div>
            <div id="loading"><img src="/static/image/spinner.svg" width="100" height="100"></div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
<script>
    $("#send_upload_btn").click(function() {
        //alert('test');
        var url = $('#send_upload_btn').attr('name');
        //alert(url);
        $('#claim-edit').attr('action', url);
        $('#claim-edit').submit();

    });
    $("#send_delete_btn").click(function() {
        //alert('test');
        var url = $('#send_delete_btn').attr('name');
        //alert(url);
        $('#claim-edit').attr('action', url);
        $('#claim-edit').submit();
    });
    $("#receive_upload_btn").click(function() {
        //alert('test');
        var url = $('#receive_upload_btn').attr('name');
        //alert(url);
        $('#claim-edit').attr('action', url);
        $('#claim-edit').submit();
    });
    $("#receive_delete_btn").click(function() {
        //alert('test');
        var url = $('#receive_delete_btn').attr('name');
        //alert(url);
        $('#claim-edit').attr('action', url);
        $('#claim-edit').submit();
    });
    $("#edit_btn").click(function() {
        //alert('test');
        var url = $('#edit_btn').attr('name');
        //alert(url);
        $('#claim-edit').attr('action', url);
        $('#claim-edit').submit();
    });
    $("#claim_action_btn").click(function() {
        //alert('test');
        var id = $('#claim_action_btn').attr('name');
        $('#' + id).submit();
    });
    $("#claim_remand_btn").click(function() {
        //alert('test');
        var id = $('#claim_remand_btn').attr('name');
        $('#' + id).submit();
    });
    $("#claim_withdraw_btn").click(function() {
        var id = $('#claim_withdraw_btn').attr('name');
        $('#' + id).submit();
    });
</script>
@endsection
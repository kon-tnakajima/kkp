@extends('layouts.app')

@php($title = '業者別')

@php($category = 'claim')

@php($breadcrumbs = ['HOME'=>'/','請求照会'=>'/claim?page='.$page, $title=>'javascript:void(0);'])

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

@if ( session('errorMessageKai'))
<div class="alert alert-danger">
    {{ session('errorMessageKai') }}
</div>
@endif

<?php
// $kengen = $viewdata->get('kengen');
$purchase_kengen = $kengen["請求登録_仕入価枠表示"]->privilege_value == "true";
$sales_kengen    = $kengen["請求登録_納入価枠表示"]->privilege_value == "true";
$admin_kengen    = $kengen["一覧SQLダウンロード"]->privilege_value == "true";
?>


<style type="text/css">
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

.text-right {
    text-align: right !important;
    }

.text-left {
    text-align: left !important;
}
</style>

<div class="card card-primary {{ $category }}">
@if ( !empty($detail->flg))
    <input type="hidden" name="flg" id="flg" value="{{ $detail->flg }}">
@endif

@if ( !is_null($detail))
    <div class="card-body">
        <div class="block">
            <div class="row">
                <div class="col-12">
                    <!-- ガイド文追加start -->
                    <div class="mb10">
    @if (!empty($user_guide))
                        <p style="font-weight: bold;">赤色の行について、下記の施設タスクを実行してください。</p>
        @foreach($user_guide as $row)
                        <span>{{$row->user_guide_text}}<br></span>
        @endforeach
    @endif
                    </div>
                    <div class="mb20">
    @if (!empty($list->where('flg_in_charge_of_transact_confirmation_trader',1)->first()))
                    <p style="font-weight: bold;">青色の行について、【明細別】をクリックして、業者タスクを実行してください。</p>
    @endif
                    </div>

                    <div>取引年月・施設・供給区分で集計した取引金額</div>
                    <table class="table table-primary table-small" id="statement">
                        <thead>
                            <tr>
                                <!-- <th>ID</th> -->
                                <th>取引年月</th>
                                <th>施設</th>
                                <th>供給区分</th>
    @if($purchase_kengen)
                                <th>仕入金額合計</th>
    @endif
    @if($sales_kengen)
                                <th>納入金額合計</th>
                                <th>施設請求書</th>
                                <!-- <th>押印済み施設請求書</th> -->
    @endif
                            </tr>
                        </thead>
                        <tbody>
                            <form class="form-horizontal" method="post" id="upDown" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="id" id="id" value="{{ $detail->id }}">
                                <input type="hidden" name="head_id" id="head_id" value="{{ $detail->id }}">
                                <tr>
                                    <!-- <td>{{ $detail->id }}</td> -->
                                    <td>{{ date('Y/m',  strtotime($detail->claim_month)) }}</td>
                                    <td>{{ $detail->facility_name }}</td>
                                    <td>{{ $detail->supply_division_name }}</td>
    @if($purchase_kengen)
                                    <td>{{ number_format($detail->purchase_price_total,0) }}</td>
    @endif
    @if($sales_kengen)
                                    <td>{{ number_format($detail->sales_price_total,0) }}</td>
                                    <td>
                                        <!-- 施設請求書 -->
        @if($detail->del_facility_invoice && !empty($detail->attachment_send_file_name))
                                        <!--button class="btn btn-primary delete" type="submit" id="send_delete_btn" name="{{route('claim.sendh_delete')}}" -->
                                        <button class="btn btn-primary delete" type="button" data-toggle="modal" data-target="#modal-action-send-delete">
                                            <div class="fas"></div>削除
                                        </button>
        @endif
        @if($detail->up_facility_invoice && empty($detail->attachment_send_file_name))
                                        <div class="d-flex align-items-center block">
                                            <div class="error-tip">
                                                <div class="error-tip-inner">{{ $errors->first('file') }}</div>
                                            </div>
                                            <input class="form-control w300 h40" type="file" data-validate="empty" id="send_file" name="send_file">
                                            <!-- button class="btn btn-primary upload" type="submit" id="send_upload_btn" name="{{route('claim.sendh_upload')}}" -->
                                            <button class="btn btn-primary upload" type="button" data-toggle="modal" data-target="#modal-action-send-upload">
                                                <div class="fas"></div>登録
                                            </button>
                                        </div>
        @endif
                                        <a target="_blank" href="{{ route( 'claim.sendh_download',[ 'id' => $detail->id ] ) }}"> {{ $detail->attachment_send_file_name }}</a>
                                    </td>

                                    <!-- 押印済み施設請求書 -->
                                    {{--<!-- <td>
        @if($detail->del_facility_confirmed_invoice && !empty($detail->attachment_receive_file_name))
                                        <button class="btn btn-primary delete" type="button" data-toggle="modal" data-target="#modal-action-receive-delete">
                                            <div class="fas"></div>削除
                                        </button>
        @endif
        @if($detail->up_facility_confirmed_invoice && empty($detail->attachment_receive_file_name))
                                        <div class="d-flex align-items-center block">
                                            <div class="error-tip">
                                                <div class="error-tip-inner">{{ $errors->first('file') }}</div>
                                            </div>
                                            <input class="form-control w300 h40" type="file" data-validate="empty" id="receive_file" name="receive_file">
                                            <button class="btn btn-primary upload" type="button" data-toggle="modal" data-target="#modal-action-receive-upload">
                                                <div class="fas"></div>登録
                                            </button>
                                        </div>
        @endif
                                        <a target="_blank" href="{{ route( 'claim.receiveh_download',[ 'id' => $detail->id ] ) }}"> {{ $detail->attachment_receive_file_name }}</a>
                                    </td> -->--}}
    @endif
                                </tr>
                            </form>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
@endif
@if( !is_array($list) && count($list) > 0 )
        <div class="mt30 pt15 block block-table-control">
            <div class="row">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <p class="fs16 text">業者別内訳　 全{{ $list->total() }}件</p>
                    <!--p class="fs16 text">@if ( $page_count > $list->total() ){{ $list->total() }} @else @if($pager->current == $pager->last){{ ($list->total() - ($page_count * ($pager->current - 1) )) }} @else{{ $page_count }}@endif @endif件 / 全{{ $list->total() }}件</p-->
                    <!--div class="d-flex align-items-center block">
                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                <label class="btn btn-default btn-search-count @if( $page_count == '20' ) active @endif" data-search-count="20">20件表示</label>
                                <label class="btn btn-default btn-search-count @if( $page_count == '50' ) active @endif" data-search-count="50">50件表示</label>
                                <label class="btn btn-default btn-search-count @if( $page_count == '100' ) active @endif" data-search-count="100">100件表示</label>
                            </div>
                        </div-->
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <table class="table table-primary table-small" id="statement">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>業者</th>
    @if($purchase_kengen)
                                <th>仕入金額合計</th>
    @endif
    @if($sales_kengen)
                                <th>納入金額合計</th>
    @endif
    @if($purchase_kengen)
                                <th>業者<br>ステータス</th>
                                <!--  th>業者タスク</th -->
                                <th>業者請求書</th>
                                <th>押印済み<br>業者請求書</th>
                                <!-- <th>業者コメント</th> -->
                                <th>業者－文化連<br>連絡事項</th>
    @endif
    @if($sales_kengen)
                                <th>施設<br>ステータス</th>
                                <th>施設<br>タスク</th>
                                <!-- <th>施設コメント</th> -->
    @endif
                            </tr>
                        </thead>
                        <tbody>
    @foreach($list as $transactConfirmation)
                            <tr class = "@if ($transactConfirmation->flg_in_charge_of_transact_confirmation_facility) target-claim_facility
                                         @elseif ($transactConfirmation->flg_in_charge_of_transact_confirmation_trader) target-claim_trader
                                         @endif">
                                <td>{{ $loop->iteration }} @if (!is_null($transactConfirmation->detail_count) && $transactConfirmation->detail_count > 0)<a title="業者の実績明細を確認できます" class="ml10" href="{{ $transactConfirmation->url }}?page={{ $page }}">明細別</a> @endif</td>
                                <td>{{ $transactConfirmation->trader_name }}</td>
        @if($purchase_kengen)
                                <td class="text-right">{{ number_format($transactConfirmation->purchase_price_total, 0) }}</td>
        @endif

        @if($sales_kengen)
                                <td class="text-right">{{ number_format($transactConfirmation->sales_price_total, 0) }}</td>
        @endif

        @if($purchase_kengen)
                                <td><span class="badge badge-{{ App\Helpers\Claim\getStatusBadgeClass($transactConfirmation->trader_confirmation_status)}}">{{ $transactConfirmation->trader_confirmation_status_name }}</span></td>
                                <td class="text-left">@if(!empty($transactConfirmation->attachment_send_file_name))<a target="_blank" href="{{ route( 'claim.send_download',[ 'id' => $transactConfirmation->id ] ) }}"> {{ $transactConfirmation->attachment_send_file_name }}</a>@endif</td>
                                <td class="text-left">@if(!empty($transactConfirmation->attachment_receive_file_name))<a target="_blank" href="{{ route( 'claim.receive_download',[ 'id' => $transactConfirmation->id ] ) }}"> {{ $transactConfirmation->attachment_receive_file_name }}</a>@endif</td>
                                <!-- <td></td> -->
                                <td><a class="comment" href="javascript:void(0);" data-tc-id="{{ $transactConfirmation->id }}" data-toggle="modal" data-target="#modal-comment">未読{{ $transactConfirmation->unread_comments_count }}件/既読{{ $transactConfirmation->read_comments_count }}件</a></td>
        @endif

        @if($sales_kengen)
                                <td><span class="badge badge-{{ App\Helpers\Claim\getStatusBadgeClass($transactConfirmation->facility_confirmation_status)}}">{{ $transactConfirmation->facility_confirmation_status_name }}</span></td>
                                <td>
            @if (!is_null($transactConfirmation->taskButtonFacility['url']))
                                    <button class="btn {{ $transactConfirmation->taskButtonFacility['style'] }} btn-sm claim" type="button" data-toggle="modal" data-target="#modal-action{{ $transactConfirmation->id }}head_id{{ $transactConfirmation->transact_header_id }}">{{ $transactConfirmation->taskButtonFacility['label'] }}</button>
                                    <div class="fade modal" id="modal-action{{ $transactConfirmation->id }}head_id{{ $transactConfirmation->transact_header_id }}" role="dialog" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">施設</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p class="tac text">この内容を{{ $transactConfirmation->taskButtonFacility['label'] }}してよろしいでしょうか？</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
                                                    <form method="get" action="{{ $transactConfirmation->taskButtonFacility['url'] }}" name="claim-action{{ $transactConfirmation->id }}head_id{{ $transactConfirmation->transact_header_id }}" id="claim-action{{ $transactConfirmation->id }}head_id{{ $transactConfirmation->transact_header_id }}">
                                                        <!--
                                                            <button class="btn2 {{ $transactConfirmation->taskButtonFacility['style'] }} " type="submit" data-form="claim-action{{ $transactConfirmation->id }}head_id{{ $transactConfirmation->transact_header_id }}">
                                                            -->
                                                        <button class="btn2 {{ $transactConfirmation->taskButtonFacility['style'] }} " type="submit" id="claim_action_btn" onClick="claimAction('claim-action{{ $transactConfirmation->id }}head_id{{ $transactConfirmation->transact_header_id }}')">
                                                            <div class="fas fa-pen mr10"></div>{{ $transactConfirmation->taskButtonFacility['label'] }}
                                                        </button>
                                                        <input type="hidden" name="head_id" id="head_id" value="{{ $transactConfirmation->transact_header_id }}">
                                                        <input type="hidden" name="id" id="id" value="{{ $transactConfirmation->id }}">
                                                        <input type="hidden" name="claim_control" id="claim_control" value="1">
                                                        <input type="hidden" name="isDetail" id="isDetail" value="1">
                                                        <input type="hidden" name="facility_status_foward" id="facility_status_foward" value="{{ $transactConfirmation->facility_status_foward }}">
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

            @endif
            @if (!is_null($transactConfirmation->buttonWithdrawFacility['url']))
                                    <button class="btn {{ $transactConfirmation->buttonWithdrawFacility['style'] }} btn-sm claim" type="button" data-toggle="modal" data-target="#modal-action-withdraw{{ $transactConfirmation->id }}head_id{{ $transactConfirmation->transact_header_id }}">{{ $transactConfirmation->buttonWithdrawFacility['label'] }}</button>
                                    <div class="fade modal" id="modal-action-withdraw{{ $transactConfirmation->id }}head_id{{ $transactConfirmation->transact_header_id }}" role="dialog" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">施設</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p class="tac text">この内容を{{ $transactConfirmation->buttonWithdrawFacility['label'] }}してよろしいでしょうか？</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
                                                    <form method="get" action="{{ $transactConfirmation->buttonWithdrawFacility['url'] }}" name="claim-action-withdraw{{ $transactConfirmation->id }}head_id{{ $transactConfirmation->transact_header_id }}" id="claim-action-withdraw{{ $transactConfirmation->id }}head_id{{ $transactConfirmation->transact_header_id }}">
                                                        <!--
                                                            <button class="btn2 {{ $transactConfirmation->taskButtonFacility['style'] }} " type="submit" data-form="claim-action{{ $transactConfirmation->id }}head_id{{ $transactConfirmation->transact_header_id }}">
                                                            -->
                                                        <button class="btn2 {{ $transactConfirmation->buttonWithdrawFacility['style'] }} " type="submit" id="claim_withdraw_btn" onClick="claimAction('claim-action-withdraw{{ $transactConfirmation->id }}head_id{{ $transactConfirmation->transact_header_id }}')">
                                                            <div class="fas fa-pen mr10"></div>{{ $transactConfirmation->buttonWithdrawFacility['label'] }}
                                                        </button>
                                                        <input type="hidden" name="head_id" id="head_id" value="{{ $transactConfirmation->transact_header_id }}">
                                                        <input type="hidden" name="id" id="id" value="{{ $transactConfirmation->id }}">
                                                        <input type="hidden" name="claim_control" id="claim_control" value="1">
                                                        <input type="hidden" name="isDetail" id="isDetail" value="1">
                                                        <input type="hidden" name="facility_status_withdraw" id="facility_status_withdraw" value="{{ $transactConfirmation->facility_status_withdraw }}">
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
            @endif
            @if (!is_null($transactConfirmation->buttonRemandFacility['url']))
                                    <button class="btn btn-secondary btn-sm claim" style="color:#fff;background-color:#6c757d;" type="button" data-toggle="modal" data-target="#modal-action2{{ $transactConfirmation->id }}head_id{{ $transactConfirmation->transact_header_id }}">{{ $transactConfirmation->buttonRemandFacility['label'] }}</button>
                                    <div class="fade modal" id="modal-action2{{$transactConfirmation->id }}head_id{{ $transactConfirmation->transact_header_id }}" role="dialog" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">施設</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <form action="{{ $transactConfirmation->buttonRemandFacility['url'] }}" name="claim-action2{{$transactConfirmation->id }}head_id{{ $transactConfirmation->transact_header_id }}" id="claim-action2{{$transactConfirmation->id }}head_id{{ $transactConfirmation->transact_header_id }}">
                                                    <div class="modal-body">
                                                        <p class="tac text">この請求内容を{{ $transactConfirmation->buttonRemandFacility['label'] }}してよろしいでしょうか？</p>

                @if ($transactConfirmation->buttonRemandFacility['comment'] =="1" )
                                                        <br><b>差戻しコメント</b>
                                                        <textarea rows="2" cols="50" id="reject_comment" name="reject_comment"></textarea>
                @endif
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
                                                        <!--
                                                                <button class="btn2 btn-secondary btn-sm claim " style="color:#fff;background-color:#6c757d;" type="submit" data-form="#claim-action2{{$transactConfirmation->id }}head_id{{ $transactConfirmation->transact_header_id }}">
                                                                -->
                                                        <button class="btn2 btn-secondary btn-sm claim " style="color:#fff;background-color:#6c757d;" type="submit" id="claim_action2_btn" onClick="claimAction('claim-action2{{$transactConfirmation->id }}head_id{{ $transactConfirmation->transact_header_id }}')">
                                                            <div class="fas fa-pen mr10"></div>{{ $transactConfirmation->buttonRemandFacility['label'] }}
                                                        </button>
                                                        <input type="hidden" name="page" id="page" value="{{ $pager->current }}">
                                                        <input type="hidden" name="head_id" id="head_id" value="{{ $transactConfirmation->transact_header_id }}">
                                                        <input type="hidden" name="claim_control" id="claim_control" value="1">
                                                        <input type="hidden" name="isDetail" id="isDetail" value="1">
                                                        <input type="hidden" name="facility_status_remand" id="facility_status_remand" value="{{ $transactConfirmation->facility_status_remand }}">
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

            @endif
                                </td>

                                <!-- <td></td> -->
        @endif
                            </tr>
    @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
@endif

    </div>
    <div class="card-footer d-flex  pt15 pb15 mt15">
@if (!is_null($detail))
        <a class="btn btn-default btn-lg" href="{{ route('claim.index') }}?page={{ $page }}"><i class="fas fa-chevron-left mr10"></i>前の画面へ戻る</a>
@else
        <a class="btn btn-default btn-lg" href="{{ route('claim.index') }}"><i class="fas fa-chevron-left mr10"></i>前の画面へ戻る</a>
@endif
        <!-- SQLダウンロード開発時用  -->
        @if ($admin_kengen)
        <a class="w200 btn btn-primary sqlm" href="{{ route('claim.sql_download3', $detail->id ) }}?page={{ $detail->page }}" download="業者別ヘッダーSQL.txt">
            <div class="col-12 tar"></div>ヘッダーSQLダウンロード
        </a>
        <a class="w200 btn btn-primary sqlm" href="{{ route('claim.sql_download4', $detail->id ) }}?page={{ $detail->page }}" download="業者別明細SQL.txt">
            <div class="col-12 tar"></div>明細SQLダウンロード
        </a>
        @endif
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
                <button class="btn2 btn-primary" type="submit" id="send_upload_btn" name="{{route('claim.sendh_upload')}}">
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
                <button class="btn2 btn-primary" type="submit" id="receive_upload_btn" name="{{route('claim.receiveh_upload')}}">
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
                <button class="btn2 btn-primary" type="submit" id="send_delete_btn" name="{{route('claim.sendh_delete')}}">
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
                <button class="btn2 btn-primary" type="submit" id="receive_delete_btn" name="{{route('claim.receiveh_delete')}}">
                    <div class="fas fa-pen mr10"></div>削除する
                </button>
            </div>
        </div>
    </div>
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

<div class="fade modal" id="modal-reject_comment" role="dialog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">差戻しコメント</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
<script>
    $("#send_upload_btn").click(function() {
        //alert('test');
        var url = $('#send_upload_btn').attr('name');
        //alert(url);
        $('#upDown').attr('action', url);
        $('#upDown').submit();

    });
    $("#send_delete_btn").click(function() {
        //alert('test');
        var url = $('#send_delete_btn').attr('name');
        //alert(url);
        $('#upDown').attr('action', url);
        $('#upDown').submit();
    });
    $("#receive_upload_btn").click(function() {
        //alert('test');
        var url = $('#receive_upload_btn').attr('name');
        //alert(url);
        $('#upDown').attr('action', url);
        $('#upDown').submit();
    });
    $("#receive_delete_btn").click(function() {
        //alert('test');
        var url = $('#receive_delete_btn').attr('name');
        //alert(url);
        $('#upDown').attr('action', url);
        $('#upDown').submit();
    });

    function claimAction(id) {
        $('#' + id).submit();
    }
</script>

@endsection
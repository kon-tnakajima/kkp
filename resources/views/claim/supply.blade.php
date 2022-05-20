@extends('layouts.app')

@php($title = 'データ区分別')

@php($category = 'claim')

@php($breadcrumbs = ['HOME'=>'/','請求照会'=>'../', $title=>'javascript:void(0);'])

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
<div class="card card-primary {{ $category }}">
    <form class="form-horizontal" action="{{ route('claim.edit', ['id '=> $detail->id]) }}" method="post" id="claim-edit" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="flg" id="flg" value="{{ $detail->flg }}">
        <div class="card-body">
            <div class="mt30 pt15 block">
                <div class="row">
                    <div class="col-12">
                        <table class="table table-primary table-small" id="statement">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>取引年月</th>
                                    <th>データ区分</th>
                                    <th>薬価金額合計</th>
                                    <th>売上金額合計</th>
                                    @if ( isBunkaren() )
                                    <th>仕入金額合計</th>
                                    @endif
                                    <th>状態</th>
                                    <th>担当</th>
                                    <th>登録者</th>
                                    <th>受取日</th>
                                    <th>登録日</th>
                                    <th>更新日</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{ $detail->id }}</td>
                                    <td>{{ date('Y/m',  strtotime($detail->claim_month)) }}</td>
                                    <td>{{ $detail->data_type_name }}</td>
                                    <td>{{ number_format($detail->medicine_price_total) }}</td>
                                    <td>{{ number_format($detail->sales_price_total) }}</td>
                                    @if ( isBunkaren() )
                                    <td>{{ number_format($detail->purchase_price_total) }}</td>
                                    @endif
                                    <td>{{ App\Model\Task::getStatusForClaim($detail->claim_task_status) }}</td>
                                    <td>{{ $detail->facility_name }}</td>
                                    <td>{{ $detail->user_name }}</td>
                                    <td>{{ $detail->receipt_date }}</td>
                                    <td>{{ $detail->created_at }}</td>
                                    <td>{{ $detail->updated_at }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @if( !is_array($list) && count($list) > 0 )
            <div class="mt30 pt15 block block-table-control">
                <div class="row">
                    <div class="col-12 mb20 d-flex">
                        <h4>データ区分別</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 d-flex justify-content-between align-items-center">
                        <p class="fs16 text"> 全{{ $list->total() }}件</p>
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
                                    <th>取引年月</th>
                                    <th>データ区分</th>
                                    <th>薬価金額合計</th>
                                    <th>売上金額合計</th>
                                    @if ( isBunkaren() )
                                    <th>仕入金額合計</th>
                                    @endif
                                    <th>施設</th>
                                    <th>登録日</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($list as $transactHeader)
                                <tr>
                                    <!--td>{{ $loop->iteration + ( $page_count * ($pager->current -1) ) }} @if ( $transactHeader->transactConfirmation )<a class="ml10" href="{{ $transactHeader->url }}">業者別</a> @endif</td-->
                                    <td>{{ $loop->iteration }} @if ( $transactHeader->transactConfirmation )<a class="ml10" href="{{ $transactHeader->url }}">業者別</a> @endif</td>
                                    <td>{{ date('Y/m',  strtotime($transactHeader->claim_month)) }}</td>
                                    <td>{{ App\Helpers\Claim\getSupplyDivisionText($transactHeader->supply_division) }}</td>
                                    <td>{{ number_format($transactHeader->medicine_price_total()) }}</td>
                                    <td>{{ number_format($transactHeader->sales_price_total()) }}</td>
                                    @if ( isBunkaren() )
                                    <td>{{ number_format($transactHeader->purchase_price_total()) }}</td>
                                    @endif
                                    <td>{{ $transactHeader->facility()->name }}</td>
                                    <td>{{ $transactHeader->created_at }}</td>
                                </tr>
                            @endforeach
                                <tr>
                                    <td class="tr_total" colspan="3">合計</td>
                                    <td>{{ number_format($detail->medicine_price_total()) }}</td>
                                    <td>{{ number_format($detail->sales_price_total()) }}</td>
                                    @if ( isBunkaren() )
                                    <td>{{ number_format($detail->purchase_price_total()) }}</td>
                                    @endif
                                    <td class="tr_total" colspan="2"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($pager->max > 1)
                <!--div class="row mt15">
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
                </div-->
                @endif
            </div>
            @endif

        </div>
        <div class="card-footer d-flex justify-content-around pt15 pb15 mt15">
            <a class="btn btn-default btn-lg" href="{{ route('claim.index') }}?page={{ $detail->page }}"><i class="fas fa-chevron-left mr10"></i>前の画面へ戻る</a>
            <div class="block">
                @if ( !empty($detail->edit) )
                <button class="mr30 btn btn-primary btn-lg" type="button" data-toggle="modal" data-target="#modal-action">
                    <div class="fas fa-pen mr10"></div>更新する
                </button>
                @endif
                <!--button class="btn btn-danger btn-lg" type="button" data-toggle="modal" data-target="#modal-delete">
                    <div class="fas fa-ban mr10"></div>削除する
                </button-->
                @if ( !is_null($detail->buttonRemand['url']))
                    <button class="ml20 btn btn-secondary apply" type="button" data-toggle="modal" data-target="#modal-actionRemand{{ $detail->id }}">{{ $detail->buttonRemand['label'] }}</button>
                @endif
                @if ( !is_null($detail->button['url']))
                    <button class="ml40 btn {{$detail->button['style']}} apply" type="button" data-toggle="modal" data-target="#modal-action{{ $detail->id }}">{{ $detail->button['label'] }}</button>
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
                        <p class="tac text">この請求を更新してよろしいでしょうか？</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
                        <button class="btn2 btn-primary" type="submit" data-form="#claim-edit">
                            <div class="fas fa-pen mr10"></div>更新する
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @if ( !is_null($detail->button['url']))
        <div class="fade modal" id="modal-action{{ $detail->id }}" role="dialog" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">請求登録</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <p class="tac text">この請求を{{ $detail->button['label'] }}してよろしいでしょうか？</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
                        <button class="btn2 {{$detail->button['style']}}" type="submit" data-form-action="{{ $detail->button['url'] }}" data-form-method="post" data-form="#claim-edit">
                            <div class="fas fa-pen mr10"></div>{{ $detail->button['label'] }}
                        </button>
                        <input class="form-control validate" type="hidden" name="page" id="page" value="{{ $detail->page }}">
                    </div>
                </div>
            </div>
        </div>
        @endif
    </form>
@if ( !is_null($detail->buttonRemand['url']))
    <div class="fade modal" id="modal-actionRemand{{ $detail->id }}" role="dialog" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">請求登録</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <p class="tac text">この請求を{{ $detail->buttonRemand['label'] }}してよろしいでしょうか？</p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
                    <form action="{{ $detail->buttonRemand['url'] }}" name="apply-action{{ $detail->id }}" id="apply-action{{ $detail->id }}">
                        <button class="btn-primary" type="submit" data-form-action="{{ $detail->buttonRemand['url'] }}" data-form-method="get" data-form="#claim-edit">
                            <div class="fas fa-pen mr10"></div>{{ $detail->buttonRemand['label'] }}
                        </button>
                        <input class="form-control validate" type="hidden" name="page" id="page" value="{{ $detail->page }}">
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
                        <input type="hidden" name="comment2" id="comment2" value="{{ $detail->comment }}">
                        <input type="hidden" name="page" id="page" value="{{ $detail->page }}">
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
                    <input type="hidden" name="facility_id" id="facility_id" value="{{ Auth::user()->facility->id }}">
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

@endsection
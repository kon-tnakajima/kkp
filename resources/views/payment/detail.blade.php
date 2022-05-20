@extends('layouts.app')

@php($title = '請求登録詳細')

@php($category = 'apply')

@php($breadcrumbs = ['HOME'=>'/','請求登録'=>'/apply/','請求登録詳細'=>'javascript:void(0);'])

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
    <form class="form-horizontal" action="{{ route('apply.edit', ['id '=> $detail->id]) }}" method="post" id="payment-edit" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="flg" id="flg" value="{{ $detail->flg }}">
        <div class="card-body">
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">請求ID</label>
                <div class="col-sm-6">
                    <p class="form-control-static">{{ $detail->id }}</p>
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">対象年月</label>
                <div class="col-sm-6">
                    <p class="form-control-static">{{ $detail->invoice_date }}</p>
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">区分</label>
                <div class="col-sm-6">
                    <p class="form-control-static">{{ App\Model\Medicine::OWNER_CLASSIFICATION_STR[$detail->owner_classification] }}</p>
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">添付ファイル</label>
                <div class="col-sm-6">
                    <a target="_blank" class="form-control-static" href="/storage/{{ $detail->file_name }}">{{ $detail->file_name }}</a>
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">薬価金額合計</label>
                <div class="col-sm-6">
                    <p class="form-control-static">{{ $detail->medicine_price_total }}</p>
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">購入金額合計</label>
                <div class="col-sm-6">
                    <p class="form-control-static">{{ $detail->purchase_price_total }}</p>
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">状態</label>
                <div class="col-sm-6">
                    <p class="form-control-static">{{ App\Model\Task::getStatusForPayment($detail->status) }}</p>
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">明細登録<span class="badge badge-danger ml-2">必須</span></label>
                <div class="col-sm-6">
                    <div class="error-tip">
                        <div class="error-tip-inner">{{ $errors->first('file') }}</div>
                    </div>
                    <input class="form-control validate w400 h40" type="file" data-validate="empty" id="file" name="file">
                </div>
            </div>

            @if (isBunkaren() )
            @else
            @endif
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">登録者</label>
                <div class="col-sm-6">
                    <p class="form-control-static">{{ $detail->user_name }}</p>
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">登録日</label>
                <div class="col-sm-6">
                <p class="form-control-static">{{ $detail->created_at }}</p>
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">更新日</label>
                <div class="col-sm-6">
                <p class="form-control-static">{{ $detail->updated_at }}</p>
                </div>
            </div>
            <div class="mt30 pt15 block block-table-control">
                <div class="row">
                    <div class="col-12 mb20 d-flex">
                        <h4>業者別</h4>
                    </div>
                </div>
                <!-- <div class="row">
                    <div class="col-12 d-flex justify-content-between align-items-center"><a class="anchor anchor-accordion anchor-payment-state" href="#collapseTrader" data-toggle="collapse" aria-expanded="false" aria-controls="collapseTrader">詳細を表示する</a></div>
                </div> -->
                <div class="" id="collapseTrader" style="">
                    <div class="row">
                        <div class="col-12 d-flex justify-content-between align-items-center">
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
                                        <th>薬価金額</th>
                                        <th>購入金額</th>
                                        <th>状態</th>
                                        <th>担当</th>
                                        <th>登録者</th>
                                        <th>登録日</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td>{{ $detail->trader_name }}</td>
                                        <td></td>
                                        <td></td>
                                        <td>{{ App\Model\Task::getStatusForPayment($detail->status) }}</td>
                                        <td>{{ $detail->facility_name }}</td>
                                        <td>{{ $detail->user_name }}</td>
                                        <td>{{ $detail->created_at }}</td>
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
            </div>

            @if( !is_array($list) && count($list) > 0 )
            <div class="mt30 pt15 block block-table-control">
                <div class="row">
                    <div class="col-12 mb20 d-flex">
                        <h4>請求明細</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 d-flex justify-content-between align-items-center"><a class="anchor anchor-accordion anchor-payment-state" href="#collapseExample" data-toggle="collapse" aria-expanded="false" aria-controls="collapseExample">詳細を表示する</a></div>
                </div>
                <div class="collapse" id="collapseExample" style="">
                    <div class="row">
                        <div class="col-12 d-flex justify-content-between align-items-center">
                            <p class="fs16 text">@if ( $page_count > $list->total() ){{ $list->total() }} @else @if($pager->current == $pager->last){{ ($list->total() - ($page_count * ($pager->current - 1) )) }} @else{{ $page_count }}@endif @endif件 / 全{{ $list->total() }}件</p>
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
                                        <th>明細日付</th>
                                        <th>メーカー</th>
                                        <th>商品名</th>
                                        <th>規格</th>
                                        <th>JANコード</th>
                                        <th>施設単価</th>
                                        <th>数量</th>
                                        <th>施設金額</th>
                                        <th>備考</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($list as $invoice_detail)
                                    <tr>
                                        <td>{{ $loop->iteration + ( $page_count * ($pager->current -1) ) }}</td>
                                        <td>{{ $invoice_detail->created_at }}</td>
                                        <td>{{ $invoice_detail->maker_name }}</td>
                                        <td>{{ $invoice_detail->medicine_name }}</td>
                                        <td>{{ $invoice_detail->standard_unit }}</td>
                                        <td>{{ $invoice_detail->jan_code }}</td>
                                        <td>{{ $invoice_detail->sales_price }}</td>
                                        <td>{{ $invoice_detail->invoice_count }}</td>
                                        <td>{{ $invoice_detail->sales_price_total }}</td>
                                        <td>{{ $invoice_detail->comment }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
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
            </div>
            @endif

        </div>
        <div class="card-footer d-flex justify-content-around pt15 pb15 mt15">
            <a class="btn btn-default btn-lg" href="{{ route('payment.index') }}?page={{ $detail->page }}"><i class="fas fa-chevron-left mr10"></i>採用申請一覧へ戻る</a>
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
                        <button class="btn2 btn-primary" type="submit" data-form="#payment-edit">
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
                        <button class="btn2 {{$detail->button['style']}}" type="submit" data-form-action="{{ $detail->button['url'] }}" data-form-method="post" data-form="#payment-edit">
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
                        <button class="btn-primary" type="submit" data-form-action="{{ $detail->buttonRemand['url'] }}" data-form-method="get" data-form="#payment-edit">
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

    <div class="fade modal" id="medicine-price-history" role="dialog" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">薬価履歴</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <table class="table table-primary table-small" id="applies">
                        <thead>
                            <tr>
                                <th>適用期間</th>
                                <th>薬価（円）</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($detail->medicine_price_list as $m_price)
                            <tr>
                                <td>{{ $m_price->start_date }} 〜 {{ $m_price->end_date }}</td>
                                <td>{{ $detail->getPackUnitPriceForDetail($detail->coefficient, $m_price->price) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>



</div>

@endsection

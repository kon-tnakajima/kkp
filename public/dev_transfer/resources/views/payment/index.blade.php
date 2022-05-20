@extends('layouts.app')

@php($title = '請求登録')

@php($category = 'payment')

@php($breadcrumbs = ['HOME'=>'./','請求登録'=>'javascript:void(0);'])

@section('content')

{{ $viewdata->get('message') }}
@if (!is_null($viewdata->get('list')))


<div class="card card-primary payment">
    <div class="pt-0 card-body">
        <form class="form-horizontal" method="post" id="payment" action="{{ route('payment.regist') }}" enctype="multipart/form-data">
            @csrf
            <div class="row mt30">
                <div class="form-group validateGroup col-10 d-flex">
                    <div class="d-flex mr30 align-items-center block">
                        <div class="error-tip">
                            <div class="error-tip-inner">{{ $errors->first('file') }}</div>
                        </div>
                        <input class="form-control validate w400 h40" type="file" data-validate="empty" id="file" name="file"><span class="badge badge-danger ml-2">必須</span>
                    </div>
                </div>
                <div class="col-2">
                    <button class="w100p mr30 btn btn-primary btn-lg" type="button" data-toggle="modal" data-target="#modal-action">
                        <div class="fas fa-edit mr10"></div>登録する
                    </button>
                    <div class="fade modal" id="modal-action" role="dialog" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">請求登録</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                </div>
                                <div class="modal-body">
                                    <p class="tac text">この請求を登録してよろしいでしょうか？</p>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
                                    <button class="btn2 btn-primary" type="submit" data-form="#payment">
                                        <div class="fas fa-pen mr10"></div>登録する
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-4 d-flex form-group validateGroup">
                    <div class="d-flex mr10 align-items-center block">
                        <p class="w120 mr10 fwb col-form-label">取引年月<span class="badge badge-danger ml-2">必須</span></p>
                        <div class="d-flex">
                            <div class="error-tip">
                                <div class="error-tip-inner">{{ $errors->first('invoice_date') }}</div>
                            </div>
                            <input class="form-control validate w200 ui-datepicker" type="text" id="invoice_date" name="invoice_date" data-validate="empty" value="">
                        </div>
                    </div>
                </div>
                <div class="col-6 d-flex form-group validateGroup">
                    <div class="d-flex mr30 align-items-center block">
                        <p class="mr10 fwb col-form-label">区分<span class="badge badge-danger ml-2">必須</span></p>
                        <div class="d-flex">
                            <div class="error-tip">
                                <div class="error-tip-inner">{{ $errors->first('medicine_type') }}</div>
                            </div>
                            <select class="w180 form-control validate" name="owner_classification" id="owner_classification" data-validate="empty">
                                <option value="">選択してください</option>
                                @foreach(App\Model\Medicine::OWNER_CLASSIFICATION_STR as $key => $value)
                                <option value="{{$key}}">{{$value}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-2"></div>
            </div>
        </form>
        <div class="mt30 pt15 block block-table-control">
            <div class="row">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <p class="fs16 text">@if ( $viewdata->get('page_count') > $viewdata->get('list')->total() ){{ $viewdata->get('list')->total() }} @else @if($viewdata->get('pager')->current == $viewdata->get('pager')->last){{ ($viewdata->get('list')->total() - ($viewdata->get('page_count') * ($viewdata->get('pager')->current - 1) )) }} @else{{ $viewdata->get('page_count') }}@endif @endif件 / 全{{ $viewdata->get('list')->total() }}件</p>
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
                <table class="table table-primary table-small" id="payments">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>取引年月</th>
                            <th>区分</th>
                            <th>薬価金額</th>
                            <th>購入金額</th>
                            <th>状態</th>
                            <th>担当</th>
                            <th>登録日</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($viewdata->get('list') as $payment)
                        <tr>
                            <td class="tal @if ( App\Helpers\Payment\isTargetApply($payment, Auth::user()->primary_user_group_id) ) target-apply @endif">{{ $loop->iteration + ( $viewdata->get('page_count') * ($viewdata->get('pager')->current -1) ) }}<a class="ml10" href="{{ $payment->url }}">業者別</a></td>
                            <td class="@if ( App\Helpers\Payment\isTargetApply($payment, Auth::user()->primary_user_group_id) ) target-apply @endif">{{ $payment->invoice_date }}</td>
                            <td class="@if ( App\Helpers\Payment\isTargetApply($payment, Auth::user()->primary_user_group_id) ) target-apply @endif">{{ App\Model\Medicine::OWNER_CLASSIFICATION_STR[$payment->owner_classification] }}</td>
                            <td class="@if ( App\Helpers\Payment\isTargetApply($payment, Auth::user()->primary_user_group_id) ) target-apply @endif">{{ $payment->medicine_price_total }}</td>
                            <td class="@if ( App\Helpers\Payment\isTargetApply($payment, Auth::user()->primary_user_group_id) ) target-apply @endif">{{ $payment->purchase_price_total }}</td>
                            <td class="@if ( App\Helpers\Payment\isTargetApply($payment, Auth::user()->primary_user_group_id) ) target-apply @endif"><span class="badge badge-{{ App\Helpers\Payment\getStatusBadgeClass($payment->status)}}">{{ App\Model\Task::getStatusForPayment($payment->status) }}</span></td>
                            <td class="@if ( App\Helpers\Payment\isTargetApply($payment, Auth::user()->primary_user_group_id) ) target-apply @endif">{{ $payment->facility_name }}</td>
                            <td class="@if ( App\Helpers\Payment\isTargetApply($payment, Auth::user()->primary_user_group_id) ) target-apply @endif">{{ diffTime($payment->created_at) }}</td>
                            <td class="@if ( App\Helpers\Payment\isTargetApply($payment, Auth::user()->primary_user_group_id) ) target-apply @endif">
                                <input type="hidden" name="page" id="page" value="{{ $viewdata->get('pager')->current }}">
                                @if (!is_null($payment->button['url']))
                                    <button class="btn {{ $payment->button['style'] }} btn-sm payment" type="button" data-toggle="modal" data-target="#modal-action{{ $payment->medicine_id }}p{{ $payment->price_adoption_id }}">{{ $payment->button['label'] }}</button>
                                    <div class="fade modal" id="modal-action{{ $payment->medicine_id }}p{{ $payment->price_adoption_id }}" role="dialog" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">採用申請</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p class="tac text">この薬品を{{ $payment->button['label'] }}してよろしいでしょうか？</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
                                                    <form action="{{ $payment->button['url'] }}" name="payment-action{{ $payment->medicine_id }}p{{ $payment->price_adoption_id }}" id="payment-action{{ $payment->medicine_id }}p{{ $payment->price_adoption_id }}">
                                                        <button class="btn {{ $payment->button['style'] }} " type="submit" data-form="#payment-action{{ $payment->medicine_id }}p{{ $payment->price_adoption_id }}">
                                                            <div class="fas fa-pen mr10"></div>{{ $payment->button['label'] }}
                                                        </button>
                                                        <input type="hidden" name="page" id="page" value="{{ $viewdata->get('pager')->current }}">
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
</div>

@endif
@endsection

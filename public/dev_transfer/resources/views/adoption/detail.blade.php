@extends('layouts.app')

@php($title = '採用品検索')

@php($category = 'adoption')

@php($breadcrumbs = ['HOME'=>'/','採用品検索'=>'/apply/','採用品詳細'=>'javascript:void(0);'])

@section('content')
@if ($errors->any())
入力にエラーがあります<br>
@endif
@if ( !empty( $viewdata->get('errorMessage') ) )
<div class="alert alert-danger" role="alert">
{{ $viewdata->get('errorMessage') }}
</div>
@endif
@if ( !empty( $viewdata->get('message') ) )
<div class="alert alert-success" role="alert">
{{ $viewdata->get('message') }}
</div>
@endif
<div class="card card-primary apply_detail">
    <form class="form-horizontal" action="{{ route('adoption.edit', ['id '=> $viewdata->get('detail')->id]) }}" method="post" id="adoption-edit" accept-charset="utf-8">
        @csrf
        <input type="hidden" name="page" id="page" value="{{ $viewdata->get('detail')->page }}">
        <div class="card-body">
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">販売メーカー名</label>
                <div class="col-sm-6">
                    <p class="form-control-static">{{ $viewdata->get('detail')->selling_maker_name }}</p>
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">製造メーカー名</label>
                <div class="col-sm-6">
                    <p class="form-control-static">{{ $viewdata->get('detail')->maker_name }}</p>
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">商品名</label>
                <div class="col-sm-6">
                    <p class="form-control-static">{{ $viewdata->get('detail')->name }}</p>
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">一般名</label>
                <div class="col-sm-6">
                    <p class="form-control-static">{{ $viewdata->get('detail')->popular_name }}</p>
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">規格容量</label>
                <div class="col-sm-6">
                    <p class="form-control-static">{{ $viewdata->get('detail')->standard_unit }}</p>
                </div>
            </div>

            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">JANコード</label>
                <div class="col-sm-6">
                    <p class="form-control-static">{{ $viewdata->get('detail')->jan_code }}</p>
                </div>
            </div>

            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">GS1販売</label>
                <div class="col-sm-6">
                    <p class="form-control-static">{{ $viewdata->get('detail')->sales_packaging_code }}</p>
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">GS1調剤</label>
                <div class="col-sm-6">
                    <p class="form-control-static">{{ $viewdata->get('detail')->dispensing_packaging_code }}</p>
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">HOT番号</label>
                <div class="col-sm-6">
                    <p class="form-control-static">{{ $viewdata->get('detail')->hot_code }}</p>
                </div>
            </div>

            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">医薬品CD</label>
                <div class="col-sm-6">
                    <p class="form-control-static">{{ $viewdata->get('detail')->code }}</p>
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">YJコード</label>
                <div class="col-sm-6">
                    {{ $viewdata->get('detail')->medicine_code }}
                    @if(!empty($viewdata->get('detail')->medicine_code))
                    （<a class="form-control-static" target="_blank" href="https://www.safe-di.jp/service/medicine/show?params_yj_cd={{ $viewdata->get('detail')->medicine_code }}">SAFE-DI</a>）
                    @endif
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">単位薬価</label>
                <div class="col-sm-6">
                    <p class="form-control-static">{{ $viewdata->get('detail')->medicine_price }}</p>
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">包装薬価係数</label>
                <div class="col-sm-6">
                    <p class="form-control-static">{{ $viewdata->get('detail')->coefficient }}</p>
                </div>
            </div>
            <div class="form-group row"><label class="col-sm-3 form-control-label" for="medicine_price"> 包装薬価</label>
                <div class="col-sm-4">
                    <p><span class="mr20">{{ number_format($viewdata->get('detail')->pack_unit_price, 2) }} 円</span>
                </div>
            </div>

            @if (isBunkaren() )
            <div class="form-group validateGroup row"><label class="col-sm-3 col-form-label" for="purchase_price">包装仕入単価</label>
                <div class="col-sm-2">
                    <p class="form-control-static">{{ $viewdata->get('detail')->purchase_price }} 円</p>
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">値引率(仕入)</label>
                <div class="col-sm-6">
                    <p class="form-control-static">{{ $viewdata->get('detail')->getSalesRate($viewdata->get('detail')->purchase_price, $viewdata->get('detail')->pack_unit_price) }}</p>
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">掛率(仕入)</label>
                <div class="col-sm-6">
                    <p class="form-control-static">{{ $viewdata->get('detail')->getMarkUp($viewdata->get('detail')->purchase_price, $viewdata->get('detail')->pack_unit_price) }}</p>
                </div>
            </div>
            @endif

            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="sales_price">包装納入単価</label>
                <div class="col-sm-2">
                    <div class="error-tip">
                        <div class="error-tip-inner">{{ $errors->first('sales_price') }}</div>
                    </div>
                    <p class="form-control-static">{{ number_format($viewdata->get('detail')->sales_price,2) }} 円</p>
                    <input type="hidden" name="sales_price" id="sales_price" value="{{ $viewdata->get('detail')->sales_price }}">
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">値引率(納入)</label>
                <div class="col-sm-6">
                    <p class="form-control-static">{{ $viewdata->get('detail')->getSalesRate($viewdata->get('detail')->sales_price, $viewdata->get('detail')->pack_unit_price) }}</p>
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">掛率(納入)</label>
                <div class="col-sm-6">
                    <p class="form-control-static">{{ $viewdata->get('detail')->getMarkUp($viewdata->get('detail')->sales_price, $viewdata->get('detail')->pack_unit_price) }}</p>
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">業者</label>
                <div class="col-sm-3">
                    <p class="form-control-static">{{ $viewdata->get('detail')->trader_name }}</p>
                </div>
            </div>
            <!--div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">包装薬価係数</label>
                <div class="col-sm-6">
                    <p class="form-control-static">{{ $viewdata->get('detail')->coefficient }}</p>
                </div>
            </div-->
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">薬価有効開始日</label>
                <div class="col-sm-6">
                    <p class="form-control-static">{{ $viewdata->get('detail')->medicine_price_revision_date }}</p>
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">単価有効開始日</label>
                <div class="col-sm-6">
                    <p class="form-control-static">{{ $viewdata->get('detail')->facility_price_start_date }}</p>
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">剤型</label>
                <div class="col-sm-6">
                    <p class="form-control-static">{{ App\Helpers\Apply\getDosageTypeDivisionText($viewdata->get('detail')->dosage_type_division) }}</p>
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">先後発区分</label>
                <div class="col-sm-6">
                    <p class="form-control-static">{{ App\Helpers\Apply\getGenericProductDetailDivision($viewdata->get('detail')->generic_product_detail_devision) }}</p>
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">生・毒・劇・麻・向・覚・覚原・血</label>
                <div class="col-sm-6">
                    <p class="form-control-static">{{ $viewdata->get('detail')->danger_poison }}</p>
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">保管温度</label>
                <div class="col-sm-6">
                    <p class="form-control-static">{{ $viewdata->get('detail')->storage_temperature }}</p>
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">保管場所</label>
                <div class="col-sm-6">
                    <p class="form-control-static">{{ $viewdata->get('detail')->storage_place }}</p>
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">申請日</label>
                <div class="col-sm-6">
                <p class="form-control-static">{{ $viewdata->get('detail')->created_at }}</p>
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">更新日</label>
                <div class="col-sm-6">
                <p class="form-control-static">{{ $viewdata->get('detail')->updated_at }}</p>
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">製造中止日</label>
                <div class="col-sm-6">
                    <p class="form-control-static">{{ $viewdata->get('detail')->production_stop_date }}</p>
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">経過措置期限</label>
                <div class="col-sm-6">
                    <p class="form-control-static">{{ $viewdata->get('detail')->transitional_deadline }}</p>
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">販売中止日</label>
                <div class="col-sm-6">
                    <p class="form-control-static">{{ $viewdata->get('detail')->discontinuation_date }}</p>
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 col-form-label" for="adoption_stop_date">採用中止日 @if ($viewdata->get('user')->getFacility()->id === $viewdata->get('detail')->facility_id ) <span class="badge badge-success ml-2">任意</span> @endif</label>
                <div class="col-sm-6">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    @if ($viewdata->get('user')->getFacility()->id === $viewdata->get('detail')->facility_id)
                    <input class="form-control validate adoption_stop_date col-sm-4" data-datepicker="true" type="text" name="adoption_stop_date" id="adoption_stop_date" data-datepicker="true" placeholder="" value="{{ $viewdata->get('detail')->adoption_stop_date }}">
                    @else
                    <p>{{ $viewdata->get('detail')->adoption_stop_date }}</p>
                    @endif
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">オーナー区分</label>
                <div class="col-sm-6">
                    <p class="form-control-static">{{ App\Helpers\Apply\getOwnerClassification($viewdata->get('detail')->owner_classification) }}</p>
                </div>
            </div>

            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">施設名</label>
                <div class="col-sm-6">
                    <p class="form-control-static">{{ $viewdata->get('detail')->facility_name }} </p>
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">申請者</label>
                <div class="col-sm-6">
                    <p class="form-control-static">{{ $viewdata->get('detail')->user_name }}</p>
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 col-form-label" for="comment">コメント @if ( $viewdata->get('user')->getFacility()->id === $viewdata->get('detail')->facility_id ) <span class="badge badge-success ml-2">任意</span> @endif</label>
                <div class="col-sm-6">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    @if ($viewdata->get('user')->getFacility()->id === $viewdata->get('detail')->facility_id)
                    <textarea class="form-control validate comment" name="comment" id="comment" rows="10" placeholder="" data-validate="">{{ $viewdata->get('detail')->comment }}</textarea>
                    @else
                    <p>{{ $viewdata->get('detail')->comment }}</p>
                    @endif
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">更新ユーザ</label>
                <div class="col-sm-6">
                    <p class="form-control-static">{{ $viewdata->get('detail')->updater_name }}</p>
                </div>
            </div>
            <div class="form-group validateGroup row mb-2"><label class="form-control-label col-sm-3">物理キー</label>
                <div class="col-sm-6">
                    <p class="form-control-static">{{ $viewdata->get('detail')->id }}</p>
                </div>
            </div>
            @if( !empty($viewdata->get('detail')->medicine_id) )
            <div class="mt30 pt15 block block-table-control">
                <div class="row">
                    <div class="col-12 mb20 d-flex">
                        <h4>他施設採用状況</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 d-flex justify-content-between align-items-center"><a class="anchor anchor-accordion anchor-payment-state" href="#collapseExample" data-toggle="collapse" aria-expanded="false" aria-controls="collapseExample">詳細を表示する</a></div>
                </div>
                <div class="collapse" id="collapseExample" style="">
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
                    <div class="row">
                        <div class="col-12">
                            <table class="table table-primary table-small" id="statement">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>施設</th>
                                        <th>採用日</th>
                                        <th>採用中止日</th>
                                        @foreach(getFiscalYearHistory() as $value)
                                        <th>{{$value}}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($viewdata->get('list') as $apply)
                                    <tr>
                                        <td>{{ $loop->iteration + ( $viewdata->get('page_count') * ($viewdata->get('pager')->current -1) ) }}</td>
                                        <td>{{ $apply->facility_name }}</td>
                                        <td>{{ $apply->adoption_date }}</td>
                                        <td>{{ $apply->adoption_stop_date }}</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
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
        </div>
        <div class="card-footer d-flex justify-content-around pt15 pb15 mt15">
            <a class="btn btn-default btn-lg" href="{{ route('adoption.index') }}?page={{ $viewdata->get('detail')->page }}"><i class="fas fa-chevron-left mr10"></i>採用品検索へ戻る</a>
@if ($viewdata->get('user')->getFacility()->id === $viewdata->get('detail')->facility_id)
            <div class="block">
                <button class="mr30 btn btn-primary btn-lg" type="button" data-toggle="modal" data-target="#modal-action">
                    <div class="fas fa-pen mr10"></div>更新する
                </button>
                <button class="btn btn-danger btn-lg" type="button" data-toggle="modal" data-target="#modal-delete">
                    <div class="fas fa-ban mr10"></div>削除する
                </button>
            </div>
@endif
        </div>
        <div class="fade modal" id="modal-action" role="dialog" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">採用薬品更新</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <p class="tac text">この薬品を更新してよろしいでしょうか？</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
                        <button class="btn btn-primary" type="submit" data-form="#adoption-edit">
                            <div class="fas fa-pen mr10"></div>更新する
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="fade modal" id="modal-delete" role="dialog" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">採用薬品削除</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <p class="tac text">この薬品を削除してよろしいでしょうか？</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
                        <button class="btn btn-primary" type="button" onClick="location.href='{{ route('adoption.delete', ['id' => $viewdata->get('detail')->id]) }}'">
                            <div class="fas fa-pen mr10"></div>削除する
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@endsection

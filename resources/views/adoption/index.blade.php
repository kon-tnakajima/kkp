@extends('layouts.app')

@php($title = '採用品検索')

@php($category = 'adoption')

@php($breadcrumbs = ['HOME'=>'./','採用品検索'=>'javascript:void(0);'])

@section('content')
{{ $viewdata->get('message') }}
@if (!is_null($viewdata->get('list')))
                <div class="card card-primary">
                    <div class="pt-0 card-body">
                        <form class="form-horizontal" method="get" id="search_medicine" accept-charset="utf-8">
                            <input type="hidden" data-search-item="is_search" value="1">
                            <div class="row mt30">
                                <div class="col-12 tal"><a class="anchor anchor-clear-search" href="javascript:void(0);"><i class="fas fa-eraser mr10"></i>検索条件をクリア</a></div>
                            </div>
                            <div class="row mt10">
                                <div class="col-10 d-flex">
                                    <div class="d-flex mr30 align-items-center w100p block"><input class="form-control" type="search" data-search-item="search" placeholder="JANコード、商品名、メーカーで検索" value="{{ $viewdata->get('conditions')['search'] }}"></div>
                                </div>
                                <div class="col-2"><button class="w100p btn btn-primary btn-table-search" type="button"><i class="fas fa-search mr10"></i>検索する</button></div>
                            </div>
                            <div class="tac mt20 block"><a class="anchor anchor-accordion anchor-search-condition" href="#collapseExample" data-toggle="collapse" aria-expanded="{{ $viewdata->get('conditions')['is_condition'] }}" aria-controls="collapseExample">もっと検索条件を見る</a></div>
                            <div class="collapse @if( $viewdata->get('conditions')['is_condition'] ) show @endif" id="collapseExample">
                                <div class="row mt10">
                                    <div class="col-12 d-flex">
                                        <div class="d-flex mr30 align-items-center block">
                                            <p class="w50 mr10 fwb text">施設</p>
@foreach($viewdata->get('facilities') as $facility)
                                                <div class="custom-control custom-checkbox custom-control-inline"><input class="custom-control-input validate input-checkbox" id="hospital_{{ $facility->id }}" name="facility[]" data-search-item="facility[]" data-search-type="checkbox" value="{{ $facility->id }}" type="checkbox"  @if( in_array( $facility->id, $viewdata->get('conditions')['facility']) ) checked @endif><label class="custom-control-label" for="hospital_{{ $facility->id }}">{{ $facility->name}}</label></div>
@endforeach
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt10">
                                    <div class="col-4 d-flex">
                                        <div class="d-flex mr10 align-items-center block">
                                            <p class="mr10 fwb text">一般名</p>
                                            <div class="d-flex w250">
                                                <div class="d-flex w100p align-items-center block"><input class="form-control" type="search" data-search-item="popular_name" value="{{$viewdata->get('conditions')['popular_name']}}" placeholder=""></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-4 d-flex">
                                        <div class="d-flex mr10 align-items-center block">
                                            <p class="mr10 fwb text">医薬品CD&nbsp&nbsp&nbsp</p>
                                            <div class="d-flex">
                                                <div class="d-flex mr30 align-items-center block"><input class="form-control w200" type="search" value="{{ $viewdata->get('conditions')['code'] }}" data-search-item="code" placeholder=""></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-4 d-flex">
                                        <div class="d-flex mr10 align-items-center block">
                                            <p class="mr10 fwb text">包装薬価係数</p>
                                            <div class="d-flex">
                                                <div class="error-tip">
                                                    <div class="error-tip-inner">{{ $errors->first('coefficient') }}</div>
                                                </div>
                                                <div class="d-flex mr30 align-items-center block"><input class="form-control validate" type="search" data-search-item="coefficient" value="{{ $viewdata->get('conditions')['coefficient'] }}" placeholder="" data-validate="number"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt10">
				                    <div class="col-4 d-flex">
				                        <div class="d-flex mr30 align-items-center block">
				                            <p class="w80 mr10 fwb text">剤型</p>
				                            <select class="form-control" type="text" data-search-item="dosage_type_division">
				                                <option value="">全て</option>
				                            @foreach(App\Helpers\Apply\getDosage_type_division() as $key => $value)
				                                <option value="{{$key}}" @if( $key == $viewdata->get('conditions')['dosage_type_division'] ) selected @endif>{{$value}}</option>
				                            @endforeach
				                            </select>
				                        </div>
				                    </div>
				                    <div class="col-4 d-flex">
				                        <div class="d-flex mr10 align-items-center block">
				                            <p class="w120 mr10 fwb text">先後発区分</p>
				                            <select class="form-control" type="text" data-search-item="generic_product_detail_devision">
				                                <option value="">全て</option>
				                            @foreach(App\Helpers\Apply\getGeneric_product_detail_devision() as $key => $value)
				                                <option value="{{$key}}" @if( !is_null($viewdata->get('conditions')['generic_product_detail_devision']) && $key == $viewdata->get('conditions')['generic_product_detail_devision'] ) selected @endif>{{$value}}</option>
				                            @endforeach
				                            </select>
				                        </div>
				                    </div>
                                    <div class="col-3 d-flex">
                                        <div class="d-flex mr30 align-items-center block">
                                            <p class="w150 fwb text">採用中止を含まない</p>
                                            <div class="custom-control custom-checkbox custom-control-inline">
                                                <input class="custom-control-input validate input-checkbox" id="adoption_stop_date" data-search-item="adoption_stop_date" data-search-type="checkbox" data-search-clear-custom="true" value="1" type="checkbox" @if($viewdata->get('conditions')['adoption_stop_date'] == "1") checked @endif>
                                                <label class="custom-control-label" for="adoption_stop_date"></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
                                    <table class="table table-primary table-small" id="applies">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <!--th>JANコード</th-->
                                                <th>GS1販売</th>
                                                <th>販売メーカー</th>
                                                <th>商品名</th>
                                                <th>規格容量</th>
                                                <th>先後発区分</th>
                                                <th>包装薬価係数</th>
                                                <th>単位薬価</th>
                                                <!--th>単位納入価</th-->
                                                <th>包装薬価</th>
                                                <th>包装納入価</th>
                                                <th>値引率(納入)</th>
                                                <th>業者</th>
                                                <th>施設</th>
                                                <th>タスク</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($viewdata->get('list') as $adoption)
                                            <tr>
                                                <td>{{ $loop->iteration + ( $viewdata->get('page_count') * ($viewdata->get('pager')->current -1) ) }}</td>
                                                <!-- <td>{{ $adoption->getJancode() }}</td> -->
                                                <td>{{ $adoption->sales_packaging_code }}</td>
                                                <td>{{ $adoption->getMakerName() }}</td>
                                                <td>{{ $adoption->getMedicineName() }}</td>
                                                <td>{{ $adoption->getStandardUnit() }}</td>
                                                <td>{{ App\Helpers\Apply\getGenericProductDetailDivision($adoption->generic_product_detail_devision) }}</td>
                                                <td>{{ $adoption->getCoefficient() }}</td>
                                                <td>{{ number_format(round($adoption->medicine_price, 2), 2) }}</td><!-- 単位薬価 -->
                                                <!--td>@if (round($adoption->getUnitDeliveryPrice($adoption->getCoefficient(), $adoption->sales_price), 2) > 0){{ number_format(round($adoption->getUnitDeliveryPrice($adoption->getCoefficient(), $adoption->sales_price), 2), 2) }} @endif</td-->
                                                <td>@if ($adoption->getPackUnitPriceForDetail($adoption->getCoefficient(), $adoption->medicine_price) > 0){{ $adoption->getPackUnitPriceForDetail($adoption->getCoefficient(), $adoption->medicine_price) }} @endif</td><!-- 包装薬価 -->
                                                <td>@if ($adoption->sales_price > 0){{ number_format($adoption->sales_price, 2) }} @endif</td><!-- 包装納入価 -->
@if (empty($adoption->medicine_id))
                                                <td>{{ $adoption->getSalesRate($adoption->sales_price,  ($adoption->pa_pack_unit_price * $adoption->getCoefficient())) }}</td>
@else
                                                <td>{{ $adoption->getSalesRate($adoption->sales_price,  ($adoption->medicine_price * $adoption->getCoefficient())) }}</td>
@endif
                                                <td>{{ $adoption->trader_name }}</td>
                                                <td class="target-apply">{{ $adoption->facility_name }}</td>
                                                <td><a class="btn btn-primary btn-sm" href="{{ route('adoption.detail', $adoption->facility_medicine_id) }}?page={{ $viewdata->get('pager')->current }}">詳細・編集</a></td>
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
                            <div class="row mt15">
                                <div class="col-12 text-right">
                                    <a class="w300 btn btn-primary" href="{{ route('adoption.download') }}?page={{ $viewdata->get('pager')->current }}" download="採用品一覧_{{ date('Ymd') }}.csv">
                                        <div class="fas fa-pen mr10"></div>CSVダウンロード
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
@endif
@endsection

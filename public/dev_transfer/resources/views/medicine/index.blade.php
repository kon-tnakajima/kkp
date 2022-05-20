@extends('layouts.app')

@php($title = '標準薬品検索')

@php($category = 'medicine')

@php($breadcrumbs = ['HOME'=>'./','標準薬品検索'=>'javascript:void(0);'])

@section('content')

@if (!is_null($viewdata->get('list')))
<div class="card card-primary medicine">
    <div class="pt-0 card-body">
        <form class="form-horizontal" method="get" id="medicine" accept-charset="utf-8">
        <input type="hidden" data-search-item="is_search" value="1">
            <div class="row mt30">
                <div class="tar">
                    <a class="ml30 btn btn-default" href="{{ route('medicine.regist') }}">新規登録</a>
                    <a class="anchor anchor-clear-search" href="javascript:void(0);"><i class="fas fa-eraser mr10"></i>検索条件をクリア</a>
                </div>
            </div>
            <div class="row mt10">
                <div class="col-10 d-flex">
                    <div class="d-flex mr30 align-items-center block"><input class="form-control w400" type="search" data-search-item="search" placeholder="JANコード、商品名、メーカーで検索" value="{{ $viewdata->get('conditions')['search'] }}"></div>
                </div>
                <div class="col-2"><button class="w100p btn btn-primary btn-table-search" type="button"><i class="fas fa-search mr10"></i>検索する</button></div>
            </div>
            <div class="tac mt20 block"><a class="anchor anchor-accordion anchor-search-condition" href="#collapseExample" data-toggle="collapse" aria-expanded="{{ $viewdata->get('conditions')['is_condition'] }}" aria-controls="collapseExample">もっと検索条件を見る</a></div>
            <div class="collapse @if( $viewdata->get('conditions')['is_condition'] ) show @endif" id="collapseExample">
                <div class="row mt10">
                    <div class="col-4 d-flex">
                        <div class="d-flex mr10 align-items-center block">
                            <p class="mr10 fwb text">JANコード</p>
                            <div class="d-flex">
                                <div class="d-flex mr30 align-items-center block"><input class="form-control w200" type="search" value="{{ $viewdata->get('conditions')['jan_code'] }}" data-search-item="jan_code" placeholder=""></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-4 d-flex">
                        <div class="d-flex mr10 align-items-center block">
                            <p class="mr10 fwb text">商品名</p>
                            <div class="d-flex">
                                <div class="d-flex mr30 align-items-center block"><input class="form-control" type="search" data-search-item="name" value="{{ $viewdata->get('conditions')['name'] }}" placeholder=""></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-4 d-flex">
                        <div class="d-flex mr10 align-items-center block">
                            <p class="mr10 fwb text">一般名</p>
                            <div class="d-flex">
                                <div class="d-flex mr30 align-items-center block"><input class="form-control" type="search" data-search-item="popular_name" value="{{$viewdata->get('conditions')['popular_name']}}" placeholder=""></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt10">
                    <div class="col-2 d-flex">
                        <div class="d-flex mr30 align-items-center block">
                            <p class="w80 mr10 fwb text">剤型</p><select class="form-control" type="text" data-search-item="dosage_type_division">
                                <option value="">全て</option>
                            @foreach(App\Helpers\Apply\getDosage_type_division() as $key => $value)
                                <option value="{{$key}}" @if( $key == $viewdata->get('conditions')['dosage_type_division'] ) selected @endif>{{$value}}</option>
                            @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-6 d-flex">
                        <div class="d-flex mr30 align-items-center block">
                            <p class="w200 mr10 fwb text">オーナー区分</p><select class="form-control" type="text" data-search-item="owner_classification">
                            <option value="" @if($viewdata->get('conditions')['owner_classification'] == "") selected @endif>全て</option>
                                @foreach(App\Model\Medicine::OWNER_CLASSIFICATION_STR as $key => $value)
                                <option value="{{$key}}" @if($viewdata->get('conditions')['owner_classification'] == $key) selected @endif>{{$value}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
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
                <table class="table table-primary table-small" id="applies">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>JANコード</th>
                            <th>メーカー</th>
                            <th>商品名</th>
                            <th>規格単位</th>
                            <th>包装薬価</th>
                            <th>包装薬価係数</th>
                            <th>単位薬価（円）</th>
                            <th>タスク</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($viewdata->get('list') as $medicine)
                        <tr>
                            <td class="">{{ $loop->iteration + ( $viewdata->get('pager')->display_count * ($viewdata->get('pager')->current -1) ) }}</td>
                            <td class="">{{ $medicine->jan_code }}</td>
                            <td class="">{{ $medicine->maker_name }}</td>
                            <td class="">{{ $medicine->medicine_name }}</td>
                            <td class="">{{ $medicine->standard_unit }}</td>
                            <td class="">{{ number_format(round($medicine->pack_unit_price, 2),2) }}</td>
                            <td class="">{{ $medicine->coefficient }}</td>
                            <td class="">{{ number_format(round($medicine->price, 2),2) }}</td>
                            <td class="">
                                <a href="{{ route('medicine.detail', ['id '=> $medicine->medicine_id]) }}" class="btn btn-sm btn-primary">詳細・編集</button>
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

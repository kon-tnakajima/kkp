@extends('layouts.app')

@php($title = '施設グループ一覧')

@php($category = 'facility_group')

@php($breadcrumbs = ['HOME'=>'./','施設グループ一覧'=>'javascript:void(0);'])

@section('content')

@if (!is_null($viewdata->get('list')))
<div class="card card-primary facilitygroup">
    <div class="pt-0 card-body">
        <form class="form-horizontal" action="#" method="get" id="facilitygroup" accept-charset="utf-8">
        <input type="hidden" data-search-item="is_search" value="1">
            <div class="row mt30">
                <a class="ml30 btn btn-default" href="{{ route('facility.group.regist') }}">新規登録</a>
                <div class="col-12 tar"><a class="anchor anchor-clear-search" href="javascript:void(0);"><i class="fas fa-eraser mr10"></i>検索条件をクリア</a></div>
            </div>
            <div class="row mt10">
                <div class="col-10 d-flex">
                    <div class="d-flex mr30 align-items-center block"><input class="form-control w400" type="search" data-search-item="search" placeholder="コード、名称で検索" value="{{$viewdata->get('conditions')['search']}}"></div>
                </div>
                <div class="col-2"><button class="w100p btn btn-primary btn-table-search" type="button"><i class="fas fa-search mr10"></i>検索する</button></div>
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
                            <th>コード</th>
                            <th>名前</th>
                            <th>所属施設数</th>
                            <th>最終更新日</th>
                            <th>タスク</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($viewdata->get('list') as $facilitygroup)
                        <tr>
                            <td class="">{{ $loop->iteration }}</td>
                            <td class=" ">{{ $facilitygroup->code }}</td>
                            <td class="">{{ $facilitygroup->name }}</td>
                            <td class="">{{ $facilitygroup->cnt }}</td>
                            <td class="">{{ $facilitygroup->updated_at }}</td>
                            <td class="">
                                <a href="{{ route('facility.group.detail', ['id '=> $facilitygroup->id]) }}" class="btn btn-sm btn-primary">詳細・編集</button>
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

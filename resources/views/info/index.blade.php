@extends('layouts.app')

@php($title = 'ユーザー一覧')

@php($category = 'user')

@php($breadcrumbs = ['HOME'=>'./','お知らせ検索'=>'javascript:void(0);'])

@section('content')

{{ $viewdata->get('message') }}
@if (!is_null($viewdata->get('list')))
<div class="card card-primary">
    <div class="pt-0 card-body">
        <div class="row mt30">
            <div class="tar">
                <a class="ml30 btn btn-default" href="{{ route('info.add') }}">新規登録</a>
                <a class="anchor anchor-clear-search" href="javascript:void(0);"><i class="fas fa-eraser mr10"></i>検索条件をクリア </a>
            </div>
        </div>
        <div class="row mt10">
            <div class="col-10 d-flex">
                <div class="d-flex mr30 align-items-center block">
                    <input class="form-control w400" type="search" data-search-item="keyword" placeholder="キーワードで検索" value="{{ $viewdata->get('conditions')['keyword'] }}">
                </div>
            </div>
            <div class="col-2">
                <button class="w100p btn btn-primary btn-table-search" type="button"><i class="fas fa-search mr10"></i>検索する </button>
            </div>
        </div>
        <div class="row mt10">
            <div class="col-12 d-flex">
                <div class="d-flex mr30 align-items-center block">
                    <p class="mr20 fwb text">カテゴリ</p>
@foreach($viewdata->get('categories') as $key => $category)
                    <div class="custom-control custom-checkbox custom-control-inline">
                        <input class="custom-control-input validate input-checkbox" id="category{{ $key }}" name="category" data-search-item="category[]" data-search-type="checkbox" value="{{ $key }}" type="checkbox" @if (in_array($key, $viewdata->get('conditions')['category'])) checked  @endif>
                        <label class="custom-control-label" for="category{{ $key }}">{{ $category }}</label>
                    </div>
@endforeach
                </div>
            </div>
        </div>
        <div class="row mt10">
            <div class="col-2 d-flex">
                <div class="d-flex align-items-center block">
                    <input class="form-control ui-datepicker" type="search" data-search-item="start_date" placeholder="開始年月日" data-datepicker="true" name="start_date" id="start_date" value="{{ $viewdata->get('conditions')['start_date'] }}">
                </div>
            </div>
            <span class="ml0">〜</span>
            <div class="col-2 d-flex">
                <div class="d-flex align-items-center block">
                    <input class="form-control ui-datepicker" type="search" data-search-item="end_date" placeholder="終了年月日" data-datepicker="true" name="end_date" id="end_date" value="{{ $viewdata->get('conditions')['end_date'] }}">
                </div>
            </div>
        </div>

        <div class="mt30 pt15 block block-table-control">
            <div class="row">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <p class="fs16 text">{{ $viewdata->get('list')->count() }} 件 / 全{{ $viewdata->get('list')->total() }}件</p>
                    <div class="d-flex align-items-center block">
                        <div class="btn-group btn-group-toggle" data-toggle="buttons">
                            <label class="btn btn-default btn-search-count @if($viewdata->get('page_count') == 20) active @endif" data-search-count="20">20件表示</label>
                            <label class="btn btn-default btn-search-count @if($viewdata->get('page_count') == 50) active @endif" data-search-count="50">50件表示</label>
                            <label class="btn btn-default btn-search-count @if($viewdata->get('page_count') == 100) active @endif" data-search-count="100">100件表示</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt10">
            <div class="col-12">
                <table class="table table-primary table-small" id="infomations">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>タイトル</th>
                            <th>作成者</th>
                            <th>カテゴリ</th>
                            <th>最終更新日時</th>
                            <th>詳細・編集</th>
                        </tr>
                    </thead>
                    <tbody>

@foreach($viewdata->get('list') as $info)
                        <tr>
                            <td>{{ $info->id }}</td>
                            <td>{{ $info->title }}</td>
                            <td>@if (!is_null($info->getCreater)) {{ $info->getCreater->name }} @endif</td>
                            <td>{{ $info->getCategory() }}</td>
                            <td>{{ $info->updated_at }}</td>
                            <td><a class="btn btn-primary btn-sm" href="{{ route('info.detail', $info->id) }}">詳細・編集</a></td>
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

@extends('layouts.app')

@php($title = '施設一覧')

@php($category = 'facility')

@php($breadcrumbs = ['HOME'=>'./','施設一覧'=>'javascript:void(0);'])

@section('content')

@if (!is_null($viewdata->get('list')))
<div class="card card-primary facility">
    <div class="pt-0 card-body">
        <form class="form-horizontal" action="#" method="get" id="facility" accept-charset="utf-8">
        <input type="hidden" data-search-item="is_search" value="1">
            <div class="row mt30">
                <a class="ml30 btn btn-default" href="{{ route('facility.regist') }}">新規登録</a>
                <div class="col-12 tar"><a class="anchor anchor-clear-search" href="javascript:void(0);"><i class="fas fa-eraser mr10"></i>検索条件をクリア</a></div>
            </div>
            <div class="row mt10">
                <div class="col-10 d-flex">
                    <div class="d-flex mr30 align-items-center block"><input class="form-control w400" type="search" data-search-item="search" value="{{$viewdata->get('conditions')['search']}}" placeholder="施設コード、施設名、施設グループ名で検索"></div>
                </div>
                <div class="col-2"><button class="w100p btn btn-primary btn-table-search" type="button"><i class="fas fa-search mr10"></i>検索する</button></div>
            </div>
            <div class="row mt20">
                <div class="col-10 d-flex">
                    <div class="d-flex mr30 align-items-center block">
                        <p class="w180 mr10 fwb text">施設グループ</p>
                        <select class="form-control" type="text" data-search-item="facility_group">
                            <option value="">全て</option>
                            @foreach($viewdata->get('facility_groups') as $facility_group)
                                <option value="{{$facility_group->id}}" @if( $facility_group->id == $viewdata->get('conditions')['facility_group'] ) selected @endif>{{$facility_group->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="d-flex mr30 align-items-center block">
                        <p class="w100 mr10 fwb text">都道府県</p>
                        <select class="form-control" data-search-item="facility_prefecture">
                            <option value="">選択してください</option>
                            @foreach($viewdata->get('prefs') as $key => $value)
                                <option value="{{ $key }}" @if( $key == $viewdata->get('conditions')['facility_prefecture'] ) selected @endif>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="d-flex mr30 align-items-center block">
                        <p class="w150 mr10 fwb text">アクター</p>
                        <select class="form-control" type="text" data-search-item="actor">
                            <option value="">全て</option>
                            @foreach($viewdata->get('actors') as $actor)
                                <option value="{{$actor->id}}" @if( $actor->id == $viewdata->get('conditions')['actor'] ) selected @endif>{{$actor->name}}</option>
                            @endforeach
                        </select>
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
                            <th>コード</th>
                            <th>名前</th>
                            <th>住所</th>
                            <th>施設グループ</th>
                            <th>アクター</th>
                            <th>ユーザーグループ数</th>
                            <th>ユーザー数</th>
                            <th>最終更新日</th>
                            <th>タスク</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($viewdata->get('list') as $facility)
                        <tr>
                            <td class="">{{ $loop->iteration }}</td>
                            <td class="">{{ $facility->code }}</td>
                            <td class="">{{ $facility->name }}</td>
                            <td class="">{{ $facility->address }}</td>
                            <td class="">{{ $facility->group }}</td>
                            <td class="">{{ $facility->actor_name }}</td>
                            <td class="">{{ $facility->user_group_cnt }}</td>
                            <td class="">{{ $facility->user_cnt }}</td>
                            <td class="">{{ $facility->updated_at }}</td>
                            <td class="">
                                <a href="{{ route('facility.detail', ['id '=> $facility->id]) }}" class="btn btn-sm btn-primary">詳細・編集</button>
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

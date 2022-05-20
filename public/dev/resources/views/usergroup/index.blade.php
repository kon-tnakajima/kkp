@extends('layouts.app')

@php($title = 'ユーザグループ一覧')

@php($category = 'group')

@php($breadcrumbs = ['HOME'=>'./','ユーザグループ一覧'=>'javascript:void(0);'])

@section('content')

{{ $viewdata->get('message') }}
@if (!is_null($viewdata->get('list')))
<div class="card card-primary">
    <div class="pt-0 card-body">
        <div class="row mt30">
            <div class="col-8 tal"><a class="anchor anchor-clear-search" href="javascript:void(0);"><i class="fas fa-eraser mr10"></i>検索条件をクリア</a></div>
        </div>
        <div class="row">
            <div class="col-4 d-flex">
                <div class="d-flex align-items-center block">
                    <p class="w150 mr10 fwb text">名称</p>
                    <div class="d-flex">
                        <div class="d-flex w200p align-items-center block"><input class="form-control" type="search" data-search-item="user_group_name" value="{{$viewdata->get('conditions')['user_group_name']}}" placeholder="名称・正式名称"></div>
                    </div>
                </div>
            </div>
            <div class="col-4 d-flex">
                <div class="d-flex mr10 align-items-center block">
                    <p class="w100 mr10 fwb text">種別</p>
                    <div class="d-flex">
                        <select class="w180 form-control" data-search-item="user_group_type">
                            <option value="" @if($viewdata->get('conditions')['user_group_type'] == "") selected @endif>全て</option>
@foreach($viewdata->get('group_types') as $group)
                            <option value="{{$group->name}}" @if( $group->name == $viewdata->get('conditions')['user_group_type'] ) selected @endif>{{$group->name}}</option>
@endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-2"><button class="w100p btn btn-primary btn-table-search" type="button"><i class="fas fa-search mr10"></i>検索する</button></div>
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
                <table class="table table-primary table-small" id="user_groups">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>名称</th>
                            <th>正式名称</th>
                            <th>種別</th>
                            <th>最終更新日</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>

@foreach($viewdata->get('list') as $userGroup)
                        <tr>
                            <td>{{ $userGroup->id }}</td>
                            <td>{{ $userGroup->name }}</td>
                            <td>{{ $userGroup->formal_name }}</td>
                            <td>{{ $userGroup->group_type }}</td>
                            <td>{{ $userGroup->updated_at }}</td>
                            <td>
                                <a class="btn btn-primary btn-sm" href="{{ route('usergroup.detail', $userGroup->id) }}">グループ編集</a>
                                <a class="btn btn-primary btn-sm" href="{{ route('user.group.index', ['user_group_id' => $userGroup->id]) }}">ユーザ編集</a>
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

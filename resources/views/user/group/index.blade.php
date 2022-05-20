@extends('layouts.app')

@php($title = 'ユーザ一覧')

@php($category = 'group')

@php($breadcrumbs = ['HOME'=>'./', 'ユーザグループ一覧' => route('usergroup.index'), 'ユーザ一覧'=>'javascript:void(0);'])

@section('content')

{{ $viewdata->get('message') }}
@if (!is_null($viewdata->get('list')))
<div class="card card-primary">
    <div class="pt-0 card-body">
        <form class="form-horizontal h-adr" id="user-role-edit" action="{{ route('user.group.edit') }}?ug_page={{$viewdata->get('ug_page')}}" method="post" accept-charset="utf-8">
        <input type="hidden" data-search-item="user_group_id" name="user_group_id" value="{{$viewdata->get('user_group_id')}}">
            @csrf
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
                <table class="table table-primary table-small" id="applies">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>名前</th>
                            <th>メールアドレス</th>
                            <th>サブID</th>
                            <th>最終更新日</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>

@foreach($viewdata->get('list') as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->sub_id }}</td>
                            <td>{{ $user->updated_at }}</td>
                            <td>
@if($viewdata->get('isAdmin') === true)
                                <select name="roles[{{ $user->id }}]">
                                    <option value="">選択してください
    @foreach($viewdata->get('roles') as $role)
                                    <option value="{{ $role['role_key_code'] }}" @if($user->role_key_code === $role['role_key_code']) selected @endif>{{ $role['role_key_code'] }}
    @endforeach
                                </select>
@elseif(Auth::id() === $user->id)
                                <select name="roles[{{ $user->id }}]">
                                    <option value="">選択してください
    @foreach($viewdata->get('roles') as $role)
                                    <option value="{{ $role['role_key_code'] }}" @if($user->role_key_code === $role['role_key_code']) selected @endif>{{ $role['role_key_code'] }}
    @endforeach
                                </select>
@else
                                {{ $user->role_key_code }}
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
        </form>
    </div>
    <div class="d-flex justify-content-around pt15 pb15 card-footer"><a class="btn btn-default btn-lg" href="{{ route('usergroup.index') }}?page={{$viewdata->get('ug_page')}}"><i class="fas fa-chevron-left mr10"></i>ユーザグループ一覧へ戻る</a>
        <div class="block">
            <button class="mr30 btn btn-primary btn-lg" type="button" data-toggle="modal" data-target="#modal-action" data-form="#user-role-edit">
                <div class="fas fa-pen mr10"></div>登録する
            </button>
        </div>
    </div>
</div>
<div class="fade modal" id="modal-action" role="dialog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ユーザグループ更新</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="tac text">ユーザグループのロールを更新してよろしいでしょうか？</p>
            </div>
            <div class="modal-footer"><button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
                <button class="btn btn-primary" type="submit" data-form="#user-role-edit">
                    <div class="fas fa-pen mr10"></div>更新する
                </button>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

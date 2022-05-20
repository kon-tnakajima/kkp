@extends('layouts.app')

@php($title = 'ロール編集')

@php($category = 'role')

@php($breadcrumbs = ['HOME'=>'/', 'ロール一覧' => route('role.index'), 'ロール編集'=>'javascript:void(0);'])

@section('content')
@if ($errors->any())
入力にエラーがあります<br>
{{ $errors->first('email') }}
@endif
{{ $viewdata->get('errorMessage') }}
{{ $viewdata->get('message') }}
<div class="card card-primary">
    <div id="overlay">
        <div class="cv-spinner">
            <span class="spinner"></span>
        </div>
    </div>
    <div class="card-body">
        <form class="form-horizontal h-adr" id="role-edit" action="{{ route('role.edit', ['id' => $viewdata->get('detail')->id]) }}" method="post" accept-charset="utf-8">
            @csrf
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="name"> 名称</label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <input id="name" type="text" name="name" placeholder="" value="{{ old('name', $viewdata->get('detail')->name) }}">
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="key_code"> KEYコード</label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <input id="key_code" type="text" name="key_code" placeholder="" value="{{ old('key_code', $viewdata->get('detail')->key_code) }}" disabled="disabled">
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="group_type"> 種別</label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <input id="group_type" type="text" name="group_type" placeholder="" value="{{ old('group_type', $viewdata->get('detail')->group_type) }}" disabled="disabled">
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="description"> 説明</label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <textarea class="form-control" name='description' placeholder="" rows="3">{{ old('description', $viewdata->get('detail')->description) }}</textarea>
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="privileges"> 権限</label>
            <div class="col-sm-4">
                    <br>
                    権限登録一覧<br>
                    <select class="form-control validate" type="text" name="add_privieges[]" id="add_privieges" multiple size=10>
@foreach($viewdata->get('privieges') as $priviege)
    @if($priviege['use'] !== '')
                            <option value="{{ $priviege['key_code'] }}">{{ $priviege['key_code'] }}</option>
    @endif
@endforeach
                    </select>
                </div>
                <div class="col-sm-1">
                    <br><br><br><br><br>
                    <input type="button" id="right" name="right" value="≫" /><br><br>
                    <input type="button" id="left" name="left" value="≪" />
                </div>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <input type="text" type="search" data-search-item="priviege_name" id="priviege_name"><button id="search_priviege">検索</button><br>
                    権限未登録一覧<br>
                    <select class="form-control validate" type="text" name="privieges[]" id="privieges" multiple size=10>
@foreach($viewdata->get('privieges') as $priviege)
    @if($priviege['use'] === '')
                            <option value="{{ $priviege['key_code'] }}">{{ $priviege['key_code'] }}</option>
    @endif
@endforeach
                    </select>
                </div>
            </div>

@if($viewdata->get('list')->count())
        <div class="mt30 pt15 block block-table-control">
            <div class="row">
                <div class="col-12 mb20 d-flex">
                    <h4>権限情報一覧</h4>
                </div>
            </div>
            <div class="row">
                <div class="col-12 d-flex justify-content-between align-items-center"><a class="anchor anchor-accordion anchor-payment-state" href="#collapseExample" data-toggle="collapse" aria-expanded="false" aria-controls="collapseExample">詳細を表示する</a></div>
            </div>
            <div class="collapse {{ $viewdata->get('disp_privieges') }}" id="collapseExample" style="">
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
                                    <th>ユーザID</th>
                                    <th>メールアドレス</th>
                                    <th>サブID</th>
                                    <th>ユーザ名</th>
                                    <th>最終ログイン日時</th>
                                    <th>作成日時</th>
                                    <th>ユーザグループID</th>
                                    <th>ユーザグループ名</th>
                                    <th>グループキー</th>
                                    <th>グループ区分</th>
                                    <th>ロールキー</th>
                                    <th>権限キー</th>
                                </tr>
                            </thead>
                            <tbody>
        @foreach($viewdata->get('list') as $privilege)
                                <tr>
                                    <td>{{ $loop->iteration + ( $viewdata->get('page_count') * ($viewdata->get('pager')->current -1) ) }}</td>
                                    <td>{{ $privilege->user_id }}</td>
                                    <td>{{ $privilege->email }}</td>
                                    <td>{{ $privilege->sub_id }}</td>
                                    <td>{{ $privilege->name }}</td>
                                    <td>{{ $privilege->last_login_at }}</td>
                                    <td>{{ $privilege->created_at_user }}</td>
                                    <td>{{ $privilege->user_group_id }}</td>
                                    <td>{{ $privilege->user_group_name }}</td>
                                    <td>{{ $privilege->group_key }}</td>
                                    <td>{{ $privilege->group_type }}</td>
                                    <td>{{ $privilege->role_key_code }}</td>
                                    <td>{{ $privilege->privilege_key_code }}</td>
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
        </form>
    </div>
    <div class="d-flex justify-content-around pt15 pb15 card-footer"><a class="btn btn-default btn-lg" href="{{ route('role.index') }}?page={{ $viewdata->get('detail')->page }}"><i class="fas fa-chevron-left mr10"></i>ロール一覧へ戻る</a>
        <div class="block">
            <button class="mr30 btn btn-primary btn-lg" type="button" data-toggle="modal" data-target="#modal-action">
                <div class="fas fa-pen mr10"></div>更新する
            </button>
            <button class="btn btn-danger btn-lg" type="button" data-toggle="modal" data-target="#modal-delete">
                <div class="fas fa-ban mr10"></div>削除する
            </button>
        </div>
    </div>
</div>

<div class="fade modal" id="modal-delete" role="dialog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ロールの削除</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="tac text">このロールを削除してよろしいでしょうか？</p>
            </div>
            <div class="modal-footer"><button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
                <button class="btn btn-primary" type="button" onClick="location.href='{{ route('role.delete', ['id' => $viewdata->get('detail')->id]) }}'">
                    <div class="fas fa-pen mr10"></div>削除する
                </button>
            </div>
        </div>
    </div>
</div>

<div class="fade modal" id="modal-action" role="dialog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ロール編集</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="tac text">このロールを更新してよろしいでしょうか？</p>
            </div>
            <div class="modal-footer"><button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
                <button class="btn btn-primary" type="submit" data-form="#role-edit">
                    <div class="fas fa-pen mr10"></div>更新する
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

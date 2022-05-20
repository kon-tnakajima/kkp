@extends('layouts.app')

@php($title = '利用申請詳細')

@php($category = 'system')

@php($breadcrumbs = ['HOME'=>'/', '利用申請一覧' => route('account.index'), '利用申請詳細'=>'javascript:void(0);'])

@section('content')
@if ($errors->any())
入力にエラーがあります<br>
@foreach ($errors->all() as $message)
{{ $message }}
@endforeach
@endif
{{ $viewdata->get('errorMessage') }}
{{ $viewdata->get('message') }}
<div class="card card-primary">
    <form class="form-horizontal" action="{{ route('account.permission', ['id '=> $viewdata->get('detail')->id]) }}" method="post" id="account-permission" accept-charset="utf-8">
        @csrf

        <div class="card-body">
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="name"> 名前</label>
                <div class="col-sm-4">
                    <p>{{ $viewdata->get('detail')->name }}</p>
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="email"> メールアドレス</label>
                <div class="col-sm-4">
                    <p>{{ $viewdata->get('detail')->email }}</p>
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="user_group_id"> ユーザグループID</label>
                <div class="col-sm-4">
                    <p>{{ $viewdata->get('detail')->user_group_id }}</p>
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="user_group_name"> ユーザグループ名</label>
                <div class="col-sm-4">
                    <p>{{ $viewdata->get('detail')->user_group_name }}</p>
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="remarks"> 通信欄</label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <textarea class="form-control" name='remarks' placeholder="通信欄" rows="2">{{ $viewdata->get('detail')->remarks }}</textarea>
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="sub_id"> サブID</label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <input class="form-control" type="text" name="sub_id" id="sub_id" placeholder="サブID" value="">
                </div>
            </div>
<!--
@if ($viewdata->get('list')->count())
            <div class="mt30 pt15 block block-table-control">
                <div class="row">
                    <div class="col-12 mb20 d-flex">
                        <h4>関連情報</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 d-flex justify-content-between align-items-center"><a class="anchor anchor-accordion anchor-payment-state" href="#collapseExample" data-toggle="collapse" aria-expanded="false" aria-controls="collapseExample">詳細を表示する</a></div>
                </div>
                <div class="collapse" id="collapseExample" style="">
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
                <div class="row">
                    <div class="col-12">
                        <table class="table table-primary table-small" id="statement">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>サブID</th>
                                    <th>ユーザグループID</th>
                                    <th>ユーザグループ名</th>
                                    <th>メール登録日</th>
                                </tr>
                            </thead>
                            <tbody>
    @foreach($viewdata->get('list') as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->sub_id }}</td>
                                    <td>{{ $user->user_group_id }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email_verified_at }}</td>
                                </tr>
    @endforeach
                            </tbody>
                        </table>
                    </div>
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
@endif
-->
    <div class="d-flex justify-content-around pt15 pb15 card-footer"><a class="btn btn-default btn-lg" href="{{ route('account.index') }}"><i class="fas fa-chevron-left mr10"></i>利用申請一覧へ戻る</a>
        <div class="block">
            <button class="mr30 btn btn-primary btn-lg" type="button" data-toggle="modal" data-target="#modal-action">
                <div class="fas fa-pen mr10"></div>許可する
            </button>
            <button class="btn btn-danger btn-lg" type="button" data-toggle="modal" data-target="#modal-rejection">
                <div class="fas fa-ban mr10"></div>却下する
            </button>
        </div>
    </div>
    </form>
</div>
<div class="fade modal" id="modal-rejection" role="dialog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">利用申請却下確認</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="tac text">この利用申請を却下してよろしいでしょうか？</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
                    <button class="btn btn-danger" type="button" onClick="location.href='{{ route('account.rejection', ['id' => $viewdata->get('detail')->id]) }}'">
                    <div class="fas fa-ban mr10"></div>却下する
                </button>
            </div>
        </div>
    </div>
</div>

<div class="fade modal" id="modal-action" role="dialog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">利用申請許可</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="tac text">この利用申請を許可してよろしいでしょうか？</p>
            </div>
            <div class="modal-footer"><button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
                <button class="btn btn-primary" type="submit" data-form="#account-permission">
                    <div class="fas fa-pen mr10"></div>許可する
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

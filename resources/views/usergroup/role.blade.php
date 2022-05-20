@extends('layouts.app')

@php($title = 'ロール設定')

@php($category = 'user')

@php($breadcrumbs = ['HOME'=>'/', 'ユーザグループ一覧' => route('usergroup.index'), 'ロール設定'=>'javascript:void(0);'])

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
    <div class="card-body">
        <form class="form-horizontal h-adr" id="usergroup-edit" action="{{ route('usergroup.setting', ['id' => $viewdata->get('detail')->id]) }}" method="post" accept-charset="utf-8">
            @csrf
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="name"> 名称<span class="badge badge-danger ml-2">必須</span></label>
                <div class="col-sm-4">
                    {{ $viewdata->get('detail')->name }}
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="formal_name"> 正式名称<span class="badge badge-danger ml-2">必須</span></label>
                <div class="col-sm-4">
                    {{ $viewdata->get('detail')->formal_name }}
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="group_type"> 種別<span class="badge badge-danger ml-2">必須</span></label>
                <div class="col-sm-4">
                    {{ $viewdata->get('detail')['group_type'] }}
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="zip"> 郵便番号</label>
                <div class="col-sm-4">
                    {{ $viewdata->get('detail')->zip }}
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="address1"> 住所1</label>
                <div class="col-sm-4">
                    {{ $viewdata->get('detail')->address1 }}
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="address2"> 住所2</label>
                <div class="col-sm-4">
                    {{  $viewdata->get('detail')->address2 }}
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="tel"> 電話番号</label>
                <div class="col-sm-4">
                    {{ $viewdata->get('detail')->tel }}
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="fax"> FAX</label>
                <div class="col-sm-4">
                    {{ $viewdata->get('detail')->fax }}
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="role_user"> ユーザ役割</label>
                <div class="col-sm-4">
                    <table class="table table-primary table-small" id="user_groups">
                        <thead>
                            <tr>
                                <th>ユーザ一覧</th>
                                <th>役割</th>
                            </tr>
                        </thead>
                    <tbody>
@foreach($viewdata->get('users') as $user)
                        <tr>
                            <td>{{ $user->name }}</td>

                            <td>
                                <select class="form-control" name="role_{{$user->id}}[]">
@foreach($viewdata->get('roles') as $role)
                                    <option value="{{$role->role_key_code}}">{{$role->role_key_code}}</option>
@endforeach
                                </select>
                            </td>
                        </tr>
@endforeach
                    </tbody>
                    </table>
                </div>
            </div>
        </form>
    </div>
    <div class="d-flex justify-content-around pt15 pb15 card-footer"><a class="btn btn-default btn-lg" href="{{ route('usergroup.index') }}"><i class="fas fa-chevron-left mr10"></i>ユーザグループ一覧へ戻る</a>
        <div class="block">
            <button class="mr30 btn btn-primary btn-lg" type="button" data-toggle="modal" data-target="#modal-action" data-form="usergroup-delete">
                <div class="fas fa-pen mr10"></div>更新する
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
                <p class="tac text">このユーザグループを更新してよろしいでしょうか？</p>
            </div>
            <div class="modal-footer"><button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
                <button class="btn btn-primary" type="submit" data-form="#usergroup-edit">
                    <div class="fas fa-pen mr10"></div>更新する
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

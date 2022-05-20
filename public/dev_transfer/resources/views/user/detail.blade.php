@extends('layouts.app')

@php($title = 'ユーザー詳細')

@php($category = 'user')

@php($breadcrumbs = ['HOME'=>'/', 'ユーザー一覧' => route('user.index'), 'ユーザー詳細'=>'javascript:void(0);'])

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
        <form class="form-horizontal h-adr" id="user-edit" action="{{ route('user.edit', ['id' => $viewdata->get('detail')->id]) }}?ug_page={{ $viewdata->get('detail')->ug_page }}" method="post" accept-charset="utf-8">
            @csrf
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="name"> 名前<span class="badge badge-danger ml-2">必須</span></label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div><input class="form-control validate" id="name" type="text" name="name" placeholder="" data-validate="empty" value="{{ old('name', $viewdata->get('detail')->name) }}">
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="email"> メールアドレス<span class="badge badge-danger ml-2">必須</span></label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div><input class="form-control validate" id="email" type="text" name="email" placeholder="" data-validate="empty" value="{{ old('email', $viewdata->get('detail')->email) }}">
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="sub_id"> サブID</label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div><input class="form-control validate" id="sub_id" type="text" name="sub_id" placeholder="" value="{{ old('sub_id', $viewdata->get('detail')->sub_id) }}">
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="usergroup"> デフォルトグループ<span class="badge badge-danger ml-2">必須</span></label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div><select class="form-control validate" type="text" data-search-item="user_group_id" data-validate="empty" name="user_group_id" id="user_group_id">
                        <option value="">選択してください</option>
                        @foreach($viewdata->get('groups') as $group)
                        <option value="{{ $group->id }}" @if($group->id === $viewdata->get('detail')->primary_user_group_id) selected @endif>{{ $group->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="headquarters"> 本部グループ<span class="badge badge-danger ml-2">必須</span></label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div><select class="form-control validate" type="text" data-search-item="headquarters" data-validate="empty" name="headquarters" id="headquarters">
                        <option value="">選択してください</option>
                        @foreach($viewdata->get('headquarters') as $group)
                        <option value="{{ $group->id }}" @if($group->id === $viewdata->get('detail')->primary_honbu_user_group_id) selected @endif>{{ $group->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
<!--
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="passwordreset"> パスワードリセット</label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <div class="d-flex mr30 align-items-center block">
                        <div class="custom-control custom-checkbox custom-control-inline">
                            <input class="custom-control-input validate input-checkbox" id="passwordreset" name="passwordreset" data-search-item="received" data-search-type="checkbox" value="1" type="checkbox"><label class="custom-control-label" for="passwordreset"></label>
                        </div>
                    </div>
                </div>
            </div>
-->
<!--
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="password"> パスワード</label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div><input class="form-control validate" id="password" type="password" name="password" placeholder="" value="{{ old('password') }}">
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="password2"> パスワード確認</label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div><input class="form-control validate" id="password_confirmation" type="password" name="password_confirmation" placeholder="" value="{{ old('password_confirmation') }}">
                </div>
            </div>
-->
            <!-- 施設は出さない
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="facility"> 施設<span class="badge badge-danger ml-2">必須</span></label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div><select class="form-control" type="text" data-search-item="facility_id" id="facility_id" name="facility_id">
                        <option value="">選択してください</option>
                        <option value="1">A施設</option>
                        <option value="2">B施設</option>
                        <option value="3">A本部</option>
                        <option value="4">文化連</option>
                    </select>
                </div>
            </div>
            -->
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="received"> 受信メール<span class="badge badge-success ml-2">任意</span></label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <div class="d-flex mr30 align-items-center block">
                        <div class="custom-control custom-checkbox custom-control-inline"><input class="custom-control-input validate input-checkbox" id="is_adoption_mail" name="is_adoption_mail" data-search-item="received" data-search-type="checkbox" value="1" type="checkbox" {{ checked(old('is_adoption_mail', $viewdata->get('detail')->is_adoption_mail), '1') }}><label class="custom-control-label" for="is_adoption_mail">採用承認</label></div>
                        <div class="custom-control custom-checkbox custom-control-inline"><input class="custom-control-input validate input-checkbox" id="is_claim_mail" name="is_claim_mail" data-search-item="received" data-search-type="checkbox" value="1" type="checkbox" {{ checked(old('is_claim_mail', $viewdata->get('detail')->is_claim_mail), '1') }}><label class="custom-control-label" for="is_claim_mail">請求登録</label></div>
                    </div>
                </div>
            </div>

@if(Auth::user()->id !== $viewdata->get('detail')->id)
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="stop"> ユーザ停止</label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <div class="d-flex mr30 align-items-center block">
                        <div class="custom-control custom-checkbox custom-control-inline">
                            <input class="custom-control-input validate input-checkbox" id="stop" name="stop" data-search-item="received" data-search-type="checkbox" value="1" type="checkbox" @if($viewdata->get('detail')->deleter) checked @endif><label class="custom-control-label" for="stop"></label>
                        </div>
                    </div>
                </div>
            </div>
@endif
            <!--
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="received"> Googleアカウント連携<span class="badge badge-success ml-2">任意</span></label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <div class="d-flex mr30 align-items-center block">
                        <div class="custom-control custom-checkbox custom-control-inline"><input class="custom-control-input validate input-checkbox" id="is_google_account" name="is_google_account" data-search-item="received" data-search-type="checkbox" value="1" type="checkbox" {{ checked(old('is_google_account', $viewdata->get('detail')->is_google_account), '1') }}><label class="custom-control-label" for="is_google_account">連携する</label></div>
                    </div>
                </div>
            </div>
            -->
<!--
            <div class="mt30 pt15 block block-table-control">
                <div class="row">
                    <div class="col-12 mb20 d-flex">
                        <h4>ユーザグループ設定</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 d-flex justify-content-between align-items-center"><a class="anchor anchor-accordion anchor-payment-state" href="#collapseExample" data-toggle="collapse" aria-expanded="false" aria-controls="collapseExample">詳細を表示する</a></div>
                </div>
                <div class="collapse" id="collapseExample" style="">
                    <div class="row">
                        <div class="col-12">
                            <table class="table table-primary table-small" id="statement">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                        <select id="leftList" size="3">
            <option value="1" id="1">メロン</option>
            <option value="2" id="2">スイカ</option>
            <option value="3" id="3">バナナ</option>
        </select>

                                        </td>
                                        <td>
                                        <button type="button" id="left-btn">右へ</button><br>
        <button type="button" id="right-btn">左へ</button>
                                        </td>
                                        <td>
<select id="rightList" size="3">
        </select>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
-->
        </form>
    </div>

    <div class="d-flex justify-content-around pt15 pb15 card-footer"><a class="btn btn-default btn-lg" href="{{ route('user.index') }}?ug_page={{ $viewdata->get('detail')->ug_page }}"><i class="fas fa-chevron-left mr10"></i>ユーザー検索へ戻る</a>
        <div class="block">
            <button class="mr30 btn btn-primary btn-lg" type="button" data-toggle="modal" data-target="#modal-action">
                <div class="fas fa-pen mr10"></div>更新する
            </button>
            <button class="btn btn-danger btn-lg" type="button" data-toggle="modal" data-target="#modal-reset">
                <div class="fas fa-ban mr10"></div>パスワードリセットする
            </button>
        </div>
    </div>
</div>
<div class="fade modal" id="modal-action" role="dialog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ユーザー更新</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="tac text">このユーザーを更新してよろしいでしょうか？</p>
            </div>
            <div class="modal-footer"><button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
                <button class="btn btn-primary" type="submit" data-form="#user-edit">
                    <div class="fas fa-pen mr10"></div>更新する
                </button>
            </div>
        </div>
    </div>
</div>

<div class="fade modal" id="modal-reset" role="dialog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">パスワードリセット確認</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="tac text">パスワードリセットしてよろしいでしょうか？</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
                    <button class="btn btn-danger" type="button" onClick="location.href='{{ route('user.reset', ['id' => $viewdata->get('detail')->id]) }}'">
                    <div class="fas fa-ban mr10"></div>パスワードリセットする
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(function() {
    //右へ要素を追加する。
    $('#left-btn').click(rightMove);

    //カテゴリ削除イベント
    $('#right-btn').click(leftMove);
});

//右へ要素を追加する。
function rightMove() {

    //左リストで選択している要素のIDを取得する。
    value = $("#leftList").children("option:selected").val();

    //要素が選択されている場合、以下を行う。
    if(value !== void 0){

        //左リストで選択している要素を取得する。
        element = $("#leftList").children("option:selected").html();

        //選択した要素を左リストから削除する。
        $("#" + value).remove();

        //選択した要素を、右リストへ追加する。
        $("#rightList").append('<option value = ' + value + ' id = ' + value + '>' + element + '</option>');

        //選択状態を開放する。
        $("#rightList").removeAttr("option:selected");
    }
}

//左へ要素を追加する。
function leftMove() {

    //右リストで選択している要素のIDを取得する。
    value = $("#rightList").children("option:selected").val();

    //要素が選択されている場合、以下を行う。
    if(value !== void 0){

        //右リストで選択している要素を取得する。
        element = $("#rightList").children("option:selected").html();

        //選択した要素を右リストから削除する。
        $("#" + value).remove();

        //選択した要素を、左リストへ追加する。
        $("#leftList").append('<option value = ' + value + ' id = ' + value + '>' + element + '</option>');

        //選択状態を開放する。
        $("#leftList").removeAttr("option:selected");
    }
}

</script>

@endsection

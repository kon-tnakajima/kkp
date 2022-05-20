@extends('layouts.app')

@php($title = '施設登録')

@php($category = 'facility')

@php($breadcrumbs = ['HOME'=>'/','施設一覧'=>'/facility/','施設登録'=>'javascript:void(0);'])

@section('content')
@if ($errors->any())
入力にエラーがあります<br>
@endif
@if ( is_null($errorMessage) === false )
{{ $errorMessage }}
@endif
@if ( is_null($message) === false )
{{ $message }}
@endif
<div class="card card-primary facility_group_detail">
    <form class="form-horizontal h-adr" action="{{ route('facility.regexec') }}" method="post" id="facility-group-edit" accept-charset="utf-8">
        @csrf
        <div class="card-body">
            <div class="form-group validateGroup row"><label class="col-sm-3 col-form-label" for="code">コード<span class="badge badge-danger ml-2">必須</span></label>
                <div class="col-sm-2">
                    <div class="error-tip">
                        <div class="error-tip-inner">{{ $errors->first('code') }}</div>
                    </div>
                    <input class="form-control validate code" type="text" name="code" id="code" placeholder="" data-validate="empty" value="">
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="name">名前<span class="badge badge-danger ml-2">必須</span></label>
                <div class="col-sm-2">
                    <div class="error-tip">
                        <div class="error-tip-inner">{{ $errors->first('name') }}</div>
                    </div>
                    <input class="form-control validate name" type="text" name="name" id="name" placeholder="" data-validate="empty" value="">
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 col-form-label" for="formal_name">正式名<span class="badge badge-success ml-1">任意</span></label>
                <div class="col-sm-2">
                    <div class="error-tip">
                        <div class="error-tip-inner">{{ $errors->first('formal_name') }}</div>
                    </div>
                    <input class="form-control validate code" type="text" name="formal_name" id="formal_name" placeholder="" value="">
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="actor_id">アクター<span class="badge badge-danger ml-2">必須</span></label>
                <div class="col-sm-2">
                    <div class="error-tip">
                        <div class="error-tip-inner">{{ $errors->first('actor_id') }}</div>
                    </div>
                    <select class="form-control validate" data-validate="empty" name="actor_id" id="actor_id">
                        <option value="">選択してください</option>
                        <option value="1">病院</option>
                        <option value="2">本部</option>
                        <option value="3">文化連</option>
                    </select>
                </div>
                <div class="w-100">
                    <div class="col-sm-9 offset-sm-3">
                        <p class="form-description">※ 文化連は1つしか登録できません。また、本部は各都道府県で1つしか登録できません。</p>
                        <p class="form-description">※ 本部を選ぶと所属は文化連に、病院を選ぶと自動で所在都道府県の本部に所属します。</p>
                        <!--p class="form-description">※ 業者を選んだ場合は、<a class="anchor" href="/facility/relation.html">病院・業者設定</a>から関係性を登録する必要があります。</p-->
                    </div>
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="facility_group">施設グループ<span class="badge badge-success ml-1">任意</span></label>
                <div class="col-sm-2">
                    <div class="error-tip">
                        <div class="error-tip-inner">{{ $errors->first('facility_group_id') }}</div>
                    </div>
                    <select class="form-control" name="facility_group_id" id="facility_group_id">
                        <option value="" ></option>
@foreach($facility_groups as $f_group)
                        <option value="{{ $f_group->id }}">{{ $f_group->name }}</option>
@endforeach
                    </select>
                </div>
                <div class="w-100">
                    <div class="offset-sm-3 col-sm-9">
                        <p class="form-description">※ 施設グループに対象がない場合は<a class="anchor" href="{{ route('facility.group.regist') }}">こちら</a>から新規で登録をしてください。</p>
                    </div>
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="parent_facility_id">所属施設<span class="badge badge-success ml-1">任意</span></label>
                <div class="col-sm-2">
                    <div class="error-tip">
                        <div class="error-tip-inner">{{ $errors->first('parent_facility_id') }}</div>
                    </div>
                    <select class="form-control validate" name="parent_facility_id" id="parent_facility_id">
                        <option value="">選択してください</option>
@foreach($facilities as $facility)
                        <option value="{{ $facility->id }}">{{ $facility->name }}</option>
@endforeach
                    </select>
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 col-form-label" for="code">郵便番号<span class="badge badge-success ml-1">任意</span></label>
                <div class="col-sm-2">
                    <div class="error-tip">
                        <div class="error-tip-inner">{{ $errors->first('zip') }}</div>
                    </div>
                    <input class="form-control validate facility_zip p-postal-code remove-hyphen zen2han" type="tel" name="zip" id="zip" placeholder=""  data-validate="number nospace nosymbol len-7" data-target-prefecture="facility_prefecture" data-target-address="facility_address" value="">
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 col-form-label" for="facility_prefecture">都道府県<span class="badge badge-success ml-1">任意</span></label>
                <div class="col-sm-2">
                    <div class="error-tip">
                        <div class="error-tip-inner">{{ $errors->first('prefecture') }}</div>
                    </div><select class="form-control validate p-region facility_prefecture" name="prefecture" id="prefecture">
                            <option value="">選択してください</option>
                            @foreach($prefs as $key => $value)
                                <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 col-form-label" for="code">住所<span class="badge badge-success ml-1">任意</span></label>
                <div class="col-sm-2">
                    <div class="error-tip">
                        <div class="error-tip-inner">{{ $errors->first('address') }}</div>
                    </div>
                    <input class="form-control validate p-locality p-street-address p-extended-address" type="text" name="address" id="address" placeholder="" value="">
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 col-form-label" for="tel">電話番号<span class="badge badge-success ml-1">任意</span></label>
                <div class="col-sm-2">
                    <div class="error-tip">
                        <div class="error-tip-inner">{{ $errors->first('tel') }}</div>
                    </div>
                    <input class="form-control validate" type="text" name="tel" id="tel" placeholder="" value="">
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 col-form-label" for="fax">FAX番号<span class="badge badge-success ml-1">任意</span></label>
                <div class="col-sm-2">
                    <div class="error-tip">
                        <div class="error-tip-inner">{{ $errors->first('fax') }}</div>
                    </div>
                    <input class="form-control validate" type="text" name="fax" id="fax" placeholder="" value="">
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 col-form-label">オンライン<span class="badge badge-danger ml-1">必須</span></label>
                <div class="col-sm-9 pt10">
                    <div class="error-tip">
                        <div class="error-tip-inner">{{ $errors->first('is_online') }}</div>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline"><input class="custom-control-input form-control validate input-radio" id="access_online" name="is_online" value="1" data-validate="checkboxempty" type="radio"><label class="custom-control-label" for="access_online">やり取り可能</label></div>
                    <div class="custom-control custom-radio custom-control-inline"><input class="custom-control-input form-control validate input-radio" id="access_offline" name="is_online" value="0" data-validate="checkboxempty" type="radio"><label class="custom-control-label" for="access_offline">やり取り不可</label></div>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-around pt15 pb15 mt15">
            <a class="btn btn-default btn-lg" href="{{ route('facility.index') }}"><i class="fas fa-chevron-left mr10"></i>施設一覧へ戻る</a>
            <div class="block">
                <button class="mr30 btn btn-primary btn-lg" type="button" data-toggle="modal" data-target="#modal-action">
                    <div class="fas fa-pen mr10"></div>登録する
                </button>
                <button class="btn btn-danger btn-lg" type="button" data-toggle="modal" data-target="#modal-delete">
                    <div class="fas fa-ban mr10"></div>削除する
                </button>
            </div>
        </div>
        <div class="fade modal" id="modal-action" role="dialog" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">施設登録</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <p class="tac text">この施設を登録してよろしいでしょうか？</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
                        <button class="btn btn-primary" type="submit" data-form="#facility-group-edit">
                            <div class="fas fa-pen mr10"></div>登録する
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@endsection

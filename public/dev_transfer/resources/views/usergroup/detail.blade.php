@extends('layouts.app')

@php($title = 'グループ詳細')

@php($category = 'group')

@php($breadcrumbs = ['HOME'=>'/', 'ユーザグループ一覧' => route('usergroup.index'), 'グループ詳細'=>'javascript:void(0);'])

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
    <div id="overlay">
        <div class="cv-spinner">
            <span class="spinner"></span>
        </div>
    </div>
    <div class="card-body">
        <form class="form-horizontal h-adr" id="usergroup-edit" action="{{ route('usergroup.edit', ['id' => $viewdata->get('detail')->id]) }}" method="post" accept-charset="utf-8" enctype="multipart/form-data">
            @csrf
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="name"> 名称<span class="badge badge-danger ml-2">必須</span></label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div><input class="form-control validate" id="name" type="text" name="name" placeholder="" data-validate="empty" value="{{ old('name', $viewdata->get('detail')->name) }}">
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="formal_name"> 正式名称<span class="badge badge-danger ml-2">必須</span></label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <input class="form-control validate" id="formal_name" type="text" name="formal_name" placeholder="" data-validate="empty" value="{{ old('formal_name', $viewdata->get('detail')->formal_name) }}">
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="group_type"> 種別</label>
                <div class="col-sm-4">
                    {{ $viewdata->get('detail')->group_type }}
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="zip"> 郵便番号</label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <input class="form-control" id="zip" type="text" name="zip" placeholder="" value="{{ old('zip', $viewdata->get('detail')->zip) }}">
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="address1"> 住所1</label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <input class="form-control" id="address1" type="text" name="address1" placeholder="" value="{{ old('address1', $viewdata->get('detail')->address1) }}">
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="address2"> 住所2</label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <input class="form-control" id="address2" type="text" name="address2" placeholder="" value="{{ old('address2', $viewdata->get('detail')->address2) }}">
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="tel"> 電話番号</label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <input class="form-control" id="tel" type="text" name="tel" placeholder="" value="{{ old('tel', $viewdata->get('detail')->tel) }}">
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="fax"> FAX</label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <input class="form-control" id="fax" type="text" name="fax" placeholder="" value="{{ old('fax', $viewdata->get('detail')->fax) }}">
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="optional_key1_label"> 追加項目１ラベル</label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <input length="20" id="optional_key1_label" type="text" name="optional_key1_label" placeholder="" value="{{ old('optional_key1_label', $viewdata->get('detail')->optional_key1_label) }}">
                    <input type="checkbox" name="optional_key1_is_search_disp" value="1" @if ($viewdata->get('detail')->optional_key1_is_search_disp) checked @endif>検索条件に表示
                    <br>
                    <input class="optional_input_toggle" data-target_class="1" id="optional_medicine_key1-input" type="text" length="20">
                    <button class="optional_add_toggle" data-target_class="1" id="optional_medicine_key1-add">追加</button>
                    <button class="optional_fix_toggle" data-target_class="1" id="optional_medicine_key1-fix">修正</button>
                    <button class="optional_del_toggle" data-target_class="1" id="optional_medicine_key1-delete">削除</button><br>
                    <select class="optional_select_toggle" data-target_class="1" id="optional_medicine_key1" name="optional_medicine_key1[]" multiple>
@foreach($viewdata->get('optional_medicine_key1') as $key1)
                            <option value="{{ $key1->id }}">{{ $key1->value }}
@endforeach
                    </select>
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="optional_key2_label"> 追加項目２ラベル</label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <input length="20" id="optional_key2_label" type="text" name="optional_key2_label" placeholder="" value="{{ old('optional_key2_label', $viewdata->get('detail')->optional_key2_label) }}">
                    <input type="checkbox" name="optional_key2_is_search_disp" value="1" @if ($viewdata->get('detail')->optional_key2_is_search_disp) checked @endif>検索条件に表示
                    <br>
                    <input class="optional_input_toggle" data-target_class="2" id="optional_medicine_key2-input" type="text" length="20">
                    <button class="optional_add_toggle" data-target_class="2" id="optional_medicine_key2-add">追加</button>
                    <button class="optional_fix_toggle" data-target_class="2" id="optional_medicine_key2-fix">修正</button>
                    <button class="optional_del_toggle" data-target_class="2" id="optional_medicine_key2-delete">削除</button><br>
                    <select class="optional_select_toggle" data-target_class="2" id="optional_medicine_key2" name="optional_medicine_key2[]" multiple>
@foreach($viewdata->get('optional_medicine_key2') as $key2)
                            <option value="{{ $key2->id }}">{{ $key2->value }}
@endforeach
                    </select>
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="optional_key3_label"> 追加項目３ラベル</label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <input length="20" id="optional_key3_label" type="text" name="optional_key3_label" placeholder="" value="{{ old('optional_key3_label', $viewdata->get('detail')->optional_key3_label) }}">
                    <input type="checkbox" name="optional_key3_is_search_disp" value="1" @if ($viewdata->get('detail')->optional_key3_is_search_disp) checked @endif>検索条件に表示
                    <br>
                    <input class="optional_input_toggle" data-target_class="3" id="optional_medicine_key3-input" type="text" length="20">
                    <button class="optional_add_toggle" data-target_class="3" id="optional_medicine_key3-add">追加</button>
                    <button class="optional_fix_toggle" data-target_class="3" id="optional_medicine_key3-fix">修正</button>
                    <button class="optional_del_toggle" data-target_class="3" id="optional_medicine_key3-delete">削除</button><br>
                    <select class="optional_select_toggle" data-target_class="3" id="optional_medicine_key3" name="optional_medicine_key3[]" multiple>
@foreach($viewdata->get('optional_medicine_key3') as $key3)
                            <option value="{{ $key3->id }}">{{ $key3->value }}
@endforeach
                    </select>
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="optional_key4_label"> 追加項目４ラベル</label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <input length="20" id="optional_key4_label" type="text" name="optional_key4_label" placeholder="" value="{{ old('optional_key4_label', $viewdata->get('detail')->optional_key4_label) }}">
                    <input type="checkbox" name="optional_key4_is_search_disp" value="1" @if ($viewdata->get('detail')->optional_key4_is_search_disp) checked @endif>検索条件に表示
                    <br>
                    <input class="optional_input_toggle" data-target_class="4" id="optional_medicine_key4-input" type="text" length="20">
                    <button class="optional_add_toggle" data-target_class="4" id="optional_medicine_key4-add">追加</button>
                    <button class="optional_fix_toggle" data-target_class="4" id="optional_medicine_key4-fix">修正</button>
                    <button class="optional_del_toggle" data-target_class="4" id="optional_medicine_key4-delete">削除</button><br>
                    <select class="optional_select_toggle" data-target_class="4" id="optional_medicine_key4" name="optional_medicine_key4[]" multiple>
@foreach($viewdata->get('optional_medicine_key4') as $key4)
                            <option value="{{ $key4->id }}">{{ $key4->value }}
@endforeach
                    </select>
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="optional_key5_label"> 追加項目５ラベル</label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <input length="20" id="optional_key5_label" type="text" name="optional_key5_label" placeholder="" value="{{ old('optional_key5_label', $viewdata->get('detail')->optional_key5_label) }}">
                    <input type="checkbox" name="optional_key5_is_search_disp" value="1" @if ($viewdata->get('detail')->optional_key5_is_search_disp) checked @endif>検索条件に表示
                    <br>
                    <input class="optional_input_toggle" data-target_class="5" id="optional_medicine_key5-input" type="text" length="20">
                    <button class="optional_add_toggle" data-target_class="5" id="optional_medicine_key5-add">追加</button>
                    <button class="optional_fix_toggle" data-target_class="5" id="optional_medicine_key5-fix">修正</button>
                    <button class="optional_del_toggle" data-target_class="5" id="optional_medicine_key5-delete">削除</button><br>
                    <select class="optional_select_toggle" data-target_class="5" id="optional_medicine_key5" name="optional_medicine_key5[]" multiple>
@foreach($viewdata->get('optional_medicine_key5') as $key5)
                            <option value="{{ $key5->id }}">{{ $key5->value }}
@endforeach
                    </select>
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="optional_key6_label"> 追加項目６ラベル</label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <input length="20" id="optional_key6_label" type="text" name="optional_key6_label" placeholder="" value="{{ old('optional_key6_label', $viewdata->get('detail')->optional_key6_label) }}">
                    <input type="checkbox" name="optional_key6_is_search_disp" value="1" @if ($viewdata->get('detail')->optional_key6_is_search_disp) checked @endif>検索条件に表示
                    <br>
                    <input class="optional_input_toggle" data-target_class="6" id="optional_medicine_key6-input" type="text" length="20">
                    <button class="optional_add_toggle" data-target_class="6" id="optional_medicine_key6-add">追加</button>
                    <button class="optional_fix_toggle" data-target_class="6" id="optional_medicine_key6-fix">修正</button>
                    <button class="optional_del_toggle" data-target_class="6" id="optional_medicine_key6-delete">削除</button><br>
                    <select class="optional_select_toggle" data-target_class="6" id="optional_medicine_key6" name="optional_medicine_key6[]" multiple>
@foreach($viewdata->get('optional_medicine_key6') as $key6)
                            <option value="{{ $key6->id }}">{{ $key6->value }}
@endforeach
                    </select>
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="optional_key7_label"> 追加項目７ラベル</label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <input length="20" id="optional_key7_label" type="text" name="optional_key7_label" placeholder="" value="{{ old('optional_key7_label', $viewdata->get('detail')->optional_key7_label) }}">
                    <input type="checkbox" name="optional_key7_is_search_disp" value="1" @if ($viewdata->get('detail')->optional_key7_is_search_disp) checked @endif>検索条件に表示
                    <br>
                    <input class="optional_input_toggle" data-target_class="7" id="optional_medicine_key7-input" type="text" length="20">
                    <button class="optional_add_toggle" data-target_class="7" id="optional_medicine_key7-add">追加</button>
                    <button class="optional_fix_toggle" data-target_class="7" id="optional_medicine_key7-fix">修正</button>
                    <button class="optional_del_toggle" data-target_class="7" id="optional_medicine_key7-delete">削除</button><br>
                    <select class="optional_select_toggle" data-target_class="7" id="optional_medicine_key7" name="optional_medicine_key7[]" multiple>
@foreach($viewdata->get('optional_medicine_key7') as $key7)
                            <option value="{{ $key7->id }}">{{ $key7->value }}
@endforeach
                    </select>
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="optional_key8_label"> 追加項目８ラベル</label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <input length="20" id="optional_key8_label" type="text" name="optional_key8_label" placeholder="" value="{{ old('optional_key8_label', $viewdata->get('detail')->optional_key8_label) }}">
                    <input type="checkbox" name="optional_key8_is_search_disp" value="1" @if ($viewdata->get('detail')->optional_key8_is_search_disp) checked @endif>検索条件に表示
                    <br>
                    <input class="optional_input_toggle" data-target_class="8" id="optional_medicine_key8-input" type="text" length="20">
                    <button class="optional_add_toggle" data-target_class="8" id="optional_medicine_key8-add">追加</button>
                    <button class="optional_fix_toggle" data-target_class="8" id="optional_medicine_key8-fix">修正</button>
                    <button class="optional_del_toggle" data-target_class="8" id="optional_medicine_key8-delete">削除</button><br>
                    <select class="optional_select_toggle" data-target_class="8" id="optional_medicine_key8" name="optional_medicine_key8[]" multiple>
@foreach($viewdata->get('optional_medicine_key8') as $key8)
                            <option value="{{ $key8->id }}">{{ $key8->value }}
@endforeach
                    </select>
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="roles"> 使用可能ロール</label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <div class="scroll-ul">
@foreach($viewdata->get('roles') as $role)
                        <div class="scroll-li">
                            <input type="checkbox" name="roles[]" value="{{ $role['name'] }}"{{ $role['use'] }}>{{ $role['name'] }}
                        </div>
@endforeach
                    </div>
                </div>
            </div>
@if ($viewdata->get('disp_hospital') === true)
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="hospitals"> 利用可能病院</label>
                <div class="col-sm-4">
                    <br>
                    病院登録一覧<br>
                    <select class="form-control validate" type="text" name="add_hospitals[]" id="add_hospitals" multiple size=10>
                        <option disabled>病院名</option>
@foreach($viewdata->get('hospitals') as $hospital)
    @if($hospital['use'] !== '')
                        <option value="{{ $hospital['id'] }}">{{ $hospital['name'] }}</option>
    @endif
@endforeach
                    </select>
                </div>
                <div class="col-sm-1">
                    <br><br><br><br><br>
                    <input type="button" id="right_hospital" name="right_hospital" value="≫" /><br><br>
                    <input type="button" id="left_hospital" name="left_hospital" value="≪" />
                </div>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <input type="text" type="search" data-search-item="hospital_name" id="hospital_name"><button id="search_hospital">検索</button><br>
                    病院一覧<br>
                    <select class="form-control validate" type="text" name="hospitals[]" id="hospitals" multiple size=10>
                        <option disabled>病院名</option>
@foreach($viewdata->get('hospitals') as $hospital)
    @if($hospital['use'] === '')
                        <option value="{{ $hospital['id'] }}">{{ $hospital['name'] }}</option>
    @endif
@endforeach
                    </select>
                </div>
            </div>
@endif
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="traders"> 使用可能業者</label>
                <div class="col-sm-4">
                    <br>
                    業者登録一覧<br>
                    <select class="form-control validate" type="text" name="add_traders[]" id="add_traders" multiple size=10>
                        <option disabled>業者名　　　　　　　　　住所　　　　　　　　　　　　　　　</option>
@foreach($viewdata->get('traders') as $trader)
    @if($trader['use'] !== '')
                        <option value="{{ $trader['trader_id'] }}">{{ $trader['name'] }}</option>
    @endif
@endforeach
                    </select>
                </div>
                <div class="col-sm-1">
                    <br><br><br><br><br>
                    <input type="button" id="right_trader" name="right_trader" value="≫" /><br><br>
                    <input type="button" id="left_trader" name="left_trader" value="≪" />
                </div>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <input type="text" type="search" data-search-item="trader_name" id="trader_name"><button id="search_trader">検索</button><br>
                    業者一覧<br>
                    <select class="form-control validate" type="text" name="traders[]" id="traders" multiple size=10>
                        <option disabled>業者名　　　　　　　　　住所　　　　　　　　　　　　　　　</option>
@foreach($viewdata->get('traders') as $trader)
    @if($trader['use'] === '')
                        <option value="{{ $trader['trader_id'] }}">{{ $trader['name'] }}</option>
    @endif
@endforeach
                    </select>
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="types"> 使用データ区分</label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <input id="type-input" type="text" length="20"> <button id="type-add">追加</button> <button id="type-fix">修正</button> <button id="type-delete">削除</button><br>
                    <select id="select-types" name="types[]" multiple>
@foreach($viewdata->get('types') as $type)
                            <option value="{{ $type['id'] }}">{{ $type['data_type_name'] }}
@endforeach
                    </select>
                </div>
            </div>
            <div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="supplies"> 使用供給区分</label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner"></div>
                    </div>
                    <input id="supply-input" type="text" length="20"> <button id="supply-add">追加</button> <button id="supply-fix">修正</button> <button id="supply-delete">削除</button><br>
                    <select id="select-supplies" name="supplies[]" multiple>
@foreach($viewdata->get('supplies') as $supply)
                            <option value="{{ $supply['id'] }}">{{ $supply['supply_division_name'] }}
@endforeach
                    </select>
                </div>
            </div>
            <!-- div class="form-group validateGroup row"><label class="col-sm-3 form-control-label" for="seal"> 社印ファイル</label>
                <div class="col-sm-4">
                    <div class="error-tip">
                        <div class="error-tip-inner">{{ $errors->first('file') }}</div>
                    </div>
                    <input class="form-control validate w400 h40" type="file" id="file" name="file">
                </div>
            </div -->
        </form>
    </div>
    <div class="d-flex justify-content-around pt15 pb15 card-footer"><a class="btn btn-default btn-lg" href="{{ route('usergroup.index') }}"><i class="fas fa-chevron-left mr10"></i>ユーザグループ一覧へ戻る</a>
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
                <h5 class="modal-title">グループ削除確認</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="tac text">このグループを削除してよろしいでしょうか？</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
                    <button class="btn btn-danger" type="button" onClick="location.href='{{ route('usergroup.delete', ['id' => $viewdata->get('detail')->id]) }}'">
                    <div class="fas fa-ban mr10"></div>削除する
                </button>
            </div>
        </div>
    </div>
</div>

<div class="fade modal" id="modal-action" role="dialog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">グループ更新</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="tac text">このグループを更新してよろしいでしょうか？</p>
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

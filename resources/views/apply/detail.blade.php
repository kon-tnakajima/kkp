@extends('layouts.app')

@php($title = '採用管理')

@php($category = 'apply')

@php($breadcrumbs = ['HOME'=>'/','採用管理'=>'/apply?page='. $detail->page,'採用管理詳細'=>'javascript:void(0);'])

@section('content')

@if ($errors->any())
入力にエラーがあります<br>
@endif
@if (!empty($errorMessage))
<div class="alert alert-danger" role="alert">
{{ $errorMessage }}
</div>
@endif
@if (!empty($message))
<div class="alert alert-success" role="alert">
{{ $message }}
</div>
@endif

<?php
/*
 * 権限の取得
 */
$kengen = $detail->kengen;
$purchase_waku_kengen = $kengen["医薬品採用申請_仕入価枠表示"]->privilege_value == "true";
$sales_waku_kengen = $kengen["医薬品採用申請_納入価枠表示"]->privilege_value == "true";
$bunkaren_waku_kengen = $kengen["医薬品採用申請_文化連枠表示"]->privilege_value == "true";
?>

<?php
/*
 * コメントデータ
 */
// TODO コメントの仕様が固まったらコメントイン
// use App\Data\CommentData as CommentData;
// $comment_list_gyosya_bunkaren = array(
//     new CommentData('日付１', '誰１', 'コメント１'.PHP_EOL.'コメント１'.PHP_EOL.'コメント１'),
//     new CommentData('日付２', '誰２', 'コメント２'.PHP_EOL.'コメント２'.PHP_EOL.'コメント２'),
//     new CommentData('日付３', '誰３', '<input type="text"></input>'),
// );
// $comment_list_kouseiren_bunkaren = array(
//     new CommentData('日付１', '誰１', 'コメント１'.PHP_EOL.'コメント１'.PHP_EOL.'コメント１'),
//     new CommentData('日付２', '誰２', 'コメント２'.PHP_EOL.'コメント２'.PHP_EOL.'コメント２'),
//     new CommentData('日付３', '誰３', 'コメント３'.PHP_EOL.'コメント３'.PHP_EOL.'コメント３'),
//     new CommentData('日付４', '誰４', 'コメント４'.PHP_EOL.'コメント４'.PHP_EOL.'コメント４'),
// );
// $comment_list_note = array(
//     new CommentData('日付１', '誰１', 'コメント１'.PHP_EOL.'コメント１'.PHP_EOL.'コメント１'),
//     new CommentData('日付２', '誰２', 'コメント２'.PHP_EOL.'コメント２'.PHP_EOL.'コメント２'),
//     new CommentData('日付３', '誰３', 'コメント３'.PHP_EOL.'コメント３'.PHP_EOL.'コメント３'),
//     new CommentData('日付４', '誰４', 'コメント４'.PHP_EOL.'コメント４'.PHP_EOL.'コメント４'),
//     new CommentData('日付５', '誰５', 'コメント５'.PHP_EOL.'コメント５'.PHP_EOL.'コメント５'),
//     new CommentData('日付６', '誰６', 'コメント６'.PHP_EOL.'コメント６'.PHP_EOL.'コメント６'),
//     new CommentData('日付７', '誰７', 'コメント７'.PHP_EOL.'コメント７'.PHP_EOL.'コメント７'),
// );
?>

<?php
function switchDateViewByDate($date, $base){
    $time1 = strtotime($base);
    $time2 = strtotime($date);
    if (!$time1 || !$time2) {
        // どちらかの日付文字列のパースに失敗したら元の値を表示する
        return $date;
    }
    $diff = $time1 - $time2;
    return $diff > 0 ? $date : "";
}
?>

<style>
.optional_area {
    position: relative;
    margin: 2em 0;
    padding: 0.5em 1em;
    border: solid 3px #000000;
    border-radius: 8px;
}
.optional_area .box-title {
    position: absolute;
    display: inline-block;
    top: -13px;
    left: 10px;
    padding: 0 9px;
    line-height: 1;
    font-size: 19px;
    background: #FFF;
    color: #000000;
    font-weight: bold;
}
.optional_area p {
    margin: 0;
    padding: 0;
}
</style>

<style>
.form-group {
  margin: 0;
}
.form-control {
  width: 100%;
}
.relative-box {
  position: relative;
}
.error-tip {
  top: 50% !important;
  left: 50%;
  right: auto;
  white-space: nowrap;
}
.error-tip:after {
  left: 0;
  top: 40%;
  transform: translateX(-50%) translateY(-50%);
  border-color: transparent;
  border-right-color: #f86c6b;
}
.card-box {
  position: relative;
  display : -webkit-inline-box;
  display : -ms-inline-flexbox;
  display : -webkit-inline-flex;
  display : inline-flex;
  -webkit-box-orient: vertical;
  -webkit-box-direction: normal;
  -ms-flex-direction: column;
  flex-direction: column;
  -webkit-box-align: stretch;
  -ms-flex-align: stretch;
  align-items: stretch;
  -webkit-box-pack: start;
  -ms-flex-pack: start;
  justify-content: flex-start;
  box-sizing: border-box;
  padding: 0 0.5em;
  border: 1px solid #390;
}
.card-box > label:first-child {
  box-sizing: border-box;
  background-color: lightblue;
  margin-left: -0.5em;
  padding: 0.2em 0.5em;
  white-space: nowrap;
}
.card-box label {
  margin: 0;
}
.flex-stretch {
  -ms-flex-item-align: stretch;
  align-self: stretch;
}
.card-box.type-4 {
  padding: 0;
  position: relative;
  margin-top: 2.5em;
}
.card-box.type-4 > label:first-child {
  position: absolute;
  left: 0;
  top: 0;
  margin-left: -1px;
  margin-top: -2em;
  border-left: 1px solid #390;
  border-right: 1px solid #390;
  border-top: 1px solid #390;
  box-sizing: border-box;
}
label {
  display : -webkit-inline-box;
  display : -ms-inline-flexbox;
  display : -webkit-inline-flex;
  display : inline-flex;
  -webkit-box-orient: horizontal;
  -webkit-box-direction: normal;
  -ms-flex-direction: row;
  flex-direction: row;
  -webkit-box-align: center;
  -ms-flex-align: center;
  align-items: center;
  -webkit-box-pack: start;
  -ms-flex-pack: start;
  justify-content: flex-start;
  min-height: 1.5em;
}
label > span:first-child {
  color: gray;
  font-size: 80%;
  display : -webkit-inline-box;
  display : -ms-inline-flexbox;
  display : -webkit-inline-flex;
  display : inline-flex;
  -webkit-box-orient: horizontal;
  -webkit-box-direction: normal;
  -ms-flex-direction: row;
  flex-direction: row;
  -webkit-box-align: center;
  -ms-flex-align: center;
  align-items: center;
  -webkit-box-pack: end;
  -ms-flex-pack: end;
  justify-content: flex-end;
}

.optional-input {
  color: white;
  font-size: 80%;
  display : -webkit-inline-box;
  display : -ms-inline-flexbox;
  display : -webkit-inline-flex;
  display : inline-flex;
  -webkit-box-orient: horizontal;
  -webkit-box-direction: normal;
  -ms-flex-direction: row;
  flex-direction: row;
  -webkit-box-align: center;
  -ms-flex-align: center;
  align-items: center;
  -webkit-box-pack: end;
  -ms-flex-pack: end;
  justify-content: flex-end;
}

label > span:first-child.badge {
  color: white;
}
.comment > div > label {
  width: 100%;
}
.comment {
  width: 100%;
}
.comment textarea {
  width: 100%;
}
input:focus,
select:focus,
textarea:focus {
  border: 3px solid green !important;
  /* background-color:#fff8dc !important; */
  outline: none
}

input,
select,
textarea {
  border: 1px solid green !important;
  background-color: #fff8dc !important;
  font-weight: 600 !important;
}
input.require,
select.require {
  background-color: #ffdcdc !important;
  font-weight: 600 !important;
}

/* input {
  width: auto !important;
} */

input.date {
  width: 7em;
}

textarea {
  margin: 0;
  padding: 0.5em;
  border: none !important;
  box-sizing: border-box;
  resize: vertical;
  min-height: calc(1.5em * 3 + 0.75em + 2px);
}

.fp-edit {
  width: auto !important;
  text-align: right;
}

.td-value-box {
  /*
  background-color: yellow;
  */
  max-width: 12em;
  text-align: right;
}
@media print{
    * {
      overflow: visible !important;
    }
/*
    .root-container,
    .col-box,
    .row-box,
    .facility-select,
 */
    .card-box:not(.type-1) > div,
    label > span:first-child,
    .card-box-title-3,
    .card-box,
    .card-box.type-1 .scroll-area,
    .card-box-title-2,
    label:not(.card-box-title-3) {
      display: block !important;
    }
}
</style>

<!-- [START] 2020/10/14 レイアウト修正による追加 -->
<style>
    .apply_detail {
        overflow-x: auto;
        color: black;
    }

    .page-content {
        width: 1200px;
        font-size: 16px;
        margin-left: 1em;
        margin-top: 1em;
    }

    .page-content textarea {
        border: 1px solid green !important;
    }

    .page-content textarea:focus {
        border: 3px solid green !important;
        /* background-color:#fff8dc !important; */
        outline: none
    }

    table {
        font-size: inherit;
    }

    .medicine-detail-table {
        position: relative;
        table-layout: fixed;
        border-collapse: collapse;
        border-spacing: 0;
        width: 100%;
    }

    .medicine-detail-table th {
        text-align: left;
        max-width: 11em;
    }

    .medicine-detail-table td {
        min-height: 2em;
    }

    h1,
    h2,
    h3,
    h4,
    h5,
    h6,
    h7,
    h8,
    h9 {
        padding: 0;
        margin: 0;
    }

    h2 {
        position: relative;
        border-top: 2px solid green;
        font-size: 120%;
    }

    h2:not(:first-child) {
        margin-top: 0.5em;
    }

    h2 .title {
        display: inline-block;
        padding: 2px 0.5em 0;
        margin-top: -2px;
        border-left: 2px solid green;
        border-bottom: 2px solid green;
        border-right: 2px solid green;
    }

    h2 .id {
        display: inline-block;
        position: absolute;
        right: 0;
        font-size: 80%;
        font-weight: normal;
    }

    h3 {
        font-size: 110%;
        margin-top: 0.5em;
    }

    .medicine-detail-table th,
    .medicine-detail-table td {
        border-bottom: 1px solid rgba(0, 100, 0, 0.2);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .medicine-detail-table th:first-child {
        width: 8em;
    }

    .medicine-detail-table td:not(:first-child) {
        padding-right: 2em;
    }

    .toggle-view-link,
    .step-view-link {
        cursor: pointer;
        color: darkblue !important;
        font-weight: normal !important;
    }

    .send-comment-block {
        width: 100%;
        display: flex;
        justify-content: flex-start;
        align-content: center;
    }

    .send-comment-block textarea {
        flex: 1;
        resize: vertical;
    }

    textarea {
        min-height: 3em;
    }

    .comment-table {
        position: relative;
        table-layout: fixed;
        border-collapse: collapse;
        border-spacing: 0;
        width: 100%;
    }

    .comment-table td {
        vertical-align: top;
    }

    .comment-table .date {
        width: 13em;
    }

    .comment-table .who {
        width: 8em;
    }

    .comment-table tr {
        border-bottom: 1px solid rgba(0, 100, 0, 0.2);
    }

    .price {
        text-align: right;
    }

    @media print {
        .page-content {
            width: 1000px;
            font-size: 18px;
        }

        .d-print-none {
            display: none;
        }
    }

    /* 薬品情報のブロック専用のスタイル */
    .medicine-info th:nth-of-type(2) {
        width: 8em;
    }

    /* 申請情報のブロック専用のスタイル */
    .application-data th:nth-of-type(2) {
        width: 9em;
    }

    .application-data th:nth-of-type(3) {
        width: 7em;
    }

    .application-data th:last-of-type {
        width: 12em;
    }

    /* 単価情報 */
    .price-data {
        margin-bottom: 0.5em;
    }

    .price-data th:nth-of-type(2) {
        width: 8em;
    }

    /* 価格履歴 */
    .price-history {
        display: inline-table;
        position: relative;
        table-layout: fixed;
        border-collapse: collapse;
        border-spacing: 0;
        margin-top: 0.5em;
        margin-left: 1em;
        border: dashed 1px green;
        font-size: 80%;
    }

    .price-history th,
    .price-history td {
        padding: 0 1em;
    }

    .price-history caption {
        display: table-caption;
        padding: 0;
        caption-side: top;
        text-align: left;
        font-weight: bold;
    }

    .price-history tbody tr {
        border-top: 1px solid rgba(0, 100, 0, 0.2);
    }

    /* 採用情報 */
    .price-adoption-data th:nth-of-type(2) {
        width: 8em;
    }

    .btn {
        position: relative;
    }
    .btn:hover:before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.1);
    }
    .row {
        margin-left: 0 !important;
        margin-right: 0 !important;
    }
    table#statement th {
    font-weight: 400 ;
    }
    .label-comment {
        white-space: pre-wrap;
        font-weight: 500 !important;
        font-size:15px !important;
        background-color: #fffbea !important;
    }
    .comment-title {
        font-weight: 600 !important;
    }

    input::-webkit-input-placeholder {
    font-size:11px
    }
    input:-moz-placeholder {
    font-size:11px
    }
    input::-moz-placeholder {
    font-size:11px
    }
    input:-ms-input-placeholder {
    font-size:11px
    }
    input:-webkit-autofill 
    {
    -webkit-transition: background-color 9999s;
    transition: background-color 9999s;
    }
    .badge-danger {
        font-size: 80%;
    }
    .pa-date {
        font-size:14px;
    }

    @media (prefers-reduced-motion: reduce) {
        .apply-detail-action-btn {
            -webkit-transition: none;
            transition: none;
        }
    }


    .apply-detail-action-btn {
        position: relative;
        display: inline-block;
        font-weight: 400;
        color: #151b1e;
        text-align: center;
        vertical-align: middle;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        background-color: transparent;
        border: 1px solid transparent;
        padding: .375rem .75rem;
        font-size: .875rem;
        line-height: 1.5;
        border-radius: 0;
        -webkit-transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,-webkit-box-shadow .15s ease-in-out;
        transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,-webkit-box-shadow .15s ease-in-out;
        transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;
        transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out,-webkit-box-shadow .15s ease-in-out;
    }
    .apply-detail-action-btn.clicked {
        cursor: no-drop;
    }    
    .apply-detail-action-btn:hover::before {
        content: '';
        background-color: rgba(0, 0, 0, 0.2);
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
    }
    .apply-detail-action-btn:focus {
    box-shadow: 0 0 0 .2rem rgba(0,123,255,.75);
    }

    
</style>
<!-- [END] 2020/10/14 レイアウト修正による追加 -->

<div class="card card-primary apply_detail">
    <form 
        class="form-horizontal" 
        action="{{ route('apply.edit', ['id '=> $detail->m_id, 'fm_id' => $detail->fm_id, 'fp_id'=>$detail->fp_id, 'pa_id' => $detail->pa_id, 'fp_honbu_id' => $detail->fp_honbu_id ]) }}" 
        method="post" 
        id="apply-edit" 
        accept-charset="utf-8"
    >
        @csrf
    <div class="d-flex justify-content-around mt10">
        <!-- 申請可能な施設をselect -->
        <div>
            <span style="font-weight: bold;" class="ml10 mr5">施設</span>
            <span style="min-width: 10em; display: inline-block;">
                @if( count($applygroups) > 1)
                    <select class="form-control validate" name="apply_group" id="apply_group" data-validate="empty">
                @foreach($applygroups as $group)
                    <option value="{{ $group->user_group_id }}" @if ($detail->primary_user_group_id == $group->user_group_id) selected @endif >{{ $group->name }}</option>
                @endforeach
                    </select>
                @else
                    <p class="form-control-static">{{ $detail->facility_name }} </p>
                @endif
            </span>
        </div>
        <div><span style="font-weight: bold;" class="mr5">ステータス</span><span style="min-width: 10em; display: inline-block;">{{ $detail->status_current_label }}</span></div>
        @include('apply.components.detail_button_group', ['detail' => $detail])
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>

        @include('layouts.input_hidden', ['parent' => '[apply.detail]', 'name' => 'fm_id', 'value' => $detail->fm_id])
        @include('layouts.input_hidden', ['parent' => '[apply.detail]', 'name' => 'fp_id', 'value' => $detail->fp_id])
        @include('layouts.input_hidden', ['parent' => '[apply.detail]', 'name' => 'pa_id', 'value' => $detail->pa_id])
        @include('layouts.input_hidden', ['parent' => '[apply.detail]', 'name' => 'fp_honbu_id', 'value' => $detail->fp_honbu_id])
        @include('layouts.input_hidden', ['parent' => '[apply.detail]', 'name' => 'isCheck', 'value' => '2'])
        @include('layouts.input_hidden', ['parent' => '[apply.detail]', 'name' => 'status_forward', 'value' => $detail->status_forward])
        @include('layouts.input_hidden', ['parent' => '[apply.detail]', 'name' => 'status_withdraw', 'value' => $detail->status_withdraw])
        @include('layouts.input_hidden', ['parent' => '[apply.detail]', 'name' => 'status_remand', 'value' => $detail->status_remand])
        @include('layouts.input_hidden', ['parent' => '[apply.detail]', 'name' => 'status_reject', 'value' => $detail->status_reject])
        @include('layouts.input_hidden', ['parent' => '[apply.detail]', 'name' => 'page', 'value' => $detail->page])
        @include('layouts.input_hidden', ['parent' => '[apply.detail]', 'name' => 'page_count', 'value' => $page_count])
        @include('layouts.input_hidden', ['parent' => '[apply.detail]', 'name' => 'm_id', 'value' => $detail->m_id])
        @include('layouts.input_hidden', ['parent' => '[apply.detail]', 'name' => 'apply_control', 'value' => ''])
        @include('layouts.input_hidden', ['parent' => '[apply.detail]', 'name' => 'button_control', 'value' => ''])
        @include('layouts.input_hidden', ['parent' => '[apply.detail]', 'name' => 'medicine_name', 'value' => $detail->m_name])



    <!-- [START] 2020/10/14 レイアウト修正による追加 -->
        <div class="page-content">
            <h2>
                <span title="薬品マスタに関する情報です" class="title">薬品情報</span>
                <span class="id">ID：{{ $detail->m_id }}</span>
            </h2>
            <table class="medicine-detail-table medicine-info">
                <tbody>
                    <tr>
                        <th>販売メーカー</th>
                        <td>{{ $detail->m_hanbai_maker }}</td>
                        <th>JANコード</th>
                        <td>{{ $detail->pu_jan_code }}</td>
                    </tr>
                    <tr>
                        <th>製造メーカー</th>
                        <td>{{ $detail->m_seizo_maker }}</td>
                        <th>GS1販売</th>
                        <td>{{ $detail->m_sales_packaging_code }} (旧GS1:
                            @if (!empty($detail->m_old_sales_packaging_code))
                                <a class="form-control-static" href="{{ route('apply.index') }}?search={{ $detail->m_old_sales_packaging_code }}&is_search=1&discontinuation_date=1&page_count=20" target="_blank" rel="noopener noreferrer">{{ $detail->m_old_sales_packaging_code }}</a>
                            @else - @endif)
                        </td>
                    </tr>
                    <tr>
                        <th>商品名</th>
                        <td>{{ $detail->m_name }}</td>
                        <th>GS1調剤</th>
                        <td>{{ $detail->m_dispensing_packaging_code }}</td>
                    </tr>
                    <tr>
                        <th>規格容量</th>
                        <td colspan="3">{{ $detail->m_standard_unit }}</td>
                    </tr>
                    <tr>
                        <th>一般名</th>
                        <td colspan="3">{{ $detail->m_popular_name }}</td>
                    </tr>
                    <tr>
                        <th title="規格・単位とは：保険を適用して医薬品を使用するにあたっての最小の単位です">規格・単位</th>
                        <td>{{ $detail->m_standard_unit_name }}</td>
                        <th title="（財）医療情報システム開発センター（ＭＥＤＩＳ－ＤＣ）にて付番､管理されている１３桁のコードです。">HOT番号</th>
                        <td>{{ $detail->m_hot_code }}</td>
                    </tr>
                    <tr>
                        <th>包装薬価係数</th>
                        <td>{{ $detail->pu_coefficient }}</td>
                        <th title="薬価基準収載医薬品コードの略称です。">医薬品コード</th>
                        <td>{{ $detail->m_code }}</td>
                    </tr>
                    <tr>
                        <th>販売開始日</th>
                        <td>{{ substr($detail->m_sales_start_date,0,4) }}-{{ substr($detail->m_sales_start_date,4,2) }}-{{ substr($detail->m_sales_start_date,6,2) }}</td>
                        <th title="個別医薬品コードの通称。統一名収載されている医薬品に対して銘柄別（製造企業別）にコードが設定されています">YJコード</th>
                        <td>{{ $detail->m_medicine_code }}
    @if (!empty($detail->m_medicine_code))
                            （<a class="form-control-static" target="_blank" href="https://www.safe-di.jp/service/medicine/show?params_yj_cd={{ $detail->m_medicine_code }}">SAFE-DI</a>）
    @endif
                        </td>
                    </tr>
                    <tr>
                        <th>販売中止日</th>
                        <td>{{ switchDateViewByDate($detail->m_discontinuation_date, '2100-01-01') }}</td>
                        <th>収載FG</th>
                        <td>{{ $detail->medicine_price_listing_kbn_name }}
                    </tr>
                    <tr>
                        <th>製造中止日</th>
                        <td>{{ switchDateViewByDate($detail->m_production_stop_date, '2100-01-01') }}</td>
                        <th title="厚労省が「各先発医薬品の後発医薬品の有無に関する情報」として公開している区分です">先後発区分</th>
                        <td>{{ $detail->generic_product_detail_devision }}</td>
                    </tr>
                    <tr>
                        <th>経過措置日</th>
                        <td>{{ $detail->m_transitional_deadline }}</td>
                        <th>毒劇区分</th>
                        <td>{{ $detail->danger_poison }}</td>
                    </tr>
                    <tr>
                        <th>使用区分</th>
                        <td>{{ App\Helpers\Apply\getDosageTypeDivisionText($detail->m_dosage_type_division) }}</td>
                        <th>保管温度 場所</th>
                        <td>{{ $detail->storage_temperature }} {{ $detail->storage_place }}</td>
                    </tr>
                </tbody>
            </table>

            <h2>
                <span title="申請マスタに関する情報です" class="title">申請情報</span>
                <span class="toggle-view-link d-print-none fs16" data-target="pa-additional-info" 
                @if(($detail->pa_id && $detail->status != 100) || $detail->status > 100 || empty($detail->fm_id)) data-default="open" data-message="　表示する">　非表示にする @else data-default="hide" data-message="　非表示にする">　表示する@endif </span>
                <span class="id">ID：@if ($detail->pa_id){{ $detail->pa_id }}@else－@endif</span>
            </h2>
            <table class="medicine-detail-table application-data pa-additional-info">
                <tbody>
                    <tr>
                    <th title=@if ($detail->edit_pa_basis_medicine_price > 0)"見積の基準とする薬価改定日を入力してください。初期値は現在の改定日です"@else"見積の基準となる薬価改定日です"@endif>薬価基準日@if ($detail->edit_pa_basis_medicine_price > 0)<span class="badge badge-danger ml-1">必須</span>@endif</th>
                        <td>
         @if ($detail->edit_pa_basis_medicine_price > 0)
                            @include('layouts.input', ['parent' => '[apply.detail]', 'name' => 'pa_basis_mediicine_price_date', 'value' => $detail->pa_basis_mediicine_price_date, 'type' => 'text', 'class' => 'pa_basis_mediicine_price_date require', 'other_attr' => 'data-datepicker=true data-validate="empty date" placeholder='.$detail->mp_start_date])
         @elseif ($detail->status >= 30)
                            <div class="td-value-box">{{ $detail->pa_basis_mediicine_price_date }}</div>
         @endif
                        </td>
        @if ($purchase_waku_kengen)
                        <!-- 要望仕入 -->
                        @include('apply.components.detail_price_adoption', ['title' => '要望仕入単価', 'type' => 'purchase_requested', 'decimal_point' => 0, 'detail' => $detail, 'errors' => $errors, 'require_flag' => false])
        @endif
        @if ($sales_waku_kengen)
                        <!-- 納入 -->
            @if ($purchase_waku_kengen)
                        <th></th><td></td>
            @else
                        @include('apply.components.detail_price_adoption', ['title' => '納入単価', 'type' => 'sales_apply', 'decimal_point' => 0, 'detail' => $detail, 'errors' => $errors, 'require_flag' => $detail->edit_pa_sales_apply_price > 0])
            @endif
        @endif
                        <th title="初期値は1です。整数で優先度を設定します。">優先度@if ($detail->edit_priority)<span class="badge badge-success ml-1 optional-input">任意</span>@endif</th>
                        <td>
        @if ($detail->edit_priority)
                            @include('layouts.input', ['parent' => '[apply.detail]', 'name' => 'pa_priority', 'value' => $detail->pa_priority, 'type' => 'text', 'class' => '', 'other_attr' => 'data-validate="empty zero" placeholder=1'])
        @else
                            <label>{{ $detail->pa_priority }}</label>
        @endif
                        </td>
                    </tr>
                    <tr>
                        <!-- 基準日薬価 -->
                        @include('apply.components.detail_price_medicine', ['htmltitle' => '見積の基準となる薬価です', 'title' => '基準日薬価', 'medicine_price' => 'pa_basis_mediicine_price', 'detail' => $detail])
        @if ($purchase_waku_kengen)
                        <!-- 要望掛率 -->
                        @include('apply.components.detail_rate_pa', ['title' => '掛率', 'type' => 'purchase_requested', 'detail' => $detail, 'medicine' => $medicine])
        @endif
        @if ($sales_waku_kengen)
                        <!-- 納入掛率 -->
            @if ($purchase_waku_kengen)
                        <th colspan="2"></th>
            @else
                        @include('apply.components.detail_rate_pa', ['title' => '掛率', 'type' => 'sales_apply', 'detail' => $detail, 'medicine' => $medicine])
            @endif
        @endif
                        <th>申請者</th>
                        <td>{{ $detail->pa_user_name }}</td>
                    </tr>
                    <tr>
                        <th>業者@if ($detail->edit_pa_purchase_user_group_id > 0)<span class="badge badge-danger ml-1">必須</span>@endif</th>
                        <td>
        @if ($purchase_waku_kengen || $sales_waku_kengen)
            @if ($detail->edit_pa_purchase_user_group_id > 0)
                            <div class="error-tip">
                                <div class="error-tip-inner">{{ $errors->first('pa_purchase_user_group_id') }}</div>
                            </div>
                            <select class="form-control {{ $detail->edit_pa_purchase_user_group_id > 0 ? 'require' : '' }}" name="pa_purchase_user_group_id" id="pa_purchase_user_group_id" data-validate="empty">
                                <option value="">選択してください</option>
                @foreach($sellers as $seller)
                                <option value="{{ $seller->id }}" @if ($detail->pa_purchase_user_group_id == $seller->id) selected @endif >{{ $seller->name }}</option>
                @endforeach
                            </select>
                            @include('layouts.input_hidden', ['parent' => '[apply.detail]', 'name' => 'pa_purchase_user_group_id_check', 'value' => '1'])
            @else
                            <div class="td-value-box">
                @if (!empty($detail->pa_trader_name))
                                {{ $detail->pa_trader_name }}
                @endif
                            @include('layouts.input_hidden', ['parent' => '[apply.detail]', 'name' => 'pa_purchase_user_group_id_check', 'value' => '0'])
                            </div>
            @endif
        @endif
                        </td>
        @if ($purchase_waku_kengen)
                        <!-- 要望値引率 -->
                        @include('apply.components.detail_rate_pa', ['title' => '値引率', 'type' => 'purchase_requested', 'detail' => $detail, 'medicine' => $medicine])
        @endif
        @if ($sales_waku_kengen)
                        <!-- 納入値引率 -->
            @if ($purchase_waku_kengen)
                        <th colspan="2"></th>
            @else
                        @include('apply.components.detail_rate_pa', ['title' => '値引率', 'type' => 'sales_apply', 'detail' => $detail, 'medicine' => $medicine])
            @endif
        @endif
                        <th>申請日時</th>
                        <td class="pa-date">{{ $detail->pa_application_date }}</td>
                    </tr>
                    <tr>
                        <th colspan="2"></th>
        @if ($purchase_waku_kengen)
                        <!-- 仕入列 -->
                        <th colspan="2"></th>
        @endif
        @if ($sales_waku_kengen)
                        <!-- 納入列 -->
                        <th colspan="2"></th>
        @endif
                        <th>更新者</th>
                        <td>{{ $detail->pa_updater_name }}</td>
                    </tr>
                    <tr>
                        <th colspan="2"></th>
        @if ($purchase_waku_kengen)
                        <!-- 見積仕入 -->
                        @include('apply.components.detail_price_adoption', ['title' => '見積仕入単価', 'type' => 'purchase_estimated', 'decimal_point' => 0, 'detail' => $detail, 'errors' => $errors, 'require_flag' => $detail->edit_pa_purchase_estimated_price > 0])
        @endif
        @if ($sales_waku_kengen)
                        <!-- 納入列 -->
                        <th colspan="2"></th>
        @endif
                        <th>更新日時</th>
                        <td class="pa-date">{{ $detail->pa_updated_at }}</td>
                    </tr>
                    <tr>
                        <th colspan="2"></th>
        @if ($purchase_waku_kengen)
                        <!-- 見積掛率 -->
                        @include('apply.components.detail_rate_pa', ['title' => '掛率', 'type' => 'purchase_estimated', 'detail' => $detail, 'medicine' => $medicine])
        @endif
        @if ($sales_waku_kengen)
                        <!-- 納入列 -->
                        <th colspan="2"></th>
        @endif
                        <!-- 申請有効開始日 -->
                        <th title="採用した際の単価有効開始日となります">申請単価有効開始日@if ($detail->edit_price_adoption_period> 0)<span class="badge badge-danger ml-1">必須</span>@endif</th>
                        <td>
        @if ($detail->edit_price_adoption_period> 0)
                            @include('layouts.input', ['parent' => '[apply.detail]', 'name' => 'pa_start_date', 'value' => $detail->pa_start_date, 'type' => 'text', 'class' => 'pa_start_date require', 'other_attr' => 'data-datepicker=true data-validate="empty date"'])
                            @include('layouts.input_hidden', ['parent' => '[apply.detail]', 'name' => 'pa_start_date_check', 'value' => '1'])
        @else
                            <div>{{ $detail->pa_start_date }}</div>
                            @include('layouts.input_hidden', ['parent' => '[apply.detail]', 'name' => 'pa_start_date_check', 'value' => '0'])
        @endif
                        </td>
                    </tr>
                    <tr>
                        <th colspan="2"></th>
        @if ($purchase_waku_kengen)
                        <!-- 見積値引率 -->
                        @include('apply.components.detail_rate_pa', ['title' => '値引率', 'type' => 'purchase_estimated', 'detail' => $detail, 'medicine' => $medicine])
        @endif
        @if ($sales_waku_kengen)
                        <!-- 納入列 -->
                        <th colspan="2"></th>
        @endif
                        <!-- 申請有効終了日 -->
                        <th title="採用した際の単価有効終了日となります。特に指定がなければ2100/12/31です。">申請単価有効終了日</th>
                        <td>{{ $detail->pa_end_date }}</td>
                    </tr>
                    <tr>
                        <th colspan="2">　</th>
        @if ($purchase_waku_kengen)
                        <!-- 仕入列 -->
                        <th colspan="2"></th>
        @endif
        @if ($sales_waku_kengen)
                        <!-- 納入列 -->
                        <th colspan="2"></th>
        @endif
                        <th colspan="2">　</th>
                    </tr>
                    <tr>
                        <th colspan="2"></th>
        @if ($purchase_waku_kengen)
                        <!-- 仕入 -->
                        @include('apply.components.detail_price_adoption', ['title' => '仕入単価', 'type' => 'purchase_apply', 'decimal_point' => 0, 'detail' => $detail, 'errors' => $errors, 'require_flag' => $detail->edit_pa_purchase_apply_price > 0])
        @endif
        @if ($sales_waku_kengen)
                        <!-- 納入 -->
            @if ($purchase_waku_kengen)
                        @include('apply.components.detail_price_adoption', ['title' => '納入単価', 'type' => 'sales_apply', 'decimal_point' => 0, 'detail' => $detail, 'errors' => $errors, 'require_flag' => $detail->edit_pa_sales_apply_price > 0])
            @else
                        <th colspan="2"></th>
            @endif
        @endif
        @if ($purchase_waku_kengen)
                        <th>見積仕入更新者</th>
                        <td>{{ $detail->pa_purchase_estimate_updater_name }}</td>
                    </tr>
                    <tr>
                        <th colspan="2"></th>
        @endif
        @if ($purchase_waku_kengen)
                        <!-- 仕入掛率 -->
                        @include('apply.components.detail_rate_pa', ['title' => '掛率', 'type' => 'purchase_apply', 'detail' => $detail, 'medicine' => $medicine])
        @endif
        @if ($sales_waku_kengen)
                        <!-- 納入掛率 -->
            @if ($purchase_waku_kengen)
                        @include('apply.components.detail_rate_pa', ['title' => '掛率', 'type' => 'sales_apply', 'detail' => $detail, 'medicine' => $medicine])
            @else
                        <th colspan="2"></th>
            @endif
        @endif
        @if ($purchase_waku_kengen)
                        <th>見積仕入更新日時</th>
                        <td class="pa-date">{{ $detail->pa_purchase_estimate_updated_at }}</td>
                    </tr>
        @endif
        @if ($purchase_waku_kengen || ($sales_waku_kengen && $purchase_waku_kengen))
                    <tr>
                        <th colspan="2"></th>
            @if ($purchase_waku_kengen)
                        <!-- 仕入値引率 -->
                        @include('apply.components.detail_rate_pa', ['title' => '値引率', 'type' => 'purchase_apply', 'detail' => $detail, 'medicine' => $medicine])
            @endif
            @if ($sales_waku_kengen)
                        <!-- 納入値引率 -->
                @if ($purchase_waku_kengen)
                        @include('apply.components.detail_rate_pa', ['title' => '値引率', 'type' => 'sales_apply', 'detail' => $detail, 'medicine' => $medicine])
                @else
                        <th colspan="2"></th>
                @endif
            @endif
                        <th colspan="2"></th>
                    </tr>
        @endif
                </tbody>
            </table>


            <!-- 厚生連－文化連コメント -->
        @if ($sales_waku_kengen)
        <span class = "pa-additional-info">
            @include('apply.components.detail_comment_temp', ['title' => '申請備考(厚生連－文化連)', 'comment_name' => 'pa_sales_estimate_comment', 'detail' => $detail])
        </span>
        @endif
    <?php
    // TODO 仕様が固まったらコメントイン
    //         @include('layouts.comment', ['title' => '厚生連－文化連コメント', 'id' => 'comment-kouseiren-bunkaren-table', 'list' => $comment_list_kouseiren_bunkaren])
    //         <div class="step-view-link d-print-none" data-target="#comment-kouseiren-bunkaren-table" data-message="3件だけ表示" data-view-num="3">全件表示する</div>
    ?>

            <!-- 業者 - 文化連コメント -->
        @if ($purchase_waku_kengen)
        <span class = "pa-additional-info">
            @include('apply.components.detail_comment_temp', ['title' => '申請備考(業者－文化連)', 'comment_name' => 'pa_purchase_estimate_comment', 'detail' => $detail])
            </span>
        @endif


            <!-- 文化連コメント -->
        @if ($bunkaren_waku_kengen)
        <span class = "pa-additional-info">
            @include('apply.components.detail_comment_temp', ['title' => '申請備考(文化連)', 'comment_name' => 'pa_comment', 'detail' => $detail])
            </span>
        @endif   
    
        <div class="toggle-view-link d-print-none" data-target="pa-list" data-default="hide" data-message="申請履歴を非表示">申請履歴を表示</div>
        <table class="price-history pa-list">
            <caption>申請履歴</caption>
            <thead>
                <tr>
                    <th>申請日</th>
                    <th>ステータス</th>
                    <th>申請者</th>
                    <th>承認者</th>
                    <th>業者</th>
                    <th>薬価基準日</th>
                    <th>基準日薬価</th>
                    @if ($purchase_waku_kengen)
                        <th>見積仕入単価</th>
                        <th>仕入単価</th>
                    @endif
                    @if ($sales_waku_kengen)
                        <th>納入単価</th>
                    @endif
                    <th>申請単価有効期間</th>
                </tr>
            </thead>
            <tbody>
@foreach($detail->price_adoption_list as $pa_list)
                <tr>
                    <td>{{ $pa_list->application_date }}</td>
                    <td>{{ $pa_list->status_name }}</td>
                    <td>{{ $pa_list->apply_user_name }}</td>
                    <td>{{ $pa_list->approval_user_name }}</td>
                    <td>{{ $pa_list->trader_name }}</td>
                    <td>{{ $pa_list->basis_mediicine_price_date }}</td>
                    <td>@if (isset($pa_list->basis_mediicine_price)){{ number_format($pa_list->basis_mediicine_price, 0) }}@endif</td>
                    @if ($purchase_waku_kengen)
                        <td>@if (isset($pa_list->purchase_estimated_price)){{ number_format($pa_list->purchase_estimated_price, 0) }}@endif</td>
                        <td>@if (isset($pa_list->purchase_price)){{ number_format($pa_list->purchase_price, 0) }}@endif</td>
                    @endif
                    @if ($sales_waku_kengen)
                        <td>@if (isset($pa_list->sales_price)){{ number_format($pa_list->sales_price, 0) }}@endif</td>
                    @endif
                    <td>{{ $pa_list->start_date }} 〜 {{ $pa_list->end_date }}</td>
                </tr>
@endforeach
            </tbody>
        </table>

    <?php
    // TODO 仕様が固まったらコメントイン
    //         @include('layouts.comment', ['title' => '業者 - 文化連コメント', 'id' => 'comment-gyosya-bunkaren-table', 'list' => $comment_list_gyosya_bunkaren])
    //         <div class="step-view-link d-print-none" data-target="#comment-gyosya-bunkaren-table" data-message="3件だけ表示" data-view-num="3">全件表示する</div>
    ?>



            <h2>
                <span title="単価マスタに関する情報です" class="title">単価情報</span>
                <span class="toggle-view-link d-print-none fs16" data-target="fp-additional-info" data-default="open" data-message="　表示する">　非表示にする</span>
                <span class="id">ID：@if ($detail->fp_id){{ $detail->fp_id }}@else－@endif</span>
            </h2>
            <table class="medicine-detail-table price-data fp-additional-info">
                <tbody>
                    <tr>
                        <!-- 包装薬価 -->
                        @include('apply.components.detail_price_medicine', ['htmltitle' => '', 'title' => '包装薬価', 'medicine_price' => 'mp_unit_price', 'detail' => $detail])
        @if ($purchase_waku_kengen)
                        <!-- 仕入単価 -->
                        @include('apply.components.detail_price_facility', ['title' => '仕入単価', 'type' => 'purchase', 'detail' => $detail, 'require_flag' => False])
        @elseif ($sales_waku_kengen)
                        <!-- 納入単価 -->
                        @include('apply.components.detail_price_facility', ['title' => '納入単価', 'type' => 'sales', 'detail' => $detail, 'require_flag' => False])
        @else
                        <th colspan="2"></th>
        @endif
                    </tr>
                    <tr>
                        <!-- 単位薬価 -->
                        @include('apply.components.detail_price_medicine', ['htmltitle' => '', 'title' => '単位薬価', 'medicine_price' => 'mp_price', 'detail' => $detail])
        @if ($purchase_waku_kengen)
                        <!-- 仕入掛率 -->
                        @include('apply.components.detail_rate_fp', ['title' => '掛率', 'type' => 'purchase', 'detail' => $detail, 'medicine' => $medicine])
        @elseif ($sales_waku_kengen)
                        <!-- 納入掛率 -->
                        @include('apply.components.detail_rate_fp', ['title' => '掛率', 'type' => 'sales', 'detail' => $detail, 'medicine' => $medicine])
        @else
                        <th colspan="2"></th>
        @endif
                    </tr>
                    </tr>
                    <tr>
                        <th>包装薬価係数</th>
                        <td>
                            <div class="td-value-box">{{ $detail->pu_coefficient }}</div>
                        </td>
        @if ($purchase_waku_kengen)
                        <!-- 仕入値引率 -->
                        @include('apply.components.detail_rate_fp', ['title' => '値引率', 'type' => 'purchase', 'detail' => $detail, 'medicine' => $medicine])
        @elseif ($sales_waku_kengen)
                        <!-- 納入値引率 -->
                        @include('apply.components.detail_rate_fp', ['title' => '値引率', 'type' => 'sales', 'detail' => $detail, 'medicine' => $medicine])
        @else
                        <th colspan="2"></th>
        @endif
                    </tr>
                    <tr>
                        <th>薬価有効期間</th>
                        <td>
                            <div class="td-value-box">{{ $detail->mp_start_date }} ～ {{ $detail->mp_end_date }}</div>
                        </td>
                        <th colspan="2"></th>
                    </tr>
                    <tr>
        @if ($purchase_waku_kengen && $sales_waku_kengen)
                        <th colspan="2"></th>
                        <!-- 納入単価 -->
                        @include('apply.components.detail_price_facility', ['title' => '納入単価', 'type' => 'sales', 'detail' => $detail, 'require_flag' => False])
        @else
                        <th colspan="4">　</th>
        @endif
                    </tr>
                    <tr>
                        <th>業者</th>
                        <td>
                            @if ($purchase_waku_kengen || $sales_waku_kengen)
                                @if ($detail->edit_facility_price_kengen > 0)
                                    <div class="error-tip">
                                        <div class="error-tip-inner">{{ $errors->first('fp_purchase_user_group_id') }}</div>
                                    </div>
                                    <select class="form-control fp-edit" name="fp_purchase_user_group_id" id="fp_purchase_user_group_id" data-validate="empty">
                                        <option value="">選択してください</option>
                                            @foreach($sellers as $seller)
                                                <option value="{{ $seller->id }}" @if ($detail->fp_purchase_user_group_id == $seller->id) selected @endif >{{ $seller->name }}</option>
                                            @endforeach
                                    </select>
                                    @include('layouts.input_hidden', ['parent' => '[apply.detail]', 'name' => 'fp_purchase_user_group_id_check', 'value' => '1'])
                                @else
                                    <div class="td-value-box">
                                        @if (!empty($detail->fp_trader_name))
                                            {{ $detail->fp_trader_name }}
                                        @endif
                                        @include('layouts.input_hidden', ['parent' => '[apply.detail]', 'name' => 'fp_purchase_user_group_id_check', 'value' => '0'])
                                    </div>
                                @endif
                            @endif
                        </td>



        @if ($purchase_waku_kengen && $sales_waku_kengen)
                        <!-- 納入掛率 -->
                        @include('apply.components.detail_rate', ['title' => '掛率', 'type' => 'sales', 'detail' => $detail, 'medicine' => $medicine])
        @else
                        <th colspan="2"></th>
        @endif
                    </tr>
                    <tr>
                        <th>単価有効期間</th>
                        <td>
                            <div class="td-value-box">{{ $detail->fp_start_date }} ～ {{ $detail->fp_end_date }}</div>
                        </td>
        @if ($purchase_waku_kengen && $sales_waku_kengen)
                        <!-- 納入値引率 -->
                        @include('apply.components.detail_rate_fp', ['title' => '値引率', 'type' => 'sales', 'detail' => $detail, 'medicine' => $medicine])
        @else
                        <th colspan="2"></th>
        @endif
                    </tr>
                </tbody>
            </table>
            <div  title="直近3年度分の薬価と単価の履歴を表示します" class="toggle-view-link d-print-none" data-target="fp-list" data-default="hide" data-message="薬価・単価履歴を非表示">薬価・単価履歴を表示</div>
            <table class="price-history fp-list">
                <caption>薬価履歴</caption>
                <thead>
                    <tr>
                        <th>有効期間</th>
                        <th>薬価</th>
                    </tr>
                </thead>
                <tbody>
    @foreach($detail->medicine_price_list as $m_price)
                    <tr>
                        <td>{{ $m_price->start_date }} 〜 {{ $m_price->end_date }}</td>
                        <td>
                            <div class="td-value-box">{{ $medicine->getPackUnitPriceForDetail($detail->pu_coefficient, $m_price->price) }}</div>
                        </td>
                    </tr>
    @endforeach
                </tbody>
            </table>
    @if ($purchase_waku_kengen || $sales_waku_kengen)
            <table class="price-history fp-list">
                <caption>単価履歴</caption>
                <thead>
                    <tr>
                        <th>有効期間</th>
        @if ($purchase_waku_kengen)
                        <th>仕入単価</th>
        @endif
        @if ($sales_waku_kengen)
                        <th>納入単価</th>
        @endif
                        <th>業者</th>
                    </tr>
                </thead>
                <tbody>
        @foreach($detail->facility_price_list as $f_price)
                    <tr>
                        <td>{{ $f_price->start_date }} 〜 {{ $f_price->end_date }}</td>
            @if ($purchase_waku_kengen)
                        <td>
                            <div class="td-value-box">
                @if (isset($f_price->purchase_price))
                                {{ number_format($f_price->purchase_price, 0) }}
                @endif
                            </div>
                        </td>
            @endif
            @if ($sales_waku_kengen)
                        <td>
                            <div class="td-value-box">
                @if (isset($f_price->sales_price))
                                {{ number_format($f_price->sales_price, 0) }}
                @endif
                            </div>
                        </td>
            @endif
                        <td>{{ $f_price->trader_name }}</td>
                    </tr>
        @endforeach
                </tbody>
            </table>
    @endif

            <h2>
                <span title="採用マスタに関する情報です" class="title">採用情報</span>
                <span class="toggle-view-link d-print-none fs16" data-target="fm-additional-info" @if($detail->fm_id && $detail->fm_is_approval) data-default="open" data-message="　表示する">　非表示にする @else data-default="hide" data-message="　非表示にする">　表示する@endif </span>
                <span class="id">ID：@if ($detail->fm_id && $detail->fm_is_approval){{ $detail->fm_id }}@else－@endif</span>
            </h2>
            <table class="medicine-detail-table price-adoption-data fm-additional-info">
                <tbody>
                    <tr>
                        <th>採用者</th>
                        <td>@if ($detail->fm_id && $detail->fm_is_approval){{ $detail->fm_user_name }}@endif</td>
                        <th>更新者</th>
                        <td>@if ($detail->fm_id && $detail->fm_is_approval){{ $detail->fm_updater_name }}@endif</td>
                    </tr>
                    <tr>
                        <th>採用日時</th>
                        <td>@if ($detail->fm_id && $detail->fm_is_approval){{ $detail->fm_adoption_date }}@endif</td>
                        <th>更新日時</th>
                        <td>@if ($detail->fm_id && $detail->fm_is_approval){{ $detail->fm_updated_at }}@endif</td>
                    </tr>
                    <tr>
                        <th @if($detail->edit_fm_adoption_stop_date > 0) title="日付を削除するときは、スペースを入力して更新してください"@endif>採用中止日</th>
                        <td>
    @if($detail->edit_fm_adoption_stop_date > 0)
                            @include('layouts.input', ['parent' => '[apply.detail]', 'name' => 'fm_adoption_stop_date', 'value' => $detail->fm_adoption_stop_date, 'type' => 'text', 'class' => 'fm_adoption_stop_date col-sm-4 date', 'other_attr' => 'data-datepicker=true data-datepicker=true'])
    @else
                            <p class="form-control-static">{{ $detail->fm_adoption_stop_date }} </p>
    @endif
                        </td>
                        <th colspan="2"></th>
                    </tr>
                </tbody>
            </table>

<?php /* --------------------------------------------------------------------------- */ ?>
@if ($sales_waku_kengen)
<!-- 厚生連設定項目 -->

<!-- 病院設定項目 -->
    @if ($detail->fm_shisetsu_optional_key1_is_search_disp == 'true' ||
        $detail->fm_shisetsu_optional_key2_is_search_disp == 'true' ||
        $detail->fm_shisetsu_optional_key3_is_search_disp == 'true' ||
        $detail->fm_shisetsu_optional_key4_is_search_disp == 'true' ||
        $detail->fm_shisetsu_optional_key5_is_search_disp == 'true' ||
        $detail->fm_shisetsu_optional_key6_is_search_disp == 'true' ||
        $detail->fm_shisetsu_optional_key7_is_search_disp == 'true' ||
        $detail->fm_shisetsu_optional_key8_is_search_disp == 'true')
            <div class="hp-setting-container row-box flex-stretch fm-additional-info mt10">
                <div class="card-box type-3" id="hospital-division">
                    <label class="card-box-title-3">施設設定項目</label>
                    @include('apply.components.detail_optional', ['type' => 'shisetsu', 'key' => 'optional_key1', 'detail' => $detail, 'search_list' => $search_list])
                    @include('apply.components.detail_optional', ['type' => 'shisetsu', 'key' => 'optional_key2', 'detail' => $detail, 'search_list' => $search_list])
                    @include('apply.components.detail_optional', ['type' => 'shisetsu', 'key' => 'optional_key3', 'detail' => $detail, 'search_list' => $search_list])
                    @include('apply.components.detail_optional', ['type' => 'shisetsu', 'key' => 'optional_key4', 'detail' => $detail, 'search_list' => $search_list])
                    @include('apply.components.detail_optional', ['type' => 'shisetsu', 'key' => 'optional_key5', 'detail' => $detail, 'search_list' => $search_list])
                    @include('apply.components.detail_optional', ['type' => 'shisetsu', 'key' => 'optional_key6', 'detail' => $detail, 'search_list' => $search_list])
                    @include('apply.components.detail_optional', ['type' => 'shisetsu', 'key' => 'optional_key7', 'detail' => $detail, 'search_list' => $search_list])
                    @include('apply.components.detail_optional', ['type' => 'shisetsu', 'key' => 'optional_key8', 'detail' => $detail, 'search_list' => $search_list])
                </div>
            </div>
    @endif

<!-- 本部設定項目 -->            
    @if ($detail->fm_id != $detail->fm_honbu_id && 
        ($detail->fm_honbu_optional_key1_is_search_disp == 'true' ||
        $detail->fm_honbu_optional_key2_is_search_disp == 'true' ||
        $detail->fm_honbu_optional_key3_is_search_disp == 'true' ||
        $detail->fm_honbu_optional_key4_is_search_disp == 'true' ||
        $detail->fm_honbu_optional_key5_is_search_disp == 'true' ||
        $detail->fm_honbu_optional_key6_is_search_disp == 'true' ||
        $detail->fm_honbu_optional_key7_is_search_disp == 'true' ||
        $detail->fm_honbu_optional_key8_is_search_disp == 'true'))
            <div class="hq-setting-container row-box flex-stretch fm-additional-info mt10">
                <div class="card-box type-3" id="honbu-division">
                    <label class="card-box-title-3">本部参照項目</label>
                    @include('apply.components.detail_optional', ['type' => 'honbu', 'key' => 'optional_key1', 'detail' => $detail, 'search_list' => $search_list])
                    @include('apply.components.detail_optional', ['type' => 'honbu', 'key' => 'optional_key2', 'detail' => $detail, 'search_list' => $search_list])
                    @include('apply.components.detail_optional', ['type' => 'honbu', 'key' => 'optional_key3', 'detail' => $detail, 'search_list' => $search_list])
                    @include('apply.components.detail_optional', ['type' => 'honbu', 'key' => 'optional_key4', 'detail' => $detail, 'search_list' => $search_list])
                    @include('apply.components.detail_optional', ['type' => 'honbu', 'key' => 'optional_key5', 'detail' => $detail, 'search_list' => $search_list])
                    @include('apply.components.detail_optional', ['type' => 'honbu', 'key' => 'optional_key6', 'detail' => $detail, 'search_list' => $search_list])
                    @include('apply.components.detail_optional', ['type' => 'honbu', 'key' => 'optional_key7', 'detail' => $detail, 'search_list' => $search_list])
                    @include('apply.components.detail_optional', ['type' => 'honbu', 'key' => 'optional_key8', 'detail' => $detail, 'search_list' => $search_list])
                </div>
            </div>
    @endif

<?php /* --------------------------------------------------------------------------- */ ?>

            <!-- 備考コメント -->
            <span class = "fm-additional-info">
            @include('apply.components.detail_comment_temp', ['title' => '採用備考', 'comment_name' => 'fm_comment', 'detail' => $detail])
            </span>
@endif
    <?php
    // TODO 仕様が固まったらコメントイン
    //         @include('layouts.comment', ['title' => '備考コメント', 'id' => 'comment-note-table', 'list' => $comment_list_note])
    //         <div class="step-view-link d-print-none" data-target="#comment-note-table" data-message="3件だけ表示" data-view-num="3">全件表示する</div>
    ?>

            <script>
            jQuery(function() {
                // 最初に隠す
                $('.toggle-view-link').each(function (idx, elm) {
                    const defaultFlg = $(elm).data("default");
                    if (defaultFlg !== "hide") return;
                    const target = $(elm).data("target");
                    const targetElmObj = $("." + target);
                    targetElmObj.hide();
                });
                // クリックされたら表示非表示を切り替える
                $('.toggle-view-link').on("click", function () {
                    const target = $(this).data("target");
                    const message = $(this).data("message");
                    const text = $(this).text();
                    const targetElmObj = $("." + target);
                    if (targetElmObj.is(':visible')) {
                        targetElmObj.hide();
                    } else {
                        targetElmObj.show();
                    }
                    $(this).text(message);
                    $(this).data("message", text);
                });
                // 最初に隠す
                $('.step-view-link').each(function () {
                    const target = $(this).data("target");

                    const viewNum = $(this).data("view-num");
                    let viewNumCurrent = $(this).data("view-num-current");
                    if (viewNumCurrent === undefined) {
                        viewNumCurrent = viewNum;
                    }

                    if ($(target + " tr").length <= viewNum) {
                        $(this).hide();
                        return;
                    }

                    $(target + " tr").show();
                    if (viewNumCurrent > -1) {
                        $(target + " tr:gt(" + (viewNum - 1) + ")").hide();
                        $(this).data("view-num-current", -1);
                    } else {
                        $(this).data("view-num-current", viewNum);
                    }
                });
                // クリックされたら表示非表示を切り替える
                $('.step-view-link').on("click", function () {
                    const target = $(this).data("target");

                    const viewNum = $(this).data("view-num");
                    const viewNumCurrent = $(this).data("view-num-current");

                    // 文言入れ替え
                    const message = $(this).data("message");
                    const text = $(this).text();
                    $(this).text(message);
                    $(this).data("message", text);

                    $(target + " tr").show();
                    if (viewNumCurrent > -1) {
                        $(target + " tr:gt(" + (viewNum - 1) + ")").hide();
                        $(this).data("view-num-current", -1);
                    } else {
                        $(this).data("view-num-current", viewNum);
                    }
                });
            })
            </script>
        </div>
    <!-- [END] 2020/10/14 レイアウト修正による追加 -->

            <?php
                $index = 0;
                $selectList=$search_list;
            ?>

    @if (!empty($detail->m_id))
        <div class="mt15 pt15 block block-table-control">
            <div class="row">
                <div class="col-12 mb5 d-flex">
                    <h4>他施設採用状況</h4>
                </div>
            </div>
            <div class="row">
                <div class="col-12 d-flex justify-content-between align-items-center"><a class="anchor anchor-accordion anchor-payment-state" href="#collapseExample" data-toggle="collapse" aria-expanded="false" aria-controls="collapseExample">詳細を表示する</a></div>
            </div>
            <div class="collapse" id="collapseExample" style="">
                <div class="row">
                    <div class="col-12 d-flex justify-content-between align-items-center">
                        <p class="fs16 text">@if ( $page_count > $list->total() ){{ $list->total() }} @else @if($pager->current == $pager->last){{ ($list->total() - ($page_count * ($pager->current - 1) )) }} @else{{ $page_count }}@endif @endif件 / 全{{ $list->total() }}件</p>
                        <!--div class="d-flex align-items-center block">
                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                <label class="btn btn-default btn-search-count @if( $page_count == '20' ) active @endif" data-search-count="20">20件表示</label>
                                <label class="btn btn-default btn-search-count @if( $page_count == '50' ) active @endif" data-search-count="50">50件表示</label>
                                <label class="btn btn-default btn-search-count @if( $page_count == '100' ) active @endif" data-search-count="100">100件表示</label>
                            </div>
                        </div-->
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <table class="table table-primary table-small" id="statement">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>施設</th>
                                    <th>採用日</th>
                                    <th>採用中止日</th>
        @foreach(getFiscalYearHistory() as $value)
                                    <th>{{$value}}</th>
        @endforeach
                                </tr>
                            </thead>
                            <tbody>
        @foreach($list as $apply)
                                <tr>
                                    <td>{{ $loop->iteration + ( $page_count * ($pager->current -1) ) }}</td>
                                    <td>{{ $apply->facility_name }}</td>
                                    <td>{{ $apply->adoption_date }}</td>
                                    <td>{{ $apply->fm_adoption_stop_date }}</td>
                                    <td>{{ $apply->before1cnt }}</td>
                                    <td>{{ $apply->before2cnt }}</td>
                                    <td>{{ $apply->before3cnt }}</td>
                                </tr>
        @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
        @if ($pager->max > 1)
                <div class="row mt15">
                    <div class="col-12">
                        <nav aria-label="page">
                            <ul class="pagination d-fex justify-content-center">
                                <li class="page-item"><a class="page-link" data-page="1"><span aria-hidden="true">&laquo;</span></a></li>
                                <li class="page-item @if ($pager->current==1)disabled @endif">
                                    <a class="page-link" data-page="{{ $pager->current - 1}}"><span aria-hidden="true">&lt;</span></a>
                                </li>
            @for ($i = $pager->first; $i <= $pager->last; $i++)
                                <li class="page-item @if($pager->current == $i) active @endif"><a class="page-link" data-page="{{$i}}">{{$i}}</a></li>
            @endfor
                                <li class="page-item @if ($pager->current==$pager->last)disabled @endif">
                                    <a class="page-link" data-page="{{ $pager->current + 1}}"><span aria-hidden="true">&gt;</span></a>
                                </li>
                                <li class="page-item"><a class="page-link" data-page="{{ $pager->last }}"><span aria-hidden="true">&raquo;</span></a></li>
                            </ul>
                        </nav>
                    </div>
                </div>
        @endif
            </div>
        </div>
    @endif


</div>

<div class="card-footer d-flex justify-content-around pt15 pb15 mt15 d-print-none">
    @include('apply.components.detail_button_group', ['detail' => $detail])
</div>
</form>

<script>
    /**
     * アクションボタンの二重クリックを防止するロジック
     * ローディングアイコンも表示する
     */
    function ignoreDuplicateClick($thisElm) {
        if (!$thisElm.hasClass('clicked')) {
            $thisElm.addClass('clicked');
            $thisElm.append('<i class="loading-icon fa fa-spinner fa-pulse ml-2">');
        } else {
            $thisElm.prop("disabled", true);
        }
    }
    /**
     * detail_button.blade.phpの中から呼び出される。
     * 画面遷移の前にformタグの属性や、その中のinput hiddenの値を更新することで、送信パラメータを動的に設定する。
     */
    function setFormInfo($thisElm, action, method, apply_control, button_control) {
        var $formTag = $('#apply-edit');
        $formTag.attr('action', action);
        $formTag.attr('method', method);
        $('#button_control').val(apply_control);
        $('#apply_control').val(button_control);
        // 二重クリック防止
        ignoreDuplicateClick($thisElm)
    }
</script>

{{-- @include('apply.components.detail_button_dialog', ['type' => 'reject', 'apply_control' => 3, 'detail' => $detail])
@include('apply.components.detail_button_dialog', ['type' => 'remand', 'apply_control' => 2, 'detail' => $detail])
@include('apply.components.detail_button_dialog', ['type' => 'forward', 'apply_control' => 1, 'detail' => $detail])
@include('apply.components.detail_button_dialog', ['type' => 'withdraw', 'apply_control' => 1, 'detail' => $detail])

<div class="fade modal" id="modal-action" role="dialog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">採用申請更新</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="tac text">この薬品を更新してよろしいでしょうか？</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
                <button class="btn2 btn-primary" type="submit" data-form="#apply-edit">更新する</button>
            </div>
        </div>
    </div>
</div> --}}

<div class="fade modal" id="medicine-price-history" role="dialog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">薬価履歴</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <table class="table table-primary table-small" id="applies">
                    <thead>
                        <tr>
                            <th>有効期間</th>
                            <th>薬価（円）</th>
                        </tr>
                    </thead>
                    <tbody>
@foreach($detail->medicine_price_list as $m_price)
                        <tr>
                            <td>{{ $m_price->start_date }} 〜 {{ $m_price->end_date }}</td>
                            <td>{{ $medicine->getPackUnitPriceForDetail($detail->pu_coefficient, $m_price->price) }}</td>
                        </tr>
@endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="fade modal" id="facility-price-history" role="dialog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">単価履歴</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <table class="table table-primary table-small" id="applies">
                    <thead>
                        <tr>
                            <th>有効期間</th>
                            <th>単価（円）</th>
                        </tr>
                    </thead>
                    <tbody>
@if (!empty($detail->facility_price_list))
    @foreach($detail->facility_price_list as $f_price)
                        <tr>
                            <td>{{ $f_price->start_date }} 〜 {{ $f_price->end_date }}</td>
                            <td>{{ number_format($f_price->sales_price,2) }}</td>
                        </tr>
    @endforeach
@endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

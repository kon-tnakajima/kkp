<?php
use SebastianBergmann\CodeCoverage\Report\PHP;
use App\Model\Task;
?>
@extends('layouts.app')

@php($title = '採用管理')

@php($category = 'apply')

@php($breadcrumbs = ['HOME'=>'/','採用管理'=>'javascript:void(0);'])

@section('content')
@if ( !empty($viewdata->get('errorMessage')) )
<div class="alert alert-danger" role="alert">{{
    $viewdata->get('errorMessage') }}</div>
@endif @if ( !empty($viewdata->get('message')) )
<div class="alert alert-success" role="alert">{{
    $viewdata->get('message') }}</div>
@endif

<?php
/*
 * 権限の取得
 */
$kengen               = $viewdata->get('kengen');
$purchase_waku_kengen = $kengen["医薬品採用申請_仕入価枠表示"]->privilege_value == "true";
$sales_waku_kengen    = $kengen["医薬品採用申請_納入価枠表示"]->privilege_value == "true";
$bunkaren_waku_kengen = $kengen["医薬品採用申請_文化連枠表示"]->privilege_value == "true";
$new_regist_kengen    = $kengen["医薬品採用申請_採用申請(メディコード未搭載品)"]->privilege_value == "true";
$admin_kengen         = $kengen["一覧SQLダウンロード"]->privilege_value == "true";
?>

<?php
/*
 * =====================================================================================================================
 * 検索条件部分のCSV
 */
?>
<style>
.input-item > p {
    white-space: nowrap;
    font-weight: bold;
    margin-right: 0.3em;
}

.input-item>p:after {
    content: ""
}

table.search-collapse {
    width: auto;
}

table.search-collapse tr td {
    padding-top: 0.3em;
    padding-right: 0.3em;
}

label.generic {
    position: relative;
    margin: 0;
}

label.generic span.text {
    position: relative;
    cursor: pointer;
    white-space: nowrap;
    z-index: 2;
}

label.generic input ~ span.deco {
    display: block;
    font-size: inherit;
    z-index: 1;
}

label.generic.normal {
    display: -webkit-box;
    display: -ms-flexbox;
    display: -webkit-flex;
    display: flex;
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
    cursor: pointer;
}

label.generic.normal input,
label.generic.normal select {
    flex: 1;
}

label.generic.normal:hover,
table.search-collapse label.generic.normal.check-box:hover,
table.search-collapse label.generic.normal.radio-box:hover,
label.generic.normal:hover input ~ span {
    background-color: lightgoldenrodyellow;
}

label.generic.normal input ~ span {
    overflow: hidden;
    text-overflow: ellipsis;
}

#search-status-detail label.generic.normal input ~ span.text {
    width: 7em;
}

label.generic.normal input ~ span.deco {
    display: -webkit-box;
    display: -ms-flexbox;
    display: -webkit-flex;
    display: flex;
    -webkit-box-orient: horizontal;
    -webkit-box-direction: normal;
    -ms-flex-direction: row;
    flex-direction: row;
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
    -webkit-box-pack: center;
    -ms-flex-pack: center;
    justify-content: center;
    width: 1em;
    height: 1em;
    min-width: 1em;
    min-height: 1em;
    margin-right: 0.2em;
    border: 1px solid rgb(103, 136, 152);
}

label.generic.normal input:checked ~ span.deco {
    background-color: rgb(32, 168, 216);
    color: white;
    border: none;
}

label.generic.normal input:active ~ span.deco, label.generic.normal input:active:checked
    ~ span.deco {
    background-color: rgb(182, 228, 244);
    border: none;
}

label.generic.normal input:focus ~ span.deco {
    outline: 3px solid rgb(182, 228, 244);
}

label.generic.normal input:checked ~ span.deco:before {
    content: "✔";
    font-size: 10px;
}

label.generic.arrow {
    margin-right: 1em;
}

label.generic.arrow input ~ span.text {
    border: 1px solid black;
    border-right: none;
    border-radius: 0.5em 0 0 0.5em;
    padding: 0.2em 0.5em;
    padding-right: 0;
    background-color: cyan;
}

label.generic.arrow input ~ span.deco {
    position: absolute;
    right: 0;
    top: 0;
    height: 1.43em;
    width: 1.43em;
    transform: translateY(4.5px) translateX(50%) rotate(45deg);
    transform-origin: center;
    border: 1px solid black;
    background-color: cyan;
}

label.generic.arrow input:checked ~ span {
    background-color: #fc0;
}

.search-popular_name > span,
.search-code > span {
    width: 4.2em;
    min-width: 4.2em;
    max-width: 4.2em;
}

.search-dosage_type_division > span,
.search-generic_product_detail_devision > span {
    width: 5.2em;
    min-width: 5.2em;
    max-width: 5.2em;
}

.search-dosage_type_division > select,
.search-generic_product_detail_devision > select {
    width: 6.5em;
    min-width: 6.5em;
    max-width: 6.5em;
}

.search-code input {
    width: calc(6em + 3rem);
    min-width: calc(6em + 3rem);
    max-width: calc(6em + 3rem);
}

.search-coefficient input {
    width: calc(3em + 3rem);
    min-width: calc(3em + 3rem);
    max-width: calc(3em + 3rem);
}

.optional_area {
    width: 100%;
}

.optional_area>* {
    margin-top: 0.3em !important;
}

.optional_area > span.optional-label {
    font-weight: bold;
    margin-right: 0.5em;
}

.optional_area > label {
    margin-right: 0.3em;
}

.optional_area > label > span.text {
    cursor: pointer;
}

.optional_area label.generic.normal input {
    flex: 0;
    width: calc(10em + 3rem);
    min-width: calc(10em + 3rem);
}

.form-group {
    margin-bottom: 0;
    padding: 0.25rem;
}

.form-group:hover {
    background-color: lightyellow
}

.form-control-label {
    display: inline-block;
    width: 8em;
    margin-bottom: 0;
    vertical-align: middle;
}

.medicine_value {
    margin-left: 1rem;
    display: inline-block;
    width: calc(100% - 8em - 1.5rem);
    vertical-align: middle;
}

.popover-header {
    padding: 0.7rem 1rem;
}

.popover-body {
    padding: 0.5rem;
}

.popover-footer {
    padding: 0.5rem;
    border: none;
}
.medicine-info:hover .abs-button {
    visibility: visible;
}
.abs-button {
    position: absolute;
    display: inline-block;
    visibility: hidden;
    right: 0.5rem;
    top: 50%;
    transform: translateY(-50%);
}
.abs-button > * {
    width: auto !important;
}

.page-item {
    cursor: pointer;
}

.th {
    font-weight: 400 !important;
}

#apply {
    gap: 0.2rem;
}
</style>

<?php
/*
 * =====================================================================================================================
 * 検索結果部分のCSV
 */
?>
<style>
.column-hide {
    display: none !important;
}

.mode-radio-container {
    display: -webkit-inline-box;
    display: -ms-inline-flexbox;
    display: -webkit-inline-flex;
    display: inline-flex;
    -webkit-box-orient: horizontal;
    -webkit-box-direction: normal;
    -ms-flex-direction: row;
    flex-direction: row;
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
    -webkit-box-pack: center;
    -ms-flex-pack: center;
    justify-content: center;
}

.mode-radio-container > * {
    margin: 0;
}

.mode-radio-container > label > input {
    display: none;
}

.mode-radio-container > label > input + span {
    border: solid 1px #ccc;
    padding: .375rem .75rem;
    cursor: pointer;
}

.mode-radio-container > label:not(:first-of-type) > input + span {
    border-left: none;
}

.mode-radio-container > label > input:checked + span {
    background-color: #390;
    color: white;
}

.mode-radio-container > label:hover > input:not(:checked) + span {
    background-color: #f2f2f2;
}

/* .mode-radio-container > span {
    font-weight: bold;
} */

.btn-search-count {
    cursor: pointer;
}
.btn-search-count:focus {
    box-shadow: 0 0 0 .2rem rgba(0,123,255,.75);
}
.page-link:focus {
    box-shadow: 0 0 0 .2rem rgba(0,123,255,.75);
}
.task-btn:focus,
.task-btn:hover {
    box-shadow: 0 0 0 .2rem rgba(0,123,255,.75);
    background: rgb(0, 0, 0) !important;
}

table {
    width: 100%;
    height: 100%;
    color: black !important;
}
table#applies {
    border-color: #ccc;
    border-width: 1px;
    border-style: solid;
}

table tr {
    width: 100%;
    padding: 0;
    /* height: 100%; */
}

table tr>th,
table tr>td {
    box-sizing: content-box !important;
    padding: 0;
}

table th {
    white-space: nowrap;
    font-weight: 400 ;
    /* height: 100%; */
}

.row-box {
    display: -webkit-inline-box;
    display: -ms-inline-flexbox;
    display: inline-flex;
    -webkit-box-orient: horizontal;
    -webkit-box-direction: normal;
    -ms-flex-direction: row;
    flex-direction: row;
    -webkit-box-align: start;
    -ms-flex-align: start;
    align-items: flex-start;
    -webkit-box-pack: start;
    -ms-flex-pack: start;
    justify-content: flex-start;
}

.card-body {
    overflow: auto;
}

.card-body table.table-primary tr th,
.card-body table.table-primary tr td {
    padding: 0.2rem;
}

.card-body table tr td {
    text-align: left;
}

.text-right {
    text-align: right;
}

.text-center {
    text-align: center;
}

.flex-end-self {
    -ms-flex-item-align: end;
    align-self: flex-end;
}

.flex-center-self {
    -ms-flex-item-align: center;
    align-self: center;
}

.card-body table tr td > div:not(.popover-custom) {
    display: -webkit-box;
    display: -ms-flexbox;
    display: -webkit-flex;
    display: flex;
    -webkit-box-orient: vertical;
    -webkit-box-direction: normal;
    -ms-flex-direction: column;
    flex-direction: column;
    -webkit-box-align: start;
    -ms-flex-align: start;
    align-items: flex-start;
    -webkit-box-pack: justify;
    -ms-flex-pack: distribute;;
    justify-content: space-around;
    line-height: 1.5em;
    min-height: calc(1.5em * 2);
    height: 100%;
}

.sales-info {
    width: calc((0.8rem + 1px) * 10.5 + 2px);
    min-width: calc((0.8rem + 1px) * 10.5 + 2px);
    max-width: calc((0.8rem + 1px) * 10.5 + 2px);
}

.sales-info > * > .row-box > *:first-child {
    flex: 1;
    width: calc((0.8rem + 1px) * 7 + 2px);
    min-width: calc((0.8rem + 1px) * 7 + 2px);
    max-width: calc((0.8rem + 1px) * 7 + 2px);
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}

.sales-info > * > * {
    width: calc((0.8rem + 1px) * 10.5 + 2px);
    min-width: calc((0.8rem + 1px) * 10.5 + 2px);
    max-width: calc((0.8rem + 1px) * 10.5 + 2px);
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}
.medicine-info,
.medicine-info > * {
    min-width: calc((0.8rem + 1px) * 4 + 2px);
}
.medicine-info > * > *,
.trader-facillity > * > * {
    width: 100%;
}

.medicine-info .error-message {
    color: red;
    font-weight: bold;
    white-space: pre-line;
    border-top: 1px dashed #ccc;
}

.trader-facillity {
    width: calc((0.8rem + 1px) * 6 + 2px);
    min-width: calc((0.8rem + 1px) * 6 + 2px);
    max-width: calc((0.8rem + 1px) * 6 + 2px);
}

.trader-facillity > * > * {
    display: -webkit-box;
    display: -ms-flexbox;
    display: -webkit-flex;
    display: flex;
    -webkit-box-orient: horizontal;
    -webkit-box-direction: normal;
    -ms-flex-direction: row;
    flex-direction: row;
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
    -webkit-box-pack: center;
    -ms-flex-pack: center;
    justify-content: center;
}

.trader-facillity > * > * > * {
    width: 100%;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}

.product-detail-devision {
    color: var(--indigo);
    white-space: nowrap;
}

.standard-unit {
    border-top: 1px dashed #ccc;
}
.no,
.coefficient,
.medicine-price,
.pack-unit-price,
.purchase-requested-price,
.purchase-requested-nebiki-rate,
.purchase-estimated-price,
.purchase-estimated-nebiki-rate,
.purchase-price,
.purchase-nebiki-rate,
.sales-price,
.sales-rate {
    white-space: nowrap;
}

.no {
    width: 0;
}

.coefficient {
    width: calc((0.8rem + 1px) * 3.5 + 2px);
    min-width: calc((0.8rem + 1px) * 3.5 + 2px);
    max-width: calc((0.8rem + 1px) * 3.5 + 2px);
}

.medicine-price,
.pack-unit-price {
    width: calc((0.8rem + 1px) * 5 + 2px);
    min-width: calc((0.8rem + 1px) * 5 + 2px);
    max-width: calc((0.8rem + 1px) * 5 + 2px);
}

.purchase-requested-price,
.purchase-estimated-price,
.purchase-price,
.sales-price {
    width: calc((0.8rem + 1px) * 4 + 2px);
    min-width: calc((0.8rem + 1px) * 4 + 2px);
    max-width: calc((0.8rem + 1px) * 4 + 2px);
}

.purchase-requested-nebiki-rate,
.purchase-estimated-nebiki-rate,
.purchase-nebiki-rate,
.sales-rate {
    width: calc((0.8rem + 1px) * 4 + 2px);
    min-width: calc((0.8rem + 1px) * 4 + 2px);
    max-width: calc((0.8rem + 1px) * 4 + 2px);
}

.status-date {
    width: calc((0.8rem + 1px) * 5 + 2px);
    min-width: calc((0.8rem + 1px) * 5 + 2px);
    max-width: calc((0.8rem + 1px) * 5 + 2px);
}


.bunkaren-apply-task {
    width: 11rem !important;
    min-width: 11rem !important;
    max-width: 11rem !important;
}
.apply-task {
    width: 4.5rem;
    min-width: 4.5rem;
    max-width: 4.5rem;
}


.col-w-2 {
    width: calc((0.8rem + 1px) * 0.5 * 2 + 2px);
    min-width: calc((0.8rem + 1px) * 0.5 * 2 + 2px);
    max-width: calc((0.8rem + 1px) * 0.5 * 2 + 2px);
}

.col-w-3 {
    width: calc((0.8rem + 1px) * 0.5 * 3 + 2px);
    min-width: calc((0.8rem + 1px) * 0.5 * 3 + 2px);
    max-width: calc((0.8rem + 1px) * 0.5 * 3 + 2px);
}

.col-w-4 {
    width: calc((0.8rem + 1px) * 0.5 * 4 + 2px);
    min-width: calc((0.8rem + 1px) * 0.5 * 4 + 2px);
    max-width: calc((0.8rem + 1px) * 0.5 * 4 + 2px);
}

.col-w-5 {
    width: calc((0.8rem + 1px) * 0.5 * 5 + 2px);
    min-width: calc((0.8rem + 1px) * 0.5 * 5 + 2px);
    max-width: calc((0.8rem + 1px) * 0.5 * 5 + 2px);
}

@media print {
    * {
        overflow: visible !important;
    }
}

.btn-sm {
    padding:.25rem .3rem !important;
}

.btn2-sm {
    padding:.25rem .3rem !important;
    font-size: .76562rem;
    line-height: 1.5;
    width: 6em;
    border: 1px solid transparent;
}

.btn2-sm:focus {
    box-shadow: 0 0 0 .2rem rgba(0,123,255,.75);
}

.text-bold {
    font-weight: bold;
}

.page-item:not(.active) a.page-link {
    color: inherit !important;
}
.table-primary tr:hover td {background-color: #fdeebd !important;}
.apply-tr-forcus {background-color: #fdeebd !important;}
.apply table tr.adoption-stop-apply td {background-color:#E6E6E6}
.apply table tr.target-apply td {background-color:#ffe0e0}
.target-apply-input {background-color:#fff5f5}
.apply-input-forcus {background-color:white}


.hospital-group-label {
    font-weight: bold;
    min-width: 6em;
    margin-right: 0.2rem;
    cursor: pointer;
}
.hospital-group-label:before {
    border: 1px solid gray;
    font-weight: bold;
    margin-right: 0.1rem;
    width: 1em;
    height: 1em;
    line-height: 1em;
    padding: 0.2rem;
    display: -webkit-box;
    display: -ms-flexbox;
    display: -webkit-flex;
    display: flex;
    -webkit-box-pack: center;
    -ms-flex-pack: center;
    justify-content: center;
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
}
.hospital-group-label.all:before {
    content: "✓";
}
.hospital-group-label.partially:before {
    content: "■";
    color: #666;
}
.hospital-group-label.zero:before {
    content: "　";
}
.hospital-group-label:hover {
    background-color: lightgoldenrodyellow;
}
#selected-hospital-container {
    gap: 0.2rem;
    min-height: 1.2em;
}
#selected-hospital-container > div {
    padding: 0.1rem 0.3rem;
    cursor: pointer;
    height: 1.2em;
    box-sizing: border-box;
}
#selected-hospital-container > div:hover {
    background-color: lightgoldenrodyellow;
}
#selected-hospital-container > div:before {
    content: "✓";
    border: 1px solid gray;
    font-weight: bold;
    margin-right: 0.1rem;
    width: 1em;
    height: 1em;
    line-height: 1em;
    padding: 0.2rem;
    display: -webkit-box;
    display: -ms-flexbox;
    display: -webkit-flex;
    display: flex;
    -webkit-box-pack: center;
    -ms-flex-pack: center;
    justify-content: center;
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
}

.dynamic-update-input {
    width: 100%;
    text-align: right;
}

.ajax-update-target-bunkaren.forward {
    width: 4rem !important;
    min-width: 4rem !important;
    max-width: 4rem !important;
}
.ajax-update-target-bunkaren.reject {
    width: 3.5rem !important;
    min-width: 3.5rem !important;
    max-width: 3.5rem !important;
}
.ajax-update-target-bunkaren.backward {
    width: 2.5rem !important;
    min-width: 2.5rem !important;
    max-width: 2.5rem !important;
}

#copy-dialog {
    position: fixed;
    border: 1px solid gray;
    top: 50%;
    left: 50%;
    background-color: white;
    transform: translate(-50%, -50%);
    padding: 1em;
}
#copy-dialog.hide {
    display: none !important;
}
#copy-dialog .title {
    font-size: 120%;
}
.pd-0{
    padding: 0!important;
}
</style>
@include('components.search_hospital_group_css')

<script src="https://code.jquery.com/jquery-3.3.1.min.js"
    integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
    crossorigin="anonymous">
</script>

@if (!is_null($viewdata->get('list')))
<div class="card card-primary apply">
    <div class="pt-0 card-body">
        <form class="form-horizontal flex-column-stretch-top" method="get" id="apply" accept-charset="utf-8">
            <input type="hidden" data-search-item="is_search" value="1">

            <div class="flex-row-space-between-center">
<?php
/*
 * =====================================================================================================================
 * 「検索条件をクリア」
 */
?>
                <a class="anchor anchor-clear-search mr40" href="javascript:void(0);">
                    <i class="fas fa-eraser mr10"></i>検索条件をクリア
                </a>
<?php
/*
 * =====================================================================================================================
 * 「もっと検索条件を見る」
 */
$is_condition_search = $viewdata->get('conditions')['is_condition_search'];
?>
                <a
                    class="anchor anchor-accordion anchor-search-condition {{ $is_condition_search ? 'open' : '' }}"
                    href="#collapseSearch" data-toggle="collapse"
                    aria-expanded="{{ $is_condition_search }}"
                    aria-controls="collapseSearch"
                    onClick="const val = $('#collapseSearch').hasClass('show'); $('#is_condition_search').val(!val);"
                >もっと検索条件を見る</a>
                <input type="hidden" id="is_condition_search" name="is_condition_search" data-search-item="is_condition_search" value="{{ $is_condition_search }}">
            </div>
            <div class="flex-row-stretch-center" style="gap: .5em;">
<?php
/*
 * =====================================================================================================================
 * 検索条件フリー入力欄
 */
?>
                <div class="align-items-center" style="flex: 1;">
                    <input
                        class="form-control"
                        id = "search-box"
                        type="search"
                        data-search-item="search"
                        placeholder="JAN、GS1、販売メーカー、商品名、規格で検索"
                        value="{{ $viewdata->get('conditions')['search'] }}"
                        autofocus
                    >
                </div>
    <?php
    /*
     * =================================================================================================================
     * 「検索する」ボタン
     */
    ?>
                <button class="btn btn-primary btn-table-search" type="button" style="min-width: 14em;">
                    <i class="fas fa-search mr10"></i>検索する
                </button>
            </div>
            <div class="flex-row-left-center">
    <?php
    /*
     * =================================================================================================================
     * 検索条件「私が担当」チェックボックス
     */
    ?>
                <table class="search-collapse">
                    <tr>
                        <td class = 'text-bold'>ｽﾃｰﾀｽ</td>
                        <td>
                            <label class="generic normal search-my_charge radio-box">
                                <input
                                    class="custom-control-input validate input-radio"
                                    id="simple_search"
                                    name="simple_search"
                                    data-search-item="simple_search"
                                    data-search-type="radio"
                                    value="my_charge"
                                    type="radio"
    @if($viewdata->get('conditions')['simple_search'] == "my_charge")
                                    checked
    @endif
                                >
                                <span class="deco"></span>
                                <span title="ステータスを進められる申請マスタを抽出できます" class="text">私が担当　</span>
                            </label>
                        </td>

    <?php
    /*
        * =================================================================================================================
        * 検索条件「私が担当(関連申請含む)」チェックボックス
        */
    ?>
    @if ($bunkaren_waku_kengen)
                        <td>
                            <label class="generic normal radio-box">
                                <input
                                    class="custom-control-input validate input-radio"
                                    id="simple_search"
                                    name="simple_search"
                                    data-search-item="simple_search"
                                    data-search-type="radio"
                                    value="my_charge_including_related"
                                    type="radio"
                                    @if($viewdata->get('conditions')['simple_search'] == "my_charge_including_related")
                                    checked
                                    @endif
                                >
                                <span class="deco"></span>
                                <span title="「私が担当」の申請とそれに紐づく申請を抽出できます" class="text">私が担当(関連申請含む)　</span>
                            </label>
                        </td>
    @endif

    <?php
    /*
     * =================================================================================================================
     * 検索条件「私が申請」チェックボックス
     */
    ?>
    @if (!$bunkaren_waku_kengen)
                       <td>
                           <label class="generic normal search-my_apply radio-box">
                               <input
                                   class="custom-control-input validate input-radio"
                                   id="simple_search"
                                   name="simple_search"
                                   data-search-item="simple_search"
                                   data-search-type="radio"
                                   value="my_apply"
                                   type="radio"
    @if($viewdata->get('conditions')['simple_search'] == "my_apply")
                                   checked
    @endif
                               >
                               <span class="deco"></span>
                               <span class="text">{{ Auth::user()->userGroup()->name }}が申請　</span>
                            </label>
                        </td>
    @endif
    <?php
    /*
     * =================================================================================================================
     * 検索条件「申請進行中(交渉依頼待ち～採用待ち)」チェックボックス
     */
    ?>
                       <td>
                           <label class="generic normal search-is_applying radio-box">
                               <input
                                   class="custom-control-input validate input-radio"
                                   id="simple_search"
                                   name="simple_search"
                                   data-search-item="simple_search"
                                   data-search-type="radio"
                                   value="is_applying"
                                   type="radio"
    @if($viewdata->get('conditions')['simple_search'] == "is_applying")
                                   checked
    @endif
                               >
                               <span class="deco"></span>
                               <span title="ステータスが交渉依頼待ち～採用待ちの申請マスタを抽出できます" class="text">申請進行中(交渉依頼待ち～採用待ち)　</span>
                            </label>
                        </td>



    <?php
    /*
     * =================================================================================================================
     * 採用済み・採用可チェックボックス
     */
    ?>
                <div class="input-item flex-row-left-top">
                    <div class="check-box-container flex-row-left-center flex-wrap">
    @foreach($viewdata->get('tasks_min') as $key => $value)
    <td>
                        <label class="generic normal radio-box">
                            <input
                                class="custom-control-input validate input-radio"
                                id="simple_search"
                                name="simple_search"
                                data-search-item="simple_search"
                                data-search-type="radio"
                                value="{{ $key }}"
                                type="radio"
        {{-- @if( $key, $viewdata->get('conditions')['simple_search'])) --}}
        @if($viewdata->get('conditions')['simple_search'] == $key)
                                checked
        @endif
                            >
                            <span class="deco"></span>
                            <span class="text" title="{{ $value }}">{{ $value }}　</span>
                        </label>
                        </td>
    @endforeach
                    </div>
                </div>

    <?php
    /*
     * =================================================================================================================
     * 検索条件「要受諾依頼」チェックボックス
     */
    ?>
    @if ($bunkaren_waku_kengen)
                            <td>
                                <label class="generic normal radio-box">
                                    <input
                                        class="custom-control-input validate input-radio"
                                        id="simple_search"
                                        name="simple_search"
                                        data-search-item="simple_search"
                                        data-search-type="radio"
                                        value="need_request"
                                        type="radio"
                                        @if($viewdata->get('conditions')['simple_search'] == "need_request")
                                        checked
                                        @endif
                                    >
                                    <span class="deco"></span>
                                    <span title="施設：交渉区分施設、ステータス：見積回答待ち・受諾依頼待ち、申請日：2営業日前以前" class="text">要受諾依頼　</span>
                                </label>
                            </td>

    @endif
    <?php
    /*
     * =================================================================================================================
     * 検索条件「要見積提出」チェックボックス
     */
    ?>
    @if ($bunkaren_waku_kengen)
                            <td>
                                <label class="generic normal radio-box">
                                    <input
                                        class="custom-control-input validate input-radio"
                                        id="simple_search"
                                        name="simple_search"
                                        data-search-item="simple_search"
                                        data-search-type="radio"
                                        value="need_estimate"
                                        type="radio"
                                        @if($viewdata->get('conditions')['simple_search'] == "need_estimate")
                                        checked
                                        @endif
                                    >
                                    <span class="deco"></span>
                                    <span title="施設：厚生連、ステータス：見積回答待ち～見積提出待ち、申請日：2営業日前以前" class="text">要見積提出　</span>
                                </label>
                            </td>
    @endif
                    </tr>
                </table>
            </div>

    <?php
    /*
     * =================================================================================================================
     * 検索条件「施設」チェックボックス
     */

    // 常に表示する施設グループの数
    $hospitalGroupsDefaultCount = 1;

    // 施設グループを畳んでいるかどうかをセッションから取得
    $is_condition_hospital = $viewdata->get('conditions')['is_condition_hospital'];

    // ユーザグループのグループごとにまとめて表示するためにデータ構築（1次配列からネストされた連想配列へ）
    $hospitalGroups = array();
    foreach($viewdata->get('user_groups') as $group) {
        $group_name = $group->ken_name;
        if (!array_key_exists($group_name, $hospitalGroups)) {
            $hospitalGroups[$group_name] = array();
        }
        array_push($hospitalGroups[$group_name], $group);
    }
    ?>
            @include('components.search_hospital_group_script')
            <div class="input-item flex-row-left-top">
                <p class="flex-row-left-center">施設</p>
                <div class="flex-column-left-top">
                    <div id="selected-hospital-container" class="flex-row-left-center flex-wrap">
    @if (count($hospitalGroups) > $hospitalGroupsDefaultCount)
                    <a
                        class="mr2 anchor anchor-accordion anchor-search-condition {{ $is_condition_hospital ? 'open' : '' }} flex-row-left-center"
                        href="#collapseHospital" data-toggle="collapse"
                        aria-expanded="{{ $is_condition_hospital }}"
                        aria-controls="collapseHospital"
                        data-open-text="more"
                        data-close-text="close"
                        onClick="const val = $('#collapseHospital').hasClass('show'); $('#is_condition_hospital').val(!val);"
                    >more</a>
    @endif
    <?php
        foreach($viewdata->get('user_groups') as $group) {
            if(in_array($group->user_group_id, $viewdata->get('conditions')['user_group'])) {
    ?>
                    <div class="flex-row-left-center" onclick="onClickSelectedHospital(this)">{{ $group->user_group_name }}</div>
    <?php
            }
        }
    ?>
                    </div>
                    @include('components.search_hospital_group', ['hospitalGroups' => $hospitalGroups, 'isDefault' => true, 'hospitalGroupsDefaultCount' => $hospitalGroupsDefaultCount])
                    <input type="hidden" id="is_condition_hospital" name="is_condition_hospital" data-search-item="is_condition_hospital" value="{{ $is_condition_hospital }}">
                    <div id="collapseHospital" class="collapse @if ($is_condition_hospital) show @endif">
                        @include('components.search_hospital_group', ['hospitalGroups' => $hospitalGroups, 'isDefault' => false, 'hospitalGroupsDefaultCount' => $hospitalGroupsDefaultCount])
                    </div>
                </div>
            </div>


<?php
/*
 * =====================================================================================================================
 *
 * 「検索条件を閉じる」で開閉される領域
 */
?>
            <div
                class="collapse @if ($is_condition_search) show @endif"
                id="collapseSearch"
            >

    <?php
    /*
     * =================================================================================================================
     * 検索条件「状態」チェックボックス
     */
    ?>
                <div class="input-item flex-row-left-top" id="search-status-detail">
                    <p>詳細なｽﾃｰﾀｽ</p>
                    <div class="check-box-container flex-row-left-center flex-wrap">
    @foreach($viewdata->get('tasks') as $key => $value)
                        <label class="generic normal">
                            <input
                                class="custom-control-input validate input-checkbox"
                                id="status_{{ $key }}"
                                name="status[]"
                                data-search-item="status[]"
                                data-search-type="checkbox"
                                value="{{ $key }}"
                                type="checkbox"
        @if(in_array( $key, $viewdata->get('conditions')['status']))
                                checked
        @endif
                            >
                            <span class="deco"></span>
                            <span class="text" title="{{ $value }}">{{ $value }}</span>
                        </label>
    @endforeach
                    </div>
                </div>
    <?php
    /*
     * =================================================================================================================
     * 検索条件「一般名」～
     */
    ?>
                <table class="search-collapse">
                    <tr>
                        <td colspan="2">
        <?php
        /*
         * =============================================================================================================
         * 検索条件「一般名」
         */
        ?>
                            <label class="generic normal search-popular_name">
                                <span class="text text-bold">一般名</span>
                                <input
                                    class="form-control"
                                    type="search"
                                    data-search-item="popular_name"
                                    value="{{$viewdata->get('conditions')['popular_name']}}"
                                    placeholder=""
                                >
                            </label>
                        </td>
                        <td>
        <?php
        /*
         * =============================================================================================================
         * 検索条件「剤型」
         */
        ?>
                            <label class="generic normal search-dosage_type_division">
                                <span class="text text-bold">使用区分</span>
                                <select
                                    class="form-control"
                                    type="text"
                                    data-search-item="dosage_type_division"
                                >
                                    <option value="">全て</option>
    @foreach(App\Helpers\Apply\getDosage_type_division() as $key => $value)
                                    <option
                                        value="{{$key}}"
        @if($key == $viewdata->get('conditions')['dosage_type_division'])
                                        selected
        @endif
                                    >{{ $value }}</option>
    @endforeach
                                </select>
                            </label>
                        </td>
                        <td>
        <?php
        /*
        * =============================================================================================================
        * 検索条件「優先度」
        */
        ?>
                            <label class="generic normal search-coefficient">
                                <span title="詳細画面で設定する項目です。初期値は1です。" class="text text-bold">優先度</span>
                                <input
                                    class="form-control"
                                    type="search"
                                    data-search-item="priority"
                                    value="{{ $viewdata->get('conditions')['priority'] }}"
                                    placeholder=""
                                >
                            </label>
                        </td>                        
                        <td>
        <?php
        /*
            * =============================================================================================================
            * 検索条件「申請日」
            */
        ?>
                            <label class="generic normal search-pa-application-date-from">
                                <span title="指定した日付の申請を抽出します" class="text text-bold">申請日</span>
                                <input
                                    class="form-control"
                                    id="pa_application_date_from"
                                    name="pa_application_date_from"
                                    data-search-item="pa_application_date_from"
                                    value="{{ $viewdata->get('conditions')['pa_application_date_from'] }}"
                                    type="search"
                                    data-datepicker=true
                                >
                                ～
                                <input
                                    class="form-control"
                                    id="pa_application_date_to"
                                    name="pa_application_date_to"
                                    data-search-item="pa_application_date_to"
                                    value="{{ $viewdata->get('conditions')['pa_application_date_to'] }}"
                                    type="search"
                                    data-datepicker=true
                                >
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <td>
        <?php
        /*
         * =============================================================================================================
         * 検索条件「医薬品CD」
         */
        ?>
                            <label class="generic normal search-code">
                                <span title="薬価基準収載医薬品コード" class="text text-bold">医薬品CD</span>
                                <input
                                    class="form-control"
                                    type="search"
                                    value="{{ $viewdata->get('conditions')['code'] }}"
                                    data-search-item="code"
                                    placeholder=""
                                >
                            </label>
                        </td>
                        <td>
        <?php
        /*
         * =============================================================================================================
         * 検索条件「包装薬価係数」
         */
        ?>
                            <label class="generic normal search-coefficient">
                                <span class="text text-bold">包装薬価係数</span>
                                <input
                                    class="form-control"
                                    type="search"
                                    data-search-item="coefficient"
                                    value="{{ $viewdata->get('conditions')['coefficient'] }}"
                                    placeholder=""
                                >
                            </label>
                        </td>
                        <td>
        <?php
        /*
         * =============================================================================================================
         * 検索条件「先後発区分」
         */
        ?>
                            <label class="generic normal search-generic_product_detail_devision">
                                <span title="厚労省が公表している後発品の有無に関する情報です" class="text text-bold">先後発区分</span>
                                <select class="form-control" type="text" data-search-item="generic_product_detail_devision">
                                    <option value="">全て</option>
    @foreach(App\Helpers\Apply\getGeneric_product_detail_devision() as $key => $value)
                                    <option
                                        value="{{$key}}"
        @if( !is_null($viewdata->get('conditions')['generic_product_detail_devision']) && $key == $viewdata->get('conditions')['generic_product_detail_devision'])
                                        selected
        @endif
                                    >{{$value}}</option>
    @endforeach
                                </select>
                            </label>
                        </td>
                        <td>
        <?php
        /*
         * =============================================================================================================
         * 検索条件「販売中止品を含まない」→含むに変更
         */
        ?>
                            <label class="generic normal search-discontinuation_date check-box">
                                <input
                                    class="custom-control-input validate input-checkbox"
                                    id="discontinuation_date"
                                    name="discontinuation_date"
                                    data-search-item="discontinuation_date"
                                    data-search-type="checkbox"
                                    value="1"
                                    type="checkbox"
    @if($viewdata->get('conditions')['discontinuation_date'] == "1")
                                    checked
    @endif
                                >
                                <span class="deco"></span>
                                <span class="text">販売中止品を含む</span>
                            </label>
                        </td>
                        </tr>
                </table>

        <?php
        /*
         * =============================================================================================================
         * 検索条件 本部追加項目
         */
        ?>
    @if (!empty($viewdata->get('hq_optional_search')))
                <div class="optional_area flex-row-left-center flex-wrap">
                    <span class="optional-label">本部参照項目</span>
        <?php $selectList=$viewdata->get('hp_search_list'); ?>
        @foreach($viewdata->get('hq_optional_search') as $key => $value)
        <?php
            $tmp_list = array_key_exists ( $key, $selectList ) ? $selectList [$key] : array ();
            $tkey = "hq_" . $key;
            $item_value = $viewdata->get('conditions')[$tkey];
        ?>
                    <label class="generic normal honbu">
                        <span class="text">{{ $value['optional_key_label'] }}</span>
                        <input
                            class="form-control"
                            type="search"
                            data-search-item="{{$tkey}}"
                            list="hp_combolist{{$key}}"
                            value="{{ $item_value }}"
                            placeholder=""
                        >
                        <datalist id="hp_combolist{{$key}}">
            @foreach($tmp_list as $value2)
                            <option value="{{$value2->value}}">{{$value2->value}}</option>
            @endforeach
                        </datalist>
                    </label>
        @endforeach
                </div>
    @endif
        <?php
        /*
         * =============================================================================================================
         * 検索条件 病院追加項目
         */
        ?>
    @if (!empty($viewdata->get('optional_search')))
                <div class="optional_area flex-row-left-center flex-wrap">
                    <span class="optional-label">施設設定項目</span>
        <?php $selectList=$viewdata->get('search_list'); ?>
        @foreach($viewdata->get('optional_search') as $key => $value)
        <?php
            $tmp_list = array_key_exists ( $key, $selectList ) ? $selectList [$key] : array ();
            $tkey = "'" . $key . "'";
            $item_value=$viewdata->get('conditions')[$key];
        ?>
                    <label class="generic normal byoin">
                        <span class="text">{{ $value['optional_key_label'] }}</span>
                        <input
                            class="form-control"
                            type="search"
                            data-search-item="{{ $key }}"
                            list="combolist{{ $key }}"
                            value="{{ $item_value }}"
                            placeholder=""
                        >
                        <datalist id="combolist{{ $key }}">
            @foreach($tmp_list as $value2)
                            <option value="{{$value2->value}}">{{$value2->value}}</option>
            @endforeach
                        </datalist>
                    </label>
        @endforeach
                </div>
    @endif
            </div>

        <!-- </form> -->
<?php
/*
 * =====================================================================================================================
 * コンテンツ部
 */
?>
        <div class="mt10 pt6 block block-table-control">
            <div class="row">
                <div class="col-12 d-flex justify-content-between align-items-center">
    <?php
    /*
     * =================================================================================================================
     * 表示件数表示「n 件 / 全n件」
     */
    ?>
                   <p class="fs16 text">
    @if ($viewdata->get('page_count') > $viewdata->get('list')->total())
                        {{ $viewdata->get('list')->total() }}
    @elseif($viewdata->get('pager')->current == $viewdata->get('pager')->last)
                        {{$viewdata->get('page_count') * ($viewdata->get('pager')->current-1) + 1}}～{{$viewdata->get('list')->total()}}
    @elseif($viewdata->get('pager')->current < $viewdata->get('pager')->last && $viewdata->get('pager')->current > 0)
                        {{$viewdata->get('page_count') * ($viewdata->get('pager')->current-1) + 1}}～{{$viewdata->get('page_count') * $viewdata->get('pager')->current}}
    @else
                        0
    @endif
                        件 / 全{{ $viewdata->get('list')->total() }}件
                    </p>
    <?php
    /*
     * =================================================================================================================
     * 列省略情報
     */
    ?>
                    <!-- <div class="mode-radio-container">
                        <span>モード</span>
                        @include('apply.components.index_mode_radio', ['label' => '施設用', 'value' => '1', 'is_checked' => true, 'hide' => 'coefficient, medicine-price, purchase-requested-price, purchase-requested-nebiki-rate, purchase-estimated-nebiki-rate, purchase-estimated-price'])
                        @include('apply.components.index_mode_radio', ['label' => '業者用', 'value' => '2', 'is_checked' => false, 'hide' => 'coefficient, medicine-price, sales-price ,sales-rate'])
                        @include('apply.components.index_mode_radio', ['label' => '単価のみ', 'value' => '3', 'is_checked' => false, 'hide' => 'sales-info, coefficient, medicine-price, purchase-requested-nebiki-rate, purchase-estimated-nebiki-rate,purchase-nebiki-rate,sales-rate'])
                    </div> -->

    <?php
    /*
     * =================================================================================================================
     * 表示件数選択
     */
    ?>
                    <div class="d-flex align-items-center block">
                        <div class="btn-group btn-group-toggle" data-toggle="buttons">
                            @include('apply.components.index_search_count', ['count' => '20', 'viewdata' => $viewdata])
                            @include('apply.components.index_search_count', ['count' => '50', 'viewdata' => $viewdata])
                            @include('apply.components.index_search_count', ['count' => '100', 'viewdata' => $viewdata])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    
        <div class="row mt10">
            <div class="col-12 pd-0">
            
    <?php
    /*
     * =================================================================================================================
     * 検索結果のテーブル
     */
    ?>
                <table class="table table-primary table-small" id="applies">
                    <thead>
                        @include('apply.components.index_table_header', ['purchase_waku_kengen' => $purchase_waku_kengen, 'sales_waku_kengen' => $sales_waku_kengen])
                    </thead>
                    <tbody>
    @foreach ($viewdata->get('list') as $apply)
                        <tr
                            data-m-id="{{ empty($apply->medicine_id) ? 0 : $apply->medicine_id }}"
                            data-fm-id="{{ empty($apply->fm_id) ? 0 : $apply->fm_id }}"
                            data-fp-id="{{ empty($apply->fp_id) ? 0 : $apply->fp_id }}"
                            data-pa-id="{{ empty($apply->pa_id) ? 0 : $apply->pa_id }}"
                            data-fp-honbu-id="{{ empty($apply->fp_honbu_id) ? 0 : $apply->fp_honbu_id }}"
                            data-pa-updated-at="{{ $apply->pa_updated_at }}"

                            class="@if ( $apply->adoption_stop_date_fg ) adoption-stop-apply @endif @if ($apply->flg_in_charge_of_adoption ) target-apply @endif"
                        >
                            <!-- No -->
                            <td class="no text-center @if ($viewdata->get('list')->total() < 100) col-w-2 @elseif ($viewdata->get('list')->total() < 1000) col-w-3 @elseif ($viewdata->get('list')->total() < 10000) col-w-4 @elseif ($viewdata->get('list')->total() < 100000) col-w-5 @endif">
                                <span class="clip-bord-target">{{ $loop->iteration + ( $viewdata->get('page_count') * ($viewdata->get('pager')->current -1) ) }}</span>
                            </td>
                            <!-- GS1販売/販売メーカー -->
                            <td class="sales-info">
                                <div>
                                    <span class="row-box justify-content-between">
                                        <span class="clip-bord-target">{{ $apply->sales_packaging_code }}</span>
                                        <span class="product-detail-devision clip-bord-target">@if (!empty(App\Helpers\Apply\getGenericProductDetailDivision($apply->generic_product_detail_devision))){{ App\Helpers\Apply\getGenericProductDetailDivision($apply->generic_product_detail_devision) }}@endif</span>
                                    </span>
                                    <span class="clip-bord-target">{{ $apply->hanbai_maker }}</span>
                                </div>
                            </td>
                            <!-- 商品名/規格容量 -->
                            <td
                                class="medicine-info medicine"
                                data-non-pagenation="1"
                                data-price_adoption_id="{{ empty($apply->pa_id) ? 0 : $apply->pa_id }}"
                                data-sales_packaging_code="{{ $apply->sales_packaging_code }}"
                                data-medicine_id="{{ empty($apply->medicine_id) ? 0 : $apply->medicine_id }}"
                                data-medicine_name="{{ $apply->medicine_name }}"
                                data-popular_name="{{ $apply->popular_name }}"
                                data-code="{{ $apply->code }}"
                                data-price="{{ ($apply->medicine_price * $apply->coefficient) > 0 ? number_format(($apply->medicine_price * $apply->coefficient), 2) : '' }}"
                                data-sales_price="{{ $apply->sales_price > 0 ? number_format($apply->sales_price, 2) : '' }}"
                                data-page="{{ $viewdata->get('pager')->current }}"
                                data-fp_id="{{ $apply->fp_id }}"
                                data-pa_sales_estimate_comment="{{ $apply->pa_sales_estimate_comment }}"
                                data-pa_purchase_estimate_comment="{{ $apply->pa_purchase_estimate_comment }}"
                            >
                                <div>
                                    <span class="medicine-name clip-bord-target">{{ $apply->medicine_name }}
                                        @if ($sales_waku_kengen && !empty($apply->pa_sales_estimate_comment)) <span class="far fa-comment" style="float: right;"></span> @endif
                                    </span>
                                    <span class="standard-unit clip-bord-target">{{ $apply->standard_unit }}
                                        @if ($purchase_waku_kengen && !empty($apply->pa_purchase_estimate_comment)) <span class="fas fa-comment" style="float: right;"></span> @endif
                                    </span>
                                </div>
                                <a class="abs-button btn btn-primary" href="./apply/detail/{{ $apply->medicine_id }}?fp_id=@if(!empty($apply->fp_id)){{ $apply->fp_id }}@else{{0}}@endif&fm_id=@if(!empty($apply->fm_id)){{ $apply->fm_id }}@else{{ 0 }}@endif&pa_id=@if(!empty($apply->pa_id)){{ $apply->pa_id }}@else{{ 0 }}@endif&fp_honbu_id=@if(!empty($apply->fp_honbu_id)){{$apply->fp_honbu_id}}@else{{0}}@endif&page={{ $viewdata->get('pager')->current }}&page_count={{ $viewdata->get('page_count') }}">
                                    <span class="fas fa-pen mr10"></span>詳細・編集
                                </a>
                            </td>
        @if (!$bunkaren_waku_kengen)
                            <!-- 包装薬価係数 -->
                            <td class="coefficient text-right">
                                <span class="clip-bord-target">{{ $apply->coefficient }}</span>
                            </td>
                            <!-- 単位薬価 -->
                            <td class="medicine-price text-right">
                                <span class="clip-bord-target">
            @if (isset($apply->medicine_price))
                                    {{ number_format(round($apply->medicine_price, 2),2) }}
            @endif
                                </span>
                            </td>
        @endif
                            <!-- 包装薬価 -->
                            <td class="pack-unit-price text-right">
                                <span class="clip-bord-target">
            @if (isset($apply->unit_medicine_price))
                                    {{ number_format($apply->unit_medicine_price, 2) }}
            @endif
                                </span>
                            </td>
        @if ($purchase_waku_kengen)
                            <!-- 要望仕入価格 -->
                            <td class="purchase-requested-price text-right">
                                <span class="clip-bord-target">
                                    <!-- XXX -->
                @if ($apply->edit_pa_purchase_requested_price > 0)
                                    <input type="tel" class="dynamic-update-input @if ($apply->flg_in_charge_of_adoption ) target-apply-input @endif" oninput='priceRateDynamicUpdate(event)' onFocus='forcusRowColoring(event)' onblur='blurRowOriginalColoring(event)' value="{{ isset($apply->purchase_requested_price) ? number_format($apply->purchase_requested_price, 0, '', '') : '' }}" />
                @else
                                    {{ isset($apply->purchase_requested_price) ? number_format($apply->purchase_requested_price, 0) :'' }}
                @endif
                                </span>
                            </td>
        @endif
        @if ($purchase_waku_kengen)
                            <!-- 要望仕入値引率 -->
                            <td class="purchase-requested-nebiki-rate text-right">
                                <span class="clip-bord-target">
            @if (isset($apply->purchase_requested_price))
                                    {{ $apply->getSalesRate($apply->purchase_requested_price, $apply->unit_medicine_price) }}
            @endif
                                </span>
                            </td>
        @endif
        @if ($purchase_waku_kengen)
                            <!-- 見積仕入価格 -->
                            <td class="purchase-estimated-price text-right">
                                <span class="clip-bord-target">
                                    <!-- XXX -->
            @if ($apply->edit_pa_purchase_estimated_price > 0)
                                <input type="tel" class="dynamic-update-input @if ($apply->flg_in_charge_of_adoption ) target-apply-input @endif" oninput='priceRateDynamicUpdate(event)' onFocus='forcusRowColoring(event)' onblur='blurRowOriginalColoring(event)' value="{{ isset($apply->purchase_estimated_price) ? number_format($apply->purchase_estimated_price, 0, '', '') : '' }}" />
            @else
                                {{ isset($apply->purchase_estimated_price) ? number_format($apply->purchase_estimated_price, 0) :'' }}
            @endif
                                </span>
                            </td>
        @endif
        @if ($purchase_waku_kengen)
                            <!-- 見積仕入値引率 -->
                            <td class="purchase-estimated-nebiki-rate text-right">
                                <span class="clip-bord-target">
            @if (isset($apply->purchase_estimated_price))
                                    {{ $apply->getSalesRate($apply->purchase_estimated_price, $apply->unit_medicine_price) }}
            @endif
                                </span>
                            </td>
        @endif
        @if ($purchase_waku_kengen)
                            <!-- 仕入価格 -->
                            <td class="purchase-price text-right">
                                <span class="clip-bord-target">
                                    <!-- XXX -->
            @if ($apply->edit_pa_purchase_price > 0)
                                <input type="tel" class="dynamic-update-input @if ($apply->flg_in_charge_of_adoption ) target-apply-input @endif" oninput='priceRateDynamicUpdate(event)' onFocus='forcusRowColoring(event)' onblur='blurRowOriginalColoring(event)' value="{{ isset($apply->purchase_price) ? number_format($apply->purchase_price, 0, '', '') : '' }}" />
            @else
                                {{ isset($apply->purchase_price) ? number_format($apply->purchase_price, 0) :'' }}
            @endif
                                </span>
                            </td>
        @endif
        @if ($purchase_waku_kengen)
                            <!-- 仕入値引率 -->
                            <td class="purchase-nebiki-rate text-right">
                                <span class="clip-bord-target">
            @if (isset($apply->purchase_price))
                                    {{ $apply->getSalesRate($apply->purchase_price, $apply->unit_medicine_price) }}
            @endif
                                </span>
                            </td>
        @endif
        @if ($sales_waku_kengen)
                            <!-- 納入価格 -->
                            <td class="sales-price text-right">
                                <span class="clip-bord-target">
                                    <!-- XXX -->
            @if ($apply->edit_pa_sales_price > 0)
                                <input type="tel" class="dynamic-update-input @if ($apply->flg_in_charge_of_adoption ) target-apply-input @endif" oninput='priceRateDynamicUpdate(event)' onFocus='forcusRowColoring(event)' onblur='blurRowOriginalColoring(event)' value="{{ isset($apply->sales_price) ? number_format($apply->sales_price, 0, '', '') : '' }}" />
            @else
                                {{ isset($apply->sales_price) ? number_format($apply->sales_price, 0) :'' }}
            @endif
                                </span>
                            </td>
        @endif
        @if ($sales_waku_kengen)
                            <!-- 値引率(納入) -->
                            <td class="sales-rate text-right">
                                <span class="clip-bord-target">
            @if (isset($apply->sales_price))
                                    {{ $apply->getSalesRate($apply->sales_price, $apply->unit_medicine_price) }}
            @endif
                                </span>
                            </td>
        @endif
                            <!-- 施設 -->
                            <td class="trader-facillity td-facility text-left">
                                <span class="clip-bord-target">
                                    {{ $apply->facility_name }}
                                </span>
                            </td>
                            <!-- 業者 -->
                            <td class="trader-facillity td-trader text-left">
                                <span class="clip-bord-target">
                                    {{ $apply->trader_name }}
                                </span>
                            </td>
                            <!-- ステータス/申請日 -->
                            <td class="status-date">
                                <div>
                                    <span class="fa fa-spinner fa-pulse ajax-waiting flex-center-self" style="display: none;"></span>
                                    <span
                                        class="badge flex-center-self ajax-update-target-bunkaren clip-bord-target status-label"
                                        style="background-color: {{ $apply->status_current_color_code }}; color: white;"
                                    >{{ $apply->status_current_label }}</span>
        @if (!empty(diffTime($apply->pa_application_date)))
                                    <span class="flex-center-self clip-bord-target" id="sp-pa-application-date">{{ diffTime($apply->pa_application_date) }}</span>
        @endif
                                </div>
                            </td>
                            <!-- タスク -->
                            <td class="apply-task text-center @if ($bunkaren_waku_kengen) bunkaren-apply-task @endif">
                                <span class="flex-column-space-between"><!-- flex-column-left-center-->
                                    <span class="fa fa-spinner fa-pulse ajax-waiting" style="display: none;"></span>

                                    <button
                                        type="button"
                                        data-task-type="{{ $apply->forward_task_button_task_type }}"
                                        data-status-forward="{{ $apply->forward_task_button_status_forward }}"
                                        data-status-reject="{{ null }}"
                                        data-status-backward="{{ null }}"
                                        style="visibility: {{ $apply->forward_task_button_status_forward > 0 ? 'visible' : 'hidden' }}; background-color: {{ $apply->forward_task_button_color_code }} ;color: white; border: none;"
                                        class="ajax-update-target-bunkaren forward task-btn"
                                        onFocus='forcusRowColoring(event)'
                                        onblur='blurRowOriginalColoring(event)'
                                    >{{ $apply->forward_task_button_text }}</button>
        @if ($bunkaren_waku_kengen)
                                    <button
                                        type="button"
                                        data-task-type="{{ $apply->reject_task_button_task_type }}"
                                        data-status-forward="{{ null }}"
                                        data-status-reject="{{ $apply->reject_task_button_status_reject }}"
                                        data-status-backward="{{ null }}"
                                        style="visibility: {{ $apply->reject_task_button_status_reject > 0 ? 'visible' : 'hidden' }}; background-color: {{ $apply->reject_task_button_color_code }};color: white; border: none;"
                                        class="ajax-update-target-bunkaren reject task-btn"
                                        onFocus='forcusRowColoring(event)'
                                        onblur='blurRowOriginalColoring(event)'
                                    >{{ $apply->reject_task_button_text }}</button>
                                    <button
                                        type="button"
                                        data-task-type="{{ $apply->backward_task_button_task_type }}"
                                        data-status-forward="{{ null }}"
                                        data-status-reject="{{ null }}"
                                        data-status-backward="{{ $apply->backward_task_button_status_backward }}"
                                        style="visibility: {{ $apply->backward_task_button_status_backward > 0 ? 'visible' : 'hidden' }}; background-color: {{ $apply->backward_task_button_color_code }};color: white; border: none;"
                                        class="ajax-update-target-bunkaren backward task-btn"
                                        onFocus='forcusRowColoring(event)'
                                        onblur='blurRowOriginalColoring(event)'
                                    >{{ $apply->backward_task_button_text }}</button>
        @endif
                                </span>
                            </td>
                        </tr>
    @endforeach
                    </tbody>

    @if (count($viewdata->get('list')) > 0 )
                    <tfoot class="mirror">
                        @include('apply.components.index_table_header', ['purchase_waku_kengen' => $purchase_waku_kengen, 'sales_waku_kengen' => $sales_waku_kengen])
                    </tfoot>
    @endif
                </table>

                <script type="text/javascript">

                function forcusRowColoring(e) {//検索結果の行のinputやbuttonにfocusを当てたときに行を黄色に、inputを白色にする
                    $forcusElm = $(e.target);
                    $forcusElm.parents("tr").find('td').addClass('apply-tr-forcus');
                    $forcusElm.parents("tr").find('input').addClass('apply-input-forcus');
                }
                function blurRowOriginalColoring(e) {//検索結果の行のinputやbuttonからfocusが外れたときに行とinputの色を元に戻す
                    $forcusElm = $(e.target);
                    $forcusElm.parents("tr").find('td').removeClass('apply-tr-forcus');
                    $forcusElm.parents("tr").find('input').removeClass('apply-input-forcus');
                }             

                function priceRateDynamicUpdate(e) {
                    const $inputElm = $(e.target);
                    const value = $inputElm.val().trim();
                    if (isNaN(value)) {
                        // TODO 数値でない値が入力された場合の処理
                        return
                    }
                    const packUnitPrice = Number($inputElm.parents("tr").find(".pack-unit-price").text().trim().replaceAll(",", ""));
                    // 値引率の列にテキストを反映
                    const ratio = packUnitPrice === 0 || value === "" ? 0 : Math.round((packUnitPrice - value) / packUnitPrice * 10000) / 100;
                    $inputElm.parents("td").next().find(".clip-bord-target").text(ratio === 0 ? "" : ratio > 0 ? `${ratio} %` : `▲${Math.abs(ratio)} %`);
                }
                function ajaxStatusChangeBunkarenSuccess($trElm, result) {
                    console.log("ajaxStatusChangeBunkarenSuccess", result);

                    // 行の背景色を画面に反映
                    $trElm.find('td').css('background-color', result["record_background_color_code"]);

                    // ステータス情報を画面に反映
                    $trElm.find('.status-date .badge')
                        .text(result["status_name"])
                        .css('background-color', result["status_color_code"]);

                    // 施設を画面に反映
                    if (Boolean(result["facility_name"])){
                        $trElm.find('.td-facility').find(".clip-bord-target").text(result["facility_name"]);
                    }
                    // 申請日を画面に反映
                    if (Boolean(result["facility_name"])){//price_adoptionsをinsertしfacility_nameが取得出来れば、申請日を更新する
                        console.log("aiueokakikkaik");
                        console.log($trElm.find('#sp-pa-application-date'));
                        $trElm.find('#sp-pa-application-date').length > 0 ? $trElm.find('#sp-pa-application-date').text("1秒前") : $trElm.find(".status-date div").append($("<span>").addClass("flex-center-self clip-bord-target").text("1秒前"));
                        // $trElm.find(".status-date div").append($("<span>").addClass("flex-center-self clip-bord-target").text("1秒前"));
                    }
                    // 業者を画面に反映
                    if ((result["purchase_user_group_id"] == 0)){
                        if($trElm.find('.td-trader').find(".clip-bord-target").text().trim().length > 0){
                            $trElm.find('.td-trader').find(".clip-bord-target").text("(※自動判定)");
                        }
                    }

                    // Ajaxボタンの制御
                    $trElm.find('.apply-task button.forward')
                        .data('task-type', result["forward_task_button_task_type"])
                        .css('background-color', result["forward_task_button_color_code"])
                        .css('visibility', Boolean(result["forward_task_button_text"]) ? 'visible' : 'hidden');
                    $trElm.find('.apply-task button.reject')
                        .data('task-type', result["reject_task_button_task_type"])
                        .css('background-color', result["reject_task_button_color_code"])
                        .css('visibility', Boolean(result["reject_task_button_text"]) ? 'visible' : 'hidden');
                    $trElm.find('.apply-task button.backward')
                        .data('task-type', result["backward_task_button_task_type"])
                        .css('background-color', result["backward_task_button_color_code"])
                        .css('visibility', Boolean(result["backward_task_button_text"]) ? 'visible' : 'hidden');

                    const m_id = result["m_id"];
                    const fm_id = result["fm_id"];
                    const fp_id = result["fp_id"];
                    const pa_id = result["pa_id"];
                    const fp_honbu_id = result["fp_honbu_id"];
                    console.log('m_id');
                    console.log(m_id);
                    
                    // trタグの属性値の更新
                    $trElm.data("m-id", m_id);
                    $trElm.data("fm-id", fm_id);
                    $trElm.data("fp-id", fp_id);
                    $trElm.data("pa-id", pa_id);
                    $trElm.data("fp-honbu-id", fp_honbu_id);

                    // 詳細画面への遷移URLの更新
                    let absBtnUrl = $trElm.find(".abs-button").attr("href");
                    absBtnUrl = absBtnUrl.replace(/detail\/[0-9]+/i, `detail/${m_id}`);
                    absBtnUrl = absBtnUrl.replace(/fp_id=[0-9]+/i, `fp_id=${fp_id}`);
                    absBtnUrl = absBtnUrl.replace(/fm_id=[0-9]+/i, `fm_id=${fm_id}`);
                    absBtnUrl = absBtnUrl.replace(/pa_id=[0-9]+/i, `pa_id=${pa_id}`);
                    absBtnUrl = absBtnUrl.replace(/fp_honbu_id=[0-9]+/i, `fp_honbu_id=${fp_honbu_id}`);
                    $trElm.find(".abs-button").attr("href", absBtnUrl);

                    // 単価情報を画面に反映
                    const packUnitPrice = Number($trElm.find(".pack-unit-price").text().trim().replaceAll(",", ""));
                    const setTanka = (className, isUseInputParam, valueParam) => {
                        $targetElm = $trElm.find(`.${className} .clip-bord-target`);
                        const isUseInput = result[isUseInputParam];
                        const tankaValue = result[valueParam];
                        console.log('tankaValuetankaValuetankaValuetankaValuetankaValue');
                        console.log(tankaValue);
                        // 単価の反映
                        $targetElm.empty();
                        if (isUseInput > 0) {
                            $("<input type='tel' oninput='priceRateDynamicUpdate(event)'>").addClass("dynamic-update-input").val(tankaValue).appendTo($targetElm);
                        } else {
                            tankaValue === null ? $targetElm.text("") : $targetElm.text(Math.round(tankaValue).toLocaleString());
                        }
                        // 値引率の列にテキストを反映
                        const ratio = packUnitPrice === 0 || tankaValue === "" || tankaValue === null ? 0 : Math.round((packUnitPrice - tankaValue) / packUnitPrice * 10000) / 100;
                        $targetElm.parents("td").next().find(".clip-bord-target").text(ratio === 0 ? "" : ratio > 0 ? `${ratio} %` : `▲${Math.abs(ratio)} %`);
                    };
                    setTanka("purchase-requested-price", "edit_pa_purchase_requested_price", "purchase_requested_price");
                    setTanka("purchase-estimated-price", "edit_pa_purchase_estimated_price", "purchase_estimated_price");
                    setTanka("purchase-price", "edit_pa_purchase_price", "purchase_price");
                    setTanka("sales-price", "edit_pa_sales_price", "sales_price");
                }
                function ajaxStatusChangeBunkarenFail($trElm, error) {
                    console.log("$$$$$ajaxStatusChangeBunkarenFail");
                    
                    // 例外情報を行に表示する
                    var errorMessage = error.responseJSON.errorMessage;
                    console.log(errorMessage);
                    $trElm.find(".medicine-info div").append($("<span>").addClass("error-message").text(errorMessage));
                }
                $(() => {
                    $(".apply-task button").click(function() {
                        const $btnElm = $(this);
                        const $trElm = $btnElm.parents("tr");
                        const $ajaxWaitingElms = $trElm.find(".ajax-waiting");
                        const $ajaxViewWaitingElms = $trElm.find(".ajax-update-target-bunkaren");
                        const getTanka = (className) => {
                            $tdElm = $trElm.find(`.${className}`);
                            $inputElm = $tdElm.find("input.dynamic-update-input");
                            let tankaStr = $inputElm.length ? $inputElm.val() : $tdElm.text();
                            tankaStr = tankaStr.trim().replaceAll(",", "");
                            return tankaStr.length > 0 ? Number(tankaStr) : null;
                        };
                        $ajaxWaitingElms.show();
                        $ajaxViewWaitingElms.hide();

                        console.log("##############################");
                        console.log(getTanka("purchase-requested-price"));

                        const ajaxRequestData = {
                            m_id: $trElm.data("m-id"),
                            pa_id: $trElm.data("pa-id"),
                            fm_id: $trElm.data("fm-id"),
                            fp_id: $trElm.data("fp-id"),
                            fp_honbu_id: $trElm.data("fp-honbu-id"),
                            task_type: $btnElm.data("task-type"),
                            status_forward: $btnElm.data("status-forward"),
                            status_reject: $btnElm.data("status-reject"),
                            status_backward: $btnElm.data("status-backward"),
                            pa_updated_at: $trElm.data("pa-updated-at"),
                            purchase_requested_price: getTanka("purchase-requested-price"),
                            purchase_estimated_price: getTanka("purchase-estimated-price"),
                            purchase_price: getTanka("purchase-price"),
                            sales_price: getTanka("sales-price"),


                        };

                        $.ajax({
                            type: "post",
                            url: "{{ route('apply.ajaxStatusChangeBunkaren') }}",
                            data: ajaxRequestData,
                        })
                        //通信が成功したとき
                        .then(res => {
                            $ajaxWaitingElms.hide();
                            $ajaxViewWaitingElms.show();
                            ajaxStatusChangeBunkarenSuccess($trElm, res);
                        })
                        //通信が失敗したとき
                        .fail(error => {
                            $ajaxWaitingElms.hide();
                            $ajaxViewWaitingElms.show();
                            // 失敗時はタスクボタンとajaxボタンを非表示にする
                            $trElm.find(".apply-task button").hide();
                            ajaxStatusChangeBunkarenFail($trElm, error);
                        });
                    });
                });
                </script>
            </div>
        </div>

    <?php
    /*
     * =====================================================================================================================
     * ページ繰り
     */
    ?>
    @if ($viewdata->get('pager')->max > 1)
        <div class="row mt15">
            <div class="col-12">
                <nav aria-label="page">
                    <ul class="pagination d-fex justify-content-center">
                        <li class="page-item @if ($viewdata->get('pager')->current==1)disabled @endif">
                            <a class="page-link" 
                                data-page="1" 
                                href=""
                                @if ($viewdata->get('pager')->current==1) tabindex="-1" @endif
                            >
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        <li class="page-item @if ($viewdata->get('pager')->current==1)disabled @endif">
                            <a
                                class="page-link"
                                data-page="{{ $viewdata->get('pager')->current - 1}}" 
                                href=""
                                @if ($viewdata->get('pager')->current==1) tabindex="-1" @endif
                            >
                                <span aria-hidden="true">&lt;</span>
                            </a>
                        </li>
        @for ($i = $viewdata->get('pager')->first; $i <= $viewdata->get('pager')->last; $i++)
                        <li class="page-item @if($viewdata->get('pager')->current == $i) active @endif">
                            <a class="page-link" 
                            data-page="{{$i}}" 
                            href=""
                            @if($viewdata->get('pager')->current == $i) tabindex="-1" @endif
                            >
                                {{$i}}
                            </a>
                        </li>
        @endfor
                        <li class="page-item @if ($viewdata->get('pager')->current==$viewdata->get('pager')->last)disabled @endif">
                            <a
                                class="page-link"
                                data-page="{{ $viewdata->get('pager')->current + 1}}" 
                                href=""
                                @if ($viewdata->get('pager')->current==$viewdata->get('pager')->last) tabindex="-1" @endif
                            >
                                <span aria-hidden="true">&gt;</span>
                            </a>
                        </li>
                        <li class="page-item @if ($viewdata->get('pager')->current==$viewdata->get('pager')->last)disabled @endif">
                            <a
                                class="page-link"
                                data-page="{{ $viewdata->get('pager')->last }}"
                                href=""
                                @if ($viewdata->get('pager')->current==$viewdata->get('pager')->last) tabindex="-1" @endif
                            >
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    @endif
        <div class="row mt15 d-print-none">
            <div class="col-12 text-right">
            <!-- SQLダウンロード開発時用  -->
            @if ($admin_kengen)
             <a
                    class="w200 btn btn-primary"
                    href="{{ route('apply.download2') }}?page={{ $viewdata->get('pager')->current }}"
                    download="採用品一覧SQL.txt"
             >
                    <div class="fas fa-pen mr10"></div>SQLダウンロード
            </a>
            @endif
    <?php
    /*
     * =====================================================================================================================
     * 「CSVダウンロード」ボタン
     */
    ?>
    @if (count($viewdata->get('list')) > 0 )
                <a
                    class="w200 btn btn-primary"
                    href="{{ route('apply.download') }}?page={{ $viewdata->get('pager')->current }}"
                    download="採用品一覧_{{ date('Ymd') }}.csv"
                >
                    <div class="fas fa-pen mr10"></div>CSVダウンロード
                </a>
                <a
                    class="btn btn-primary"
                    href="javascript:listToClipBord();"
                    id="copy-list-btn"
                >
                    <div class="fas fa-pen mr10"></div>クリップボードにコピー
                </a>
                
                <div id="copy-dialog" class="flex-column-center-top hide">
                    <span class="title">一覧情報をクリップボードにコピーしました。</span>
                    <textarea type="text" class="message" style="display: none;"></textarea>
                </div>

<script type="text/javascript">
let copyDialogTimeoutId = null;
function listToClipBord() {
    // クリップボードに貼るテキストを作る
    const copyText = $("#applies thead tr, #applies tbody tr").get().map(trElm => $(trElm) // trタグのループ
            .find("th:visible .clip-bord-target, td:visible .clip-bord-target").get().map(targetElm => { // ターゲット要素のループ
                const $inputElm = $(targetElm).find("input");
                const value = $inputElm.length > 0 ? $inputElm.val() : $(targetElm).text(); // テキストを取得して
                return value.trim().replaceAll(/\r?\n/g, "");
            })
            .join("\t")
        )
        .join("\n");
    // クリップボードに貼る
    const $dialog = $("#copy-dialog");
    const hideDialog = () => {
        $dialog.addClass("hide");
        copyDialogTimeoutId = null;
    }
    if (copyDialogTimeoutId !== null) {
        hideDialog();
    }
    const $messageElm = $dialog.removeClass("hide").removeClass("fadeout").addClass("fadeout").find(".message").val(copyText);
    $messageElm.show().select();
    document.execCommand('copy');
    $messageElm.hide();
    $("#copy-list-btn").focus();
    copyDialogTimeoutId = window.setTimeout(hideDialog, 3000);
}
</script>
    @endif
    <?php
    /*
     * =====================================================================================================================
     * 「商品情報を登録して申請する」ボタン
     */
    ?>
    @if ( $new_regist_kengen )
                <a
                    class="w300 btn btn-primary"
                    href="{{ route('apply.add') }}?page={{ $viewdata->get('pager')->current }}"
                >
                    <div class="fas fa-pen mr10"></div>商品情報を登録して申請する
                </a>
    @endif
            </div>
        </div>
    </div>
</div>

<?php
/*
 * =====================================================================================================================
 * ポップアップダイアログ
 */
?>
<div class="popover-custom">
    <div class="popover-dialog">
        <div class="popover-content">
            <div class="popover-header">
                <h5 class="popover-title">施設薬品</h5>
                <button class="close" type="button" data-dismiss="modal"
                    aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="popover-body">
                <div class="form-group idx_1">
                    <label class="form-control-label">GS1販売</label>
                    <div class="medicine_value">GS1販売</div>
                </div>
                <div class="form-group idx_2">
                    <label class="form-control-label">商品名</label>
                    <div class="medicine_value">商品名</div>
                </div>
                {{-- <div class="form-group idx_3">
                    <label class="form-control-label">一般名</label>
                    <div class="medicine_value">一般名</div>
                </div> --}}
                <!-- <div class="form-group idx_4">
                    <label class="form-control-label">包装薬価</label>
                    <div class="medicine_value">包装薬価</div>
                </div>
                <div class="form-group idx_5">
                    <label class="form-control-label">包装納入単価</label>
                    <div class="medicine_value">包装納入単価</div>
                </div> -->
                @if ($sales_waku_kengen)
                <div class="form-group idx_6">
                    <label class="form-control-label">備考(厚生連-文)</label>
                    <div class="medicine_value">備考(厚生連-文)</div>
                </div>
                @endif
                @if ($purchase_waku_kengen)
                <div class="form-group idx_6">
                    <label class="form-control-label">備考(業者-文)</label>
                    <div class="medicine_value">備考(業者-文)</div>
                </div>
                @endif
            </div>
            <!--
            <div class="popover-footer tac">
                <a class="btn btn-primary" href="javascript:void(0);">
                    <div class="fas fa-pen mr10"></div>詳細・編集
                </a>
            </div>
            -->
        </div>
    </div>
</div>

<script type="text/javascript">
$(function radioCheck(){
    //インプット要素を取得する
    var inputs = $("input[type=radio][name='simple_search']"); // TODO 要調整
    //読み込み時に「:checked」の疑似クラスを持っているinputの値を取得する
    var selecedRadioValue = inputs.filter(':checked').val();
    
    //インプット要素がクリックされたら
    inputs.on('click', function(){
        //クリックされたinputが選択済み項目だったかどうかを判定
        if($(this).val() === selecedRadioValue) {
            //チェック状態を外す
            $(this).prop('checked', false);
            //selecedRadioValueを初期化
            selecedRadioValue = null;
        } else {
            //inputの値をselecedRadioValueに代入
            selecedRadioValue = $(this).val();
        }
    });
    
});

</script>

@endif
@endsection

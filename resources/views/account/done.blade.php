@extends('layouts.app')
@php($title = '利用申請')
@php($category = 'account_apply')

@section('content')
{{ $viewdata->get('message') }}
<div class="card card-primary account-apply">
    <style>
        .card-primary{
            width: 680px;
            margin: 40px auto 0;
            border: #bbb solid 1px;
        }
        .form-horizontal{
            padding: 40px 20px 10px;
            width: 100%;
            margin: 0 auto;
        }
    </style>
    <div class="d-flex justify-content-between align-items-center card-header">
    <h3><i class="fas fa-user"></i>利用申請完了</h3>
    </div>
    <div class="pt-0 card-body">
        <div class="row mt20">
            <div class="col-sm-12">
                <div class="block">
                    <p>利用申請が完了しました。担当者からの連絡をお待ちください。</p>
                    <a href="{{ route('dashboard.index') }}">トップに戻る</a>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

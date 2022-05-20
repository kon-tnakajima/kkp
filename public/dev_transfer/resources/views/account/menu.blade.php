@extends('layouts.out')
@php($title = '利用申請メニュー')
@php($category = 'account_apply_menu')

@section('content')

                            <div class="card card-primary account-apply-menu">
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
                                <h3><i class="fas fa-user"></i>利用申請メニュー</h3>
                                </div>
                                <div class="pt-0 card-body">
                                    <div class="form-group row mb-3">
                                        <a class="btn btn-link" href="{{ route('account.request',['condition' => 1]) }}">
                                            既にグループIDがある方で、グループ内のユーザとして参加される方はコチラ
                                        </a>
                                    </div>
                                    <div class="form-group row mb-3">
                                        <a class="btn btn-link" href="{{ route('account.request',['condition' => 2]) }}">
                                            個人で参加される方はコチラ
                                        </a>
                                    </div>
                                    <div class="form-group row mb-3">
                                        <a class="btn btn-link" href="{{ route('account.request',['condition' => 9]) }}">
                                            新たにグループを申請される方はコチラ
                                        </a>
                                    </div>
                                </div>
                            </div>
@endsection

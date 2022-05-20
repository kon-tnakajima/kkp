@extends('layouts.app')

@php($title = 'お知らせ詳細')

@php($category = 'information')

@php($breadcrumbs = ['HOME'=>'/', 'お知らせ詳細'=>'javascript:void(0);'])

@section('content')
@if ($errors->any())
入力にエラーがあります<br>
@foreach ($errors->all() as $message)
{{ $message }}
@endforeach
@endif
{{ $viewdata->get('errorMessage') }}
{{ $viewdata->get('message') }}
<div class="card card-primary {{ $category }}">
    <div class="card-body">
            <div class="form-group validateGroup row">
                <label class="col-sm-2 col-form-label" for="facility_group">タイトル</label>
                <div class="col-sm-10">
                    <p>{{ old('title', $viewdata->get('detail')->title) }}</p>
                </div>
            </div>
            <div class="form-group validateGroup row">
                <label class="col-sm-2 col-form-label" for="facility_group">カテゴリ</label>
                <div class="col-sm-10">
                    @foreach($viewdata->get('categories') as $id => $category)
                        @if( old('category', $viewdata->get('detail')->category) == $id )
                        <span class="badge badge-success">{{ $category }}</span>
                        @endif
                    @endforeach
                </div>
            </div>
            <div class="form-group validateGroup row">
                <label class="col-sm-2 col-form-label" for="valuation_id">本文</label>
                <div class="col-sm-8">
                    <p>{!! nl2br(e($viewdata->get('detail')->contents)) !!}</p>
                </div>
            </div>
    </div>
    <div class="card-footer d-flex justify-content-around pt15 pb15 mt15">
        <a class="btn btn-default btn-lg" href="{{ route('dashboard.index') }}?page={{ $viewdata->get('page') }}"><i class="fas fa-chevron-left mr10"></i>HOMEへ戻る</a>
    </div>
</div>
@endsection

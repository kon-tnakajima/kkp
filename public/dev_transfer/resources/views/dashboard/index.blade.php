@extends('layouts.app')

@php($title = 'home')

@php($category = 'home')

@php($breadcrumbs = ['HOME'=>'javascript:void(0);'])

@section('content')

<style>
.test {
    color: rgb(240, 12, 12) !important;
    font-weight: bold;
}
</style>

<?php
$date = date("Y-m-d H:i:s", strtotime("-3 day"));
?>

<div class="card card-primary {{ $title }}">
    <div class="card-header">
        <h3><i class="fas fa-info-circle"></i>お知らせ ({{ $viewdata->get('infoList')->total() }})</h3>
    </div>
    <div class="pt-0 card-body">
@forelse($viewdata->get('infoList') as $info)
        <ul class="list list-info">
            <li class="list-item">
                <a class="list-item-link list-item-link-caret @if ($info->updated_at > $date) test @endif" href="{{ route('dashboard.detail', ['id' => $info->id, 'page' => $viewdata->get('pager')->current]) }}">
                    <div class="block"><span class="badge badge-success">{{ $info->getCategory()}}</span><span class="info-date">{{ $info->updated_at }}</span></div>
                    <div class="block">{{ $info->title }}</div>
                </a>
            </li>
        </ul>
@empty
<div class="block mt20">お知らせはありません。</div>
@endforelse


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
</div>
@endsection

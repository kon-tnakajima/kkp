@php($title = 'エラー')
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8"><meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="robots" content="noｰindex, no-follow">
    <meta name="description" content="#{description}">
    <meta name="keywords" content="#{keyword}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="/favicon.ico">
    <title>{{$title}}  | {{ config('app.name', 'Laravel') }}</title>
    <link type="text/css" rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
    <link type="text/css" rel="stylesheet" href="{{ asset('css/style.min.css') }}">
</head>
<body class="app header-fixed sidebar-fixed aside-menu-hidden">
    @include('layouts.header')
    @yield('header')
    <!-- body-->
    <div class="app-body">
        <!-- Main Contents-->
        <main class="main">
            <!-- Breadcrumb-->
            <!-- Contents-->
            <div class="container-fluid">
                <div class="animated fadein">
                    <div class="row justify-content-center apply">
                        <div class="col-sm-12">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">

                <div class="card-body">
                エラー番号：404<br><br>
                エラーが発生しています。<br>
                再度、ホームメニューから操作をして下さい。<br>
                エラーが繰り返される場合、下記にお問い合わせ下さい。<br><br>
クオンシステム<br>
TEL:0120-410-621<br>
「厚生連共同購入ポータルについて」とお申し付けください。<br>
受付時間：9:00-18:00　月～金<br>

	            </div>
            </div>
        </div>
    </div>
</div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    @include('layouts.footer')
    @yield('footer')

</body>
</html>

@if (!isset($title))
    @php($title = 'ログイン')
@endif
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
<body class="app header-fixed sidebar-fixed sidebar-hidden">
    <!-- body-->
    <div class="app-body">
        <!-- Main Contents-->
        <main class="main">
            <!-- Contents-->
            <div class="container-fluid">
                <div class="animated fadein">
                    <div class="row justify-content-center">
                        <div class="col-sm-12">
                            @yield('content')
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

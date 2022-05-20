@section('header')
    <!-- header-->
    <header class="app-header navbar d-print-none" style="background-color:#F2CAAA;">
        <a class="navbar-brand" href="{{ url('/') }}"></a>
        <span class="navbar-brand-name hidden-xs" style="background-color:#F2CAAA;">
            <span>【staging】厚生連共同</span>
            <span>購入ポータル</span>
        </span>
        <button class="navbar-toggler sidebar-toggler" type="button" tabindex="-1">
            @guest
            @else
            <span class="navbar-toggler-icon"></span>
            @endguest
        </button>
        <ul class="nav navbar-nav ml-auto">
            <!-- Authentication Links -->
            @guest
            @else
                <li class="nav-item login-user">
                    <div>
                        <i class="fas fa-user mr-2"></i>
                            <div class="name">
                            <p title="ログインユーザー名" class="section">{{ Auth::user()->userGroup()->name }}</p>
                            <p title="ログインユーザーのメイン所属グループ" class="login-user-name">{{ Auth::user()->name }}</p>
                        </div>
                    </div>
                    <!--
                    <p class="last-login-date">{{ Auth::user()->last_login_at }}</p>
                    -->
                </li>
                <li class="nav-item">
                    <a class="nav-link mr-4 sign-out" href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </li>
            @endguest
        </ul>
    </header>
@endsection
@section('sidebar')
    @guest
    @else
        
<style>
    .sidebar-nav a:focus {
        outline: 2px black solid !important;
        outline-offset: -2px;
    }
</style>
        <!-- sidebar-->
        <div class="sidebar d-print-none">
            <nav class="sidebar-nav">
                <ul class="nav">
                    <li class="nav-item @if ( $category == 'home' ) open @endif">
                        <a class="nav-link" href="{{ route('dashboard.index') }}">
                            <i class="fas fa-tachometer-alt"></i>HOME
                        </a>
                    </li>
                    <li class="nav-item nav-dropdown open">
                        <a class="nav-link nav-dropdown-toggle" href="javascript:void(0);">
                            <i class="fas fa-edit"></i>医薬品
                        </a>
                        <ul class="nav-dropdown-items">
                            <li class="nav-item active">
                                <a class="nav-link pl-4" href="{{ route('apply.index', ['initial' => 1]) }}">
                                    <i class="fas fa-edit"></i>採用管理
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item nav-dropdown open">
                        <a class="nav-link nav-dropdown-toggle" href="javascript:void(0);">
                            <i class="fas fa-yen-sign"></i>請求管理
                        </a>
                        <ul class="nav-dropdown-items">
                            <li class="nav-item">
                                <a class="nav-link pl-4" href="{{ route('claim.regist') }}">
                                    <i class="fas fa-yen-sign"></i>請求登録
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link pl-4" href="{{ route('claim.regist_list', ['reginitial' => 1]) }}">
                                    <i class="fas fa-yen-sign"></i>請求登録履歴
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link pl-4" href="{{ route('claim.index', ['initial' => 1]) }}">
                                    <i class="fas fa-yen-sign"></i>請求照会
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link pl-4" href="{{ route('claim.search') }}">
                                    <i class="fas fa-yen-sign"></i>請求データ出力
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li style="display:none;" class="nav-item nav-dropdown @if ( $category == 'information' ) open @endif">
                        <a class="nav-link nav-dropdown-toggle" href="javascript:void(0);">
                            <i class="fas fa-exclamation-circle"></i>お知らせ管理
                        </a>
                        <ul class="nav-dropdown-items">
                            <li class="nav-item">
                                <a class="nav-link pl-4" href="{{ route('info.index') }}">
                                    <i class="fas fa-exclamation-circle"></i>お知らせ一覧
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link pl-4" href="{{ route('info.add') }}">
                                    <i class="fas fa-exclamation-circle"></i>お知らせ登録
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li style="display:none;" class="nav-item nav-dropdown @if ( $category == 'user' ) open @endif">
                        <a class="nav-link nav-dropdown-toggle" href="javascript:void(0);">
                            <i class="fas fa-user"></i>ユーザー管理
                        </a>
                        <ul class="nav-dropdown-items">
                            <li class="nav-item">
                                <a class="nav-link pl-4" href="{{ route('user.index') }}">
                                    <i class="fas fa-user"></i>ユーザー一覧
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link pl-4" href="{{-- route('user.add') --}}">
                                    <i class="fas fa-user"></i>ユーザー登録
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link pl-4" href="{{ route('usergroup.index') }}">
                                    <i class="fas fa-users"></i>ユーザーグループ一覧
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link pl-4" href="{{ route('usergroup.add') }}">
                                    <i class="fas fa-users"></i>ユーザーグループ登録
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li style="display:none;" class="nav-item nav-dropdown @if ( $category == 'system' ) open @endif">
                        <a class="nav-link nav-dropdown-toggle" href="javascript:void(0);">
                            <i class="fas fa-building"></i>システム管理
                        </a>
                        <ul class="nav-dropdown-items">
                            <li class="nav-item">
                                <a class="nav-link pl-4" href="{{ route('account.group.index') }}">
                                    <i class="fas fa-user"></i>利用申請一覧
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link pl-4" href="{{ route('agreement.index') }}">
                                    <i class="fas fa-building"></i>規約一覧
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li style="display:none;" class="nav-item nav-dropdown @if ( $category == 'user' ) open @endif">
                        <a class="nav-link nav-dropdown-toggle" href="javascript:void(0);">
                            <i class="fas fa-user"></i>ユーザ管理
                        </a>
                        <ul class="nav-dropdown-items">
                            <li class="nav-item">
                                <a class="nav-link pl-4" href="{{ route('user.index') }}">
                                    <i class="fas fa-user"></i>ユーザー一覧
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link pl-4" href="{{ route('user.detail', ['id' => Auth::user()->id]) }}">
                                    <i class="fas fa-user"></i>ユーザー管理
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li style="display:none;" class="nav-item nav-dropdown @if ( $category == 'group' ) open @endif">
                        <a class="nav-link nav-dropdown-toggle" href="javascript:void(0);">
                            <i class="fas fa-user"></i>ユーザグループ管理
                        </a>
                        <ul class="nav-dropdown-items">
                            <li class="nav-item">
                                <a class="nav-link pl-4" href="{{ route('account.use.index') }}">
                                    <i class="fas fa-user"></i>所属申請一覧
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link pl-4" href="{{ route('usergroup.index') }}">
                                    <i class="fas fa-user"></i>ユーザグループ一覧
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li style="display:none;" class="nav-item nav-dropdown @if ( $category == 'role' ) open @endif">
                        <a class="nav-link nav-dropdown-toggle" href="javascript:void(0);">
                            <i class="fas fa-list-alt"></i>ロール管理
                        </a>
                        <ul class="nav-dropdown-items">
                            <li class="nav-item">
                                <a class="nav-link pl-4" href="{{ route('role.index') }}">
                                    <i class="fas fa-list-alt"></i>ロール一覧
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link pl-4" href="{{ route('role.add') }}">
                                    <i class="fas fa-list-alt"></i>ロール登録
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link pl-4" href="{{ route('privilege.index') }}">
                                    <i class="fas fa-list-alt"></i>権限一覧
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link pl-4" href="{{ route('privilege.add') }}">
                                    <i class="fas fa-list-alt"></i>権限登録
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li style="display:none;" class="nav-item nav-dropdown @if ( $category == 'medicine' ) open @endif">
                        <a class="nav-link nav-dropdown-toggle" href="javascript:void(0);">
                            <i class="fas fa-pills"></i>標準薬品管理
                        </a>
                        <ul class="nav-dropdown-items">
                            <li class="nav-item">
                                <a class="nav-link pl-4" href="{{ route('medicine.index') }}">
                                    <i class="fas fa-pills"></i>標準薬品一覧
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link pl-4" href="{{ route('medicine.regist') }}">
                                    <i class="fas fa-pills"></i>標準薬品登録
                                </a>
                            </li>
                        </ul>
                    </li>
                 </ul>
            </nav>
            <button class="sidebar-minimizer" type="button" tabindex="-1"></button>
        </div>
    @endguest
@endsection

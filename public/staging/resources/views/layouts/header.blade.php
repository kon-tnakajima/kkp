@section('header')
    <style>
    #sign-out-icon {
        font-size: 1.6em;
        color: #390;
    }
    .grid-layout {
        display: grid;
        grid-template-columns: auto auto auto auto;
        grid-template-rows: 1fr 1fr;
        margin-right: 1rem;
        column-gap: 0.5rem;
    }
    .user-icon {
        grid-column: 1;
        grid-row: 1/3;
        font-size: 1.5rem;
        justify-self: end;
        text-align: center;
        display: flex;
        align-items: center;
        color: #390;
    }
    .single-group-user .section {
        font-size: .75rem;
        align-self: end;
        grid-column: 2/4;
        grid-row: 1;
    }
    .multi-group-user .section {
        align-self: center;
        grid-column: 2;
        grid-row: 1/3;
    }
    .login-user-name {
        font-weight: 600;
        color: #666;
        font-size: 16px;
        margin-right: 2em;
    }
    .single-group-user .login-user-name {
        grid-column: 2/4;
        grid-row: 2;
    }
    .multi-group-user .login-user-name {
        grid-column: 3;
        grid-row: 1/3;
    }
    #sign-out-icon-link {
        grid-column: 4;
        grid-row: 1/3;
        min-width: 3em;
        background: 0;
        justify-self: center;
        border: 0;
        position: relative;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        box-sizing: content-box;
        text-decoration: none;
    }
    #sign-out-icon-link:hover #sign-out-icon:after {
        color: #333;
    }
    #sign-out-icon {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    #sign-out-icon::after {
        content: 'ログアウト';
        display: inline-block;
        position: absolute;
        left: 0;
        bottom: 0;
        font-size: 10px;
        font-weight: 400;
        white-space: nowrap;
        color: #536c79;
        transform: translate(0, 100%);
    }
    #header-gap {
        margin: auto;
    }
    </style>
    <!-- header-->
    <header class="app-header navbar d-print-none" style="background-color:#F2CAAA;">
        <a class="navbar-brand" href="{{ url('/') }}"></a>
        <span class="navbar-brand-name hidden-xs" style="background-color:#F2CAAA;">
            <span>【staging】厚生連共同</span>
            <span>購入ポータル</span>
        </span>
        @guest
        @else
            <button class="navbar-toggler sidebar-toggler" type="button" tabindex="-1">
                <span class="navbar-toggler-icon"></span>
            </button>
        @endguest
        <span id="header-gap"></span>
        @guest
        @else
<script type="text/javascript">
function onChangeHeaderGroup(elm) {
    var $selectElm = $(elm);
    var $spinerElm = $("#header-ajax-spinner-container");
    var id = $selectElm.val();
    $selectElm.hide();
    $spinerElm.show().find("span.label").text($selectElm.find("option:selected").text());
    $.ajax({
        type: "post",
        url: "{{ route('account.group.updatePrimaryUserGroupId', '') }}/" + id,
        dataType: "json",
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
        },
    })
    //通信が成功したとき
    .then(res => {
        location.reload();
    })
    //通信が失敗したとき
    .fail(error => {
        $selectElm.show();
        $spinerElm.hide();
        alert(error.responseJSON.errorMessage);
    });
}
</script>
<?php
    // $groups = array(
    //     array("id"=>0, "label"=>"default", "selected"=>true),
    //     array("id"=>1, "label"=>Auth::user()->userGroup()->name, "selected"=>false),
    //     array("id"=>2, "label"=>"cccc", "selected"=>false),
    //     array("id"=>3, "label"=>"dddd", "selected"=>false),
    // );
    $groups = Auth::user()->getSelectableUserGroup();
?>
            <div class="grid-layout nav navbar-nav {{ count($groups) > 1 ? 'multi-group-user' : 'single-group-user' }}">
                <i class="user-icon fas fa-user"></i>
    @if (count($groups) > 1)
                <div id="header-ajax-spinner-container" class="section" style="display: none;">
                    <span class="fa fa-spinner fa-pulse"></span>
                    <span class="label"></span>
                </div>
                <select class="section" onchange="onChangeHeaderGroup(this)">
        @foreach ($groups as $group)
                    <option value="{{ $group->user_group_id }}" @if ($group->is_set_as_primary) selected @endif>{{ $group->user_group_name }}</option>
        @endforeach
                </select>
    @else
                <p class="section" title="ログインユーザー名">{{ $groups[0]->user_group_name }}</p>
    @endif
                <p class="login-user-name" title="ログインユーザーのメイン所属グループ">{{ Auth::user()->name }}</p>
                <a id="sign-out-icon-link" title="logout" href="" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                    <i id="sign-out-icon" class="fas fa-sign-out-alt"></i>
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>
        @endguest
    </header>
@endsection
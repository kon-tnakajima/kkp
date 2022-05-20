@section('breadcrumb')
<style>
.breadcrumb {
  display : -webkit-box;
  display : -ms-flexbox;
  display : -webkit-flex;
  display : flex;
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
  background-color: #390;
  color: white;
  padding: 0.3rem 1rem;
}
.breadcrumb a {
  color: white;
}
.breadcrumb-item {
  color: white;
  display : -webkit-box;
  display : -ms-flexbox;
  display : -webkit-flex;
  display : flex;
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
}
.breadcrumb-item:before {
  color: white !important;
  font-size: 1rem;
}
.breadcrumb-item.active {
  color: white;
  font-size: 120%;
  /* font-family: 'Ricty Diminished', 'Monaco', 'Consolas', 'Courier New', Courier, monospace, sans-serif; */
}
</style>
    @guest
    @else
        <!-- Breadcrumb-->
        <ol class="breadcrumb">
        @foreach($breadcrumbs as $key=>$val)
            @if ($val === end($breadcrumbs))
                    <li class="breadcrumb-item active">
                @if (count($breadcrumbs) > 1)
                    <?php $level1title = key(array_slice($breadcrumbs, 1, 1, true)) ?>
                    <?php // 前方一致でアイコンを表示する ?>
                    @if (0 === strpos($level1title, "採用"))
                        <i class="fas fa-edit mr8"></i>
                    @elseif (0 === strpos($level1title, "標準薬品"))
                        <i class="fas fa-edit mr8"></i>
                    @elseif (0 === strpos($level1title, "施設"))
                        <i class="fas fa-edit mr8"></i>
                    @elseif (0 === strpos($level1title, "請求"))
                        <i class="fas fa-yen-sign mr8"></i>
                    @elseif (0 === strpos($level1title, "お知らせ"))
                        <i class="fas fa-exclamation-circle mr8"></i>
                    @elseif (0 === strpos($level1title, "ユーザ"))
                        <i class="fas fa-user mr8"></i>
                    @elseif (0 === strpos($level1title, "利用申請"))
                        <i class="fas fa-user mr8"></i>
                    @elseif (0 === strpos($level1title, "所属申請"))
                        <i class="fas fa-user mr8"></i>
                    @elseif (0 === strpos($level1title, "規約"))
                        <i class="fas fa-building mr8"></i>
                    @elseif (0 === strpos($level1title, "ロール"))
                        <i class="fas fa-list-alt mr8"></i>
                    @elseif (0 === strpos($level1title, "権限"))
                        <i class="fas fa-list-alt mr8"></i>
                    @endif
                @endif
                        {{$key}}
                    </li>
            @else
                    <li class="breadcrumb-item"><a href="{{url($val)}}">{{$key}}</a></li>
            @endif
        @endforeach
        </ol><!-- Contents-->
    @endguest
@endsection
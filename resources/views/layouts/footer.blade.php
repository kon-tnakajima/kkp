@section('footer')
    <!-- Footer-->
    @guest
    <footer class="login app-footer">
    @else
    <footer class="app-footer d-print-none">
    @endguest
        <span>&copy; 2018 Japan Culture and Welfare Federation of Agricultural Cooperatives. All Rights Reserved.</span>
    </footer>
    <!-- ▼ JS Libraries ▼-->
    <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
    <!-- ▲ JS Libraries ▲-->
    <script src="{{ asset('js/const.js') }}" defer></script>
    @guest
        @if (!empty($category) && $category != 'login')
        <script src="{{ asset('js/application.min.js') }}" defer></script>
        @endif
    @else
    <script src="{{ asset('js/application.min.js')}}?{{ date('YmdHis', filemtime('js/application.min.js')) }}" defer></script>
    <script src="{{ asset('js/user-group.js') }}" defer></script>
    <script src="{{ asset('js/privieges.js') }}" defer></script>
    <script src="{{ asset('js/invoice.js') }}" defer></script>
    @endguest
@endsection
<!doctype html>
<html lang="en">
@include('laravel-query-execute::layouts.header')
<body>
<div class="wrapper d-flex align-items-stretch">
    @include('laravel-query-execute::layouts.sidebar')

    <div id="content" class="p-4 p-md-5 pt-5">
        @yield('content')
    </div>
</div>
@include('laravel-query-execute::layouts.footer')
</body>
</html>

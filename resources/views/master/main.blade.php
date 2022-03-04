<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="/css/app.css">

    @stack('stylesheet')

    @hasSection('title')
        <title>{{config('app.name')}} - @yield('title')</title>
    @else
        <title>{{config('app.name')}}</title>
    @endif

</head>
<body class="pb-4">
@include('master.navbar')
@include('master.search')

@hasSection('container-fluid')
    <div class="container-fluid">
@else
    <div class="container">
@endif
        @include('includes.jswarning')
        <div class="mt-4">
            @yield('content')
        </div>


</div>

    @stack('javascript')

</body>
</html>
<html>
<head>
    <title>
        @hasSection('title')
        @yield('title') &ndash;
        @endif
        {{ config('app.name') }}
    </title>
    <link rel="stylesheet" href="{{ url(mix('css/app.css')) }}">
    <script src="{{ url(mix('js/app.js')) }}"></script>
    <script type="text/javascript">@yield('scripts')</script>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
</head>
<body> {{-- classes potentially purged by purgecss that should still be kept: modal-open --}}
<nav class="navbar navbar-dark bg-dark">
    <a class="navbar-brand nav-item" href="/">{{ config('app.name') }}</a>
    @auth
        <div class="dropdow nav-item" style="color:white;">
            @if (Auth::user()->verified)
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/0/06/Oxygen480-status-security-high.svg/30px-Oxygen480-status-security-high.svg.png">
            @else
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/f/f8/Oxygen480-status-security-low.svg/30px-Oxygen480-status-security-low.svg.png">
            @endif
            <a href="/logout" style="color:white;">{{ Auth::user()->username }}</a>
        </div>
    @endauth
</nav>

<div class="container">
    <br/>
    @yield('content')
</div>
</body>
</html>

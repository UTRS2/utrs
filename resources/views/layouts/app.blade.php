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
<body>
<nav class="navbar navbar-dark navbar-expand-lg bg-dark">
    <a class="navbar-brand" href="/">{{ config('app.name') }}</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto"> <!-- left nav -->
            @if (Auth::check() && Auth::user()->verified)
                <li class="nav-item">
                    <a href="/review" class="nav-link">Appeal list</a>
                </li>
            @endif
        </ul>
        <ul class="navbar-nav"> <!-- right nav -->
            @auth
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        @if (Auth::user()->verified)
                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/0/06/Oxygen480-status-security-high.svg/30px-Oxygen480-status-security-high.svg.png">
                        @else
                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/f/f8/Oxygen480-status-security-low.svg/30px-Oxygen480-status-security-low.svg.png">
                        @endif
                        {{ Auth::user()->username }}
                    </a>

                    <div class="dropdown-menu dropdown-menu-right ml-auto" aria-labelledby="navbarDropdown">
                        @if (Auth::user()->verified)
                            <a class="dropdown-item" href="{{ route('admin.users.view', Auth::user()) }}">My account</a>
                        @endif
                        <a class="dropdown-item" href="/logout">Log out</a>
                    </div>
                </li>
            @endauth
        </ul>
    </div>
</nav>

<div class="container">
    <br/>
    @yield('content')
</div>
</body>
</html>

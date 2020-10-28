<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
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
<body> {{-- classes potentially purged by purgecss that should still be kept: modal-open modal-backdrop fade show --}}
<nav class="navbar navbar-dark navbar-expand-lg bg-dark">
    <a class="navbar-brand" href="/">{{ config('app.name') }}</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto"> <!-- left nav -->
            @auth
                <li class="nav-item">
                    <a href="{{ route('appeal.list') }}" class="nav-link">Appeal list</a>
                </li>
            @endauth
        </ul>
        <ul class="navbar-nav"> <!-- right nav -->
            @auth
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        {{ Auth::user()->username }}
                    </a>

                    <div class="dropdown-menu dropdown-menu-right ml-auto" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="{{ route('admin.users.view', Auth::user()) }}">My account</a>
                        <a class="dropdown-item" href="{{ route('logout') }}">Log out</a>
                    </div>
                </li>
            @endauth
        </ul>
    </div>
</nav>

<div class="container">
    <br/>
    @if(session()->has('message'))
        <div class="alert alert-info">
            {{ session('message') }}
        </div>
    @endif
    @yield('content')
</div>
</body>
</html>

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
    <style>@yield('css')</style>
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
                @canany(['viewAny','stewardClerk'], App\Models\Appeal::class)
                    <li class="nav-item">
                        <a href="{{ route('appeal.list') }}" class="nav-link">{{__('generic.open-appeals')}}</a>
                    </li>
                @endcan
                @canany('viewAny', [App\Models\User::class, App\Models\Ban::class, App\Models\Template::class])
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="adminNavbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            {{__('generic.tool-admin')}}
                        </a>

                        <div class="dropdown-menu dropdown-menu-right ml-auto" aria-labelledby="adminNavbarDropdown">
                            @can('viewAny', App\Models\Ban::class)
                                <a class="dropdown-item" href="{{ route('admin.bans.list') }}">{{__('generic.admin-tools.bans')}}</a>
                            @endcan
                            @can('viewAny', App\Models\Template::class)
                                <a class="dropdown-item" href="{{ route('admin.templates.list') }}">{{__('generic.admin-tools.template')}}</a>
                            @endcan
                            @can('viewAny', App\Models\User::class)
                                <a class="dropdown-item" href="{{ route('admin.users.list') }}">{{__('generic.admin-tools.users')}}</a>
                            @endcan
                        </div>
                    </li>
                @endcanany
                @can('viewAny', App\Models\Appeal::class)
                    <li class="nav-item">
                        <a href="{{ route('stats.overall') }}" class="nav-link">Statistics</a>
                    </li>
                @endcan
                @can('viewAny', \App\Models\Wiki::class)
                    <li class="nav-item">
                        <a href="{{ route('wiki.list') }}" class="nav-link">{{__('generic.support-wiki')}}</a>
                    </li>
                @endcan
            @endauth
        </ul>
        <ul class="navbar-nav"> <!-- right nav -->
            @auth
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userNavbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        {{ Auth::user()->username }}
                    </a>

                    <div class="dropdown-menu dropdown-menu-right ml-auto" aria-labelledby="userNavbarDropdown">
                        <a class="dropdown-item" href="{{ route('admin.users.view', Auth::user()) }}">My account</a>
                        <a class="dropdown-item" href="{{ route('logout') }}">Log out</a>
                    </div>
                </li>
            @else
                <li class="nav-item">
                    <a href="{{ route('login') }}" class="nav-link">
                        {{__('generic.admin-login')}}
                    </a>
                </li>
            @endauth
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="userNavbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{__('generic.language')}}: {{App::getLocale()}}</a>

                <div class="dropdown-menu dropdown-menu-right ml-auto" aria-labelledby="userNavbarDropdown">
                    <a class="dropdown-item" href="/changelang/en">English</a>
                    <a class="dropdown-item" href="/changelang/fr">Français</a>
                    <a class="dropdown-item" href="/changelang/es">Español</a>
                    <a class="dropdown-item" href="/changelang/pt-BR">Português (Brasil)</a>
                    <a class="dropdown-item" href="/changelang/pt-PT">Português</a>
                </div>
            </li>
        </ul>
    </div>
</nav>
@env(['local','dev'])
<div class="alert alert-warning" role="alert">BE AWARE: You are running UTRS in development mode. There are some functionality differences, plus, this will help you know whether you are looking at live or dev ;).</div>
@endenv
<div class="container" style="max-width:100%">
    <br/>
    @if(session()->has('message'))
        <div class="alert alert-info">
            {{ session('message') }}
        </div>
    @endif
    @if(session()->has('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    @yield('content')

    <footer class="mt-4">
        <hr/>
        <p>
            Unblock Ticket Request System{!! Version::getVersion() !!}, <a href="https://github.com/utrs2/utrs/issues">report bugs</a>.
        </p>
    </footer>
</div>
</body>
</html>

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
    <div class="container-fluid">
    <a class="navbar-brand" href="/">{{ config('app.name') }}</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarText">
        <ul class="navbar-nav me-auto"> <!-- left nav -->
            @auth
                @canany(['viewAny','stewardClerk'], App\Models\Appeal::class)
                    <li class="nav-item">
                        <a href="{{ route('appeal.list') }}" class="nav-link">{{__('generic.open-appeals')}}</a>
                    </li>
                @endcan
                @canany('viewAny', [App\Models\User::class, App\Models\Ban::class, App\Models\Template::class])
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="adminNavbarDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            {{__('generic.tool-admin')}}
                        </a>

                        <ul class="dropdown-menu" aria-labelledby="adminNavbarDropdown">
                            @can('viewAny', App\Models\Ban::class)
                                <li><a class="dropdown-item" href="{{ route('admin.bans.list') }}">{{__('generic.admin-tools.bans')}}</a></li>
                            @endcan
                            @can('viewAny', App\Models\Template::class)
                                <li><a class="dropdown-item" href="{{ route('admin.templates.list') }}">{{__('generic.admin-tools.template')}}</a></li>
                            @endcan
                            @can('viewAny', App\Models\User::class)
                                <li><a class="dropdown-item" href="{{ route('admin.users.list') }}">{{__('generic.admin-tools.users')}}</a></li>
                            @endcan
                            @can('private', App\Models\LogEntry::class)
                                <li><a class="dropdown-item" href="{{ route('admin.logs.list') }}">{{__('generic.admin-tools.logs')}}</a></li>
                            @endcan
                            @can('viewAny', App\Models\Ban::class)
                                <li><a class="dropdown-item" href="{{ route('admin.emailban.list') }}">{{__('generic.admin-tools.emailbans')}}</a></li>
                            @endcan
                        </ul>
                    </li>
                @endcanany
                @can('viewAny', App\Models\Appeal::class)
                    <li class="nav-item">
                        <a href="{{ route('stats.overall') }}" class="nav-link">{{__('generic.statistics')}}</a>
                    </li>
                @endcan
                @can('viewAny', \App\Models\Wiki::class)
                    <li class="nav-item">
                        <a href="{{ route('wiki.list') }}" class="nav-link">{{__('generic.support-wiki')}}</a>
                    </li>
                @endcan
            </ul>
            <ul class="navbar-nav ms-auto"> <!-- right nav -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userNavbarDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        {{ Auth::user()->username }}
                    </a>

                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userNavbarDropdown">
                        <li><a class="dropdown-item" href="{{ route('admin.users.view', Auth::user()) }}">{{ __('generic.my-account') }}</a></li>
                        <li><a class="dropdown-item" href="{{ route('logout') }}">{{ __('generic.logout') }}</a></li>
                    </ul>
                </li>
            @else
                <li class="nav-item">
                    <a href="{{ route('login') }}" class="nav-link">
                        {{__('generic.admin-login')}}
                    </a>
                </li>
            @endauth
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userNavbarDropdown" role="button" data-bs-container="body" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{__('generic.language')}}: {{App::getLocale()}}</a>

                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userNavbarDropdown2">
                        <li><a class="dropdown-item" href="/changelang/en">English</a></li>
                        <li><a class="dropdown-item" href="/changelang/fr">Français</a></li>
                        <li><a class="dropdown-item" href="/changelang/es">Español</a></li>
                        <li><a class="dropdown-item" href="/changelang/pt-BR">Português (Brasil)</a></li>
                        <li><a class="dropdown-item" href="/changelang/pt-PT">Português</a></li>
                        @env(['local','dev'])
                            <li><a class="dropdown-item" href="/changelang/qqq">Template Description</a></li>
                            <li><a class="dropdown-item" href="/changelang/qqz">Template Name</a></li>
                        @endenv
                    </ul>
                </li>
            </ul>
        </div>
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
    @if(session()->has('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    @yield('content')

    <footer class="mt-4">
        <hr/>
        <p>
            Unblock Ticket Request System{!! Version::getVersion() !!}, <a href="https://github.com/utrs2/utrs/issues">{{__('generic.reportbugs')}}</a>.
        </p>
    </footer>
</div>
</body>
</html>

<html>
    <head>
        <title>
            @hasSection('title')
            @yield('title') &ndash;
            @endif
            {{ config('app.name') }}
        </title>
        <link rel="stylesheet" href="https://tools-static.wmflabs.org/cdnjs/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
        <script src="https://tools-static.wmflabs.org/cdnjs/ajax/libs/jquery/3.3.1/jquery.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
        <script src="https://tools-static.wmflabs.org/cdnjs/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
        <script src="https://tools-static.wmflabs.org/cdnjs/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
        <script type="text/javascript">@yield('scripts')</script>
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
    </head>
    <body>
        <nav class="navbar navbar-dark bg-dark">
            <a class="navbar-brand nav-item" href="/">{{ config('app.name') }}</a>
            @auth
            <div class="dropdow nav-item" style="color:white;">
                @if (Auth::user()->verified)
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/0/06/Oxygen480-status-security-high.svg/30px-Oxygen480-status-security-high.svg.png">
                @else
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/f/f8/Oxygen480-status-security-low.svg/30px-Oxygen480-status-security-low.svg.png">
                @endif
                <a href="/logout" style="color:white;">{{Auth::user()->username}}</a>
            </div>
            @endauth             
        </nav>

        <div class="container">
            <br />
            @yield('content')
        </div>
    </body>
</html>

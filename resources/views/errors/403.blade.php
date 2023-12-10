@extends('layouts.app')

@section('content')
    @if(Auth::check() && !Auth::user()->last_permission_check_at)
        <div class="alert alert-warning" role="alert">
            <h4 class="alert-heading">403 Forbidden: {{ $exception->getMessage() ?: 'You do not have access to view this page or perform this action.' }}</h4>

            <b>Your user roles may not have not been loaded yet.</b>
            <p>
                This should not take too long.
            </p>

            <p>
                <button onclick="window.location.reload();" class="btn btn-warning">Try again</button>
            </p>

            <p>
                If your role have not loaded after a couple of minutes,
                please <a href="https://en.wikipedia.org/wiki/WT:UTRS" class="alert-link">contact us</a>.
            </p>
        </div>
    @else
        <div class="alert alert-danger" role="alert">
            <h4 class="alert-heading"><b>403 Forbidden:</b> {{ $exception->getMessage() ?: 'You do not have access to view this page or perform this action.' }}</h4>
        </div>
    @endif
@endsection

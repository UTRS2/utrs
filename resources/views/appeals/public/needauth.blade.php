@extends('layouts.app')

@section('title', htmlspecialchars(__('auth.auth-needed-screen.title')))
@section('content')
    <div class="alert alert-info" role="alert">
        <h1>
            {{ __('auth.auth-needed-screen.title') }}
        </h1>

        <p>
            {{ __('auth.auth-needed-screen.text') }}
        </p>
    </div>

    <div class="card-deck">
        <div class="card">
            <div class="card-body">
                {{ __('auth.auth-needed-screen.key-text') }}

                {{ Form::open(['url' => route('public.appeal.view'), 'method' => 'GET']) }}
                <div class="input-group w-100 mb-3">
                    {{ Form::text('hash', null, ['class' => 'form-control w-100','placeholder' => __('auth.auth-needed-screen.key-placeholder')]) }}
                </div>

                <div>
                    <button type="submit" class="btn btn-primary">{{ __('auth.auth-needed-screen.submit-text') }}</button>
                    {{-- <a href="#" class="btn btn-danger">Forgot Appeal Key</a> --}}
                </div>
                {{ Form::close() }}
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <p>
                    {{ __('auth.auth-needed-screen.login-text') }}
                </p>

                <a href="?send_to_oauth=1" class="btn btn-info">
                    {{ __('auth.auth-needed-screen.oauth-button') }}
                </a>
            </div>
        </div>
    </div>
@endsection

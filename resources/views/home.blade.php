@extends('layouts.app')

@section('content')
    <div class="alert alert-danger" role="alert">
        <b>{{ __('home.noemail-header') }}</b><br/>
        {{ __('home.noemail-text') }}
    </div>

    <div class="card-group d-flex justify-content-center">
        <div class="card bg-light mb-3 text-center">
            <div class="card-header">{{ __('home.appeal-block-header') }}</div>
            <div class="card-body">
                <h5 class="card-title">{{ __('home.appeal-block-title') }}</h5>
                <p class="card-text">{{ __('home.appeal-block-text') }}</p>
            </div>
            <div class="card-footer">
                <div class="mb-2">
                    <a href="{{ route('public.appeal.create.account') }}" class="btn btn-primary">{{ __('home.appeal-block-button') }}</a>
                </div>
            </div>
        </div>
        <div class="card bg-light mb-3 text-center">
            <div class="card-header">{{ __('home.appeal-ip-header') }}</div>
            <div class="card-body">
                <h5 class="card-title">{{ __('home.appeal-ip-title') }}</h5>
                <p class="card-text">{{ __('home.appeal-ip-text') }}</p>
            </div>
            <div class="card-footer">
                <div class="mb-2">
                    <a href="{{ route('public.appeal.create.ip') }}" class="btn btn-primary">{{ __('home.appeal-ip-button') }}</a>
                </div>
            </div>
        </div>
        <div class="card bg-light mb-3 text-center">
            <div class="card-header">{{ __('home.admin-header') }}</div>
            <div class="card-body">
                <h5 class="card-title">{{ __('home.admin-title') }}</h5>
                <p class="card-text">{{ __('home.admin-text') }}</p>
            </div>
            <div class="card-footer">
                <div class="mb-2">
                    @auth
                        <a href="{{ route('appeal.list') }}" class="btn btn-primary">{{ __('home.admin-button') }}</a>
                    @endauth
                    @guest
                        <a href="{{ route('login') }}" class="btn btn-primary">{{ __('home.login-button') }}</a>
                    @endguest
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="text-center">
                <h5 class="card-title">If you already have an appeal</h5>
                <p class="card-text">{{ Form::label('hash', 'Please enter your appeal key below') }}</p>
                {{ Form::open(['url' => route('public.appeal.view'), 'method' => 'GET']) }}
                <div class="input-group w-100 mb-3">
                    <div class="input-group-prepend w-100">
                        <span class="input-group-text">#</span>
                        {{ Form::text('hash', null, ['class'=>'form-control w-100','placeholder'=>'Appeal Key']) }}
                    </div>
                </div>

                <div>
                    <button type="submit" class="btn btn-primary">{{ __('generic.submit') }}</button>
                    <a href="#" class="btn btn-danger">Forgot Appeal Key</a>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
@endsection

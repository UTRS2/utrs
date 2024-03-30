@extends('layouts.app')

@section('content')
    <div class="card-group justify-content-center">
        <div class="card bg-light mb-3 text-center">
            <div class="card-header">{{ __('home.appeal-block-header') }}</div>
            <div class="card-body">
                <h5 class="card-title">{{ __('home.appeal-block-title') }}</h5>
                <p class="card-text">{{ __('home.appeal-block-text') }}</p>
            </div>
            <div class="card-footer">
                <div>
                    <a href={{ route('public.appeal.create.account') }} class="btn btn-primary">{{ __('home.appeal-block-button') }}</a>
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
                <div>
                    <a href={{ route('public.appeal.create.ip') }} class="btn btn-primary">{{ __('home.appeal-ip-button') }}</a>
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
                <div>
                    @auth
                        <a href={{ route('appeal.list') }} class="btn btn-primary">{{ __('home.admin-button') }}</a>
                    @endauth
                    @guest
                        <a href={{ route('login') }} class="btn btn-primary">{{ __('home.login-button') }}</a>
                    @endguest
                </div>
            </div>
        </div>
    </div>
    <div class="card-group justify-content-center">
        <div class="card mb-5 text-center">
            {{ html()->form('POST', route('public.appeal.map'))->open() }}
            <div class="card-body">
                <h5 class="card-title">{{__('auth.auth-needed-screen.key-title')}}</h5>
                {{ html()->label(__('auth.auth-needed-screen.key-text'), 'hash') }}
                <p class="card-text">{{ html()->text('appealkey')->class('form-control w-100')->placeholder(__('auth.auth-needed-screen.key-placeholder')) }}</p>
            </div>
            <div class="card-footer">
                <div>
                    <button type="submit" id="view-my-appeal" class="btn btn-primary">{{ __('auth.auth-needed-screen.submit-text') }}</button>
                </div>
            </div>
            {{ html()->form()->close() }}
        </div>
        <div class="card bg-light mb-5 text-center">
            {{ html()->form('POST', route('appealkey.reset'))->open() }}
            <div class="card-body">
                <h5 class="card-title">{{__('auth.email.key-title')}}</h5>
                {{ html()->label(__('auth.email.key-text'), 'hash') }}
                <p class="card-text">{{ html()->text('email')->class('form-control w-100')->placeholder('joe@domain.email') }}</p>
            </div>
            <div class="card-footer">
                <div>
                    <button type="submit" class="btn btn-danger">{{ __('auth.email.submit-text') }}</button>
                </div>
            </div>
            {{ html()->form()->close() }}
        </div>
    </div>
@endsection

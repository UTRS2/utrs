@extends('layouts.app')

@section('title', htmlspecialchars(__('appeals.wrong-key.title')))
@section('content')
    <div class="alert alert-warning" role="alert">
        <h2>
            {{ __('appeals.wrong-key.title') }}
        </h2>

        <p>
            {{ __('appeals.wrong-key.text') }}
        </p>
    </div>

    <div class="card-deck">
        <div class="card">
            <div class="card-body">
                {{ html()->form('POST', route('public.appeal.map'))->open() }}
            <div class="card-body">
                <h5 class="card-title">{{__('auth.auth-needed-screen.key-title')}}</h5>
                {{ html()->label(__('auth.auth-needed-screen.key-text'), 'hash') }}
                <p class="card-text">{{ html()->text('appealkey')->class('form-control w-100')->placeholder(__('auth.auth-needed-screen.key-placeholder')) }}</p>
            </div>
            <div class="card-footer">
                <div>
                    <button type="submit" dusk="view-my-appeal" class="btn btn-primary">{{ __('auth.auth-needed-screen.submit-text') }}</button>
                </div>
            </div>
            {{ html()->form()->close() }}
            </div>
        </div>
    </div>
@endsection

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
                {{ __('auth.auth-needed-screen.key-text') }}

                {{ html()->form('GET', route('public.appeal.view'))->open() }}
                <div class="input-group w-100 mb-3">
                    {{ html()->text('hash')->class('form-control w-100')->placeholder(__('auth.auth-needed-screen.key-placeholder')) }}
                </div>

                <div>
                    <button type="submit" class="btn btn-primary">{{ __('auth.auth-needed-screen.submit-text') }}</button>
                    {{-- <a href="#" class="btn btn-danger">Forgot Appeal Key</a> --}}
                </div>
                {{ html()->form()->close() }}
            </div>
        </div>
    </div>
@endsection

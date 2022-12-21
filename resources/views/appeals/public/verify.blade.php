@extends('layouts.app')

@section('title', htmlspecialchars(__('appeals.forms.header-verify')))
@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                {{ __('appeals.forms.header-verify') }}
            </div>

            <div class="card-body">
                @component('components.errors')
                @endcomponent

                <form action="{{ route('public.appeal.verifyownership.submit', $appeal) }}" method="POST">
                    @csrf

                    <input type="hidden" name="verify_token" value="{{ $appeal->verify_token }}">

                    <div class="mb-4">
                        <label for="secret_key" class="form-label">{{ __('appeals.forms.verify-secret') }}</label>
                        <input type="text" class="form-control" id="secret_key" name="secret_key">
                        <small class="form-text text-muted">
                            {{ __('appeals.forms.verify-secret-help') }}
                        </small>
                    </div>

                    <button type="submit" class="btn btn-primary">{{ __('generic.submit') }}</button>
                </form>
            </div>
        </div>
    </div>
@endsection

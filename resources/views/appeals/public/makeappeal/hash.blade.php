@extends('layouts.app')

@section('title', htmlspecialchars(__('appeals.key.header')))
@section('content')
    <div class="card">
        <h5 class="card-header">{{ __('appeals.key.header') }}</h5>
        <div class="card-body">
            <div class="alert alert-danger" role="alert">
                {{ __('appeals.key.do-not-lose') }}
            </div>
            <br>
            <center>{{ __('appeals.key.your-key-is') }}<br>
                <h2>{{ $hash }}</h2></center>
            <br/>
            <a href="{{ route('public.appeal.view') . '?' . http_build_query([ 'hash' => $hash ]) }}" class="btn btn-success">
                {{ __('appeals.key.view-appeal-details') }}
            </a>
        </div>
    </div>
@endsection

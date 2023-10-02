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
            {{ html()->form('POST', route('public.appeal.view'))->open() }}
            {{ html()->token() }}
            {{ html()->hidden('appealkey', $hash) }}
            {{ html()->submit(__('appeals.key.view-appeal-details'))->class('btn btn-primary') }}
            {{ html()->form()->close() }}
        </div>
    </div>
@endsection

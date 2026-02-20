@extends('layouts.app')

@section('title', 'Access Prohibited - HTTP 403')
@section('content')
    <div class="alert alert-success" role="alert">
        <h2>
            Appeal data changed
        </h2>

        <p>
            Your request to change the data on your appeal has completed.

            {{ html()->form('POST', route('public.appeal.view'))->open() }}
            {{ html()->token() }}
            {{ html()->hidden('appealkey', $appealkey) }}
            {{ html()->submit(__('appeals.key.view-appeal-details'))->class('btn btn-primary') }}
            {{ html()->form()->close() }}
        </p>
    </div>
@endsection
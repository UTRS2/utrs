@extends('layouts.app')
@section('content')

    <div class="col-1"></div>
    <div class="col-10">
        <div class="alert alert-info" role="alert">
            This page allows you to create a custom response to the user.
        </div>

        {{ Form::open(['url' => route('appeal.customresponse.submit', $appeal)]) }}
        {{ Form::token() }}
        <div class="card mb-4">
            <h5 class="card-header">Response</h5>
            <div class="card-body">
                Dear {{ $appeal->appealfor }},
                <div class="form-group">
                    {{ Form::textarea('custom', null, ['class' => 'form-control', 'rows' => 10, 'style' => 'height: 10rem;']) }}
                </div>
                {{ $userlist[Auth::id()] }}<br/>
                English Wikipedia Administrator
            </div>
        </div>

        <div class="card mb-4">
            <h5 class="card-header">Options</h5>
            <div class="card-body">
                <div class="form-group">
                    {{ Form::label('status', 'Change appeal status to:') }}
                    {{ Form::select('status', \App\Appeal::REPLY_STATUS_CHANGE_OPTIONS, old('status', $appeal->status), ['class' => 'form-control']) }}
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-success">Submit</button>
        <a type="button" class="btn btn-danger" href="/appeal/{{ $appeal->id }}">Return to appeal</a>
        {{ Form::close() }}
    </div>
    <div class="col-1"></div>

@endsection

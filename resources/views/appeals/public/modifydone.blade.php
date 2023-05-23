@extends('layouts.app')

@section('title', 'Access Prohibited - HTTP 403')
@section('content')
    <div class="alert alert-success" role="alert">
        <h2>
            Appeal data changed
        </h2>

        <p>
            Your request to change the data on your appeal has completed.

            {{ Form::open(['url' => route('public.appeal.view')]) }}
            {{ Form::token() }}
            {{ Form::hidden('appealkey', $appealkey) }}
            {{ Form::button(__('appeals.key.view-appeal-details'), ['class' => 'btn btn-primary','type'=>'submit']) }}
            {{ Form::close() }}
        </p>
    </div>
@endsection
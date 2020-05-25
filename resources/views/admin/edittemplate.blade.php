@extends('layouts.app')
@section('content')
    @component('components.errors')
    @endcomponent

    <div class="card">
        <div class="card-header">
            Edit template
        </div>
        <div class="card-body">
            {{ Form::open(['url' => route('admin.templates.update', $template)]) }}
            {{ Form::token() }}

            <div class="form-group">
                {{ Form::label('name', 'Template name') }}
                {{ Form::text('name', old('name', $template->name), ['class' => 'form-control']) }}
            </div>

            <div class="form-group">
                {{ Form::label('template', 'What should the template say?') }}
                {{ Form::textarea('template', old('template', $template->template), ['class' => 'form-control h-25', 'rows'=>'5']) }}
            </div>

            <button type="submit" class="btn btn-success">Submit</button>
            {{ Form::close() }}
        </div>
    </div>
@endsection

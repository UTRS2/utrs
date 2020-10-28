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
                {{ Form::textarea('template', old('template', $template->template), ['class' => 'form-control h-25', 'rows'=>'15']) }}
            </div>

            <div class="form-group">
                {{ Form::label("default_status-" . $template->id, 'Default status after replying:') }}
                {{ Form::select('default_status', \App\Models\Appeal::REPLY_STATUS_CHANGE_OPTIONS, old('default_status', $template->default_status), ['class' => 'form-control', 'id' => "default_status-" . $template->id]) }}
            </div>

            <button type="submit" class="btn btn-success">Submit</button>
            {{ Form::close() }}
        </div>
    </div>
@endsection

@extends('layouts.app')
@section('content')
	@component('components.errors')
	@endcomponent

	<div class="card">
		<div class="card-header">
			New template
		</div>
		<div class="card-body">
			{{ Form::open(array('url' => 'admin/templates/create')) }}
			{{ Form::token() }}

			<div class="form-group">
				{{ Form::label('name', 'Template name') }}
				{{ Form::text('name', old('name'), ['class' => 'form-control']) }}
			</div>

			<div class="form-group">
				{{ Form::label('template', 'What should the template say?') }}
				{{ Form::textarea('template', old('template'), ['class' => 'form-control h-25','rows'=>'15']) }}
			</div>

			<div class="form-group">
                {{ Form::label("default_status-", 'Default status after replying:') }}
                {{ Form::select('default_status', \App\Appeal::REPLY_STATUS_CHANGE_OPTIONS, null, ['class' => 'form-control', 'id' => "default_status-"]) }}
            </div>

			<button type="submit" class="btn btn-success">Submit</button>
			{{ Form::close() }}
		</div>
	</div>
@endsection

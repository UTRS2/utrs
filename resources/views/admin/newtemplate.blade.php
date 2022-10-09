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

			<div class="mb-4">
				{{ Form::label('name', 'Template name', ['class' => 'form-label']) }}
				{{ Form::text('name', old('name'), ['class' => 'form-control']) }}
			</div>

			<div class="mb-4">
				{{ Form::label('template', 'What should the template say?', ['class' => 'form-label']) }}
				{{ Form::textarea('template', old('template'), ['class' => 'form-control h-25','rows'=>'15']) }}
			</div>

			<div class="mb-4">
				{{ Form::label("default_status", 'Default status after replying:', ['class' => 'form-label']) }}
				{{ Form::select('default_status', \App\Models\Appeal::REPLY_STATUS_CHANGE_OPTIONS, old('default_status'), ['class' => 'form-control']) }}
			</div>

			@if($wikis->count() > 1)
				<div class="mb-4">
					{{ Form::label('wiki_id', 'Wiki', ['class' => 'form-label']) }}
					{{ Form::select('wiki_id', $wikis, old('wiki_id'), ['class' => 'form-control']) }}
				</div>
			@else
				{{ Form::hidden('wiki_id', $wikis->keys()->first()) }}
			@endif

			<button type="submit" class="btn btn-success">Submit</button>
			{{ Form::close() }}
		</div>
	</div>
@endsection

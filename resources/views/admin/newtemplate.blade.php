@extends('layouts.app')
@section('content')

<div class="col-md-1"></div>
<div class="col-md-10">
	<div class="card">
  		<div class="card-header">
    		New template
  		</div>
  		<div class="card-body">
  			{{ Form::open(array('url' => 'admin/templates/create')) }}
  			{{ Form::token() }}

    		{{ Form::label('name', 'What is the name of the template') }}<br>
    		{{ Form::text('name') }}<br><br>
    		{{ Form::label('template', 'What should the template say?') }}<br>
    		{{ Form::textarea('template') }}<br>
    		
			<button type="submit" class="btn btn-success">Submit</button>
    		{{ Form::close() }}
  		</div>
	</div>
</div>

@endsection
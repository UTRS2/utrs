@extends('layouts.app')
@section('content')

<div class="col-1"></div>
<div class="col-10">
	<div class="alert alert-info" role="alert">
  		This page allows you to create a custom response to the user.
	</div>
	<br />
	Dear {{$appeal['appealfor']}},<br />
	<br />
	{{ Form::open(array('url' => 'appeal/custom/'.$appeal['id'])) }}
    {{Form::token()}}
	{{Form::textarea('custom',null,['rows'=>10])}}<br><br>
	{{$userlist[Auth::id()]}}<br />
	English Wikipedia Administrator<br />
    <button type="submit" class="btn btn-success">Submit</button>
    <button type="button" class="btn btn-danger" onclick="window.history.back();">Return to appeal</button>
    {{ Form::close() }}
</div>
<div class="col-1"></div>

@endsection
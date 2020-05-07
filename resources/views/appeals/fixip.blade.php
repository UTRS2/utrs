@extends('layouts.app')

@section('title', 'Modify appeal')
@section('content')

<div class="col-md-1"></div>
<div class="col-md-10">
	<div class="alert alert-danger" role="alert">
  		You are now modifying your appeal to be resubmitted. Please ensure the information is correct.
	</div>
	@if(sizeof($errors)>0)
  		<div class="alert alert-danger" role="alert">
  			The following errors occured:
  			<ul>
	  		@foreach ($errors->all() as $message)
				<li>{{$message}}</li>
			@endforeach
	@endif
	{{ Form::open(array('url' => 'fixip/'.$appeal->id)) }}
	{{Form::token()}}
	<h5 class="card-title">About you</h5>
	{{Form::label('wiki', 'Which Wiki are you blocked on?')}}<br>
	{{Form::select('wiki', array('enwiki' => 'English Wikipedia','ptwiki' => 'Portuguese Wikipedia', 'global' => 'Global Locks/Blocks'), $appeal->wiki)}}<br>
	{{Form::label('appealfor', 'What is your Username?')}}<br>
	{{Form::text('appealfor',$appeal->appealfor)}}<br>
	<br>
	{{Form::label('blocktype', 'Is your account directly blocked?')}}<br>
	@if($appeal->blocktype===0)
	{{Form::radio('blocktype', 1)}} Yes<br>
	{{Form::radio('blocktype', 0,true)}} No, I don't have an account.<br>
	{{Form::radio('blocktype', 2)}} No, the underlying IP address is blocked<br>
	@elseif($appeal->blocktype===1)
	{{Form::radio('blocktype', 1,true)}} Yes<br>
	{{Form::radio('blocktype', 0)}} No, I don't have an account.<br>
	{{Form::radio('blocktype', 2)}} No, the underlying IP address is blocked<br>
	@elseif($appeal->blocktype===2)
	{{Form::radio('blocktype', 1)}} Yes<br>
	{{Form::radio('blocktype', 0)}} No, I don't have an account.<br>
	{{Form::radio('blocktype', 2,true)}} No, the underlying IP address is blocked<br>
	@endif
	<br>
	{{Form::label('hiddenip', 'If you selected "No, the underlying IP address is blocked" above, what is the IP?')}}<br>
	{{Form::text('hiddenip',$appeal->hiddenip)}}<br>
	{{Form::hidden('hash', $hash)}}
	<br>
	<button type="submit" class="btn btn-success">Submit</button>
	{{ Form::close() }}
</div>

@endsection
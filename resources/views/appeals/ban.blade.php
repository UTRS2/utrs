@extends('layouts.app')
@section('content')

<div class="col-md-1"></div>
<div class="col-md-10">
	<div class="alert alert-danger" role="alert">
  		Your IP address or username has been banned from using the UTRS system.
  		@if($expire!=="0000-00-00 00:00:00")
  			The ban expires on {{$expire}}.
  		@endif
  		If you contact UTRS Admins about this ban, please mention the following ban ID: {{$id}}.
	</div>
  <br><center><img src="https://upload.wikimedia.org/wikipedia/commons/0/01/AnimatedStop2.gif" width="500px"></center>
</div>

@endsection
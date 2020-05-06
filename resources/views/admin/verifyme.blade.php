@extends('layouts.app')

@section('title', 'Account not verified')
@section('content')

<div class="col-md-1"></div>
<div class="col-md-10">
	<div class="alert alert-danger" role="alert">
  		Your account has not been verified at this time. You will not be able to proceed until the system has verified your account. Verifications should take less than 5 minutes to send to your Wikipedia email. If you do not recieve one, make sure you have an email verified your preferences on Wikipedia, and login again.
	</div>
  <br><center><img src="https://upload.wikimedia.org/wikipedia/commons/0/01/AnimatedStop2.gif" width="500px"></center>
</div>

@endsection
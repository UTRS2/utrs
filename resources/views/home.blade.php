@extends('layouts.app')

@section('content')
<center>
<div class="col-md-1"></div>
<div class="col-md-10">
<div class="card-group">
	<div class="card bg-light mb-3 text-center" style="max-width: 18rem;">
	  <div class="card-header">Blocked user</div>
	  <div class="card-body">
	    <h5 class="card-title">If you have a user account</h5>
	    <p class="card-text">on Wikipedia and are blocked, please select the button below
	    to start your appeal.</p>
	    <a href="appeal/account" class="btn btn-primary">Appeal my block</a>
	  </div>
	</div>
	<div class="card bg-light mb-3 text-center" style="max-width: 18rem;">
	  <div class="card-header">Blocked IP</div>
	  <div class="card-body">
	    <h5 class="card-title">If you <b>DO NOT</b> have a user account</h5>
	    <p class="card-text">on Wikipedia and are blocked, please select the button below
	    to start your appeal.</p>
	    <a href="appeal/ip" class="btn btn-primary">Appeal IP block</a>
	  </div>
	</div>
	<div class="card bg-light mb-3 text-center" style="max-width: 18rem;">
	  <div class="card-header">Login</div>
	  <div class="card-body">
	    <h5 class="card-title">For users that are here</h5>
	    <p class="card-text">to comment on, process or assist with appeals, 
	    please select the button below. Please note that only user accounts 
		over 5,000 edits are allowed to participate in this form.</p>
		@auth
		<a href="/review" class="btn btn-primary">Go to Appeals</a>
		@endauth
		@guest
	    <a href="/login" class="btn btn-primary">Login</a>
	    <a href="/register" class="btn btn-primary">Register</a>
	    @endguest
	  </div>
	</div>
</div>
</div>
<div class="col-md-1"></div>
<br>
<div class="col-md-1"></div>
<div class="col-md-10">
<div class="card">
  <div class="card-body">
    <h5 class="card-title">If you already have an appeal</h5>
    <p class="card-text">Please enter your appeal key below</p>
    {{ Form::open(array('url' => 'publicappeal')) }}
    {{Form::token()}}
    <div class="input-group mb-3">
	  <div class="input-group-prepend">
	    <span class="input-group-text" id="basic-addon1">#</span>{{Form::text('hash', null, ['class'=>'form-control','placeholder'=>'Appeal Key'])}}
	  </div>
	</div>
    <button type="submit" class="btn btn-primary">Submit</button>
    {{ Form::close() }}
    <a href="#" class="btn btn-danger">Forgot Appeal Key</a>
    </form>
  </div>
</div>
</div>
<div class="col-md-1"></div>
</center>
@endsection
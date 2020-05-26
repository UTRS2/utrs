@extends('layouts.app')

@section('content')
    <div class="alert alert-danger" role="alert">
        <b>IMPORTANT MESSAGE</b><br/>
        During this time, no emails will be sent out for appeals except for blocks that are not found for
            accounts. That means you need to keep your appeal secret key on hand at all times (DON'T SHARE IT) and check
            back regularly.<br/><br/>
    </div>
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
                        <a href="{{ route('public.appeal.create.account') }}" class="btn btn-primary">Appeal my block</a>
                    </div>
                </div>
                <div class="card bg-light mb-3 text-center" style="max-width: 18rem;">
                    <div class="card-header">Blocked IP</div>
                    <div class="card-body">
                        <h5 class="card-title">If you <b>DO NOT</b> have a user account</h5>
                        <p class="card-text">on Wikipedia and are blocked, please select the button below
                            to start your appeal.</p>
                        <a href="{{ route('public.appeal.create.ip') }}" class="btn btn-primary">Appeal IP block</a>
                    </div>
                </div>
                <div class="card bg-light mb-3 text-center" style="max-width: 18rem;">
                    <div class="card-header">Login</div>
                    <div class="card-body">
                        <h5 class="card-title">For <b>administrators</b> that are here</h5>
                        <p class="card-text">to comment on, process or assist with appeals,
                            please select the button below. Please note that only 
                            <b>administrators</b> are allowed to participate in this form.</p>
                        @auth
                            <a href="/review" class="btn btn-primary">Go to Appeals</a>
                        @endauth
                        @guest
                            <a href="/oauth" class="btn btn-primary">Login with Wikimedia account</a>
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
                    <div class="alert alert-danger" role="alert">
                        Due to database issues all data from April 29 to May 23 has been lost. 
                        If you are trying to access an appeal and get an error, you will need
                        to file your appeal again.
                    </div>
                    <h5 class="card-title">If you already have an appeal</h5>
                    <p class="card-text">Please enter your appeal key below</p>
                    {{ Form::open(['url' => route('public.appeal.view'), 'method' => 'GET']) }}
                        <div class="input-group mb-3" style="display: block;">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1">#</span>
                                {{ Form::text('hash', null, ['class'=>'form-control','placeholder'=>'Appeal Key']) }}
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    {{ Form::close() }}
                    <a href="#" class="btn btn-danger">Forgot Appeal Key</a>
                </div>
            </div>
        </div>
        <div class="col-md-1"></div>
    </center>
@endsection

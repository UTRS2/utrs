@extends('layouts.app')

@section('content')
    <div class="alert alert-danger" role="alert">
        <b>IMPORTANT MESSAGE</b><br/>
        UTRS is in the process of moving over to UTRS 2.0 of the software. We needed to do this because several users
        were unable to file proper appeals due to IPv6 IP addresses not being accepted by our severs. Therefore, we made
        the decision to move over to a rudimentary beta software instead to allow everyone to appeal properly.<br/><br/>
        In doing this, please understand that there will be bugs and issues. We will try our best to keep up with those
        issues. You can get assistance at <a
                href="https://en.wikipedia.org/wiki/Wikipedia_talk:Unblock_Ticket_Request_System">the UTRS talkpage</a>
        or by placing <a href="https://en.wikipedia.org/wiki/Template:UTRS_help_me">@{{ UTRS help me }}</a> on your
        talkpage.<br/><br/>
        <b>Note: During this time, no emails will be sent out for appeals except for blocks that are not found for
            accounts. That means you need to keep your appeal secret key on hand at all times (DON'T SHARE IT) and check
            back regularly.</b><br/><br/>
        We thank you for your patience.<br/>
        UTRS Development Team
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
                    <h5 class="card-title">If you already have an appeal</h5>
                    <p class="card-text">Please enter your appeal key below</p>
                    {{ Form::open(array('url' => 'publicappeal', 'method' => 'GET')) }}
                    <div class="input-group mb-3" style="display: block;">
                        <div class="input-group-prepend">
                            <span class="input-group-text"
                                  id="basic-addon1">#</span>{{ Form::text('hash', null, ['class'=>'form-control','placeholder'=>'Appeal Key']) }}
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

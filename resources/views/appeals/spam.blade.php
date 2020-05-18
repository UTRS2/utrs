@extends('layouts.app')

@section('title', 'Blocked')
@section('content')

    <div class="col-md-1"></div>
    <div class="col-md-10">
        <div class="alert alert-danger" role="alert">
            It has been detected that you or someone else is trying to spam our system with appeals. Please wait until
            your previous appeal is closed, or if it's already closed, please try again later.<br><br>

            If you are applying for an unblock of an IP address, this could mean that an appeal has already been
            submitted for your IP. In this case, please try again later or contact us to help clarify the issue.
        </div>
        <br>
        <center><img src="https://upload.wikimedia.org/wikipedia/commons/0/01/AnimatedStop2.gif" width="500px"></center>
    </div>

@endsection
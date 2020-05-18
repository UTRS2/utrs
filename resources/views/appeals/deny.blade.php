@extends('layouts.app')

@section('title', 'Access Denied')
@section('content')

    <div class="col-md-1"></div>
    <div class="col-md-10">
        <div class="alert alert-danger" role="alert">
            The appeal you are trying to access has been restricted. You do not have sufficient permissions to view it.
        </div>
        <br>
        <center><img src="https://upload.wikimedia.org/wikipedia/commons/0/01/AnimatedStop2.gif" width="500px"></center>
    </div>

@endsection

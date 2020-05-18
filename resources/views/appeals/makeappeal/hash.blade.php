@extends('layouts.app')

@section('title', 'Appeal submitted')
@section('content')

    <div class="col-md-1"></div>
    <div class="col-md-10">
        <div class="alert alert-danger" role="alert">
            Do not lose this Appeal Key. You can only recover it if you have an account with an email address enabled.
        </div>
        <br>
        <center>Your Appeal key is:<br>
            <h2>{{ $hash }}</h2></center>
    </div>

@endsection

@extends('layouts.app')

@section('title', 'Access Prohibited - HTTP 403')
@section('content')
    <div class="alert alert-danger" role="alert">
        <h2>
            Access Prohibited
        </h2>

        <p>
            This appeal has been locked from view because of potentially sensitive information inside of it. You are prohibited from accessing it. Should you need to know about the contents of this request, you must send a request to utrs-developers@googlegroups.com

            Your request will only be replied to if there is no sensitive information in the file.

            Should you still need to appeal your block, you must create a new appeal.
        </p>
    </div>
@endsection
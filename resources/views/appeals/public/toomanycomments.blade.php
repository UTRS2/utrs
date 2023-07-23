@extends('layouts.app')

@section('title', 'Blocked')
@section('content')

    <div class="col-md-1"></div>
    <div class="col-md-10">
        <div class="alert alert-danger" role="alert">
            <p>
                {{__('appeals.comment-spam')}} utrs-admins@googlegroups.com
            </p>
        </div>
        <br>
    </div>

@endsection

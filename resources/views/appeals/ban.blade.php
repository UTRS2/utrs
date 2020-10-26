@extends('layouts.app')

@section('title', 'Banned')
@section('content')
    <div class="alert alert-danger" role="alert">
        Your IP address or username has been banned from using UTRS.
        @if(!empty($reason))
            The banning administrator specified the following reason: "{{ $reason }}".
        @endif
        @if($expire !== 'indefinite')
            The ban expires on {{ $expire }}.
        @endif
        If you contact UTRS admininstrators about this ban, please mention the following ban ID: #{{ $id }}.
    </div>
@endsection

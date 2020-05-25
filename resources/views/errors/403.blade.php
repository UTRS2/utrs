@extends('layouts.app')

@section('content')
    <div class="alert alert-danger" role="alert">
        <h4 class="alert-heading"><b>Oops.</b> {{ $exception->getMessage() ?: 'You do not have access to view this page or perform this action.' }}</h4>
    </div>
@endsection

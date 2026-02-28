@extends('layouts.app')

@section('title', htmlspecialchars(__('appeals.key.header')))
@section('content')
    <div class="card">
        <h5 class="card-header">{{ __('appeals.key.header') }}</h5>
        <div class="card-body">
            <div id="appealfound">
                <div class="alert alert-danger" role="alert">
                    {{__('appeals.process.success')}}
                </div>
            </div>
        </div>
    </div>
@endsection

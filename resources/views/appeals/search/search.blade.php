@extends('layouts.app')

@section('title', 'Appeal search')
@section('content')
    <div class="mb-2">
        <a href="{{ route('appeal.list') }}" class="btn btn-primary">
            Back to appeal list
        </a>
    </div>

    @component('components.errors')
    @endcomponent


@endsection

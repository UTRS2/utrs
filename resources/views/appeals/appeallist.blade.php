@extends('layouts.app')
@section('content')
    @if($tooladmin)
        <div class="card">
            <h5 class="card-header">Admin tools</h5>
            <div class="card-body">
                <div class="alert alert-danger" role="alert">
                    Site notice management is currently not functional.
                </div>
                <a href="/admin/templates" class="btn btn-primary">Manage Templates</a>
                <a href="{{ route('admin.bans.list') }}" class="btn btn-primary">Manage Bans</a>
                <a href="{{ route('admin.users.list') }}" class="btn btn-primary">Manage Users</a>
                <a href="/admin/sitenotices" class="btn btn-primary disabled">Manage Sitenotices</a>
            </div>
        </div>
    @endif

    @if($noWikis)
        <div class="alert alert-warning mt-2" role="alert">
            <b>Notice:</b> You do not have the necessary permissions to view appeals on any queues.
        </div>
    @else
        <div class="card mt-2 mb-4">
            <h5 class="card-header">Search appeals</h5>
            <div class="card-body">
                {{ Form::open(['url' => route('appeal.search.quick'), 'method' => 'GET']) }}
                {{ Form::label('search', 'Search for Appeal ID or appellant') }}
                <div class="input-group">
                    {{ Form::search('search', old('search'), ['class' => $errors->has('search') ? 'form-control is-invalid' : 'form-control']) }}
                    <div class="input-group-append">
                        {{ Form::submit('Quick search', ['class' => 'btn btn-primary']) }}
                    </div>

                    @if($errors->has('search'))
                        <span class="invalid-feedback" role="alert">
                        <strong>{{ $errors->first('search') }}</strong>
                    </span>
                    @endif
                </div>

                {{ Form::close() }}

                <div class="mt-2">
                    <a href="{{ route('appeal.search.advanced') }}" class="btn btn-secondary">
                        Advanced search
                    </a>
                </div>
            </div>
        </div>
    @endif

    @foreach($appealtypes as $type)
    <div class="card mt-4">
        <h5 class="card-header">{{ $type }}</h5>
        <div class="card-body">
            @component('components.appeal-table', ['appeals' => $appeals[$type]])
            @endcomponent
        </div>
    </div>
    @endforeach

@endsection

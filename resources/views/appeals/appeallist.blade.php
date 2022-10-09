@extends('layouts.app')
@section('content')
    @if($tooladmin)
        <div class="card">
            <h5 class="card-header">{{__('generic.admin-tools.title')}}</h5>
            <div class="card-body">
                <div class="alert alert-danger" role="alert">
                    {{__('generic.admin-tools.sn-disabled')}}
                </div>
                <a href="{{ route('admin.templates.list') }}" class="btn btn-primary">{{__('generic.admin-tools.template')}}</a>
                <a href="{{ route('admin.bans.list') }}" class="btn btn-primary">{{__('generic.admin-tools.bans')}}</a>
                <a href="{{ route('admin.users.list') }}" class="btn btn-primary">{{__('generic.admin-tools.users')}}</a>
                <a href="/admin/sitenotices" class="btn btn-primary disabled">{{__('generic.admin-tools.sitenotice')}}</a>
            </div>
        </div>
    @endif

    @if($noWikis)
        <div class="alert alert-warning mt-2" role="alert">
            {{__('generic.no-appeals')}}
        </div>
    @else
        <div class="card mt-2 mb-4">
            <h5 class="card-header">{{__('generic.list-headers.search-appeals')}}</h5>
            <div class="card-body">
                {{ Form::open(['url' => route('appeal.search.quick'), 'method' => 'GET']) }}
                {{ Form::label('search', __('generic.search-text'), ['class' => 'form-label']) }}
                <div class="input-group">
                    {{ Form::search('search', old('search'), ['class' => $errors->has('search') ? 'form-control is-invalid' : 'form-control']) }}
                    {{ Form::submit(__('generic.quick-search'), ['class' => 'input-group-button btn btn-primary']) }}
                </div>

                @if($errors->has('search'))
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $errors->first('search') }}</strong>
                    </span>
                @endif

                {{ Form::close() }}

                <div class="mt-2">
                    <a href="{{ route('appeal.search.advanced') }}" class="btn btn-secondary">
                        {{__('generic.advanced-search')}}
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

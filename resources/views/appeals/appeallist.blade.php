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
                {{ Form::open(['url' => route('appeal.search'), 'method' => 'GET']) }}
                {{ Form::label('search', 'Search for Appeal ID or appealant') }}
                <div class="input-group">
                    {{ Form::search('search', old('search'), ['class' => $errors->has('search') ? 'form-control is-invalid' : 'form-control']) }}
                    <div class="input-group-append">
                        {{ Form::submit('Search', ['class' => 'btn btn-primary']) }}
                    </div>

                    @if($errors->has('search'))
                        <span class="invalid-feedback" role="alert">
                        <strong>{{ $errors->first('search') }}</strong>
                    </span>
                    @endif
                </div>

                {{ Form::close() }}
            </div>
        </div>
    @endif

    @foreach($appealtypes as $type)
        <div class="card mt-4">
            <h5 class="card-header">{{ $type }}</h5>
            <div class="card-body">
                <table class="table table-dark">
                    <thead>
                    <tr>
                        <th scope="col">ID #</th>
                        <th scope="col">Subject</th>
                        <th scope="col">Status/Type/Wiki</th>
                        <th scope="col">Blocking Admin</th>
                        <th scope="col">Block Reason</th>
                        <th scope="col">Date</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($appeals[$type] as $appeal)
                        @if($appeal->status === \App\Appeal::STATUS_ADMIN)
                            <tr class="table-dark-primary">
                        @elseif($appeal->status === \App\Appeal::STATUS_CHECKUSER)
                            <tr class="table-dark-warning">
                        @else
                            <tr>
                                @endif
                                <td style="vertical-align: middle;">
                                    @isset($appeal->handlingadmin)
                                        @if(Auth::check() && $appeal->handlingadmin == Auth::id())
                                            <a href="{{ route('appeal.view', $appeal) }}" class="btn btn-info">
                                        @else
                                            <a href="{{ route('appeal.view', $appeal) }}" class="btn btn-danger">
                                        @endif
                                    @else
                                        <a href="{{ route('appeal.view', $appeal) }}" class="btn btn-primary">
                                    @endisset
                                        #{{ $appeal->id }}
                                    </a>
                                </td>
                                <td style="vertical-align: middle;">{{ $appeal->appealfor }}</td>
                                <td style="vertical-align: middle">
                                    {{ $appeal->status }}<br/>
                                    @if($appeal->blocktype === 0)
                                        IP address
                                    @elseif($appeal->blocktype === 1)
                                        Account
                                    @elseif($appeal->blocktype === 2)
                                        IP underneath account
                                    @else
                                        Unknown type: {{ $appeal->blocktype }}
                                    @endif
                                    on {{ $appeal->wiki }}<br/>
                                    {{-- @if($appeal->handlingAdminObject)
                                        Reserved by
                                        @can('view', $appeal->handlingAdminObject)
                                            <a href="{{ route('admin.users.view', $appeal->handlingAdminObject) }}">
                                                {{ $appeal->handlingAdminObject->username }}
                                            </a>
                                        @else
                                            {{ $appeal->handlingAdminObject->username }}
                                        @endcan
                                    @endif --}}
                                </td>
                                <td style="vertical-align: middle;">{{ $appeal->blockingadmin }}</td>
                                <td style="vertical-align: middle;">{!! $appeal->getFormattedBlockReason('style="color: #00ffea!important;"') !!}</td>
                                <td style="vertical-align: middle;">{{ $appeal->submitted }}</td>
                            </tr>
                            @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach

@endsection

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
            <table class="table table-bordered table-dark">
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
                        @if($appeal->status === "ADMIN")
                            <tr class="bg-primary">
                        @elseif($appeal->status === "CHECKUSER")
                            <tr class="bg-warning" style="color: #212529!important;">
                        @else
                            <tr>
                        @endif
                            <td style="vertical-align: middle;">
                                @isset($appeal['handlingadmin'])
                                    <a href="/appeal/{{ $appeal['id'] }}" class="btn btn-danger">
                                @else
                                    <a href="/appeal/{{ $appeal['id'] }}" class="btn btn-primary">
                                @endisset
                                    #{{ $appeal->id }}
                                </a>
                            </td>
                            <td style="vertical-align: middle;">{{ $appeal['appealfor'] }}</td>
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
                                on {{ $appeal->wiki }}
                            </td>
                            <td style="vertical-align: middle;">{{ $appeal['blockingadmin'] }}</td>
                            <td style="vertical-align: middle;">{!! $appeal->getFormattedBlockReason('style="color: #00ffea!important;"') !!}</td>
                            <td style="vertical-align: middle;">{{ $appeal['submitted'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach

@endsection

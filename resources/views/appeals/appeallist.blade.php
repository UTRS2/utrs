@extends('layouts.app')
@section('content')
    @if($tooladmin)
        <div class="card">
            <h5 class="card-header">Admin tools</h5>
            <div class="card-body">
                <div class="alert alert-danger" role="alert">
                    Managing templates is the only functional option at this time.
                </div>
                <a href="/admin/templates">
                    <button type="button" class="btn btn-primary">Manage Templates</button>
                </a>
                <a href="/admin/bans">
                    <button type="button" class="btn btn-primary">Manage Bans</button>
                </a>
                <a href="/admin/users">
                    <button type="button" class="btn btn-primary">Manage Users</button>
                </a>
                <a href="/admin/sitenotices">
                    <button type="button" class="btn btn-primary">Manage Sitenotices</button>
                </a>
            </div>
        </div>
    @endif
    <br/>

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

    <div class="card">
        <h5 class="card-header">Current appeals</h5>
        <div class="card-body">
            <table class="table table-bordered table-dark">
                <thead>
                <tr>
                    <th scope="col">ID #</th>
                    <th scope="col">Subject</th>
                    <th scope="col">Status</th>
                    <th scope="col">Blocking Admin</th>
                    <th scope="col">Block Reason</th>
                    <th scope="col">Date</th>
                </tr>
                </thead>
                <tbody>
                @foreach($appeals as $appeal)
                    @if($appeal['status']=="ADMIN")
                        <tr class="bg-primary">
                    @elseif($appeal['status']=="TOOLADMIN")
                        <tr class="bg-info">
                    @elseif($appeal['status']=="CHECKUSER")
                        <tr class="bg-warning">
                    @elseif($appeal['status']=="PROXY")
                        <tr class="bg-warning">
                    @elseif($appeal['status']=="PRIVACY")
                        <tr class="bg-danger">
                    @else
                        <tr>
                    @endif
                            <td style="vertical-align: middle;">
                                <a href="/appeal/{{ $appeal['id'] }}" class="btn btn-primary">
                                    #{{ $appeal->id }}
                                </a>
                            </td>
                            <td style="vertical-align: middle;">{{ $appeal['appealfor'] }}</td>
                            <td style="vertical-align: middle">
                                {{ $appeal->status }}<br/>
                                @if($appeal->blocktype === 0)
                                    Blocked IP address
                                @elseif($appeal->blocktype === 1)
                                    Blocked account
                                @elseif($appeal->blocktype === 2)
                                    Blocked IP underneath account
                                @else
                                    Unknown type {{ $appeal->blocktype }}
                                @endif<br/>
                                on {{ $appeal->wiki }}
                            </td>
                            <td style="vertical-align: middle;">{{ $appeal['blockingadmin'] }}</td>
                            <td style="vertical-align: middle;">{{ $appeal['blockreason'] }}</td>
                            <td style="vertical-align: middle;">{{ $appeal['submitted'] }}</td>
                        </tr>
                        @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection

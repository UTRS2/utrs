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
                    @if($appeal['status']=="NEW")
                        <tr>
                    @elseif($appeal['status']=="USER")
                        <tr>
                    @elseif($appeal['status']=="ADMIN")
                        <tr class="bg-primary">
                    @elseif($appeal['status']=="TOOLADMIN")
                        <tr class="bg-info">
                    @elseif($appeal['status']=="CHECKUSER")
                        <tr class="bg-warning">
                    @elseif($appeal['status']=="PROXY")
                        <tr class="bg-warning">
                    @elseif($appeal['status']=="PRIVACY")
                        <tr class="bg-danger">
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
                            <td style="vertical-align: middle;">{!! $appeal->getFormattedBlockReason('style="color: #ccc;"') !!}</td>
                            <td style="vertical-align: middle;">{{ $appeal['submitted'] }}</td>
                        </tr>
                        @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection

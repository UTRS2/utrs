@extends('layouts.app')

@section('title', 'Appeal #' . $info->id)
@section('content')
    <div class="mb-1">
        <a href="/review" class="btn btn-primary">
            Back to appeal list
        </a>
    </div>
    @if($info['status']==="CLOSED")
        <br/>
        <div class="alert alert-danger" role="alert">This appeal is closed. No further changes can be made to it.</div>
    @endif
    <br/>
    <div class="card">
        <h5 class="card-header">Appeal details</h5>
        <div class="card-body">
            <div class="container">
                <div class="row">
                    <div class="col-5">
                        @if($info['hasAccount'])
                            <h6 class="card-title">Appeal for "{{ $info['wikiAccountName'] }}"</h6>
                        @else
                            <h6 class="card-title">Appeal for "{{ $info['ip'] }}"</h6>
                        @endif
                        <p class="card-text">
                            Appeal number: #{{ $info->id }}
                            <br/>Appeal status: {{ $info['status'] }}
                            <br/>Blocking Admin: {{ $info['blockingAdmin'] }}
                            <br/>Time Submitted: {{ $info['timestamp'] }}
                        </p>
                    </div>
                    <div class="col-7">
                        <div class="alert alert-danger" role="alert">
                            <h4 class="alert-heading">Legacy Actions</h4>
                            <p>You are currently reading an appeal made in UTRS 1.8. Backwards compatability is
                                available for viewing only. Taking action on legacy appeals is restricted. If you feel
                                this is in error, please file a request with the developers.</p>
                        </div>
                    </div>
                </div>
            </div>
            <b><u>Appeal content</u></b>
            <br/>
            <div class="container">
                <div class="row">
                    <div class="col-6">
                        <br/><b>Why should you be unblocked?</b>
                        <p>{{ $info['appealText'] }}</p>
                        <br/><b>What articles do you intend to edit?</b>
                        <p>{{ $info['intendedEdits'] }}</p>
                        <br/><b>Why do you think a block is affecting you?</b>
                        <p>{{ $info['blockReason'] }}</p>
                        <br/><b>Anything else we should consider?</b>
                        <p>{{ $info['otherInfo'] }}</p>
                    </div>
                    <div class="col-3">
                        <center>This is a legacy appeal. The success of this appeal is unknown.
                            <br/><img
                                    src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/ec/Emblem-unblock-request.svg/200px-Emblem-unblock-request.svg.png"
                                    class="img-fluid"></center>
                    </div>
                    <div class="col-3">
                        <center>This is a legacy appeal. This appeal is automatically considered private.
                            <br/><img
                                    src="https://upload.wikimedia.org/wikipedia/commons/thumb/4/4e/Oxygen480-actions-irc-unvoice.svg/200px-Oxygen480-actions-irc-unvoice.svg.png"
                                    class="img-fluid"></center>
                    </div>
                </div>
            </div>
            <br/>
            <b><u>Admin Comments</u></b>
            <br/>
            <br/>
            <table class="table table-bordered table-dark">
                <thead>
                <tr>
                    <th scope="col">Commenting User</th>
                    <th scope="col">Time</th>
                    <th scope="col">Comment</th>
                </tr>
                </thead>
                <tbody>
                @foreach($comments as $comment)
                    @if($comment['action'])
                        <tr>
                    @else
                        <tr class="bg-success">
                            @endif
                            @if(is_null($comment['commentUser']))
                                <td><i>System</i></td>
                                <td><i>{{ $comment['timestamp'] }}</i></td>
                                <td><i>{{ $comment['oldcomments'] }}</i></td>
                            @else
                                <td>{{ $userlist[$comment['commentUser']] }}</td>
                                <td>{{ $comment['timestamp'] }}</td>
                                @if($comment['protected'])
                                    <td><i>Access to comment is restricted.</i></td>
                                @else
                                    <td>{{ $comment['oldcomments'] }}</td>
                                @endif
                            @endif
                        </tr>
                        @endforeach
                </tbody>
            </table>
            <br/>
            <div class="alert alert-danger" role="alert">
                <h4 class="alert-heading">Legacy Comments</h4>
                <p>You are currently reading an appeal made in UTRS 1.8. Backwards compatability is available for
                    viewing only. Commenting on legacy appeals is restricted. If you feel this is in error, please file
                    a request with the developers.</p>
            </div>


        </div>
    </div>

@endsection

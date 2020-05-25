@extends('layouts.app')

@section('title', 'Your Appeal')
@section('content')
    @if($appeal['status']==="ACCEPT" || $appeal['status']==="DECLINE" || $appeal['status']==="EXPIRE")
        <br/>
        <div class="alert alert-danger" role="alert">This appeal is closed. No further changes can be made to it.</div>
    @endif
    @if($appeal['status']=="NOTFOUND")
        <br/>
        <div class="alert alert-danger" role="alert">The block for your appeal could not be found. Please <a
                    href="{{ route('public.appeal.modify', $appeal->appealsecretkey) }}" class="alert-link">modify the
                information</a>.
        </div>
    @endif
    <div class="card mb-4 mt-4">
        <h5 class="card-header">Appeal details</h5>
        <div class="card-body">
            <div class="row">
                <div class="col-5">
                    <h4 class="card-title">Appeal for "{{$appeal['appealfor']}}"</h4>
                    <p class="card-text">
                        Appeal status: {{$appeal['status']}}
                        <br/>Blocking Admin: {{$appeal['blockingadmin']}}
                        <br/>Block reason: {!! $appeal->getFormattedBlockReason() !!}
                        <br/>Time Submitted: {{$appeal['submitted']}}
                        @if($appeal->handlingAdminObject)
                            <br/>Handling Admin: {{ $appeal->handlingAdminObject->username }}
                        @endif

                        @if($appeal['status'] == "NOTFOUND")
                            <a href="{{ route('public.appeal.modify', $appeal->appealsecretkey) }}"
                               class="btn btn-success">
                                Fix block information
                            </a>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <h5 class="card-header">Appeal Content</h5>
        <div class="card-body">
            <div class="row">
                <div class="col-md-9">
                    <b>Why should you be unblocked?</b>
                    <p>{{$appeal['appealtext']}}</p>
                </div>
                <div class="col-md-3">
                    @if($appeal['status']=="ACCEPT")
                        <center>This appeal was approved.<br/>
                            <br/><img
                                    src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/ee/Emblem-unblock-granted.svg/200px-Emblem-unblock-granted.svg.png"
                                    class="img-fluid"></center>
                    @elseif($appeal['status']=="EXPIRE")
                        <center>This appeal expired.<br/>
                            <br/><img
                                    src="https://upload.wikimedia.org/wikipedia/commons/thumb/3/34/Emblem-unblock-expired.svg/200px-Emblem-unblock-expired.svg.png"
                                    class="img-fluid"></center>
                    @elseif($appeal['status']=="DECLINE")
                        <center>This appeal was denied.<br/>
                            <br/><img
                                    src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/59/Emblem-unblock-denied.svg/200px-Emblem-unblock-denied.svg.png"
                                    class="img-fluid"></center>
                    @else
                        <center>This appeal is in progress.<br/>
                            <br/><img
                                    src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/ec/Emblem-unblock-request.svg/200px-Emblem-unblock-request.svg.png"
                                    class="img-fluid"></center>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <h5 class="card-header">Admin Comments</h5>
        <div class="card-body">
            <table class="table table-bordered table-dark">
                <thead>
                <tr>
                    <th scope="col">Commenting User</th>
                    <th scope="col">Time</th>
                    <th scope="col">Comment</th>
                </tr>
                </thead>
                <tbody>
                @foreach($appeal->comments as $comment)
                    @if($comment->action=="comment")
                        <tr class="bg-success">
                    @elseif($comment->action=="responded")
                        <tr class="bg-primary">
                    @else
                        <tr>
                            @endif
                            @if($comment->action!=="comment" && $comment->action!=="responded")
                                @if($comment->user==0)
                                    <td><i>System</i></td>
                                @elseif($comment->user === -1)
                                    <td><i>{{ $appeal->appealfor }}</i></td>
                                @else
                                    <td><i>{{ $comment->userObject->username }}</i></td>
                                @endif
                                <td><i>{{$comment->timestamp}}</i></td>
                                @if($comment->protected)
                                    <td><i>Access to comment is restricted.</i></td>
                                @else
                                    @if($comment->comment!==null)
                                        <td><i>{{$comment->comment}}</i></td>
                                    @else
                                        @if(!is_null($comment->reason))
                                            <td><i>Action: {{$comment->action}},
                                                    Reason: {{$comment->reason}}</i></td>
                                        @else
                                            <td><i>Action: {{$comment->action}}</i></td>
                                        @endif
                                    @endif
                                @endif
                            @else
                                @if($comment->user==0)
                                    <td><i>System</i></td>
                                @elseif($comment->user === -1)
                                    <td><i>{{ $appeal->appealfor }}</i></td>
                                @else
                                    <td><i>{{ $comment->userObject->username }}</i></td>
                                @endif
                                <td>{{$comment->timestamp}}</td>
                                @if($comment->protected || $comment->action=="comment")
                                    <td>Access to comment is restricted.</td>
                                @else
                                    @if($comment->comment!==null)
                                        <td>{{$comment->comment}}</td>
                                    @else
                                        <td>{{$comment->reason}}</td>
                                    @endif
                                @endif
                            @endif
                        </tr>
                        @endforeach
                </tbody>
            </table>
            <i>Lines that are in blue indicate a response to or from the user. Lines in green are comments from other
                administrators.</i>
        </div>
    </div>

    <div class="card mb-4">
        <h5 class="card-header">Drop a comment</h5>
        <div class="card-body">
            @if(!in_array($appeal->status, ['NOTFOUND', 'EXPIRE', 'ACCEPT', 'DECLINE', 'INVALID']))
                {{ Form::open(array('url' => route('public.appeal.comment'))) }}
                {{ Form::hidden('appealsecretkey', $appeal->appealsecretkey) }}
                <div class="form-group">
                    {{ Form::label('comment', 'Add a comment to this appeal:') }}
                    {{ Form::textarea('comment', null, ['rows' => 4, 'class' => 'form-control']) }}
                </div>
                <button type="submit" class="btn btn-success">Submit</button>
                {{ Form::close() }}
            @else
                <div class="alert alert-danger" role="alert">This appeal is closed. No further comments.</div>
            @endif
        </div>
    </div>
@endsection

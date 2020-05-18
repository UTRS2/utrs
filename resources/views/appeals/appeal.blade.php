@extends('layouts.app')

@section('title', 'Appeal #' . $id)
@section('content')
    <div class="container">
        <div class="mb-1">
            <a href="/review" class="btn btn-primary">
                Back to appeal list
            </a>
        </div>

        @if($info->status==="ACCEPT" || $info->status==="DECLINE" || $info->status==="EXPIRE")
            <br/>
            <div class="alert alert-danger" role="alert">
                This appeal is closed. No further changes can be made to it.
            </div>
        @endif

        <div class="card my-2">
            <h4 class="card-header">Appeal details</h4>
            <div class="card-body">
                <div>
                    @if($info->privacyreview!=0 && $info->status=="PRIVACY")
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-primary" role="alert">
                                    You are currently reviewing a ticket that is restricted from public view. You have
                                    three
                                    options to review this ticket:<br>
                                    <br>
                                    1) You select "Publicize Appeal" - Only select this if you have reviewed the entire
                                    appeal and there is no potentially private data involved.<br>
                                    2) You select "Restrict Appeal" - Only select this option if there is private data, 
                                    but it is relevant to the appeal. (Example: Real name, IP that is blocked, position 
                                    in an organization)<br>
                                    3) You select "Oversight Appeal" - Only select this option if there is irrelevant 
                                    personally identifying information (IPII) in the appeal OR if it is an oversight 
                                    block. If there is IPII infomration in the appeal, please contact a developer at 
                                    utrs-developers@googlegroups.com and state what needs to be removed.
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="row">
                        <div class="col-md-5">
                            <h4 class="card-title">Appeal for "{{ $info->appealfor }}"</h4>
                            <p class="card-text">
                                Appeal number: #{{ $info->id }}
                                <br/>Appeal status: {{$info->status}}
                                <br/>Blocking Admin: {{$info->blockingadmin}}
                                <br/>Block Reason: {{$info->blockreason}}
                                <br/>Time Submitted: {{$info->submitted}}
                                <br/>Wiki: {{$info->wiki}}
                                @if(!is_null($info->handlingadmin))
                                    <br/>Handling Admin: {{$userlist[$info->handlingadmin]}}
                                @endif
                                <br/>

                                <a href="https://en.wikipedia.org/wiki/User_talk:{{$info->appealfor}}"
                                   class="btn btn-secondary">
                                    User talk
                                </a>

                                <a href="https://en.wikipedia.org/wiki/Special:Contributions/{{$info->appealfor}}"
                                   class="btn btn-light">
                                    Contribs
                                </a>

                                <a href="https://en.wikipedia.org/wiki/Special:BlockList/{{$info->appealfor}}"
                                   class="btn btn-light">
                                    Find block
                                </a>

                                <a href="https://en.wikipedia.org/w/index.php?title=Special:Log/block&page=User:{{$info->appealfor}}"
                                   class="btn btn-light">
                                    Block log
                                </a>

                                <a href="https://meta.wikimedia.org/wiki/Special:CentralAuth?target={{$info->appealfor}}"
                                   class="btn btn-light">
                                    Global (b)locks
                                </a>

                                @if($perms['admin'])
                                    <a href="https://en.wikipedia.org/wiki/Special:Unblock/{{$info->appealfor}}"
                                       class="btn btn-warning">
                                        Unblock
                                    </a>
                            @endif
                            @if($perms['checkuser'])
                                <h5 class="card-title">CU data</h5>
                                @if($checkuserdone && !is_null($cudata))
                                    IP address: {{$cudata->ipaddress}}<br/>
                                    Useragent: {{$cudata->useragent}}<br/>
                                    Browser Language: {{$cudata->language}}
                                @elseif(is_null($cudata))
                                    <div class="alert alert-danger" role="alert">
                                        The CU data for this appeal has expired.
                                    </div>
                                @else
                                    <div class="alert alert-danger" role="alert">
                                        You have not submitted a request to view the CheckUser data yet.
                                    </div>
                                    {{ Form::open(['url' => '/appeal/checkuser/' . $id]) }}
                                        {{ Form::token() }}

                                        <div class="form-group">
                                            {{ Form::label('reason', 'Reason') }}
                                            {{ Form::textarea('reason', old('reason'), ['class' => 'form-control']) }}
                                        </div>

                                        {{ Form::submit('Submit', ['class' => 'btn btn-success']) }}
                                    {{ Form::close() }}
                                @endif
                            @endif
                        </div>
                        <div class="col-md-7">
                            @if($info->privacyreview!=0 && $info->status=="PRIVACY" && $perms['admin'])
                                <div class="row">
                                    @if ($info->privacyreview==1 || $info->privacyreview==2)
                                        <div class="alert alert-primary" role="alert">
                                            @if ($info->privacyreview==1)
                                                It has been requested that this appeal be hidden from public view and only visible to
                                                administrators.
                                            @elseif ($info->privacyreview==2)
                                                It has been requested that this appeal be oversighted and only available to those on
                                                the privacy team to review.
                                            @endif
                                        </div>
                                    @endif
                                    <div class="col-4">
                                        <a href="/appeal/privacy/{{$id}}/publicize" class="btn btn-danger">
                                            Publicize Appeal
                                        </a>
                                    </div>
                                    <div class="col-4">
                                        <a href="/appeal/privacy/{{$id}}/privatize" class="btn btn-warning">
                                            Restrict Appeal
                                        </a>
                                    </div>
                                    <div class="col-4">
                                        <a href="/appeal/privacy/{{$id}}/oversight" class="btn btn-success">
                                            Oversight Appeal
                                        </a>
                                    </div>
                                </div>
                            @else
                                <div class="row">
                                    <div class="col-md-4"></div>
                                    <div class="col-md-8">
                                        <h5 class="card-title">Actions</h5>
                                        @if(!$perms['admin'])
                                            <div class="alert alert-danger" role="alert">
                                                You are not an admin, and therefore can't perform any action on this
                                                appeal.
                                            </div>
                                        @else
                                            @if($info->status==="ACCEPT" || $info->status==="DECLINE" || $info->status==="EXPIRE")
                                                @if($perms['functionary'])
                                                    <div>
                                                        <a href="/appeal/open/{{$id}}" class="btn btn-success">
                                                            Re-open</a>
                                                        <a href="/appeal/oversight/{{$id}}" class="btn btn-danger">
                                                            Oversight appeal</a>
                                                    </div>
                                                @else
                                                    <div class="alert alert-danger" role="alert">
                                                        This appeal is closed and no further action can be taken.
                                                    </div>
                                                @endif
                                            @else
                                                <div>
                                                    <div class="mb-2">
                                                        @if($info->handlingadmin==null)
                                                            <a href="/appeal/reserve/{{$id}}" class="btn btn-success">
                                                                Reserve
                                                            </a>
                                                        @elseif($info->handlingadmin!=null && $info->handlingadmin == Auth::id())
                                                            <a href="/appeal/release/{{$id}}" class="btn btn-success">
                                                                Release
                                                            </a>
                                                        @elseif($info->handlingadmin!=null && $info->handlingadmin != Auth::id())
                                                            <button class="btn btn-success" disabled>
                                                                Reserve
                                                            </button>
                                                        @endif
                                                        @if($perms['dev'])
                                                            <a href="/appeal/invalidate/{{$id}}" class="btn btn-danger">
                                                                Invalidate
                                                            </a>
                                                        @endif
                                                    </div>

                                                    <div class="mb-2">
                                                        <a href="/appeal/close/{{$id}}/accept" class="btn btn-danger">
                                                            Accept appeal
                                                        </a>

                                                        <a href="/appeal/close/{{$id}}/decline" class="btn btn-danger">
                                                            Decline appeal
                                                        </a>
                                                    </div>

                                                    <div class="mb-2">
                                                        <a href="/appeal/close/{{$id}}/expire" class="btn btn-danger">
                                                            Mark appear as expired
                                                        </a>
                                                    </div>

                                                    @if($info->status=="OPEN")
                                                        <div class="mb-2">
                                                            <a href="/appeal/privacy/{{$id}}" class="btn btn-warning">
                                                                Privacy Team
                                                            </a>
                                                            <a href="/appeal/checkuserreview/{{$id}}" class="btn btn-warning">
                                                                CheckUser
                                                            </a>
                                                            <a href="/appeal/tooladmin/{{$id}}" class="btn btn-warning">
                                                                Tool admin
                                                            </a>
                                                        </div>
                                                    @endif
                                                    @if(($info->status!=="OPEN" && $info->status!=="EXPIRE" && $info->status!=="DECLINE" && $info->status!=="ACCEPT") && ($perms['tooladmin'] || $perms['functionary'] || $perms['developer']))
                                                        <div class="mb-2">
                                                            <a href="/appeal/open/{{$id}}" class="btn btn-info">
                                                                Return to tool users
                                                            </a>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($previousAppeals->isNotEmpty())
        <br />
            <div class="card my-2">
                <h4 class="card-header">
                    Previous appeals
                </h4>

                <div class="card-body">
                    <table class="table table-dark">
                        <tr>
                            <th>Appeal #</th>
                            <th>Appeal For</th>
                            <th>Status</th>
                            <th>Handling admin</th>
                            <th>Submitted at</th>
                        </tr>

                        @foreach($previousAppeals as $appeal)
                            <tr class="{{ $appeal->status === 'ACCEPT' ? 'bg-success' : (in_array($appeal->status,['DECLINE','EXPIRE']) ? 'bg-danger' : '') }}">
                                <td style="vertical-align: middle;">
                                    <a href="/appeal/{{ $appeal->id }}" class="btn btn-primary">
                                        #{{ $appeal->id }}
                                    </a>
                                </td>

                                <td style="vertical-align: middle;">
                                    {{ $appeal->appealfor }}
                                </td>

                                <td style="vertical-align: middle;">
                                    {{ $appeal->status }}
                                </td>

                                <td style="vertical-align: middle;">
                                    @if($appeal->handlingAdminObject)
                                        {{ $appeal->handlingAdminObject->username }}
                                    @else
                                        <i>None</i>
                                    @endif
                                </td>

                                <td style="vertical-align: middle;">
                                    {{ $appeal->submitted }}
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        @endif
        <br />
        <div class="card my-2">
            <h4 class="card-header">Appeal Content</h4>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <b>Why should you be unblocked?</b>
                        <p>{{$info->appealtext}}</p>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                                <div class="col-6">
                                    @if($info->status=="ACCEPT")
                                        <center>This appeal was approved.<br/>
                                            <br/><img
                                                    src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/ee/Emblem-unblock-granted.svg/200px-Emblem-unblock-granted.svg.png"
                                                    class="img-fluid"></center>
                                    @elseif($info->status=="EXPIRE")
                                        <center>This appeal expired.<br/>
                                            <br/><img
                                                    src="https://upload.wikimedia.org/wikipedia/commons/thumb/3/34/Emblem-unblock-expired.svg/200px-Emblem-unblock-expired.svg.png"
                                                    class="img-fluid"></center>
                                    @elseif($info->status=="DECLINE")
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
                                <div class="col-6">
                                    @if($info->privacylevel==0 && $info->privacyreview==0)
                                        <center>This appeal is considered public. Logged in Wikimedians can view this.
                                            <br/><img
                                                    src="https://upload.wikimedia.org/wikipedia/commons/thumb/0/0d/Oxygen480-actions-irc-voice.svg/200px-Oxygen480-actions-irc-voice.svg.png"
                                                    class="img-fluid"></center>
                                    @elseif($info->privacylevel==1 && $info->privacyreview==1)
                                        <center>This appeal is considered private. Only logged in administrators have
                                            access to this appeal.
                                            <br/><img
                                                    src="https://upload.wikimedia.org/wikipedia/commons/thumb/4/4e/Oxygen480-actions-irc-unvoice.svg/200px-Oxygen480-actions-irc-unvoice.svg.png"
                                                    class="img-fluid"></center>
                                    @elseif($info->privacylevel==2 || ($info->privacylevel!==$info->privacyreview))
                                        <center>This appeal is oversighted or under privacy review. Only logged in
                                            Privacy Team members have access to this appeal.
                                            <br/><img
                                                    src="https://upload.wikimedia.org/wikipedia/commons/thumb/0/06/Oversight_logo.png/200px-Oversight_logo.png"
                                                    class="img-fluid"></center>
                                    @endif
                                </div>
                            </div>
                    </div>
                </div>
            </div>
        </div>
        <br />
        <div class="card my-2">
            <h4 class="card-header">Admin Comments</h4>
            <div class="card-body">
                <table class="table table-dark">
                <thead>
                <tr>
                    <th scope="col">Commenting User</th>
                    <th scope="col">Time</th>
                    <th scope="col">Comment</th>
                </tr>
                </thead>
                <tbody>
                @foreach($comments as $comment)
                    <tr class="{{ $comment->action === 'comment' ? 'bg-success' : ($comment->action === 'responded' ? 'bg-primary' : '') }}">
                        @if(is_null($comment['commentUser']))
                            @if($comment->action !== "comment" && $comment->action!=="responded")
                                @if($comment->user==0)
                                    <td><i>System</i></td>
                                @else
                                    <td><i>{{$userlist[$comment->user]}}</i></td>
                                @endif
                                <td><i>{{$comment->timestamp}}</i></td>
                                @if($comment->protected && !$perms['functionary'])
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
                                @else
                                    <td>{{$userlist[$comment->user]}}</td>
                                @endif
                                <td>{{$comment->timestamp}}</td>
                                @if($comment->protected && !$perms['functionary'])
                                    <td>Access to comment is restricted.</td>
                                    @else
                                        @if($comment->comment!==null)
                                            <td>{{$comment->comment}}</td>
                                        @else
                                            <td>{{$comment->reason}}</td>
                                        @endif
                                    @endif
                                @endif
                            @else
                                @if($comment->user==0)
                                    <td><i>System</i></td>
                                @else
                                    <td>{{$userlist[$comment['commentUser']]}}</td>
                                @endif
                                <td>{{$comment->timestamp}}</td>
                                @if($comment->protected && !$perms['functionary'])
                                    <td><i>Access to comment is restricted.</i></td>
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
                <i>Lines that are in blue indicate a response to the user. Lines in green are comments from other
                    administrators or the user involved.</i>
                <br/>
                <br/>
                @if($perms['admin'])
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="card-title">Send a templated reply</h5>
                            @if($info->handlingadmin!=null && $info->handlingadmin == Auth::id())
                                <a href="/appeal/template/{{$id}}" class="btn btn-info">
                                    Send a reply to the user
                                </a>
                            @else
                                <div class="alert alert-danger" role="alert">
                                    You are not the handling admin.
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h5 class="card-title">Drop a comment</h5>
                            {{ Form::open(['url' => '/appeal/comment/' . $id]) }}
                                {{ Form::token() }}

                                <div class="form-group">
                                    {{ Form::label('comment', 'Add a comment to this appeal') }}
                                    {{ Form::textarea('comment', old('comment'), ['class' => 'form-control']) }}
                                </div>

                                {{ Form::submit('Submit', ['class' => 'btn btn-success']) }}
                            {{ Form::close() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

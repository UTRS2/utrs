@extends('layouts.app')
@php use App\Appeal; @endphp

@section('title', 'Appeal #' . $id)
@section('content')
    <div class="container">
        <div class="mb-1">
            <a href="/review" class="btn btn-primary">
                Back to appeal list
            </a>
        </div>

        @if($info->status === Appeal::STATUS_ACCEPT || $info->status === Appeal::STATUS_DECLINE || $info->status === Appeal::STATUS_EXPIRE)
            <br/>
            <div class="alert alert-danger" role="alert">
                This appeal is closed. No further changes can be made to it.
            </div>
        @endif

        <div class="card my-2">
            <h4 class="card-header">Appeal details</h4>
            <div class="card-body">
                <div>
                    <div class="row">
                        <div class="col-md-5">
                            <h4 class="card-title">Appeal for "{{ $info->appealfor }}"</h4>
                            <p class="card-text">
                                Appeal number: #{{ $info->id }}&nbsp;
                                @if($info->user_verified)
                                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/0/06/Oxygen480-status-security-high.svg/30px-Oxygen480-status-security-high.svg.png">
                                <br/><i>This appeal has been verified<br/>to the the account on the wiki.</i>
                                @else
                                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/8/87/Oxygen480-status-security-medium.svg/30px-Oxygen480-status-security-medium.svg.png">
                                <br/><i>This appeal has not been/will not <br/>be verified to the the account on the wiki.</i>
                                @endif
                                <br/>Appeal status: {{ $info->status }}
                                <br/>Blocking Admin: {{ $info->blockingadmin }}
                                <br/>Block Reason: {{ $info->blockreason }}
                                <br/>Time Submitted: {{ $info->submitted }}
                                <br/>Wiki: {{ $info->wiki }}
                                @if(!is_null($info->handlingadmin))
                                    <br/>Handling Admin: {{ $userlist[$info->handlingadmin] }}
                                @endif
                                <br/>

                                <a href="https://en.wikipedia.org/wiki/User_talk:{{ $info->appealfor }}"
                                   class="btn btn-secondary">
                                    User talk
                                </a>

                                <a href="https://en.wikipedia.org/wiki/Special:Contributions/{{ $info->appealfor }}"
                                   class="btn btn-light">
                                    Contribs
                                </a>

                                <a href="https://en.wikipedia.org/wiki/Special:BlockList/{{ $info->appealfor }}"
                                   class="btn btn-light">
                                    Find block
                                </a>

                                <a href="https://en.wikipedia.org/w/index.php?title=Special:Log/block&page=User:{{ $info->appealfor }}"
                                   class="btn btn-light">
                                    Block log
                                </a>

                                <a href="https://meta.wikimedia.org/wiki/Special:CentralAuth?target={{ $info->appealfor }}"
                                   class="btn btn-light">
                                    Global (b)locks
                                </a>

                                @if($perms['admin'])
                                    <a href="https://en.wikipedia.org/wiki/Special:Unblock/{{ $info->appealfor }}"
                                       class="btn btn-warning">
                                        Unblock
                                    </a>
                                @endif
                                @if($perms['checkuser'])
                                <h5 class="card-title">CU data</h5>
                                @if($checkuserdone && !is_null($cudata))
                                    <a href="https://en.wikipedia.org/wiki/User_talk:{{$cudata->ipaddress}}"
                                       class="btn btn-secondary">
                                        User talk
                                    </a>

                                    <a href="https://en.wikipedia.org/wiki/Special:Contributions/{{ $cudata->ipaddress }}"
                                       class="btn btn-light">
                                        Contribs
                                    </a>

                                    <a href="https://en.wikipedia.org/wiki/Special:BlockList/{{ $cudata->ipaddress }}"
                                       class="btn btn-light">
                                        Find block
                                    </a>

                                    <a href="https://en.wikipedia.org/w/index.php?title=Special:Log/block&page=User:{{ $cudata->ipaddress }}"
                                       class="btn btn-light">
                                        Block log
                                    </a>

                                    <a href="https://meta.wikimedia.org/wiki/Special:CentralAuth?target={{ $cudata->ipaddress }}"
                                       class="btn btn-light">
                                        Global (b)locks
                                    </a>

                                    @if($perms['admin'])
                                        <a href="https://en.wikipedia.org/wiki/Special:Unblock/{{ $cudata->ipaddress }}"
                                           class="btn btn-warning">
                                            Unblock
                                        </a>
                                    @endif
                                    <br/>
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
                                                    <a href="/appeal/open/{{ $id }}" class="btn btn-success">
                                                        Re-open</a>
                                                    <a href="/appeal/oversight/{{ $id }}" class="btn btn-danger">
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
                                                        <a href="/appeal/reserve/{{ $id }}" class="btn btn-success">
                                                            Reserve
                                                        </a>
                                                    @elseif($info->handlingadmin!=null && $info->handlingadmin == Auth::id())
                                                        <a href="/appeal/release/{{ $id }}" class="btn btn-success">
                                                            Release
                                                        </a>
                                                    @elseif($info->handlingadmin!=null && $info->handlingadmin != Auth::id())
                                                        <button class="btn btn-success" disabled>
                                                            Reserve
                                                        </button>
                                                    @endif
                                                    @if($perms['dev'])
                                                        <a href="/appeal/invalidate/{{ $id }}" class="btn btn-danger">
                                                            Invalidate
                                                        </a>
                                                    @endif
                                                </div>

                                                <div class="mb-2">
                                                    <a href="/appeal/close/{{ $id }}/accept" class="btn btn-danger">
                                                        Accept appeal
                                                    </a>

                                                    <a href="/appeal/close/{{ $id }}/decline" class="btn btn-danger">
                                                        Decline appeal
                                                    </a>
                                                </div>

                                                <div class="mb-2">
                                                    <a href="/appeal/close/{{ $id }}/expire" class="btn btn-danger">
                                                        Mark appear as expired
                                                    </a>
                                                </div>

                                                @if($info->status=="OPEN")
                                                    <div class="mb-2">
                                                        <a href="/appeal/checkuserreview/{{ $id }}" class="btn btn-warning">
                                                            CheckUser
                                                        </a>
                                                        <a href="/appeal/tooladmin/{{ $id }}" class="btn btn-warning">
                                                            Tool admin
                                                        </a>
                                                    </div>
                                                @endif
                                                @if(($info->status!=="OPEN" && $info->status!=="EXPIRE" && $info->status!=="DECLINE" && $info->status!=="ACCEPT") && ($perms['tooladmin'] || $perms['functionary'] || $perms['developer']))
                                                    <div class="mb-2">
                                                        <a href="/appeal/open/{{ $id }}" class="btn btn-info">
                                                            Return to tool users
                                                        </a>
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
                            <tr class="{{ $appeal->status === Appeal::STATUS_ACCEPT ? 'bg-success' : (in_array($appeal->status, [Appeal::STATUS_DECLINE, Appeal::STATUS_EXPIRE]) ? 'bg-danger' : '') }}">
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
                        <p>{{ $info->appealtext }}</p>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                                <div class="col-12">
                                    @if($info->status === Appeal::STATUS_ACCEPT)
                                        <center>This appeal was approved.<br/>
                                            <br/><img
                                                    src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/ee/Emblem-unblock-granted.svg/200px-Emblem-unblock-granted.svg.png"
                                                    class="img-fluid"></center>
                                    @elseif($info->status === Appeal::STATUS_EXPIRE)
                                        <center>This appeal expired.<br/>
                                            <br/><img
                                                    src="https://upload.wikimedia.org/wikipedia/commons/thumb/3/34/Emblem-unblock-expired.svg/200px-Emblem-unblock-expired.svg.png"
                                                    class="img-fluid"></center>
                                    @elseif($info->status === Appeal::STATUS_DECLINE)
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
                            @if($comment->action !== "comment" && $comment->action !== "responded")
                                @if($comment->user==0)
                                    <td><i>System</i></td>
                                @elseif($comment->user === -1)
                                    <td><i>{{ $info->appealfor }}</i></td>
                                @else
                                    <td><i>{{ $userlist[$comment->user] }}</i></td>
                                @endif
                                <td><i>{{ $comment->timestamp }}</i></td>
                                @if($comment->protected && !$perms['functionary'])
                                    <td><i>Access to comment is restricted.</i></td>
                                @else
                                    @if($comment->comment !== null)
                                        <td><i>{{ $comment->comment }}</i></td>
                                    @else
                                        @if(!is_null($comment->reason))
                                            <td><i>Action: {{ $comment->action }},
                                                    Reason: {{ $comment->reason }}</i></td>
                                        @else
                                            <td><i>Action: {{ $comment->action }}</i></td>
                                        @endif
                                    @endif
                                @endif
                            @else
                                @if($comment->user==0)
                                    <td><i>System</i></td>
                                @elseif($comment->user === -1)
                                    <td><i>{{ $info->appealfor }}</i></td>
                                @else
                                    <td>{{ $userlist[$comment->user] }}</td>
                                @endif
                                <td>{{ $comment->timestamp }}</td>
                                @if($comment->protected && !$perms['functionary'])
                                    <td>Access to comment is restricted.</td>
                                    @else
                                        @if($comment->comment !== null)
                                            <td>{{ $comment->comment }}</td>
                                        @else
                                            <td>{{ $comment->reason }}</td>
                                        @endif
                                    @endif
                                @endif
                            @else
                                @if($comment->user==0)
                                    <td><i>System</i></td>
                                @elseif($comment->user === -1)
                                    <td><i>{{ $info->appealfor }}</i></td>
                                @else
                                    <td>{{ $userlist[$comment['commentUser']] }}</td>
                                @endif
                                <td>{{ $comment->timestamp }}</td>
                                @if($comment->protected && !$perms['functionary'])
                                    <td><i>Access to comment is restricted.</i></td>
                                @else
                                    @if($comment->comment !== null)
                                        <td>{{ $comment->comment }}</td>
                                    @else
                                        <td>{{ $comment->reason }}</td>
                                    @endif
                                @endif
                            @endif
                        </tr>
                        @endforeach
                </tbody>
            </table>
                <i>Lines that are in blue indicate a response to or from the user. Lines in green are comments from other
                    administrators or the user involved.</i>
                <br/>
                <br/>
                @if($perms['admin'])
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="card-title">Send a templated reply</h5>
                            @if($info->handlingadmin != null && $info->handlingadmin == Auth::id())
                                <a href="/appeal/template/{{ $id }}" class="btn btn-info">
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

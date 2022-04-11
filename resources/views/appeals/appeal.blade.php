@extends('layouts.app')
@php use App\Models\Appeal; use App\Models\LogEntry; @endphp

@section('title', 'Appeal #' . $id)
@section('content')
    <div class="container">
        <div class="mb-1">
            <a href="{{ route('appeal.list') }}" class="btn btn-primary">
                Back to appeal list
            </a>
        </div>

        @if($info->status === Appeal::STATUS_ACCEPT || $info->status === Appeal::STATUS_DECLINE || $info->status === Appeal::STATUS_EXPIRE)
            <br/>
            <div class="alert alert-danger" role="alert">
                This appeal is closed. No further changes can be made to it.
            </div>
        @endif

        @if(sizeof($errors)>0)
            <div class="alert alert-danger" role="alert">
                The following errors occured:
                <ul>
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
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
                                <br/><i>This appeal has been verified<br/>to the account on the wiki.</i>
                                @else
                                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/8/87/Oxygen480-status-security-medium.svg/30px-Oxygen480-status-security-medium.svg.png">
                                <br/><i>This appeal has not been/will not <br/>be verified to the account on the wiki.</i>
                                @endif
                                <br/>Appeal status: {{ $info->status }}
                                <br/>Blocking Admin: {{ $info->blockingadmin }}
                                <br/>Block Reason: {!! $info->getFormattedBlockReason() !!}
                                <br/>Time Submitted: {{ $info->submitted }}
                                <br/>Wiki: {{ $info->wiki }}
                                @if(!is_null($info->handlingadmin))
                                    <br/>Handling Admin: {{ $info->handlingAdminObject->username }}
                                @endif
                                <br/>

                                @component('components.user-action-buttons', ['target' => $info->appealfor, 'baseUrl' => \App\Services\Facades\MediaWikiRepository::getTargetProperty($info->wiki, 'url_base'), 'canUnblock' => $perms['admin']])
                                @endcomponent
                                @if($perms['checkuser'])
                                <h5 class="card-title">CU data</h5>
                                @if($checkuserdone && !is_null($cudata))
                                    @component('components.user-action-buttons', ['target' => $cudata->ipaddress, 'baseUrl' => \App\Services\Facades\MediaWikiRepository::getTargetProperty($info->wiki, 'url_base'), 'canUnblock' => $perms['admin']])
                                    @endcomponent

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
                                    {{ Form::open(['url' => route('appeal.action.viewcheckuser', $info)]) }}
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
                                    @can('update', $info)
                                        @if($info->status === Appeal::STATUS_ACCEPT || $info->status === Appeal::STATUS_DECLINE || $info->status === Appeal::STATUS_EXPIRE)
                                            @if($perms['functionary'])
                                                <div>
                                                    <form action="{{ route('appeal.action.reopen', $info) }}" method="POST" style="display: inline;">
                                                        @csrf
                                                        <button class="btn btn-success">
                                                            Re-open
                                                        </button>
                                                    </form>
                                                </div>
                                            @else
                                                <div class="alert alert-danger" role="alert">
                                                    This appeal is closed and no further action can be taken.
                                                </div>
                                            @endif
                                        @else
                                            <div>
                                                <div class="mb-2">
                                                    @if($info->handlingadmin == null)
                                                        <form action="{{ route('appeal.action.reserve', $info) }}" method="POST" style="display: inline;">
                                                            @csrf
                                                            <button class="btn btn-success">
                                                                Reserve
                                                            </button>
                                                        </form>
                                                    @elseif($info->handlingadmin == Auth::id() || $perms['tooladmin'] || $perms['developer'])
                                                        <form action="{{ route('appeal.action.release', $info) }}" method="POST" style="display: inline;">
                                                            @csrf
                                                            <button class="btn btn-success">
                                                                @if($info->handlingadmin != Auth::id()) Force @endif
                                                                Release
                                                            </button>
                                                        </form>
                                                    @else
                                                        <button class="btn btn-success" disabled>
                                                            Reserve
                                                        </button>
                                                    @endif {{-- disabled button --}}
                                                    @if($perms['developer'] || $perms['oversight'])
                                                        <form action="{{ route('appeal.action.invalidate', $info) }}" method="POST" style="display: inline;">
                                                            @csrf
                                                            <button class="btn btn-danger">
                                                                Invalidate/Oversight
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>

                                                <div class="mb-2">
                                                    <form action="{{ route('appeal.action.close', [$info, Appeal::STATUS_ACCEPT]) }}" method="POST" style="display: inline;">
                                                        @csrf
                                                        <button class="btn btn-danger">
                                                            Accept appeal
                                                        </button>
                                                    </form>

                                                    <form action="{{ route('appeal.action.close', [$info, Appeal::STATUS_DECLINE]) }}" method="POST" style="display: inline;">
                                                        @csrf
                                                        <button class="btn btn-danger">
                                                            Decline appeal
                                                        </button>
                                                    </form>
                                                </div>

                                                <div class="mb-2">
                                                    <form action="{{ route('appeal.action.close', [$info, Appeal::STATUS_EXPIRE]) }}" method="POST" style="display: inline;">
                                                        @csrf
                                                        <button class="btn btn-danger">
                                                            Mark appeal as expired
                                                        </button>
                                                    </form>
                                                </div>

                                                @if($info->status === Appeal::STATUS_OPEN || $info->status === Appeal::STATUS_AWAITING_REPLY)
                                                    <div class="mb-2">
                                                        <button class="btn btn-warning" data-toggle="modal" data-target="#checkuserModal">
                                                            CheckUser
                                                        </button>
                                                        <form action="{{ route('appeal.action.tooladmin', $info) }}" method="POST" style="display: inline;">
                                                            @csrf
                                                            <button class="btn btn-warning">
                                                                Tool admin
                                                            </button>
                                                        </form>
                                                    </div>
                                                @endif
                                                @if(($info->status !== Appeal::STATUS_OPEN && $info->status !== Appeal::STATUS_EXPIRE && $info->status !== Appeal::STATUS_AWAITING_REPLY && $info->status !== Appeal::STATUS_DECLINE && $info->status !== Appeal::STATUS_ACCEPT) && ($perms['tooladmin'] || $perms['functionary'] || $perms['developer']))
                                                    <div class="mb-2">
                                                        <form action="{{ route('appeal.action.reopen', $info) }}" method="POST">
                                                            @csrf
                                                            <button class="btn btn-info">
                                                                Return to tool users
                                                            </button>
                                                        </form>
                                                    </div>
                                                @endif
                                                @if($perms['developer'] && ($info->status=="NOTFOUND" || $info->status=="VERIFY"))
                                                    <div class="mb-2">
                                                        <form action="{{ route('appeal.action.findagain', $info) }}" method="POST">
                                                            @csrf
                                                            <button class="btn btn-info">
                                                                Re-verify block
                                                            </button>
                                                        </form>
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                    @else
                                        <div class="alert alert-danger" role="alert">
                                            You are not permitted to perform any actions on this appeal.
                                        </div>
                                    @endcan
                                </div>
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

                    <div class="mt-2">
                        <a href="{{ route('appeal.search.advanced', ['appealfor' => $appeal->appealfor]) }}" class="btn btn-info">Advanced search</a>
                    </div>
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
                                @if($comment->user_id == 0)
                                    <td><i>System</i></td>
                                @elseif($comment->user_id === -1)
                                    <td><i>{{ $info->appealfor }}</i></td>
                                @else
                                    @if($perms['tooladmin'])
                                        <td><i><a href="{{ route('admin.users.view', $comment->user) }}" class="text-light">{{ $comment->user->username }}</a></i></td>
                                    @else
                                        <td><i>{{ $comment->user->username }}</i></td>
                                    @endif
                                @endif
                                <td><i>{{ $comment->timestamp }}</i></td>
                                @if($comment->protected === LogEntry::LOG_PROTECTION_FUNCTIONARY && !$perms['functionary'])
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
                                @if($comment->user_id == 0)
                                    <td><i>System</i></td>
                                @elseif($comment->user_id === -1)
                                    <td><i>{{ $info->appealfor }}</i></td>
                                @else
                                    @if($perms['tooladmin'])
                                        <td><i><a href="{{ route('admin.users.view', $comment->user) }}" class="text-light">{{ $comment->user->username }}</a></i></td>
                                    @else
                                        <td><i>{{ $comment->user->username }}</i></td>
                                    @endif
                                @endif
                                <td>{{ $comment->timestamp }}</td>
                                @if($comment->protected === LogEntry::LOG_PROTECTION_FUNCTIONARY && !$perms['functionary'])
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
                                @if($comment->user_id == 0)
                                    <td><i>System</i></td>
                                @elseif($comment->user_id === -1)
                                    <td><i>{{ $info->appealfor }}</i></td>
                                @else
                                    @if($perms['tooladmin'])
                                        <td><i><a href="{{ route('admin.users.view', $comment->user) }}" class="text-light">{{ $comment->user->username }}</a></i></td>
                                    @else
                                        <td><i>{{ $comment->user->username }}</i></td>
                                    @endif
                                @endif
                                <td>{{ $comment->timestamp }}</td>
                                @if($comment->protected === LogEntry::LOG_PROTECTION_FUNCTIONARY && !$perms['functionary'])
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
                @can('update', $info)
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="card-title">Send a templated reply</h5>
                            @if($info->handlingadmin != null && $info->handlingadmin == Auth::id())
                                <a href="{{ route('appeal.template', $info) }}" class="btn btn-info">
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
                            {{ Form::open(['url' => route('appeal.action.comment', $info)]) }}
                                {{ Form::token() }}

                                <div class="form-group">
                                    {{ Form::label('comment', 'Add a comment to this appeal') }}
                                    {{ Form::textarea('comment', old('comment'), ['class' => 'form-control']) }}
                                </div>

                                {{ Form::submit('Submit', ['class' => 'btn btn-success']) }}
                            {{ Form::close() }}
                        </div>
                    </div>
                @endcan
            </div>
        </div>
    </div>

    <div class="modal fade" id="checkuserModal" tabindex="-1" role="dialog" aria-labelledby="checkuserModalTitle" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="checkuserModalTitle">Submit to CheckUser review</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                {{ Form::open(['url' => route('appeal.action.requestcheckuser', $info)]) }}
                {{ Form::token() }}
                <div class="modal-body">

                    <div class="form-group mb-4">
                        {{ Form::label('cu_reason', 'What would you like the checkuser to review in this appeal?') }}
                        {{ Form::input('text', 'cu_reason', old('cu_reason'), ['class' => 'form-control']) }}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    {{ Form::submit('Submit', ['class' => 'btn btn-primary']) }}
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
@endsection

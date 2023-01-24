@extends('layouts.app')
@php use App\Models\Appeal; use App\Models\LogEntry; @endphp

@section('title', 'Appeal #' . $id)
@section('content')
    <div class="container">
        <div class="mb-1">
            <a href="{{ route('appeal.list') }}" class="btn btn-primary">
                {{__('appeals.nav.back-appeal-list')}}
            </a>
        </div>

        @if($info->status === Appeal::STATUS_ACCEPT || $info->status === Appeal::STATUS_DECLINE || $info->status === Appeal::STATUS_EXPIRE)
            <br/>
            <div class="alert alert-danger" role="alert">
                {{__('appeals.closed-notice')}}
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
            <h4 class="card-header">{{__('appeals.section-headers.details')}}</h4>
            <div class="card-body">
                <div>
                    <div class="row">
                        <div class="col-md-5">
                            <h4 class="card-title">{{__('appeals.appeal-title',['name'=>$info->appealfor])}}</h4>
                            <p class="card-text">
                                {{__('appeals.appeal-number')}} #{{ $info->id }}&nbsp;
                                @if($info->user_verified == 1)
                                <br/><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/0/06/Oxygen480-status-security-high.svg/30px-Oxygen480-status-security-high.svg.png">
                                <i>{{__('appeals.verify.verified')}}</i>
                                @elseif($info->user_verified == -1)
                                <br/><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/f/f8/Oxygen480-status-security-low.svg/30px-Oxygen480-status-security-low.svg.png">
                                <i>This appeal will not be able to be verified.</i>
                                @else
                                <br/><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/8/87/Oxygen480-status-security-medium.svg/30px-Oxygen480-status-security-medium.svg.png">
                                <i>{{__('appeals.verify.not-verified')}}</i>
                                @endif
                                <br/>{{__('appeals.appeal-types.title')}}: 
                                @if($info->blocktype==0)
                                    {{__('appeals.appeal-types.ip')}}
                                @elseif($info->blocktype==1)
                                    {{__('appeals.appeal-types.account')}}
                                @elseif($info->blocktype==2)
                                    {{__('appeals.appeal-types.ip-under')}}
                                @endif
                                <br/>{{__('appeals.details-status')}}: {{ __('appeals.status.'.$info->status) }}
                                <br/>{{__('appeals.details-block-admin')}}: {{ $info->blockingadmin }}
                                <br/>{{__('appeals.details-block-reason')}}: {!! $info->getFormattedBlockReason() !!}
                                @if($info->hiddenip != NULL && $info->blocktype==2)
                                <br/><b style="color:red">{{__('appeals.cu.under-ip')}}</b>
                                @endif
                                <br/>{{__('appeals.details-submitted')}}: {{ $info->submitted }}
                                <br/>Wiki: {{ $info->wiki }}
                                @if(!is_null($info->handlingadmin))
                                    <br/>{{__('appeals.details-handling-admin')}}: {{ $info->handlingAdminObject->username }}
                                @endif
                                <br/>

                                @component('components.user-action-buttons', ['target' => $info->appealfor, 'baseUrl' => \App\Services\Facades\MediaWikiRepository::getTargetProperty($info->wiki, 'url_base'), 'canUnblock' => $perms['admin']])
                                @endcomponent
                                @if($perms['checkuser'])
                                <br/><br/>
                                <h5 class="card-title">{{__('appeals.cu.title')}}</h5>
                                @if($info->hiddenip!=NULL)
                                <b style="color:red">{{__('appeals.cu.user-ip')}}: {{$info->hiddenip}}</b><br/>
                                @component('components.user-action-buttons', ['target' => $info->hiddenip, 'baseUrl' => \App\Services\Facades\MediaWikiRepository::getTargetProperty($info->wiki, 'url_base'), 'canUnblock' => $perms['admin']])
                                @endcomponent
                                @endif
                                @if($checkuserdone && !is_null($cudata))
                                    <br/>
                                    IP address: {{$cudata->ipaddress}}<br/>
                                    @component('components.user-action-buttons', ['target' => $cudata->ipaddress, 'baseUrl' => \App\Services\Facades\MediaWikiRepository::getTargetProperty($info->wiki, 'url_base'), 'canUnblock' => $perms['admin']])
                                    @endcomponent
                                    <br/>
                                    Useragent: {{$cudata->useragent}}<br/>
                                    Browser Language: {{$cudata->language}}
                                @elseif(is_null($cudata))
                                    <div class="alert alert-danger" role="alert">
                                        {{__('appeals.cu.data-expire')}}
                                    </div>
                                @else
                                    <div class="alert alert-danger" role="alert">
                                        {{__('appeals.cu.no-request')}}
                                    </div>
                                    {{ Form::open(['url' => route('appeal.action.viewcheckuser', $info)]) }}
                                        {{ Form::token() }}

                                        <div class="form-group">
                                            {{ Form::label('reason', __('appeals.cu.reason')) }}
                                            {{ Form::textarea('reason', old('reason'), ['class' => 'form-control']) }}
                                        </div>

                                        {{ Form::submit(__('appeals.cu.submit'), ['class' => 'btn btn-success']) }}
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
                                                            {{__('appeals.links.reopen')}}
                                                        </button>
                                                    </form>
                                                </div>
                                            @else
                                                <div class="alert alert-danger" role="alert">
                                                    {{__('appeals.closed-notice')}}
                                                </div>
                                            @endif
                                        @else
                                            <div>
                                                <div class="mb-2">
                                                    @if($info->handlingadmin == null)
                                                        <form action="{{ route('appeal.action.reserve', $info) }}" method="POST" style="display: inline;">
                                                            @csrf
                                                            <button class="btn btn-success">
                                                                {{__('appeals.links.reserve')}}
                                                            </button>
                                                        </form>
                                                    @elseif($info->handlingadmin == Auth::id() || $perms['tooladmin'] || $perms['developer'])
                                                        <form action="{{ route('appeal.action.release', $info) }}" method="POST" style="display: inline;">
                                                            @csrf
                                                            <button class="btn btn-success">
                                                                @if($info->handlingadmin != Auth::id()) {{__('appeals.links.force')}} @endif
                                                                {{__('appeals.links.release')}}
                                                            </button>
                                                        </form>
                                                    @else
                                                        <button class="btn btn-success" disabled>
                                                            {{__('appeals.links.reserve')}}
                                                        </button>
                                                    @endif {{-- disabled button --}}
                                                    @if($perms['developer'] || $perms['oversight'])
                                                        <form action="{{ route('appeal.action.invalidate', $info) }}" method="POST" style="display: inline;">
                                                            @csrf
                                                            <button class="btn btn-danger">
                                                                {{__('appeals.links.invalidate')}}
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>

                                                <div class="mb-2">
                                                    <form action="{{ route('appeal.action.close', [$info, Appeal::STATUS_ACCEPT]) }}" method="POST" style="display: inline;">
                                                        @csrf
                                                        <button class="btn btn-danger">
                                                            {{__('appeals.links.accept')}}
                                                        </button>
                                                    </form>

                                                    <form action="{{ route('appeal.action.close', [$info, Appeal::STATUS_DECLINE]) }}" method="POST" style="display: inline;">
                                                        @csrf
                                                        <button class="btn btn-danger">
                                                            {{__('appeals.links.decline')}}
                                                        </button>
                                                    </form>
                                                </div>

                                                <div class="mb-2">
                                                    <form action="{{ route('appeal.action.close', [$info, Appeal::STATUS_EXPIRE]) }}" method="POST" style="display: inline;">
                                                        @csrf
                                                        <button class="btn btn-danger">
                                                            {{__('appeals.links.expire')}}
                                                        </button>
                                                    </form>
                                                </div>

                                                @if($info->status === Appeal::STATUS_OPEN || $info->status === Appeal::STATUS_AWAITING_REPLY)
                                                    <div class="mb-2">
                                                        <button class="btn btn-warning" data-toggle="modal" data-target="#checkuserModal">
                                                            {{__('appeals.links.checkuser')}}
                                                        </button>
                                                        <form action="{{ route('appeal.action.tooladmin', $info) }}" method="POST" style="display: inline;">
                                                            @csrf
                                                            <button class="btn btn-warning">
                                                                {{__('appeals.links.tooladmin')}}
                                                            </button>
                                                        </form>
                                                    </div>
                                                @endif
                                                @if(($info->status !== Appeal::STATUS_OPEN && $info->status !== Appeal::STATUS_EXPIRE && $info->status !== Appeal::STATUS_AWAITING_REPLY && $info->status !== Appeal::STATUS_DECLINE && $info->status !== Appeal::STATUS_ACCEPT) && ($perms['tooladmin'] || $perms['functionary'] || $perms['developer']))
                                                    <div class="mb-2">
                                                        <form action="{{ route('appeal.action.reopen', $info) }}" method="POST">
                                                            @csrf
                                                            <button class="btn btn-info">
                                                                {{__('appeals.links.return')}}
                                                            </button>
                                                        </form>
                                                    </div>
                                                @endif
                                                @if($perms['developer'] && ($info->status=="NOTFOUND" || $info->status=="VERIFY"))
                                                    <div class="mb-2">
                                                        <form action="{{ route('appeal.action.findagain', $info) }}" method="POST">
                                                            @csrf
                                                            <button class="btn btn-info">
                                                                {{__('appeals.links.reverify')}}
                                                            </button>
                                                        </form>
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                    @else
                                        <div class="alert alert-danger" role="alert">
                                            {{__('appeals.noaction')}}
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
                    {{__('appeals.header-previous-appeals')}}
                </h4>

                <div class="card-body">
                    <table class="table table-dark">
                        <tr>
                            <th>{{__('appeals.appeal-number')}}</th>
                            <th>{{__('appeals.appeal-for')}}</th>
                            <th>{{__('appeals.details-status')}}</th>
                            <th>{{__('appeals.details-handling-admin')}}</th>
                            <th>{{__('appeals.details-submitted')}}</th>
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
                                        <i>{{__('appeals.appeal-none')}}</i>
                                    @endif
                                </td>

                                <td style="vertical-align: middle;">
                                    {{ $appeal->submitted }}
                                </td>
                            </tr>
                        @endforeach
                    </table>

                    <div class="mt-2">
                        <a href="{{ route('appeal.search.advanced', ['appealfor' => $appeal->appealfor]) }}" class="btn btn-info">{{__('appeals.links.advance-search')}}</a>
                    </div>
                </div>
            </div>
        @endif
        <br />
        <div class="card my-2">
            <h4 class="card-header">{{__('appeals.section-headers.content')}}</h4>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <b>{{__('appeals.content-question-why')}}</b>
                        <p>{{ $info->appealtext }}</p>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                                <div class="col-12">
                                    @if($info->status === Appeal::STATUS_ACCEPT)
                                        <center>{{__('appeals.status-texts.ACCEPT')}}<br/>
                                            <br/><img
                                                    src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/ee/Emblem-unblock-granted.svg/200px-Emblem-unblock-granted.svg.png"
                                                    class="img-fluid"></center>
                                    @elseif($info->status === Appeal::STATUS_EXPIRE)
                                        <center>{{__('appeals.status-texts.EXPIRE')}}<br/>
                                            <br/><img
                                                    src="https://upload.wikimedia.org/wikipedia/commons/thumb/3/34/Emblem-unblock-expired.svg/200px-Emblem-unblock-expired.svg.png"
                                                    class="img-fluid"></center>
                                    @elseif($info->status === Appeal::STATUS_DECLINE)
                                        <center>{{__('appeals.status-texts.DECLINE')}}<br/>
                                            <br/><img
                                                    src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/59/Emblem-unblock-denied.svg/200px-Emblem-unblock-denied.svg.png"
                                                    class="img-fluid"></center>
                                    @else
                                        <center>{{__('appeals.status-texts.default')}}<br/>
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
            <h4 class="card-header">{{__('appeals.section-headers.comments')}}</h4>
            <div class="card-body">
                <table class="table table-dark">
                <thead>
                <tr>
                    <th scope="col">{{ __('generic.logs-user') }}</th>
                    <th scope="col">{{ __('generic.logs-time') }}</th>
                    <th scope="col">{{ __('generic.logs-action') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($comments as $comment)
                    <tr class="{{ $comment->action === 'comment' ? 'bg-success' : ($comment->action === 'responded' ? 'bg-primary' : '') }}">
                        @if(is_null($comment['commentUser']))
                            @if($comment->action !== "comment" && $comment->action !== "responded")
                                @if($comment->user_id == 0)
                                    <td><i>{{__('appeals.comments.system')}}</i></td>
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
                                    <td><i>{{__('appeals.comments.restricted')}}</i></td>
                                @else
                                    @if($comment->comment !== null)
                                        <td><i>{{ $comment->comment }}</i></td>
                                    @else
                                        @if(!is_null($comment->reason))
                                            <td><i>{{__('appeals.comments.action')}}: {{ $comment->action }},
                                                    {{__('appeals.comments.reason')}}: {{ $comment->reason }}</i></td>
                                        @else
                                            <td><i>{{__('appeals.comments.action')}}: {{ $comment->action }}</i></td>
                                        @endif
                                    @endif
                                @endif
                            @else
                                @if($comment->user_id == 0)
                                    <td><i>{{__('appeals.comments.system')}}</i></td>
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
                                    <td>{{__('appeals.comments.restricted')}}</td>
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
                                    <td><i>{{__('appeals.comments.system')}}</i></td>
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
                                    <td><i>{{__('appeals.comments.restricted')}}</i></td>
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
                <i>{{__('appeals.comment-color-text')}}</i>
                <br/>
                <br/>
                @can('update', $info)
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="card-title">{{__('appeals.send-reply-header')}}</h5>
                            @if($info->handlingadmin != null && $info->handlingadmin == Auth::id())
                                <a href="{{ route('appeal.template', $info) }}" class="btn btn-info">
                                    {{__('appeals.send-reply-button')}}
                                </a>
                            @else
                                <div class="alert alert-danger" role="alert">
                                    {{__('appeals.not-handling-admin')}}
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h5 class="card-title">{{__('appeals.comments.leave')}}</h5>
                            {{ Form::open(['url' => route('appeal.action.comment', $info)]) }}
                                {{ Form::token() }}

                                <div class="form-group">
                                    {{ Form::label('comment', __('appeals.comments.add')) }}
                                    {{ Form::textarea('comment', old('comment'), ['class' => 'form-control']) }}
                                </div>

                                {{ Form::submit(__('appeals.cu.submit'), ['class' => 'btn btn-success']) }}
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
                    <h5 class="modal-title" id="checkuserModalTitle">{{__('appeals.cu.submit-title')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                {{ Form::open(['url' => route('appeal.action.requestcheckuser', $info)]) }}
                {{ Form::token() }}
                <div class="modal-body">

                    <div class="form-group mb-4">
                        {{ Form::label('cu_reason', __('appeals.cu.review-req')) }}
                        {{ Form::input('text', 'cu_reason', old('cu_reason'), ['class' => 'form-control']) }}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    {{ Form::submit(__('appeals.cu.submit'), ['class' => 'btn btn-primary']) }}
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
@endsection

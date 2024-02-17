@extends('layouts.app')
@php use App\Models\Appeal; use App\Models\LogEntry; @endphp

@section('scripts')
function displayTransfer() {
    document.getElementById('transfer').style.display = "block";
    document.getElementById('transferbutton').style.display = "none";
}
@endsection

@section('title', 'Appeal #' . $id)
@section('content')
    <div class="container">
        <div class="mb-1">
            <a href="{{ route('appeal.list') }}" class="btn btn-primary">
                {{__('appeals.nav.back-appeal-list')}}
            </a>
            <a href="/appeal/map/{{$id}}" class="btn btn-info">
                {{__('appeals.map.switch-appeal-map')}}
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
                        <div class="col-md-6">
                            <h4 class="card-title">{{__('appeals.appeal-title',['name'=>$info->appealfor])}}</h4>
                            <p class="card-text">
                                {{__('appeals.appeal-number')}} #{{ $info->id }}&nbsp;
                                @if($info->proxy == 0)
                                <br/><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/0/06/Oxygen480-status-security-high.svg/30px-Oxygen480-status-security-high.svg.png">
                                <i>{{__('appeals.proxy.unlikelyproxy')}}</i>
                                @else
                                <br/><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/f/f8/Oxygen480-status-security-low.svg/30px-Oxygen480-status-security-low.svg.png">
                                <i>{{__('appeals.proxy.likelyproxy')}}</i>
                                @endif
                                @if($info->user_verified == 1)
                                    @if($info->blocktype == 0)
                                    <br /><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/f/f8/Oxygen480-status-security-low.svg/30px-Oxygen480-status-security-low.svg.png">
                                    <i>{{__('appeals.verify.notableverified')}}</i>
                                    <br/><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/0/06/Oxygen480-status-security-high.svg/30px-Oxygen480-status-security-high.svg.png">
                                    <i>{{__('appeals.verify.ip-emailverified')}}</i>
                                    <br /><b style="color:red">{{__('appeals.verify.negativeaction')}}</b>
                                    @else
                                    <br/><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/0/06/Oxygen480-status-security-high.svg/30px-Oxygen480-status-security-high.svg.png">
                                    <i>{{__('appeals.verify.verified')}}</i>
                                    @endif
                                @elseif($info->user_verified == -1)
                                <br/><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/f/f8/Oxygen480-status-security-low.svg/30px-Oxygen480-status-security-low.svg.png">
                                <i>{{__('appeals.verify.notableverified')}}</i>
                                <br /><b style="color:red">{{__('appeals.verify.negativeaction')}}</b>
                                @elseif(!$info->blocktype == 0)
                                <br/><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/8/87/Oxygen480-status-security-medium.svg/30px-Oxygen480-status-security-medium.svg.png">
                                <i>{{__('appeals.verify.not-verified')}}</i> 
                                <br /><b style="color:red">{{__('appeals.verify.negativeaction')}}</b>
                                @endif
                                <br/>{{__('appeals.appeal-types.title')}}: 
                                @if($info->blocktype==0)
                                    {{__('appeals.appeal-types.ip')}}
                                @elseif($info->blocktype==1)
                                    {{__('appeals.appeal-types.account')}}
                                @elseif($info->blocktype==2)
                                    {{__('appeals.appeal-types.ip-under')}}
                                @endif
                                @if($info->status=="INVALID")
                                <br/>{{__('appeals.details-status')}}: {{ __('appeals.status.'.$info->status) }} <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/f/f5/Octagon_delete.svg/20px-Octagon_delete.svg.png">
                                @else
                                <br/>{{__('appeals.details-status')}}: {{ __('appeals.status.'.$info->status) }}
                                @endif
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

                                @component('components.user-action-buttons', ['target' => $urlname, 'baseUrl' => \App\Services\Facades\MediaWikiRepository::getTargetProperty($info->wiki, 'url_base'), 'canUnblock' => $perms['admin']])
                                @endcomponent

                                <br /><br />
                                <h5 class="card-title">{{__('appeals.section-headers.content')}}</h5>
                                <b>{{__('appeals.content-question-why')}}</b>
                                <p>{{ $info->appealtext }}</p>
                                @if(in_array(0,$translateIDs))
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/3/35/Oxygen15.04.1-applications-education-language.svg/30px-Oxygen15.04.1-applications-education-language.svg.png" />&nbsp;&nbsp;{{__('generic.translated-by-deepl')}}
                                @else
                                <a href="{{ route('translate.activate', ['appeal' => $info, 'logid' => 0])}}">
                                    <div style="height: 55px; display: flex; align-items: center;">
                                        <div style="font-size: 20px; font-family: 'Courier New', monospace; text-align: center;">{{__('generic.translate-with')}}</div>&nbsp;&nbsp;&nbsp;<img src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/ed/DeepL_logo.svg/105px-DeepL_logo.svg.png" />
                                    </div>
                                </a>
                                @endif

                                @can('update', $info)
                                    <br /><br />
                                    
                                    <br /><br />
                                        <h5 class="card-title">{{__('appeals.comments.leave')}}</h5>
                                        {{ html()->form('POST', route('appeal.action.comment', $info))->open() }}
                                            {{ html()->token() }}

                                            <div class="form-group">
                                                {{ html()->label(__('appeals.comments.add'), 'comment') }}
                                                {{ html()->textarea('comment', old('comment'))->class('form-control') }}
                                            </div>

                                            {{ html()->submit(__('appeals.cu.submit'))->class('btn btn-success') }}
                                        {{ html()->form()->close() }}
                                @endcan
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-1"></div>
                                <div class="col-md-11">
                                    <h5 class="card-title">{{__('appeals.section-headers.actions')}}</h5>
                                    @can('update', $info)
                                        @if($info->status === Appeal::STATUS_ACCEPT || $info->status === Appeal::STATUS_DECLINE || $info->status === Appeal::STATUS_EXPIRE || $info->status === Appeal::STATUS_INVALID)
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
                                                    @if($perms['developer'] || $perms['oversight'] || $perms['steward'])
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
                                        
                                            <button class="btn btn-info" onclick="displayTransfer()" id="transferbutton">{{__('appeals.links.transfer')}}</button>

                                            <div style="display: none;" id="transfer">
                                                <br />
                                                {{ html()->form('POST', route('appeal.transfer', [$info]))->open() }}
                                                    {{ html()->token() }}
                                                    <div class="form-group">
                                                        <div class="card">
                                                            <div class="card-header">
                                                                {{__('appeals.links.transfer')}}
                                                            </div>
                                                            <div class="card-body">
                                                                <p class="card-text">
                                                        {{ html()->label(__('appeals.links.transfer-to').':', 'wiki') }}<br />
                                                        {{ html()->select('wiki', $wikis, ['class' => 'form-control']) }}
                                                        <br /><br />
                                                        {{ html()->submit(__('appeals.cu.submit'))->class('btn btn-success') }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                {{ html()->form()->close() }}
                                            </div>
                                            @if($info->handlingadmin != null && $info->handlingadmin == Auth::id())
                                                <a href="{{ route('appeal.template', $info) }}" class="btn btn-info">
                                                    {{__('appeals.send-reply-button')}}
                                                </a>
                                            @else
                                                <div class="alert alert-danger" role="alert">
                                                    {{__('appeals.not-handling-admin')}}
                                                </div>
                                            @endif

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
                                                    {{__('appeals.cu.ip-address',['ip'=>$cudata->ipaddress])}}<br/>
                                                    @component('components.user-action-buttons', ['target' => $cudata->ipaddress, 'baseUrl' => \App\Services\Facades\MediaWikiRepository::getTargetProperty($info->wiki, 'url_base'), 'canUnblock' => $perms['admin']])
                                                    @endcomponent
                                                    <br/>
                                                    {{__('appeals.cu.user-agent',['ua'=>$cudata->useragent])}}<br/>
                                                    {{__('appeals.cu.browser-lang',['lang'=>$cudata->language])}}
                                                @elseif(is_null($cudata))
                                                    <div class="alert alert-danger" role="alert">
                                                        {{__('appeals.cu.data-expire')}}
                                                    </div>
                                                @else
                                                    <div class="alert alert-danger" role="alert">
                                                        {{__('appeals.cu.no-request')}}
                                                    </div>
                                                    {{ html()->form('POST', route('appeal.action.viewcheckuser', $info))->open() }}
                                                        {{ html()->token() }}

                                                        <div class="form-group">
                                                            {{ html()->label(__('appeals.cu.reason'), 'reason') }}
                                                            {{ html()->textarea('reason', old('reason'))->class('form-control')->rows(3) }}
                                                        </div>

                                                        {{ html()->submit(__('appeals.cu.submit'))->class('btn btn-success') }}
                                                    {{ html()->form()->close() }}
                                                @endif
                                            @endif
                                            <br /><br />
                                            <h5 class="card-title">{{__('appeals.section-headers.status')}}</h5>
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
                                            {{--
                                            @if($previousAppeals->isNotEmpty())
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
                                            @endif
                                            --}}
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
        <br />
        <div class="card my-2">
            <h4 class="card-header">{{__('appeals.section-headers.comments')}}</h4>
            <div class="card-body">
                <table class="table table-dark" id="comments">
                <thead>
                <tr>
                    <th scope="col">{{ __('generic.logs-user') }}</th>
                    <th scope="col">{{ __('generic.logs-time') }}</th>
                    <th scope="col">{{ __('generic.logs-action') }}</th>
                    <th scope="col">{{ __('generic.translate-link') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($comments as $comment)
                    @if($comment->action === 'translate')
                        <!-- Skip translation log entries -->
                    @else
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
                                    <td><i>{{__('appeals.comments.restricted')}}</i></td>
                                @else
                                    <td>{{ $comment->reason }}</td>
                                @endif
                            @endif
                            <td>
                                @if($comment->action === 'responded' && $comment->reason !== null && $comment->user_id === -1)
                                    @if(in_array($comment->id,$translateIDs))
                                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/3/35/Oxygen15.04.1-applications-education-language.svg/30px-Oxygen15.04.1-applications-education-language.svg.png" />&nbsp;&nbsp;{{__('generic.translated-by-deepl')}}
                                    @else
                                    <a href="{{ route('translate.activate', ['appeal' => $info->id, 'logid' => $comment->id])}}" style="color: white">
                                        {{__('generic.translate-with')}} &nbsp;<img src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/ed/DeepL_logo.svg/105px-DeepL_logo.svg.png" />
                                    </a>
                                    @endif
                                @endif
                            </td>
                        @endif
                        </tr>
                    @endif
                @endforeach
                </tbody>
            </table>
                <i>{{__('appeals.comment-color-text')}}</i>
                <br/>
                <br/>
                
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
                {{ html()->form('POST', route('appeal.action.requestcheckuser', $info))->open() }}
                {{ html()->token() }}
                <div class="modal-body">

                    <div class="form-group mb-4">
                        {{ html()->label(__('appeals.cu.review-req'), 'cu_reason') }}
                        {{ html()->input('text', 'cu_reason', old('cu_reason'), ['class' => 'form-control']) }}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('appeal.links.cancel')}}</button>
                    {{ html()->submit(__('appeals.cu.submit'))->class('btn btn-primary') }}
                </div>
                {{ html()->form()->close() }}
            </div>
        </div>
    </div>
@endsection

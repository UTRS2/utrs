@extends('layouts.app')
@php use App\Appeal; @endphp

@section('title', 'Your Appeal')
@section('content')
    @if(in_array($appeal->status, [Appeal::STATUS_NOTFOUND, Appeal::STATUS_EXPIRE, Appeal::STATUS_ACCEPT, Appeal::STATUS_DECLINE]))
        <div class="alert alert-danger" role="alert">{{ __('appeals.closed-notice') }}</div>
    @endif
    @if($appeal->status === "NOTFOUND")
        <div class="alert alert-danger" role="alert">
            {!! str_replace(':link', '<a href="' . route('public.appeal.modify', $appeal->appealsecretkey) .'" class="alert-link">' . htmlspecialchars(__('appeals.not-found-link-text')) . '</a>', htmlspecialchars(__('appeals.not-found-text'))) !!}
        </div>
    @endif
    <div class="card mb-4 mt-4">
        <h5 class="card-header">{{ __('appeals.section-headers.details') }}</h5>
        <div class="card-body">
            <h4>{{ __('appeals.appeal-title', ['name' => $appeal->appealfor]) }}</h4>
            <div class="mt-4">
                <table class="table">
                    <tbody>
                    <tr>
                        <th>{{ __('appeals.details-status') }}</th>
                        <td>{{ $appeal->status }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('appeals.details-block-admin') }}</th>
                        <td>{{ $appeal->blockingadmin }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('appeals.details-block-reason') }}</th>
                        <td>{!! $appeal->getFormattedBlockReason() !!}</td>
                    </tr>
                    <tr>
                        <th>{{ __('appeals.details-submitted') }}</th>
                        <td>{{ $appeal->submitted }}</td>
                    </tr>
                    @if($appeal->handlingAdminObject)
                        <tr>
                            <th>{{ __('appeals.details-handling-admin') }}</th>
                            <td>{{ $appeal->handlingAdminObject->username }}</td>
                        </tr>
                    @endif
                    </tbody>
                </table>

                @if($appeal->status == "NOTFOUND")
                    <a href="{{ route('public.appeal.modify', $appeal->appealsecretkey) }}"
                       class="btn btn-success">
                        {{ __('appeals.not-found-button') }}
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <h5 class="card-header">{{ __('appeals.section-headers.content') }}</h5>
        <div class="card-body">
            <div class="row">
                <div class="col-md-9">
                    <b>{{ __('appeals.content-question-why') }}?</b>
                    <p>{{ $appeal->appealtext }}</p>
                </div>
                <div class="col-md-3">
                    @if($appeal->status === "ACCEPT")
                        <center>{{ __('appeals.status-texts.ACCEPT') }}<br/>
                            <br/><img
                                    src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/ee/Emblem-unblock-granted.svg/200px-Emblem-unblock-granted.svg.png"
                                    class="img-fluid"></center>
                    @elseif($appeal->status === "EXPIRE")
                        <center>{{ __('appeals.status-texts.EXPIRE') }}<br/>
                            <br/><img
                                    src="https://upload.wikimedia.org/wikipedia/commons/thumb/3/34/Emblem-unblock-expired.svg/200px-Emblem-unblock-expired.svg.png"
                                    class="img-fluid"></center>
                    @elseif($appeal->status === "DECLINE")
                        <center>{{ __('appeals.status-texts.DECLINE') }}<br/>
                            <br/><img
                                    src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/59/Emblem-unblock-denied.svg/200px-Emblem-unblock-denied.svg.png"
                                    class="img-fluid"></center>
                    @elseif($appeal->status === "INVALID")
                        <center>{{ __('appeals.status-texts.INVALID') }}<br/>
                            <br/><img
                                    src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/59/Emblem-unblock-denied.svg/200px-Emblem-unblock-denied.svg.png"
                                    class="img-fluid"></center>
                    @else
                        <center>{{ __('appeals.status-texts.default') }}<br/>
                            <br/><img
                                    src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/ec/Emblem-unblock-request.svg/200px-Emblem-unblock-request.svg.png"
                                    class="img-fluid"></center>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <h5 class="card-header">{{ __('appeals.section-headers.comments') }}</h5>
        <div class="card-body">
            <table class="table table-dark">
                <thead>
                <tr>
                    <th scope="col">{{ __('generic.logs-user') }}</th>
                    <th scope="col">{{ __('generic.logs-user') }}</th>
                    <th scope="col">{{ __('generic.logs-action') }}</th>
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
                                    <td><i>{{ __('generic.logs-system') }}</i></td>
                                @elseif($comment->user === -1)
                                    <td><i>{{ $appeal->appealfor }}</i></td>
                                @else
                                    <td><i>{{ $comment->userObject->username }}</i></td>
                                @endif
                                <td><i>{{ $comment->timestamp }}</i></td>
                                @if($comment->protected)
                                    <td><i>{{ __('generic.logs-private') }}</i></td>
                                @else
                                    @if($comment->comment!==null)
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
                                    <td><i>{{ __('generic.logs-system') }}</i></td>
                                @elseif($comment->user === -1)
                                    <td><i>{{ $appeal->appealfor }}</i></td>
                                @else
                                    <td><i>{{ $comment->userObject->username }}</i></td>
                                @endif
                                <td>{{ $comment->timestamp }}</td>
                                @if($comment->protected || $comment->action=="comment")
                                    <td><i>{{ __('generic.logs-private') }}</i></td>
                                @else
                                    @if($comment->comment!==null)
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
            <i>{{ __('appeals.comment-color-text') }}</i>
        </div>
    </div>

    <div class="card mb-4">
        <h5 class="card-header">{{ __('appeals.section-headers.add-comment') }}</h5>
        <div class="card-body">
            @if(!in_array($appeal->status, [Appeal::STATUS_NOTFOUND, Appeal::STATUS_EXPIRE, Appeal::STATUS_ACCEPT, Appeal::STATUS_DECLINE, Appeal::STATUS_INVALID]))
                {{ Form::open(array('url' => route('public.appeal.comment'))) }}
                {{ Form::hidden('appealsecretkey', $appeal->appealsecretkey) }}
                <div class="form-group">
                    {{ Form::label('comment', __('appeals.comment-input-text')) }}
                    {{ Form::textarea('comment', old('comment'), ['rows' => 4, 'class' => 'form-control']) }}
                </div>
                <button type="submit" class="btn btn-success">{{ __('generic.submit') }}</button>
                {{ Form::close() }}
            @else
                <div class="alert alert-danger" role="alert">{{ __('appeals.closed-notice') }}</div>
            @endif
        </div>
    </div>
@endsection

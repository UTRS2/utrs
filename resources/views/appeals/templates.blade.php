@extends('layouts.app')

@section('title', 'Templates')
@section('content')
    <div class="alert alert-info" role="alert">
        {{__('appeals.templates.alert')}}
    </div>
    <div class="mt-2 mb-4">
        <a href="/appeal/{{ $appeal->id }}" class="btn btn-danger">{{__('appeals.template.return-appeal')}}</a>
        <a href="{{ route('appeals.customresponse', $appeal) }}" class="btn btn-info">{{__('appeals.template.reply-custom')}}</a>
    </div>

    @foreach($templates as $template)
        <div class="card mt-2 mb-2">
            <h6 class="card-header">
                <button class="p-0 btn btn-link" data-toggle="collapse" data-target="#contents-{{ $template->id }}">
                    {{ $template->name }}
                </button>
            </h6>

            <div class="card-body collapse" id="contents-{{ $template->id }}"> {{-- for purgecss: show hr --}}
                {{ __('appeals.template.greeting',[$appeal->appealfor]) }},

                <p class="mt-2">
                    {{ $template->template }}
                </p>

                {{ $username }}<br/>
                {{ \App\Services\Facades\MediaWikiRepository::getTargetProperty($appeal->wiki, 'responding_user_title') }}

                <hr/>

                {{ html()->form('POST', route('appeal.template.submit', [$appeal, $template]))->open() }}
                <div class="form-group">
                    {{ html()->label('Change appeal status to:', "status-" . $template->id) }}
                    {{ html()->select('status', $appeal->getValidStatusChanges(), old('status', $template->default_status))->class('form-control')->id("status-" . $template->id) }}
                </div>

                <button type="submit" class="btn btn-success">{{__('appeals.cu.submit')}}</button>
                {{ html()->form()->close() }}
            </div>
        </div>
    @endforeach
@endsection

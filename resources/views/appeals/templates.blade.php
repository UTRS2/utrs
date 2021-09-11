@extends('layouts.app')

@section('title', 'Templates')
@section('content')
    <div class="alert alert-info" role="alert">
        On this screen, you will see a list of templates to choose from in responding to a user. To use a template,
        click it's name.
    </div>
    <div class="mt-2 mb-4">
        <a href="/appeal/{{ $appeal->id }}" class="btn btn-danger">Return to appeal</a>
        <a href="{{ route('appeal.customresponse', $appeal) }}" class="btn btn-info">Reply custom text</a>
    </div>

    @foreach($templates as $template)
        <div class="card mt-2 mb-2">
            <h6 class="card-header">
                <button class="p-0 btn btn-link" data-toggle="collapse" data-target="#contents-{{ $template->id }}">
                    {{ $template->name }}
                </button>
            </h6>

            <div class="card-body collapse" id="contents-{{ $template->id }}"> {{-- for purgecss: show hr --}}
                Hello {{ $appeal->appealfor }},

                <p class="mt-2">
                    {{ $template->template }}
                </p>

                {{ $username }}<br/>
                {{ \App\Services\Facades\MediaWikiRepository::getTargetProperty($appeal->wiki, 'responding_user_title') }}

                <hr/>

                {{ Form::open(['url' => route('appeal.template.submit', [$appeal, $template])]) }}
                <div class="form-group">
                    {{ Form::label("status-" . $template->id, 'Change appeal status to:') }}
                    {{ Form::select('status', $appeal->getValidStatusChanges(), old('status', $template->default_status), ['class' => 'form-control', 'id' => "status-" . $template->id]) }}
                </div>

                <button type="submit" class="btn btn-success">Submit</button>
                {{ Form::close() }}
            </div>
        </div>
    @endforeach
@endsection

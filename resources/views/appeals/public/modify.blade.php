@extends('layouts.app')

@section('title', 'Modify appeal')
@section('content')

    <div class="alert alert-danger" role="alert">
        You are now modifying your appeal to be resubmitted. Please ensure the information is correct.
    </div>
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

    {{ Form::open(['url' => route('public.appeal.modify.submit')]) }}
    {{ Form::token() }}
    <div class="form-group mb-4">
        {{ Form::label('wiki', 'Which Wiki are you blocked on?') }}<br>
        {{ Form::select('wiki', \App\MwApi\MwApiUrls::getWikiDropdown(), old('wiki', $appeal->wiki), ['class' => 'custom-select']) }}
    </div>

    <div class="form-group mb-4">
        {{ Form::label('appealfor', 'What is your Username?') }}
        {{ Form::text('appealfor', old('appealfor', $appeal->appealfor), ['class' => 'form-control']) }}
    </div>

    <div class="form-group mb-4">
        Is your account directly blocked?
        <div class="custom-control custom-radio">
            {{ Form::radio('blocktype', 1, old('blocktype', $appeal->blocktype) === 1, ['class' => 'custom-control-input', 'id' => 'blocktype-1']) }} {{ Form::label('blocktype-1', 'Yes', ['class' => 'custom-control-label']) }}
        </div>

        <div class="custom-control custom-radio">
            {{ Form::radio('blocktype', 0, old('blocktype', $appeal->blocktype) === 0, ['class' => 'custom-control-input', 'id' => 'blocktype-0']) }} {{ Form::label('blocktype-0', 'No, I do not have an account', ['class' => 'custom-control-label']) }}
        </div>

        <div class="custom-control custom-radio">
            {{ Form::radio('blocktype', 2, old('blocktype', $appeal->blocktype) === 2, ['class' => 'custom-control-input', 'id' => 'blocktype-2']) }} {{ Form::label('blocktype-2', 'No, the underlying IP address is blocked', ['class' => 'custom-control-label']) }}
        </div>
    </div>

    <div class="form-group mb-4">
        {{ Form::label('hiddenip', 'If you selected "No, the underlying IP address is blocked" above, what is the IP?') }}
        {{ Form::text('hiddenip', old('hiddenip', $appeal->hiddenip), ['class' => 'form-control']) }}
    </div>

    {{ Form::hidden('hash', $hash) }}
    <button type="submit" class="btn btn-success">Submit</button>
    {{ Form::close() }}
@endsection

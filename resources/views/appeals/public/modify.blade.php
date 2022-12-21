@extends('layouts.app')

@section('title', htmlspecialchars(__('appeals.forms.header-modify')))
@section('content')

    <div class="alert alert-info" role="alert">
        {{ __('appeals.forms.edit-notice') }}
    </div>

    @component('components.errors')
    @endcomponent

    <div class="card">
        <h5 class="card-header">
            {{ __('appeals.forms.header-modify') }}
        </h5>
        <div class="card-body">
            {{ Form::open(['url' => route('public.appeal.modify.submit')]) }}
            {{ Form::token() }}
            <div class="mb-4">
                {{ Form::label('wiki_id', __('appeals.forms.block-wiki'), ['class' => 'form-label']) }}<br>
                {{ Form::select('wiki_id', $wikis, old('wiki_id'), ['class' => 'form-select']) }}
            </div>

            <div class="mb-4">
                {{ Form::label('appealfor', __($appeal->blocktype === 0 ? 'appeals.forms.block-ip' : 'appeals.forms.block-username'), ['class' => 'form-label']) }}
                {{ Form::text('appealfor', old('appealfor', $appeal->appealfor), ['class' => 'form-control']) }}
            </div>

            <div class="mb-4">
                {{ __('appeals.forms.direct-question') }}
                <div class="form-check">
                    {{ Form::radio('blocktype', 1, old('blocktype', $appeal->blocktype) === 1, ['class' => 'form-check-input', 'id' => 'blocktype-1']) }} {{ Form::label('blocktype-1', __('appeals.forms.direct-yes'), ['class' => 'form-check-label']) }}
                </div>

                <div class="form-check">
                    {{ Form::radio('blocktype', 0, old('blocktype', $appeal->blocktype) === 0, ['class' => 'form-check-input', 'id' => 'blocktype-0']) }} {{ Form::label('blocktype-0', __('appeals.forms.direct-ip'), ['class' => 'form-check-label']) }}
                </div>

                <div class="form-check">
                    {{ Form::radio('blocktype', 2, old('blocktype', $appeal->blocktype) === 2, ['class' => 'form-check-input', 'id' => 'blocktype-2']) }} {{ Form::label('blocktype-2', __('appeals.forms.direct-no'), ['class' => 'form-check-label']) }}
                </div>
            </div>

            <div class="mb-4">
                {{ Form::label('hiddenip', __('appeals.forms.hiddenip-question', ['option' => __('appeals.forms.direct-no')]), ['class' => 'form-label']) }}
                {{ Form::text('hiddenip', old('hiddenip', $appeal->hiddenip), ['class' => 'form-control']) }}
            </div>

            {{ Form::hidden('hash', $hash) }}
            <button type="submit" class="btn btn-success">{{ __('generic.submit') }}</button>
            {{ Form::close() }}

        </div>
    </div>
@endsection

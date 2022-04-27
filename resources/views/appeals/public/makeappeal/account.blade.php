@extends('layouts.app')

@section('title', 'Appeal a block on an account ')
@section('content')
    <div class="alert alert-warning" role="alert">
        {{ __('appeals.publicheader.appealkey') }}
    </div>
    <div class="alert alert-info" role="alert">
        {{ __('appeals.publicheader.afterfile') }}
    </div>
    <div class="card">
        <h5 class="card-header">
            {{ __('appeals.forms.header-account') }}
        </h5>
        <div class="card-body">
            @component('components.errors')
            @endcomponent

            {{ Form::open(['url' => route('public.appeal.store')]) }}
            {{ Form::token() }}
            <h5>{{ __('appeals.forms.about-you') }}</h5>
            <div class="form-group mb-4">
                {{ Form::label('wiki_id', __('appeals.forms.block-wiki')) }}<br>
                {{ Form::select('wiki_id', $wikis, old('wiki_id'), ['class' => 'custom-select']) }}
            </div>
            <div class="form-group mb-4">
                {{ Form::label('appealfor', __('appeals.forms.block-username')) }}
                {{ Form::text('appealfor', old('appealfor'), ['class' => 'form-control']) }}
            </div>
            <div class="form-group mb-4">
                {{ __('appeals.forms.direct-question') }}
                <div class="custom-control custom-radio">
                    {{ Form::radio('blocktype', 1, old('blocktype') === 1, ['class' => 'custom-control-input', 'id' => 'blocktype-1']) }} {{ Form::label('blocktype-1', __('appeals.forms.direct-yes'), ['class' => 'custom-control-label']) }}
                </div>

                <div class="custom-control custom-radio">
                    {{ Form::radio('blocktype', 2, old('blocktype') === 2, ['class' => 'custom-control-input', 'id' => 'blocktype-2']) }} {{ Form::label('blocktype-2', __('appeals.forms.direct-no'), ['class' => 'custom-control-label']) }}
                </div>
            </div>

            <div class="form-group mb-4">
                {{ Form::label('hiddenip', __('appeals.forms.hiddenip-question', ['option' => __('appeals.forms.direct-no')])) }}
                {{ Form::text('hiddenip', old('hiddenip'), ['class' => 'form-control']) }}
            </div>

            <h5>{{ __('appeals.forms.appeal-info') }}</h5>

            <div class="alert alert-warning" role="alert">
                {{ __('appeals.forms.admin-only-notice') }}
                <br/>{{ __('appeals.forms.word-notice') }}
            </div>

            <div class="form-group mb-4">
                {{ Form::label('appealtext', __('appeals.forms.question-why')) }}
                {{ Form::textarea('appealtext', old('appealtext'), ['class' => 'form-control h-25']) }}
            </div>

            {{ Form::submit(__('generic.submit'), ['class' => 'btn btn-success']) }}
            {{ Form::close() }}
        </div>
    </div>
@endsection

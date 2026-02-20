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
            {{ html()->form('POST', route('public.appeal.modify.submit'))->open() }}
            {{ html()->token() }}
            <div class="form-group mb-4">
                {{ html()->label(__('appeals.forms.block-wiki'), 'wiki_id') }}<br>
                {{ html()->select('wiki_id', $wikis, old('wiki_id'))->class('custom-select') }}
            </div>

            <div class="form-group mb-4">
                {{ html()->label(__($appeal->blocktype === 0 ? 'appeals.forms.block-ip' : 'appeals.forms.block-username'), 'appealfor') }}
                {{ html()->text('appealfor', old('appealfor', $appeal->appealfor))->class('form-control') }}
            </div>

            <div class="form-group mb-4">
                {{ __('appeals.forms.direct-question') }}
                <div class="custom-control custom-radio">
                    {{ html()->radio('blocktype', old('blocktype', $appeal->blocktype) === 1, 1)->class('custom-control-input')->id('blocktype-1') }} {{ html()->label(__('appeals.forms.direct-yes'), 'blocktype-1')->class('custom-control-label') }}
                </div>

                <div class="custom-control custom-radio">
                    {{ html()->radio('blocktype', old('blocktype', $appeal->blocktype) === 0, 0)->class('custom-control-input')->id('blocktype-0') }} {{ html()->label(__('appeals.forms.direct-ip'), 'blocktype-0')->class('custom-control-label') }}
                </div>

                <div class="custom-control custom-radio">
                    {{ html()->radio('blocktype', old('blocktype', $appeal->blocktype) === 2, 2)->class('custom-control-input')->id('blocktype-2') }} {{ html()->label(__('appeals.forms.direct-no'), 'blocktype-2')->class('custom-control-label') }}
                </div>
            </div>

            <div class="form-group mb-4">
                {{ html()->label(__('appeals.forms.hiddenip-question', ['option' => __('appeals.forms.direct-no')]), 'hiddenip') }}
                {{ html()->text('hiddenip', old('hiddenip', $appeal->hiddenip))->class('form-control') }}
            </div>

            {{ html()->hidden('appealkey', $appealkey) }}
            <button type="submit" class="btn btn-success">{{ __('generic.submit') }}</button>
            {{ html()->form()->close() }}

        </div>
    </div>
@endsection

@extends('layouts.app')

@section('title', 'Appeal a block on an IP address')
@section('content')
    <div class="alert alert-danger" role="alert">
        On the next page, you will be issued a Appeal Key. Keep it in a safe place. If you forget it, you WILL NOT
        able to recover it. DO NOT SHARE this key with anyone.
    </div>
    <div class="alert alert-info" role="alert">
        After filing this appeal, you will get an appeal key. For security reasons, we do not send email updates for
        IP blocks. You will have to check back here for updates.

        An administrator will look at your request in due time. Depending on which language and site you are
        appealing from, appeal times may vary excessively.

        Please note, any text you input for your appeal you agree to release under a <a
                href="https://en.wikipedia.org/wiki/Public_domain">public domain licence</a> so that it can be
        copied over to Wikipedia if needed. If you do not agree, do not file an appeal.

        If you have any questions, you can contact us. Please note: We will not expedite, approve, deny, or edit
        your appeal. It is for information only.
    </div>
    <div class="card">
        <h5 class="card-header">
            {{ __('appeals.forms.header-ip') }}
        </h5>
        <div class="card-body">
            @component('components.errors')
            @endcomponent

            {{ Form::open(['url' => route('public.appeal.store')]) }}
            {{ Form::token() }}

            <h5>About you</h5>
            <div class="form-group mb-4">
                {{ Form::label('wiki_id', __('appeals.forms.block-wiki')) }}<br>
                {{ Form::select('wiki_id', $wikis, old('wiki_id'), ['class' => 'custom-select']) }}
            </div>
            {{ Form::hidden('blocktype', 0) }}

            <div class="form-group mb-4">
                {{ Form::label('appealfor', __('appeals.forms.block-ip')) }}
                {{ Form::text('appealfor', old('appealfor'), ['class' => 'form-control']) }}
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

@extends('layouts.app')

@section('title', 'Appeal a block on an IP address')
@section('scripts')
        function getIP() {
            fetch("{{ route('apikey.ip') }}")
                .then(response => {
                    if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(text => {
            let ip;
            try {
                const parsed = JSON.parse(text);
                ip = parsed && parsed.ip ? parsed.ip : text;
            } catch (e) {
                ip = text.trim();
            }
            if (!ip) {
                throw new Error('Empty IP returned');
            }
            
            console.log('Retrieved IP:', ip);

            const appealElement = document.getElementById("appealfor");
            const hiddenElement = document.getElementById("forhidden");

            if (appealElement) {
                appealElement.value = ip;
                appealElement.style.visibility = "hidden";
                appealElement.style.display = "none";
            }
            if (hiddenElement) {
                hiddenElement.style.display = "block";
            }
        })
        .catch(err => {
            console.error('Failed to get IP:', err);
            alert('Could not retrieve IP address automatically. Please enter it manually.');
        });
}
@endsection
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

            {{ html()->form('POST', route('public.appeal.store'))->open() }}
            {{ html()->token() }}

            <h5>About you</h5>
            <div class="form-group mb-4">
                {{ html()->label(__('appeals.forms.block-wiki'), 'wiki_id') }}<br>
                {{ html()->select('wiki_id', $wikis, old('wiki_id'))->class('custom-select') }}
            </div>
            {{ html()->hidden('blocktype', 0) }}

            <div class="form-group mb-4">
                {{ html()->label(__('appeals.forms.block-ip'), 'appealfor') }}
                {{ html()->text('appealfor', old('appealfor'))->class('form-control') }}
                <noscript>
                    <div class="alert alert-warning" role="alert">The following button will not work as you don't have javascript enabled.</div>
                </noscript>
                <br /><div style="display: none" class="alert alert-success" role="alert" id="forhidden">This question has been answered automatically.</div>
                <br /><button type="button" class="btn btn-info" onclick="getIP()">Don't know your IP? Get IP address automatically.</button>
            </div>

            {{--<div class="form-group mb-4">
                {{ html()->label(__('appeals.forms.email'), 'email') }}
                {{ html()->text('email', old('email'))->class('form-control') }}
            </div>--}}


            <h5>{{ __('appeals.forms.appeal-info') }}</h5>

            <div class="alert alert-warning" role="alert">
                {{ __('appeals.forms.admin-only-notice') }}
                <br/>{{ __('appeals.forms.word-notice') }}
            </div>

            
            <div class="form-group mb-4">
                {{ html()->label(__('appeals.forms.question-why'), 'appealtext') }}
                {{ html()->textarea('appealtext', old('appealtext'))->class('form-control')->attribute('style','height:180px;resize:vertical;') }}
            </div>

            {{ html()->submit(__('generic.submit'))->class('btn btn-success') }}
            {{ html()->form()->close() }}
        </div>
    </div>
@endsection

@extends('layouts.app')
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

                const hiddenIpEl = document.getElementById("hiddenip");
                const hiddenLabel = document.querySelector('label[for="hiddenip"]');

                if (hiddenIpEl) {
                hiddenIpEl.value = ip;
                // hide the visible input now that we have the IP automatically
                hiddenIpEl.style.display = 'none';
                }
                if (hiddenLabel) {
                hiddenLabel.style.display = 'none';
                }

                showHiddenIP();
            })
            .catch(err => {
                console.error('Failed to get IP:', err);
                alert('Could not retrieve IP address automatically. Please enter it manually.');
            });
        }
        function showHiddenIP() {
            const hiddenDiv = document.getElementById("hiddenipdiv");
            const forHidden = document.getElementById("forhidden");
            const hiddenIpEl = document.getElementById("hiddenip");
            const hiddenLabel = document.querySelector('label[for="hiddenip"]');

            if (hiddenDiv) {
            hiddenDiv.style.display = "block";
            }

            // If we already have a value, hide the input and show the "answered automatically" message.
            if (hiddenIpEl && hiddenIpEl.value && hiddenIpEl.value.trim() !== "") {
            if (hiddenIpEl) hiddenIpEl.style.display = 'none';
            if (hiddenLabel) hiddenLabel.style.display = 'none';
            if (forHidden) forHidden.style.display = "block";
            } else {
            // otherwise show the input so the user can type it
            if (hiddenIpEl) hiddenIpEl.style.display = 'block';
            if (hiddenLabel) hiddenLabel.style.display = 'block';
            if (forHidden) forHidden.style.display = 'none';
            }
        }
        function hideHiddenIP() {
            const hiddenDiv = document.getElementById("hiddenipdiv");
            const forHidden = document.getElementById("forhidden");
            const hiddenIpEl = document.getElementById("hiddenip");
            const hiddenLabel = document.querySelector('label[for="hiddenip"]');

            if (hiddenDiv) {
            hiddenDiv.style.display = "none";
            }
            if (forHidden) {
            forHidden.style.display = "none";
            }
            // when hiding because the user selected the direct option, ensure the input + label are visible again
            if (hiddenIpEl) hiddenIpEl.style.display = 'block';
            if (hiddenLabel) hiddenLabel.style.display = 'block';
        }
@endsection
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

            {{ html()->form('POST', route('public.appeal.store'))->open() }}
            {{ html()->token() }}
            <h5>{{ __('appeals.forms.about-you') }}</h5>
            <div class="form-group mb-4">
                {{ html()->label(__('appeals.forms.block-wiki'), 'wiki_id') }}<br>
                {{ html()->select('wiki_id', $wikis, old('wiki_id'))->class('custom-select') }}
            </div>
            <div class="form-group mb-4">
                {{ html()->label(__('appeals.forms.block-username'), 'appealfor') }}
                {{ html()->text('appealfor', old('appealfor'))->class('form-control') }}
            </div>
            <div class="form-group mb-4">
                {{ __('appeals.forms.direct-question') }}
                <div class="custom-control custom-radio">
                    {{ html()->radio('blocktype', (int)old('blocktype', 1) === 1, 1)->class('custom-control-input')->id('blocktype-1')->attribute('onclick', 'hideHiddenIP()') }} {{ html()->label(__('appeals.forms.direct-yes'), 'blocktype-1')->class('custom-control-label') }}
                </div>

                <div class="custom-control custom-radio">
                    {{ html()->radio('blocktype', (int)old('blocktype', 1) === 2, 2)->class('custom-control-input')->id('blocktype-2')->attribute('onclick', 'showHiddenIP()') }} {{ html()->label(__('appeals.forms.direct-no'), 'blocktype-2')->class('custom-control-label') }}
                </div>
            </div>

            <noscript>
                <div class="form-group mb-4">
                {{ html()->label(__('appeals.forms.hiddenip-question', ['option' => __('appeals.forms.direct-no')]), 'hiddenip') }}
                {{ html()->text('hiddenip', old('hiddenip'))->class('form-control') }}
                </div>
            </noscript>
            <div class="form-group mb-4" id="hiddenipdiv" style="{{ (int)old('blocktype', 1) === 2 ? '' : 'display:none' }}">
                {{ html()->label(__('appeals.forms.hiddenip-question', ['option' => __('appeals.forms.direct-no')]), 'hiddenip') }}
                {{ html()->text('hiddenip', old('hiddenip'))->class('form-control') }}
                <br /><div style="display: none" class="alert alert-success" role="alert" id="forhidden">The underlying IP question has been answered automatically.</div>
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

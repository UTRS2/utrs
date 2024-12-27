@extends('layouts.app')
@section('scripts')
        function getIP() {
            const url = "https://api64.ipify.org/"
            const auth = confirm('The following button will connect to' + url + ' to obtain your IP address. Press OK if you agree to allow this.')
            if (!auth) {
                console.error("Authorization to check API rejected. Site will not connect.");
                return;
            }
            fetch(url).then(res => res.text()).then(data => document.getElementById("hiddenip").value=data);
            document.getElementById("hiddenip").style.visibility="hidden";
            document.getElementById("hiddenip").style.display="none";
            document.getElementById("forhidden").style.display="block";
        }
        function showHiddenIP() {
            if (document.getElementById("hiddenipdiv").style.display=="none") {
                document.getElementById("hiddenipdiv").style.display="block";
            }
        }
        function hideHiddenIP() {
            if (document.getElementById("hiddenipdiv").style.display=="block" || document.getElementById("hiddenipdiv").style.display=='') {
                document.getElementById("hiddenipdiv").style.display="none";
            }
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
                    {{ html()->radio('blocktype', old('blocktype') === 1, 1)->class('custom-control-input')->id('blocktype-1')->attribute('onclick', 'hideHiddenIP()') }} {{ html()->label(__('appeals.forms.direct-yes'), 'blocktype-1')->class('custom-control-label') }}
                </div>

                <div class="custom-control custom-radio">
                    {{ html()->radio('blocktype', old('blocktype') === 2, 2)->class('custom-control-input')->id('blocktype-2')->attribute('onclick', 'showHiddenIP()') }} {{ html()->label(__('appeals.forms.direct-no'), 'blocktype-2')->class('custom-control-label') }}
                </div>
            </div>

            <noscript>
                <div class="form-group mb-4" id="hiddenipdiv">
                {{ html()->label(__('appeals.forms.hiddenip-question', ['option' => __('appeals.forms.direct-no')]), 'hiddenip') }}
                {{ html()->text('hiddenip', old('hiddenip'))->class('form-control') }}
                </div>
            </noscript>
            <div class="form-group mb-4" id="hiddenipdiv" style = "display:none">
                {{ html()->label(__('appeals.forms.hiddenip-question', ['option' => __('appeals.forms.direct-no')]), 'hiddenip') }}
                {{ html()->text('hiddenip', old('hiddenip'))->class('form-control') }}
                <br /><div style="display: none" class="alert alert-success" role="alert" id="forhidden">This question has been answered automatically.</div>
                <br /><button type="button" class="btn btn-info" onclick="getIP()">Don't know your IP? Get IP address automatically.</button>
            </div>

            <div class="form-group mb-4">
                {{ html()->label(__('appeals.forms.email'), 'email') }}
                {{ html()->text('email', old('email'))->class('form-control') }}
            </div>

            <h5>{{ __('appeals.forms.appeal-info') }}</h5>

            <div class="alert alert-warning" role="alert">
                {{ __('appeals.forms.admin-only-notice') }}
                <br/>{{ __('appeals.forms.word-notice') }}
            </div>

            <div class="form-group mb-4">
                {{ html()->label(__('appeals.forms.question-why'), 'appealtext') }}
                {{ html()->textarea('appealtext', old('appealtext'))->class('form-control h-25') }}
            </div>

            {{ html()->submit(__('generic.submit'))->class('btn btn-success') }}
            {{ html()->form()->close() }}
        </div>
    </div>
@endsection

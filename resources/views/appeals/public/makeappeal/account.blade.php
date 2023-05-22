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
                    {{ Form::radio('blocktype', 1, old('blocktype') === 1, ['class' => 'custom-control-input', 'id' => 'blocktype-1','onclick'=>'hideHiddenIP()']) }} {{ Form::label('blocktype-1', __('appeals.forms.direct-yes'), ['class' => 'custom-control-label']) }}
                </div>

                <div class="custom-control custom-radio">
                    {{ Form::radio('blocktype', 2, old('blocktype') === 2, ['class' => 'custom-control-input', 'id' => 'blocktype-2','onclick'=>'showHiddenIP()']) }} {{ Form::label('blocktype-2', __('appeals.forms.direct-no'), ['class' => 'custom-control-label']) }}
                </div>
            </div>

            <noscript>
                <div class="form-group mb-4" id="hiddenipdiv">
                {{ Form::label('hiddenip', __('appeals.forms.hiddenip-question', ['option' => __('appeals.forms.direct-no')])) }}
                {{ Form::text('hiddenip', old('hiddenip'), ['class' => 'form-control']) }}
                </div>
            </noscript>
            <div class="form-group mb-4" id="hiddenipdiv" style = "display:none">
                {{ Form::label('hiddenip', __('appeals.forms.hiddenip-question', ['option' => __('appeals.forms.direct-no')])) }}
                {{ Form::text('hiddenip', old('hiddenip'), ['class' => 'form-control']) }}
                <br /><div style="display: none" class="alert alert-success" role="alert" id="forhidden">This question has been answered automatically.</div>
                <br /><button type="button" class="btn btn-info" onclick="getIP()">Don't know your IP? Get IP address automatically.</button>
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

            {{ Form::button(__('generic.submit'), ['class' => 'btn btn-success','type'=>'submit']) }}
            {{ Form::close() }}
        </div>
    </div>
@endsection

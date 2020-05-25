@extends('layouts.app')

@section('title', 'Appeal a block on an account ')
@section('content')
    <div class="col-md-1"></div>
    <div class="col-md-10">
        <div class="alert alert-warning" role="alert">
            On the next page, you will be issued a Appeal Key. Keep it in a safe place. If you forget it, you are able
            to recover it, but only if your Wikimedia Account has a valid email address. DO NOT SHARE this key with
            anyone.
        </div>
        <div class="alert alert-info" role="alert">
            After filing this appeal, you will get an appeal key. <s>Update emails will be sent via the Wikimedia
                interface.</s> If you do not have an email associated with your Wikimedia account, you will have to
            check back here for updates.

            An administrator will look at your request in due time. Depending on which language and site you are
            appealing from, appeal times may vary excessively.

            Please note, any text you input for your appeal you agree to release under a <a
                    href="https://en.wikipedia.org/wiki/Public_domain">public domain licence</a> so that it can be
            copied over to Wikipedia if needed. If you do not agree, do not file an appeal.

            If you have any questions, you can contact us. Please note: We will not expedite, approve, deny, or edit
            your appeal. It is for information only.
        </div>
        <div class="card">
            <div class="card-header">
                Appeal a block on an account
            </div>
            <div class="card-body">
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

                {{ Form::open(['url' => route('public.appeal.store')]) }}
                {{ Form::token() }}
                <h5>About you</h5>
                <div class="form-group mb-4">
                    {{ Form::label('wiki', 'Which Wiki are you blocked on?') }}<br>
                    {{ Form::select('wiki', \App\MwApi\MwApiUrls::getWikiDropdown(), old('wiki', 'enwiki'), ['class' => 'custom-select']) }}
                </div>
                <div class="form-group mb-4">
                    {{ Form::label('appealfor', 'What is your Username?') }}
                    {{ Form::text('appealfor', old('appealfor'), ['class' => 'form-control']) }}
                </div>
                <div class="form-group mb-4">
                    Is your account directly blocked?
                    <div class="custom-control custom-radio">
                        {{ Form::radio('blocktype', 1, old('blocktype') === 1, ['class' => 'custom-control-input', 'id' => 'blocktype-1']) }} {{ Form::label('blocktype-1', 'Yes', ['class' => 'custom-control-label']) }}
                    </div>

                    <div class="custom-control custom-radio">
                        {{ Form::radio('blocktype', 2, old('blocktype') === 2, ['class' => 'custom-control-input', 'id' => 'blocktype-2']) }} {{ Form::label('blocktype-2', 'No, the underlying IP address is blocked', ['class' => 'custom-control-label']) }}
                    </div>
                </div>

                <h5>Block appeal information</h5>

                <div class="alert alert-warning" role="alert">
                    Only administrators will be able to see your appeal.
                </div>

                <div class="alert alert-warning" role="alert">
                    There is a 4,000 word maximum in this textbox. If you go over it, you will be prevented from filing
                    an appeal.
                </div>

                <div class="form-group mb-4">
                    {{ Form::label('appealtext', 'Why should you be unblocked?') }}
                    {{ Form::textarea('appealtext', old('appealtext'), ['class' => 'form-control h-25']) }}
                </div>

                {{ Form::submit('Submit', ['class' => 'btn btn-success']) }}
                {{ Form::close() }}
            </div>
        </div>
    </div>

@endsection

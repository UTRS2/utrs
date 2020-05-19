@extends('layouts.app')

@section('title', 'Appeal a block on an IP address')
@section('content')
    <div class="col-md-1"></div>
    <div class="col-md-10">
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
        <div class="card" style="align-content: left">
            <div class="card-header">
                Appeal a block on an IP address
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
            <div class="card-body">
                {{ Form::open(['url' => 'appeal/account']) }}
                {{ Form::token() }}

                <h5>About you</h5>
                <div class="form-group mb-4">
                    {{ Form::label('wiki', 'Which Wiki are you blocked on?') }}<br>
                    {{ Form::select('wiki', \App\MwApi\MwApiUrls::getWikiDropdown(), old('wiki', 'enwiki'), ['class' => 'custom-select']) }}
                </div>
                {{ Form::hidden('blocktype', 0) }}

                <div class="form-group mb-4">
                    {{ Form::label('appealfor', 'What is the IP address that is blocked?') }}
                    {{ Form::text('appealfor', old('appealfor'), ['class' => 'form-control']) }}
                </div>

                <h5>Block appeal information</h5>

                <div class="alert alert-danger" role="alert">
                    Please note that your answer to the following question does not guarentee that your appeal will be
                    private. It will be reviewed by select users and a determination will be made about if the appeal
                    contains private data and needs to be hidden from public view. Any information you put in this
                    appeal may be posted publicly.
                </div>

                <div class="form-group mb-4">
                    Does your appeal contain private information?

                    <div class="custom-control custom-radio">
                        {{ Form::radio('privacyreview', 0, old('privacyreview') === 0, ['class' => 'custom-control-input', 'id' => 'privacyreview-0']) }} {{ Form::label('privacyreview-0', 'No', ['class' => 'custom-control-label']) }}
                    </div>

                    <div class="custom-control custom-radio">
                        {{ Form::radio('privacyreview', 1, old('privacyreview') === 1, ['class' => 'custom-control-input', 'id' => 'privacyreview-1']) }} {{ Form::label('privacyreview-1', 'No, but I prefer my appeal be private', ['class' => 'custom-control-label']) }}
                    </div>

                    <div class="custom-control custom-radio">
                        {{ Form::radio('privacyreview', 2, old('privacyreview') === 2, ['class' => 'custom-control-input', 'id' => 'privacyreview-2']) }} {{ Form::label('privacyreview-2', 'Yes, my appeal contains private data', ['class' => 'custom-control-label']) }}
                    </div>
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

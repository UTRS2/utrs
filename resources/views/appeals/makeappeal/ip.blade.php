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
            <div class="card-body">
                {{ Form::open(array('url' => 'appeal/ip')) }}
                {{ Form::token() }}
                <h5 class="card-title">About you</h5>
                {{ Form::label('wiki', 'Which Wiki are you blocked on?') }}<br>
                {{ Form::select('wiki', array('enwiki' => 'English Wikipedia','ptwiki' => 'Portuguese Wikipedia', 'global' => 'Global Locks/Blocks'), 'enwiki') }}
                <br>
                {{ Form::label('username', 'What is the IP address that is blocked?') }}<br>
                {{ Form::text('username') }}<br>
                {{ Form::hidden('blocktype', 0) }}
                <br>
                <h5 class="card-title">Block appeal information</h5>
                <div class="alert alert-warning" role="alert">
                    There is a 4,000 word maximum in this textbox. If you go over it, you will be prevented from filing
                    an appeal.
                </div>
                {{ Form::label('blocktype', 'Why should you be unblocked?') }}<br>
                {{ Form::textarea('appealtext') }}<br>
                <br>
                <button type="submit" class="btn btn-success">Submit</button>
                {{ Form::close() }}
            </div>
        </div>
    </div>

@endsection

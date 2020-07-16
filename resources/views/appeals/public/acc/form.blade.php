@extends('layouts.app')

@section('title', htmlspecialchars(__('appeals.acc.form.header')))
@section('content')
    <div class="card">
        <h5 class="card-header">
            {{ __('appeals.acc.form.header') }}
        </h5>
        <div class="card-body">
            <div class="mb-4">
                <p>
                    You are about to request a Wikipedia account. We will need a few bits of information in order to create
                    your account. However, please keep in mind that you do not need an account to read the encyclopedia or
                    look up information - that can be done by anyone with or without an account. The first thing we need is
                    a username, and secondly, a valid email address that we can send your password to (please don't use
                    temporary inboxes, or email aliasing, as this may cause your request to be rejected).  If you have not
                    yet done so, please review the <a href="https://en.wikipedia.org/wiki/Wikipedia:Username_policy">Username Policy</a>
                    before submitting a request.
                </p>

                <p>
                    Your request will be sent to the <a href="https://en.wikipedia.org/wiki/Wikipedia:Request_an_account">
                        Account Creation Assistance</a> tool and will be processed by highly trusted volunteers.
                </p>
            </div>

            @component('components.errors')
            @endcomponent

            <form action="{{ route('public.appeal.acc.submit') }}" method="POST">
                @csrf
                <input type="hidden" name="secret_key" value="{{ $appeal->appealsecretkey }}">

                <div class="form-group mb-4">
                    <label for="inputUsername">Username</label>
                    <input class="form-control" type="text" id="inputUsername" placeholder="Username" name="name" required="required" value="{{ old('username') }}">
                    <small class="form-text text-muted">
                        Case sensitive, first letter is always capitalized, you do not need to use all uppercase.
                        Note that this need not be your real name. Please make sure you don't leave any trailing
                        spaces or underscores on your requested username. Usernames may not consist entirely of
                        numbers, contain the following characters: <code># / | [ ] { } &lt; &gt; @ % :</code> or
                        exceed 85 characters in length.
                    </small>
                </div>
                <div class="form-group mb-4">
                    <label for="inputEmail">Email</label>
                    <input class="form-control" type="email" id="inputEmail" placeholder="Email" name="email" required="required" value="{{ old('email') }}">
                </div>
                <div class="form-group mb-4">
                    <label for="inputEmailConfirm">Confirm Email</label>
                    <input class="form-control" type="email" id="inputEmailConfirm" placeholder="Confirm Email" name="emailconfirm"
                           required="required">
                    <small class="form-text text-muted">
                        We need a valid email in order to send you your password and confirm your account request.
                        Without it, you will not receive your password, and will be unable to log in to your account.
                    </small>
                </div>
                <div class="form-group mb-4">
                    <label for="inputComments">Comments</label>
                    <textarea class="form-control h-25" id="inputComments" rows="4" name="comments">{{ old('comments', $appeal->appealtext) }}</textarea>
                    <small class="form-text text-muted">
                        Any additional details you feel are relevant may be placed here. <strong>Please do NOT ask
                            for a specific password. One will be randomly created for you.</strong>
                    </small>
                </div>
                <div>
                    <button type="submit" class="btn btn-primary">Send request</button>
                </div>
            </form>
        </div>
    </div>
@endsection

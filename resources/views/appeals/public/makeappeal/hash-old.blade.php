@extends('layouts.app')

@section('title', htmlspecialchars(__('appeals.key.header')))
@section('scripts')
        $(document).ready(function() {
            var count = 1;
            setInterval(function() {
                if ($('#appeal-key').is(':visible')) {
                    return;
                }
                if (count > 8) {
                    $('#progress').hide();
                    $('#error').show();
                    $('#appeal-key').show();
                    return;
                }
                var appealkey = $('#appealkey').text();
                var token = document.getElementsByName("_token")[0].value;
                $.ajax({
                    url: '{{ route('public.appeal.checkstatus') }}',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'appealkey': appealkey,
                    },
                    type: 'POST',
                    success: function(data) {
                        if (data.status === 'success' && data.processed === true && data.notfound === false) {
                            $('#appeal-key').show();
                            $('#progress').hide();
                            $('#appealfound').show();
                        }
                        if (data.status === 'success' && data.processed === true && data.notfound === true) {
                            $('#appeal-key').show();
                            $('#progress').hide();
                            $('#notfound').show();
                        }
                        if (data.status === 'Failed - appeal not found') {
                            $('#appeal-key').show();
                            $('#progress').hide();
                            $('#error').show();
                        }
                    }
                });
                count++;
            }, 30000);
        });
@endsection
@section('content')
    <div class="card">
        <h5 class="card-header">{{ __('appeals.key.header') }}</h5>
        <div class="card-body">
            <div id="progress" style="{{ $processed ? 'display:none;':''}}">
                <h4 style="text-align: center">Your appeal is being processed. Please wait...</h4>
                <br />
                <div class="progress">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-warning" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div>
                </div>
            </div>
            <div id="error" style='display:none;'>
                <div class="alert alert-danger" role="alert">
                    {{__('appeals.process.error')}}
                </div>
            </div>
            <div id="notfound" style='display:none;'>
                <div class="alert alert-danger" role="alert">
                    {{__('appeals.process.notfound')}}
                </div>
            </div>
            <div id="appealfound" style='display:none;'>
                <div class="alert alert-success" role="alert">
                    {{__('appeals.process.success')}}
                </div>
            </div>
            <div id="appeal-key" style="{{ $processed ? '':'display:none;'}}">
                <center>{{ __('appeals.key.your-key-is') }}<br>
                    <h2><div id="appealkey">{{ $hash }}</div></h2></center>
                <br/>
                {{ html()->form('POST', route('public.appeal.view'))->open() }}
                {{ html()->token() }}
                {{ html()->hidden('appealkey', $hash) }}
                {{ html()->submit(__('appeals.key.view-appeal-details'))->class('btn btn-primary') }}
                {{ html()->form()->close() }}
            </div>
            <div id="proxyask" style="{{ $askproxy ? 'display: none':''}}">
                <br />
                <div class="alert alert-warning" role="alert">
                    <h4>{{ __('appeals.key.proxyask') }}</h4>
                    <a href="https://meta.wikimedia.org/wiki/Special:MyLanguage/No_open_proxies">https://meta.wikimedia.org/wiki/Special:MyLanguage/No_open_proxies</a>
                </div>
                <br>
                {{ html()->form('POST', route('public.appeal.proxyreason'))->open() }}
                {{ html()->textarea('proxyreason', NULL)->rows(7)->cols(100)}}
                {{ html()->token() }}
                {{ html()->hidden('appealkey', $hash) }}
                <br />
                {{ html()->submit(__('generic.submit'))->class('btn btn-primary') }}
                {{ html()->form()->close() }}
            </div>
        </div>
    </div>
@endsection

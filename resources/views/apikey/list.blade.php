@extends('layouts.app')

@section('title', 'API Keys')
@section('content')
    <!-- error or success messages -->
    <div class="container" style="max-width:100%">
        <br/>
        @if(session()->has('message'))
            <div class="alert alert-info">
                {{ session('message') }}
            </div>
        @endif
        @if(session()->has('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif
        @if(session()->has('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
    </div>
    <!-- container with a list of API keys -->
    <div class="container">
        <div class="card my-12">
            <div class="card-header">
                <h5>{{__('api.title')}}</h5>
            </div>
            <!--new key collapsable form -->
            <div class="card-body">
                <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#newKeyForm" aria-expanded="false" aria-controls="newKeyForm">
                    {{__('api.new.button')}}
                </button>
                <br /><br />
                <div class="collapse" id="newKeyForm">
                    <div class="card card-body">
                        <form method="POST" action="{{ route('apikey.create') }}">
                            @csrf
                            <label for="newKey">{{__('api.label')}}</label>
                            <input type="text" class="form-control" id="newKey" name="name" required>
                            <label for="permission">{{__('api.permission')}}</label>
                            <select class="form-select" id="permission" name="permission" required>
                                <option value="public">{{__('api.permission-public')}}</option>
                                <option value="admin">{{__('api.permission-admin')}}</option>
                                <option value="acc">{{__('api.permission-acc')}}</option>
                            </select>
                            <!-- date time picker -->
                            <label for="expires_at">{{__('api.expires')}}</label>
                            <input type="datetime-local" class="form-control" id="expires_at" name="expires_at">
                            <br /><button type="submit" class="btn btn-primary">{{__('generic.submit')}}</button>
                        </form>
                    </div>
                </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>{{__('api.key')}}</th>
                            <th>{{__('api.label')}}</th>
                            <th>{{__('api.permission')}}</th>
                            <th>{{__('api.expires')}}</th>
                            <th>{{__('appeals.section-headers.actions')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($apikeys as $apikey)
                            <tr>
                                <td>{{ $apikey->key }}</td>
                                <td>{{ $apikey->name }}</td>
                                <td>{{__('api.permission-'. $apikey->permission)}}</td>
                                <!-- if active is false, show inactive in red. if not show red if expired, and black if not -->
                                <td>
                                    @if($apikey->active == False)
                                        <span style="color:red">{{__('api.inactive')}}</span>
                                    @else
                                        @if($apikey->expires_at < now())
                                            <span style="color:red">{{__('api.expired')}}</span>
                                        @else
                                            <span>{{ $apikey->expires_at }}</span>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    <!-- if key is inactive, show activate button, if not show revoke button. if activate is shown, then provide popup with date time picker form -->
                                    @if($apikey->expires_at < now())
                                        <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#expiresForm{{$apikey->id}}" aria-expanded="false" aria-controls="expiresForm{{$apikey->id}}">
                                            {{__('api.activate')}}
                                        </button>
                                        <div class="collapse" id="expiresForm{{$apikey->id}}">
                                            <div class="card card-body">
                                                <form method="POST" action="{{ route('apikey.activate', $apikey->id) }}">
                                                    @csrf
                                                    <label for="expires_at">{{__('api.expires')}}</label>
                                                    <input type="datetime-local" class="form-control" id="expires_at" name="expires_at">
                                                    <br /><button type="submit" class="btn btn-primary">{{__('generic.submit')}}</button>
                                                </form>
                                            </div>
                                        </div>
                                    @elseif($apikey->active)
                                        <form method="POST" action="{{ route('apikey.revoke', $apikey->id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-danger">{{__('api.revoke')}}</button>
                                        </form>
                                        <!-- add regenerate button -->
                                        <form method="POST" action="{{ route('apikey.regenerate', $apikey->id) }}">
                                            @csrf
                                            <br /><button type="submit" class="btn btn-warning">{{__('api.regenerate')}}</button>
                                        </form>
                                    @else
                                        <!-- show activate button with a popup form to set expiration date -->
                                        <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#expiresForm{{$apikey->id}}" aria-expanded="false" aria-controls="expiresForm{{$apikey->id}}">
                                            {{__('api.activate')}}
                                        </button>
                                        <div class="collapse" id="expiresForm{{$apikey->id}}">
                                            <div class="card card-body">
                                                <form method="POST" action="{{ route('apikey.activate', $apikey->id) }}">
                                                    @csrf
                                                    <label for="expires_at">{{__('api.expires')}}</label>
                                                    <input type="datetime-local" class="form-control" id="expires_at" name="expires_at">
                                                    <br /><button type="submit" class="btn btn-primary">{{__('generic.submit')}}</button>
                                                </form>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
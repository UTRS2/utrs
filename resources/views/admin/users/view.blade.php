@extends('layouts.app')

@section('title', $user->username)
@section('content')
    @can('viewAny', \App\Models\User::class)
        <div class="mb-2">
            <a href="{{ route('admin.users.list') }}" class="btn btn-primary">
                Back to user list
            </a>
        </div>
    @endcan

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

    {{ Form::open(['url' => route('admin.users.update', $user)]) }}
    <div class="card mb-4">
        <h5 class="card-header">User details</h5>
        <div class="card-body">
            <table class="table">
                <tbody>
                <tr>
                    <th>ID</th>
                    <td>{{ $user->id }}</td>
                </tr>
                <tr>
                    <th>Name</th>
                    <td>{{ $user->username }}</td>
                </tr>
                <tr>
                    <th>Last Permission Check</th>
                    <td>{{ $user->last_permission_check_at }}</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mb-4">
        <h5 class="card-header">Permissions</h5>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Wiki</th>
                        <th>CheckUser</th>
                        <th>Oversight</th>
                        <th>Steward</th>
                        <th>WMF Staff</th>
                        <th>UTRS Developer</th>
                        <th>Tool admin</th>
                        <th>Sysop</th>
                        <th>User</th>
                    </tr>
                </thead>

                <tbody>
                @foreach(\App\MwApi\MwApiUrls::getSupportedWikis(true) as $wiki)
                    @php
                        $wikiDbName = $wiki === 'global' ? '*' : $wiki;
                        /** @var \App\Models\User $user */ /** @var \App\Models\Permission $permission */
                        $permission = $user->permissions->where('wiki', $wikiDbName)->first();
                    @endphp
                    <tr>
                        <td>
                            {{ $wikiDbName }}
                        </td>

                        @foreach(\App\Models\Permission::ALL_POSSIBILITIES as $permNode)
                            @php $oldValue = $permission && $permission->$permNode; @endphp
                            <td>
                                @can('updatePermission', [$user, $wikiDbName, $permNode])
                                    {{ Form::checkbox('permission[' . $wiki . '][' . $permNode . ']', 1,
                                        old('permission.' . $wiki . '.' . $permNode, $oldValue)) }}
                                @else
                                    {{ $oldValue ? 'Yes' : '-' }}
                                @endcan
                            </td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @can('update', $user)
        <div class="card mb-4">
            <h5 class="card-header">Options</h5>
            <div class="card-body">
                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        {{ Form::checkbox('refresh_from_wiki', 1, old('refresh_from_wiki') === 1, ['class' => 'custom-control-input', 'id' => 'refresh_from_wiki']) }} {{ Form::label('refresh_from_wiki', 'Reload permissions from attached wikis', ['class' => 'custom-control-label']) }}
                    </div>
                </div>
            </div>
        </div>
        

        <div class="card mb-4">
            <h5 class="card-header">Save changes</h5>
            <div class="card-body">
                <div class="form-group">
                    {{ Form::label('reason', 'Reason') }}
                    {{ Form::input('text', 'reason', old('reason'), ['class' => 'form-control']) }}

                    @error('reason')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                {{ Form::submit('Save', ['class' => 'btn btn-primary']) }}
            </div>
        </div>
    @endcan

    <div class="card">
        <h5 class="card-header">Logs</h5>
        <div class="card-body">
            <table class="table">
                <thead>
                <tr>
                    <th scope="col">Acting user</th>
                    <th scope="col">Time</th>
                    <th scope="col">Details</th>
                </tr>
                </thead>
                <tbody>
                @foreach($user->logs as $log)
                    <tr class="{{ $log->action === 'comment' ? 'bg-success' : '' }}">
                        @if($log->user == 0)
                            <td><i>System</i></td>
                        @else
                            @can('view', $log->userObject)
                                <td><i><a href="{{ route('admin.users.view', $log->userObject) }}">{{ $log->userObject->username }}</a></i></td>
                            @else
                                <td><i>{{ $log->userObject->username }}</i></td>
                            @endcan
                        @endif
                        <td><i>{{ $log->timestamp }}</i></td>
                        @if($log->protected && !$perms['functionary'])
                            <td><i>Access to comment is restricted.</i></td>
                        @else
                            @if($log->comment!==null)
                                <td><i>{{ $log->comment }}</i></td>
                            @else
                                @if(!is_null($log->reason))
                                    <td><i>Action: {{ $log->action }},
                                            Reason: {{ $log->reason }}</i></td>
                                @else
                                    <td><i>Action: {{ $log->action }}</i></td>
                                @endif
                            @endif
                        @endif
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    {{ Form::close() }}
@endsection

@extends('layouts.app')
@section('content')
    <div class="mb-2">
        <a href="{{ route('admin.users.list') }}" class="btn btn-primary">
            Back to user list
        </a>
    </div>

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
                    <th>Verified</th>
                    <td>{{ $user->verified ? 'Yes' : 'No' }}</td>
                </tr>
                <tr>
                    <th>Wikis</th>
                    <td>{{ $user->wikis }}</td>
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
                        <th>Privacy</th>
                        <th>Sysop</th>
                        <th>User</th>
                    </tr>
                </thead>

                <tbody>
                @foreach($user->permissions as $permission)
                    <tr>
                        <td>
                            {{ $permission->wiki }}
                        </td>

                        @foreach(App\Permission::ALL_POSSIBILITIES as $permNode)
                            <td>
                                @can('updatePermission', [$user, $permission->wiki, $permNode])
                                    {{ Form::checkbox('permission[' . $permission->wikiFormKey . '][' . $permNode . ']', 1,
                                        old('permission.' . $permission->wikiFormKey . '.' . $permNode, $permission->$permNode)) }}
                                @else
                                    {{ $permission->$permNode ? 'Yes' : '-' }}
                                @endcan
                            </td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mb-4">
        <h5 class="card-header">Save changes</h5>
        <div class="card-body">
            <div class="form-group">
                {{ Form::label('reason', 'Reason for changing user permissions') }}
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

    <div class="card">
        <h5 class="card-header">Logs</h5>
        <div class="card-body">
            {{ json_encode($user->logs) }}
        </div>
    </div>
    {{ Form::close() }}
@endsection

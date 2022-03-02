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
                    <th>CentralAuth ID</th>
                    <td>{{ $user->mediawiki_id ?? '(not known)' }}</td>
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
                @foreach(\App\Services\Facades\MediaWikiRepository::getSupportedTargets(true) as $wiki)
                    @php
                        /** @var \App\Models\User $user */ /** @var \App\Models\Permission $permission */
                        $permission = $user->permissions->where('wiki', $wiki)->first();
                    @endphp
                    <tr>
                        <td>
                            {{ $wiki }}
                        </td>

                        @foreach(\App\Models\Permission::ALL_POSSIBILITIES as $permNode)
                            @php $oldValue = $permission && $permission->$permNode; @endphp
                            <td>
                                @can('updatePermission', [$user, $wiki, $permNode])
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

    @component('components.logs', ['logs' => $user->logs])
    @endcomponent
    {{ Form::close() }}
@endsection

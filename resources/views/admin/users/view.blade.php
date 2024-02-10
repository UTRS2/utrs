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
    
    @if(isset($setemail) && $verifiedemail === true)
        <div class="alert alert-success" role="alert">
            Email address updated successfully.
        </div>
    @endif

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

    {{ html()->form('POST', route('admin.users.update', $user))->open() }}
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
        <h5 class="card-header">Default Language</h5>
        <div class="card-body">
            <div class="form-group">
                {{ html()->label('Default Language', 'default_translation_language') }}<br />
                {{ html()->select('default_translation_language', $languages, $langid, old('default_translation_language'))->class('form-control') }}<br />
                @error('default_translation_language')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>
    </div>
    @if($setemail)
    <div class="card mb-4">
        <h5 class="card-header">Email address</h5>
        <div class="card-body">
            <div class="form-group">
                {{ html()->label('Email address', 'email') }}<br />
                {{ html()->textarea('email', $user->email, old('email'))->class('form-control h-1') }}<br />

                <!-- checkboxes for email preferences -->
                {{ html()->label('Email preferences', 'email_preferences') }}<br />
                <div class="custom-control custom-checkbox">
                    {{ html()->checkbox('appeal_notifications', old('appeal_notifications') === 1, 1)->checked(old('appeal_notifications', $user->appeal_notifications))->class('custom-control-input')->id('appeal_notifications') }} {{ html()->label('Notify me when there is a response to an appeal', 'appeal_notifications')->class('custom-control-label') }}
                </div>
                <div class="custom-control custom-checkbox">
                    {{ html()->checkbox('weekly_appeal_list', old('weekly_appeal_list') === 1, 1)->checked(old('weekly_appeal_list', $user->weekly_appeal_list))->class('custom-control-input')->id('weekly_appeal_list') }} {{ html()->label('Send me a weekly list of appeals where I am the blocking admin', 'weekly_appeal_list')->class('custom-control-label') }}
                </div>

                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

        </div>
    </div>
    @endif
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
                        <th>Steward Clerk</th>
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
                                    {{ html()->checkbox('permission[' . $wiki . '][' . $permNode . ']', old('permission.' . $wiki . '.' . $permNode, $oldValue), 1) }}
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
                        {{ html()->checkbox('refresh_from_wiki', old('refresh_from_wiki') === 1, 1)->class('custom-control-input')->id('refresh_from_wiki') }} {{ html()->label('Reload permissions from attached wikis', 'refresh_from_wiki')->class('custom-control-label') }}
                    </div>
                </div>
            </div>
        </div>
        

        <div class="card mb-4">
            <h5 class="card-header">Save changes</h5>
            <div class="card-body">
                <div class="form-group">
                    {{ html()->label('Reason', 'reason') }} <br />
                    {{ html()->textarea('reason', NULL, old('reason'))->class('form-control h-1') }}

                    @error('reason')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                {{ html()->submit('Save')->class('btn btn-primary') }}
            </div>
        </div>
    @endcan

    @component('components.logs', ['logs' => $user->logs])
    @endcomponent
    {{ html()->form()->close() }}
@endsection

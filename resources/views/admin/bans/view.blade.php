@extends('layouts.app')

@section('title', 'Ban for ' . $target)
@section('content')
    @can('viewAny', \App\Models\Ban::class)
        <div class="mb-2">
            <a href="{{ route('admin.bans.list') }}" class="btn btn-primary">
                Back to ban list
            </a>
        </div>
    @endcan

    @component('components.errors')
    @endcomponent

    <div class="card mb-4">
        <h5 class="card-header">Ban details</h5>
        <div class="card-body">
            <table class="table">
                <tbody>
                <tr>
                    <th>ID</th>
                    <td>{{ $ban->id }}</td>
                </tr>
                <tr>
                    <th>Target</th>
                    <td>{!! $targetHtml !!}</td>
                </tr>
                <tr>
                    <th>Wiki</th>
                    <td>
                        @if($ban->wiki)
                            {{ $ban->wiki->display_name }} ({{ $ban->wiki->database_name }})
                        @else
                            All UTRS wikis
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Reason</th>
                    <td>{{ $ban->reason }}</td>
                </tr>
                <tr>
                    <th>Expires at</th>
                    <td>{!! $formattedExpiry !!}</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

    @can('update', $ban)
        {{ html()->form('POST', route('admin.bans.update', $ban))->open() }}
        {{ html()->token() }}
        <div class="card mb-4">
            <h5 class="card-header">Modify ban options</h5>
            <div class="card-body">
                <div class="form-group mb-4">
                    Active
                    <div class="custom-control custom-radio">
                        {{ html()->radio('is_active', old('is_active', $ban->is_active) == 0, 0)->class('custom-control-input')->id('is_active-0') }} {{ html()->label('Ban has no effect', 'is_active-0')->class('custom-control-label') }}
                    </div>

                    <div class="custom-control custom-radio">
                        {{ html()->radio('is_active', old('is_active', $ban->is_active) == 1, 1)->class('custom-control-input')->id('is_active-1') }} {{ html()->label('Ban is active', 'is_active-1')->class('custom-control-label') }}
                    </div>
                </div>

                @if(sizeof($wikis) > 1)
                    <div class="form-group">
                        {{ html()->label('Wiki', 'wiki_id') }}
                        {{ html()->select('wiki_id', $wikis, old('wiki_id', $ban->wiki_id))->class('form-control') }}
                    </div>
                @else
                    {{ html()->hidden('wiki_id', array_keys($wikis)[0]) }}
                @endif

                <div class="form-group mb-4">
                    {{ html()->label('Ban reason', 'reason') }}
                    {{ html()->text('reason', old('reason', $ban->reason))->class('form-control') }}
                    <p class="small">
                        This will be shown to the user.
                    </p>
                </div>

                <div class="form-group mb-4">
                    {{ html()->label('Expiration', 'expiry') }}
                    {{ html()->text('expiry', old('expiry', $formOldExpiry))->class('form-control') }}
                    <p class="small">
                        Leave empty or as 'indefinite' for a permanent ban.
                    </p>
                </div>

                <div class="form-group mb-4">
                    {{ html()->label('Reason for changes', 'update_reason') }}
                    {{ html()->input('text', 'update_reason', old('update_reason'), ['class' => 'form-control' . ($errors->has('update_reason') ? ' is-invalid' : '')]) }}

                    @error('update_reason')
                    <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                @can('oversight', $ban)
                    <hr/>
                    <div class="form-group mb-2">
                        Ban target visibility
                        <div class="custom-control custom-radio">
                            {{ html()->radio('is_protected', old('is_protected', $ban->is_protected) == 0, 0)->class('custom-control-input')->id('is_protected-0') }} {{ html()->label('Ban target is visible to all users who can view ban list', 'is_protected-0')->class('custom-control-label') }}
                        </div>

                        <div class="custom-control custom-radio">
                            {{ html()->radio('is_protected', old('is_protected', $ban->is_protected) == 1, 1)->class('custom-control-input')->id('is_protected-1') }} {{ html()->label('Ban target is oversighted', 'is_protected-1')->class('custom-control-label') }}
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        {{ html()->label('Visibility change reason', 'os_reason') }}
                        {{ html()->text('os_reason', old('os_reason'))->class('form-control') }}
                        <p class="small">
                            Reason for restricting the ban target visibility to oversighters only. This can only be seen
                            by functionaries.
                        </p>
                    </div>
                @endcan

                <hr/>
                {{ html()->submit('Save')->class('btn btn-primary') }}
            </div>
        </div>
        {{ html()->form()->close() }}
    @endcan

    @component('components.logs', ['logs' => $ban->logs])
    @endcomponent
@endsection
